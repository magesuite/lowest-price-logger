<?php

namespace MageSuite\LowestPriceLogger\Model;

class GenerateLowestPriceForAllProducts
{
    protected \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory;
    protected \Magento\Customer\Model\Session $customerSession;
    protected \Magento\Store\Model\StoreManagerInterface $storeManager;
    protected \Magento\Framework\DB\Adapter\AdapterInterface $connection;
    protected \MageSuite\LowestPriceLogger\Model\FilterOutDuplicates $filterOutDuplicates;
    protected GetCurrentDate $getCurrentDate;
    protected \Magento\Customer\Model\GroupManagement $groupManagement;
    protected \MageSuite\LowestPriceLogger\Helper\Configuration $configuration;
    protected $customerGroups = null;
    protected AddCatalogRulePricesToCollection $addCatalogRulePricesToCollection;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \MageSuite\LowestPriceLogger\Model\FilterOutDuplicates $filterOutDuplicates,
        GetCurrentDate $getCurrentDate,
        \Magento\Customer\Model\GroupManagement $groupManagement,
        \MageSuite\LowestPriceLogger\Helper\Configuration $configuration,
        AddCatalogRulePricesToCollection $addCatalogRulePricesToCollection
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->connection = $resourceConnection->getConnection();
        $this->filterOutDuplicates = $filterOutDuplicates;
        $this->getCurrentDate = $getCurrentDate;
        $this->groupManagement = $groupManagement;
        $this->configuration = $configuration;
        $this->addCatalogRulePricesToCollection = $addCatalogRulePricesToCollection;
    }

    public function execute()
    {
        $websites = $this->storeManager->getWebsites();

        foreach ($websites as $website) {
            $this->generatePricesForWebsite($website);
        }
    }

    protected function generatePricesForWebsite($website)
    {
        $stores = $website->getStores();

        if (empty($stores)) {
            return;
        }

        $store = current($stores);

        if (!$store) {
            return;
        }

        $storeId = $store->getStoreId();
        $websiteId = $website->getId();

        foreach ($this->getProductsBatch($storeId) as $products) {
            $prices = $this->calculateProductPrices($products, $websiteId);
            $prices = $this->filterOutDuplicates->execute($prices, $websiteId);

            if (empty($prices)) {
                continue;
            }

            $this->connection->insertOnDuplicate(
                $this->connection->getTableName('price_history_log'),
                $prices,
                []
            );
        }
    }

    public function getProductsBatch($storeId)
    {
        $page = 1;

        $collection = $this->productCollectionFactory->create();
        $collection->setStoreId($storeId);
        $collection->setPageSize($this->configuration->getBatchSize());
        $lastPageNumber = $collection->getLastPageNumber();

        do {
            $this->storeManager->setCurrentStore($this->storeManager->getStore($storeId));
            $collection = $this->productCollectionFactory->create();
            $collection->setPage($page, $this->configuration->getBatchSize());
            $collection->addAttributeToSelect('*');
            $collection->setStoreId($storeId);
            $collection->addTierPriceData();
            $collection = $this->processCollection($collection, $storeId);

            $page++;

            $products = $collection->getItems();

            yield $products;
        } while ($page <= $lastPageNumber);
    }

    protected function calculateProductPrices(array $products, int $websiteId): array
    {
        $prices = [];

        foreach ($this->getCustomerGroups() as $customerGroup) {
            $customerGroupId = $customerGroup->getId();
            $this->customerSession->setCustomerGroupId($customerGroupId);

            $this->addCatalogRulePricesToCollection->execute($products, $websiteId, $customerGroupId);

            foreach ($products as $product) {
                $productId = $product->getId();

                if (!$productId) {
                    continue;
                }

                $product->setCustomerGroupId($customerGroupId);

                $priceInfo = $product->getPriceInfo();

                if ($product->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
                    && $product->getPriceType() == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
                    $finalPrice = $regularPrice = (float) $product->getPriceInfo()
                        ->getPrice(\Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE)
                        ->getAmount()
                        ->getValue();
                } else {
                    $finalPrice = $priceInfo
                        ->getPrice(\Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE)
                        ->getValue();

                    $regularPrice = $priceInfo
                        ->getPrice(\Magento\Catalog\Pricing\Price\RegularPrice::PRICE_CODE)
                        ->getValue();
                }

                $prices[] = [
                    'product_id' => $productId,
                    'price' => $finalPrice,
                    'website_id' => $websiteId,
                    'customer_group_id' => $customerGroupId,
                    'price_type' => \MageSuite\LowestPriceLogger\Api\LogPriceInterface::PRICE_TYPE_FINAL,
                    'log_date' => $this->getCurrentDate->execute(),
                    'was_autogenerated_by_cron' => true
                ];

                $prices = $this->addCustomPrices($prices, $product, $websiteId, $customerGroupId);

                if ($regularPrice === $finalPrice) {
                    continue;
                }

                $prices[] = [
                    'product_id' => $productId,
                    'price' => $regularPrice,
                    'website_id' => $websiteId,
                    'customer_group_id' => $customerGroupId,
                    'price_type' => \MageSuite\LowestPriceLogger\Api\LogPriceInterface::PRICE_TYPE_REGULAR,
                    'log_date' => $this->getCurrentDate->execute(),
                    'was_autogenerated_by_cron' => true
                ];
            }
        }

        return $prices;
    }

    protected function getCustomerGroups()
    {
        if ($this->customerGroups == null) {
            $this->customerGroups = array_merge([$this->groupManagement->getNotLoggedInGroup()], $this->groupManagement->getLoggedInGroups());
        }

        return $this->customerGroups;
    }

    /**
     * Extension point for adding custom prices to log
     */
    public function addCustomPrices(array $prices, $product, int $websiteId, int $customerGroupId) // phpcs:ignore
    {
        return $prices;
    }

    /**
     * Extension point for modifying collection configuration to allow customizations
     */
    public function processCollection(\Magento\Catalog\Model\ResourceModel\Product\Collection $collection, int $storeId)
    {
        return $collection;
    }
}

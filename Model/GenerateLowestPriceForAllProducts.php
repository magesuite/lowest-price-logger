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
    protected \Magento\CatalogRule\Model\ResourceModel\Product\CollectionProcessor $catalogRuleCollectionProcessor;
    protected \MageSuite\LowestPriceLogger\Helper\Configuration $configuration;
    protected $customerGroups = null;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \MageSuite\LowestPriceLogger\Model\FilterOutDuplicates $filterOutDuplicates,
        GetCurrentDate $getCurrentDate,
        \Magento\Customer\Model\GroupManagement $groupManagement,
        \Magento\CatalogRule\Model\ResourceModel\Product\CollectionProcessor $catalogRuleCollectionProcessor,
        \MageSuite\LowestPriceLogger\Helper\Configuration $configuration
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->connection = $resourceConnection->getConnection();
        $this->filterOutDuplicates = $filterOutDuplicates;
        $this->getCurrentDate = $getCurrentDate;
        $this->groupManagement = $groupManagement;
        $this->catalogRuleCollectionProcessor = $catalogRuleCollectionProcessor;
        $this->configuration = $configuration;
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
            $collection = $this->productCollectionFactory->create();
            $collection->setPage($page, $this->configuration->getBatchSize());
            $collection->addAttributeToSelect('*');
            $collection->setStoreId($storeId);
            $this->catalogRuleCollectionProcessor->addPriceData($collection);
            $collection->addTierPriceData();

            $page++;

            $products = $collection->getItems();

            yield $products;
        } while ($page <= $lastPageNumber);
    }

    protected function calculateProductPrices(array $products, int $websiteId): array
    {
        $prices = [];

        foreach ($products as $product) {
            // catalog_rule_price should be joined automatically
            // if there is no catalog rule price available for the product
            // then we need to put false into the field so pricing engine do not try to
            // fetch it again with a separate SQL query for every product
            if (!$product->hasData('catalog_rule_price')) {
                $product->setData('catalog_rule_price', false);
            }

            $productId = $product->getId();

            if (!$productId) {
                continue;
            }

            foreach ($this->getCustomerGroups() as $customerGroup) {
                $customerGroupId = $customerGroup->getId();

                $this->customerSession->setCustomerGroupId($customerGroupId);
                $product->setCustomerGroupId($customerGroupId);

                $priceInfo = $product->getPriceInfo();

                $finalPrice = $priceInfo
                    ->getPrice(\Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE)
                    ->getValue();

                $regularPrice = $priceInfo
                    ->getPrice(\Magento\Catalog\Pricing\Price\RegularPrice::PRICE_CODE)
                    ->getValue();

                $prices[] = [
                    'product_id' => $productId,
                    'price' => $finalPrice,
                    'website_id' => $websiteId,
                    'customer_group_id' => $customerGroupId,
                    'price_type' => \MageSuite\LowestPriceLogger\Plugin\Framework\Pricing\Price\PriceInterface\LogPrice::PRICE_TYPE_FINAL,
                    'log_date' => $this->getCurrentDate->execute(),
                    'was_autogenerated_by_cron' => true
                ];

                if ($regularPrice === $finalPrice) {
                    continue;
                }

                $prices[] = [
                    'product_id' => $productId,
                    'price' => $regularPrice,
                    'website_id' => $websiteId,
                    'customer_group_id' => $customerGroupId,
                    'price_type' => \MageSuite\LowestPriceLogger\Plugin\Framework\Pricing\Price\PriceInterface\LogPrice::PRICE_TYPE_REGULAR,
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
}

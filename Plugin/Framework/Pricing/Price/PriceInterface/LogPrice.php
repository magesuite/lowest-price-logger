<?php

namespace MageSuite\LowestPriceLogger\Plugin\Framework\Pricing\Price\PriceInterface;

class LogPrice
{
    public const PRICE_TYPE_REGULAR = 2;
    public const PRICE_TYPE_FINAL = 1;

    protected $priceTypes = [
        \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE => 1,
        \Magento\Catalog\Pricing\Price\RegularPrice::PRICE_CODE => 2
    ];

    protected \Magento\Customer\Model\Session $customerSession;
    protected \Magento\Store\Model\StoreManagerInterface $storeManager;
    protected \Magento\Framework\DB\Adapter\AdapterInterface $connection;
    protected \MageSuite\LowestPriceLogger\Model\PriceStorage $priceStorage;
    protected \MageSuite\LowestPriceLogger\Model\GetCurrentDate $getCurrentDate;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \MageSuite\LowestPriceLogger\Model\PriceStorage $priceStorage,
        \MageSuite\LowestPriceLogger\Model\GetCurrentDate $getCurrentDate
    ) {
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->connection = $resourceConnection->getConnection();
        $this->priceStorage = $priceStorage;
        $this->getCurrentDate = $getCurrentDate;
    }

    public function afterGetValue(\Magento\Framework\Pricing\Price\PriceInterface $subject, $result)
    {
        try {
            $priceCode = $subject->getPriceCode();

            if ($priceCode !== \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE
                && $priceCode !== \Magento\Catalog\Pricing\Price\RegularPrice::PRICE_CODE) {
                return $result;
            }

            $quantity = $subject->getQuantity();

            if ($quantity > 1) {
                return $result;
            }

            $product = $subject->getProduct();

            if (!$product) {
                return $result;
            }

            $productId = $product->getId();
            $price = $result;
            $websiteId = $this->storeManager->getWebsite()->getId();
            $customerGroupId = $this->customerSession->getCustomerGroupId();
            $date = $this->getCurrentDate->execute();

            $this->priceStorage->addPriceData([
                'product_id' => $productId,
                'price' => $price,
                'website_id' => $websiteId,
                'customer_group_id' => $customerGroupId,
                'price_type' => $this->priceTypes[$priceCode],
                'log_date' => $date
            ]);
        } catch (\Exception $exception) { // phpcs:ignore
        }

        return $result;
    }
}

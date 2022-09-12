<?php

namespace MageSuite\LowestPriceLogger\Plugin\Framework\Pricing\Price\PriceInterface;

class LogPrice implements \MageSuite\LowestPriceLogger\Api\LogPriceInterface
{
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

            $price = $this->getPrice($product, (float)$result);

            $websiteId = $this->storeManager->getWebsite()->getId();
            $customerGroupId = $this->customerSession->getCustomerGroupId();
            $date = $this->getCurrentDate->execute();

            $this->priceStorage->addPriceData([
                'product_id' => $productId,
                'price' => $price,
                'website_id' => $websiteId,
                'customer_group_id' => $customerGroupId,
                'price_type' => self::PRICE_TYPES[$priceCode],
                'log_date' => $date
            ]);
        } catch (\Exception $exception) { // phpcs:ignore
        }

        return $result;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param float $price
     * @return float
     */
    protected function getPrice($product, float $price): float
    {
        if ($product->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
            && $product->getPriceType() == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
            /**
             * getAmount() method returns \Magento\Framework\Pricing\Adjustment\AdjustmentInterface
             */
            return (float) $product->getPriceInfo()
                ->getPrice(\Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE)
                ->getAmount()
                ->getValue();
        }

        return $price;
    }
}

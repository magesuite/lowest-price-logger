<?php

namespace MageSuite\LowestPriceLogger\Model;

class FilterOutDuplicates
{
    protected ResourceModel\PriceHistoryLog $priceHistoryLog;

    public function __construct(\MageSuite\LowestPriceLogger\Model\ResourceModel\PriceHistoryLog $priceHistoryLog)
    {
        $this->priceHistoryLog = $priceHistoryLog;
    }

    public function execute($prices, $websiteId = null, $customerGroupId = null)
    {
        if (empty($prices)) {
            return [];
        }

        $productIds = [];

        foreach ($prices as $price) {
            $productIds[] = $price['product_id'];
        }

        $productIds = array_unique($productIds);

        $lastPrices = $this->priceHistoryLog->getLastPricesPerProduct($productIds, $websiteId, $customerGroupId);

        foreach ($prices as $index => $priceData) {
            if (!isset($lastPrices[$priceData['product_id']][$priceData['customer_group_id']][$priceData['website_id']])) {
                continue;
            }

            if (!$this->isDuplicateOfLastLoggedPrice($lastPrices, $priceData)) {
                continue;
            }

            unset($prices[$index]);
        }

        return $prices;
    }

    protected function isDuplicateOfLastLoggedPrice($lastPrices, $priceData): bool
    {
        if (isset($lastPrices[$priceData['product_id']][$priceData['customer_group_id']][$priceData['website_id']][$priceData['price_type']])) {
            if ($lastPrices[$priceData['product_id']][$priceData['customer_group_id']][$priceData['website_id']][$priceData['price_type']] == $priceData['price']) {
                return true;
            }

            return false;
        }

        // check if other price types are equal if the same price type is not in the log (can happen when final price == regular price)
        foreach ($lastPrices[$priceData['product_id']][$priceData['customer_group_id']][$priceData['website_id']] as $typedPrice) {
            if ($typedPrice != $priceData['price']) {
                return false;
            }
        }

        return true;
    }
}

<?php

namespace MageSuite\LowestPriceLogger\Model;

class PriceStorage
{
    protected $prices = [];

    public function addPriceData($priceData)
    {
        $key = $this->getKey($priceData);

        if (!isset($this->prices[$key])) {
            $this->prices[$key] = $priceData;
        }
    }

    public function getPrices()
    {
        return $this->prices;
    }

    protected function getKey($priceData)
    {
        return md5(implode('|', $priceData)); // phpcs:ignore
    }
}

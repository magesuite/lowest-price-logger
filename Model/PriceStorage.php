<?php

declare(strict_types=1);

namespace MageSuite\LowestPriceLogger\Model;

class PriceStorage
{
    protected array $prices = [];

    public function addPriceData(array $priceData): void
    {
        $key = $this->getKey($priceData);

        if (!isset($this->prices[$key])) {
            $this->prices[$key] = $priceData;
        }
    }

    public function getPrices(): array
    {
        return $this->prices;
    }

    protected function getKey(array $priceData): string
    {
        return md5(implode('|', $priceData)); // phpcs:ignore
    }
}

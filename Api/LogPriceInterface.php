<?php

declare(strict_types=1);

namespace MageSuite\LowestPriceLogger\Api;

interface LogPriceInterface
{
    public const PRICE_TYPE_REGULAR = 2;
    public const PRICE_TYPE_FINAL = 1;

    public const PRICE_TYPES = [
        \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE => 1,
        \Magento\Catalog\Pricing\Price\RegularPrice::PRICE_CODE => 2
    ];
}

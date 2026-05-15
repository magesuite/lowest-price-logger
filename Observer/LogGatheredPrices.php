<?php

declare(strict_types=1);

namespace MageSuite\LowestPriceLogger\Observer;

class LogGatheredPrices implements \Magento\Framework\Event\ObserverInterface
{
    public function __construct(
        protected \MageSuite\LowestPriceLogger\Model\PriceStorage $priceStorage,
        protected \MageSuite\LowestPriceLogger\Service\LogGatheredPrices $logGatheredPrices
    ) {
    }

    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        $prices = $this->priceStorage->getPrices();
        if (empty($prices)) {
            return;
        }

        $this->logGatheredPrices->execute($prices);
    }
}

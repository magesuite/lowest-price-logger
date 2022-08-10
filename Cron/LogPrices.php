<?php

namespace MageSuite\LowestPriceLogger\Cron;

class LogPrices
{
    protected \MageSuite\LowestPriceLogger\Helper\Configuration $configuration;
    protected \MageSuite\LowestPriceLogger\Model\GenerateLowestPriceForAllProductsFactory $generateLowestPriceForAllProductsFactory;

    public function __construct(
        \MageSuite\LowestPriceLogger\Helper\Configuration $configuration,
        \MageSuite\LowestPriceLogger\Model\GenerateLowestPriceForAllProductsFactory $generateLowestPriceForAllProductsFactory
    ) {
        $this->configuration = $configuration;
        $this->generateLowestPriceForAllProductsFactory = $generateLowestPriceForAllProductsFactory;
    }

    public function execute()
    {
        if (!$this->configuration->isCalculationCronEnabled()) {
            return;
        }

        $this->generateLowestPriceForAllProductsFactory
            ->create()
            ->execute();
    }
}

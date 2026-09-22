<?php

declare(strict_types=1);

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

    public function execute(): void
    {
        if (!$this->configuration->isCalculationCronEnabled()) {
            return;
        }

        $this->generateLowestPriceForAllProductsFactory
            ->create()
            ->execute();
    }
}

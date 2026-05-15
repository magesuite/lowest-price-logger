<?php

declare(strict_types=1);

namespace MageSuite\LowestPriceLogger\Service;

class LogGatheredPrices
{
    public function __construct(
        protected \MageSuite\LowestPriceLogger\Helper\Configuration $configuration,
        protected \MageSuite\LowestPriceLogger\Model\ResourceModel\PriceHistoryStagingLog $priceHistoryStagingLog,
        protected \MageSuite\LowestPriceLogger\Model\FilterOutDuplicates $filterOutDuplicates,
        protected \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
    }

    public function execute(array $prices): void
    {
        $prices = $this->filterOutDuplicates->execute($prices);
        if (empty($prices)) {
            return;
        }

        if ($this->configuration->isAsyncLoggingEnabled()) {
            $this->priceHistoryStagingLog->save($prices);

            return;
        }

        $connection = $this->resourceConnection->getConnection();
        $connection->insertOnDuplicate($connection->getTableName('price_history_log'), $prices, []);
    }
}

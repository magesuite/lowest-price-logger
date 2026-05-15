<?php

declare(strict_types=1);

namespace MageSuite\LowestPriceLogger\Service;

class TransferStagedPricesToLog
{
    protected const string LOCK_NAME = 'price_history_log_drain';

    public function __construct(
        protected \MageSuite\LowestPriceLogger\Model\ResourceModel\PriceHistoryStagingLog $stagingLog,
        protected \MageSuite\LowestPriceLogger\Model\FilterOutDuplicates $filterOutDuplicates,
        protected \MageSuite\LowestPriceLogger\Helper\Configuration $configuration,
        protected \Magento\Framework\App\ResourceConnection $resourceConnection,
        protected \Magento\Framework\Lock\LockManagerInterface $lockManager
    ) {
    }

    public function execute(): void
    {
        if (!$this->configuration->isAsyncLoggingEnabled()) {
            return;
        }

        if (!$this->lockManager->lock(self::LOCK_NAME, 0)) {
            return;
        }

        try {
            $this->drain();
        } finally {
            $this->lockManager->unlock(self::LOCK_NAME);
        }
    }

    protected function drain(): void
    {
        $connection = $this->resourceConnection->getConnection();
        $batchSize = $this->configuration->getBatchSize();

        while (true) {
            $rows = $this->stagingLog->getBatch($batchSize);
            if (empty($rows)) {
                break;
            }

            $maxId = (int) max(array_column($rows, 'id'));
            $prices = $this->unsetStagingId($rows);

            $filteredPrices = $this->filterOutDuplicates->execute($prices);
            if (!empty($filteredPrices)) {
                $connection->insertOnDuplicate(
                    $connection->getTableName('price_history_log'),
                    $filteredPrices,
                    []
                );
            }

            $this->stagingLog->deleteUpToId($maxId);
        }
    }

    protected function unsetStagingId(array $rows): array
    {
        return array_map(fn(array $row) => array_diff_key($row, ['id' => null]), $rows);
    }
}
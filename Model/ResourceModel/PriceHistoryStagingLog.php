<?php

declare(strict_types=1);

namespace MageSuite\LowestPriceLogger\Model\ResourceModel;

class PriceHistoryStagingLog
{
    public const TABLE_NAME = 'price_history_log_staging';

    protected \Magento\Framework\DB\Adapter\AdapterInterface $connection;

    public function __construct(\Magento\Framework\App\ResourceConnection $resourceConnection)
    {
        $this->connection = $resourceConnection->getConnection();
    }

    public function save(array $prices): void
    {
        $this->connection->insertMultiple($this->connection->getTableName(self::TABLE_NAME), $prices);
    }

    public function getBatch(int $batchSize): array
    {
        $select = $this->connection->select()
            ->from($this->connection->getTableName(self::TABLE_NAME))
            ->order('id ASC')
            ->limit($batchSize);

        return $this->connection->fetchAll($select);
    }

    public function deleteUpToId(int $id): void
    {
        $this->connection->delete($this->connection->getTableName(self::TABLE_NAME), ['id <= ?' => $id]);
    }

    public function cleanTable(): void
    {
        $this->connection->truncateTable($this->connection->getTableName(self::TABLE_NAME));
    }
}

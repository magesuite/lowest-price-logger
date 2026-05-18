<?php

declare(strict_types=1);

namespace MageSuite\LowestPriceLogger\Model;

class TableMaintainer
{
    public const TEMPLATE_TABLE = 'price_history_log_website';

    public function __construct(
        protected \Magento\Framework\App\ResourceConnection $resourceConnection,
        protected \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
    }

    public function getTableNameForWebsite(int $websiteId): string
    {
        return $this->resourceConnection->getTableName(self::TEMPLATE_TABLE . '_' . $websiteId);
    }

    public function getTemplateTableName(): string
    {
        return $this->resourceConnection->getTableName(self::TEMPLATE_TABLE);
    }

    public function createTableForWebsite(int $websiteId): void
    {
        $this->createTable($this->getTemplateTableName(), $this->getTableNameForWebsite($websiteId));
    }

    public function dropTableForWebsite(int $websiteId): void
    {
        $this->resourceConnection->getConnection()->dropTable($this->getTableNameForWebsite($websiteId));
    }

    public function createTablesForAllWebsites(): void
    {
        foreach ($this->storeManager->getWebsites() as $website) {
            $this->createTableForWebsite((int) $website->getId());
        }
    }

    public function stripWebsiteId(array $prices): array
    {
        return array_map(fn(array $price) => array_diff_key($price, ['website_id' => null]), $prices);
    }

    protected function createTable(string $templateTable, string $targetTable): void
    {
        $connection = $this->resourceConnection->getConnection();
        if ($connection->isTableExists($targetTable)) {
            return;
        }

        $connection->createTable($connection->createTableByDdl($templateTable, $targetTable));
    }
}

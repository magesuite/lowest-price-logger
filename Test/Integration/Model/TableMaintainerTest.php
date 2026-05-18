<?php

declare(strict_types=1);

namespace MageSuite\LowestPriceLogger\Test\Integration\Model;

#[\Magento\TestFramework\Fixture\DbIsolation(false)]
#[\Magento\TestFramework\Fixture\AppIsolation(true)]
class TableMaintainerTest extends \PHPUnit\Framework\TestCase
{
    protected \Magento\Framework\App\ObjectManager $objectManager;
    protected \MageSuite\LowestPriceLogger\Model\TableMaintainer $tableMaintainer;
    protected \Magento\Framework\App\ResourceConnection $resourceConnection;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->tableMaintainer = $this->objectManager->get(\MageSuite\LowestPriceLogger\Model\TableMaintainer::class);
        $this->resourceConnection = $this->objectManager->get(\Magento\Framework\App\ResourceConnection::class);
    }

    protected function tearDown(): void
    {
        $connection = $this->resourceConnection->getConnection();
        foreach ([9997, 9998, 9999] as $websiteId) {
            $tableName = $this->tableMaintainer->getTableNameForWebsite($websiteId);
            if ($connection->isTableExists($tableName)) {
                $connection->dropTable($tableName);
            }
        }
    }

    public function testGetTableNameForWebsiteReturnsCorrectName(): void
    {
        $this->assertSame(
            'price_history_log_website_42',
            $this->tableMaintainer->getTableNameForWebsite(42)
        );
    }

    public function testCreateTableForWebsiteCreatesTableInDatabase(): void
    {
        $this->tableMaintainer->createTableForWebsite(9999);

        $this->assertTrue(
            $this->resourceConnection->getConnection()->isTableExists(
                $this->tableMaintainer->getTableNameForWebsite(9999)
            )
        );
    }

    public function testCreateTableIsIdempotent(): void
    {
        $this->tableMaintainer->createTableForWebsite(9998);
        $this->tableMaintainer->createTableForWebsite(9998);

        $this->assertTrue(
            $this->resourceConnection->getConnection()->isTableExists(
                $this->tableMaintainer->getTableNameForWebsite(9998)
            )
        );
    }

    public function testDropTableForWebsiteRemovesTable(): void
    {
        $this->tableMaintainer->createTableForWebsite(9997);
        $this->tableMaintainer->dropTableForWebsite(9997);

        $this->assertFalse(
            $this->resourceConnection->getConnection()->isTableExists(
                $this->tableMaintainer->getTableNameForWebsite(9997)
            )
        );
    }

    public function testStripWebsiteIdRemovesKeyFromAllPriceRecords(): void
    {
        $prices = [
            ['product_id' => 1, 'website_id' => 2, 'price' => 9.99, 'log_date' => '2024-01-01'],
            ['product_id' => 2, 'website_id' => 3, 'price' => 14.99, 'log_date' => '2024-01-01'],
        ];

        $result = $this->tableMaintainer->stripWebsiteId($prices);

        $this->assertCount(2, $result);
        foreach ($result as $row) {
            $this->assertArrayNotHasKey('website_id', $row);
            $this->assertArrayHasKey('product_id', $row);
            $this->assertArrayHasKey('price', $row);
        }
    }
}

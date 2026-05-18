<?php

declare(strict_types=1);

namespace MageSuite\LowestPriceLogger\Setup;

class Recurring implements \Magento\Framework\Setup\InstallSchemaInterface
{
    public function __construct(protected \MageSuite\LowestPriceLogger\Model\TableMaintainer $tableMaintainer)
    {
    }

    public function install(
        \Magento\Framework\Setup\SchemaSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    ): void {
        $this->tableMaintainer->createTablesForAllWebsites();
    }
}

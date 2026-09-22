<?php

declare(strict_types=1);

namespace MageSuite\LowestPriceLogger\Model\ResourceModel\PriceHistoryLog;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        protected \MageSuite\LowestPriceLogger\Model\TableMaintainer $tableMaintainer,
        ?\Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        ?\Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    protected function _construct(): void
    {
        $this->_init(
            \MageSuite\LowestPriceLogger\Model\PriceHistoryLog::class,
            \MageSuite\LowestPriceLogger\Model\ResourceModel\PriceHistoryLog::class
        );
    }

    public function setWebsiteId(int $websiteId): self
    {
        $this->_mainTable = $this->tableMaintainer->getTableNameForWebsite($websiteId);
        $this->getSelect()->reset(\Magento\Framework\DB\Select::FROM);
        $this->_initSelect();

        return $this;
    }
}

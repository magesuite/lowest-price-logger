<?php

namespace MageSuite\LowestPriceLogger\Observer;

class LogGatheredPrices implements \Magento\Framework\Event\ObserverInterface
{
    protected \MageSuite\LowestPriceLogger\Model\PriceStorage $priceStorage;
    protected \Magento\Framework\DB\Adapter\AdapterInterface $connection;
    protected \MageSuite\LowestPriceLogger\Model\FilterOutDuplicates $filterOutDuplicates;

    public function __construct(
        \MageSuite\LowestPriceLogger\Model\PriceStorage $priceStorage,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \MageSuite\LowestPriceLogger\Model\FilterOutDuplicates $filterOutDuplicates
    ) {
        $this->priceStorage = $priceStorage;
        $this->connection = $resourceConnection->getConnection();
        $this->filterOutDuplicates = $filterOutDuplicates;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (empty($this->priceStorage->getPrices())) {
            return;
        }

        $prices = $this->priceStorage->getPrices();
        $prices = $this->filterOutDuplicates->execute($prices);

        if (empty($prices)) {
            return;
        }

        $this->connection->insertOnDuplicate(
            $this->connection->getTableName('price_history_log'),
            $prices,
            []
        );
    }
}

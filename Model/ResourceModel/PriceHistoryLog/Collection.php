<?php

namespace MageSuite\LowestPriceLogger\Model\ResourceModel\PriceHistoryLog;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'log_id'; // phpcs:ignore

    protected function _construct()
    {
        $this->_init(
            \MageSuite\LowestPriceLogger\Model\PriceHistoryLog::class,
            \MageSuite\LowestPriceLogger\Model\ResourceModel\PriceHistoryLog::class
        );
    }
}

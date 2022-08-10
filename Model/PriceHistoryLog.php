<?php

namespace MageSuite\LowestPriceLogger\Model;

class PriceHistoryLog extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init(\MageSuite\LowestPriceLogger\Model\ResourceModel\PriceHistoryLog::class);
    }
}

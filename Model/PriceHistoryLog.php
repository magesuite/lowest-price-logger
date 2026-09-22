<?php

declare(strict_types=1);

namespace MageSuite\LowestPriceLogger\Model;

class PriceHistoryLog extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct(): void
    {
        $this->_init(\MageSuite\LowestPriceLogger\Model\ResourceModel\PriceHistoryLog::class);
    }
}

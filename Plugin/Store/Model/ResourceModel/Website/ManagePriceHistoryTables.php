<?php

declare(strict_types=1);

namespace MageSuite\LowestPriceLogger\Plugin\Store\Model\ResourceModel\Website;

class ManagePriceHistoryTables
{
    public function __construct(protected \MageSuite\LowestPriceLogger\Model\TableMaintainer $tableMaintainer)
    {
    }

    public function afterSave(
        \Magento\Store\Model\ResourceModel\Website $subject,
        \Magento\Store\Model\ResourceModel\Website $result,
        \Magento\Store\Model\Website $website
    ): \Magento\Store\Model\ResourceModel\Website {
        if ($website->isObjectNew()) {
            $this->tableMaintainer->createTableForWebsite((int) $website->getId());
        }

        return $result;
    }

    public function afterDelete(
        \Magento\Store\Model\ResourceModel\Website $subject,
        \Magento\Store\Model\ResourceModel\Website $result,
        \Magento\Store\Model\Website $website
    ): \Magento\Store\Model\ResourceModel\Website {
        $this->tableMaintainer->dropTableForWebsite((int) $website->getId());

        return $result;
    }
}

<?php

namespace MageSuite\LowestPriceLogger\Cron;

class LogsCleanup
{
    protected \MageSuite\LowestPriceLogger\Helper\Configuration $configuration;
    protected \MageSuite\LowestPriceLogger\Model\ResourceModel\PriceHistoryLog $priceHistoryLog;

    public function __construct(
        \MageSuite\LowestPriceLogger\Helper\Configuration $configuration,
        \MageSuite\LowestPriceLogger\Model\ResourceModel\PriceHistoryLog $priceHistoryLog
    ) {
        $this->configuration = $configuration;
        $this->priceHistoryLog = $priceHistoryLog;
    }

    public function execute()
    {
        if (!$this->configuration->isCleanupCronEnabled()) {
            return;
        }

        $logsRetentionPeriodInDays = $this->configuration->getLogsRetentionPeriodInDays();

        $this->priceHistoryLog->deleteOlderThan($logsRetentionPeriodInDays);
    }
}

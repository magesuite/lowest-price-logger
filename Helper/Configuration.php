<?php

declare(strict_types=1);

namespace MageSuite\LowestPriceLogger\Helper;

class Configuration
{
    public const BATCH_SIZE_XML_PATH = 'lowest_price_logger/processing/batch_size';
    public const CALCULATION_CRON_ENABLED_XML_PATH = 'lowest_price_logger/cron/enabled';
    public const CLEANUP_CRON_ENABLED_XML_PATH = 'lowest_price_logger/cleanup_cron/enabled';
    public const CLEANUP_CRON_RETENTION_PERIOD_IN_DAYS_PATH = 'lowest_price_logger/cleanup_cron/retention_period_in_days';
    public const ASYNC_LOGGING_ENABLED_XML_PATH = 'lowest_price_logger/general/async_logging_enabled';

    public function __construct(
        protected \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        protected int $batchSize = 1000
    ) {
    }

    public function getBatchSize(): int
    {
        $batchSize = (int)$this->scopeConfig->getValue(self::BATCH_SIZE_XML_PATH);

        return $batchSize > 0 ? $batchSize : $this->batchSize;
    }

    public function isCalculationCronEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::CALCULATION_CRON_ENABLED_XML_PATH);
    }

    public function isCleanupCronEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::CLEANUP_CRON_ENABLED_XML_PATH);
    }

    public function getLogsRetentionPeriodInDays(): int
    {
        return (int)$this->scopeConfig->getValue(self::CLEANUP_CRON_RETENTION_PERIOD_IN_DAYS_PATH);
    }

    public function isAsyncLoggingEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::ASYNC_LOGGING_ENABLED_XML_PATH);
    }
}

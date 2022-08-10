<?php

namespace MageSuite\LowestPriceLogger\Helper;

class Configuration
{
    public const BATCH_SIZE_XML_PATH = 'lowest_price_logger/processing/batch_size';
    public const CALCULATION_CRON_ENABLED_XML_PATH = 'lowest_price_logger/cron/enabled';
    public const CLEANUP_CRON_ENABLED_XML_PATH = 'lowest_price_logger/cleanup_cron/enabled';
    public const CLEANUP_CRON_RETENTION_PERIOD_IN_DAYS_PATH = 'lowest_price_logger/cleanup_cron/retention_period_in_days';

    protected \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function getBatchSize()
    {
        return $this->scopeConfig->getValue(self::BATCH_SIZE_XML_PATH);
    }

    public function isCalculationCronEnabled()
    {
        return $this->scopeConfig->getValue(self::CALCULATION_CRON_ENABLED_XML_PATH);
    }

    public function isCleanupCronEnabled()
    {
        return $this->scopeConfig->getValue(self::CLEANUP_CRON_ENABLED_XML_PATH);
    }

    public function getLogsRetentionPeriodInDays()
    {
        return $this->scopeConfig->getValue(self::CLEANUP_CRON_RETENTION_PERIOD_IN_DAYS_PATH);
    }
}

<?php

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \MageSuite\LowestPriceLogger\Model\ResourceModel\PriceHistoryLog $priceHistoryLog */
$priceHistoryLog = $objectManager->get(\MageSuite\LowestPriceLogger\Model\ResourceModel\PriceHistoryLog::class);
$priceHistoryLog->cleanTable();

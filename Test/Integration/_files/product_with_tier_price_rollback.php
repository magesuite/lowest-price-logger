<?php

include 'remove_all_products.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$configResource = $objectManager->get(\Magento\Config\Model\ResourceModel\Config::class);
$configResource->deleteConfig(\Magento\Catalog\Helper\Data::XML_PATH_PRICE_SCOPE, 'default', 0);
$observer = $objectManager->get(\Magento\Framework\Event\Observer::class);
$objectManager->get(\Magento\Catalog\Observer\SwitchPriceAttributeScopeOnConfigChange::class)->execute($observer);

$resolver = \Magento\TestFramework\Workaround\Override\Fixture\Resolver::getInstance();
$resolver->requireDataFixture('Magento/Store/_files/second_website_with_two_stores_rollback.php');

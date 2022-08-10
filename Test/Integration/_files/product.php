<?php

declare(strict_types=1);

$resolver = \Magento\TestFramework\Workaround\Override\Fixture\Resolver::getInstance();
$objectManager =  \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

include 'second_website_with_two_stores.php';

$configResource = $objectManager->get(\Magento\Config\Model\ResourceModel\Config::class);
$configResource->saveConfig(\Magento\Catalog\Helper\Data::XML_PATH_PRICE_SCOPE, \Magento\Store\Model\Store::PRICE_SCOPE_WEBSITE, 'default', 0);
$objectManager->get(\Magento\Framework\App\Config\ReinitableConfigInterface::class)->reinit();
/** @var \Magento\Framework\Event\Observer $observer */
$observer = $objectManager->get(\Magento\Framework\Event\Observer::class);
$objectManager->get(\Magento\Catalog\Observer\SwitchPriceAttributeScopeOnConfigChange::class)->execute($observer);
/** @var \Magento\Catalog\Model\ProductFactory $productFactory */
$productFactory = $objectManager->create(\Magento\Catalog\Model\ProductFactory::class);
/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
/** @var \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->create(\Magento\Store\Api\WebsiteRepositoryInterface::class);
$websiteId = $websiteRepository->get('test')->getId();
$defaultWebsiteId = $websiteRepository->get('base')->getId();
$storeManager = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
$secondStoreId = $storeManager->getStore('fixture_second_store')->getId();

$product = $productFactory->create();
$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setAttributeSetId($product->getDefaultAttributeSetId())
    ->setWebsiteIds([$defaultWebsiteId, $websiteId])
    ->setName('Simple Product on two websites')
    ->setSku('simple')
    ->setPrice(10)
    ->setDescription('Description')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1]);

$productRepository->save($product);

$product = $productRepository->get('simple', true, $secondStoreId, true);
$product->setPrice(8)
    ->setSpecialPrice(5.99);

$productRepository->save($product);

$product = $productFactory->create();
$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setAttributeSetId($product->getDefaultAttributeSetId())
    ->setWebsiteIds([$defaultWebsiteId, $websiteId])
    ->setName('Second product')
    ->setSku('simple_second')
    ->setPrice(12)
    ->setDescription('Description')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1]);

$productRepository->save($product);

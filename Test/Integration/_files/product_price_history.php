<?php

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
/** @var \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->create(\Magento\Store\Api\WebsiteRepositoryInterface::class);

$defaultWebsiteId = $websiteRepository->get('base')->getId();

$product = $productRepository->get('simple');
$secondProduct = $productRepository->get('simple_second');

/** @var \MageSuite\LowestPriceLogger\Model\ResourceModel\PriceHistoryLog $priceHistoryLog */
$priceHistoryLog = $objectManager->get(\MageSuite\LowestPriceLogger\Model\ResourceModel\PriceHistoryLog::class);

$priceHistoryLog->addPricesToLog([
    [
        'product_id' => $product->getId(),
        'price' => 10,
        'website_id' => $defaultWebsiteId,
        'customer_group_id' => 0,
        'log_date' => (new DateTime())->modify('-2 day')->format('Y-m-d'),
        'price_type' => 1
    ],
    [
        'product_id' => $product->getId(),
        'price' => 8,
        'website_id' => $defaultWebsiteId,
        'customer_group_id' => 0,
        'log_date' => (new DateTime())->modify('-3 day')->format('Y-m-d'),
        'price_type' => 1
    ],
    [
        'product_id' => $product->getId(),
        'price' => 6,
        'website_id' => $defaultWebsiteId,
        'customer_group_id' => 0,
        'log_date' => (new DateTime())->modify('-31 day')->format('Y-m-d'),
        'price_type' => 1
    ],
    [
        'product_id' => $secondProduct->getId(),
        'price' => 4,
        'website_id' => $defaultWebsiteId,
        'customer_group_id' => 0,
        'log_date' => (new DateTime())->modify('-2 day')->format('Y-m-d'),
        'price_type' => 1
    ],
]);

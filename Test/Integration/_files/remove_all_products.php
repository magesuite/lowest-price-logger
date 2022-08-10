<?php

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$registry = $objectManager->get(\Magento\Framework\Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$productRepository = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
$productCollectionFactory = $objectManager->get(\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class);
$productCollection = $productCollectionFactory->create();

foreach ($productCollection->getItems() as $product) {
    if (!$product->getId()) {
        continue;
    }

    $productRepository->delete($product);
}

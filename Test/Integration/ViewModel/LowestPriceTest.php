<?php

namespace MageSuite\LowestPriceLogger\Test\Integration\ViewModel;

class LowestPriceTest extends \PHPUnit\Framework\TestCase
{
    protected ?\Magento\Framework\App\ObjectManager $objectManager;
    protected ?\Magento\Catalog\Api\ProductRepositoryInterface $productRepository;
    protected ?\MageSuite\LowestPriceLogger\ViewModel\LowestPrice $viewModel;
    protected ?\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->productRepository = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->viewModel = $this->objectManager->create(\MageSuite\LowestPriceLogger\ViewModel\LowestPrice::class);
        $this->productCollectionFactory = $this->objectManager->create(\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/catalog/price/scope 1
     * @magentoDataFixture MageSuite_LowestPriceLogger::Test/Integration/_files/product.php
     * @magentoDataFixture MageSuite_LowestPriceLogger::Test/Integration/_files/product_price_history.php
     */
    public function testItReturnsFormattedLowestPriceForProductEntity()
    {
        $product = $this->productRepository->get('simple');

        $lowestPrice = $this->viewModel->getByProduct($product, true);

        $this->assertEquals('$8.00', $lowestPrice);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/catalog/price/scope 1
     * @magentoDataFixture MageSuite_LowestPriceLogger::Test/Integration/_files/product.php
     * @magentoDataFixture MageSuite_LowestPriceLogger::Test/Integration/_files/product_price_history.php
     */
    public function testItReturnsFormattedLowestPriceForProductFromCollection()
    {
        $collection = $this->productCollectionFactory->create()
            ->addAttributeToSelect('*');

        $products = $collection->getItems();

        foreach ($products as $product) {
            if ($product->getSku() != 'simple') {
                continue;
            }

            $lowestPrice = $this->viewModel->getByProduct($product, true);
            $this->assertEquals('$8.00', $lowestPrice);
        }
    }
}

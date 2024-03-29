<?php

declare(strict_types=1);

namespace MageSuite\LowestPriceLogger\Test\Integration;

class LogBundleProductPriceFromFrontendTest extends \Magento\TestFramework\TestCase\AbstractController
{
    protected ?\Magento\Framework\App\ObjectManager $objectManager;
    protected ?\MageSuite\LowestPriceLogger\Model\ResourceModel\PriceHistoryLog $priceHistoryLog;
    protected ?\Magento\Catalog\Api\ProductRepositoryInterface $productRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->priceHistoryLog = $this->objectManager->get(\MageSuite\LowestPriceLogger\Model\ResourceModel\PriceHistoryLog::class);
        $this->productRepository = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->priceHistoryLog->cleanTable();
    }

    /**
     * @return void
     */
    public function tearDown(): void
    {
        $this->priceHistoryLog->cleanTable();
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/PriceCalculator/fixed_bundle_product.php
     * @magentoAppArea frontend
     * @magentoDbIsolation disabled
     */
    public function testBundleWithFixedPrice(): void
    {
        $product = $this->productRepository->get('bundle_product');

        $this->dispatch(sprintf('catalog/product/view/id/%s/', $product->getId()));

        $priceHistory = $this->priceHistoryLog->getPriceHistory([$product->getId()], 1, 0);
        $this->assertEquals(110, $priceHistory[0]['price']);
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/bundle_product_dropdown_options.php
     * @magentoAppArea frontend
     * @magentoDbIsolation disabled
     */
    public function testBundleWithDynamicPrice(): void
    {
        $product = $this->productRepository->get('bundle-product-dropdown-options');
        $simpleProduct = $this->productRepository->get('simple-1');

        $indexerRegistry = $this->objectManager->create(\Magento\Framework\Indexer\IndexerRegistry::class);
        $indexerRegistry->get(\Magento\Catalog\Model\Indexer\Product\Price\Processor::INDEXER_ID)
            ->reindexList([$product->getId(), $simpleProduct->getId()]);

        $this->dispatch(sprintf('catalog/product/view/id/%s/', $product->getId()));

        $priceHistory = $this->priceHistoryLog->getPriceHistory([$product->getId()], 1, 0);
        $this->assertEquals(10, $priceHistory[0]['price']);

        $priceHistory = $this->priceHistoryLog->getPriceHistory([$simpleProduct->getId()], 1, 0);
        $this->assertEquals(10, $priceHistory[0]['price']);
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/bundle_product_dropdown_options.php
     * @magentoAppArea frontend
     * @magentoDbIsolation disabled
     */
    public function testBundleWithFixedPriceCron(): void
    {
        $product = $this->productRepository->get('bundle-product-dropdown-options');
        $product->setPriceType(\Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED);
        $this->productRepository->save($product);

        $generateLowestPriceForAllProductsFactory = $this->objectManager->create(
            \MageSuite\LowestPriceLogger\Model\GenerateLowestPriceForAllProductsFactory::class
        );

        $generateLowestPriceForAllProductsFactory
            ->create()
            ->execute();

        $priceHistory = $this->priceHistoryLog->getPriceHistory([$product->getId()], 1, 0);
        $this->assertEquals(10, $priceHistory[0]['price']);
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/bundle_product_dropdown_options.php
     * @magentoAppArea frontend
     * @magentoDbIsolation disabled
     */
    public function testBundleWithDynamicPriceCron(): void
    {
        $generateLowestPriceForAllProductsFactory = $this->objectManager->create(
            \MageSuite\LowestPriceLogger\Model\GenerateLowestPriceForAllProductsFactory::class
        );

        $generateLowestPriceForAllProductsFactory
            ->create()
            ->execute();

        $product = $this->productRepository->get('bundle-product-dropdown-options');
        $simpleProduct = $this->productRepository->get('simple-1');

        $priceHistory = $this->priceHistoryLog->getPriceHistory([$product->getId()], 1, 0);
        $this->assertEquals(10, $priceHistory[0]['price']);

        $priceHistory = $this->priceHistoryLog->getPriceHistory([$simpleProduct->getId()], 1, 0);
        $this->assertEquals(10, $priceHistory[0]['price']);
    }
}

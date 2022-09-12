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
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/PriceCalculator/fixed_bundle_product.php
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
     */
    public function testBundleWithDynamicPrice(): void
    {
        $product = $this->productRepository->get('bundle-product-dropdown-options');
        $simpleProduct = $this->productRepository->get('simple-1');

        $this->dispatch(sprintf('catalog/product/view/id/%s/', $product->getId()));

        $priceHistory = $this->priceHistoryLog->getPriceHistory([$product->getId()], 1, 0);
        $this->assertEquals(10, $priceHistory[0]['price']);

        $priceHistory = $this->priceHistoryLog->getPriceHistory([$simpleProduct->getId()], 1, 0);
        $this->assertEquals(10, $priceHistory[0]['price']);
    }
}

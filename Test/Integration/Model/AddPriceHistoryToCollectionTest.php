<?php

namespace MageSuite\LowestPriceLogger\Test\Integration\Model;

class AddPriceHistoryToCollectionTest extends \PHPUnit\Framework\TestCase
{
    protected ?\MageSuite\LowestPriceLogger\Model\GenerateLowestPriceForAllProducts $generateLowestPriceForAllProducts;
    protected ?\Magento\Framework\App\ObjectManager $objectManager;
    protected ?\MageSuite\LowestPriceLogger\Test\Integration\GetCurrentDateFake $getCurrentDateFake;
    protected ?\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory;
    protected ?\MageSuite\LowestPriceLogger\Model\AddPriceHistoryToCollection $addPriceHistoryToCollection;
    protected ?\Magento\Catalog\Api\ProductRepositoryInterface $productRepository;
    protected ?\Magento\Store\Model\StoreManagerInterface $storeManager;
    protected ?\MageSuite\LowestPriceLogger\Model\ResourceModel\PriceHistoryLog $priceHistoryLog;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->getCurrentDateFake = new \MageSuite\LowestPriceLogger\Test\Integration\GetCurrentDateFake();

        $this->generateLowestPriceForAllProducts = $this->objectManager->create(
            \MageSuite\LowestPriceLogger\Model\GenerateLowestPriceForAllProducts::class,
            ['getCurrentDate' => $this->getCurrentDateFake]
        );

        $this->productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->productCollectionFactory = $this->objectManager->create(\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class);
        $this->addPriceHistoryToCollection = $this->objectManager->create(\MageSuite\LowestPriceLogger\Model\AddPriceHistoryToCollection::class);
        $this->storeManager = $this->objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        $this->priceHistoryLog = $this->objectManager->get(\MageSuite\LowestPriceLogger\Model\ResourceModel\PriceHistoryLog::class);

        $this->priceHistoryLog->cleanTable();
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/catalog/price/scope 1
     * @magentoDataFixture MageSuite_LowestPriceLogger::Test/Integration/_files/product.php
     */
    public function testItAddsPriceHistoryToCollection()
    {
        $this->getCurrentDateFake->setValue('2022-08-02');
        $this->generateLowestPriceForAllProducts->execute();

        $productCollection = $this->productCollectionFactory->create();
        $productCollection->getItems();

        $this->storeManager->setCurrentStore($this->storeManager->getDefaultStoreView());
        $this->addPriceHistoryToCollection->execute($productCollection);

        $products = $productCollection->getItems();

        $simpleProduct = $this->productRepository->get('simple');
        $secondSimpleProduct = $this->productRepository->get('simple_second');

        $simplePriceHistory = $products[$simpleProduct->getId()]->getData('price_history');
        $secondSimplePriceHistory = $products[$simpleProduct->getId()]->getData('price_history');

        $this->assertCount(1, $simplePriceHistory);
        $this->assertEquals(10, $simplePriceHistory[0]['price']);
        $this->assertEquals('2022-08-02', $simplePriceHistory[0]['log_date']);
        $this->assertCount(1, $secondSimplePriceHistory);
        $this->assertEquals(10, $secondSimplePriceHistory[0]['price']);
        $this->assertEquals('2022-08-02', $secondSimplePriceHistory[0]['log_date']);
    }

    public function tearDown(): void
    {
        $this->priceHistoryLog->cleanTable();
    }
}

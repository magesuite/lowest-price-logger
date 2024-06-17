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

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/catalog/price/scope 1
     * @magentoConfigFixture current_store tax/calculation/algorithm UNIT_BASE_CALCULATION
     * @magentoConfigFixture current_store tax/display/type 2
     * @magentoConfigFixture current_store tax/defaults/country US
     * @magentoConfigFixture current_store tax/defaults/region 12
     * @magentoDataFixture Magento/Tax/_files/tax_classes.php
     * @magentoDataFixture Magento/Customer/_files/customer_group.php
     * @magentoDataFixture MageSuite_LowestPriceLogger::Test/Integration/_files/product.php
     * @magentoDataFixture MageSuite_LowestPriceLogger::Test/Integration/_files/product_price_history.php
     */
    public function testItReturnsFormattedLowestPriceWithTaxForProductFromCollection()
    {
        $collection = $this->productCollectionFactory->create()
            ->addAttributeToSelect('*');

        $customerClassModel = $this->objectManager->create(\Magento\Tax\Model\ClassModel::class);
        $customerClassModel->load('CustomerTaxClass1', 'class_name');

        $productClassModel = $this->objectManager->get(\Magento\Tax\Model\ClassModel::class);
        $productClassModel->load('ProductTaxClass1', 'class_name');

        $customerGroup = $this->objectManager->create(\Magento\Customer\Model\Group::class);
        $customerGroup->load('NOT LOGGED IN', 'customer_group_code');
        $taxClassId = $customerGroup->getTaxClassId();
        $customerGroup->setTaxClassId($customerClassModel->getId());
        $customerGroup->save();

        $products = $collection->getItems();

        foreach ($products as $product) {
            if ($product->getSku() != 'simple') {
                continue;
            }
            $product->setTaxClassId($productClassModel->getId());
            $lowestPrice = $this->viewModel->getByProduct($product, true);
            $this->assertEquals('$8.60', $lowestPrice);
        }

        $customerGroup->setTaxClassId($taxClassId);
        $customerGroup->save();
    }
}

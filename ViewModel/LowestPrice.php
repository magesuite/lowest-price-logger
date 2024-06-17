<?php

namespace MageSuite\LowestPriceLogger\ViewModel;

class LowestPrice implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    protected \Magento\Catalog\Helper\Data $catalogHelper;
    protected \Magento\Customer\Model\Session $customerSession;
    protected \Magento\Framework\Pricing\Helper\Data $pricingHelper;
    protected \Magento\Store\Model\StoreManagerInterface $storeManager;
    protected \Magento\Tax\Api\TaxCalculationInterface $taxCalculation;
    protected \Magento\Tax\Helper\Data $taxHelper;
    protected \Magento\Tax\Model\Config $taxConfig;
    protected \MageSuite\LowestPriceLogger\Model\AddPriceHistoryToCollection $addPriceHistoryToCollection;
    protected \MageSuite\LowestPriceLogger\Model\ResourceModel\PriceHistoryLog $priceHistoryLog;

    public function __construct(
        \MageSuite\LowestPriceLogger\Model\AddPriceHistoryToCollection $addPriceHistoryToCollection,
        \MageSuite\LowestPriceLogger\Model\ResourceModel\PriceHistoryLog $priceHistoryLog,
        \Magento\Catalog\Helper\Data $catalogHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Tax\Api\TaxCalculationInterface $taxCalculation,
        \Magento\Tax\Helper\Data $taxHelper
    ) {
        $this->addPriceHistoryToCollection = $addPriceHistoryToCollection;
        $this->catalogHelper = $catalogHelper;
        $this->customerSession = $customerSession;
        $this->priceHistoryLog = $priceHistoryLog;
        $this->pricingHelper = $pricingHelper;
        $this->storeManager = $storeManager;
        $this->taxCalculation = $taxCalculation;
        $this->taxHelper = $taxHelper;
    }

    public function getByProduct(\Magento\Catalog\Model\Product $product, bool $withCurrency = false): ?string
    {
        $store = $this->storeManager->getStore();

        if ($product->getData('origins_from_collection') !== null) {
            $this->addPriceHistoryToCollection->execute($product->getData('origins_from_collection'));
        }

        if ($product->hasData('price_history')) {
            $price = $this->getLowestPriceFromHistory($product->getData('price_history'));
        } else {
            $productId = $product->getId();

            $price = $this->priceHistoryLog->getLowestPrice(
                [$productId],
                $store->getWebsiteId(),
                $this->customerSession->getCustomerGroupId()
            );

            $price = $price['price'] ?? null;
        }

        if ($price === null) {
            return null;
        }

        $price = $this->calculatePriceWithTax($product, (float) $price);

        if ($withCurrency) {
            $price = $this->pricingHelper->currencyByStore($price, $store, true, false);
        }

        return $price;
    }

    public function calculatePriceWithTax(\Magento\Catalog\Api\Data\ProductInterface $product, float $value): float
    {
        $customerId = $this->customerSession->getCustomerId();

        $taxRate = $this->taxCalculation->getCalculatedRate($product->getTaxClassId(), $customerId);

        if (empty($taxRate) || !$this->taxHelper->displayPriceIncludingTax()) {
            return $value;
        }

        return $this->catalogHelper->getTaxPrice($product, $value, true);
    }

    protected function getLowestPriceFromHistory($priceHistory): ?float
    {
        if (empty($priceHistory)) {
            return null;
        }

        $min = null;

        foreach ($priceHistory as $price) {
            if ($min === null || $price['price'] < $min) {
                $min = (float)$price['price'];
            }
        }

        return $min;
    }

    public function hasSpecialPrice($product)
    {
        $displayRegularPrice = $product->getPriceInfo()->getPrice(\Magento\Catalog\Pricing\Price\RegularPrice::PRICE_CODE)->getAmount()->getValue();
        $displayFinalPrice = $product->getPriceInfo()->getPrice(\Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE)->getAmount()->getValue();
        return $displayFinalPrice < $displayRegularPrice;
    }
}

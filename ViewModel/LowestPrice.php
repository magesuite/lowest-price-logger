<?php

namespace MageSuite\LowestPriceLogger\ViewModel;

class LowestPrice implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    protected \MageSuite\LowestPriceLogger\Model\ResourceModel\PriceHistoryLog $priceHistoryLog;
    protected \Magento\Store\Model\StoreManagerInterface $storeManager;
    protected \Magento\Customer\Model\Session $customerSession;
    protected \Magento\Framework\Pricing\Helper\Data $pricingHelper;
    protected \MageSuite\LowestPriceLogger\Model\AddPriceHistoryToCollection $addPriceHistoryToCollection;

    public function __construct(
        \MageSuite\LowestPriceLogger\Model\ResourceModel\PriceHistoryLog $priceHistoryLog,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \MageSuite\LowestPriceLogger\Model\AddPriceHistoryToCollection $addPriceHistoryToCollection
    ) {
        $this->priceHistoryLog = $priceHistoryLog;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->pricingHelper = $pricingHelper;
        $this->addPriceHistoryToCollection = $addPriceHistoryToCollection;
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

        if ($withCurrency) {
            $price = $this->pricingHelper->currencyByStore($price, $store, true, false);
        }

        return $price;
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

<?php

namespace MageSuite\LowestPriceLogger\ViewModel;

class LowestPrice implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    protected \MageSuite\LowestPriceLogger\Model\ResourceModel\PriceHistoryLog $priceHistoryLog;
    protected \Magento\Store\Model\StoreManagerInterface $storeManager;
    protected \Magento\Customer\Model\Session $customerSession;
    protected \Magento\Framework\Pricing\Helper\Data $pricingHelper;

    public function __construct(
        \MageSuite\LowestPriceLogger\Model\ResourceModel\PriceHistoryLog $priceHistoryLog,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper
    ) {
        $this->priceHistoryLog = $priceHistoryLog;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->pricingHelper = $pricingHelper;
    }

    public function getByProduct(\Magento\Catalog\Model\Product $product, bool $withCurrency = false): ?string
    {
        $store = $this->storeManager->getStore();

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
}

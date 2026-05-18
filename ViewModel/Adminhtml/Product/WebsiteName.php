<?php

declare(strict_types=1);

namespace MageSuite\LowestPriceLogger\ViewModel\Adminhtml\Product;

class WebsiteName implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    public function __construct(
        protected \Magento\Framework\App\RequestInterface $request,
        protected \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
    }

    public function getWebsiteName(): string
    {
        $storeId = (int) $this->request->getParam('store', \Magento\Store\Model\Store::DEFAULT_STORE_ID);

        try {
            if ($storeId) {
                return $this->storeManager->getStore($storeId)->getWebsite()->getName();
            }

            return $this->storeManager->getDefaultStoreView()->getWebsite()->getName();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return '';
        }
    }
}

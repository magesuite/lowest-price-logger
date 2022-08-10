<?php

namespace MageSuite\LowestPriceLogger\Model;

class AddPriceHistoryToCollection
{
    protected ResourceModel\PriceHistoryLog $priceHistoryLog;
    protected \Magento\Customer\Model\Session $customerSession;
    protected \Magento\Store\Model\StoreManagerInterface $storeManager;

    public function __construct(
        \MageSuite\LowestPriceLogger\Model\ResourceModel\PriceHistoryLog $priceHistoryLog,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->priceHistoryLog = $priceHistoryLog;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
    }

    public function execute($collection)
    {
        if ($collection->hasFlag('price_history_loaded')) {
            return $collection;
        }

        $collection->setFlag('price_history_loaded');

        if ($collection->getSize() === 0) {
            return $collection;
        }

        $productIds = [];

        foreach ($collection->getItems() as $item) {
            $productsById[$item->getData('entity_id')] = $item;
            $productIds[] = $item->getData('entity_id');
        }

        $priceHistory = $this->priceHistoryLog->getPriceHistory(
            $productIds,
            $this->storeManager->getStore()->getWebsiteId(),
            $this->customerSession->getCustomerGroupId()
        );

        if (empty($priceHistory)) {
            return $collection;
        }

        foreach ($priceHistory as $priceLogData) {
            $productId = (int)$priceLogData['product_id'];

            if (!isset($productsById[$productId])) {
                continue;
            }

            $productPriceHistory = $productsById[$productId]->getData('price_history') ?? [];
            $productPriceHistory[] = $priceLogData;

            $productsById[$productId]->setData('price_history', $productPriceHistory);
        }

        return $collection;
    }
}

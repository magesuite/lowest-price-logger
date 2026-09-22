<?php

declare(strict_types=1);

namespace MageSuite\LowestPriceLogger\Ui\Component\Listing\PriceHistoryLog;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    //@codingStandardsIgnoreStart
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \MageSuite\LowestPriceLogger\Model\ResourceModel\PriceHistoryLog\CollectionFactory $collectionFactory,
        protected \Magento\Framework\App\RequestInterface $request,
        protected \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }
    //@codingStandardsIgnoreEnd

    public function getData(): array
    {
        $this->collection->setWebsiteId($this->resolveWebsiteId());

        if ($this->request->getParam('current_product_id')) {
            $this->collection->addFieldToFilter('product_id', (int) $this->request->getParam('current_product_id'));
        }

        return parent::getData();
    }

    protected function resolveWebsiteId(): int
    {
        $storeId = $this->request->getParam('current_store_id');
        if ($storeId) {
            return (int)$this->storeManager->getStore((int)$storeId)->getWebsiteId();
        }

        return (int)$this->storeManager->getDefaultStoreView()->getWebsiteId();
    }
}

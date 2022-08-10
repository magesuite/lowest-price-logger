<?php

namespace MageSuite\LowestPriceLogger\Ui\Component\Listing\PriceHistoryLog;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;
    protected \Magento\Store\Model\StoreManagerInterface $storeManager;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \MageSuite\LowestPriceLogger\Model\ResourceModel\PriceHistoryLog\CollectionFactory $priceHistoryLogCollection,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $priceHistoryLogCollection->create();
        $this->request = $request;

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->storeManager = $storeManager;
    }

    public function getData()
    {
        if ($this->request->getParam('current_product_id')) {
            $this->collection->addFieldToFilter('product_id', $this->request->getParam('current_product_id'));
        }

        if ($this->request->getParam('current_store_id')) {
            $storeId = $this->request->getParam('current_store_id');
            $store = $this->storeManager->getStore($storeId);
            $websiteId = $store->getWebsiteId();

            $this->collection->addFieldToFilter('website_id', $websiteId);
        }

        return parent::getData();
    }
}

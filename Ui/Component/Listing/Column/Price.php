<?php

namespace MageSuite\LowestPriceLogger\Ui\Component\Listing\Column;

class Price extends \Magento\Ui\Component\Listing\Columns\Column
{
    protected \Magento\Framework\Pricing\Helper\Data $pricingHelper;
    protected \Magento\Store\Model\WebsiteRepository $websiteRepository;

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Store\Model\WebsiteRepository $websiteRepository,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        $this->pricingHelper = $pricingHelper;
        $this->websiteRepository = $websiteRepository;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        $fieldName = $this->getData('name');

        foreach ($dataSource['data']['items'] as & $item) {
            $websiteId = $item['website_id'];
            $website = $this->websiteRepository->getById($websiteId);
            $stores = $website->getStores();

            if (empty($stores)) {
                continue;
            }

            $store = current($stores);

            $price = $this->pricingHelper->currencyByStore($item[$fieldName], $store, true, false);
            $item[$fieldName] = $price;
        }

        return $dataSource;
    }
}

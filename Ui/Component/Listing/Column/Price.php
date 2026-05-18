<?php

declare(strict_types=1);

namespace MageSuite\LowestPriceLogger\Ui\Component\Listing\Column;

class Price extends \Magento\Ui\Component\Listing\Columns\Column
{
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        protected \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        protected \Magento\Store\Model\StoreManagerInterface $storeManager,
        protected \Magento\Framework\App\RequestInterface $request,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        $store = $this->storeManager->getStore(
            (int)$this->request->getParam('current_store_id', \Magento\Store\Model\Store::DEFAULT_STORE_ID)
        );

        $fieldName = $this->getData('name');
        foreach ($dataSource['data']['items'] as & $item) {
            if (!isset($item[$fieldName])) {
                continue;
            }

            $item[$fieldName] = $this->pricingHelper->currencyByStore(
                $item[$fieldName],
                $store,
                true,
                false
            );
        }

        return $dataSource;
    }
}

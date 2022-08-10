<?php

namespace MageSuite\LowestPriceLogger\Ui\Component\Listing\Column;

class Website extends \Magento\Ui\Component\Listing\Columns\Column
{
    protected \Magento\Store\Model\WebsiteRepository $websiteRepository;

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Magento\Store\Model\WebsiteRepository $websiteRepository,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        $this->websiteRepository = $websiteRepository;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        $fieldName = $this->getData('name');

        foreach ($dataSource['data']['items'] as & $item) {
            $websiteId = $item[$fieldName];
            $website = $this->websiteRepository->getById($websiteId);

            $item[$fieldName] = $website->getName();
        }

        return $dataSource;
    }
}

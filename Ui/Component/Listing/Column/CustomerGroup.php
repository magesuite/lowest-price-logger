<?php

namespace MageSuite\LowestPriceLogger\Ui\Component\Listing\Column;

class CustomerGroup extends \Magento\Ui\Component\Listing\Columns\Column
{
    protected \Magento\Customer\Api\GroupRepositoryInterface $groupRepository;

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        $this->groupRepository = $groupRepository;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        $fieldName = $this->getData('name');

        foreach ($dataSource['data']['items'] as & $item) {
            $customerGroupId = $item[$fieldName];
            $customerGroup = $this->groupRepository->getById($customerGroupId);

            $item[$fieldName] = $customerGroup->getCode();
        }

        return $dataSource;
    }
}

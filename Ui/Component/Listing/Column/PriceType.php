<?php

namespace MageSuite\LowestPriceLogger\Ui\Component\Listing\Column;

class PriceType extends \Magento\Ui\Component\Listing\Columns\Column
{
    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        $fieldName = $this->getData('name');

        foreach ($dataSource['data']['items'] as & $item) {
            $priceType = $this->getPriceTypeLabel($item[$fieldName]);

            $item[$fieldName] = $priceType;
        }

        return $dataSource;
    }

    protected function getPriceTypeLabel($item): \Magento\Framework\Phrase
    {
        return
            $item == \MageSuite\LowestPriceLogger\Plugin\Framework\Pricing\Price\PriceInterface\LogPrice::PRICE_TYPE_FINAL
            ? __('Final price') : __('Regular price');
    }
}

<?php

namespace MageSuite\LowestPriceLogger\Plugin\Catalog\Model\ResourceModel\Product\Collection;

class AddPriceHistoryToLoadedItems
{
    protected \MageSuite\LowestPriceLogger\Model\AddPriceHistoryToCollection $addPriceHistoryToCollection;

    public function __construct(\MageSuite\LowestPriceLogger\Model\AddPriceHistoryToCollection $addPriceHistoryToCollection)
    {
        $this->addPriceHistoryToCollection = $addPriceHistoryToCollection;
    }

    public function afterLoad(\Magento\Catalog\Model\ResourceModel\Product\Collection $subject)
    {
        $this->addPriceHistoryToCollection->execute($subject);

        return $subject;
    }
}

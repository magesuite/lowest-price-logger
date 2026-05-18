<?php

declare(strict_types=1);

namespace MageSuite\LowestPriceLogger\Service;

class LogGatheredPrices
{
    public function __construct(
        protected \MageSuite\LowestPriceLogger\Helper\Configuration $configuration,
        protected \MageSuite\LowestPriceLogger\Model\FilterOutDuplicates $filterOutDuplicates,
        protected \Magento\Framework\App\ResourceConnection $resourceConnection,
        protected \MageSuite\LowestPriceLogger\Model\TableMaintainer $tableMaintainer
    ) {
    }

    public function execute(array $prices): void
    {
        $prices = $this->filterOutDuplicates->execute($prices);
        if (empty($prices)) {
            return;
        }

        $connection = $this->resourceConnection->getConnection();

        foreach ($this->groupByWebsite($prices) as $websiteId => $websitePrices) {
            $connection->insertOnDuplicate(
                $this->tableMaintainer->getTableNameForWebsite($websiteId),
                $this->tableMaintainer->stripWebsiteId($websitePrices),
                []
            );
        }
    }

    protected function groupByWebsite(array $prices): array
    {
        $grouped = [];
        foreach ($prices as $price) {
            $grouped[(int) $price['website_id']][] = $price;
        }

        return $grouped;
    }
}

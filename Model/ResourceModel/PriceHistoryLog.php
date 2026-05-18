<?php

declare(strict_types=1);

namespace MageSuite\LowestPriceLogger\Model\ResourceModel;

class PriceHistoryLog extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        protected \MageSuite\LowestPriceLogger\Model\TableMaintainer $tableMaintainer,
        protected \Magento\Store\Model\StoreManagerInterface $storeManager,
        protected \MageSuite\LowestPriceLogger\Model\GetCurrentDate $getCurrentDate,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
    }

    protected function _construct(): void
    {
        $this->_init('price_history_log_website', 'log_id');
    }

    public function getPriceHistory(array $productIds, int $websiteId, ?int $customerGroupId = null): array
    {
        $select = $this->getConnection()->select()
            ->from($this->tableMaintainer->getTableNameForWebsite($websiteId))
            ->where('product_id IN(?)', $productIds)
            ->where(new \Zend_Db_Expr(sprintf('`log_date` > "%s"-INTERVAL 30 day', $this->getCurrentDate->execute())))
            ->order('price DESC');

        if ($customerGroupId !== null) {
            $select->where('customer_group_id = ?', $customerGroupId);
        }

        return $this->getConnection()->fetchAll($select);
    }

    public function getLowestPrices(array $productIds, int $websiteId, int $customerGroupId): array
    {
        $select = $this->getConnection()->select()
            ->from($this->tableMaintainer->getTableNameForWebsite($websiteId))
            ->where('product_id IN(?)', $productIds)
            ->where('customer_group_id = ?', $customerGroupId)
            ->where(new \Zend_Db_Expr('`log_date` > NOW()-INTERVAL 30 day'))
            ->order('price ASC')
            ->group('product_id');

        return $this->getConnection()->fetchAll($select);
    }

    public function getLowestPrice(array $productIds, int $websiteId, int $customerGroupId): array
    {
        $select = $this->getConnection()->select()
            ->from($this->tableMaintainer->getTableNameForWebsite($websiteId))
            ->where('product_id IN(?)', $productIds)
            ->where('customer_group_id = ?', $customerGroupId)
            ->where(new \Zend_Db_Expr('`log_date` > NOW()-INTERVAL 30 day'))
            ->order('price ASC')
            ->limit(1);

        $price = $this->getConnection()->fetchRow($select);

        return is_array($price) ? $price : [];
    }

    public function getLastPricesPerProduct(
        array $productIds,
        ?int $websiteId = null,
        ?int $customerGroupId = null
    ): array {
        $websiteIds = $websiteId !== null
            ? [$websiteId]
            : array_map(fn($website) => (int) $website->getId(), $this->storeManager->getWebsites());

        $pricesToCompare = [];

        foreach ($websiteIds as $wId) {
            $select = $this->getConnection()->select()
                ->from($this->tableMaintainer->getTableNameForWebsite($wId))
                ->where('product_id IN(?)', $productIds)
                ->group(['product_id', 'customer_group_id', 'price_type'])
                ->order(['log_date DESC', 'log_id DESC']);

            if ($customerGroupId !== null) {
                $select->where('customer_group_id = ?', $customerGroupId);
            }

            foreach ($this->getConnection()->fetchAll($select) as $price) {
                $pricesToCompare[$price['product_id']][$price['customer_group_id']][$wId][$price['price_type']] = $price['price'];
            }
        }

        return $pricesToCompare;
    }

    public function deleteOlderThan(int $retentionPeriodInDays): void
    {
        if ($retentionPeriodInDays <= 0) {
            return;
        }

        foreach ($this->storeManager->getWebsites() as $website) {
            $this->getConnection()->delete(
                $this->tableMaintainer->getTableNameForWebsite((int) $website->getId()),
                'log_date < date_sub(CURDATE(), INTERVAL ' . $retentionPeriodInDays . ' DAY)'
            );
        }
    }

    public function addPricesToLog(array $prices): void
    {
        $grouped = [];
        foreach ($prices as $price) {
            $grouped[(int) $price['website_id']][] = array_diff_key($price, ['website_id' => null]);
        }

        foreach ($grouped as $wId => $websitePrices) {
            $this->getConnection()->insertMultiple(
                $this->tableMaintainer->getTableNameForWebsite($wId),
                $websitePrices
            );
        }
    }

    public function cleanTable(): void
    {
        foreach ($this->storeManager->getWebsites() as $website) {
            $this->getConnection()->truncateTable(
                $this->tableMaintainer->getTableNameForWebsite((int) $website->getId())
            );
        }
    }

}

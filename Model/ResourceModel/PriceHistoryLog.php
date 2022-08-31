<?php

namespace MageSuite\LowestPriceLogger\Model\ResourceModel;

class PriceHistoryLog extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected \MageSuite\LowestPriceLogger\Model\GetCurrentDate $getCurrentDate;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \MageSuite\LowestPriceLogger\Model\GetCurrentDate $getCurrentDate,
        $connectionName = null
    )
    {
        parent::__construct($context, $connectionName);

        $this->getCurrentDate = $getCurrentDate;
    }

    protected function _construct()
    {
        $this->_init('price_history_log', 'log_id');
    }

    public function getPriceHistory(array $productIds, int $websiteId = null, int $customerGroupId = null)
    {
        $select = $this->getConnection()->select();
        $select->from($this->getTableName());

        $select->where('product_id IN(?)', $productIds);

        if ($websiteId !== null) {
            $select->where('website_id = ?', $websiteId);
        }

        if ($customerGroupId !== null) {
            $select->where('customer_group_id = ?', $customerGroupId);
        }

        $select->where(new \Zend_Db_Expr(sprintf('`log_date` > "%s"-INTERVAL 30 day', $this->getCurrentDate->execute())));
        $select->order('price DESC');

        return $this->getConnection()->fetchAll($select);
    }

    public function getLowestPrice(array $productIds, int $websiteId, int $customerGroupId)
    {
        $select = $this->getConnection()->select();
        $select->from($this->getTableName());

        $select->where('product_id IN(?)', $productIds);
        $select->where('website_id = ?', $websiteId);
        $select->where('customer_group_id = ?', $customerGroupId);
        $select->where(new \Zend_Db_Expr('`log_date` > NOW()-INTERVAL 30 day'));

        $select->order('price ASC');
        $select->limit(1);

        return $this->getConnection()->fetchRow($select);
    }

    public function getLastPricesPerProduct(array $productIds, int $websiteId = null, int $customerGroupId = null)
    {
        $select = $this->getConnection()->select();
        $select->from($this->getTableName());

        if ($websiteId !== null) {
            $select->where('website_id = ?', $websiteId);
        }

        if ($customerGroupId !== null) {
            $select->where('customer_group_id = ?', $customerGroupId);
        }

        $select->where('product_id IN(?)', $productIds);
        $select->group(['product_id', 'customer_group_id', 'website_id', 'price_type']);
        $select->order(['log_date DESC', 'log_id DESC']);

        $pricesToCompare = [];

        foreach ($this->getConnection()->fetchAll($select) as $price) {
            $pricesToCompare[$price['product_id']][$price['customer_group_id']][$price['website_id']][$price['price_type']] = $price['price'];
        }

        return $pricesToCompare;
    }

    public function deleteOlderThan($retentionPeriodInDays)
    {
        $retentionPeriodInDays = (int)$retentionPeriodInDays;

        if ($retentionPeriodInDays <= 0) {
            return;
        }

        $this->getConnection()->delete(
            $this->getTableName(),
            "log_date < date_sub(CURDATE(), INTERVAL " . $retentionPeriodInDays . " Day)"
        );
    }

    public function addPricesToLog($prices)
    {
        return $this->getConnection()->insertMultiple(
            $this->getTableName(),
            $prices
        );
    }

    public function cleanTable()
    {
        return $this->getConnection()->truncateTable($this->getTableName());
    }

    protected function getTableName(): string
    {
        return $this->getConnection()->getTableName('price_history_log');
    }
}

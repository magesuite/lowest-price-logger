<?php

namespace MageSuite\LowestPriceLogger\Model;

class AddCatalogRulePricesToCollection
{
    protected \Magento\Customer\Model\Session $customerSession;
    protected \Magento\Store\Model\StoreManagerInterface $storeManager;
    protected \Magento\Framework\DB\Adapter\AdapterInterface $connection;
    protected \Magento\Framework\Stdlib\DateTime $dateTime;
    protected \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
    ) {
        $this->connection = $resourceConnection->getConnection();
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->dateTime = $dateTime;
        $this->localeDate = $localeDate;
    }

    public function execute($products, $websiteId, $customerGroupId)
    {
        $productIds = [];

        foreach ($products as $item) {
            $productsById[$item->getData('entity_id')] = $item;
            $productIds[] = $item->getData('entity_id');
        }

        $select = $this->connection->select();
        $select->from(['catalog_rule' => $this->connection->getTableName('catalogrule_product_price')]);
        $select->where('product_id IN(?)', $productIds);
        $select->where('website_id = ?', $websiteId);
        $select->where('customer_group_id = ?', $customerGroupId);

        $date = date('Y-m-d');

        $select->where('catalog_rule.rule_date = ?', $date);
        $catalogRulePrices = $this->connection->fetchAll($select);
        $pricePerProduct = [];
        
        if (!empty($catalogRulePrices)) {
            foreach ($catalogRulePrices as $price) {
                $pricePerProduct[$price['product_id']] = $price['rule_price'];
            }
        }

        foreach ($products as $product) {
            $product->setData('catalog_rule_price', $pricePerProduct[$product->getData('entity_id')] ?? false);
        }
    }
}

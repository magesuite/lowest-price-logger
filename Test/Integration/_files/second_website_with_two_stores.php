<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$website = $objectManager->create(\Magento\Store\Model\Website::class);
/** @var $website \Magento\Store\Model\Website */
if (!$website->load('test', 'code')->getId()) {
    $website->setData(['code' => 'test', 'name' => 'Test Website', 'default_group_id' => '1', 'is_default' => '0']);
    $website->save();
}
$websiteId = $website->getId();

$storeGroup = $objectManager->create(\Magento\Store\Model\Group::class);

if (!$storeGroup->load('Test Website Store Group', 'name')->getId()) {
    $storeGroup->setWebsiteId($website->getId());
    $storeGroup->setName('Test Website Store Group');
    $storeGroup->setCode('test_website_store_group');
    $storeGroup->setRootCategoryId(2);
    $storeGroup->save();
}

$store = $objectManager->create(\Magento\Store\Model\Store::class);
if (!$store->load('fixture_second_store', 'code')->getId()) {
    $groupId = $storeGroup->getId();
    $store
        ->setCode('fixture_second_store')->setWebsiteId(
            $websiteId
        )->setGroupId(
            $groupId
        )->setName(
            'Fixture Second Store'
        )->setSortOrder(
            10
        )->setIsActive(
            1
        );
    $store->save();

    $storeGroup->setDefaultStoreId($store->getId());
    $storeGroup->save();
}

$store = $objectManager->create(\Magento\Store\Model\Store::class);
if (!$store->load('fixture_third_store', 'code')->getId()) {
    $groupId = $objectManager->get(
        \Magento\Store\Model\StoreManagerInterface::class
    )->getWebsite()->getDefaultGroupId();
    $store->setCode(
        'fixture_third_store'
    )->setWebsiteId(
        $websiteId
    )->setGroupId(
        $groupId
    )->setName(
        'Fixture Third Store'
    )->setSortOrder(
        11
    )->setIsActive(
        1
    );
    $store->save();
}

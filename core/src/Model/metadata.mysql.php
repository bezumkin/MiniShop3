<?php

$xpdo_meta_map = [
    'version' => '3.0',
    'namespace' => 'ModxPro\MiniShop3\\Model',
    'namespacePrefix' => 'MiniShop3',
    'class_map' =>
        [
            'MODX\\Revolution\\modResource' =>
                [
                    'ModxPro\\MiniShop3\\Model\\msCategory',
                    'ModxPro\\MiniShop3\\Model\\msProduct',
                ],
            'xPDO\\Om\\xPDOSimpleObject' =>
                [
                    'ModxPro\\MiniShop3\\Model\\msProductData',
                    'ModxPro\\MiniShop3\\Model\\msVendor',
                    'ModxPro\\MiniShop3\\Model\\msProductFile',
                    'ModxPro\\MiniShop3\\Model\\msDelivery',
                    'ModxPro\\MiniShop3\\Model\\msPayment',
                    'ModxPro\\MiniShop3\\Model\\msOrder',
                    'ModxPro\\MiniShop3\\Model\\msOrderStatus',
                    'ModxPro\\MiniShop3\\Model\\msOrderLog',
                    'ModxPro\\MiniShop3\\Model\\msOrderAddress',
                    'ModxPro\\MiniShop3\\Model\\msOrderProduct',
                    'ModxPro\\MiniShop3\\Model\\msLink',
                    'ModxPro\\MiniShop3\\Model\\msCustomer',
                    'ModxPro\\MiniShop3\\Model\\msCustomerProfile',
                    'ModxPro\\MiniShop3\\Model\\msOption',
                ],
            'xPDO\\Om\\xPDOObject' =>
                [
                    'ModxPro\\MiniShop3\\Model\\msCategoryMember',
                    'ModxPro\\MiniShop3\\Model\\msProductOption',
                    'ModxPro\\MiniShop3\\Model\\msDeliveryMember',
                    'ModxPro\\MiniShop3\\Model\\msProductLink',
                    'ModxPro\\MiniShop3\\Model\\msCategoryOption',
                ],
        ],
];

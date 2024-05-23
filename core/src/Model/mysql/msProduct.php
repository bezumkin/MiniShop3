<?php

namespace ModxPro\MiniShop3\Model\mysql;

class msProduct extends \ModxPro\MiniShop3\Model\msProduct
{
    public static $metaMap = [
        'package' => 'ModxPro\MiniShop3\\Model',
        'version' => '3.0',
        'extends' => 'MODX\\Revolution\\modResource',
        'tableMeta' =>
            [
                'engine' => 'InnoDB',
            ],
        'fields' =>
            [
                'class_key' => 'ModxPro\\MiniShop3\\Model\\msProduct',
            ],
        'fieldMeta' =>
            [
                'class_key' =>
                    [
                        'dbtype' => 'varchar',
                        'precision' => '100',
                        'phptype' => 'string',
                        'null' => false,
                        'default' => 'ModxPro\\MiniShop3\\Model\\msProduct',
                    ],
            ],
        'composites' =>
            [
                'Data' =>
                    [
                        'class' => 'ModxPro\\MiniShop3\\Model\\msProductData',
                        'local' => 'id',
                        'foreign' => 'id',
                        'cardinality' => 'one',
                        'owner' => 'local',
                    ],
                'Categories' =>
                    [
                        'class' => 'ModxPro\\MiniShop3\\Model\\msCategoryMember',
                        'local' => 'id',
                        'foreign' => 'product_id',
                        'cardinality' => 'many',
                        'owner' => 'local',
                    ],
                'Options' =>
                    [
                        'class' => 'ModxPro\\MiniShop3\\Model\\msProductOption',
                        'local' => 'id',
                        'foreign' => 'product_id',
                        'cardinality' => 'many',
                        'owner' => 'local',
                    ],
            ],
        'aggregates' =>
            [
                'Category' =>
                    [
                        'class' => 'ModxPro\\MiniShop3\\Model\\msCategory',
                        'local' => 'parent',
                        'foreign' => 'id',
                        'cardinality' => 'one',
                        'owner' => 'foreign',
                    ],
            ],
    ];

}

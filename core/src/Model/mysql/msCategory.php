<?php

namespace ModxPro\MiniShop3\Model\mysql;

class msCategory extends \ModxPro\MiniShop3\Model\msCategory
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
                'class_key' => 'ModxPro\\MiniShop3\\Model\\msCategory',
            ],
        'fieldMeta' =>
            [
                'class_key' =>
                    [
                        'dbtype' => 'varchar',
                        'precision' => '100',
                        'phptype' => 'string',
                        'null' => false,
                        'default' => 'ModxPro\\MiniShop3\\Model\\msCategory',
                    ],
            ],
        'composites' =>
            [
                'OwnProducts' =>
                    [
                        'class' => 'ModxPro\\MiniShop3\\Model\\msProduct',
                        'local' => 'id',
                        'foreign' => 'parent',
                        'cardinality' => 'many',
                        'owner' => 'local',
                    ],
                'AlienProducts' =>
                    [
                        'class' => 'ModxPro\\MiniShop3\\Model\\msCategoryMember',
                        'local' => 'id',
                        'foreign' => 'category_id',
                        'cardinality' => 'many',
                        'owner' => 'local',
                    ],
                'CategoryOptions' =>
                    [
                        'class' => 'ModxPro\\MiniShop3\\Model\\msCategoryOption',
                        'local' => 'id',
                        'foreign' => 'category_id',
                        'cardinality' => 'many',
                        'owner' => 'local',
                    ],
            ],
    ];
}

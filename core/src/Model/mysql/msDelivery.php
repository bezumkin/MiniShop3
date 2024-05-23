<?php

namespace ModxPro\MiniShop3\Model\mysql;

class msDelivery extends \ModxPro\MiniShop3\Model\msDelivery
{
    public static $metaMap = [
        'package' => 'ModxPro\MiniShop3\\Model',
        'version' => '3.0',
        'table' => 'ms3_deliveries',
        'extends' => 'xPDO\\Om\\xPDOSimpleObject',
        'tableMeta' =>
            [
                'engine' => 'InnoDB',
            ],
        'fields' =>
            [
                'name' => null,
                'description' => null,
                'price' => '0',
                'weight_price' => 0.0,
                'distance_price' => 0.0,
                'logo' => null,
                'position' => 0,
                'active' => 1,
                'class' => null,
                'properties' => null,
                'validation_rules' => null,
                'free_delivery_amount' => 0.0,
            ],
        'fieldMeta' =>
            [
                'name' =>
                    [
                        'dbtype' => 'varchar',
                        'precision' => '255',
                        'phptype' => 'string',
                        'null' => false,
                    ],
                'description' =>
                    [
                        'dbtype' => 'text',
                        'phptype' => 'string',
                        'null' => true,
                    ],
                'price' =>
                    [
                        'dbtype' => 'varchar',
                        'precision' => '11',
                        'phptype' => 'string',
                        'null' => true,
                        'default' => '0',
                    ],
                'weight_price' =>
                    [
                        'dbtype' => 'decimal',
                        'precision' => '12,2',
                        'phptype' => 'float',
                        'null' => true,
                        'default' => 0.0,
                    ],
                'distance_price' =>
                    [
                        'dbtype' => 'decimal',
                        'precision' => '12,2',
                        'phptype' => 'float',
                        'null' => true,
                        'default' => 0.0,
                    ],
                'logo' =>
                    [
                        'dbtype' => 'varchar',
                        'precision' => '255',
                        'phptype' => 'string',
                        'null' => true,
                    ],
                'position' =>
                    [
                        'dbtype' => 'tinyint',
                        'precision' => '1',
                        'attributes' => 'unsigned',
                        'phptype' => 'integer',
                        'null' => true,
                        'default' => 0,
                    ],
                'active' =>
                    [
                        'dbtype' => 'tinyint',
                        'precision' => '1',
                        'phptype' => 'integer',
                        'null' => true,
                        'default' => 1,
                    ],
                'class' =>
                    [
                        'dbtype' => 'varchar',
                        'precision' => '50',
                        'phptype' => 'string',
                        'null' => true,
                    ],
                'properties' =>
                    [
                        'dbtype' => 'text',
                        'phptype' => 'json',
                        'null' => true,
                    ],
                'validation_rules' =>
                    [
                        'dbtype' => 'text',
                        'phptype' => 'string',
                        'null' => true,
                    ],
                'free_delivery_amount' =>
                    [
                        'dbtype' => 'decimal',
                        'precision' => '12,2',
                        'phptype' => 'float',
                        'null' => true,
                        'default' => 0.0,
                    ],
            ],
        'aggregates' =>
            [
                'Orders' =>
                    [
                        'class' => 'ModxPro\\MiniShop3\\Model\\msOrder',
                        'local' => 'id',
                        'foreign' => 'delivery_id',
                        'cardinality' => 'many',
                        'owner' => 'local',
                    ],
                'Payments' =>
                    [
                        'class' => 'ModxPro\\MiniShop3\\Model\\msDeliveryMember',
                        'local' => 'id',
                        'foreign' => 'delivery_id',
                        'cardinality' => 'many',
                        'owner' => 'local',
                    ],
            ],
    ];
}

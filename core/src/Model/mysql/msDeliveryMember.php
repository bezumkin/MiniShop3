<?php

namespace ModxPro\MiniShop3\Model\mysql;

class msDeliveryMember extends \ModxPro\MiniShop3\Model\msDeliveryMember
{
    public static $metaMap = [
        'package' => 'ModxPro\MiniShop3\\Model',
        'version' => '3.0',
        'table' => 'ms3_delivery_payments',
        'extends' => 'xPDO\\Om\\xPDOObject',
        'tableMeta' =>
            [
                'engine' => 'InnoDB',
            ],
        'fields' =>
            [
                'delivery_id' => null,
                'payment_id' => null,
            ],
        'fieldMeta' =>
            [
                'delivery_id' =>
                    [
                        'dbtype' => 'int',
                        'precision' => '10',
                        'attributes' => 'unsigned',
                        'phptype' => 'integer',
                        'null' => false,
                        'index' => 'pk',
                    ],
                'payment_id' =>
                    [
                        'dbtype' => 'int',
                        'precision' => '10',
                        'attributes' => 'unsigned',
                        'phptype' => 'integer',
                        'null' => false,
                        'index' => 'pk',
                    ],
            ],
        'indexes' =>
            [
                'delivery' =>
                    [
                        'alias' => 'delivery',
                        'primary' => true,
                        'unique' => true,
                        'type' => 'BTREE',
                        'columns' =>
                            [
                                'delivery_id' =>
                                    [
                                        'length' => '',
                                        'collation' => 'A',
                                        'null' => false,
                                    ],
                                'payment_id' =>
                                    [
                                        'length' => '',
                                        'collation' => 'A',
                                        'null' => false,
                                    ],
                            ],
                    ],
            ],
        'aggregates' =>
            [
                'Delivery' =>
                    [
                        'class' => 'ModxPro\\MiniShop3\\Model\\msDelivery',
                        'local' => 'delivery_id',
                        'foreign' => 'id',
                        'cardinality' => 'one',
                        'owner' => 'foreign',
                    ],
                'Payment' =>
                    [
                        'class' => 'ModxPro\\MiniShop3\\Model\\msPayment',
                        'local' => 'payment_id',
                        'foreign' => 'id',
                        'cardinality' => 'one',
                        'owner' => 'foreign',
                    ],
            ],
    ];
}

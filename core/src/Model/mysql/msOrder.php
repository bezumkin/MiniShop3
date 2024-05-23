<?php

namespace ModxPro\MiniShop3\Model\mysql;

class msOrder extends \ModxPro\MiniShop3\Model\msOrder
{
    public static $metaMap = [
        'package' => 'ModxPro\MiniShop3\\Model',
        'version' => '3.0',
        'table' => 'ms3_orders',
        'extends' => 'xPDO\\Om\\xPDOSimpleObject',
        'tableMeta' =>
            [
                'engine' => 'InnoDB',
            ],
        'fields' =>
            [
                'user_id' => 0,
                'token' => '',
                'createdon' => null,
                'updatedon' => null,
                'num' => '',
                'cost' => 0.0,
                'cart_cost' => 0.0,
                'delivery_cost' => 0.0,
                'weight' => 0.0,
                'status_id' => 0,
                'delivery_id' => 0,
                'payment_id' => 0,
                'context' => 'web',
                'order_comment' => null,
                'properties' => null,
            ],
        'fieldMeta' =>
            [
                'user_id' =>
                    [
                        'dbtype' => 'int',
                        'precision' => '10',
                        'attributes' => 'unsigned',
                        'phptype' => 'integer',
                        'null' => true,
                        'default' => 0,
                    ],
                'token' =>
                    [
                        'dbtype' => 'varchar',
                        'precision' => '32',
                        'phptype' => 'string',
                        'null' => false,
                    ],
                'createdon' =>
                    [
                        'dbtype' => 'datetime',
                        'phptype' => 'datetime',
                        'null' => true,
                    ],
                'updatedon' =>
                    [
                        'dbtype' => 'datetime',
                        'phptype' => 'datetime',
                        'null' => true,
                    ],
                'num' =>
                    [
                        'dbtype' => 'varchar',
                        'precision' => '20',
                        'phptype' => 'string',
                        'null' => true,
                        'default' => '',
                    ],
                'cost' =>
                    [
                        'dbtype' => 'decimal',
                        'precision' => '12,2',
                        'phptype' => 'float',
                        'null' => true,
                        'default' => 0.0,
                    ],
                'cart_cost' =>
                    [
                        'dbtype' => 'decimal',
                        'precision' => '12,2',
                        'phptype' => 'float',
                        'null' => true,
                        'default' => 0.0,
                    ],
                'delivery_cost' =>
                    [
                        'dbtype' => 'decimal',
                        'precision' => '12,2',
                        'phptype' => 'float',
                        'null' => true,
                        'default' => 0.0,
                    ],
                'weight' =>
                    [
                        'dbtype' => 'decimal',
                        'precision' => '13,3',
                        'phptype' => 'float',
                        'null' => true,
                        'default' => 0.0,
                    ],
                'status_id' =>
                    [
                        'dbtype' => 'int',
                        'precision' => '10',
                        'attributes' => 'unsigned',
                        'phptype' => 'integer',
                        'null' => true,
                        'default' => 0,
                    ],
                'delivery_id' =>
                    [
                        'dbtype' => 'int',
                        'precision' => '10',
                        'attributes' => 'unsigned',
                        'phptype' => 'integer',
                        'null' => true,
                        'default' => 0,
                    ],
                'payment_id' =>
                    [
                        'dbtype' => 'int',
                        'precision' => '10',
                        'attributes' => 'unsigned',
                        'phptype' => 'integer',
                        'null' => true,
                        'default' => 0,
                    ],
                'context' =>
                    [
                        'dbtype' => 'varchar',
                        'precision' => '100',
                        'phptype' => 'string',
                        'null' => true,
                        'default' => 'web',
                    ],
                'order_comment' =>
                    [
                        'dbtype' => 'text',
                        'phptype' => 'string',
                        'null' => true,
                    ],
                'properties' =>
                    [
                        'dbtype' => 'text',
                        'phptype' => 'json',
                        'null' => true,
                    ],
            ],
        'indexes' =>
            [
                'user_id' =>
                    [
                        'alias' => 'user_id',
                        'primary' => false,
                        'unique' => false,
                        'type' => 'BTREE',
                        'columns' =>
                            [
                                'user_id' =>
                                    [
                                        'length' => '',
                                        'collation' => 'A',
                                        'null' => false,
                                    ],
                            ],
                    ],
                'token' =>
                    [
                        'alias' => 'token',
                        'primary' => false,
                        'unique' => false,
                        'type' => 'BTREE',
                        'columns' =>
                            [
                                'token' =>
                                    [
                                        'length' => '',
                                        'collation' => 'A',
                                        'null' => false,
                                    ],
                            ],
                    ],
                'status_id' =>
                    [
                        'alias' => 'status_id',
                        'primary' => false,
                        'unique' => false,
                        'type' => 'BTREE',
                        'columns' =>
                            [
                                'status_id' =>
                                    [
                                        'length' => '',
                                        'collation' => 'A',
                                        'null' => false,
                                    ],
                            ],
                    ],
            ],
        'composites' =>
            [
                'Address' =>
                    [
                        'class' => 'ModxPro\\MiniShop3\\Model\\msOrderAddress',
                        'local' => 'id',
                        'foreign' => 'order_id',
                        'cardinality' => 'one',
                        'owner' => 'local',
                    ],
                'Products' =>
                    [
                        'class' => 'ModxPro\\MiniShop3\\Model\\msOrderProduct',
                        'local' => 'id',
                        'foreign' => 'order_id',
                        'cardinality' => 'many',
                        'owner' => 'local',
                    ],
                'Log' =>
                    [
                        'class' => 'ModxPro\\MiniShop3\\Model\\msOrderLog',
                        'local' => 'id',
                        'foreign' => 'order_id',
                        'cardinality' => 'many',
                        'owner' => 'local',
                    ],
            ],
        'aggregates' =>
            [
                'User' =>
                    [
                        'class' => 'MODX\\Revolution\\modUser',
                        'local' => 'user_id',
                        'foreign' => 'id',
                        'cardinality' => 'one',
                        'owner' => 'foreign',
                    ],
                'UserProfile' =>
                    [
                        'class' => 'MODX\\Revolution\\modUserProfile',
                        'local' => 'user_id',
                        'foreign' => 'internalKey',
                        'owner' => 'foreign',
                        'cardinality' => 'one',
                    ],
                'CustomerProfile' =>
                    [
                        'class' => 'ModxPro\\MiniShop3\\Model\\msCustomerProfile',
                        'local' => 'user_id',
                        'foreign' => 'id',
                        'owner' => 'foreign',
                        'cardinality' => 'one',
                    ],
                'Status' =>
                    [
                        'class' => 'ModxPro\\MiniShop3\\Model\\msOrderStatus',
                        'local' => 'status_id',
                        'foreign' => 'id',
                        'cardinality' => 'one',
                        'owner' => 'foreign',
                    ],
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

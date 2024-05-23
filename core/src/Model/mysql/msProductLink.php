<?php

namespace ModxPro\MiniShop3\Model\mysql;

class msProductLink extends \ModxPro\MiniShop3\Model\msProductLink
{
    public static $metaMap = [
        'package' => 'ModxPro\MiniShop3\\Model',
        'version' => '3.0',
        'table' => 'ms3_product_links',
        'extends' => 'xPDO\\Om\\xPDOObject',
        'tableMeta' =>
            [
                'engine' => 'InnoDB',
            ],
        'fields' =>
            [
                'link' => null,
                'master' => null,
                'slave' => null,
            ],
        'fieldMeta' =>
            [
                'link' =>
                    [
                        'dbtype' => 'int',
                        'precision' => '10',
                        'phptype' => 'integer',
                        'attributes' => 'unsigned',
                        'null' => false,
                        'index' => 'pk',
                    ],
                'master' =>
                    [
                        'dbtype' => 'int',
                        'precision' => '10',
                        'phptype' => 'integer',
                        'attributes' => 'unsigned',
                        'null' => false,
                        'index' => 'pk',
                    ],
                'slave' =>
                    [
                        'dbtype' => 'int',
                        'precision' => '10',
                        'phptype' => 'integer',
                        'attributes' => 'unsigned',
                        'null' => false,
                        'index' => 'pk',
                    ],
            ],
        'indexes' =>
            [
                'type' =>
                    [
                        'alias' => 'link',
                        'primary' => true,
                        'unique' => true,
                        'type' => 'BTREE',
                        'columns' =>
                            [
                                'link' =>
                                    [
                                        'length' => '',
                                        'collation' => 'A',
                                        'null' => false,
                                    ],
                                'master' =>
                                    [
                                        'length' => '',
                                        'collation' => 'A',
                                        'null' => false,
                                    ],
                                'slave' =>
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
                'Link' =>
                    [
                        'class' => 'ModxPro\\MiniShop3\\Model\\msLink',
                        'local' => 'link',
                        'foreign' => 'id',
                        'owner' => 'foreign',
                        'cardinality' => 'one',
                    ],
                'Master' =>
                    [
                        'class' => 'ModxPro\\MiniShop3\\Model\\msProduct',
                        'local' => 'master',
                        'foreign' => 'id',
                        'owner' => 'foreign',
                        'cardinality' => 'one',
                    ],
                'Slave' =>
                    [
                        'class' => 'ModxPro\\MiniShop3\\Model\\msProduct',
                        'local' => 'slave',
                        'foreign' => 'id',
                        'owner' => 'foreign',
                        'cardinality' => 'one',
                    ],
            ],
    ];
}

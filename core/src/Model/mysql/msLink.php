<?php

namespace ModxPro\MiniShop3\Model\mysql;

class msLink extends \ModxPro\MiniShop3\Model\msLink
{
    public static $metaMap = [
        'package' => 'ModxPro\MiniShop3\\Model',
        'version' => '3.0',
        'table' => 'ms3_links',
        'extends' => 'xPDO\\Om\\xPDOSimpleObject',
        'tableMeta' =>
            [
                'engine' => 'InnoDB',
            ],
        'fields' =>
            [
                'type' => null,
                'name' => null,
                'description' => null,
            ],
        'fieldMeta' =>
            [
                'type' =>
                    [
                        'dbtype' => 'varchar',
                        'precision' => '100',
                        'phptype' => 'string',
                        'null' => false,
                    ],
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
            ],
        'indexes' =>
            [
                'type' =>
                    [
                        'alias' => 'type',
                        'primary' => false,
                        'unique' => false,
                        'type' => 'BTREE',
                        'columns' =>
                            [
                                'type' =>
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
                'Links' =>
                    [
                        'class' => 'ModxPro\\MiniShop3\\Model\\msProductLink',
                        'local' => 'id',
                        'foreign' => 'link',
                        'owner' => 'local',
                        'cardinality' => 'many',
                    ],
            ],
    ];
}

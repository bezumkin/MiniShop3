<?php

namespace ModxPro\MiniShop3\Processors\Settings\Link;

use ModxPro\MiniShop3\Model\msLink;
use MODX\Revolution\Processors\Model\GetListProcessor;
use xPDO\Om\xPDOObject;
use xPDO\Om\xPDOQuery;

class GetList extends GetListProcessor
{
    public $classKey = msLink::class;
    public $defaultSortField = 'id';
    public $defaultSortDirection = 'asc';
    public $permission = 'mssetting_list';


    /**
     * @return bool|null|string
     */
    public function initialize()
    {
        if (!$this->modx->hasPermission($this->permission)) {
            return $this->modx->lexicon('access_denied');
        }

        return parent::initialize();
    }

    /**
     * @param xPDOQuery $c
     *
     * @return xPDOQuery
     */
    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        if ($this->getProperty('combo')) {
            $c->select('id,name');
        }
        if ($id = (int)$this->getProperty('id')) {
            $c->where(['id' => $id]);
        }
        if ($query = trim($this->getProperty('query'))) {
            $c->where([
                'name:LIKE' => "%{$query}%",
                'OR:type:LIKE' => "%{$query}%",
            ]);
        }

        return $c;
    }


    /**
     * @param xPDOObject $object
     *
     * @return array
     */
    public function prepareRow(xPDOObject $object)
    {
        if ($this->getProperty('combo')) {
            $data = [
                'id' => $object->get('id'),
                'name' => $object->get('name'),
            ];
        } else {
            $data = $object->toArray();
            $data['actions'] = [];

            $data['actions'][] = [
                'cls' => '',
                'icon' => 'icon icon-edit',
                'title' => $this->modx->lexicon('ms3_menu_update'),
                'action' => 'updateLink',
                'button' => true,
                'menu' => true,
            ];

            $data['actions'][] = [
                'cls' => [
                    'menu' => 'red',
                    'button' => 'red',
                ],
                'icon' => 'icon icon-trash-o',
                'title' => $this->modx->lexicon('ms3_menu_remove'),
                'multiple' => $this->modx->lexicon('ms3_menu_remove_multiple'),
                'action' => 'removeLink',
                'button' => true,
                'menu' => true,
            ];
        }

        return $data;
    }
}

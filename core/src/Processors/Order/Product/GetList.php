<?php

namespace ModxPro\MiniShop3\Processors\Order\Product;

use MODX\Revolution\Processors\Model\GetListProcessor;
use xPDO\Om\xPDOObject;
use xPDO\Om\xPDOQuery;
use ModxPro\MiniShop3\Model\msCategory;
use ModxPro\MiniShop3\Model\msOrder;
use ModxPro\MiniShop3\Model\msOrderProduct;
use ModxPro\MiniShop3\Model\msProduct;
use ModxPro\MiniShop3\Model\msProductData;
use ModxPro\MiniShop3\Model\msVendor;

class GetList extends GetListProcessor
{
    public $classKey = msOrderProduct::class;
    public $languageTopics = ['minishop3:default'];
    public $permission = 'msorder_list';
    public $defaultSortField = 'id';
    public $defaultSortDirection = 'ASC';


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
        $c->innerJoin(msOrder::class, 'msOrder', '`msOrderProduct`.`order_id` = `msOrder`.`id`');
        $c->leftJoin(msProduct::class, 'msProduct', '`msOrderProduct`.`product_id` = `msProduct`.`id`');
        $c->leftJoin(msProductData::class, 'msProductData', '`msOrderProduct`.`product_id` = `msProductData`.`id`');
        $c->leftJoin(msCategory::class, 'msCategory', '`msProduct`.`parent` = `msCategory`.`id`');
        $c->leftJoin(msVendor::Class, 'msVendor', '`msProductData`.`vendor_id` = `msVendor`.`id`');
        $c->select($this->modx->getSelectColumns(msVendor::class, 'msVendor', 'vendor_', ['id'], true));
        $c->where([
            'order_id' => $this->getProperty('order_id'),
        ]);

        $c->select($this->modx->getSelectColumns(msOrderProduct::class, 'msOrderProduct'));
        $c->select($this->modx->getSelectColumns(msProduct::class, 'msProduct', 'product_'));
        $c->select($this->modx->getSelectColumns(msProductData::class, 'msProductData', 'product_', ['id'], true));
        $c->select($this->modx->getSelectColumns(msCategory::class, 'msCategory', 'category_', ['id'], true));

        $query = $this->getProperty('query', null);
        if (!empty($query)) {
            $c->where([
                'msProduct.pagetitle:LIKE' => '%' . $query . '%',
                'OR:msProduct.description:LIKE' => '%' . $query . '%',
                'OR:msProduct.introtext:LIKE' => '%' . $query . '%',
                'OR:msProductData.article:LIKE' => '%' . $query . '%',
                'OR:msProductData.vendor:LIKE' => '%' . $query . '%',
                'OR:msProductData.made_in:LIKE' => '%' . $query . '%',
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
        $fields = array_map('trim', explode(',', $this->modx->getOption('ms3_order_product_fields', null, '')));
        $fields = array_values(array_unique(array_merge($fields, [
            'id',
            'product_id',
            'name',
            'product_pagetitle',
        ])));

        $data = [];
        foreach ($fields as $v) {
            $data[$v] = $object->get($v);
            if ($v == 'product_price' || $v == 'product_old_price') {
                $data[$v] = round($data[$v], 2);
            } else {
                if ($v == 'product_weight') {
                    $data[$v] = round($data[$v], 3);
                }
            }
        }

        $data['name'] = !$object->get('name')
            ? $object->get('product_pagetitle')
            : $object->get('name');

        $options = $object->get('options');
        if (!empty($options) && is_array($options)) {
            $tmp = [];
            foreach ($options as $k => $v) {
                $tmp[] = $this->modx->lexicon('ms3_' . $k) . ': ' . $v;
                $data['option_' . $k] = $v;
            }
            $data['options'] = implode('; ', $tmp);
        }

        $data['actions'] = [
            [
                'cls' => '',
                'icon' => 'icon icon-edit',
                'title' => $this->modx->lexicon('ms3_menu_update'),
                'action' => 'updateOrderProduct',
                'button' => true,
                'menu' => true,
            ],
            [
                'cls' => [
                    'menu' => 'red',
                    'button' => 'red',
                ],
                'icon' => 'icon icon-trash-o',
                'title' => $this->modx->lexicon('ms3_menu_remove'),
                'multiple' => $this->modx->lexicon('ms3_menu_remove'),
                'action' => 'removeOrderProduct',
                'button' => true,
                'menu' => true,
            ],
        ];

        return $data;
    }
}

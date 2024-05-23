<?php

namespace ModxPro\MiniShop3\Processors\Settings\Option;

use ModxPro\MiniShop3\Model\msOption;
use MODX\Revolution\modCategory;
use MODX\Revolution\Processors\Model\GetListProcessor;
use xPDO\Om\xPDOQuery;

class GetCategories extends GetListProcessor
{
    public $classKey = modCategory::class;
    public $defaultSortField = 'category';
    public $permission = 'view_category';
    public $languageTopics = ['default'];


    /**
     * @param xPDOQuery $c
     *
     * @return xPDOQuery
     */
    public function prepareQueryBeforeCount(xPDOQuery $c)
    {

        $c->innerJoin(msOption::class, 'msOption', 'msOption.modcategory_id=modCategory.id');

        return $c;
    }


    /**
     * @param array $list
     *
     * @return array
     */
    public function afterIteration(array $list)
    {
        array_unshift($list, [
            'id' => 0,
            'category' => $this->modx->lexicon('no_category'),
        ]);

        return $list;
    }
}

<?php

namespace ModxPro\MiniShop3\Processors\Category\Option;

use MODX\Revolution\Processors\Model\RemoveProcessor;
use ModxPro\MiniShop3\Model\msCategoryOption;

class Remove extends RemoveProcessor
{
    public $classKey = msCategoryOption::class;
    public $objectType = 'ms3_option';
    public $languageTopics = ['minishop3:default'];
    public $permission = 'mscategory_save';

    /**
     * @return bool|null|string
     */
    public function initialize()
    {
        $this->object = $this->modx->getObject($this->classKey, [
            'option_id' => $this->getProperty('option_id'),
            'category_id' => $this->getProperty('category_id'),
        ]);
        if (empty($this->object)) {
            return $this->modx->lexicon('ms3_option_err_nfs');
        }

        return true;
    }

    /**
     * @return bool
     */
    public function afterRemove()
    {
        $sql = "UPDATE {$this->modx->getTableName($this->classKey)} SET `position`=`position`-1
            WHERE `position`>{$this->object->get('position')} AND `category_id`={$this->object->get('category_id')}";
        $this->modx->exec($sql);

        return parent::afterRemove();
    }
}

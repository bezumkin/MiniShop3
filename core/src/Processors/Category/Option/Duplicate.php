<?php

namespace ModxPro\MiniShop3\Processors\Category\Option;

use ModxPro\MiniShop3\Model\msCategory;
use ModxPro\MiniShop3\Model\msCategoryOption;
use MODX\Revolution\Processors\ModelProcessor;

class Duplicate extends ModelProcessor
{
    public $classKey = msCategory::class;
    public $languageTopics = ['minishop3:default'];
    public $permission = 'mscategory_save';
    /** @var msCategory $to_object */
    public $to_object;


    /**
     * @return bool|null|string
     */
    public function initialize()
    {
        $from = (int)$this->getProperty('category_from');
        $to = (int)$this->getProperty('category_to');
        if (!$from || !$to) {
            return $this->modx->lexicon('ms3_category_err_ns');
        }
        $this->object = $this->modx->getObject($this->classKey, $from);
        $this->to_object = $this->modx->getObject($this->classKey, $to);

        if (!$this->object || !$this->to_object) {
            return $this->modx->lexicon('ms3_category_err_nfs', [
                $this->primaryKeyField => [$from, $to],
            ]);
        }

        return true;
    }


    /**
     * @return array|string
     */
    public function process()
    {
        $options = $this->object->getMany('CategoryOptions');
        /** @var msCategoryOption $option */
        foreach ($options as $option) {
            $new = $this->modx->getObject(msCategoryOption::class, [
                'option_id' => $option->get('option_id'),
                'category_id' => $this->to_object->get('id'),
            ]);
            if (!$new) {
                /** @var msCategoryOption $new */
                $new = $this->modx->newObject(msCategoryOption::class);
                $new->fromArray($option->toArray(), '', true);
            }
            $this->to_object->addMany($new);
        }

        if (!$this->to_object->save()) {
            return $this->failure($this->modx->lexicon('ms3_category_err_save'));
        }

        return $this->cleanup();
    }


    /**
     * @return array|string
     */
    public function cleanup()
    {
        $fields = [];
        if ($options = $this->to_object->getMany('CategoryOptions')) {
            /** @var msCategoryOption $option */
            foreach ($options as $option) {
                $fields[] = $option->get('option_id');
            }
        }
        $this->to_object->set('options', $fields);

        return $this->success('', $this->to_object);
    }
}

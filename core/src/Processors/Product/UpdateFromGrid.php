<?php

namespace ModxPro\MiniShop3\Processors\Product;

use MODX\Revolution\modResource;
use MODX\Revolution\modX;
use MODX\Revolution\Processors\Processor;
use ModxPro\MiniShop3\Processors\Product\Update;

class UpdateFromGrid extends Update
{
    /**
     * @param modX $modx
     * @param string $className
     * @param array $properties
     *
     * @return Processor
     */
    public static function getInstance(modX $modx, $className, $properties = [])
    {
        return new UpdateFromGrid($modx, $properties);
    }


    /**
     * @return bool|null|string
     */
    public function initialize()
    {
        $data = $this->getProperty('data');
        if (empty($data)) {
            return $this->modx->lexicon('invalid_data');
        }

        $data = json_decode($data, true);
        if (empty($data)) {
            return $this->modx->lexicon('invalid_data');
        }

        $data = $this->prepareValues($data);
        $this->setProperties($data);
        $this->unsetProperty('data');

        return parent::initialize();
    }


    /**
     * @param array $data
     *
     * @return array
     */
    public function prepareValues(array $data)
    {
        $fields = $this->modx->getFieldMeta(modResource::class);
        foreach ($fields as $key => $field) {
            if ($field['phptype'] == 'timestamp') {
                if (!empty($data[$key]) && is_numeric($data[$key])) {
                    $data[$key] = date('Y-m-d H:i:s', $data[$key]);
                }
            }
        }

        return $data;
    }


    /**
     * @return array|string
     */
    public function beforeSet()
    {
        $properties = $this->getProperties();
        $options = $this->object->loadData()->get('options');
        foreach ($properties as $key => $value) {
            if (strpos($key, 'options-') === 0) {
                $options[substr($key, 8)] = $value;
                $this->unsetProperty($key);
            }
        }
        if (!empty($options)) {
            $this->setProperty('options', $options);
        }

        return parent::beforeSet();
    }
}

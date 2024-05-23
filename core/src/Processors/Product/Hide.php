<?php

namespace ModxPro\MiniShop3\Processors\Product;

use ModxPro\MiniShop3\Model\msProduct;
use ModxPro\MiniShop3\Processors\Product\Update;

class Hide extends Update
{
    public $classKey = msProduct::class;


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $this->workingContext = $this->modx->getContext(
            $this->getProperty(
                'context_key',
                $this->object->get('context_key') ? $this->object->get('context_key') : 'web'
            )
        );

        $this->properties = [
            'show_in_tree' => false,
        ];

        return true;
    }
}

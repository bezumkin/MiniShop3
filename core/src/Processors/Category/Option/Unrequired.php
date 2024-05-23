<?php

namespace ModxPro\MiniShop3\Processors\Category\Option;

use ModxPro\MiniShop3\Processors\Category\Option\Update;

class Unrequired extends Update
{
    /**
     * @return bool
     */
    public function beforeSet()
    {
        $this->properties = [
            'required' => false,
        ];

        return true;
    }
}

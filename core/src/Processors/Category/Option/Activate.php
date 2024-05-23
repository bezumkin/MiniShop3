<?php

namespace ModxPro\MiniShop3\Processors\Category\Option;

use ModxPro\MiniShop3\Processors\Category\Option\Update;

class Activate extends Update
{
    /**
     * @return bool
     */
    public function beforeSet()
    {
        $this->properties = [
            'active' => true,
        ];

        return true;
    }
}

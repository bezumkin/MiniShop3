<?php

namespace ModxPro\MiniShop3\Processors\Category\Option;

use ModxPro\MiniShop3\Processors\Category\Option\Update;

class Required extends Update
{
    /**
     * @return bool
     */
    public function beforeSet()
    {
        $this->properties = [
            'required' => true,
        ];

        return true;
    }
}

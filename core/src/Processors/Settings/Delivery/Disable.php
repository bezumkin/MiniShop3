<?php

namespace ModxPro\MiniShop3\Processors\Settings\Delivery;

use ModxPro\MiniShop3\Processors\Settings\Delivery\Update;

class Disable extends Update
{
    /**
     * @return bool
     */
    public function beforeSet()
    {
        $this->properties = [
            'active' => false,
        ];

        return true;
    }
}

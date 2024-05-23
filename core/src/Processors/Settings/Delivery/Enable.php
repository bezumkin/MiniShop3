<?php

namespace ModxPro\MiniShop3\Processors\Settings\Delivery;

use ModxPro\MiniShop3\Processors\Settings\Delivery\Update;

class Enable extends Update
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

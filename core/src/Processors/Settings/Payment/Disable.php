<?php

namespace ModxPro\MiniShop3\Processors\Settings\Payment;

use ModxPro\MiniShop3\Processors\Settings\Payment\Update;

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

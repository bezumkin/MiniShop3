<?php

namespace ModxPro\MiniShop3\Processors\Settings\Status;

use ModxPro\MiniShop3\Processors\Settings\Status\Update;

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

<?php

namespace ModxPro\MiniShop3\Processors\Settings\Status;

use ModxPro\MiniShop3\Processors\Settings\Status\Update;

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

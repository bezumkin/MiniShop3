<?php

namespace ModxPro\MiniShop3\Controllers\Options\Types;

use ModxPro\MiniShop3\Controllers\Options\Types\msOptionType;

class Textarea extends msOptionType
{

    /**
    * @param $field
    *
    * @return string
    */
    public function getField($field)
    {
        return "{xtype:'textarea'}";
    }
}

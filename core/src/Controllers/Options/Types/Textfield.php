<?php

namespace ModxPro\MiniShop3\Controllers\Options\Types;

use ModxPro\MiniShop3\Controllers\Options\Types\msOptionType;

class Textfield extends msOptionType
{

    /**
    * @param $field
    *
    * @return string
    */
    public function getField($field)
    {
        return "{xtype:'textfield'}";
    }
}

return 'msTextfieldType';

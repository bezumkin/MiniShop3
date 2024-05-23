<?php

namespace ModxPro\MiniShop3\Processors\Product;

use ModxPro\MiniShop3\Model\msProduct;
use MODX\Revolution\Processors\Model\GetProcessor;

class Get extends GetProcessor
{
    public $classKey = msProduct::class;
    public $languageTopics = ['minishop3:default'];
}

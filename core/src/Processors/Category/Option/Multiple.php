<?php

namespace ModxPro\MiniShop3\Processors\Category\Option;

use MODX\Revolution\Processors\ModelProcessor;

class Multiple extends ModelProcessor
{
    /**
     * @return array|string
     */
    public function process()
    {
        $method = $this->getProperty('method', false);
        if (!$method) {
            return $this->failure();
        }
        $method = ucfirst($method);
        $ids = json_decode($this->getProperty('ids'), true);
        if (empty($ids)) {
            return $this->success();
        }

        foreach ($ids as $key) {
            $this->modx->runProcessor('ModxPro\\MiniShop3\\Processors\\Category\\Option\\' . $method, $key);
        }

        return $this->success();
    }
}

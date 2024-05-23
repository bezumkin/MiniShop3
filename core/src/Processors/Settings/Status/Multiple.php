<?php

namespace ModxPro\MiniShop3\Processors\Settings\Status;

use ModxPro\MiniShop3\MiniShop3;
use MODX\Revolution\Processors\ModelProcessor;
use MODX\Revolution\Processors\ProcessorResponse;

class Multiple extends ModelProcessor
{
    /**
     * @return array|string
     *
     * @throws
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

        foreach ($ids as $id) {
            $this->modx->runProcessor('ModxPro\\MiniShop3\\Processors\\Settings\\Status\\' . $method, ['id' => $id]);
        }

        return $this->success();
    }
}

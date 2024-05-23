<?php

namespace ModxPro\MiniShop3\Processors\Settings\Vendor;

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

        /** @var MiniShop3 $ms3 */
        $ms3 = $this->modx->services->get('ms3');

        foreach ($ids as $id) {
            /** @var ProcessorResponse $response */
            $response = $ms3->utils->runProcessor('ModxPro\\MiniShop3\\Processors\\Settings\\Vendor\\' . $method, ['id' => $id]);
            if ($response->isError()) {
                return $response->getResponse();
            }
        }

        return $this->success();
    }
}

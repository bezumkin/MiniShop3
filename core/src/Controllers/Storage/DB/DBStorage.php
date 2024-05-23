<?php

namespace ModxPro\MiniShop3\Controllers\Storage\DB;

use ModxPro\MiniShop3\MiniShop3;
use ModxPro\MiniShop3\Model\msOrder;
use ModxPro\MiniShop3\Model\msOrderAddress;
use MODX\Revolution\modX;

class DBStorage
{
    protected $modx;
    protected $ms3;
    protected $ctx;
    protected $token;
    protected $draft;
    public function __construct(modX $modx, MiniShop3 $ms3)
    {
        $this->modx = $modx;
        $this->ms3 = $ms3;
    }

    /**
     * @return bool
     */
    public function initDraft()
    {
        if (empty($this->token)) {
            return false;
        }
        $this->draft = $this->getDraft($this->token);
        if (empty($this->draft)) {
            $this->draft = $this->newDraft($this->token);
        }
        return true;
    }

    public function restrictDraft($draft)
    {
        //TODO событие до перерасчета заказа
        $products = $draft->getMany('Products');
        $cart_cost = 0;
        $weight = 0;
        if (!empty($products)) {
            foreach ($products as $product) {
                $weight += $product->get('weight');
                $cart_cost += $product->get('cost');
            }
        }

        $delivery_cost = $draft->get('delivery_cost');
        $cost = $cart_cost + $delivery_cost;

        //TODO событие перерасчета заказа
        $draft->set('updatedon', time());
        $draft->set('cart_cost', $cart_cost);
        $draft->set('cost', $cost);
        $draft->set('weight', $weight);
        $draft->save();
    }

    protected function getDraft($token)
    {
        $status_draft = $this->modx->getOption('ms3_status_draft', null, 1);
        $where = [
            'token' => $token,
            'status_id' => $status_draft,
            'context' => $this->ms3->config['ctx']
        ];
        return $this->modx->getObject(msOrder::class, $where);
    }

    protected function newDraft($token): msOrder
    {
        $status_draft = $this->modx->getOption('ms3_status_draft', null, 1);
        /** @var msOrder $msOrder */
        $msOrder = $this->modx->newObject(msOrder::class);
        $data = [
            'token' => $token,
            'status_id' => $status_draft,
            'createdon' => time(),
            'user_id' => $this->modx->getLoginUserID($this->ctx),
        ];
        $msOrder->fromArray($data);

        //TODO Событие перед созданием черновика
        //TODO Запись в лог msOrderLog
        $save = $msOrder->save();
        if ($save) {
            $msOrderAddress = $this->modx->newObject(msOrderAddress::class);
            $msOrderAddress->fromArray([
                'createdon' => time(),
                'user_id' => $this->modx->getLoginUserID($this->ctx),
                'order_id' => $msOrder->get('id')
            ]);
            $msOrderAddress->save();
            //TODO Событие по факту созданием черновика
        }

        return $msOrder;
    }

    /**
     * Shorthand for MS3 success method
     *
     * @param string $message
     * @param array $data
     * @param array $placeholders
     *
     * @return array|string
     */
    protected function success(string $message = '', array $data = [], array $placeholders = []): array|string
    {
        return $this->ms3->utils->success($message, $data, $placeholders);
    }

    /**
     * Shorthand for MS3 error method
     *
     * @param string $message
     * @param array $data
     * @param array $placeholders
     *
     * @return array|string
     */
    protected function error(string $message = '', array $data = [], array $placeholders = []): array|string
    {
        return $this->ms3->utils->error($message, $data, $placeholders);
    }

    /**
     * Shorthand for MS3 invokeEvent method
     *
     * @param string $eventName
     * @param array $params
     *
     * @return array|string
     */
    protected function invokeEvent(string $eventName, array $params = []): array|string
    {
        return $this->ms3->utils->invokeEvent($eventName, $params);
    }
}

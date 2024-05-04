<?php

namespace MiniShop3\Controllers\Order;

use MiniShop3\MiniShop3;
use MiniShop3\Model\msDelivery;
use MiniShop3\Model\msDeliveryMember;
use MiniShop3\Model\msOrder;
use MiniShop3\Model\msPayment;
use MODX\Revolution\modX;
use MiniShop3\Controllers\Storage\DB\DBOrder;

class Order implements OrderInterface
{
    /** @var modX $modx */
    public $modx;
    /** @var MiniShop3 $ms3 */
    public $ms3;
    /** @var array $config */
    public $config = [];
    protected $storage;

    /**
     * Order constructor.
     *
     * @param MiniShop3 $ms3
     * @param array $config
     */
    public function __construct(MiniShop3 $ms3, array $config = [])
    {
        $this->ms3 = $ms3;
        $this->modx = $ms3->modx;

        $this->config = array_merge([], $config);

        $this->modx->lexicon->load('minishop3:cart');

        $this->storage = new DBOrder($this->modx, $this->ms3);
    }

    public function initialize(string $token = '', array $config = []): bool
    {
        return $this->storage->initialize($token, $this->config);
    }

    public function get(): array
    {
        return $this->storage->get();
    }

    /**
     * @param bool $with_cart
     * @param bool $only_cost
     *
     * @return array
     */
    public function getCost(bool $with_cart = true, bool $only_cost = false): array
    {
        return $this->storage->getCost($with_cart, $only_cost);
    }

    /**
     * @param string $key
     * @param string $value
     *
     * @return array
     */
    public function add(string $key, mixed $value = null): array
    {
        return $this->storage->add($key, $value);
    }

    /**
     * @param string $key
     * @param string $value
     *
     * @return bool|mixed|string
     */
    public function validate(string $key, $value): mixed
    {
        return $this->storage->validate($key, $value);
    }

    /**
     * @param string $key
     *
     * @return array|bool|string
     */
    public function remove($key): bool
    {
        return $this->storage->remove($key);
    }

    /**
     * @param array $order
     *
     * @return array
     */
    public function set(array $order): array
    {
        return $this->storage->set($order);
    }

    /**
     * Checks accordance of payment and delivery
     *
     * @param $delivery
     * @param $payment
     *
     * @return bool
     */
    public function hasPayment($delivery, $payment)
    {
        //TODO перенесен из ms2 - не используется, проверить
        $this->modx->log(1, 'OrderController::hasPayment');
        $q = $this->modx->newQuery(msPayment::class, ['id' => $payment, 'active' => 1]);
        $q->innerJoin(
            msDeliveryMember::class,
            'Member',
            'Member.payment_id = msPayment.id AND Member.delivery_id = ' . $delivery
        );

        return (bool)$this->modx->getCount(msPayment::class, $q);
    }

    /**
     * Returns required fields for delivery
     *
     * @param int $delivery_id
     *
     * @return array
     */
    public function getDeliveryRequiresFields(int $delivery_id = 0): array
    {
        return $this->storage->getDeliveryRequiresFields($delivery_id);
    }

    public function submit(array $data = []): array
    {
        return $this->storage->submit();
    }

    public function clean(): array
    {
        return $this->storage->clean();
    }

    /**
     * Returns number for new order
     * @return string
     */
    public function getNewOrderNum(): string
    {
        return $this->storage->getNewOrderNum();
    }

    /**
     * Shorthand for ms3 error method
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
     * Shorthand for ms3 success method
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

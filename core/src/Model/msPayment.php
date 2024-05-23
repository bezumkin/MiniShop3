<?php

namespace ModxPro\MiniShop3\Model;

use ModxPro\MiniShop3\Controllers\Order\OrderInterface;
use ModxPro\MiniShop3\Controllers\Payment\Payment;
use ModxPro\MiniShop3\Controllers\Payment\PaymentInterface;
use ModxPro\MiniShop3\MiniShop3;
use MODX\Revolution\modX;
use ModxPro\MiniShop3\Model\msDeliveryMember;
use ModxPro\MiniShop3\Model\msOrder;
use xPDO\Om\xPDOSimpleObject;
use xPDO\xPDO;

/**
 * Class msPayment
 *
 * @property string $name
 * @property string $description
 * @property string $price
 * @property string $logo
 * @property integer $position
 * @property integer $active
 * @property string $class
 * @property array $properties
 *
 * @package MiniShop3\Model
 */
class msPayment extends xPDOSimpleObject
{
    /** @var Payment $controller */
    public $controller;
    /** @var MiniShop3 $ms3 */
    public $ms3;

    /** @var string $defaultControllerClass */
    private string $defaultControllerClass = 'ModxPro\\MiniShop3\\Controllers\\Payment\\Payment';

    /**
     * msPayment constructor.
     *
     * @param xPDO $xpdo
     */
    public function __construct(xPDO $xpdo)
    {
        parent::__construct($xpdo);
        if ($this->xpdo->services->has('ms3')) {
            $this->ms3 = $this->xpdo->services->get('ms3');
        }
    }

    /**
     * Loads payment handler class
     *
     * @return bool
     */
    public function loadHandler()
    {
        $class = $this->get('class');
        if (!$class || $class === 'Payment') {
            $class = $this->defaultControllerClass;
        }

        if ($class !== $this->defaultControllerClass) {
            // TODO: ждём новой реализации
            //$this->ms3->loadCustomClasses('payment');
        }
        if (!class_exists($class)) {
            $this->xpdo->log(modX::LOG_LEVEL_ERROR, 'Payment controller class "' . $class . '" not found.');
            $class = $this->defaultControllerClass;
        }
        $this->controller = new $class($this->ms3, []);
        if (!($this->controller instanceof PaymentInterface)) {
            $this->xpdo->log(modX::LOG_LEVEL_ERROR, 'Could not initialize payment controller class: "' . $class . '"');

            return false;
        }

        return true;
    }

    /**
     * Send user to payment service
     *
     * @param msOrder $order Object with an order
     *
     * @return array|boolean $response
     */
    public function send(msOrder $order)
    {
        if (!is_object($this->controller) || !($this->controller instanceof PaymentInterface)) {
            if (!$this->loadHandler()) {
                return false;
            }
        }

        return $this->controller->send($order);
    }

    /**
     * Receives payment
     *
     * @param msOrder $order Object with an order
     *
     * @return array|boolean $response
     */
    public function receive(msOrder $order)
    {
        if (!is_object($this->controller) || !($this->controller instanceof PaymentInterface)) {
            if (!$this->loadHandler()) {
                return false;
            }
        }

        return $this->controller->receive($order);
    }

    /**
     * Returns an additional cost depending on the method of payment
     *
     * @param OrderInterface $order
     * @param float $cost Current cost of order
     *
     * @return float|integer
     */
    public function getCost(OrderInterface $order, float $cost = 0.0)
    {
        if (!is_object($this->controller) || !($this->controller instanceof PaymentInterface)) {
            if (!$this->loadHandler()) {
                return false;
            }
        }
        return $this->controller->getCost($order, $this, $cost);
    }

    /**
     * @param array $ancestors
     *
     * @return bool
     */
    public function remove(array $ancestors = [])
    {
        $this->xpdo->removeCollection(msDeliveryMember::class, ['payment_id' => $this->id]);
        return parent::remove($ancestors);
    }
}

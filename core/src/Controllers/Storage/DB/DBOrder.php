<?php

namespace ModxPro\MiniShop3\Controllers\Storage\DB;

use ModxPro\MiniShop3\Controllers\Storage\DB\DBStorage;
use ModxPro\MiniShop3\MiniShop3;
use ModxPro\MiniShop3\Model\msDelivery;
use ModxPro\MiniShop3\Model\msOrder;
use ModxPro\MiniShop3\Model\msOrderAddress;
use ModxPro\MiniShop3\Model\msPayment;
use MODX\Revolution\modX;
use ModxPro\MiniShop3\Controllers\Order\OrderInterface;
use Rakit\Validation\Validator;
use ModxPro\MiniShop3\Controllers\Order\OrderStatus;

class DBOrder extends DBStorage implements OrderInterface
{
    private $config;
    private $order;

    protected $validationRules = [];
    protected $validationMessages = [];
    protected $deliverValidationRules;

    /**
     * @param string $token
     * @param $config
     * @return bool
     */
    public function initialize(string $token = '', $config = []): bool
    {
        if (empty($token)) {
            return false;
        }
        $this->token = $token;
        $this->config = $config;
        if (!empty($_SESSION['ms3']['validation']['rules'])) {
            $this->validationRules = $_SESSION['ms3']['validation']['rules'];
        }
        if (!empty($_SESSION['ms3']['validation']['messages'])) {
            $this->validationMessages = $_SESSION['ms3']['validation']['messages'];
        }
        return true;
    }

    public function get(): array
    {
        if (empty($this->token)) {
            return $this->error('ms3_err_token');
        }
        $this->initDraft();

        //TODO Добавить событие?
//        $response = $this->invokeEvent('msOnBeforeGetOrder', [
//            'draft' => $this->draft,
//            'controller' => $this,
//        ]);
//        if (!($response['success'])) {
//            return $this->error($response['message']);
//        }
        $this->order = $this->getOrder();

        //TODO Добавить событие?
//        $response = $this->invokeEvent('msOnGetOrder, [
//            'draft' => $this->draft,
//            'data' => $this->order,
//            'controller' => $this,
//        ]);
//
//        if (!$response['success']) {
//            return $this->error($response['message']);
//        }
//
//        $this->cart = $response['data']['data'];

        $data = [];

        $data['order'] = $this->order;
        return $this->success(
            'ms3_order_get_success',
            $data
        );
    }

    public function getCost($with_cart = true, $only_cost = false): array
    {
        $response = $this->ms3->utils->invokeEvent('msOnBeforeGetOrderCost', [
            'controller' => $this,
            'cart' => $this->ms3->cart,
            'with_cart' => $with_cart,
            'only_cost' => $only_cost,
        ]);
        if (!$response['success']) {
            return $this->error($response['message']);
        }

        $cost = 0;
        $status = [];
        $this->ms3->cart->initialize($this->ms3->config['ctx'], $this->token);
        $response = $this->ms3->cart->status();
        if ($response['success']) {
            $status = $response['data'];
            $cost = $with_cart
                ? $status['total_cost']
                : 0;
        }

        $delivery_cost = 0;
        if (!empty($this->order['delivery_id'])) {
            /** @var msDelivery $msDelivery */
            $msDelivery = $this->modx->getObject(
                msDelivery::class,
                ['id' => $this->order['delivery_id']]
            );
            if ($msDelivery) {
                $cost = $msDelivery->getCost($this, $cost);
                $delivery_cost = $cost - $status['total_cost'];
                $this->setDeliveryCost($delivery_cost);
            }
        }

        if (!empty($this->order['payment_id'])) {
            /** @var msPayment $msPayment */
            $msPayment = $this->modx->getObject(
                msPayment::class,
                ['id' => $this->order['payment_id']]
            );
            if ($msPayment) {
                $cost = $msPayment->getCost($this, $cost);
            }
        }

        $response = $this->ms3->utils->invokeEvent('msOnGetOrderCost', [
            'controller' => $this,
            'cart' => $this->ms3->cart,
            'with_cart' => $with_cart,
            'only_cost' => $only_cost,
            'cost' => $cost,
            'delivery_cost' => $delivery_cost,
        ]);
        if (!$response['success']) {
            return $this->error($response['message']);
        }
        $cost = $response['data']['cost'];
        $delivery_cost = $response['data']['delivery_cost'];

        $data = $only_cost
            ? [
                'cost' => $cost,
            ]
            : [
                'cost' => $cost,
                'cart_cost' => $status['total_cost'],
                'discount_cost' => $status['total_discount'],
                'delivery_cost' => $delivery_cost
            ];
        return $this->success('ms3_order_getcost_success', $data);
    }

    public function add(string $key, mixed $value = null): array
    {
        if (empty($this->order)) {
            $response = $this->get();
            if ($response['success']) {
                $this->order = $response['data']['order'];
            }
        }
        $response = $this->ms3->utils->invokeEvent('msOnBeforeAddToOrder', [
            'key' => $key,
            'value' => $value,
            'controller' => $this,
        ]);
        if (!$response['success']) {
            return $this->error($response['message']);
        }
        $value = $response['data']['value'];

        if (empty($value)) {
            $this->remove($key);
            return $this->success('', [$key => null]);
        }
        $validateResponse = $this->validate($key, $value);
        if ($validateResponse['success']) {
            $validated = $validateResponse['data']['value'];
            $response = $this->ms3->utils->invokeEvent('msOnAddToOrder', [
                'key' => $key,
                'value' => $validated,
                'controller' => $this,
            ]);
            if (!$response['success']) {
                return $this->error($response['message']);
            }
            $validated = $response['data']['value'];
            $this->updateDraft($key, $validated);

            return $this->success('', [$key => $validated]);
        }
        $this->updateDraft($key);
        return $this->error($validateResponse['data']['error'][$key], [$key => null]);
    }

    public function validate(string $key, mixed $value): mixed
    {
        if (empty($this->order)) {
            $response = $this->get();
            if ($response['success']) {
                $this->order = $response['data']['order'];
            }
        }
        //TODO реализовать use custom validation rule для проверки существования payment, delivery,
        // для показа уникального message
        $this->validationRules = [
            'delivery_id' => 'required|numeric',
            'payment_id' => 'required|numeric',
        ];

        $this->validationMessages = [
            'required' => 'Обязательно для заполнения',
            'numeric' => 'Требуется число',
            'min' => 'Минимум :min символов',
            'email' => 'Email заполнен некорректно'
        ];

        if (!empty($this->order['delivery_id']) && empty($this->deliverValidationRules)) {
            $response = $this->getDeliveryValidationRules($this->order['delivery_id']);
            if (!empty($response['success'])) {
                $this->deliverValidationRules = $response['data']['validation_rules'];
                $this->validationRules = array_unique(
                    array_merge($this->validationRules, $this->deliverValidationRules)
                );
            }
        }

        $eventParams = [
            'key' => $key,
            'value' => $value,
            'controller' => $this,
        ];
        $response = $this->invokeEvent('msOnBeforeValidateOrderValue', $eventParams);
        $value = $response['data']['value'];

        if (!isset($this->validationRules[$key])) {
            return $this->success('', [
                'value' => $response['data']['value']
            ]);
        }

        $validator = new Validator();

        $validation = $validator->validate(
            [$key => $value],
            [$key => $this->validationRules[$key]],
            $this->validationMessages
        );

        $validation->validate();

        if ($validation->fails()) {
            $errors = $validation->errors();
            $eventParams = [
                'key' => $key,
                'value' => $value,
                'error' => $errors->firstOfAll(),
                'controller' => $this,
            ];
            $response = $this->invokeEvent('msOnErrorValidateOrderValue', $eventParams);
            if (!empty($response['data']['error'])) {
                return $this->error('', [
                    'error' => $response['data']['error']
                ]);
            }
        } else {
            $eventParams = [
                'key' => $key,
                'value' => $value,
                'controller' => $this,
            ];
            $response = $this->invokeEvent('msOnValidateOrderValue', $eventParams);
        }
        return $this->success('', [
            'value' => $response['data']['value']
        ]);
    }

    public function remove($key): bool
    {
        if (empty($this->order)) {
            $response = $this->get();
            if ($response['success']) {
                $this->order = $response['data']['order'];
            }
        }
        if ($exists = array_key_exists($key, $this->order)) {
            $response = $this->ms3->utils->invokeEvent('msOnBeforeRemoveFromOrder', [
                'key' => $key,
                'controller' => $this,
            ]);
            if (!$response['success']) {
                return $this->error($response['message']);
            }
            $this->updateDraft($key);
            $response = $this->ms3->utils->invokeEvent('msOnRemoveFromOrder', [
                'key' => $key,
                'controller' => $this,
            ]);
            if (!$response['success']) {
                return $this->error($response['message']);
            }
        }

        return $exists;
    }

    public function set(array $order): array
    {
        if (empty($this->order)) {
            $response = $this->get();
            if ($response['success']) {
                $this->order = $response['data']['order'];
            }
        }
        //TODO  Event before set
        //TODO Сообрать массив возможных ошибок валидации
        foreach ($order as $key => $value) {
            $this->add($key, $value);
        }
        // TODO event on set

        $data = [];
        $this->order = $this->getOrder();
        $data['order'] = $this->order;
        return $this->success('ms3_order_set_success', $data);
    }

    public function submit(array $data = []): array
    {
        if (empty($this->token)) {
            return $this->error('ms3_err_token');
        }
        $this->initDraft();

        if (empty($this->order)) {
            $response = $this->get();
            if ($response['success']) {
                $this->order = $response['data']['order'];
            }
        }

        $response = $this->ms3->utils->invokeEvent('msOnSubmitOrder', [
            'data' => $data,
            'controller' => $this,
        ]);
        if (!$response['success']) {
            return $this->error($response['message']);
        }
        if (!empty($response['data']['data'])) {
            $this->set($response['data']['data']);
        }

        $response = $this->getDeliveryRequiresFields();
        if (!$response['success']) {
            return $this->error($response['message']);
        }
        $requires = $response['data']['requires'];
        $errors = [];
        foreach ($requires as $k => $v) {
            if (empty($this->order[$k]) && empty($this->order['address_' . $k])) {
                $errors[] = $k;
            }
        }
        if (!empty($errors)) {
            return $this->error('ms3_order_err_requires', $errors);
        }

        //TODO Беру профиль msCustomer.  Но проверить, регистрируем ли пользователя?
//        $user_id = $this->ms2->getCustomerId();
        $user_id = 1;
        if (empty($user_id) || !is_int($user_id)) {
            return $this->error(is_string($user_id) ? $user_id : 'ms3_err_user_nf');
        }
        $this->ms3->cart->initialize($this->ctx, $this->token);
        $response = $this->ms3->cart->status();
        if (!$response['success']) {
            return $this->error($response['message']);
        }
        $cart_status = $response['data'];
        if (empty($cart_status['total_count'])) {
            return $this->error('ms3_order_err_empty');
        }

        $response = $this->getCost(false, true);
        if (!$response['success']) {
            return $this->error($response['message']);
        }
        $delivery_cost = $response['data']['cost'];
        $response = $this->getCost(true, true);
        if (!$response['success']) {
            return $this->error($response['message']);
        }
        $cart_cost = $response['data']['cost'] - $delivery_cost;

        $num = $this->getNewOrderNum();

        $this->draft->fromArray([
            'user_id' => $user_id,
            'updatedon' => time(),
            'num' => $num,
            'delivery_cost' => $delivery_cost,
            'cost' => $cart_cost + $delivery_cost,
        ]);

        $this->draft->Address->fromArray([
            'user_id' => $user_id,
            'updatedon' => time(),
        ]);
        $this->draft->save();

        $response = $this->ms3->utils->invokeEvent('msOnBeforeCreateOrder', [
            'msOrder' => $this->draft,
            'controller' => $this,
        ]);
        if (!$response['success']) {
            return $this->error($response['message']);
        }

        $response = $this->ms3->utils->invokeEvent('msOnCreateOrder', [
            'msOrder' => $this->draft,
            'controller' => $this,
        ]);

        if (!$response['success']) {
            return $this->error($response['message']);
        }
        if (empty($_SESSION['ms3']['orders'])) {
            $_SESSION['ms3']['orders'] = [];
        }
        $_SESSION['ms3']['orders'][] = $this->draft->get('id');

        // Trying to set status "new"
        $status_new = $this->modx->getOption('ms3_status_new', null, 1);
        $orderStatus = new OrderStatus($this->ms3);
        $response = $orderStatus->change($this->draft->get('id'), $status_new);

        if ($response !== true) {
            return $this->error($response, ['msorder' => $this->draft->get('id')]);
        }

        // Reload order object after changes in changeOrderStatus method

        /** @var msOrder $msOrder */
        $msOrder = $this->modx->getObject(msOrder::class, ['id' => $this->draft->get('id')]);
        $payment = $this->modx->getObject(
            msPayment::class,
            ['id' => $msOrder->get('payment_id'), 'active' => 1]
        );
        if (!$payment) {
            return $this->success('', ['msorder' => $msOrder->get('id')]);
        }

        // TODO  редирект на конкретный ID страницы, заданный через системную настройку или сниппет.
        $response = $payment->send($msOrder);
        if (!$response['success']) {
            return $this->error($response['message']);
        }
        if (!empty($response['data']['redirect'])) {
            return $response;
        }
        $thanks_id = $this->modx->getOption('ms3_order_redirect_thanks_id', null, 1);
        $redirect = $this->modx->makeUrl($thanks_id, $this->ctx, ['msorder' => $msOrder->get('id')]);
        $response['data']['redirect'] = $redirect;
        return $response;
    }

    public function clean(): array
    {
        if (empty($this->draft)) {
            $this->initDraft();
        }
        //TODO  Event before clean
        foreach ($this->draft->Address->_fields as $key => $value) {
            switch ($key) {
                case 'id':
                case 'order_id':
                case 'user_id':
                case 'createdon':
                    break;
                default:
                    $this->draft->Address->set($key, null);
            }
        }
        $this->draft->Address->save();
        $this->draft->set('updatedon', time());

        foreach ($this->draft->_fields as $key => $value) {
            switch ($key) {
                case 'id':
                case 'user_id':
                case 'token':
                case 'createdon':
                    break;
                default:
                    $this->draft->set($key, null);
            }
        }
        $this->draft->set('updatedon', time());
        $this->draft->save();

        // TODO event on clean

        return $this->success('ms3_order_clean_success');
    }

    /**
     * Returns the validation rules for delivery
     *
     * @param integer $delivery_id
     * @return array
     */
    public function getDeliveryValidationRules(int $delivery_id): array
    {
        if (empty($delivery_id)) {
            if (empty($this->order)) {
                $response = $this->get();
                if ($response['success']) {
                    $this->order = $response['data']['order'];
                }
            }
            $delivery_id = $this->order['delivery_id'];
        }
        if (empty($delivery_id)) {
            return $this->error('ms3_order_delivery_id_nf');
        }
        $q = $this->modx->newQuery(msDelivery::class);
        $q->where([
            'id' => $delivery_id,
            'active' => 1
        ]);
        $q->select('validation_rules');
        $q->prepare();
        $q->stmt->execute();
        $rules = $q->stmt->fetch(\PDO::FETCH_COLUMN);
        if (empty($rules)) {
            return $this->success('', ['validation_rules' => []]);
        }
        $rules = json_decode($rules, true);
        if (!is_array($rules)) {
            return $this->success('', ['validation_rules' => []]);
        }
        return $this->success('', ['validation_rules' => $rules]);
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
        if (empty($delivery_id)) {
            if (empty($this->order)) {
                $response = $this->get();
                if ($response['success']) {
                    $this->order = $response['data']['order'];
                }
            }

            $delivery_id = $this->order['delivery_id'];
        }
        $response = $this->getDeliveryValidationRules($delivery_id);
        if (!$response['success']) {
            if (isset($response['message'])) {
                return $this->error($response['message'], ['delivery']);
            } else {
                return $this->error('ms3_order_err_delivery', ['delivery']);
            }
        }
        $requires = array_filter($response['data']['validation_rules'], function ($rules) {
            return in_array('required', array_map('trim', explode("|", $rules)));
        }, ARRAY_FILTER_USE_BOTH);

        return $this->success('', ['requires' => $requires]);
    }

    protected function getOrder()
    {
        $Address = $this->draft->getOne('Address');
        $output = $this->draft->toArray();
        if (!empty($Address)) {
            $addressFields = [];
            foreach ($Address->toArray() as $key => $value) {
                $addressFields['address_' . $key] = $value;
            }
            $output = array_merge($output, $addressFields);
        }
        return $output;
    }

    protected function setDeliveryCost($delivery_cost)
    {
        $cart_cost = $this->draft->get('cart_cost');
        $cost = $cart_cost + $delivery_cost;

        $this->draft->set('delivery_cost', $delivery_cost);
        $this->draft->set('cost', $cost);
        $this->draft->save();
    }

    protected function updateDraft(string $key, mixed $value = null): bool
    {
        if (in_array($key, array_keys($this->draft->_fields))) {
            $this->draft->set($key, $value);
            $this->draft->set('updatedon', time());
            $this->draft->save();
            return true;
        }
        if (in_array($key, array_keys($this->draft->Address->_fields))) {
            $this->draft->Address->set($key, $value);
            $this->draft->Address->save();
            $this->draft->set('updatedon', time());
            $this->draft->save();
            return true;
        }
        // check msCustomer
        return false;
    }

    /**
     * Return current number of order
     *
     * @return string
     */
    public function getNewOrderNum(): string
    {
        $format = htmlspecialchars($this->modx->getOption('ms3_order_format_num', null, 'ym'));
        $separator = trim(
            preg_replace(
                "/[^,\/\-]/",
                '',
                $this->modx->getOption('ms3_order_format_num_separator', null, '/')
            )
        );
        $separator = $separator ?: '/';

        $cur = $format ? date($format) : date('ym');

        $count = 0;

        $c = $this->modx->newQuery(msOrder::class);
        $c->where(['num:LIKE' => "{$cur}%"]);
        $c->select('num');
        $c->sortby('id', 'DESC');
        $c->limit(1);
        if ($c->prepare() && $c->stmt->execute()) {
            $num = $c->stmt->fetchColumn();
            [, $count] = explode($separator, $num);
        }
        $count = intval($count) + 1;

        return sprintf('%s%s%d', $cur, $separator, $count);
    }
}

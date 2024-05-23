<?php

namespace ModxPro\MiniShop3\Controllers\Order;

use ModxPro\MiniShop3\Controllers\Order\OrderLog;
use ModxPro\MiniShop3\Controllers\Payment\Payment;
use ModxPro\MiniShop3\MiniShop3;
use ModxPro\MiniShop3\Model\msOrder;
use ModxPro\MiniShop3\Model\msOrderStatus;
use MODX\Revolution\modChunk;
use MODX\Revolution\modContextSetting;
use MODX\Revolution\modUserProfile;
use MODX\Revolution\modUserSetting;
use MODX\Revolution\modX;

class OrderStatus
{
    /** @var modX */
    public $modx;
    /** @var MiniShop3 */
    public $ms3;
    /**
     * @var OrderLog
     */
    private $orderLogController;

    public function __construct(MiniShop3 $ms3)
    {
        $this->ms3 = $ms3;
        $this->modx = $ms3->modx;
        $this->orderLogController = new OrderLog($ms3);

        $this->modx->lexicon->load('minishop3:default');
    }

    /**
     * Switch order status
     *
     * @param integer $order_id The id of msOrder
     * @param integer $status_id The id of msOrderStatus
     *
     * @return boolean|string
     */
    public function change($order_id, $status_id)
    {
        /** @var msOrder $order */
        $msOrder = $this->modx->getObject(msOrder::class, ['id' => $order_id]);
        if (!$msOrder) {
            return $this->modx->lexicon('ms3_err_order_nf');
        }

        $ctx = $msOrder->get('context');
        $this->modx->switchContext($ctx);
        $this->ms3->initialize($ctx);


        /** @var msOrderStatus $status */
        $status = $this->modx->getObject(msOrderStatus::class, ['id' => $status_id, 'active' => 1]);
        if (!$status) {
            return $this->modx->lexicon('ms3_err_status_nf');
        }
        /** @var msOrderStatus $old_status */
        $old_status = $this->modx->getObject(
            msOrderStatus::class,
            ['id' => $msOrder->get('status_id'), 'active' => 1]
        );
        if ($old_status) {
            if ($old_status->get('final')) {
                return $this->modx->lexicon('ms3_err_status_final');
            }
            if ($old_status->get('fixed')) {
                if ($status->get('position') <= $old_status->get('position')) {
                    return $this->modx->lexicon('ms3_err_status_fixed');
                }
            }
        }

        if ($msOrder->get('status_id') == $status_id) {
            return $this->modx->lexicon('ms3_err_status_same');
        }

        $eventParams = [
            'msOrder' => $msOrder,
            'status_id' => $msOrder->get('status_id'),
        ];
        $response = $this->ms3->utils->invokeEvent('msOnBeforeChangeOrderStatus', $eventParams);
        if (!$response['success']) {
            return $response['message'];
        }

        $msOrder->set('status_id', $status_id);

        if ($msOrder->save()) {
//            $this->orderLogController->process($msOrder->get('id'), 'status', $status_id);
//            $response = $this->ms3->utils->invokeEvent('msOnChangeOrderStatus', [
//                'msOrder' => $msOrder,
//                'status_id' => $status_id,
//            ]);
//            if (!$response['success']) {
//                return $response['message'];
//            }
//            $lang = $this->modx->getOption('cultureKey', null, 'en', true);
//            $tmp = $this->modx->getObject(
//                modUserSetting::class,
//                ['key' => 'cultureKey', 'user' => $msOrder->get('user_id')]
//            );
//            if ($tmp) {
//                $lang = $tmp->get('value');
//            } else {
//                $tmp = $this->modx->getObject(
//                    modContextSetting::class,
//                    ['key' => 'cultureKey', 'context_key' => $msOrder->get('context')]
//                );
//                if ($tmp) {
//                    $lang = $tmp->get('value');
//                }
//            }
//
//            $this->modx->setOption('cultureKey', $lang);
//            $this->modx->lexicon->load($lang . ':minishop3:default', $lang . ':minishop3:cart');
            //$this->sendEmails($msOrder, $status);
        }

        return true;
    }

    private function sendEmails($msOrder, $status)
    {
        $tv_list = $this->modx->getOption('ms3_order_tv_list', null, '');
        $pls = $msOrder->toArray();
        $pls['cost'] = $this->ms3->format->price($pls['cost']);
        $pls['cart_cost'] = $this->ms3->format->price($pls['cart_cost']);
        $pls['delivery_cost'] = $this->ms3->format->price($pls['delivery_cost']);
        $pls['weight'] = $this->ms3->format->weight($pls['weight']);
        $pls['payment_link'] = '';
        if (!empty($tv_list)) {
            $pls['includeTVs'] = $tv_list;
        }
        $payment = $msOrder->getOne('Payment');
        if ($payment) {
            $class = $payment->get('class');
            if (!empty($class)) {
                $this->ms3->loadCustomClasses('payment');
                if (class_exists($class)) {
                    /** @var Payment $controller */
                    $controller = new $class($msOrder);
                    if (method_exists($controller, 'getPaymentLink')) {
                        $link = $controller->getPaymentLink($msOrder);
                        $pls['payment_link'] = $link;
                    }
                }
            }
        }

        if ($status->get('email_manager')) {
            $subject = $this->ms3->pdoFetch->getChunk('@INLINE ' . $status->get('subject_manager'), $pls);
            $tpl = '';
            if ($chunk = $this->modx->getObject(modChunk::class, ['id' => $status->get('body_manager')])) {
                $tpl = $chunk->get('name');
            }
            $body = $this->modx->runSnippet('msGetOrder', array_merge($pls, ['tpl' => $tpl]));
            $emails = array_map(
                'trim',
                explode(
                    ',',
                    $this->modx->getOption('ms3_email_manager', null, $this->modx->getOption('emailsender'))
                )
            );
            if (!empty($subject)) {
                foreach ($emails as $email) {
                    if (preg_match('#.*?@.*#', $email)) {
                        $this->ms3->utils->sendEmail($email, $subject, $body);
                    }
                }
            }
        }

        if ($status->get('email_user')) {
            if ($profile = $this->modx->getObject(modUserProfile::class, ['internalKey' => $pls['user_id']])) {
                $subject = $this->ms3->pdoFetch->getChunk('@INLINE ' . $status->get('subject_user'), $pls);
                $tpl = '';
                if ($chunk = $this->modx->getObject(modChunk::class, ['id' => $status->get('body_user')])) {
                    $tpl = $chunk->get('name');
                }
                $body = $this->modx->runSnippet('msGetOrder', array_merge($pls, ['tpl' => $tpl]));
                $email = $profile->get('email');
                if (!empty($subject) && preg_match('#.*?@.*#', $email)) {
                    $this->ms3->utils->sendEmail($email, $subject, $body);
                }
            }
        }
    }
}

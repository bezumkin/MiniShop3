<?php

namespace ModxPro\MiniShop3\Processors\Settings\Payment;

use ModxPro\MiniShop3\Model\msPayment;
use MODX\Revolution\Processors\Model\UpdateProcessor;

class Update extends UpdateProcessor
{
    /** @var msPayment $object */
    public $object;
    public $classKey = msPayment::class;
    public $languageTopics = ['minishop3'];
    public $permission = 'mssetting_save';


    /**
     * @return bool|null|string
     */
    public function initialize()
    {
        if (!$this->modx->hasPermission($this->permission)) {
            return $this->modx->lexicon('access_denied');
        }

        return parent::initialize();
    }

    /**
     * @return bool
     */
    public function beforeSet()
    {
        $required = ['name'];
        foreach ($required as $field) {
            if (!$tmp = trim($this->getProperty($field))) {
                $this->addFieldError($field, $this->modx->lexicon('field_required'));
            } else {
                $this->setProperty($field, $tmp);
            }
        }
        $name = $this->getProperty('name');
        $count = $this->modx->getCount(
            $this->classKey,
            ['name' => $name, 'id:!=' => $this->object->get('id')]
        );
        if (!empty($count)) {
            $this->modx->error->addField('name', $this->modx->lexicon('ms3_err_ae'));
        }

        $prices = ['price'];
        foreach ($prices as $field) {
            if ($tmp = $this->getProperty($field)) {
                $tmp = $this->preparePrice($tmp);
                $this->setProperty($field, $tmp);
            }
        }

        return !$this->hasErrors();
    }

    public function preparePrice($price = 0)
    {
        $sign = '';
        $price = preg_replace(['#[^\d%\-,\.]#', '#,#'], ['', '.'], $price);
        if (strpos($price, '-') !== false) {
            $price = str_replace('-', '', $price);
            $sign = '-';
        }
        if (strpos($price, '%') !== false) {
            $price = str_replace('%', '', $price) . '%';
        }
        $price = $sign . $price;
        if (empty($price)) {
            $price = 0;
        }

        return $price;
    }
}

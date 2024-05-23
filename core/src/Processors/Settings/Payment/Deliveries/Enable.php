<?php

namespace ModxPro\MiniShop3\Processors\Settings\Payment\Deliveries;

use ModxPro\MiniShop3\Model\msDeliveryMember;
use ModxPro\MiniShop3\Model\msPayment;
use MODX\Revolution\Processors\Model\CreateProcessor;

class Enable extends CreateProcessor
{
    /** @var msPayment $object */
    public $object;
    public $classKey = msDeliveryMember::class;
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
    public function beforeSave()
    {
        $this->object->fromArray($this->getProperties(), '', true, true);

        return true;
    }
}

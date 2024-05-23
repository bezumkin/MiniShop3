<?php

namespace ModxPro\MiniShop3\Processors\Settings\Delivery\Payments;

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
     * @return bool
     */
    public function beforeSave()
    {
        $this->object->fromArray($this->getProperties(), '', true, true);

        return true;
    }
}

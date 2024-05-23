<?php

namespace ModxPro\MiniShop3\Processors\Product;

use ModxPro\MiniShop3\Model\msProduct;
use ModxPro\MiniShop3\Model\msVendor;
use ModxPro\MiniShop3\Utils\Utils;
use MODX\Revolution\Processors\Resource\Create as CreateProcessor;

class Create extends CreateProcessor
{
    public $classKey = msProduct::class;
    public $languageTopics = ['resource', 'minishop3:default'];
    public $permission = 'msproduct_save';
    public $beforeSaveEvent = 'OnBeforeDocFormSave';
    public $afterSaveEvent = 'OnDocFormSave';
    /** @var msProduct $object */
    public $object;

    /**
     * @return string
     */
    public function prepareAlias()
    {
        $id_as_alias = $this->workingContext->getOption('ms3_product_id_as_alias');
        if ($id_as_alias) {
            $alias = 'empty-resource-alias';
            $this->setProperty('alias', $alias);
        } else {
            $alias = parent::prepareAlias();
        }
        return $alias;
    }

    /**
     * @return array|string
     */
    public function beforeSet()
    {
        $this->setDefaultProperties([
            'show_in_tree' => $this->modx->getOption('ms3_product_show_in_tree_default', null, false),
            'hidemenu' => $this->modx->getOption('hidemenu_default', null, true),
            'source_id' => $this->modx->getOption('ms3_product_source_default', null, 1),
            'template' => $this->modx->getOption(
                'ms3_template_product_default',
                null,
                $this->modx->getOption('default_template')
            ),
        ]);

        $properties = $this->getProperties();
        $options = [];
        foreach ($properties as $key => $value) {
            if (strpos($key, 'options-') === 0) {
                $options[substr($key, 8)] = $value;
                $this->unsetProperty($key);
            }
        }
        $this->setProperty('options', $options);

        if (!empty($properties['vendor'])) {
            $vendor_id = Utils::getVendorId($this->modx, $properties['vendor']);
            if ($vendor_id) {
                $this->setProperty('vendor_id', $vendor_id);
            }
        }

        return parent::beforeSet();
    }

    /**
     * @return mixed
     */
    public function beforeSave()
    {
        $this->object->set('isfolder', false);
        return parent::beforeSave();
    }

    /**
     * @return mixed
     */
    public function afterSave()
    {
        if ($this->object->get('alias') == 'empty-resource-alias') {
            $this->object->set('alias', $this->object->get('id'));
            $this->object->save();
        }
        // Update resourceMap before OnDocSaveForm event
        $results = $this->modx->cacheManager->generateContext($this->object->get('context_key'));
        if (isset($results['resourceMap'])) {
            $this->modx->context->resourceMap = $results['resourceMap'];
        }
        if (isset($results['aliasMap'])) {
            $this->modx->context->aliasMap = $results['aliasMap'];
        }

        return parent::afterSave();
    }
}

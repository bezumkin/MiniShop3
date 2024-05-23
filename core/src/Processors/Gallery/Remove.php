<?php

namespace ModxPro\MiniShop3\Processors\Gallery;

use ModxPro\MiniShop3\MiniShop3;
use ModxPro\MiniShop3\Model\msProduct;
use ModxPro\MiniShop3\Model\msProductFile;
use MODX\Revolution\Processors\Model\RemoveProcessor;
use ModxPro\MiniShop3\Processors\Gallery\RemoveCatalogs;

class Remove extends RemoveProcessor
{
    public $classKey = msProductFile::class;
    public $languageTopics = ['minishop3:default'];
    public $permission = 'msproductfile_save';

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
     * @return array|string
     */
    public function process()
    {
        parent::process();

        /** @var msProduct $product */
        $product = $this->object->getOne('Product');
        $thumb = '';
        if ($product) {
            $thumb = $product->updateProductImage();
        }

        /** @var MiniShop3 $ms3 */
        $ms3 = $this->modx->services->get('ms3');
        if (empty($thumb)) {
            $thumb = $ms3->config['defaultThumb'];
        }

        if (empty($product->getMany('Files'))) {
            RemoveCatalogs::process($this->modx, $product->get('id'));
        }

        return $this->success('', ['thumb' => $thumb]);
    }
}

<?php

namespace ModxPro\MiniShop3\Processors\Gallery;

use ModxPro\MiniShop3\MiniShop3;
use ModxPro\MiniShop3\Model\msProductData;
use ModxPro\MiniShop3\Model\msProductFile;
use MODX\Revolution\Processors\ModelProcessor;
use ModxPro\MiniShop3\Processors\Gallery\RemoveCatalogs;

class RemoveAll extends ModelProcessor
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
        $product_id = (int)$this->getProperty('product_id');
        if (empty($product_id)) {
            return $this->failure($this->modx->lexicon('ms3_gallery_err_ns'));
        }

        $files = $this->modx->getCollection(msProductFile::class, ['product_id' => $product_id, 'parent_id' => 0]);
        /** @var msProductFile $file */
        foreach ($files as $file) {
            $file->remove();
        }

        /** @var msProductData $product */
        $product = $this->modx->getObject(msProductData::class, ['id' => $product_id]);
        if ($product) {
            $thumb = $product->updateProductImage();
            /** @var MiniShop3 $ms3 */
            $ms3 = $this->modx->services->get('ms3');
            if (empty($thumb)) {
                $thumb = $ms3->config['defaultThumb'];
            }
            return $this->success('', ['thumb' => $thumb]);
        }

        if (empty($product->getMany('Files'))) {
            RemoveCatalogs::process($this->modx, $product_id);
        }

        return $this->success();
    }
}

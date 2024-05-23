<?php

namespace ModxPro\MiniShop3\Processors\Gallery;

use ModxPro\MiniShop3\Model\msProductData;
use ModxPro\MiniShop3\Model\msProductFile;
use MODX\Revolution\Processors\ModelProcessor;

class Generate extends ModelProcessor
{
    public $classKey = msProductFile::class;
    public $languageTopics = ['minishop3:default'];
    public $permission = 'msproductfile_generate';


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
        $id = (int)$this->getProperty('id');
        if (empty($id)) {
            return $this->failure($this->modx->lexicon('ms3_gallery_err_ns'));
        }

        /** @var msProductFile $file */
        $file = $this->modx->getObject(msProductFile::class, $id);
        if ($file) {
            $children = $file->getMany('Children');
            /** @var msProductFile $child */
            foreach ($children as $child) {
                $child->remove();
            }
            $file->generateThumbnails();

            $thumb = $file->getFirstThumbnail();
            /** @var msProductData $product */
            $product = $this->modx->getObject(msProductData::class, ['id' => $file->get('product_id')]);
            $product->set('thumb', $thumb['url']);
            if ($product->save()) {
                return $this->success();
            }
        }

        return $this->success();
    }
}

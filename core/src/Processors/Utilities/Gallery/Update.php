<?php

namespace ModxPro\MiniShop3\Processors\Utilities\Gallery;

use ModxPro\MiniShop3\MiniShop3;
use ModxPro\MiniShop3\Model\msProduct;
use ModxPro\MiniShop3\Model\msProductData;
use ModxPro\MiniShop3\Model\msProductFile;
use MODX\Revolution\Processors\Processor;
use PDO;

class Update extends Processor
{
    public $classKey = msProductFile::class;
    public $languageTopics = ['minishop3:default', 'minishop3:manager'];
    public $permission = 'msproductfile_generate';

    /** @var MiniShop3 $ms3 */
    protected $ms3;

    protected $limit = 10;
    protected $offset = 0;
    protected $total = 0;


    /**
     * @return bool|null|string
     */
    public function initialize()
    {
        $this->ms3 = $this->modx->services->get('ms3');

        return parent::initialize();
    }

    /**
     * {@inheritDoc}
     */
    public function checkPermissions()
    {
        return !empty($this->permission) ? $this->modx->hasPermission($this->permission) : true;
    }

    /**
     * {@inheritDoc}
     */
    public function getLanguageTopics()
    {
        return $this->languageTopics;
    }


    /**
     * {@inheritDoc}
     */
    public function process()
    {
        $this->limit = (int)$this->getProperty('limit', 10);
        $this->offset = (int)$this->getProperty('offset', 0);

        $c = $this->modx->newQuery(msProduct::class);
        $c->sortby('id', 'ASC');
        $c->where(['class_key' => msProduct::class]);
        $c->select('msProduct.id');

        $this->total = $this->modx->getCount(msProduct::class, $c);
        $c->limit($this->limit, $this->offset);

        $products = [];
        if ($c->prepare() && $c->stmt->execute()) {
            $products = $c->stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        if (!is_array($products) || empty($products)) {
            return $this->failure($this->modx->lexicon('ms3_utilities_gallery_err_noproducts'));
        }

        $i = 0;
        foreach ($products as $product) {
            $this->generateThumbnails($product['id']);
            $i++;
        }

        $offset = $this->offset + $this->limit;
        $done = $offset >= $this->total;

        return $this->success('', [
            'updated' => $i,
            'offset' => $done ? 0 : $offset,
            'done' => $done,
            'total' => $this->total,
            'limit' => $this->limit,
        ]);
    }


    public function generateThumbnails($product_id)
    {
        if (empty($product_id)) {
            return $this->failure($this->modx->lexicon('ms3_gallery_err_ns'));
        }

        $files = $this->modx->getCollection(msProductFile::class, ['product_id' => $product_id, 'parent_id' => 0]);
        /** @var msProductFile $file */
        foreach ($files as $file) {
            $children = $file->getMany('Children');
            /** @var msProductFile $child */
            foreach ($children as $child) {
                $child->remove();
            }
            $file->generateThumbnails();
        }

        /** @var msProductData $product */
        $product = $this->modx->getObject(msProductData::class, ['id' => $product_id]);
        if ($product) {
            $thumb = $product->updateProductImage();
            if (empty($thumb) && $this->ms3) {
                $thumb = $this->ms3->config['defaultThumb'];
            }
            return $this->success('', ['thumb' => $thumb]);
        }

        return $this->success();
    }
}

<?php

namespace ModxPro\MiniShop3\Model;

use ModxPro\MiniShop3\MiniShop3;
use MODX\Revolution\modPhpThumb;
use MODX\Revolution\modX;
use MODX\Revolution\Sources\modMediaSource;
use ModxPro\MiniShop3\Model\msProduct;
use xPDO\Om\xPDOSimpleObject;
use xPDO\xPDO;

/**
 * Class msProductFile
 *
 * @property integer $product_id
 * @property integer $source_id
 * @property integer $parent_id
 * @property string $name
 * @property string $description
 * @property string $path
 * @property string $file
 * @property string $type
 * @property string $createdon
 * @property integer $createdby
 * @property integer $position
 * @property string $url
 * @property array $properties
 * @property string $hash
 * @property integer $active
 *
 * @propertymsProductFile[] $Children
 *
 * @package MiniShop3\Model
 */
class msProductFile extends xPDOSimpleObject
{
    public $file;
    /** @var modPhpThumb $phpThumb */
    public $phpThumb;
    /** @var modMediaSource $mediaSource */
    public $mediaSource;
    /** @var MiniShop3 $ms3 */
    public $ms3;

    /**
     * msProductFile constructor.
     *
     * @param xPDO $xpdo
     */
    public function __construct(xPDO $xpdo)
    {
        parent::__construct($xpdo);
        if ($this->xpdo->services->has('ms3')) {
            $this->ms3 = $this->xpdo->services->get('ms3');
        }
    }

    /**
     * @param modMediaSource $mediaSource
     *
     * @return bool|string
     */
    public function prepareSource(modMediaSource $mediaSource = null)
    {
        if ($mediaSource) {
            $this->mediaSource = $mediaSource;

            return true;
        } elseif (is_object($this->mediaSource) && $this->mediaSource instanceof modMediaSource) {
            return true;
        } else {
            /** @var msProduct $product */
            $product = $this->xpdo->getObject(msProduct::class, ['id' => $this->get('product_id')]);
            if ($product) {
                $this->mediaSource = $product->initializeMediaSource();
                if (!$this->mediaSource || !($this->mediaSource instanceof modMediaSource)) {
                    return '[miniShop3] Could not initialize media source for product with id = ' . $this->get(
                            'product_id'
                        );
                }

                return true;
            } else {
                return '[miniShop3] Could not find product with id = ' . $this->get('product_id');
            }
        }
    }

    /**
     * @param null $cacheFlag
     *
     * @return bool
     */
    public function save($cacheFlag = null)
    {
        if ($this->isDirty('position')) {
            $table = $this->xpdo->getTableName(msProductFile::class);
            $this->xpdo->exec(
                "UPDATE {$table} SET `position` = {$this->get('position')} WHERE parent_id = {$this->id}"
            );
        }

        return parent::save($cacheFlag);
    }

    /**
     * @param string $file
     * @param bool $isRaw
     *
     * @return string
     */
    public function generateHash($file = '', $isRaw = false)
    {
        $raw = '';
        if ($isRaw) {
            $raw = $file;
        } else {
            if (file_exists($file)) {
                $res = fopen($file, 'rb');
                $raw = fread($res, 80000);
                fclose($res);
            }
        }

        return sha1($raw);
    }

    /**
     * @param modMediaSource $mediaSource
     *
     * @return bool|string
     */
    public function generateThumbnails(modMediaSource $mediaSource = null)
    {
        if ($this->get('type') != 'image' || $this->get('parent_id') != 0) {
            return true;
        }

        $prepare = $this->prepareSource($mediaSource);
        if ($prepare !== true) {
            return $prepare;
        }
        $this->mediaSource->errors = [];
        $filename = $this->get('path') . $this->get('file');
        $info = $this->mediaSource->getObjectContents($filename);
        if (!is_array($info)) {
            return "[miniShop3] Could not retrieve contents of file {$filename} from media source.";
        } elseif (!empty($this->mediaSource->errors['file'])) {
            return "[miniShop3] Could not retrieve file {$filename} from media source: " . $this->mediaSource->errors['file'];
        }

        $properties = $this->mediaSource->getProperties();
        $thumbnails = [];
        if (array_key_exists('thumbnails', $properties) && !empty($properties['thumbnails']['value'])) {
            $thumbnails = json_decode($properties['thumbnails']['value'], true);
        }

        if (empty($thumbnails)) {
            $thumbnails = [
                [
                    'w' => 120,
                    'h' => 90,
                    'q' => 90,
                    'zc' => 'T',
                    'bg' => '000000',
                    'f' => !empty($properties['thumbnailType']['value'])
                        ? $properties['thumbnailType']['value']
                        : 'jpg',
                ],
            ];
        }

        foreach ($thumbnails as $k => $options) {
            if (empty($options['f'])) {
                $options['f'] = !empty($properties['thumbnailType']['value'])
                    ? $properties['thumbnailType']['value']
                    : 'jpg';
            }
            if (empty($options['name']) && !is_numeric($k)) {
                $options['name'] = $k;
            }
            if ($image = $this->makeThumbnail($options, $info)) {
                $this->saveThumbnail($image, $options);
            }
        }

        return true;
    }

    /**
     * @param array $options
     * @param array $info
     *
     * @return bool|null
     */
    public function makeThumbnail($options = [], array $info)
    {
        $phpThumb = new modPhpThumb($this->xpdo);
        $phpThumb->initialize();

        $tf = tempnam(MODX_BASE_PATH, 'ms3_');
        file_put_contents($tf, $info['content']);
        $phpThumb->setSourceFilename($tf);

        foreach ($options as $k => $v) {
            $phpThumb->setParameter($k, $v);
        }

        $output = false;
        if ($phpThumb->GenerateThumbnail() && $phpThumb->RenderOutput()) {
            $this->xpdo->log(
                modX::LOG_LEVEL_INFO,
                '[miniShop3] phpThumb messages for "' . $this->get('url') .
                '". ' . print_r($phpThumb->debugmessages, true)
            );
            $output = $phpThumb->outputImageData;
        } else {
            $this->xpdo->log(
                modX::LOG_LEVEL_ERROR,
                '[miniShop3] Could not generate thumbnail for "' .
                $this->get('url') . '". ' . print_r($phpThumb->debugmessages, true)
            );
        }

        if (file_exists($phpThumb->sourceFilename)) {
            @unlink($phpThumb->sourceFilename);
        }
        @unlink($tf);

        return $output;
    }

    /**
     * @param $raw_image
     * @param array $options
     *
     * @return bool
     */
    public function saveThumbnail($raw_image, $options = [])
    {
        $filename = $this->ms3->utils->pathinfo($this->get('file'), 'filename') . '.' . $options['f'];
        if (!empty($options['name'])) {
            $thumb_dir = preg_replace('#[^\w]#', '', $options['name']);
        }
        if (empty($thumb_dir)) {
            $thumb_dir = $options['w'] . 'x' . $options['h'];
        }
        $path = $this->get('path') . $thumb_dir . '/';

        /** @var msProductFile $product_file */
        $product_file = $this->xpdo->newObject(msProductFile::class, [
            'product_id' => $this->get('product_id'),
            'parent_id' => $this->get('id'),
            'name' => $this->get('name'),
            'file' => $filename,
            'path' => $path,
            'source_id' => $this->mediaSource->get('id'),
            'type' => $this->get('type'),
            'position' => $this->get('position'),
            'createdon' => date('Y-m-d H:i:s'),
            'createdby' => $this->xpdo->user->id,
            'active' => 1,
            'hash' => $this->generateHash($raw_image, true),
            'properties' => [
                'size' => strlen($raw_image),
            ],
        ]);

        $tf = tempnam(MODX_BASE_PATH, 'ms3_');
        file_put_contents($tf, $raw_image);
        $tmp = getimagesize($tf);
        if (is_array($tmp)) {
            $product_file->set(
                'properties',
                array_merge(
                    $product_file->get('properties'),
                    [
                        'width' => $tmp[0],
                        'height' => $tmp[1],
                        'bits' => $tmp['bits'],
                        'mime' => $tmp['mime'],
                    ]
                )
            );
        }
        unlink($tf);

        $this->mediaSource->createContainer($product_file->get('path'), '/');
        $file = $this->mediaSource->createObject(
            $product_file->get('path'),
            $product_file->get('file'),
            $raw_image
        );

        if ($file) {
            $product_file->set(
                'url',
                $this->mediaSource->getObjectUrl(
                    $product_file->get('path') . $product_file->get('file')
                )
            );

            return $product_file->save();
        }

        return false;
    }

    /**
     * @return array|mixed
     */
    public function getFirstThumbnail()
    {
        $c = $this->xpdo->newQuery(msProductFile::class, [
            'product_id' => $this->get('product_id'),
            'parent_id:>' => 0,
            'type' => 'image',
        ]);
        $c->limit(1);
        $c->sortby('position', 'ASC');
        $c->sortby('`id`', 'ASC');
        $c->select('id,url');

        $res = [];
        if ($c->prepare() && $c->stmt->execute()) {
            $res = $c->stmt->fetch(\PDO::FETCH_ASSOC);
        }

        return $res;
    }

    /**
     * @param array $ancestors
     *
     * @return bool
     */
    public function remove(array $ancestors = [])
    {
        $this->prepareSource();
        if (!$this->mediaSource->removeObject($this->get('path') . $this->get('file'))) {
            /* $this->xpdo->log(xPDO::LOG_LEVEL_ERROR,
                'Could not remove file at "' . $this->get('path') . $this->get('file') . '": ' .
                $this->mediaSource->errors['file']
            );*/
        }

        $children = $this->xpdo->getIterator(msProductFile::class, ['parent_id' => $this->get('id')]);
        /** @var msProductFile $child */
        foreach ($children as $child) {
            $child->remove();
        }

        return parent::remove($ancestors);
    }

    /**
     * Recursive file rename
     *
     * @param string $new_name
     * @param string $old_name
     *
     * @return bool
     */
    public function rename($new_name, $old_name = '')
    {
        if (empty($old_name)) {
            $old_name = $this->get('file');
        }

        $path = $this->get('path');
        $extension = strtolower(pathinfo($old_name, PATHINFO_EXTENSION));
        $name = preg_replace('#\.' . $extension . '$#', '', $new_name);
        $name .= '.' . $extension;

        // Process children
        if ($children = $this->getMany('Children')) {
            /** @var msProductFile $child */
            foreach ($children as $child) {
                $child->rename($new_name, $child->get('file'));
            }
        }

        // Rename
        $this->prepareSource();
        if ($this->mediaSource->renameObject($path . $old_name, $name)) {
            $this->set('file', $name);
            $this->set('url', $this->mediaSource->getObjectUrl($path . $name));

            return $this->save();
        }

        return false;
    }
}

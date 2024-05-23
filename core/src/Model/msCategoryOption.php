<?php

namespace ModxPro\MiniShop3\Model;

use ModxPro\MiniShop3\Model\msProduct;
use ModxPro\MiniShop3\Model\msProductOption;
use xPDO\Om\xPDOObject;

/**
 * Class msCategoryOption
 *
 * @property integer $option_id
 * @property integer $category_id
 * @property integer $position
 * @property boolean $active
 * @property boolean $required
 * @property string $value
 *
 * @package MiniShop3\Model
 */
class msCategoryOption extends xPDOObject
{
    /**
     * Create option values for product in category
     *
     * @param null $cacheFlag
     *
     * @return bool
     */
    public function save($cacheFlag = null)
    {
        $save = parent::save();
        $q = $this->xpdo->newQuery(msProduct::class, ['parent' => $this->get('category_id')]);
        $q->select('id');
        if ($q->prepare() && $q->stmt->execute()) {
            $products = $q->stmt->fetchAll(\PDO::FETCH_COLUMN);
            $value = $this->get('value');
            $key = $this->getOne('Option')->get('key');
            foreach ($products as $id) {
                $po = $this->xpdo->getObject(msProductOption::class, ['key' => $key, 'product_id' => $id]);
                // дефолтные значения применяются только к тем товарам, у которых их еще нет
                if (!$po) {
                    /* @TODO вызывать метод msOption для поддержки множественных типов */
                    $po = $this->xpdo->newObject(msProductOption::class);
                    $po->set('product_id', $id);
                    $po->set('key', $key);
                    $po->set('value', $value);
                    $po->save();
                }
            }
        }
        return $save;
    }

    /**
     * Delete option values for product in category while remove option from category
     *
     * @param array $ancestors
     *
     * @return bool
     */
    public function remove(array $ancestors = [])
    {
        $q = $this->xpdo->newQuery(msProduct::class, ['parent' => $this->get('category_id')]);
        $q->select('id');
        if ($q->prepare() && $q->stmt->execute()) {
            $products = $q->stmt->fetchAll(\PDO::FETCH_COLUMN);
            $products = implode(',', $products);
            $key = $this->getOne('Option')->get('key');
            $key = $this->xpdo->quote($key);
            if (!empty($products)) {
                $sql = "DELETE FROM {$this->xpdo->getTableName(msProductOption::class)} WHERE `product_id` IN ({$products}) AND `key`={$key};";
                $stmt = $this->xpdo->prepare($sql);
                $stmt->execute();
                $stmt->closeCursor();
            }
        }

        return parent::remove($ancestors);
    }
}

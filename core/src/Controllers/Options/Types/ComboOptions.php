<?php

namespace ModxPro\MiniShop3\Controllers\Options\Types;

use ModxPro\MiniShop3\Controllers\Options\Types\msOptionType;
use ModxPro\MiniShop3\Model\msProductOption;

class ComboOptions extends msOptionType
{

    /**
     * @param $field
     *
     * @return string
     */
    public function getField($field)
    {
        return "{xtype:'ms3-combo-options'}";
    }

    /**
     * @param $criteria
     *
     * @return array
     */
    public function getValue($criteria)
    {
        $result = [];

        $c = $this->xpdo->newQuery(msProductOption::class, $criteria);
        $c->select('value');
        $c->where(['value:!=' => '']);
        if ($c->prepare() && $c->stmt->execute()) {
            if (!$result = $c->stmt->fetchAll(\PDO::FETCH_ASSOC)) {
                $result = [];
            }
        }

        return $result;
    }

    /**
     * @param $criteria
     *
     * @return array
     */
    public function getRowValue($criteria)
    {
        $result = [];

        $rows = $this->getValue($criteria);
        foreach ($rows as $row) {
            $result[] = $row['value'];
        }

        return $result;
    }
}

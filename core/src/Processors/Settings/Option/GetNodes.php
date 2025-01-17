<?php

namespace ModxPro\MiniShop3\Processors\Settings\Option;

use ModxPro\MiniShop3\Model\msCategoryOption;
use ModxPro\MiniShop3\Model\msOption;
use ModxPro\MiniShop3\Processors\Category\GetNodes as CategoryNodes;
use MODX\Revolution\modResource;

class GetNodes extends CategoryNodes
{
    protected $categories = [];


    /**
     * @return bool
     */
    public function initialize()
    {
        if ($categories = $this->getProperty('categories')) {
            $this->categories = json_decode($categories, true);
        } elseif ($options = $this->getProperty('options')) {
            $options = json_decode($options, true);
            if (is_array($options) && count($options) === 1) {
                /** @var msOption $option */
                if ($option = $this->modx->getObject('msOption', ['id' => $options[0]])) {
                    $categories = $option->getMany('OptionCategories');
                    $tmp = [];
                    /** @var msCategoryOption $cat */
                    foreach ($categories as $cat) {
                        $category = $cat->getOne('Category');
                        if ($category) {
                            $tmp[] = $category->get('id');
                        }
                    }
                    $this->categories = $tmp;
                }
            }
        }

        return parent::initialize();
    }


    /**
     * @param modResource $resource
     *
     * @return array
     */
    public function prepareResourceNode(modResource $resource)
    {
        $node = parent::prepareResourceNode($resource);
        if (!empty($this->categories[$node['pk']])) {
            $node['checked'] = true;
        }

        return $node;
    }
}

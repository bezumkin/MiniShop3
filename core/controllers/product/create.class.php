<?php

use ModxPro\MiniShop3\Controllers\msResourceCreateController;
use ModxPro\MiniShop3\Model\msProduct;

class msProductCreateManagerController extends msResourceCreateController
{
    /** @var msProduct $resource */
    public $resource;

    /**
     * Returns language topics
     * @return array
     */
    public function getLanguageTopics()
    {
        return ['resource', 'minishop3:default', 'minishop3:product', 'minishop3:manager'];
    }

    /**
     * @return int|mixed
     */
    public function getDefaultTemplate()
    {
        parent::getDefaultTemplate();;
        if (!$template = $this->getOption('ms3_template_product_default')) {
            $template = parent::getDefaultTemplate();
        }

        return $template;
    }

    /**
     * Check for any permissions or requirements to load page
     * @return bool
     */
    public function checkPermissions()
    {
        return $this->modx->hasPermission('new_document');
    }

    /**
     * @param array $scriptProperties
     *
     * @return mixed
     */
    public function process(array $scriptProperties = [])
    {
        $placeholders = parent::process($scriptProperties);

        $this->resourceArray['show_in_tree'] = (int)$this->getOption('ms3_product_show_in_tree_default');
        $this->resourceArray['source'] = (int)$this->getOption('ms3_product_source_default');

        return $placeholders;
    }

    /**
     * Register custom CSS/JS for the page
     * @return void
     */
    public function loadCustomCssJs()
    {
        $mgrUrl = $this->getOption('manager_url', null, MODX_MANAGER_URL);
        $assetsUrl = $this->ms3->config['assetsUrl'];

        $this->addCss($assetsUrl . 'css/mgr/main.css');
        $this->addJavascript($mgrUrl . 'assets/modext/util/datetime.js');
        $this->addJavascript($mgrUrl . 'assets/modext/widgets/element/modx.panel.tv.renders.js');
        $this->addJavascript($mgrUrl . 'assets/modext/widgets/resource/modx.grid.resource.security.local.js');
        $this->addJavascript($mgrUrl . 'assets/modext/widgets/resource/modx.panel.resource.tv.js');
        $this->addJavascript($mgrUrl . 'assets/modext/widgets/resource/modx.panel.resource.js');
        $this->addJavascript($mgrUrl . 'assets/modext/sections/resource/create.js');
        $this->addJavascript($assetsUrl . 'js/mgr/minishop3.js');
        $this->addJavascript($assetsUrl . 'js/mgr/misc/sortable/sortable.min.js');
        $this->addJavascript($assetsUrl . 'js/mgr/misc/ms3.combo.js');
        $this->addJavascript($assetsUrl . 'js/mgr/misc/ms3.utils.js');
        $this->addLastJavascript($assetsUrl . 'js/mgr/product/category.tree.js');
        $this->addLastJavascript($assetsUrl . 'js/mgr/product/product.common.js');
        $this->addLastJavascript($assetsUrl . 'js/mgr/product/create.js');

        // Customizable product fields feature
        $product_fields = array_merge($this->resource->getAllFieldsNames(), ['syncsite']);
        $product_data_fields = $this->resource->getDataFieldsNames();

        if (!$product_main_fields = $this->getOption('ms3_product_main_fields')) {
            $product_main_fields = 'pagetitle,longtitle,introtext,content,publishedon,pub_date,unpub_date,template,
                parent,alias,menutitle,searchable,cacheable,richtext,uri_override,uri,hidemenu,show_in_tree';
        }
        $product_main_fields = array_map('trim', explode(',', $product_main_fields));
        $product_main_fields = array_values(array_intersect($product_main_fields, $product_fields));

        if (!$product_extra_fields = $this->getOption('ms3_product_extra_fields')) {
            $product_extra_fields = 'article,price,old_price,weight,color,remains,reserved,vendor,made_in,tags';
        }
        $product_extra_fields = array_map('trim', explode(',', $product_extra_fields));
        $product_extra_fields = array_values(array_intersect($product_extra_fields, $product_fields));
        $product_option_fields = $this->resource->loadData()->getOptionFields();

        $config = [
            'assets_url' => $this->ms3->config['assetsUrl'],
            'connector_url' => $this->ms3->config['connectorUrl'],
            'show_gallery' => (bool)$this->getOption('ms3_product_tab_gallery', null, true),
            'show_extra' => (bool)$this->getOption('ms3_product_tab_extra', null, true),
            'show_options' => (bool)$this->getOption('ms3_product_tab_options', null, true),
            'show_links' => (bool)$this->getOption('ms3_product_tab_links', null, true),
            'show_categories' => (bool)$this->getOption('ms3_product_tab_categories', null, true),
            'default_thumb' => $this->ms3->config['defaultThumb'],
            'main_fields' => $product_main_fields,
            'extra_fields' => $product_extra_fields,
            'option_fields' => $product_option_fields,
            'product_tab_extra' => (bool)$this->getOption('ms3_product_tab_extra', null, true),
            'product_tab_gallery' => (bool)$this->getOption('ms3_product_tab_gallery', null, true),
            'product_tab_links' => (bool)$this->getOption('ms3_product_tab_links', null, true),
            'data_fields' => $product_data_fields,
            'additional_fields' => [],
            'isHideContent' => $this->isHideContent(),
        ];

        $ready = [
            'xtype' => 'ms3-page-product-create',
            'resource' => $this->resource->get('id'),
            'record' => $this->resourceArray,
            'publish_document' => $this->canPublish,
            'canSave' => $this->canSave,
            'canEdit' => $this->canEdit,
            'canCreate' => $this->canCreate,
            'canDuplicate' => $this->canDuplicate,
            'canDelete' => $this->canDelete,
            'canPublish' => $this->canPublish,
            'show_tvs' => !empty($this->tvCounts),
            'mode' => 'create',
        ];

        $this->addHtml(
            '
        <script>
        // <![CDATA[
        MODx.config.publish_document = "' . $this->canPublish . '";
        MODx.onDocFormRender = "' . $this->onDocFormRender . '";
        MODx.ctx = "' . $this->ctx . '";
        ms3.config = ' . json_encode($config) . ';
        Ext.onReady(function() {
            MODx.load(' . json_encode($ready) . ');
        });
        // ]]>
        </script>'
        );

        // load RTE
        $this->loadRichTextEditor();
        $this->modx->invokeEvent('msOnManagerCustomCssJs', ['controller' => &$this, 'page' => 'product_create']);
        //$this->loadPlugins();
    }

//    /**
//     * Loads additional scripts for product form from miniShop2 plugins
//     */
//    public function loadPlugins()
//    {
//        $plugins = $this->ms3->plugins->load();
//        foreach ($plugins as $plugin) {
//            if (!empty($plugin['manager']['msProductData'])) {
//                $this->addJavascript($plugin['manager']['msProductData']);
//            }
//        }
//    }
}

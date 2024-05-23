<?php

use ModxPro\MiniShop3\Controllers\msManagerController;

class MiniShop3MgrHelpManagerController extends msManagerController
{
    /**
     * @return string
     */
    public function getPageTitle()
    {
        return $this->modx->lexicon('ms3_help') . ' | MiniShop3';
    }

    /**
     * @return array
     */
    public function getLanguageTopics()
    {
        return ['minishop3:help'];
    }

    /**
     *
     */
    public function loadCustomCssJs()
    {
        $this->addCss($this->ms3->config['cssUrl'] . 'mgr/help.css');
    }

    /**
     * @param array $scriptProperties
     * @return mixed
     */
    public function process(array $scriptProperties = [])
    {
        $placeholders = [];
        $placeholders['logo'] = $this->ms3->config['defaultThumb'];
        $placeholders['changelog'] = file_get_contents(dirname(__FILE__, 3) . '/docs/changelog.txt');

        return $placeholders;
    }

    /**
     * @return string
     */
    public function getTemplateFile()
    {
        return dirname(__FILE__, 3) . '/templates/default/help.tpl';
    }
}

<?php

use MODX\Revolution\modConnectorRequest;
use ModxPro\MiniShop3\Model\msCategory;
use ModxPro\MiniShop3\Model\msProduct;

if (file_exists('/modx/config.core.php')) {
    require '/modx/config.core.php';
} else {
    $dir = __DIR__;
    while (true) {
        if ($dir === '/') {
            break;
        }
        if (file_exists($dir . '/config.core.php')) {
            require $dir . '/config.core.php';
            break;
        }
        $dir = dirname($dir);
    }
}
if (!defined('MODX_CORE_PATH')) {
    exit('Could not load MODX core');
}

require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CONNECTORS_PATH . 'index.php';

/** @var modX $modx */
/** @var ModxPro\MiniShop3\MiniShop3 $ms3 */
$ms3 = $modx->services->get('ms3');
$modx->lexicon->load('minishop3:default', 'minishop3:manager');

$path = $modx->getOption('processorsPath', $ms3->config, MODX_CORE_PATH . 'components/minishop3/src/Processors/');

if (!empty($_REQUEST['class_key'])) {
    $action = $_REQUEST['action'];
    $tmp = explode('/', $action);
    $action = $tmp[count($tmp) - 1];

    switch ($_REQUEST['class_key']) {
        case msProduct::class:
            $_REQUEST['action'] = 'ModxPro\MiniShop3\Processors\Product\\' . $action;
            break;
        case msCategory::class:
            $_REQUEST['action'] = 'ModxPro\MiniShop3\Processors\Category\\' . $action;
            break;
    }

    if ($action === 'Reload') {
        $_REQUEST['action'] = 'Resource/Reload';
    }
}

/** @var modConnectorRequest $request */
$request = $modx->request;
$request->handleRequest([
    'processors_path' => $path,
    'location' => '',
]);

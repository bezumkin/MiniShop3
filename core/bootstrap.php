<?php

if (!defined('MODX_CORE_PATH')) {
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
            if (file_exists($dir . '/config/config.inc.php')) {
                require $dir . '/config/config.inc.php';
                break;
            }
            $dir = dirname($dir);
        }
    }
    if (!defined('MODX_CORE_PATH')) {
        exit('Could not load MODX core');
    }
    require MODX_CORE_PATH . '/vendor/autoload.php';
}

/** @var MODX\Revolution\modX $modx */
if (!isset($modx)) {
    $modx = new MODX\Revolution\modX();
    $modx->initialize();
}

/** @var \MODX\Revolution\modX $modx */
if (!$modx->services->has('ms3')) {
    $modx->services->add('ms3', function () use ($modx) {
        return new ModxPro\MiniShop3\MiniShop3($modx);
    });
}
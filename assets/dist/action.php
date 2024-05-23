<?php

if (empty($_REQUEST['action']) && empty($_REQUEST['ms3_action'])) {
    http_response_code(403);
}

if (!empty($_REQUEST['action'])) {
    $_REQUEST['ms3_action'] = $_REQUEST['action'];
}

if (file_exists('/modx/index.php')) {
    require '/modx/index.php';
} else {
    $dir = __DIR__;
    while (true) {
        if ($dir === '/') {
            break;
        }
        if (file_exists($dir . '/index.php')) {
            require $dir . '/index.php';
            break;
        }
        $dir = dirname($dir);
    }
}
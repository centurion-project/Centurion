<?php

if (isset($_SERVER['REDIRECT_URL'])) {
    $url = $_SERVER['REDIRECT_URL'];
} else if (isset($_SERVER['REQUEST_URI'])) {
    $url = $_SERVER['REQUEST_URI'];
} else {
    $url = null;
}

if (isset($_GET['noredirect'])) {
    if (null !== $url && $url == '/test_redirect/') {
        echo 'Mod_Rewrite works!';
    } else {
        echo 'Mod_Rewrite does not works';
    }
    die();
}
if (APPLICATION_ENV == 'testing') {
    //We allow to run test unit without deleting the status page.
    require_once 'index.php_next';
} else {
    header('Location: ' . substr($_SERVER['PHP_SELF'], 0, -strlen('index.php')) . 'status/');
    die();
}

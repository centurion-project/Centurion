<?php


if (APPLICATION_ENV == 'testing') {
    //We allow to run test unit without deleting the status page.
    require_once 'index.php_next';
} else {
    header('Location: ' . substr($_SERVER['PHP_SELF'], 0, -strlen('index.php')) . 'status/');
    die();
}

<?php

$root   = dirname(__DIR__);
$etcdir = join('/', array($root, 'etc'));
$libdir = join('/', array($root, 'lib'));
$logdir = join('/', array($root, 'log'));
$tpldir = join('/', array(__DIR__, 'templates'));
$extern = array(
    'github.com.erusev.parsedown',
    'github.com.speedmax.h2o-php',
);

foreach ($extern as $lib) {
    ini_set('include_path', ini_get('include_path') . ':' . join('/', array($libdir, $lib)));
}

// Error handling
error_reporting(E_ALL | E_STRICT);
ini_set('error_log', join('/', array($logdir, 'error.log')));
if (php_sapi_name() === 'cli') {
    ini_set('display_errors', 1);
    ini_set('track_errors', 1);
    ini_set('html_errors', 1);
} else {
    ini_set('display_errors', 0);
}

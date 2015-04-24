<?php
require_once('lib.php');
require_once($etcdir . '/config.php');
require_once('spacephone.class.php');

$spacephone = new SpacePhone($config);
$spacephone->handle();

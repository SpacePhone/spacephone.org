<?php

$config = array();

// DNS resolvers, "default" is mandatory
$config['resolvers'] = array(
    'default' => array(
        '172.23.40.51',
        '172.23.40.52',
    ),
);

// E.164 zones, "default" is mandatory
$config['e164_zones'] = array(
    '319799'  => 'e164.spacephone.org',
    '31979'   => 'e164.ptt-tele.com',
    'default' => 'e164.arpa',
);

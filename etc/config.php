<?php

$config = array();

// DNS resolvers
$config['resolvers'] = array();

// E.164 zones, "default" is mandatory
$config['e164_zones'] = array(
    '319799'  => array('e164.spacephone.org'),
    '31979'   => array('e164.ptt-tele.com'),
    'default' => array('e164.arpa', 'e164.org', 'e164.info', 'e164enum.eu'),
);

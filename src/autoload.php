<?php

spl_autoload_register(function ($class) {
    static $map = array (
        'SergeyPreobrazhensky\PhoneGeoData\PhoneGeoDataGetter' => 'PhoneGeoDataGetter.php',
        'SergeyPreobrazhensky\PhoneGeoData\PhoneGeoData' => 'PhoneGeoData.php',
        'SergeyPreobrazhensky\PhoneGeoData\NumberBorder' => 'NumberBorder.php',
    );

    if (isset($map[$class])) {
        require_once __DIR__ . "/{$map[$class]}";
    }
}, true, false);
<?php
    include_once __DIR__.'/IUpdater.php';

    includeProviders( __DIR__.'/providers');

    $providerClasses = array_filter(
        get_declared_classes(),
        function ($className) {
            return in_array(IUpdater::class, class_implements($className), true);
        }
    );

    foreach ($providerClasses as $providerClass) {
        /** @var IUpdater $provider */
        $provider = new $providerClass();
        $provider->update();
    }

    function includeProviders($providersDir) {
        $objDir = opendir($providersDir);

        while ($filename = readdir($objDir))
        {
            if(($filename !== '.') AND ($filename !== '..')) {
                $fullPath = $providersDir."/".$filename;
                if (strpos($filename, '.php')) {
                    include_once($fullPath);
                }
                elseif (is_dir($fullPath)) {
                    includeProviders($fullPath);
                }
            }
        }
    }

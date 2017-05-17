<?php
/**
 * This is the configuration file for the 'yii2-authclient' unit tests.
 * You can override configuration values by creating a `config.local.php` file
 * and manipulate the `$config` variable.
 */

$config = [
    'google' => [
        'serviceAccount' => '', // e.g. 'your-service-account-id@developer.gserviceaccount.com'
        'serviceAccountPrivateKey' => "", // e.g. "-----BEGIN PRIVATE KEY-----   ...   -----END PRIVATE KEY-----\n"
    ]
];

if (is_file(__DIR__ . '/config.local.php')) {
    include(__DIR__ . '/config.local.php');
}

return $config;
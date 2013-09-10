<?php

define('ROOT_DIR', __DIR__ . '/../../');

require_once ROOT_DIR . 'vendor/autoload.php';

$env = 'dev';

$settingsFile = ROOT_DIR . 'settings.ini';

if (file_exists($settingsFile)) {
    $settings = parse_ini_file($settingsFile, true);
    $env = isset($settings['env']) ? $settings['env'] : 'dev';
} else {
    $settings = array();
}

define('ENVIRONMENT', $env);

$envSettings = isset($settings[ENVIRONMENT]) ? $settings[ENVIRONMENT] : array();

$host = isset($envSettings['tests_api_url']) ? $envSettings['tests_api_url'] : 'http://api.devmobilerider.com';

define('APP_HOST', $host);
define('APP_ID', $envSettings['tests_api_id']);
define('APP_SECRET', $envSettings['tests_api_secret']);


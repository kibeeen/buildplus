<?php
// URL
define('URL', $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/');

// HTTP
define('HTTP_SERVER', 'http://' . URL);

// HTTPS
define('HTTPS_SERVER', 'http://' . URL);

// DIR
define('BASE_DIR', realpath(dirname(__FILE__)));
define('DIR_APPLICATION',  BASE_DIR . '/catalog/');
define('DIR_SYSTEM',  BASE_DIR . '/system/');
define('DIR_LANGUAGE',  BASE_DIR . '/catalog/language/');
define('DIR_TEMPLATE',  BASE_DIR . '/catalog/view/theme/');
define('DIR_CONFIG',  BASE_DIR . '/system/config/');
define('DIR_IMAGE',  BASE_DIR . '/image/');
define('DIR_CACHE',  BASE_DIR . '/system/cache/');
define('DIR_DOWNLOAD',  BASE_DIR . '/system/download/');
define('DIR_UPLOAD',  BASE_DIR . '/system/upload/');
define('DIR_MODIFICATION',  BASE_DIR . '/system/modification/');
define('DIR_LOGS',  BASE_DIR . '/system/logs/');
define('DIR_CATALOG_IMG', 'catalog/view/theme/default/image/');

// DB
define('DB_DRIVER', 'mysqli');
define('DB_HOSTNAME', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_DATABASE', 'oc_buildplus');
define('DB_PORT', '3306');
define('DB_PREFIX', '');

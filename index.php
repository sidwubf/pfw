<?php

error_reporting(E_ALL);

defined('PFW_ROOT_PATH') || define('PFW_ROOT_PATH', dirname(__FILE__) . '/');
defined('PFW_CORE_PATH') || define('PFW_CORE_PATH', PFW_ROOT_PATH . 'core/');
defined('PFW_CONTROLLER_PATH') || define('PFW_CONTROLLER_PATH', PFW_ROOT_PATH . 'controller/');
defined('PFW_VIEW_PATH') || define('PFW_VIEW_PATH', PFW_ROOT_PATH . 'view/');

include(PFW_CORE_PATH . "loader.php");

try {
    Loader::core('Marker');
    Loader::core('PFW');

    Marker::mark('whole');
    PFW::run();
    Marker::mark('whole');
    echo "<pre>";print_r(Marker::get('whole'));
} catch (Exception $e) {
    echo $e->getMessage();
}


/*
echo PFW_ROOT_PATH;exit;

defined('PFW_CORE_PATH')       || define('PFW_CORE_PATH',       PFW_ROOT_PATH . 'core/');
defined('PFW_CONFIG_PATH')     || define('PFW_CONFIG_PATH',     PFW_ROOT_PATH . 'config/');
defined('PFW_CONTROLLER_PATH') || define('PFW_CONTROLLER_PATH', PFW_ROOT_PATH . 'controller/');
defined('PFW_HELPER_PATH')     || define('PFW_HELPER_PATH',     PFW_ROOT_PATH . 'helper/');
defined('PFW_LIBRARY_PATH')    || define('PFW_LIBRARY_PATH',    PFW_ROOT_PATH . 'library/');
defined('PFW_MODEL_PATH')      || define('PFW_MODEL_PATH',      PFW_ROOT_PATH . 'model/');
defined('PFW_VIEW_PATH')       || define('PFW_VIEW_PATH',       PFW_ROOT_PATH . 'view/');

defined('EXT') || define('EXT', '.php');

require_once PFW_CORE_PATH . "exceptions" . EXT;

require_once PFW_CONFIG_PATH . "constants" . EXT;
require_once PFW_CONFIG_PATH . "databases" . EXT;
require_once PFW_CONFIG_PATH . "memcaches" . EXT;

require_once PFW_CORE_PATH . "mysql"    . EXT;
require_once PFW_CORE_PATH . "memcache" . EXT;
require_once PFW_CORE_PATH . "template" . EXT;

DBFactory::set_database($database);

require_once PFW_CORE_PATH . "mark" . EXT;
$marker = new MARK();
$marker->mark('start');

require_once PFW_CORE_PATH . "pfw" . EXT;
$pfw = new PFW();
$controller = $pfw->get_controller();
$function = $pfw->get_function();

require_once PFW_CORE_PATH . "pfw_controller" . EXT;
require_once PFW_CONTROLLER_PATH . $controller . EXT;
$controller = strtoupper($controller);
$controller = new $controller();
$controller->$function();

$marker->mark('end');

echo microtime(true) - $start;
*/
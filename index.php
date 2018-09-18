<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-9-17
 * Time: 下午9:14
 */

define('DEBUG', true);
defined('DS') or define('DS', DIRECTORY_SEPARATOR);
defined('ROOT_PATH') or define('ROOT_PATH', dirname(__FILE__) . DS);

include "./core/Loader.php";
\core\Loader::register();

include "./core/init.php";
include "./core/lib.php";

$config = require_once "./app/config.php";

$bootstrap = new \core\Bootstrap($config);
$bootstrap->run();
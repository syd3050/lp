<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-9-17
 * Time: 下午9:14
 */

define('DEBUG', true);
/* env 指定header头中环境变量的标识key，默认为env，在多个项目存在时，只需修改这个值为各自不同值即可 */
defined('ENV_KEY') or define('ENV_KEY','env');
defined('DS') or define('DS', DIRECTORY_SEPARATOR);
defined('ROOT_PATH') or define('ROOT_PATH', dirname(__FILE__) . DS);

include "./core/Loader.php";
\core\Loader::register();

include "./core/init.php";
include "./core/lib.php";

$config = require_once "./app/config.php";

$bootstrap = new \core\Bootstrap($config);
$bootstrap->run();
<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-9-17
 * Time: 下午9:14
 */

define('DEBUG', true);

include "./core/init.php";

include "./core/Loader.php";
\core\Loader::register();

include "./core/lib.php";

$bootstrap = new \core\Bootstrap();
$bootstrap->run();
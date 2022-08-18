<?php

use ApiCore\Library\InterfaceWarehouse\Command;

define("START_TIME", $_SERVER['REQUEST_TIME_FLOAT'] * 1000);
define('APP_BASE_PATH', dirname(__FILE__,2));
require "../vendor/autoload.php";
Command::dispatch(\ApiCore\Command\Make\Filter::class,['module_name'=>'aa','filter_name'=>'vv']);
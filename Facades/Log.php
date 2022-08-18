<?php

namespace ApiCore\Facades;

use ApiCore\Library\InterfaceWarehouse\Facade;

/**
 *
 * @method static void log($level, string|\Stringable $message, array $context = [])
 * @method static void info(string|\Stringable $message, array $context = [])
 * @method static void debug(string|\Stringable $message, array $context = [])
 * @method static void notice(string|\Stringable $message, array $context = [])
 * @method static void warning(string|\Stringable $message, array $context = [])
 * @method static void error(string|\Stringable $message, array $context = [])
 * @method static void critical(string|\Stringable $message, array $context = [])
 * @method static void alert(string|\Stringable $message, array $context = [])
 * @method static void emergency(string|\Stringable $message, array $context = [])
 * 更多的方法看下面,上面只是方便IDE识别
 *
 * @see Logger;
 */
class Log extends Facade
{

    protected static function getFacadeName(): string
    {
        return 'Log';
    }
}
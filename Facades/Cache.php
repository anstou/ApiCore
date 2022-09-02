<?php

namespace ApiCore\Facades;

use ApiCore\Library\InterfaceWarehouse\Facade;

/**
 *
 * @method static bool has(string $key)
 * @method static bool set(string $key, mixed $data, null|int|\DateInterval $ttl = null)
 * @method static mixed get(string $key, mixed $default = null)
 * @method static bool delete(string $key)
 * @method static void setCacheName(string $cacheName = '')
 * @method static string getCacheName()
 * @method static bool setMultiple(iterable $values, null|int|\DateInterval $ttl = null)
 * @method static bool deleteMultiple(iterable $keys)
 * @method static bool clear()
 *
 * 以上方法全都来源于CacheInterface抽象接口的实现,具体请@see CacheInterface;
 * 如果你使用静态Cache::setCacheName,当进程没有结束时;
 * 再次使用静态的Cache的方法将会是你最近一次设置的cacheName;
 *
 * 会发生意外的情景:上面的点当运行在常驻进中,例如:swoole,会存在一个woker被不同的用户多次请求不同的业务场景始终复用这个$example
 *
 */
class Cache extends Facade
{

    protected static function getFacadeName(): string
    {
        return 'Cache';
    }
}
<?php

namespace ApiCore\Library\Cache;

use ApiCore\Library\ApiRestful\ApiCode;
use ApiCore\Library\InterfaceWarehouse\CacheInterface;
use ApiCore\Modules\Application\Library\Token\Token;

/**
 *
 * @method static bool has(string $key)
 * @method static static set(string $key, array $data, null|int|\DateInterval $ttl = null)
 * @method static mixed get(string $key, mixed $default = null)
 * @method static static delete(string $key)
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
class Cache
{
    private static ?self $example = null;

    /**
     *
     * @param CacheInterface $storage 储存驱动
     */
    public function __construct(protected CacheInterface $storage = new FileStorage('cache'))
    {

    }

    public function __call(string $name, array $arguments)
    {
        try {
            if (method_exists($this->storage, $name)) {
                return call_user_func_array([$this->storage, $name], $arguments);
            }
            throw new \Exception(ApiCode::SERVER_ERROR, get_class($this->storage) . '()->' . $name . '方法不存在');
        } catch (\Exception $exception) {
            throw new \Exception(ApiCode::SERVER_ERROR, '意料内但未处理的错误');
        }
    }


    public static function __callStatic(string $name, array $params)
    {
        try {
            if (is_null(static::$example)) static::$example = new static();
            return call_user_func_array([static::$example, $name], $params);
        } catch (\Exception $exception) {
            throw new \Exception('意料内但未处理的错误', ApiCode::SERVER_ERROR);
        }
    }
}
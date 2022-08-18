<?php

namespace ApiCore\Library\InterfaceWarehouse;

interface CacheInterface
{
    /**
     * 缓存大类的名字
     * 方便管理
     *
     * @param string $name
     */
    public function __construct(string $name = '');

    /**
     * 设置数据管理管理类名
     * 不推荐调用,建议在初始化类的时候传入就不要再改动了
     * 让数据更加可信
     *
     * @param string $cacheName
     * @return void
     */
    public function setCacheName(string $cacheName = ''): void;

    /**
     * 获取数据管理类名
     *
     * @return string
     */
    public function getCacheName(): string;

    /**
     * 获取指定缓存
     *
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * 设置指定缓存
     *
     * @param string $key
     * @param mixed $value
     * @param int|\DateInterval|null $ttl 有效期DateInterval:1月后过期=>'P1M' 1天后过期=>'P1D' 1分钟后过期=>'PT1M' 60秒后过期=>'PT60S'
     * @return bool
     */
    public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool;

    /**
     * 删除指定缓存
     *
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool;

    /**
     * 清除所有缓存
     *
     * @return bool
     */
    public function clear(): bool;

    /**
     * 批量获取数据键
     *
     * @param iterable $keys
     * @param mixed|null $default
     * @return iterable
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable;

    /**
     * 批量设置数据键对值
     *
     * @param iterable $values
     * @param int|\DateInterval|null $ttl
     * @return bool
     */
    public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool;

    /**
     * 批量删除指定数据键
     *
     * @param iterable $keys
     * @return bool
     */
    public function deleteMultiple(iterable $keys): bool;

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;
}
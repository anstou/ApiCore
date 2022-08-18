<?php

namespace ApiCore\Library\Cache;

use ApiCore\Library\InterfaceWarehouse\CacheInterface;

class FileStorage implements CacheInterface
{

    private string $cacheDirPathname;


    /**
     * @param string $cacheName
     * @throws \Exception
     */
    public function __construct(private string $cacheName = 'cache')
    {
        $this->setCacheName($cacheName);
    }

    /**
     * 设置缓存类名
     *
     * @param string $cacheName
     * @return void
     * @throws \Exception
     */
    public function setCacheName(string $cacheName = ''): void
    {
        if (!empty($cacheName) && !preg_match('/^[A-Za-z\d]+$/', $cacheName)) {
            throw new \Exception('$name需要被/^[A-Za-z\d]+$/匹配');
        }
        $this->cacheName = $cacheName;
        $this->cacheDirPathname = storage_path('cache' . (empty($this->cacheName) ? '' : DIRECTORY_SEPARATOR . $this->cacheName));
        if (!is_dir($this->cacheDirPathname) && !create_dir($this->cacheDirPathname)) {
            throw new \Exception('创建文件夹失败,create:' . $this->cacheDirPathname);
        }
    }

    /**
     * 获取缓存类名
     *
     * @return string
     */
    public function getCacheName(): string
    {
        return $this->cacheName;
    }

    /**
     * 获取指定缓存
     *
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $pn = $this->cacheDirPathname . DIRECTORY_SEPARATOR . sha1($key);
        if ($this->has($key) && ($jsonStr = file_get_contents($pn)) !== false) {
            $arr = json_decode($jsonStr, true);
            if (!is_array($arr) || !array_key_exists('data', $arr) || !array_key_exists('ttl', $arr) || ($arr['ttl'] !== 'never' && time() >= $arr['ttl'])) {
                //time to live
                //解析失败|data,ttl字段不存在|ttl过期,返回默认值并且删掉这个解析失败的数据
                $this->delete($key);
                return $default;
            }
            return $arr['data'];
        }
        return $default;
    }

    /**
     * 设置指定缓存
     *
     * @param string $key 数据键名
     * @param mixed $value 传进来的数据将会被json_encode,而不是serialize,所以请不要传入类
     * @param int|\DateInterval|null $ttl 过期时间 null为永不过期
     * @return bool
     * @throws \Exception
     */
    public function set(string $key, mixed $value, \DateInterval|int|null $ttl = null): bool
    {
        $pn = $this->cacheDirPathname . DIRECTORY_SEPARATOR . sha1($key);
        $data = ['data' => $value, 'ttl' => ''];
        if (is_null($ttl)) $data['ttl'] = 'never';
        if (is_int($ttl)) {
            if (time() >= $ttl) throw new \Exception('传入了已经过期的时间');
            $data['ttl'] = $ttl;
        }
        if ($ttl instanceof \DateInterval) $data['ttl'] = date_create()->add($ttl)->getTimestamp();
        return filePutContents($pn, json_encode($data, JSON_UNESCAPED_UNICODE)) !== false;
        // TODO: Implement set() method.
    }

    /**
     * 删除指定缓存
     *
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        $pn = $this->cacheDirPathname . DIRECTORY_SEPARATOR . sha1($key);
        return unlink($pn);
    }

    /**
     * 清除所有缓存
     *
     * @return bool
     */
    public function clear(): bool
    {
        return deleteDir($this->cacheDirPathname);
    }

    /**
     * 批量获取key的过期时间
     *
     * @param iterable $keys
     * @param mixed|null $default
     * @return iterable
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $data = [];
        foreach ($keys as $key) $data[$key] = $this->get($key, $default);
        return $data;
    }

    /**
     * 批量设置键对值的过期时间
     *
     * @param iterable $values
     * @param int|\DateInterval|null $ttl
     * @return bool
     * @throws \Exception
     */
    public function setMultiple(iterable $values, \DateInterval|int|null $ttl = null): bool
    {
        foreach ($values as $key => $data) $this->set($key, $data, $ttl);
        return true;
    }

    /**
     * @param iterable $keys
     * @return bool
     */
    public function deleteMultiple(iterable $keys): bool
    {
        foreach ($keys as $key) $this->delete($key);
        return true;
    }

    /**
     * 只是判定存在
     * 过没过期不晓得
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        $pn = $this->cacheDirPathname . DIRECTORY_SEPARATOR . sha1($key);
        return file_exists($pn) && is_file($pn);
    }
}
<?php

namespace ApiCore\Library\InterfaceWarehouse;

use ApiCore\Library\Http\Request\Headers;

/**
 * 数据仓库基类
 */
abstract class DataWarehouse implements \ArrayAccess, \Iterator
{

    private static int|string $_position = 0;

    public function __construct(protected array $Data = [])
    {

    }

    /**
     * 获取指定键内容
     *
     * @param string $name
     * @param mixed|null $default
     * @return mixed
     */
    public function Get(string $name, mixed $default = null): mixed
    {
        return $this->Data[$name] ?? $default;
    }

    /**
     * 设置指定键内容
     *
     * @param string $name
     * @param mixed $value
     * @return Headers
     */
    public function Set(string $name, mixed $value): static
    {
        $this->Data[$name] = $value;
        return $this;
    }

    public function __get(string $name)
    {
        return $this->Get($name);
    }

    public function __set(string $name, mixed $value): void
    {
        $this->Set($name, $value);
    }

    /**
     * 数据条数
     *
     * @return int
     */
    public function Size(): int
    {
        return count($this->Data);
    }

    /**
     * 返回所有数据
     *
     * @return array
     */
    public function All(): array
    {
        return $this->Data;
    }


    /**
     * 结果转化为json
     *
     * @param int $flags
     * @return string
     */
    public function json(int $flags = 0): string
    {
        $json = json_encode($this->Data, $flags);
        return is_string($json) ? $json : '[]';
    }


    public function offsetExists($offset): bool
    {
        return (bool)(($this->Data[$offset] ?? false));
    }


    public function offsetGet($offset): mixed
    {
        return $this->Data[$offset] ?? null;
    }


    public function offsetSet($offset, $value): void
    {

        $this->Data[$offset] = $value;

    }

    public function offsetUnset($offset): void
    {
        unset($this->Data[$offset]);
    }

    /**
     * foreach循环的当前元素值
     * @return mixed
     */
    public function current(): mixed
    {
        return $this->Data[key($this->Data)] ?? null;
    }

    /**
     * 数组内部指针指向下一个元素
     *
     * @return void
     */
    public function next(): void
    {
        next($this->Data);
    }

    /**
     * 当前元素的键名
     *
     * @return mixed
     */
    public function key(): mixed
    {
        return key($this->Data);
    }

    /**
     * 检查当前指针指向元素是否存在
     *
     * @return bool
     */
    public function valid(): bool
    {
        return isset($this->Data[key($this->Data)]);
    }

    /**
     * 重置数组内部指针
     *
     * @return void
     */
    public function rewind(): void
    {
        reset($this->Data);
    }
}
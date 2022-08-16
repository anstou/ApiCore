<?php

namespace ApiCore\Library\InterfaceWarehouse;

abstract class DataWarehouse implements \ArrayAccess
{
    protected array $data = [];


    /**
     * 数据长度
     *
     * @return int
     */
    public function length(): int
    {
        return count($this->data);
    }

    /**
     * 返回所有数据
     *
     * @return array
     */
    public function all(): array
    {
        return $this->data;
    }


    /**
     * 结果转化为json
     *
     * @param int $flags
     * @return string
     */
    public function json(int $flags = 0): string
    {
        $json = json_encode($this->data, $flags);
        return is_string($json) ? $json : '[]';
    }


    public function offsetExists($offset): bool
    {
        return (bool)(($this->data[$offset] ?? false));
    }


    public function offsetGet($offset): mixed
    {
        return $this->data[$offset] ?? null;
    }


    public function offsetSet($offset, $value): void
    {

        $this->data[$offset] = $value;

    }

    public function offsetUnset($offset): void
    {
        unset($this->data[$offset]);
    }

}
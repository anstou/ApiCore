<?php

namespace ApiCore\Library\Http\Request;

use ApiCore\Library\InterfaceWarehouse\DataWarehouse;
use ReturnTypeWillChange;

class State extends DataWarehouse
{


    public function __construct(protected array $data = [])
    {

    }

    /**
     * 获取指定键内容
     *
     * @param string $name
     * @param mixed|null $default
     * @return mixed
     */
    public function get(string $name, mixed $default = null): mixed
    {
        return $this->data[$name] ?? $default;
    }

    /**
     * 设置指定键内容
     *
     * @param string $name
     * @param mixed $value
     * @return State
     */
    public function set(string $name, mixed $value): static
    {
        $this->data[$name] = $value;
        return $this;
    }

    public function __get(string $name)
    {
        return $this->get($name);
    }


}
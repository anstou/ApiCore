<?php

namespace ApiCore\Library\Command;

abstract class CommandKernel
{

    /**
     * @var string|null 命令别名
     */
    public static ?string $Alias = null;

    /**
     * 需要哪些数据字段
     * @var string[]
     */
    protected array $Params = [];


    /**
     * 将需要的字段存起来的数据
     *
     * @var array
     */
    private array $ParamData = [];


    abstract public function Run(): mixed;

    final public function __construct(array $defaultParams = [])
    {
        $params = cliParams();
        foreach ($this->Params as $paramKey) {
            if (array_key_exists($paramKey, $params) || array_key_exists($paramKey, $defaultParams)) {
                $this->ParamData[$paramKey] = $defaultParams[$paramKey] ?? $params[$paramKey];
            } else {
                echo $paramKey . '必填', PHP_EOL;
                exit;
            }
        }
    }

    /**
     * 获取参数
     *
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    final protected function Param(string $key, mixed $default = null): mixed
    {
        return $this->paramData[$key] ?? $default;
    }

    /**
     * 获取所有参数
     *
     * @return array
     */
    final protected function Params(): array
    {
        return $this->ParamData;
    }

}
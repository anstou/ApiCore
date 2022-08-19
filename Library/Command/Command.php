<?php

namespace ApiCore\Library\Command;

abstract class Command
{
    /**
     * 初始化的命令行们
     * @var array
     */
    private static array $commands = [];

    /**
     * 需要哪些key
     * @var string[]
     */
    protected array $params = [];

    /**
     * 将需要的key存起来的数据
     *
     * @var array
     */
    private array $paramData = [];


    abstract public function run(): mixed;

    final public function __construct(array $defaultParams = [])
    {
        $params = cliParams();
        foreach ($this->params as $paramKey) {
            if (array_key_exists($paramKey, $params) || array_key_exists($paramKey, $defaultParams)) {
                $this->paramData[$paramKey] = $defaultParams[$paramKey] ?? $params[$paramKey];
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
    final protected function param(string $key, mixed $default = null): mixed
    {
        return $this->paramData[$key] ?? $default;
    }

    /**
     * 获取所有参数
     *
     * @return array
     */
    final protected function params(): array
    {
        return $this->paramData;
    }

    /**
     * 快捷使用控制命令
     *
     * @param string $command 命令类
     * @param array $params 要传递的参数
     * @return mixed
     * @throws \Exception
     */
    final public static function dispatch(string $command, array $params = []): mixed
    {
        if (static::class !== self::class) throw new \Exception('请从' . self::class . '中调用dispatch');
        if (class_exists($command)) {
            return (new $command($params))->run();
        }
        return throw new \Exception($command . '::class不存在');
    }

    /**
     * 外部command命令存在的文件夹
     *
     * @param string $commandPath
     * @return void
     */
    public static function Init(string $commandPath): void
    {
        $list = [];
        $filenames = scandir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Commands');
        if (is_array($filenames)) foreach ($filenames as $filename) {
            if ($filename === '.' || $filename === '..') continue;
            if(is_dir($filename)){

            }

        }
    }
}
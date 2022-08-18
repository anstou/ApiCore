<?php

namespace ApiCore\Library\InterfaceWarehouse;


use ApiCore\Library\ApiRestful\ApiCode;

abstract class Facade
{
    /**
     * 储存门面别名与对象类名
     * 储存方式为 [门面名 => 类名]
     *
     * @var array|null
     */
    private static ?array $app = null;

    /**
     * 储存已经实例化的门面对象
     * 储存方式为 [门面名 => 实例对象]
     *
     * @var array
     */
    private static array $resolvedInstance = [];

    final public function __construct(string $app)
    {
        //没有初始化或者提前调用了,也有可能是被swoole开了新的工作进程
        if (is_null(self::$app)) self::loadFacade();

        if (array_key_exists($app, self::$app)) {
            return $this;
        }
        throw new \Exception($app . '不存在于Facade::$app中');
    }

    /**
     * 用于获取当前被继承的门面实例key
     *
     * @return string
     * @throws \Exception
     */
    protected static function getFacadeName(): string
    {
        throw new \Exception('请继承覆盖实现这个方法');
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    final protected static function getApp(): mixed
    {
        //没有初始化或者提前调用了,也有可能是被swoole开了新的工作进程
        if (is_null(self::$app)) self::loadFacade();

        //如果不重写getFacadeName()方法,static::app()是不可能返回正确门面名的
        $name = static::getFacadeName();
        $resolvedInstance = self::$resolvedInstance[$name] ?? (self::$app[$name] ?? null);
        if (is_null($resolvedInstance)) {
            throw new \Exception($name . '不存在于Facade::$app中');
        }
        if (is_string($resolvedInstance)) {
            self::$resolvedInstance[$name] = new $resolvedInstance;
        }
        return self::$resolvedInstance[$name];
    }

    /**
     * @param string $name
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    final public function __call(string $name, array $params)
    {
        return self::__callStatic($name, $params);
    }

    /**
     * @param string $name
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    final public static function __callStatic(string $name, array $params)
    {
        $resolvedInstance = static::getApp();
        try {
            return call_user_func_array([$resolvedInstance, $name], $params);
        } catch (\Throwable $exception) {
            throw new \Exception(ApiCode::SERVER_ERROR, '意料内但未处理的错误');
        }
    }

    /**
     * 设置门面别名与对象类名数组
     *
     * @param array $app
     * @return void
     */
    private static function setFacadeApp(array $app): void
    {
        static::$app = $app;
    }

    /**
     * 载入门面
     *
     * @return void
     */
    public static function loadFacade(): void
    {
        if (!is_null(static::$app)) return;
        $facades = include_once config_path('facade.php');
        $app = [];
        foreach ($facades as $alias => $class) {
            if (class_exists($class)) {
                $app[$alias] = $class;
            }
        }
        self::setFacadeApp($app);
    }
}
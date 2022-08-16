<?php

namespace ApiCore\Library\Module;

abstract class Module
{


    protected static ?array $Modules = null;

    /**
     * @return bool
     * @throws \Exception
     */
    public static function init(): bool
    {
        if (!is_null(static::$Modules)) return true;
        $configPath = config_path('modules.json');
        $config = file_get_contents($configPath);
        if (is_string($config)) {
            $config = json_decode($config, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                static::$Modules = $config;
                return true;
            }
        }
        throw new \Exception('模块初始化失败');
    }


    /**
     * 取所有模块名
     * 默认全部
     *
     * @param bool $isRegister 是否只显示已注册的模块
     * @param bool $isOpen 是否只可访问的模块
     * @return array 模块名组成的数组
     */
    public static function getAllModules(bool $isRegister = false, bool $isOpen = false): array
    {
        $modules = [];
        $names = scandir(path('app/Modules'));
        foreach ($names as $name) {
            if (preg_match('/^[A-Z][a-z]+$/', $name) > 0) {
                //是否注册才显示
                if ($isRegister) {

                    //判断注册
                    if (array_key_exists($name, static::$Modules)) {

                        //是否可访问才显示
                        if ($isOpen) {
                            if (static::$Modules[$name]) {

                                $modules[] = $name;//需要注册且开放

                            }
                        } else {

                            $modules[] = $name;//需要注册,但是不需要开放

                        }//是否可访问才显示--end

                    }//判断注册--end

                } else {

                    $modules[] = $name;//直接显示

                }//是否注册才显示--end

            }
        }
        return $modules;
    }

    /**
     * 判断模块是否可访问
     *
     * @param string $moduleName
     * @return bool
     */
    public static function AuthModule(string $moduleName): bool
    {
        return !empty(static::$Modules[$moduleName]);
    }

    /**
     * 判断模块是否已注册
     *
     * @param string $moduleName
     * @return bool
     */
    public static function hasRegisterModule(string $moduleName): bool
    {
        return isset(static::$Modules[$moduleName]);
    }

    /**
     * 模块是否存在
     *
     * @param string $moduleName
     * @return bool
     */
    public static function hasModule(string $moduleName): bool
    {
        return is_dir(path('app/Modules/' . $moduleName));
    }

    /**
     * 获取模块路径
     *
     * @param string $moduleName
     * @return string
     */
    public static function getModulePath(string $moduleName): string
    {
        return path('app/Modules/' . $moduleName);
    }

}
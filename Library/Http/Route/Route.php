<?php

namespace ApiCore\Library\Http\Route;

use ApiCore\Facades\Cache;
use ApiCore\Library\InterfaceWarehouse\Filter;
use ApiCore\Library\InterfaceWarehouse\MiddlewareBase;
use ApiCore\Library\Module\Module;

/**
 * 原本定义了默认调用Index模块中Index控制器的IndexAction方法
 * 想了想还是删除了,既然要求都是明确已知的,
 * 那么就不应该出现模糊不清的route,
 * 只有明确输入 模块名/控制器名/方法 才能准确调用,否则都将返回404的json
 * 这也就导致直接访问 "/"是会直接返回ApiRestful:init(0,"api")
 */
class Route
{

    /**
     * @var RouteUnit[]|null
     */
    private static ?array $routes = null;

    /**
     * 格式化输出方法 classname:method(type $name)
     *
     * @param string $className
     * @param string $method
     * @return string
     * @throws \ReflectionException
     */
    private static function formatClassMethod(string $className, string $method): string
    {
        $r = new \ReflectionMethod($className, $method);

        $parameters = [];
        $reflectionParameters = $r->getParameters();
        foreach ($reflectionParameters as $reflectionParameter) {
            $parameters[] = (is_null($reflectionParameter->getType()) ? '' : $reflectionParameter->getType()->getName() . ' ') . '$' . $reflectionParameter->getName();
        }
        $return = is_null($r->getReturnType()) ? '' : ':' . $r->getReturnType();

        return "$className::$method(" . implode(' , ', $parameters) . ')' . $return;
    }

    /**
     * 获取控制器的所有路由
     *
     * @param string $className 控制器名
     * @param string $moduleName 模块名
     * @return array
     */
    private static function getControllerRoute(string $className, string $moduleName): array
    {
        $routes = [];
        if (class_exists($className)) {
            $reflection = new \ReflectionClass($className);

            foreach ($reflection->getMethods() as $method) {
                //&& $method->isStatic()
                if ($method->isProtected() && preg_match('/^[A-Z]+\w+Action$/', $method->getName())) {
                    $name = strtolower(str_replace('Action', '', $method->getName()));
                    try {
                        $formatStr = static::formatClassMethod($className, $method->getName());
                    } catch (\ReflectionException $e) {
                        $formatStr = 'error';
                    }
                    $parent = strtolower(str_replace('\\', '/', str_replace(['\\App\\Modules\\', '\\Controllers'], '', $className)));

                    $filterClassName = str_replace('\\App\\Modules\\' . $moduleName . '\\Controllers\\', '\\App\\Modules\\' . $moduleName . '\\Filter\\', $className);
                    $filterMethod = ucfirst($name) . 'Filter';

                    if (class_exists($filterClassName)) {
                        $filterReflection = new \ReflectionClass($filterClassName);
                        if ($filterReflection->isSubclassOf(Filter::class)) {
                            if ($filterReflection->hasMethod($filterMethod)) {
                                $filterReflectionMethod = true;
                            } else {
                                $filterReflectionMethod = null;
                            }
                        } else {
                            $filterReflectionMethod = null;
                        }

                    } else {
                        $filterReflection = null;
                        $filterReflectionMethod = null;
                    }

                    $controller_method_param_count = 0;
                    $controller_method_param_must_count = 0;
                    foreach ($method->getParameters() as $parameter) {
                        $controller_method_param_count++;
                        $controller_method_param_must_count += $parameter->isDefaultValueAvailable() ? 0 : 1;
                    }

                    $url = '/' . $parent . '/' . $name;
                    $routes[md5($url)] = [
                        'url' => $url,
                        'module' => $moduleName,
                        'controller' => $className,
                        'controller_method' => $method->getName(),
                        'controller_method_param_count' => $controller_method_param_count,
                        'controller_method_param_must_count' => $controller_method_param_must_count,
                        'filter' => is_null($filterReflection) ? '' : $filterClassName,
                        'filter_method' => is_null($filterReflectionMethod) ? '' : $filterMethod,
                        'prototype_call' => $formatStr
                    ];

                }

            }
        }
        return $routes;
    }

    /**
     * 获取模块的所有路由
     *
     * @param string $moduleName 模块名
     * @param string $additional 附加名,用于深层回调
     * @return array
     */
    private static function getModuleRoutes(string $moduleName, string $additional = ''): array
    {
        $answer = [];
        $moduleNameSpace = '\\App\\Modules\\' . $moduleName . '\\Controllers\\' . $additional;
        $controllerClassnamePath = str_replace(['\\App\\Modules', '\\'], ['app\\Modules', '/'], $moduleNameSpace);
        $filenames = scandir(path($controllerClassnamePath));
        if (!empty($filenames)) {
            foreach ($filenames as $filename) {
                if ($filename === '.' || $filename === '..') continue;
                $path = path($controllerClassnamePath . $filename);
                if (is_dir($path)) {
                    $answer = array_merge_recursive($answer, static::getModuleRoutes($moduleName, $filename . '\\'));
                } elseif (is_file($path)) {
                    $answer = array_merge_recursive($answer, static::getControllerRoute(str_replace('.php', '', $moduleNameSpace . $filename), $moduleName));
                }
            }
        }
        return $answer;
    }

    /**
     * 加载所有已注册且开放访问模块的路由
     *
     * @param bool $forceRefresh 强制刷新
     * @return array
     * @throws \Exception
     */
    public static function loadRoutes(bool $forceRefresh = false): array
    {
        Module::init();
        if (!$forceRefresh) {
            $routes = Cache::get('allRoutes');
            if (is_array($routes)) {
                $newRoutes = [];
                foreach ($routes as $hash => $routeArr) {
                    $newRoutes[$hash] = new RouteUnit(...$routeArr);
                }
                return $newRoutes;
            }
        }

        $routes = [];
        $routesArr = [];
        $middlewares = MiddlewareBase::loadMiddleware();
        foreach (Module::getAllModules(true, true) as $module) {

            foreach (static::getModuleRoutes($module) as $hash => $route) {
                $routeUnit = $route;
                $routeUnit['middlewares'] = [];
                foreach ($middlewares as $middleware => $middlewareRule) {
                    if (MiddlewareBase::inMiddleware($route['module'], $route['controller'], $route['controller_method'], $middlewareRule)) {
                        $routeUnit['middlewares'][] = $middleware;
                    }
                }
                $routes[$hash] = new RouteUnit(...$routeUnit);
                $routesArr[$hash] = $routeUnit;
            }
        }

        static::setRoutes($routes);
        Cache::set('allRoutes', $routesArr, new \DateInterval('P1D'));
        return $routes;
    }

    /**
     * @return RouteUnit[]
     * @throws \Exception
     */
    public static function getRoutes(): array
    {
        //没被初始化,可能是提前调用,也有可能是被swoole开了新的工作进程
        if (is_null(self::$routes)) {
            return self::loadRoutes();
        }
        return self::$routes;
    }

    /**
     * @param array $routes
     */
    private static function setRoutes(array $routes): void
    {
        self::$routes = $routes;
    }

    /**
     * 检查请求路径是否存在于已注册路由中
     *
     * @param string $url
     * @return bool
     * @throws \Exception
     */
    public static function inRoutes(string $url): bool
    {
        $routes = self::getRoutes();
        $md = md5(strtolower($url));
        return array_key_exists($md, $routes);
    }

    /**
     * 获取请求对应的路由信息
     *
     * @param string $url
     * @return RouteUnit|null
     * @throws \Exception
     */
    public static function getRoute(string $url): ?RouteUnit
    {
        if (self::inRoutes($url)) {
            $md = md5(strtolower($url));
            return self::$routes[$md];
        }
        return null;
    }
}
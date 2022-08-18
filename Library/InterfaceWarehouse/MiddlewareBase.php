<?php

namespace ApiCore\Library\InterfaceWarehouse;

use ApiCore\Library\ApiRestful\ApiRestful;
use ApiCore\Library\Http\Request\Request;
use ApiCore\Library\Module\Module;

abstract class MiddlewareBase
{

    /**
     * 例外的控制器方法
     * 由继承的中间件来实现它
     *
     * url,请不要带参数,大小写不敏感,接受通配符*
     *
     * @var array
     */
    protected array $exceptionUrls = [

    ];

    /**
     * 传入的请求实例路径[url]是否在这个中间件的例外中
     *
     * @var bool
     */
    protected readonly bool $isException;

    /**
     * 中间件初始化方法,禁止重写
     *
     *
     * @param Request $request 起参考作用,对这个请求实例的修改不会传递到控制器中的请求实例去
     */
    final  public function __construct(protected Request $request)
    {
        $this->isException = $this->InException();
    }

    /**
     * 要调用的中间件方法
     *
     * @param string $classMethod
     * @param array $data
     * @return ApiRestful
     */
    final function run(string $classMethod, array $data = []): ApiRestful
    {
        if ($this->isException) return new ApiRestful();
        return call_user_func([$this, $classMethod], ...$data);
    }

    /**
     * 返回的ApiRestful的data会被控制器request->state->set键对值的方式储存
     *
     * @return ApiRestful
     */
    abstract protected function FilterBeforeHandle(): ApiRestful;

    /**
     * 实现一个空白的FilterAfterHandle
     * 中间件可以没有FilterAfterHandle,但是必须要有FilterBeforeHandle
     * 会接收到过滤好的参数,[参考性质,更改无效 不会传递到控制器方法]
     *
     * @param array $data
     * @return ApiRestful
     */
    protected function FilterAfterHandle(array $data = []): ApiRestful
    {
        // TODO: Implement FilterAfterHandle() method.
        return new ApiRestful();
    }


    /**
     * 看访问的路由是不是在例外中
     *
     * @return bool
     */
    public function InException(): bool
    {
        foreach ($this->exceptionUrls as $url) {
            if ($url === $this->request->URL) return true;
            $pattern = preg_quote($url);
            $pattern = str_replace('\*', '.*', $pattern);

            if (preg_match('#^' . $pattern . '\z#ui', $this->request->URL) === 1) {
                return true;
            }
        }
        return false;
    }

    /**
     * 加载所有中间件
     * 并返回
     *
     * @return array
     */
    public static function loadMiddleware(): array
    {
        $middlewares = include config_path('middleware.php');
        $modules = Module::getAllModules();
        foreach ($modules as $module) {
            $middlewarePathname = module_path($module . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'middleware.php');
            if (is_file($middlewarePathname)) {
                $middlewares = array_merge_recursive($middlewares, include_once $middlewarePathname);
            }

        }
        return $middlewares;
    }


    /**
     * 根据模块/控制器/方法这三个关键要素来从$middlewareRule中判断是否符合
     *
     * 判断优先级global > controller_methods > controllers > modules
     *
     * @param string $module
     * @param string $controller
     * @param string $controllerMethod
     * @param array $middlewareRule
     * @return bool
     */
    public static function inMiddleware(string $module, string $controller, string $controllerMethod, array $middlewareRule): bool
    {
        //global最高优先级判断
        if (array_key_exists('global', $middlewareRule) && $middlewareRule['global'] === true) {
            return true;
        }

        //controller_methods判断
        foreach ($middlewareRule['controller_methods'] ?? [] as $c => $methods) {
            if ($c === $controller && (is_array($methods) ? in_array($controllerMethod, $methods) : $methods === 'all')) return true;
        }

        //controllers判断
        foreach ($middlewareRule['controllers'] ?? [] as $c) {
            if ($c === $controller) return true;;
        }

        //modules判断
        foreach ($middlewareRule['modules'] ?? [] as $m) {
            if (strtoupper($m) === strtoupper($module)) return true;
        }

        return false;
    }
}
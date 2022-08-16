<?php

namespace ApiCore\Library\InterfaceWarehouse;

use ApiCore\Facades\Log;
use ApiCore\Library\ApiRestful\ApiRestful;
use ApiCore\Library\Enum\MiddlewareMethod;
use ApiCore\Library\Http\Request\Request;
use ApiCore\Library\Http\Route\RouteUnit;

/**
 * 获取控制器方法指针
 * 被App\App::class继承,
 * App\App::class则会被控制器类继承,
 * 否则无法访问控制器类的protected修饰符方法和指针
 */
abstract class ControllerFunction
{
    /**
     * 当前实例模块名
     *
     * @var string
     */
    protected readonly string $module;


    /**
     * @param Request $request 用户请求实例
     * @param RouteUnit $route 路由单元
     * @throws \Exception
     */
    final public function __construct(
        readonly protected Request   $request,
        readonly protected RouteUnit $route
    )
    {
        if ('\\' . get_class($this) !== $route->controller) {
            throw  new \Exception('路由映射控制器非此实例', 406);
        }
        if (!method_exists($this, $route->controller_method)) {
            throw  new \Exception('不存在的控制器方法', 406);
        }
        $this->module = $this->route->module;
    }

    /**
     * 分发请求和路由单元
     *
     * @param Request $request
     * @param RouteUnit $route
     * @param \Closure|null $closure 完成后的回调
     * @return ApiRestful
     */
    public static function dispatch(Request $request, RouteUnit $route, ?\Closure $closure = null): ApiRestful
    {
        if (class_exists($route->controller)) {

            try {
                $result = (new $route->controller($request, $route))->run();
            } catch (\Throwable $exception) {
                $result = new ApiRestful(500, $exception->getMessage(), ['file' => $exception->getFile(), 'line' => $exception->getLine()]);
            }

        } else {
            $result = new ApiRestful(1, $route->controller . '控制器不存在');
        }

        if (!is_null($closure)) $closure($result);
        return $result;
    }

    /**
     * 根据给予的请求示例和路由单元来运行业务逻辑
     *
     * @return ApiRestful
     */
    final public function run(): ApiRestful
    {
        //运行前置中间件
        foreach ($this->route->middlewares as $middleware) {
            $middlewareReturn = $this->runMiddleware($middleware);
            if ($middlewareReturn->code !== 0) return $middlewareReturn;
        }

        //运行参数过滤器
        $filterData = [];
        if (class_exists($this->route->filter)) {
            /**
             * @var \App\Library\ApiRestful\ApiRestful
             */
            $filterReturn = (new $this->route->filter($this->request, $this->route->filter_method))->run();
            if ($filterReturn->code !== 0) return $filterReturn;
            $filterData = $filterReturn->data;
        }

        //运行后置中间件
        foreach ($this->route->middlewares as $middleware) {
            $middlewareReturn = $this->runMiddleware($middleware, MiddlewareMethod::AFTER, $filterData);
            if ($middlewareReturn->code !== 0) return $middlewareReturn;
        }

        if ($this->route->controller_method_param_must_count > count($filterData)) {
            return new ApiRestful(1, 'lack params');//参数数量映射失败
        }
        return call_user_func_array([$this, $this->route->controller_method], $filterData);
    }

    /**
     * 运行一个中间件
     *
     * @param string $middleware 中间件类名
     * @param MiddlewareMethod $method 要运行的中间件状态
     * @param array $data 过滤后的数据
     * @return mixed
     */
    private function runMiddleware(string $middleware, MiddlewareMethod $method = MiddlewareMethod::BEFORE, array $data = []): ApiRestful
    {
        $params = [];
        if ($method->name === 'AFTER') {
            $classMethod = 'FilterAfterHandle';
            $params[] = $data;
        } else {
            $classMethod = 'FilterBeforeHandle';
        }

        $middlewareReturn = (new $middleware($this->request))->run($classMethod, $params);
        if ($middlewareReturn->code !== 0) return $middlewareReturn;
        $state = $middlewareReturn->data;
        if (is_array($state)) {
            foreach ($state as $k => $v) {
                $this->request->state->set($k, $v);
            }
        }
        return $middlewareReturn;
    }

    /**
     * 返回当前控制器实例所属模块
     *
     * @return string
     */
    final protected function getModuleName(): string
    {
        return $this->module;
    }

    /**
     * 返回的是控制器实例的命名空间
     *
     * @return string
     */
    final protected function getControllerName(): string
    {
        return static::class;
    }

    /**
     * 这是一个危险方法
     * 所以仅供命令行调用
     * 它不会调用中间件
     * 它不会调用过滤器
     * 会直接调用保护方法并传递$params
     * !!!风险请自行控制!!!
     *
     * @param array $params
     * @return ApiRestful
     * @throws \Exception
     */
    final public function runMethod(array $params): ApiRestful
    {
        if (!is_cli()) {
            throw new \Exception('!!!这个方法只有命令行模式能调用!!!');
        }
        return call_user_func_array([$this, $this->route->controller_method], $params);
    }
}
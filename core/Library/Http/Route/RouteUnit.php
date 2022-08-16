<?php

namespace ApiCore\Library\Http\Route;

class RouteUnit
{

    /**
     * @param string $url 访问链接
     * @param string $module 所属模块
     * @param string $controller 所属控制器类
     * @param string $controller_method 控制器中的方法名
     * @param string $controller_method_param_count 方法一共需要多少参数
     * @param string $controller_method_param_must_count 方法必须需要的参数数量
     * @param string $filter 参数过滤类
     * @param string $filter_method 参数过滤类中的过滤方法
     * @param string $prototype_call 调用的原型 $controller::$controller_method(...$params):returnDataFormat
     * @param string[] $middlewares 绑定的中间件
     */
    public function __construct(
        public readonly string $url,
        public readonly string $module,
        public readonly string $controller,
        public readonly string $controller_method,
        public readonly string $controller_method_param_count,
        public readonly string $controller_method_param_must_count,
        public readonly string $filter,
        public readonly string $filter_method,
        public readonly string $prototype_call,
        public readonly array  $middlewares
    )
    {

    }

    public function toArray(): array
    {
        return [
            'url' => $this->url,
            'module' => $this->module,
            'controller' => $this->controller,
            'controller_method' => $this->controller_method,
            'controller_method_param_count' => $this->controller_method_param_count,
            'controller_method_param_must_count' => $this->controller_method_param_must_count,
            'filter' => $this->filter,
            'filter_method' => $this->filter_method,
            'prototype_call' => $this->prototype_call,
            'middlewares' => $this->middlewares,
        ];
    }
}
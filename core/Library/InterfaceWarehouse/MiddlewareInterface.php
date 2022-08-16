<?php

namespace ApiCore\Library\InterfaceWarehouse;

use ApiCore\Library\ApiRestful\ApiRestful;
use ApiCore\Library\Http\Request\Request;

interface MiddlewareInterface
{
    /**
     * 在Filter过滤器之前使用
     *
     * @param Request $request
     * @return ApiRestful
     */
    public function FilterBeforeHandle(Request &$request): ApiRestful;

    /**
     * 在Filter过滤器之后使用,
     * 同时还会接收到过滤好的参数,[参考性质,更改无效]
     *
     * @param Request $request
     * @param array $data
     * @return ApiRestful
     */
    public function FilterAfterHandle(Request &$request, array $data = []): ApiRestful;

}
<?php

namespace ApiCore\Library\Enum;

enum MiddlewareMethod
{
    /**
     * Filter前调用的中间件
     * FilterBeforeHandle方法必须要被实现
     */
    case BEFORE;

    /**
     * Filter后调用的中间件
     * FilterAfterHandle可以为空
     */
    case AFTER;
}
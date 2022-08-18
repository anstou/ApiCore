<?php

namespace ApiCore;

use ApiCore\Library\InterfaceWarehouse\ControllerFunction;

/**
 * 项目控制类
 */
abstract class App extends ControllerFunction
{

    /**
     * 控制器的方法是直接收参数然后对参数进行逻辑操作,
     * 不接收Request请求实例
     * 但是请求实例会被传入实例中[$this->>request],用于获取一些状态,如用户登录信息
     * 用户的请求参数应该由Filter类来过滤与处理后,
     * 传递给控制器的方法
     * !!并不推荐在控制器中对请求参数的处理和判断过滤!!
     * !!控制器中的方法应该更专注于业务逻辑的实现!!
     */

}
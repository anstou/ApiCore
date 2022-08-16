<?php

namespace ApiCore\Library\ApiRestful;

abstract class ApiCode
{
    //需要登录
    const NEED_LOGIN = 22;

    const DATABASE_UPDATE_ERROR = 300;

    //意料之外的错误
    const UNEXPECTED_ERROR = 100;

    //服务器内部错误,已被捕捉的,预期内的
    const SERVER_ERROR = 500;
}
<?php

namespace ApiCore\Library\Http\Request\State;

use ApiCore\Library\InterfaceWarehouse\DataWarehouse;

/**
 * 登录的用户状态储存类
 * 将会附带在用户请求的状态中:$request->state->User
 */
class User extends DataWarehouse
{
    /**
     * 获取当前请求用户的ID,如果已登录
     *
     * @return int|null
     */
    public function GetUserId(): int|null
    {
        return $this->Get('id');
    }

    /**
     * 获取用户登录的token
     *
     * @return int|null
     */
    public function GetToken(): int|null
    {
        return $this->Get('token');
    }

    /**
     * 是否登录,将会检查$Data是否为空为判断标准
     *
     * @return bool
     */
    public function IsLogin(): bool
    {
        return !empty($this->Data);
    }

    /**
     * 设置用户信息
     *
     * @param array $userData
     * @return void
     */
    public function SetUser(array $userData): void
    {
        $this->Data = $userData;
    }

    /**
     * 获取用户信息
     *
     * @return array
     */
    public function GetUser(): array
    {
        return $this->Data;
    }

}
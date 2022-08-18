<?php

namespace ApiCore\Library\InterfaceWarehouse;

use ApiCore\Library\ApiRestful\ApiRestful;
use ApiCore\Library\Http\Request\Request;

abstract class Filter
{
    /**
     * @var bool 在检查被继承的控制器过滤器中,检查结果为false则该控制器不可访问
     */
    public bool $authorize = true;

    /**
     * 设置调用指定过滤器的request->method类型,
     * 不设置则为所有method类型.如:['login'=>'GET,POST','logout'=>'GET']
     * 请注意在对比的时候将会以小写的方式对比该变量中的键名
     * 不符合时返回404,详情见本类的run方法
     *
     * @var array
     */
    public array $methodRule = [];


    /**
     * @param Request $request 用户传入的请求实例,起参考作用
     * @param string $method 执行run方法时候要调用的过滤方法名
     * @throws \Exception
     */
    final public function __construct(
        public readonly Request   $request,
        protected readonly string $method
    )
    {
        if (!method_exists($this, $method)) {
            throw  new \Exception('不存在的过滤器方法', 405);
        }

    }


    /**
     * @param array $rules [key=>['rule'=>正则|function:{true|false},'message'=>string]] 验证规则
     * @param array $data 要验证的数据
     * @return ApiRestful
     */
    final public static function check(array $rules, array $data): ApiRestful
    {
        $r = new ApiRestful();
        $messages = [];
        $newData = [];
        foreach ($rules as $key => $ruleData) {
            $rule = $ruleData['rule'];
            $message = $ruleData['message'];

            if (!array_key_exists($key, $data)) {
                $messages[] = $message;
                continue;
            }

            if (
                (is_string($rule) && in_array(preg_match($rule, $data[$key]), [false, 0])) ||
                (is_callable($rule) && call_user_func($rule, $data[$key]) === false)
            ) {
                $messages[] = $message;
                continue;
            }
            $newData[$key] = $data[$key];
        }

        if (count($messages) > 0) {
            $r->setCode(1)->setMessage(implode(';', $messages));
        } else {
            $r->setData($newData);
        }
        return $r;
    }

    /**
     * 访问方式为GET
     *
     * @return bool
     */
    final public function isGET(): bool
    {

        return $this->request->getMethod() === 'GET';
    }

    /**
     * 访问方式为POST
     *
     * @return bool
     */
    final public function isPOST(): bool
    {
        return $this->request->getMethod() === 'POST';
    }

    /**
     * 运行过滤器
     *
     * @return ApiRestful
     */
    final public function run(): ApiRestful
    {
        $name = strtolower(strstr($this->method, 'Filter', true));
        if (array_key_exists($name, $this->methodRule) && !str_contains($this->methodRule[$name], $this->request->getMethod())) {
            return new ApiRestful(404, 'Filter');
        }

        $r = $this->{$this->method}();
        if ($r instanceof ApiRestful) {
            return $r;
        } else {
            return new ApiRestful(1, '返回的不是ApiRestful实例');
        }
    }

}
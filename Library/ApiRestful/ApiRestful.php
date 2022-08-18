<?php

namespace ApiCore\Library\ApiRestful;

use JetBrains\PhpStorm\Pure;

/**
 * 响应请求返回规范类
 */
class ApiRestful
{
    /**
     * @var string 提示信息字段名
     */
    public static string $message_name = 'message';

    /**
     * @var string 代码表达字段名
     */
    public static string $code_name = 'code';

    /**
     * @var string 反馈数据字段名
     */
    public static string $data_name = 'data';


    /**
     * 是否直接输出msg的内容,将不再是json格式,用于特殊场景
     * @var bool
     */
//    public bool $echoMsg = false;

    /**
     * @var int 储存的状态码
     */
//    protected int $code;

    /**
     * @var string 储存的文字消息
     */
//    protected string $message;

    /**
     * @var array 储存的数据
     */
//    protected array $data;

    /**
     * 初始化返回类
     *
     * @param int $code
     * @param string $message
     * @param array $data
     * @return $this
     */
    #[Pure] public static function init(int $code = 0, string $message = '', array $data = []): self
    {
        return new self($code, $message, $data);
    }

    /**
     * @param int $code
     * @param string $message
     * @param array $data
     * @param bool $echoMsg 是否直接输出msg的内容,将不再是json格式,用于特殊场景
     */
    public function __construct(
        protected int    $code = 0,//储存的状态码
        protected string $message = '',//储存的文字消息
        protected array  $data = [],//储存的数据
        public bool      $echoMsg = false)
    {
    }

    /**
     * @param int $code
     * @return ApiRestful
     */
    public function setCode(int $code): static
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @param string $message
     * @return ApiRestful
     */
    public function setMessage(string $message): static
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @param array $data
     * @return ApiRestful
     */
    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    /**
     * 获取结果
     *
     * @return array
     */
    public function getResult(): array
    {
        $result = [
            self::$code_name => $this->code,
            self::$message_name => $this->message,
        ];
//        if (!empty($this->data)) $result[self::$data_name] = $this->data;
       $result[self::$data_name] = $this->data;
        return $result;
    }

    public function json(): string
    {
        return json_encode($this->getResult());
    }

    public function __get(string $name)
    {
        return match ($name) {
            self::$data_name => $this->data,
            self::$message_name => $this->message,
            self::$code_name => $this->code,
            default => null
        };
    }

    public function __set(string $name, $value): void
    {
        switch ($name) {
            case self::$data_name:
                $this->data = (array)$value;
                break;
            case self::$message_name:
                $this->message = (string)$value;
                break;
            case self::$code_name:
                $this->code = (int)$value;
                break;
        }
    }

    public function __toString(): string
    {
        return $this->json();
    }

    public function pushData(string $string, mixed $restaurant_order_sn): static
    {
        $this->data[$string] = $restaurant_order_sn;
        return $this;
    }
}
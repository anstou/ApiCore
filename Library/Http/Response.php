<?php

namespace ApiCore\Library\Http;

use ApiCore\Facades\Log;
use ApiCore\Library\ApiRestful\ApiCode;
use ApiCore\Library\ApiRestful\ApiRestful;

/**
 * 响应调用的类,也就是最终输出的结果
 */
class Response
{
    /**
     * @var array|bool[] 触发保存日志的code,默认为500
     */
    protected static array $saveCodeLog = [500 => true, 404 => false, ApiCode::UNEXPECTED_ERROR => true];

    /**
     * 相应输出结果
     *
     * @param string $message
     * @param int $code
     * @param array $data
     * @param bool $exit 直接输出响应
     * @return string
     */
    public static function response(string $message, int $code = 0, array $data = [], bool $exit = true): string
    {

        if (is_cli()) {
            $flags = JSON_UNESCAPED_UNICODE;
        } else {
            $flags = JSON_UNESCAPED_UNICODE;
        }

        $returnData = [
            ApiRestful::$code_name => $code,
            ApiRestful::$message_name => $message
        ];
//        $data['execution_time'] = sprintf('%.3fms', microtime(true) * 1000 - START_TIME);

        $returnData[ApiRestful::$data_name] = $data;
        $json = json_encode($returnData, $flags);
        if (!$json) {
            $json = json_encode([
                'code' => 500,
                'message' => '出现严重内部错误,输出数据编码失败,该条数据使用原型名{code,message,data:[无法编码的数据]}',
                'data' => var_export($returnData, true)
            ], $flags);
        }
        if (array_key_exists($code, self::$saveCodeLog) && self::$saveCodeLog[$code] === true) {
            //需要保存日志的返回,会将data抹除后返回
            Log::error($message,$data);
            $returnData[ApiRestful::$message_name] = '内部错误';
            $returnData[ApiRestful::$data_name] = [];
            $json = json_encode($returnData, $flags);
        }
        if ($exit) {
            if(!headers_sent()){
                header('Content-Type:application/json; charset=utf-8');
            }

            echo $json;
            exit;
        } else {
            return $json;
        }

    }
}
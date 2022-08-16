<?php

namespace ApiCore\Library\Http\Request;

class Request
{

    /**
     * 访问方式
     *
     * @var RequestMethod
     */
    protected readonly RequestMethod $METHOD;

    /**
     * 请求数据 当为POST方式:body与$_GET键重复时,body键优先
     *
     * @var array|string
     */
    protected readonly array|string $DATA;

    /**
     * 请求数据中的文件
     *
     * @var array
     */
    protected readonly array $FILES;

    /**
     * @var string 访问路径
     */
    public readonly string $URL;

    public readonly State $state;

    public readonly Headers $headers;

    /**
     * 初始化请求
     *
     * @param string $url 请求的路径
     * @param string $method 请求模式,默认请求GET
     * @param array $queryData 查询数据,GET的
     * @param array|string $postData POST提交的数据,有可能是数组也有可能是字符串流
     * @param array $headers 请求头部
     * @param array $files 上传的文件
     * @param bool $checkFile 是否重新检查上传的文件,默认检查
     * @throws \Exception
     */
    public function __construct(
        string       $url,
        string       $method = 'GET',
        array        $queryData = [],
        array|string $postData = [],
        array        $headers = [],
        array        $files = [],
        bool         $checkFile = true)
    {
        $this->URL = trim($url);

        $this->METHOD = match (strtoupper($method)) {
            RequestMethod::GET->name => RequestMethod::GET,
            RequestMethod::POST->name => RequestMethod::POST,
            RequestMethod::PUT->name => RequestMethod::PUT,
            RequestMethod::DELETE->name => RequestMethod::DELETE,
            default => throw new \Exception('未知的访问方式')
        };
        //解析data
        $_data = is_string($postData) ? $this->decodeJsonStr($postData) : $postData;
        if ($_data === false) $_data = $this->decodeXmlStr($postData);
        if ($_data === false) $_data = $this->decodeQueryStr($postData);
        if (is_array($_data)) {
            $this->DATA = array_merge($queryData, $_data);
        } else {
            $this->DATA = $queryData;
        }


        if ($checkFile) {
            foreach ($files as $name => $file) {
                $tmpName = $file['tmp_name'] ?? '';
                if (file_exists($tmpName)) {
                    $filename = basename($tmpName);
                    $type = filetype($tmpName);
                    $size = filesize($tmpName);

                    $this->FILES[$name] = [
                        'name' => $filename,
                        'type' => $type,
                        'size' => $size,
                        'tmp_name' => $tmpName,
                        'error' => 0
                    ];
                }
            }
        } else {
            $this->FILES = $files;
        }
        $this->state = new State([]);
        $this->headers = new Headers($headers);
    }

    /**
     * 解码json字符串到数组
     *
     * @param string $string
     * @return array|bool
     */
    private function decodeJsonStr(string $string): array|bool
    {
        $data = json_decode($string, true);
        return json_last_error() === JSON_ERROR_NONE ? $data : false;
    }

    /**
     * 解码xml字符串到数组
     *
     * @param string $xmlStr
     * @return array|bool
     */
    private function decodeXmlStr(string $xmlStr): array|bool
    {
        $arr = xmlToArray($xmlStr);
        return empty($arr) ? false : $arr;
    }

    /**
     * 解码查询字符串到数组
     *
     * @param string $queryStr
     * @return array|bool
     */
    private function decodeQueryStr(string $queryStr): array|bool
    {
        if (empty($queryStr)) return false;
        $_data = [];
        $arr = explode('&', $queryStr);
        foreach ($arr as $item) {
            list($name, $value) = explode('=', $item);
            $_data[$name] = $value;
        }
        return $_data;
    }

    /**
     * 获取访问方式
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->METHOD->name;
    }


    /**
     * 获取所有参数
     * @return array|string
     */
    public function getParams(): array|string
    {
        return $this->DATA;
    }

    /**
     * 获取提交的参数
     *
     * @param string $name
     * @param mixed $default
     * @return mixed|string|null
     */
    public function get(string $name, mixed $default = null): mixed
    {
        return $this->DATA[$name] ?? $default;
    }

    /**
     * 获取上传的文件
     *
     * @param string $name
     * @return array|null
     */
    public function file(string $name): null|array
    {
        return $this->FILES[$name];
    }

    /**
     * 过滤$_SERVER得到header头部信息
     *
     * @param array $server 传入$_SERVER
     * @return array
     */
    public static function FilterHeaders(array $server): array
    {
        $headers = [];
        foreach ($server as $key => $value) {
            if (preg_match('/^HTTP_(.*?)$/', $key) > 0) {
                $newKey = str_replace('HTTP_', '', $key);
                $headers[$newKey] = $value;
            }
        }
        return $headers;
    }

}
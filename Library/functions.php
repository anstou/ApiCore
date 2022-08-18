<?php

if (!function_exists('array_deep_get')) {
    /**
     * 深度获取指定值
     * 如:array_deep_get(['a'=>['b'=>1]],'a.b'); 得到值为 1
     * 将会默认"."为分割
     *
     * @param array $arr
     * @param string $name
     * @param mixed $default
     * @param string $separator 分割符号
     * @return mixed
     */
    function array_deep_get(array $arr, string $name, mixed $default = null, string $separator = '.'): mixed
    {
        $names = explode($separator, trim($name));
        if (!array_key_exists($names[0], $arr)) return $default;

        $length = count($names);
        if ($length === 1) return $arr[$names[0]];

        $name = implode($separator, array_slice($names, 1));

        return array_deep_get($arr[$names[0]], $name, $separator);
    }
}

if (!function_exists('is_cli')) {
    /**
     * 是否命令行运行
     *
     * @return bool
     */
    function is_cli(): bool
    {
        return (bool)preg_match("/cli/i", php_sapi_name());
    }
}

if (!function_exists('config')) {
    /**
     * 获取配置文件指定数据
     *
     * @param string $config 配置文件名,应当在根目录的config文件夹中,不需要带".json"后缀,会自动带上
     * @param string|null $name 为null时返回整个配置文件
     * @param mixed|null $default
     * @return mixed
     * @throws Exception
     */
    function config(string $config, string $name = null, mixed $default = null): mixed
    {
        $filename = config_path($config . '.json');
        if (is_file($filename)) {
            $arr = json_decode(file_get_contents($filename), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                if (is_null($name)) return $arr;
                return array_deep_get($arr, $name, $default);
            }
            throw new \Exception($filename . '配置文件json_decode错误', 100);
        } else
            throw new \Exception($filename . '配置文件不存在', 100);

    }
}

if (!function_exists('path')) {
    /**
     * 获取项目根路径
     *
     * @param string $path
     * @return string
     */
    function path(string $path = ''): string
    {
        $dir = APP_BASE_PATH . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : $path);
        return str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $dir);
    }
}

if (!function_exists('public_path')) {
    /**
     * 获取项目公共目录
     *
     * @param string $path
     * @return string
     */
    function public_path(string $path = ''): string
    {
        return path('public' . DIRECTORY_SEPARATOR . $path);
    }
}

if (!function_exists('module_path')) {
    /**
     * 获取项目模块路径
     *
     * @param string $path
     * @return string
     */
    function module_path(string $path = ''): string
    {
        return path('app' . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . $path);
    }
}

if (!function_exists('config_path')) {
    /**
     * 获取配置文件路径
     *
     * @param string $path
     * @return string
     */
    function config_path(string $path = ''): string
    {
        return path('config' . DIRECTORY_SEPARATOR . $path);
    }
}
if (!function_exists('storage_path')) {
    /**
     * 获取储存文件路径
     *
     * @param string $path
     * @return string
     */
    function storage_path(string $path = ''): string
    {
        return path('storage' . DIRECTORY_SEPARATOR . $path);
    }
}
if (!function_exists('create_dir')) {
    /**
     * 创建目录
     *
     *
     * @param string $path
     * @param int $permissions 0755 最高权限可以设置到rwx-rwx-rx
     * @return bool
     */
    function create_dir(string $path, int $permissions = 0775): bool
    {
        if (file_exists($path) && is_dir($path)) return true;
        $oldUmask = umask();
        umask(0002);//0-rwx-rwx-rx
        $result = mkdir($path, $permissions, true);
        umask($oldUmask);
        return $result;
    }
}


if (!function_exists('is_xml')) {
    /**
     * 是否为xml
     *
     * @param string $str
     * @return bool
     */
    function is_xml(string $str): bool
    {
        $xml_parser = xml_parser_create();
        if (!xml_parse($xml_parser, $str, true)) {
            xml_parser_free($xml_parser);
            return false;
        } else {
            return !(json_decode(json_encode(simplexml_load_string($str)), true)) === false;
        }
    }
}
if (!function_exists('xmlToArray')) {
    /**
     * xml转化为数组
     *
     * @param string $xml
     * @return array
     */
    function xmlToArray(string $xml): array
    {
        $string_xml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $arr = json_decode(json_encode($string_xml), true);
        return is_array($arr) ? $arr : [];
    }
}

if (!function_exists('is_json')) {
    /**
     * 是否为json
     *
     * @param string $jsonStr
     * @return bool
     */
    function is_json(string $jsonStr): bool
    {
        json_decode($jsonStr);
        return json_last_error() === JSON_ERROR_NONE;
    }
}

if (!function_exists('create_guid')) {
    /**
     * 生成唯一uid
     *
     * @param string $namespace
     * @return string
     */
    function create_guid(string $namespace = 'Api'): string
    {
        $guid = '';
        $uid = uniqid("", true);
        $data = $namespace;
        $data .= $_SERVER['REQUEST_TIME'] ?? '';
        $data .= $_SERVER['HTTP_USER_AGENT'] ?? '';
        $data .= $_SERVER['LOCAL_ADDR'] ?? '';
        $data .= $_SERVER['LOCAL_PORT'] ?? '';
        $data .= $_SERVER['REMOTE_ADDR'] ?? '';
        $data .= $_SERVER['REMOTE_PORT'] ?? '';
        $hash = strtoupper(hash('ripemd128', $uid . $guid . md5(sha1($data))));
        $guid =
            substr($hash, 0, 8) .
            '-' .
            substr($hash, 8, 4) .
            '-' .
            substr($hash, 12, 4) .
            '-' .
            substr($hash, 16, 4) .
            '-' .
            substr($hash, 20, 12);
        return $guid;
    }
}

if (!function_exists('deleteDir')) {
    /**
     * 递归删除一个非空的目录
     *
     * @param $directory
     * @return bool
     */
    function deleteDir($directory): bool
    {
        if (file_exists($directory) && is_dir($directory)) {//判断目录是否存在，如果不存在rmdir()函数会出错
            $files = scandir($directory);
            array_map(function ($filename) use ($directory) {
                if ($filename === '.' || $filename === '..') return true;
                $subFilename = $directory . DIRECTORY_SEPARATOR . $filename;
                if (is_dir($subFilename)) {
                    return deleteDir($subFilename);
                }
                if (is_file($subFilename)) {
                    return unlink($subFilename);
                }
                return true;
            }, $files);
            return rmdir($directory);
        }
        return true;
    }
}

if (!function_exists('getClassName')) {
    /**
     * 去除命名空间,返回纯类名
     *
     * @param string $namespaceClassName 带命名空间的类名
     * @return string
     */
    function getClassName(string $namespaceClassName): string
    {
        $texts = explode('\\', $namespaceClassName);
        return end($texts);
    }
}
if (!function_exists('arrayToXml')) {
    /**
     * 将数组转换成XML,仅支持一维数组
     *
     * @param array $arr
     * @return string
     */
    function arrayToXml(array $arr): string
    {
        $xml = '<xml>';
        foreach ($arr as $key => $val) {
            $xml .= is_numeric($val) ? "<$key>$val</$key>" : "<$key><![CDATA[$val]]></$key>";
        }
        $xml .= '</xml>';
        return $xml;
    }
}

if (!function_exists('curlPost')) {

    /**
     * 简单的post请求
     *
     * @param string $url
     * @param mixed $data
     * @param array|false $ssl
     * @return string
     */
    function curlPost(string $url, mixed $data, array|false $ssl = false): string
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        if (is_array($ssl)) {
            //使用证书：cert 与 key 分别属于两个.pem文件
            curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'pem');
            curl_setopt($ch, CURLOPT_SSLCERT, $ssl['ssl_cert_path']);
            curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'pem');
            curl_setopt($ch, CURLOPT_SSLKEY, $ssl['ssl_key_path']);
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }


        //设置为访问方式为post
        curl_setopt($ch, CURLOPT_POST, true);
        //设置post数据
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $returnData = curl_exec($ch);
        curl_close($ch);
        return is_bool($returnData) ? '' : $returnData;
    }
}

if (!function_exists('sha256')) {
    /**
     * sha256
     * 补充只有sha1
     *
     * @param string $data
     * @param bool $rawOutput
     * @return false|string
     */
    function sha256(string $data, bool $rawOutput = false): false|string
    {
        return hash('sha256', $data, $rawOutput);
    }
}

if (!function_exists('filePutContents')) {
    /**
     * 写入文件
     * 带权限设置,默认权限0774|rwx+rwx+r
     *
     * @param string $filename
     * @param mixed $data
     * @param int $flags
     * @param int $mode
     * @return bool|int
     */
    function filePutContents(string $filename, mixed $data, int $flags = 0, int $mode = 0774): bool|int
    {
        $re = file_put_contents($filename, $data, $flags);
        if ($re !== false) chmod($filename, $mode);
        return $re;
    }
}

if (!function_exists('humpToUnderscore')) {
    /**
     * 驼峰转下划线
     *
     * @param string $name
     * @return string
     */
    function humpToUnderscore(string $name): string
    {
        $name = lcfirst($name);
        $dstr = preg_replace_callback('/([A-Z]+)/', function ($matches) {
            return '_' . strtolower($matches[0]);
        }, $name);
        return trim(preg_replace('/_{2,}/', '_', $dstr), '_');
    }
}

if (!function_exists('cliParams')) {
    /**
     * 获取命令行的所有参数
     * 转化成键对值模式,没有键则转化成数字
     *
     * @return array
     */
    function cliParams(): array
    {
        global $argv;
        $params = $argv;
        $data = [];
        foreach ($params as $param) {
            $d = explode('=', $param);
            if (count($d) === 2) {
                $data[$d[0]] = $d[1];
            } else {
                $data[] = $param;
            }
        }
        return $data;
    }
}
if (!function_exists('getMillisecond')) {
    /**
     * 获取毫秒时间戳
     *
     * @return int
     */
    function getMillisecond(): int
    {
        list($microsecond, $time) = explode(' ', microtime()); //' '中间是一个空格
        return (int)sprintf('%.0f', (floatval($microsecond) + floatval($time)) * 1000);
    }
}

if (!function_exists('getNowTimeBlockID')) {
    /**
     * 获取当前时间块ID
     *
     * @param int $minute 以多少分钟为一个时间块
     * @return int
     */
    function getNowTimeBlockID(int $minute = 30): int
    {
        //小数点代表把$minute分割成的百分比,直接舍去代表这一整个块
        return getMillisecond() / ($minute * 60 * 1000);
    }
}

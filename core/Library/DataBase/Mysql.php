<?php

namespace ApiCore\Library\DataBase;

use ApiCore\Library\ApiRestful\ApiCode;
use ApiCore\Library\Exception\InsideException;

class Mysql
{

    protected static ?\PDO $_pdo = null;

    protected static int $transCount = 0;

    /**
     * 持久连接mysql数据库,将会设定编码为utf8mb4
     *
     * @param string $host
     * @param string $port
     * @param string $dbname
     * @param string $username
     * @param string $password
     * @param string $code 数据库编码
     * @return void
     * @throws \Exception
     */
    public static function connect(string $host, string $port, string $dbname, string $username, string $password, string $code = 'utf8mb4'): void
    {
        $dbms = 'mysql';
        $_opts_values = [
            \PDO::ATTR_PERSISTENT => true,
            \PDO::ATTR_EMULATE_PREPARES => false,
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . $code
        ];
        try {
            self::$_pdo = new \PDO("{$dbms}:host={$host};port={$port};dbname={$dbname}", $username, $password, $_opts_values);
        } catch (\Exception $exception) {
            throw  new \Exception('数据服务出错', ApiCode::SERVER_ERROR);
        }
    }

    /**
     * 获取已经连接的PDO实例
     *
     * @return \PDO
     * @throws \Exception
     */
    public static function getPDO(): \PDO
    {
        if (is_null(self::$_pdo) || !self::pdoPing()) {
            $config = config('database');
            self::connect($config['HOST'], $config['PORT'], $config['DATABASE'], $config['USERNAME'], $config['PASSWORD'], $config['CODE']);
        }
        return self::$_pdo;
    }

    /**
     * 检查一下PDO连接是否还存活
     *
     * @return bool
     */
    protected static function pdoPing(): bool
    {
        try {
            self::$_pdo->getAttribute(\PDO::ATTR_SERVER_INFO);
        } catch (\PDOException $e) {
            if (str_contains($e->getMessage(), 'MySQL server has gone away')) {
                return false;
            }
        }
        return true;
    }
}

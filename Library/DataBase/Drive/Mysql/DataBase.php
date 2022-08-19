<?php

namespace ApiCore\Library\DataBase\Drive\Mysql;

use ApiCore\Library\Cache\FileStorage;

abstract class DataBase
{
    /**
     * PDO对象
     *
     * @var null|\PDO
     */
    protected static ?\PDO $PDO = null;

    /**
     * 主键
     *
     * @var string
     */
    protected static string $primaryKey = 'id';

    /**
     * 当前数据模型的对应表名
     *
     * @var null|string
     */
    protected static ?string $table = null;

    /**
     * 当前数据模型的所有字段;
     * 使用application模块model控制器创建的数据模型将会自动填入当前数据模型所有字段
     *
     * @var string[]
     */
    protected static array $columns = [];

    /**
     * 用于事务计数
     *
     * @var int
     */
    private static int $transCount = 0;


    public function __construct()
    {
        self::PDO();
    }

    /**
     * 获取PDO对象
     *
     * @return \PDO
     * @throws \Exception
     */
    public static function PDO(): \PDO
    {
        if (!is_null(self::$PDO)) return self::$PDO;
        self::$PDO = Connect::getPDO();
        return self::$PDO;
    }

    public static function __callStatic(string $name, array $arguments)
    {
        // TODO: Implement __callStatic() method.
    }

    /**
     * 查询一条数据
     *
     * @param string $sql
     * @param array $bindData
     * @return array
     */
    public static function selectOne(string $sql, array $bindData = []): array
    {
        $statement = self::PDO()->prepare($sql);
        foreach ($bindData as $k => $data) {
            $statement->bindValue($k + 1, $data);
        }
        if ($statement->execute()) {
            $re = $statement->fetch(\PDO::FETCH_ASSOC);
            return is_array($re) ? $re : [];
        }
        return [];
    }

    /**
     * 查询数据
     *
     * @param string $sql
     * @param array $bindData
     * @return array
     * @throws \Exception
     */
    public static function select(string $sql, array $bindData = []): array
    {
        $statement = self::PDO()->prepare($sql);
        foreach ($bindData as $k => $data) {
            $statement->bindValue($k + 1, $data);
        }
        if ($statement->execute()) {
            return $statement->fetchAll(\PDO::FETCH_ASSOC);
        }
        return [];
    }

    /**
     * 根据条件判断是否存在
     *
     * @param string $where 查询条件
     * @param array $bindData 绑定数据
     * @param string|null $table 指定的表格,可以join
     * @return bool
     * @throws \Exception
     */
    public static function exists(string $where, array $bindData = [], string $table = null): bool
    {
        $table = is_null($table) ? self::table() : $table;
        return static::selectOne(SQL::Exists($table, $where), $bindData)['exists'] === 1;
    }

    /**
     * 根据条件查询数量
     *
     * @param string $where 查询条件
     * @param array $bindData 绑定数据
     * @param string|null $table 指定的表格,可以join
     * @return int
     */
    public static function count(string $where, array $bindData = [], string $table = null): int
    {
        $table = is_null($table) ? self::table() : $table;
        return static::selectOne(SQL::Count($table, $where), $bindData)['count'];
    }

    /**
     * 根据条件删除
     * 返回被删除的行数
     *
     * @param string $where 查询条件
     * @param array $bindData 绑定数据
     * @param string|null $table 指定的表格
     * @return int
     * @throws \Exception
     */
    public static function delete(string $where, array $bindData = [], string $table = null): int
    {
        $table = is_null($table) ? self::table() : $table;
        $statement = self::PDO()->prepare(SQL::Delete($table, $where));
        foreach ($bindData as $k => $data) {
            $statement->bindValue($k + 1, $data);
        }
        if ($statement->execute()) {
            return $statement->rowCount();
        }
        return 0;
    }

    /**
     * 插入数据
     *
     * @param array $data 要插入的数据 [key=>val,key2.=>val2...],所有的key会与$this->columns判断是否存在
     * @param string|null $table 指定的表格
     * @return bool
     * @throws \Exception
     */
    protected static function insert(array $data, string|null $table = null): bool
    {
        $table = is_null($table) ? self::table() : $table;
        $keys = [];
        $values = [];
        foreach ($data as $key => $value) {
            if (in_array($key, static::$columns)) {
                $keys[] = $key;
                $values[] = $value;
            } else {
                throw new \Exception($key . '字段不存在于$this->columns中');
            }
        }
        $KeyPlaceholder = implode(',', $keys);
        $ValuePlaceholder = self::getPlaceholder(count($values));
        $statement = self::PDO()->prepare(SQL::Insert($table, $KeyPlaceholder, $ValuePlaceholder));
        foreach ($values as $k => $value) {
            $statement->bindValue($k + 1, $value);
        }
        self::PDO()->lastInsertId();
        return $statement->execute();
    }

    /**
     * 插入数据并获取id
     *
     * @param array $data 要插入的数据 [key=>val,key2.=>val2...],所有的key会与$this->columns判断是否存在
     * @param string|null $table 指定的表格
     * @return bool
     * @throws \Exception
     */
    protected static function insertGetId(array $data, string|null $table = null): bool|string
    {
        $b = self::insert($data, $table);
        if ($b) return self::PDO()->lastInsertId();
        return false;
    }

    /**
     * @param array $data 要更新的数据 [key=>val,key2.=>val2...],所有的key会与$this->columns判断是否存在
     * @param string $where
     * @param array $bindData
     * @param string|null $table 指定表格
     * @return int
     * @throws \Exception
     */
    public static function update(array $data, string $where, array $bindData, string $table = null): int
    {
        if (self::isSelf()) return throw new \Exception('不可以在DataBase中调用');
        $table = is_null($table) ? self::table() : $table;
        $keys = [];
        $values = [];
        foreach ($data as $key => $value) {
            if (in_array($key, static::$columns) && strtolower($key) !== 'id') {
                $keys[] = $key . '=?';
                $values[] = $value;
            } else {
                throw new \Exception($key . '字段不存在于$this->columns中且字段名不能为id');
            }
        }

        $KeyPlaceholder = implode(',', $keys);
        $statement = static::PDO()->prepare(SQL::Update($table, $where, $KeyPlaceholder));
        foreach (array_merge($values, $bindData) as $k => $value) {
            $statement->bindValue($k + 1, $value);
        }
        return $statement->execute() ? $statement->rowCount() : 0;
    }

    /**
     * 优化版分页
     *
     * @param string[] $select 要查询的字段,需要带上表名,当$tableJoin为空的时候,会自动附加表名可以不附带
     * @param int $page 第几页
     * @param int $pageSize 每页显示多少条
     * @param string $where 筛选条件
     * @param array $bindData 筛选条件绑定的数据
     * @param string|null $table 要查询的表,不填时使用static::$table
     * @param string $tableJoin 要连接的表,连接表请使用此参数,而不是写在$table参数中,$table参数会单独当主表名使用
     * @return array|string 发生错误的时候将会返回string
     */
    public static function ListForPage(array $select, int $page, int $pageSize = 15, string $where = '', array $bindData = [], ?string $table = null, string $tableJoin = '', string $orderBy = 'ASC'): array|string
    {
        if (self::isSelf()) return '不可以在DataBase中调用,该方法只允许在数据模型调用';
        if (is_null($table) && is_null(static::$table)) return 'static::$table没有初始化';
        $table = is_null($table) ? static::$table : $table;
        $primaryKey = static::$primaryKey;

        $skip = $pageSize * ($page - 1);
        $select = array_map(function ($column) use ($table, $tableJoin) {
            if (empty($tableJoin) && strpos('.', $column, 1) === false) {
                return "`$table`.`$column`";
            }
            return $column;
        }, $select);

        $selectStr = implode(',', $select);


        $mainSql = SQL::Select("`$table`.`$primaryKey`", "`$table` $tableJoin", $where, "order by `$table`.`$primaryKey` $orderBy limit $skip,$pageSize");
        $sql = SQL::Select($selectStr, "`$table` $tableJoin inner join ($mainSql) filter_table ON filter_table.`$primaryKey`=`$table`.`$primaryKey`", '', "ORDER BY `$table`.`$primaryKey` $orderBy");

        try {

            $countSql = SQL::Count("$table $tableJoin", $where);
            $hashKey = sha1($countSql . json_encode($bindData));
            $cache = (new FileStorage('DataBase'));
            $count = $cache->get($hashKey);
            if (!is_numeric($count)) {
                $count = static::selectOne($countSql, $bindData)['count'];
                try {
                    //节约开销,2秒缓存,十分合理
                    $cache->set($hashKey, $count, new \DateInterval('PT2S'));
                } catch (\Exception $e) {
                    return $e->getMessage();
                }
            }

            $data = [
                'list' => static::select($sql, $bindData),
                'page' => [
                    'count' => (int)ceil($count / $pageSize),
                    'current' => $page,
                    'size' => $pageSize
                ]
            ];


        } catch (\PDOException $exception) {
            return 'ListForPage查询错误';
        }
        return $data;
    }

    /**
     * 获取指定数量的问号占位符
     *
     * @param int $number
     * @return string
     */
    public static function getPlaceholder(int $number): string
    {
        $str = [];
        for ($i = 0; $number > $i; $i++) $str[] = '?';
        return implode(',', $str);
    }


    /**
     * 启动一个事务
     * @return bool
     * @throws \Exception
     */
    final public static function beginTransaction(): bool
    {
        if (!self::isSelf()) return throw new \Exception('事务只可以在DataBase中调用');
        ++static::$transCount;
        if (1 === static::$transCount) {
            return self::PDO()->beginTransaction();
        }
        return self::PDO()->exec('SAVEPOINT trans' . static::$transCount) !== false;
    }

    /**
     * 提交一个事务
     * @throws \Exception
     */
    final  public static function commit(): bool
    {
        if (!self::isSelf()) return throw new \Exception('事务只可以在DataBase中调用');
        --static::$transCount;
        if (static::$transCount == 0) return self::PDO()->commit();
        return static::$transCount >= 0;
    }

    /**
     * 回滚一个事务
     *
     * @return bool
     * @throws \Exception
     */
    final public static function rollback(): bool
    {
        if (!self::isSelf()) return throw new \Exception('事务只可以在DataBase中调用');
        if ((static::$transCount - 1) === -1) return false;
        --static::$transCount;
        if (static::$transCount == 0) return self::PDO()->rollback();
        //因为前面减一了但又不是最后一层 所以这里回滚加一补偿回去
        self::PDO()->exec('ROLLBACK TO trans' . (static::$transCount + 1));
        return true;
    }

    /**
     * 判断是否为自身
     *
     * @return bool
     */
    private static function isSelf(): bool
    {
        return static::class === self::class;
    }

    /**
     * 获取当前类转化成的表名
     *
     * @return string
     * @throws \Exception
     */
    final public static function table(): string
    {
        if (self::isSelf()) return throw new \Exception('不可以在DataBase中调用table,这不被认为是安全的操作');

        if (!is_null(static::$table)) return static::$table;
        $className = lcfirst(getClassName(static::class));
        $dstr = preg_replace_callback('/([A-Z]+)/', function ($matches) {
            return '_' . strtolower($matches[0]);
        }, $className);
        return trim(preg_replace('/_{2,}/', '_', $dstr), '_');
    }

}
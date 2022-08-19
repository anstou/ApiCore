<?php

namespace ApiCore\Library\DataBase\Drive\Mysql;

class SQL
{

    /**
     * 创建数据库
     *
     * @param string $tableName
     * @return string
     */
    public static function CreateDatabase(string $tableName): string
    {
        //utf8mb4_general_ci可以友好的支持emoji
        return "CREATE DATABASE `$tableName` CHARACTER SET 'utf8mb4' COLLATE 'utf8mb4_general_ci'";
    }

    /**
     * 检查条件是否存在匹配的数据
     *
     * @param string $table 表名
     * @param string $where 条件 a=? AND b=? OR ....
     * @return string
     */
    public static function Exists(string $table, string $where): string
    {
        return "SELECT EXISTS ( SELECT * FROM $table WHERE $where ) AS `exists`";
    }


    /**
     * 根据条件计算匹配的数量
     *
     * @param string $table 表名
     * @param string $where 条件 a=? AND b=? OR ....
     * @return string
     */
    public static function Count(string $table, string $where): string
    {
        return "SELECT COUNT(*) as `count` FROM $table WHERE $where";
    }

    /**
     * 插入数据
     *
     * @param string $table 表名
     * @param string $KeyPlaceholder 键名
     * @param string $ValuePlaceholder 键值占位 基本传入为对应$KeyPlaceholder数量的"?"
     * @return string
     */
    public static function Insert(string $table, string $KeyPlaceholder, string $ValuePlaceholder): string
    {
        return "insert into $table($KeyPlaceholder) values($ValuePlaceholder)";
    }

    /**
     * 根据条件删除符合的数据
     *
     * @param string $table 表名
     * @param string $where 条件 a=? AND b=? OR ....
     * @return string
     */
    public static function Delete(string $table, string $where): string
    {
        return "DELETE FROM $table WHERE $where";
    }

    /**
     * 根据条件更新内容
     *
     * @param string $table 表名
     * @param string $where 条件 a=? AND b=? OR ....
     * @param string $KeyPlaceholder 要更新的内容 比如:name=?,sex=?,age=? 请不要传入实际值
     * @return string
     */
    public static function Update(string $table, string $where, string $KeyPlaceholder): string
    {
        return "UPDATE $table SET $KeyPlaceholder WHERE $where";
    }

    /**
     * @param string $select 要查询的数据
     * @param string $table 表名
     * @param string $where 条件 a=? AND b=? OR ....
     * @param string $extra 额外的,会增加在where后
     * @return string
     */
    public static function Select(string $select, string $table, string $where, string $extra = ''): string
    {
        $where = empty($where) ? '' : 'WHERE ' . $where;
        return "SELECT $select FROM $table $where $extra";
    }

}
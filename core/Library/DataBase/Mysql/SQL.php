<?php

namespace ApiCore\Library\DataBase\Mysql;

class SQL
{

    /**
     * 创建数据库
     *
     * @param string $dataBaseName
     * @return string
     */
    public static function createDatabase(string $dataBaseName): string
    {
        //utf8mb4_general_ci可以友好的支持emoji
        return "CREATE DATABASE `$dataBaseName` CHARACTER SET 'utf8mb4' COLLATE 'utf8mb4_general_ci'";
    }
}
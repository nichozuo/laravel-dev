<?php

namespace LaravelDev\DBTools;

use Doctrine\DBAL\Exception;
use Illuminate\Support\Facades\Cache;
use LaravelDev\DBTools\Models\DBModel;
use LaravelDev\DBTools\Models\DBTableModel;

class DBToolsServices
{
    /**
     * @return void
     * @throws Exception
     */
    public static function CacheDBModel(): void
    {
        Cache::store('file')->put('_dev_DBModel', GenDBModelServices::GenDBModel());
    }

    /**
     * @return DBModel
     */
    public static function GetDBModel(): DBModel
    {
        return Cache::store('file')->rememberForever('_dev_DBModel', function () {
            return GenDBModelServices::GenDBModel();
        });
    }

    /**
     * @param string $tableName
     * @return DBTableModel
     * @throws \Exception
     */
    public static function GetTable(string $tableName): DBTableModel
    {
        $dbModel = self::GetDBModel();
        $table = $dbModel->tables[$tableName] ?? null;
        if (!$table)
            throw new \Exception("table $tableName not found");
        return $table;
    }
}
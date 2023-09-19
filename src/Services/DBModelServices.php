<?php

namespace LaravelDev\Services;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\Type;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LaravelDev\Models\DBModel\DBModel;
use LaravelDev\Models\DBModel\DBTableColumnModel;
use LaravelDev\Models\DBModel\DBTableModel;

class DBModelServices
{
    const DBType2ColumnType = [
//        'int' => 'integer',
//        'tinyint' => 'integer',
        'smallint' => 'integer',
//        'mediumint' => 'integer',
        'integer' => 'integer',
        'bigint' => 'integer',
        'string' => 'string',
        'json' => 'array',
        'float' => 'numeric',
//        'double' => 'float',
        'decimal' => 'numeric',
        'date' => 'date',
        'datetime' => 'date',
//        'timestamp' => 'timestamp',
        'time' => 'time',
//        'year' => 'year',
//        'char' => 'string',
//        'varchar' => 'string',
//        'tinyblob' => 'string',
//        'tinytext' => 'string',
//        'blob' => 'string',
        'text' => 'string',
//        'mediumblob' => 'string',
//        'mediumtext' => 'string',
//        'longblob' => 'string',
//        'longtext' => 'string',
//        'enum' => 'string',
//        'set' => 'string',
//        'binary' => 'string',
//        'varbinary' => 'string',
//        'point' => 'string',
//        'linestring' => 'string',
//        'polygon' => 'string',
//        'geometry' => 'string',
//        'multipoint' => 'string',
//        'multilinestring' => 'string',
//        'multipolygon' => 'string',
//        'geometrycollection' => 'string',
        'boolean' => 'boolean'
    ];

    /**
     * @return void
     * @throws Exception
     */
    public static function ForceCache(): void
    {
        Cache::store('file')->put('_dev_DBModel', DBModelServices::GenDBModel());
    }

    /**
     * @return DBModel
     */
    public static function GetDBModel(): DBModel
    {
        return Cache::store('file')->rememberForever('_dev_DBModel', function () {
            return DBModelServices::GenDBModel();
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

    /**
     * @return DBModel
     * @throws Exception
     */
    public static function GenDBModel(): DBModel
    {
        $sm = DB::getDoctrineSchemaManager();
        $sm->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');

        $types = self::getTypes();

        // process db model
        $tables = $sm->listTables();
        $dbModel = new DBModel();
        $hasApiTokens = config('project.hasApiTokens');
        $hasRoles = config('project.hasRoles');
        $hasNodeTrait = config('project.hasNodeTrait');

        // process table keys
        foreach ($tables as $table) {
            $dbModel->tableKeys[] = $table->getName();
        }

        // process table model
        foreach ($tables as $table) {
            // parse table model
            $tableModel = new DBTableModel();
            $tableModel->name = $table->getName();
            $tableModel->comment = $table->getComment();
            $tableModel->modelName = str()->of($tableModel->name)->studly();

            if (in_array($tableModel->name, $hasApiTokens))
                $tableModel->hasApiTokens = true;
            if (in_array($tableModel->name, $hasRoles))
                $tableModel->hasRoles = true;
            if (in_array($tableModel->name, $hasNodeTrait))
                $tableModel->hasNodeTrait = true;

            foreach ($table->getColumns() as $column) {
                // parse column model
                $name = $column->getName();
                $comment = $column->getComment();
                list($isForeignKey, $foreignTableName) = self::parseColumnForeignInfo($dbModel, $column);

                $columnModel = new DBTableColumnModel();
                $columnModel->name = $name;
                $columnModel->dbType = $types[$column->getType()::class] ?? 'unknown';
                $columnModel->phpType = self::DBType2ColumnType[$columnModel->dbType] ?? 'unknown';
                $columnModel->length = $column->getLength();
                $columnModel->precision = $column->getPrecision();
                $columnModel->scale = $column->getScale();
                $columnModel->notNull = $column->getNotnull();
                $columnModel->comment = $comment;
                $columnModel->default = $column->getDefault();
                $columnModel->isPrimaryKey = $column->getAutoincrement();
                $columnModel->isForeignKey = $isForeignKey;
                $columnModel->foreignTable = $foreignTableName;
                $columnModel->nullableString = $columnModel->notNull ? 'required' : 'nullable';

                // update table model
                $isSkipField = in_array($name, ['id', 'created_at', 'updated_at', 'deleted_at']);
                $isDeletedAt = $name == 'deleted_at';
                $isJsonField = $columnModel->dbType == 'json';

                $tableModel->columns[$name] = $columnModel;
                $tableModel->columnNames[] = $name;
                $tableModel->modelProperties[] = "* @property $columnModel->phpType \$$columnModel->name";

                if (!$isSkipField) {
                    $tableModel->fillable[] = $name;
                    $tableModel->validate[] = "'$columnModel->name' => '$columnModel->nullableString|$columnModel->phpType', # $columnModel->comment";
                    $tableModel->insert[] = "'$columnModel->name' => '', # $columnModel->comment";
                }
                if ($isDeletedAt) {
                    $tableModel->hasSoftDelete = true;
                }
                if ($isJsonField) {
                    $tableModel->casts[] = "'$columnModel->name' => 'array',";
                }

                if ($columnModel->isForeignKey) {
                    $tableModel->foreignKey[$columnModel->name] = $columnModel->foreignTable;
                }

                if (in_array($tableModel->name, ['_lft', '_rgt']))
                    $tableModel->hasNodeTrait = true;

                if (str_contains($columnModel->comment, '[hidden]')) {
                    $columnModel->isHidden = true;
                    $tableModel->hidden[] = $columnModel->name;
                }
            }

            $dbModel->tables[$tableModel->name] = $tableModel;
        }

//        // 处理 $hasMany $belongsTo 关联关系
        foreach ($dbModel->tables as $tableModel) {
            // hasMany
            $tableModel->hasMany = self::parseHasMany($dbModel, $tableModel);
            // belongsTo
            $tableModel->belongsTo = self::parseBelongsTo($tableModel);
            // belongsToMany
            $tableModel->relationsString = self::parseRelationsStr($tableModel);
            if (count($tableModel->hasMany) || count($tableModel->belongsTo))
                $tableModel->hasRelation = true;
        }
        return $dbModel;
    }

    /**
     * @return array
     */
    private static function getTypes(): array
    {
        $types = [];
        foreach (Type::getTypesMap() as $key => $value) {
            $types[$value] = $key;
        }
        return $types;
    }

    /**
     * @param DBModel $db
     * @param Column $column
     * @return array
     */
    private static function parseColumnForeignInfo(DBModel $db, Column $column): array
    {
        if ($column->getType()->getName() != 'bigint')
            return [false, null];
        if (!str()->of($column->getName())->endsWith('s_id'))
            return [false, null];

        $comment = $column->getComment();
        $name = $column->getName();

        // foreign key 备注中有：ref[表名]
        if (str()->of($comment)->contains("[ref:")) {
            $foreignTableName = str()->of($comment)->between("[ref:", "]");
            return [true, $foreignTableName];
        }

        // foreign key 列名称：表名+_id
        $foreignTableName = str()->of($name)->before('_id');
        if (in_array($foreignTableName, $db->tableKeys)) {
            return [true, $foreignTableName];
        }

        return [false, null];
    }

    /**
     * @param DBModel $dbModel
     * @param DBTableModel $tableModel
     * @return array
     */
    private static function parseHasMany(DBModel $dbModel, DBTableModel $tableModel): array
    {
        $hasMany = [];
        foreach ($dbModel->tables as $table) {
            // 排除自己
            if ($table->name == $tableModel->name)
                continue;
            // 是否有跟自己相关的外键
            foreach ($table->foreignKey as $foreignKey => $foreignTableName) {
                if ($foreignTableName == $tableModel->name)
                    $hasMany[$table->name] = [
                        'related' => Str::of($table->name)->studly()->toString(),
                        'foreignKey' => $foreignKey,
                        'localKey' => 'id'
                    ];
            }
        }
        return $hasMany;
    }

    /**
     * @param DBTableModel $tableModel
     * @return array
     */
    private static function parseBelongsTo(DBTableModel $tableModel): array
    {
        $belongsTo = [];
        foreach ($tableModel->foreignKey as $foreignKey => $foreignTableName) {
            $belongsTo[str()->of(str_replace('_id', '', $foreignKey))->singular()->toString()] = [
                'related' => Str::of($foreignTableName)->studly()->toString(),
                'foreignKey' => $foreignKey,
                'ownerKey' => 'id'
            ];
        }
        return $belongsTo;
    }

    /**
     * @param DBTableModel $tableModel
     * @return string
     */
    private static function parseRelationsStr(DBTableModel $tableModel): string
    {
        $str = "# relations" . PHP_EOL;

        // hasMany
        foreach ($tableModel->hasMany as $key => $value) {
            $str .= "    public function $key(): Relations\HasMany
    {
        return \$this->hasMany(Models\\{$value['related']}::class, '{$value['foreignKey']}', '{$value['localKey']}');
    }" . PHP_EOL . PHP_EOL;
        }

        // belongsTo
        foreach ($tableModel->belongsTo as $key => $value) {
            $str .= "    public function $key(): Relations\BelongsTo
    {
        return \$this->belongsTo(Models\\{$value['related']}::class, '{$value['foreignKey']}', '{$value['ownerKey']}');
    }" . PHP_EOL . PHP_EOL;
        }

        return $str;
    }

    /**
     * @param DBTableModel $tableModel
     * @return string
     */
    public static function ParseCasts(DBTableModel $tableModel): string
    {
        if (count($tableModel->casts) == 0)
            return '';

        $castsString = "protected \$casts = [\n\t\t";
        $castsString .= implode("\n\t\t", $tableModel->casts);
        $castsString .= "\n\t];\n";
        return $castsString;
    }
}
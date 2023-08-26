<?php

namespace LaravelDev\DBTools;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LaravelDev\DBTools\Models\DBModel;
use LaravelDev\DBTools\Models\DBTableColumnModel;
use LaravelDev\DBTools\Models\DBTableModel;

class DBToolsServices
{
    /**
     * @return void
     * @throws Exception
     */
    public static function CacheDBModel(): void
    {
        Cache::store('file')->put('_dev_DBModel', self::GenDBModel());
    }

    /**
     * @return DBModel
     */
    public static function GetDBModel(): DBModel
    {
        return Cache::store('file')->rememberForever('_dev_DBModel', function () {
            return self::GenDBModel();
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

        // process table keys
        foreach ($tables as $table) {
            $dbModel->tableKeys[] = $table->getName();
        }

        // process table model
        foreach ($tables as $table) {
            $tableModel = self::parseDBTableModel($table);
            // process column model
            foreach ($table->getColumns() as $column) {
                $columnModel = self::parseDBTableColumnModel($dbModel, $column, $types);
                $tableModel->columns[$column->getName()] = $columnModel;
                if ($columnModel->isForeignKey) {
                    $tableModel->foreignKeys[$columnModel->name] = $columnModel->foreignTable;
                }
            }
            self::parseDBTableModelAfterColumnsIsOk($tableModel);
//            $tableModel->parse($dbModel);
            $dbModel->tables[$table->getName()] = $tableModel;
        }

        // 处理BelongsTo关联关系
        foreach ($dbModel->tables as $tableModel) {
            // hasMany
            $tableModel->hasMany = self::parseHasMany($dbModel, $tableModel);
            // belongsTO
            $tableModel->belongsTo = self::parseBelongsTo($tableModel);
            // belongsToMany
            $tableModel->relationsString = self::parseRelationsStr($tableModel);
            if (count($tableModel->hasMany) || count($tableModel->belongsTo))
                $tableModel->hasRelations = true;
        }
        return $dbModel;
    }

    /**
     * @param Table $table
     * @return DBTableModel
     */
    private static function parseDBTableModel(Table $table): DBTableModel
    {
        $model = new DBTableModel();
        $model->name = $table->getName();
        $model->comment = $table->getComment();
        $model->modelName = Str::of($model->name)->studly();
        return $model;
    }

    /**
     * @param DBModel $dbModel
     * @param Column $column
     * @param array $types
     * @return DBTableColumnModel
     */
    private static function parseDBTableColumnModel(DBModel $dbModel, Column $column, array $types): DBTableColumnModel
    {
        $name = $column->getName();
        $comment = $column->getComment();

        $model = new DBTableColumnModel();
        $model->name = $name;
        $model->dbType = $types[$column->getType()::class] ?? 'unknown';
        $model->phpType = Constants::DBType2ColumnType[$model->dbType] ?? 'unknown';
        $model->length = $column->getLength();
        $model->precision = $column->getPrecision();
        $model->scale = $column->getScale();
        $model->notNull = $column->getNotnull();
        $model->comment = $comment;
        $model->default = $column->getDefault();
        $model->isPrimaryKey = $column->getAutoincrement();

        // foreign key 备注中有：ref[表名]
        if (Str::of($comment)->contains("ref[")) {
            $foreignTableName = Str::of($comment)->between("ref[", "]");
            if (in_array($foreignTableName, $dbModel->tableKeys)) {
                $model->isForeignKey = true;
                $model->foreignTable = $foreignTableName;
            }
        }
        // foreign key 列名称：表名+_id
        if (Str::of($name)->contains('_id') && !$model->isForeignKey && $column->getType()->getName() == 'bigint') {
            $foreignTableName = Str::of($name)->before('_id');
            if (in_array($foreignTableName, $dbModel->tableKeys)) {
                $model->isForeignKey = true;
                $model->foreignTable = $foreignTableName;
            }
        }

        // 扩展的属性
        $model->nullableString = $model->notNull ? 'required' : 'nullable';
        return $model;
    }

    /**
     * @param DBTableModel $table
     * @return void
     */
    private static function parseDBTableModelAfterColumnsIsOk(DBTableModel $table): void
    {
        // model @property
        $modelPropertiesString = '';
        foreach ($table->columns as $column) {
            $table->keys[] = $column->name;
            $modelPropertiesString .= " * @property $column->phpType \$$column->name\n";
        }
        $table->modelPropertiesString = $modelPropertiesString;

        // $fillable
        $fillable = array_diff($table->keys, ['id', 'created_at', 'updated_at', 'deleted_at']);
        foreach ($fillable as &$string) {
            $string = "'$string'";
        }
//        $table->fillable = $fillable;
        $table->fillableString = implode(', ', $fillable);

        $validateString = $insertString = $castsString = '';
        foreach ($table->columns as $column) {
            if ($column->isPrimaryKey)
                continue;

            $inArray = in_array($column->name, ['id', 'created_at', 'updated_at', 'deleted_at']);

            // validateString
            if (!$inArray)
                $validateString .= "'$column->name' => '$column->nullableString|$column->phpType', # $column->comment\n";

            // insertString
            if (!$inArray)
                $insertString .= "'$column->name' => '', # $column->comment\n";

            // softDeletes
            if ($column->name === 'deleted_at') {
                $table->hasSoftDeletes = true;
            }

            // castsString
            if($column->dbType == 'json') {
                $castsString .= "\t\t'$column->name' => 'array',\n";
            }
        }
        $table->validateString = $validateString;
        $table->insertString = $insertString;
        if($castsString)
            $table->castsString = "protected \$casts = [\n" . $castsString . "\t];\n";
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
            foreach ($table->foreignKeys as $foreignKey => $foreignTableName) {
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
        foreach ($tableModel->foreignKeys as $foreignKey => $foreignTableName) {
            $belongsTo[Str::of($foreignTableName)->singular()->toString()] = [
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

//    /**
//     * @return array
//     */
//    public static function GenDocTree(): array
//    {
//        $tree = [];
//        foreach (self::GetDBModel()->tables as $table) {
//            $tree[] = [
//                'key' => $table->name,
//                'title' => $table->name,
//                'description' => $table->comment,
//                'isLeaf' => true,
//            ];
//        }
//        return $tree;
//    }

//    /**
//     * @return array
//     */
//    public static function GenDocList(): array
//    {
//        $nodes = [];
//        foreach (self::GetDBModel()->tables as $table) {
//            $columns = [];
//            foreach ($table->columns as $key => $column) {
//                $columns[] = $column;
//            }
//
//            $nodes[$table->name] = [
//                'key' => $table->name,
//                'title' => $table->name,
//                'description' => $table->comment,
//                'columns' => $columns,
//            ];
//        }
//        return $nodes;
//    }

    /**
     * @param string $type
     * @return string
     */
    private function parseColumnType(string $type): string
    {
        $type = strtolower($type);
        return Constants::DBType2ColumnType[$type] ?? 'unknown';
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
}
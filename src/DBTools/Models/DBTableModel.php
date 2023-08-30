<?php

namespace LaravelDev\DBTools\Models;


class DBTableModel
{
    public string $name;
    public string $comment;
    public string $modelName; // 模型名称
    /**
     * @var DBTableColumnModel[]
     */
    public array $columns = [];
    public array $columnNames = []; // 所有的字段名
    public bool $hasSoftDelete = false;
    public array $foreignKey = []; // 外键
    public array $hasMany = []; // 关系
    public array $belongsTo = []; // 关系
    public string $relationsString = ''; // 关系
    public bool $hasRelation = false;
    public array $fillable;

    public array $modelProperties = []; // BaseModel文件中的property注释

    public array $validate = []; // 验证字符串
    public array $insert = []; // 插入符串
    public ?array $casts = []; // casts字符串
}

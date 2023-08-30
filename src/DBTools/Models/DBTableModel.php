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
    public bool $hasSoftDeletes = false;

    public array $keys = []; // 所有的字段名
//    public array $fillable = []; // 对应model文件中的fillable
    public array $foreignKeys = []; // 外键
    public array $belongsTo = []; // 关系
    public array $hasMany = []; // 关系
    public string $relationsString = ''; // 关系
    public bool $hasRelations = false;

    public string $modelPropertiesString = ''; // BaseModel文件中的property注释

    public string $fillableString;
    public string $validateString = ''; // 验证字符串
    public string $insertString = ''; // 插入符串
    public ?string $castsString = null; // casts字符串
}

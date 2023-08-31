<?php

namespace LaravelDev\Models\DBModel;

class DBTableColumnModel
{
    public string $name;
    public string $dbType;
    public string $phpType;
    public ?int $length;
    public int $precision;
    public ?int $scale;
    public bool $notNull;
    public ?string $comment;
    public ?string $default;
    public bool $isPrimaryKey = false;
    public bool $isForeignKey = false;
    public ?string $foreignTable;
    public string $nullableString;
}

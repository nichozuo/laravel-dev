<?php

namespace LaravelDev\DBTools\Models;

class DBTableRelationModel
{
    public string $name;
    public string $type;
    public string $modelName;
    public string $foreignKey;
    public string $ownerKey;
}

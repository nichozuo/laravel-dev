<?php

namespace LaravelDev\DBTools;

class Constants
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
//        'float' => 'float',
//        'double' => 'float',
//        'decimal' => 'float',
//        'date' => 'date',
        'datetime' => 'date',
//        'timestamp' => 'timestamp',
//        'time' => 'time',
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
}

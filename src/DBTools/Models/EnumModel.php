<?php

namespace LaravelDev\DBTools\Models;

class EnumModel
{
    public string $name;
    public string $intro;
    /**
     * @var EnumConstModel[]
     */
    public array $consts;
}
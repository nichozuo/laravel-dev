<?php

namespace LaravelDev\Models\EnumModel;

class EnumModel
{
    public string $name;
    public string $intro;
    public string $field;
    /**
     * @var EnumConstModel[]
     */
    public array $consts;
}
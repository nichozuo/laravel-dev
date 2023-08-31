<?php

namespace LaravelDev\Models\EnumModel;

class EnumModel
{
    public string $name;
    public string $intro;
    /**
     * @var EnumConstModel[]
     */
    public array $consts;
}
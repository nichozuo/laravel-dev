<?php

namespace LaravelDev\App\Traits;


use LaravelDev\App\Exceptions\Err;
use LaravelDev\Services\EnumModelServices;
use ReflectionClass;

/**
 * @method static cases()
 */
trait EnumTrait
{
    /**
     * @return array
     */
    public static function Values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * @param string $name
     * @return string
     */
    public static function Comment(string $name): string
    {
        return $name . ':' . str_replace("App\\Enums\\", "", self::class);  //. implode(',', self::columns());
    }

    /**
     * @return int
     */
    public static function GetMaxLength(): int
    {
        $lens = array_map(function ($item) {
            return strlen($item->value);
        }, self::cases());
        return max($lens);
    }

    /**
     * @param mixed|null $label
     * @param bool $throw
     * @return null
     * @throws Err
     */
    public static function GetValueByLabel(mixed $label = null, bool $throw = true)
    {
        if (!$label && $throw)
            ee("枚举值不能为空");

        $enumRef = new ReflectionClass(self::class);
        $consts = EnumModelServices::GetConsts($enumRef);
        foreach ($consts as $item) {
            if ($item->label == $label) {
                return $item->value;
            }
        }

        if ($throw)
            ee("枚举值不存在");
        return null;
    }

    /**
     * @param mixed|null $value
     * @param bool $throw
     * @return string|null
     * @throws Err
     */
    public static function GetLabelByValue(mixed $value = null, bool $throw = true): ?string
    {
        if (!$value && $throw)
            ee("枚举值不能为空");

        $enumRef = new ReflectionClass(self::class);
        $consts = EnumModelServices::GetConsts($enumRef);

        foreach ($consts as $item) {
            if ($item->value == $value) {
                return $item->label;
            }
        }

        if ($throw)
            ee("枚举值不存在");
        return null;
    }

    /**
     * @param mixed|null $value
     * @param bool $throw
     * @return bool
     * @throws Err
     */
    public static function IsValueInEnum(mixed $value = null, bool $throw = true): bool
    {
        if (!in_array($value, self::Values())) {
            if ($throw)
                ee("枚举值不存在");
            return false;
        }
        return true;
    }

    /**
     * @return array|string[]
     */
    public static function GetLabels(): array
    {
        $enumRef = new ReflectionClass(self::class);
        $consts = EnumModelServices::GetConsts($enumRef);

        return array_map(function ($item) {
            return $item->label;
        }, $consts);
    }
}

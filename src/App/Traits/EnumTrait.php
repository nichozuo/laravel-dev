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
    public static function columns(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * @param string $name
     * @return string
     */
    public static function comment(string $name): string
    {
        return $name . ':' . str_replace("App\\Enums\\", "", self::class);  //. implode(',', self::columns());
    }

    /**
     * @return array
     */
    public static function nameAndValue(): array
    {
        $data = [];
        foreach (self::cases() as $item) {
            $data[] = [
                'name' => $item->name,
                'value' => $item->value,
            ];
        }
        return $data;
    }

    /**
     * @return int
     */
    public static function GetMaxLength(): int
    {
        $max = 0;
        foreach (self::cases() as $item) {
            if ($max < strlen($item->value)) {
                $max = strlen($item->value);
            }
        }
        return $max;
    }

    /**
     * @param mixed|null $label
     * @param bool $throw
     * @return null
     * @throws Err
     */
    public static function GetValueByLabel(mixed $label = null, bool $throw = false)
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
    public static function GetLabelByValue(mixed $value = null, bool $throw = false): ?string
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
}

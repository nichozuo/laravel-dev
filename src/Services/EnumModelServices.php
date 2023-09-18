<?php

namespace LaravelDev\Services;

use Illuminate\Support\Facades\File;
use LaravelDev\Models\EnumModel\EnumConstModel;
use LaravelDev\Models\EnumModel\EnumModel;
use LaravelDev\Utils\DocBlockReader;
use ReflectionClass;
use ReflectionException;

class EnumModelServices
{
    /**
     * @return EnumModel[]
     * @throws ReflectionException
     */
    public static function GetEnums(): array
    {
        $enums = [];
        foreach (File::files(app_path("Enums")) as $item) {
            $enumName = str_replace('.php', '', $item->getFilename());
            $enumRef = new ReflectionClass('\\App\\Enums\\' . $enumName);
            $enumDoc = DocBlockReader::parse($enumRef->getDocComment());

            // 获取常量
            $consts = [];
            foreach ($enumRef->getConstants() as $constRef) {
                $constDoc = DocBlockReader::parse($enumRef->getReflectionConstant($constRef->name)->getDocComment());
                $const = new EnumConstModel();
                $const->label = $constDoc['value'] ?? $constRef->name;
                $const->value = $constDoc['value'] ?? $constRef->value;
                $const->color = $constDoc['color'] ?? self::getRandomColor();
                $consts[] = $const;
            }

            $enum = new EnumModel();
            $enum->name = $enumName;
            $enum->intro = $enumDoc['intro'] ?? '';
            $enum->consts = $consts;

            $enums[] = $enum;
        }
        return $enums;
    }

    /**
     * @return string
     */
    private static function getRandomColor(): string
    {
        $str = '#';
        for ($i = 0; $i < 6; $i++) {
            $str .= dechex(rand(0, 15));
        }
        return $str;
    }

    /**
     * @return string
     * @throws ReflectionException
     */
    public static function ToString(): string
    {
        $enums = self::GetEnums();
        $str = '';
        foreach ($enums as $enum) {
            $consts = [];
            foreach ($enum->consts as $const) {
                $consts[] = [
                    'label' => $const->label,
                    'value' => $const->value,
                    'color' => $const->color,
                    'textColor' => self::getTextColor($const->color),
                ];
            }
            $str .= '// ' . $enum->intro . PHP_EOL;
            $str .= "export const $enum->name : MyEnumItemProps[] =" . json_encode($consts, JSON_UNESCAPED_UNICODE) . PHP_EOL;
        }
        return $str;
    }

    /**
     * @param $colorInput
     * @return string
     */
    private static function getTextColor($colorInput): string
    {
        $colorNamesToHex = [
            "red" => "FF0000",
            'green' => '00FF00',
            'blue' => '0000FF',
            'yellow' => 'FFFF00'
        ];

        // 如果输入的是颜色名称，将其转换为十六进制颜色代码
        if (array_key_exists($colorInput, $colorNamesToHex)) {
            $colorInput = $colorNamesToHex[$colorInput];
        }

        // 如果输入的颜色代码带有#，去掉#
        $hexColor = str_replace("#", "", $colorInput);

        $r = hexdec(substr($hexColor, 0, 2));
        $g = hexdec(substr($hexColor, 2, 2));
        $b = hexdec(substr($hexColor, 4, 2));

        $luminance = ($r * 0.299 + $g * 0.587 + $b * 0.114) / 255;

        if ($luminance > 0.5) {
            // 浅色背景，使用深色文字
            return '000000'; // 黑色
        } else {
            // 深色背景，使用浅色文字
            return 'FFFFFF'; // 白色
        }
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    public static function GenDocTree(): array
    {
        $enums = self::GetEnums();
        $tree = [];
        foreach ($enums as $enum) {
            $tree[] = [
                'key' => $enum->name,
                'title' => $enum->name,
                'description' => $enum->intro,
                'isLeaf' => true,
            ];
        }
        return $tree;
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    public static function GenDocList(): array
    {
        $enums = self::GetEnums();
        $list = [];
        foreach ($enums as $enum) {
            $list[$enum->name] = [
                'key' => $enum->name,
                'title' => $enum->name,
                'description' => $enum->intro,
                'consts' => $enum->consts,
            ];
        }
        return $list;
    }
}
<?php

namespace LaravelDev\Services;

use LaravelDev\Models\DBModel\DBModel;
use LaravelDev\Models\DBModel\DBTableModel;
use LaravelDev\Models\EnumModel\EnumModel;
use LaravelDev\Models\RouteModel\ControllerModel;
use ReflectionException;

class DocToolsServices
{
    /**
     * @return array
     * @throws ReflectionException
     */
    public static function GenOpenApiV3Doc(): array
    {
        $controllers = RouterModelServices::GenRoutersModels();
        $db = DBModelServices::GetDBModel();
        $enums = EnumModelServices::GetEnums();

        return [
            'openapi' => '3.0.1',
            'info' => [
                'title' => config('app.name'),
                'version' => '0.0.x',
            ],
            'servers' => [
                [
                    "description" => "Server Address",
                    "url" => config('app.url') . "/api/"
                ]
            ],
            'tags' => self::getTags($controllers),
            'paths' => self::getPaths($controllers),
            'components' => self::getComponents($db, $enums),
        ];
    }

    /**
     * @param DBModel $db
     * @param EnumModel[] $enums
     * @return array[]
     */
    private static function getComponents(DBModel $db, array $enums): array
    {
        $schemas = [];
        foreach ($db->tables as $table) {
            $schemas[$table->name] = [
                "type" => "object",
                "title" => $table->comment,
                "x-type" => "database",
                "properties" => self::getTableProperties($table)
            ];
        }
        foreach ($enums as $enum) {
            $schemas[$enum->name] = [
                "type" => "object",
                "title" => $enum->intro,
                "x-type" => "enum",
                "properties" => self::getEnumProperties($enum)
            ];
        }
        return $schemas;
    }

    /**
     * @param ControllerModel[] $controllers
     * @return array
     */
    private static function getTags(array $controllers): array
    {
        $tags = [];
        foreach ($controllers as $key => $value) {
            $tags[] = [
                "name" => implode('/', $value->modules),
                "description" => $value->intro
            ];
        }
        return $tags;
    }

    /**
     * @param ControllerModel[] $controllers
     * @return array
     */
    private static function getPaths(array $controllers): array
    {
        $paths = [];
        foreach ($controllers as $key => $controller) {
            $folder = implode("/", $controller->modules);
            foreach ($controller->actions as $action) {
                list($properties, $required) = self::getDBPropertiesAndRequired($action->params);
                $paths["/$controller->routerPrefix/$action->uri"] = [
                    strtolower($action->method[0]) => [
                        "tags" => [$folder],
//                        "x-apifox-folder" => $folder,
//                        "x-module-name" => str_replace("Controller", "", $controller->controllerName),
//                        "x-action-name" => $action->methodName,
                        "summary" => $action->uri,
                        "description" => $action->intro,
                        "requestBody" => count($action->params) == 0 ? null : [
                            "content" => [
                                'application/x-www-form-urlencoded' => [
                                    "schema" => [
                                        "type" => "object",
                                        "properties" => $properties,
                                        "required" => $required,
                                    ]
                                ]
                            ]
                        ]
                    ]
                ];
            }

        }
        return $paths;
    }

    /**
     * @param array $params
     * @return array
     */
    private static function getDBPropertiesAndRequired(array $params): array
    {
        $properties = [];
        $required = [];
        foreach ($params as $param) {
            $properties[$param->key] = [
                'type' => $param->type,
                'description' => $param->description,
                "required" => $param->required,
            ];
            if ($param->required) {
                $required[] = $param->key;
            }
        }
        return [$properties, $required];
    }

    /**
     * @param DBTableModel $table
     * @return array
     */
    private static function getTableProperties(DBTableModel $table): array
    {
        $properties = [];
        foreach ($table->columns as $name => $column) {
            $properties[$name] = [
                'type' => $column->dbType,
                'description' => $column->comment,
                "required" => $column->nullableString,
            ];
        }
        return $properties;
    }

    /**
     * @param EnumModel $enum
     * @return array
     */
    private static function getEnumProperties(EnumModel $enum): array
    {
        $properties = [];
        foreach ($enum->consts as $key => $value) {
            $properties[$key] = [
                'label' => $value->label,
                'value' => $value->value,
                "color" => $value->color,
            ];
        }
        return $properties;
    }
}
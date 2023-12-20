<?php

namespace LaravelDev\Services;

use LaravelDev\App\Exceptions\Err;
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
     * @throws Err
     */
    public static function GenOpenApiV3Doc(): array
    {
        $controllers = RouterModelServices::GenRoutersModels();
        $db = DBModelServices::GetDBModel();
        $enums = EnumModelServices::GetEnums();

        return [
            'openapi' => '3.1.0',
            'info' => [
                'title' => config('app.name'),
                'version' => '0.0.x',
            ],
            'servers' => [
                [
                    "url" => config('app.url') . "/api/",
                    "description" => "Server Address"
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
        $responses = [
            "DefaultResponse" => [
                "description" => "默认响应",
                "content" => [
                    "application/json" => [
                        "schema" => [
                            "type" => "object",
                            "properties" => [
                                "success" => [
                                    "type" => "boolean"
                                ],
                                "data" => [
                                    "type" => "object"
                                ]
                            ],
                            "required" => ['success', 'data']
                        ]
                    ]
                ]
            ]
        ];

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
                "x-field" => $enum->field,
                "properties" => self::getEnumProperties($enum)
            ];
        }

        return [
            'responses' => $responses,
            'schemas' => $schemas
        ];
    }

    /**
     * @param ControllerModel[] $controllers
     * @return array
     */
    private static function getTags(array $controllers): array
    {
        $tags = [];
        foreach ($controllers as $value) {
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
        foreach ($controllers as $controller) {
            $folder = implode("/", $controller->modules);
            foreach ($controller->actions as $action) {
                list($properties, $required) = self::getDBPropertiesAndRequired($action->params);
                $paths["/$controller->routerPrefix/$action->uri"] = [
                    strtolower($action->method[0]) => [
                        "tags" => [$folder],
                        "summary" => $action->uri,
                        "description" => $action->intro,
                        "x-is-download" => $action->isDownload,
                        "x-response-json" => $action->resp,
                        "requestBody" => count($action->params) == 0 ? null : [
                            "content" => [
                                'application/x-www-form-urlencoded' => [
                                    "schema" => [
                                        "type" => "object",
                                        "properties" => $properties,
                                        "required" => $required,
                                    ]
                                ]
                            ],
                        ],
                        "responses" => [
                            "default" => [
                                '$ref' => '#/components/responses/DefaultResponse'
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
                "textColor" => $value->textColor,
            ];
        }
        return $properties;
    }
}
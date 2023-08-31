<?php

namespace LaravelDev\Services;

use cebe\openapi\exceptions\TypeErrorException;
use cebe\openapi\spec\MediaType;
use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\PathItem;
use cebe\openapi\spec\Paths;
use cebe\openapi\spec\RequestBody;
use cebe\openapi\spec\Response;
use cebe\openapi\spec\Responses;
use cebe\openapi\spec\Schema;
use cebe\openapi\Writer;
use Doctrine\DBAL\Exception;
use JetBrains\PhpStorm\ArrayShape;
use ReflectionException;

class DocToolsServices
{
    /**
     * @return array
     * @throws TypeErrorException
     */
    public static function GenOpenApiV3Doc(): array
    {
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
            'tags' => self::getTags(),
            'paths' => self::getPaths(),
            'components' => self::getComponents(),
//            'extends' => [
//                'tree' => [
//                    'api' => RouterToolsServices::GenDocTree(),
//                    'db' => DBToolsServices::GenDocTree(),
//                    'enum' => EnumToolsServices::GenDocTree(),
//                ],
//                'data' => [
//                    'api' => RouterToolsServices::GenDocList(),
//                    'db' => DBToolsServices::GenDocList(),
//                    'enum' => EnumToolsServices::GenDocList(),
//                ],
//            ]
        ];
    }

    /**
     * @return array
     * @throws ReflectionException
     * @throws TypeErrorException
     */
    private static function getPathsAndTags(): array
    {
        $controllers = RouterModelServices::GenRoutersModels();
        $pathItems = [];
        $tags = [];
        $tagNames = [];
        foreach ($controllers as $controller) {
            foreach ($controller->actions as $action) {
                $name = implode('/', $controller->modules);

                // 处理tag
                if (!in_array($name, $tagNames)) {
                    $tags[] = [
                        "name" => $name,
                        "description" => $controller->intro
                    ];
                    $tagNames[] = $name;
                }

                // 处理properties 和 required
                $properties = [];
                $required = [];

                foreach ($action->params as $param) {
                    $properties[$param->key] = new Schema([
                        'type' => $param->type,
                        'description' => $param->description,
                        "required" => $param->required,
                    ]);
                    if ($param->required) {
                        $required[] = $param->key;
                    }
                }
                // 处理pathItem
                $pathItems["/$controller->routerPrefix/$action->uri"] = new PathItem([
                    strtolower($action->method[0]) => new Operation([
                        "tags" => [$name],
                        "x-apifox-folder" => $name,
                        "x-module-name" => str_replace("Controller", "", $controller->controllerName),
                        "x-action-name" => $action->methodName,
                        "summary" => $action->intro,
                        "description" => '',
                        "requestBody" => count($action->params) == 0 ? null : new RequestBody([
                            "content" => [
                                'application/x-www-form-urlencoded' => new MediaType([
                                    "schema" => new Schema([
                                        "type" => "object",
                                        "properties" => $properties,
                                        "required" => $required,
                                    ])
                                ])
                            ]
                        ])
                    ])
                ]);
            }
        }

        $paths = new Paths($pathItems);
        return [$tags, $paths];
    }

    /**
     * @return array
     * @throws Exception
     * @throws TypeErrorException
     */
    private static function getSchemas(): array
    {
        $schemas = [];

        // database
        $db = DBModelServices::GenDBModel();
        foreach ($db->tables as $table) {
            $properties = [];
            $required = [];
            foreach ($table->columns as $column) {
                $properties[$column->name] = new Schema([
                    "type" => $column->dbType,
                    "description" => $column->comment,
                    "required" => $column->notNull,
                ]);
                if ($column->notNull) {
                    $required[] = $column->name;
                }
            }
            $schemas[$table->name] = new Schema([
                "type" => "object",
                "description" => $table->comment,
                "properties" => $properties,
                "required" => $required
            ]);
        }

        // enums

        return $schemas;
    }

    /**
     * @return array
     * @throws TypeErrorException
     */
    #[ArrayShape(["responses" => "\cebe\openapi\spec\Responses", "schemas" => ""])]
    private static function getComponents(): array
    {
        return [
            "responses" => new Responses([
                'default' => new Response([
                    "description" => "default response",
                    "content" => [
                        "application/json" => new MediaType([
                            "schema" => new Schema([
                                "type" => "object",
                                "properties" => [
                                    "code" => new Schema([
                                        "type" => "integer",
                                        "description" => "code",
                                        "example" => 0,
                                    ]),
                                    "message" => new Schema([
                                        "type" => "string",
                                        "description" => "message",
                                        "example" => "ok",
                                    ]),
                                    "data" => new Schema([
                                        "type" => "object",
                                        "description" => "data"
                                    ])
                                ]
                            ])
                        ])
                    ]
                ])
            ]),
        ];
    }

    /**
     * @return array
     */
    private static function getTags(): array
    {
        return [];
    }

    /**
     * @return array
     */
    private static function getPaths(): array
    {
        return [];
    }
}
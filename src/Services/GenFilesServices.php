<?php

namespace LaravelDev\Services;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Stringable;
use LaravelDev\App\Exceptions\Err;
use ReflectionException;

class GenFilesServices
{

    /**
     * @param string $tableName
     * @param bool $force
     * @return void
     * @throws Exception
     */
    public static function GenModels(string $tableName, bool $force): void
    {
        $table = DBModelServices::GetTable($tableName);

        // 生成BaseModel
        $useClasses = [];
        $useTraits = [];
        $hidden = '';
        $guardName = '';

        if ($table->hasNodeTrait) {
            $useClasses[] = 'use Kalnoy\Nestedset\NodeTrait;';
            $useTraits[] = 'use NodeTrait;';
        }
        if ($table->hasApiTokens) {
            $useClasses[] = 'use Laravel\Sanctum\HasApiTokens;';
            $useTraits[] = 'use HasApiTokens;';
            $guardName = "protected string \$guard_name = 'sanctum';";
        }
        if ($table->hasRoles) {
            $useClasses[] = 'use Spatie\Permission\Traits\HasRoles;';
            $useTraits[] = 'use HasRoles;';
        }
        if ($table->hasRelation) {
            $useClasses[] = 'use Illuminate\Database\Eloquent\Relations;';
            $useClasses[] = 'use App\Models;';
        }
        if (count($table->hidden)) {
            $hidden = "protected \$hidden = ['" . implode("', '", $table->hidden) . "'];";
        }
        if ($table->hasSoftDelete) {
            $useClasses[] = 'use Illuminate\Database\Eloquent\SoftDeletes;';
            $useTraits[] = 'use SoftDeletes;';
        }

        $content = self::loadStub("BaseModel");
        $content = self::replaceAll([
            'useClasses' => implode("\n", $useClasses),
            'properties' => implode("\n ", $table->modelProperties),
            'modelName' => $table->modelName,
            'useTraits' => implode("\n\t", $useTraits),
            'name' => config('project.tablePrefix') . $table->name,
            'comment' => $table->comment,
            'fillable' => "'" . implode("', '", $table->fillable) . "'",
            'hidden' => $hidden,
            'guard_name' => $guardName,
            'relations' => $table->relationsString,
            'casts' => DBModelServices::ParseCasts($table)
        ], $content);

        self::saveFile(["Models", "Base", "Base$table->modelName.php"], $content, $force);

        // 生成Model
        $content = self::loadStub("Model");
        $content = self::replaceAll([
            'modelName' => $table->modelName,
        ], $content);

        self::saveFile(["Models", "$table->modelName.php"], $content, false);
    }

    /**
     * @param Collection $modulesName
     * @param Stringable $tableName
     * @param mixed $force
     * @return void
     * @throws Err
     */
    public static function GenController(Collection $modulesName, Stringable $tableName, mixed $force): void
    {
        $table = DBModelServices::GetTable($tableName, false);
        $modelName = $tableName->studly();

        if ($table) {
            $content = self::loadStub($table->hasSoftDelete ? "ControllerSoftDelete" : "Controller");
            $content = self::replaceAll([
                'moduleName' => $modulesName->implode('\\'),
                'modelName' => $modelName,
                'comment' => $table->comment,
                'validateString' => implode("\n\t\t\t", $table->validate),
            ], $content);
        } else {
            $content = self::loadStub("EmptyController");
            $content = self::replaceAll([
                'moduleName' => $modulesName->implode('\\'),
                'modelName' => $modelName,
            ], $content);
        }
        self::saveFile(["Modules", ...$modulesName, "{$modelName}Controller.php"], $content, $force);
    }

    /**
     * @param array $modulesName
     * @param string $tableName
     * @param mixed $force
     * @return void
     * @throws ReflectionException
     */
    public static function GenTest(array $modulesName, string $tableName, mixed $force): void
    {
        $className = implode("\\", ["App", "Modules", ...$modulesName, "{$tableName}Controller"]);
//        $modelName = array_pop($arr);
        $moduleName = implode("\\", $modulesName);

        $r = RouterModelServices::GenRoutersModels()[$className] ?? null;
        if (!$r)
            return;

        $stub = self::loadStub("Test");

        $content = [];
        foreach ($r->actions as $action) {
            $name = str()->of($action->methodName)->camel()->ucfirst();
            if (!count($action->params)) {
                $content[] = "public function test$name()
    {
        \$this->go(__METHOD__);
    }";
            } else {
                $fields = [];
                foreach ($action->params as $param) {
                    $fields[] = "'$param->key' => '', # $param->description";
                }
                $fieldsStr = implode(",\n\t\t\t", $fields);
                $content[] = "public function test$name()
    {
        \$this->go(__METHOD__, [
            $fieldsStr
        ]);
    }";
            }
        }
        $contentStr = implode("\n\n\t", $content);

        $content = self::replaceAll([
            'moduleName' => $moduleName,
            'modelName' => $tableName,
            'content' => $contentStr,
        ], $stub);

        self::saveFile(["..", "tests", "Modules", ...$modulesName, "{$tableName}ControllerTest.php"], $content, $force);
    }

    /**
     * @param string $key
     * @param string|null $field
     * @param bool $force
     * @return void
     */
    public static function GenEnum(string $key, ?string $field = '', ?bool $force = false): void
    {
        $content = self::loadStub("Enum");
        $content = self::replaceAll([
            'EnumName' => $key,
            'field' => $field,
        ], $content);
        self::saveFile(["Enums", "$key.php"], $content, $force);
    }

    /**
     * @param string $stubName
     * @return string
     */
    private static function loadStub(string $stubName): string
    {
        $path = resource_path("stubs/$stubName.stub");
        if (!File::exists($path))
            $path = __DIR__ . "/stubs/$stubName.stub";
        return File::get($path);
    }

    /**
     * @param array $array
     * @param $content
     * @return string
     */
    private static function replaceAll(array $array, $content): string
    {
        foreach (array_keys($array) as $key) {
            if (isset($array[$key])) {
                $content = str_replace("{\$$key}", $array[$key], $content);
            }
        }
        return $content;
    }

    /**
     * @param array $path
     * @param string $content
     * @param bool $force
     * @return void
     */
    private static function saveFile(array $path, string $content, bool $force): void
    {
        $filePath = app_path(implode(DIRECTORY_SEPARATOR, $path));
        $exists = File::exists($filePath);
        if (!$exists || $force) {
            File::makeDirectory(File::dirname($filePath), 0755, true, true);
            File::put($filePath, $content);
            dump($filePath);
        } else {
            dump("File exist");
        }
    }
}
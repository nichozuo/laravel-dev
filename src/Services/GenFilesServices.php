<?php

namespace LaravelDev\Services;

use Exception;
use Illuminate\Support\Facades\File;

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
//            'methods' => '',
            'modelName' => $table->modelName,
            'useTraits' => implode("\n\t", $useTraits),
            'name' => $table->name,
            'comment' => $table->comment,
            'fillable' => "'" . implode("', '", $table->fillable) . "'",
            'hidden' => $hidden,
            'guard_name' => $guardName,
            'relations' => $table->relationsString,
            'casts' => DBModelServices::ParseCasts($table)
        ], $content);
        self::saveFile(app_path("Models/Base/Base$table->modelName.php"), $content, $force);

        // 生成Model
        $content = self::loadStub("Model");
        $content = self::replaceAll([
            'modelName' => $table->modelName,
        ], $content);
        self::saveFile(app_path("Models/$table->modelName.php"), $content, false);
    }

    /**
     * @param array $moduleName
     * @param string $tableName
     * @param mixed $force
     * @return void
     * @throws Exception
     */
    public static function GenController(array $moduleName, string $tableName, mixed $force): void
    {
        $table = DBModelServices::GetTable($tableName);

        $content = self::loadStub($table->hasSoftDelete ? "ControllerSoftDelete" : "Controller");
        $content = self::replaceAll([
            'moduleName' => implode('\\', $moduleName),
            'modelName' => $table->modelName,
            'comment' => $table->comment,
            'validateString' => implode("\n\t\t\t", $table->validate),
        ], $content);
        $moduleName = implode('/', $moduleName);
        self::saveFile(app_path("Modules/$moduleName/{$table->modelName}Controller.php"), $content, $force);
    }

    /**
     * @param string $key
     * @param mixed $force
     * @return void
     */
    public static function GenEnum(string $key, mixed $force): void
    {
        $content = self::loadStub("Enum");
        $content = self::replaceAll([
            'EnumName' => $key,
        ], $content);
        self::saveFile(app_path("Enums/$key.php"), $content, $force);
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
     * @param string $filePath
     * @param string $content
     * @param bool $force
     * @return void
     */
    private static function saveFile(string $filePath, string $content, bool $force): void
    {
        $exists = File::exists($filePath);
        if (!$exists || $force) {
            File::makeDirectory(File::dirname($filePath), 0755, true, true);
            File::put($filePath, $content);
            dump("Make file...$filePath");
        } else {
            dump("File exist");
        }
    }
}
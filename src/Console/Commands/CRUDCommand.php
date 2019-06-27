<?php

namespace Imtigger\LaravelCRUD\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class CRUDCommand extends Command
{
    protected $indentation = '    ';

    public function init()
    {
        $this->fs = new Filesystem();

        $this->name = $this->argument('name');
        $this->nameNormalized = str_singular($this->name);
        $this->nameSingular = str_singular($this->name);
        $this->namePlural = str_plural($this->name);

        $this->modelNamespace = 'App\\Models';
        $this->formNamespace = 'App\\Forms';
        $this->controllerNamespace = 'App\\Http\\Controllers\\Admin';

        $this->urlName = snake_case($this->nameNormalized);
        $this->viewPrefix = 'admin.' . snake_case($this->nameNormalized);
        $this->routePrefix = 'admin.' . snake_case($this->nameNormalized);
        $this->permissionPrefix = snake_case($this->nameNormalized);
        $this->translationPrefix = 'backend.' . snake_case($this->nameNormalized) . '.label';

        $this->controllerName = studly_case($this->nameNormalized) . 'Controller';
        $this->modelName = studly_case($this->nameNormalized);
        $this->formName = studly_case($this->nameNormalized) . 'Form';
        $this->migrationName = 'Create' . studly_case($this->namePlural) . 'Table';
        $this->tableName = snake_case($this->namePlural);
        $this->entityName = title_case(str_replace('_', ' ', snake_case($this->nameNormalized)));
        $this->internalName = snake_case($this->nameNormalized);
    }

    protected function getControllerPath($name)
    {
        $name = str_replace_first($this->laravel->getNamespace(), '', $name);
        return $this->laravel['path'].'/'.str_replace('\\', '/', $name).'.php';
    }

    protected function getModelPath($name)
    {
        $name = str_replace_first($this->laravel->getNamespace(), '', $name);
        return $this->laravel['path'].'/'.str_replace('\\', '/', $name).'.php';
    }

    protected function getFormPath($name)
    {
        $name = str_replace_first($this->laravel->getNamespace(), '', $name);
        return $this->laravel['path'].'/'.str_replace('\\', '/', $name).'.php';
    }

    protected function getViewPath($name)
    {
        return base_path() . '/resources/views/admin/' . snake_case($name);
    }

    protected function getMigrationPath($name)
    {
        $name = snake_case(str_plural($name));
        return base_path() . '/database/migrations/' . date('Y_m_d_His') . '_create_' . $name . '_table.php';
    }
	
    protected function getStubContent($path)
    {
        if ($this->fs->exists(resource_path('crud-stubs/' .  $path . '.stub'))) {
            return $this->fs->get(resource_path('crud-stubs/' .  $path . '.stub'));
        } else {
            return $this->fs->get(__DIR__ . '/../../stubs/' . $path . '.stub');
        }
    }

    protected function replaceTokens($content)
    {
        $map = [
            '$CONTROLLER_NAME$' => $this->controllerName,
            '$MODEL_NAME$' => $this->modelName,
            '$FORM_NAME$' => $this->formName,
            '$MIGRATION_NAME$' => $this->migrationName,
            '$TABLE_NAME$' => $this->tableName,
            '$VIEW_PREFIX$' => $this->viewPrefix,
            '$ROUTE_PREFIX$' => $this->routePrefix,
            '$PERMISSION_PREFIX$' => $this->permissionPrefix,
            '$TRANSLATION_PREFIX$' => $this->translationPrefix,
            '$INTERNAL_NAME$' => $this->internalName,
            '$ENTITY_NAME$' => $this->entityName,
        ];

        return strtr($content, $map);
    }
}

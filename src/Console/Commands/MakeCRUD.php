<?php

namespace Imtigger\LaravelCRUD\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;

class MakeCRUD extends CRUDCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '
    make:crud {name} 
    {--form : (Re)generate only form}
    {--view : (Re)generate only view}
    {--no-model : Generates no model} 
    {--no-view : Generates no view} 
    {--no-controller : Generates no controller}
    {--no-form : Generates no form} 
    {--no-migration : Generates no migration}
    {--no-soft-delete : No soft delete}
    {--no-ui : Shortcut for --no-view, --no-controller and --no-form}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate CRUD Controller/Model/Migration/View/Form';
    protected $softDelete = true;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
		$this->init();
		
        if ($this->option('no-soft-delete')) {
            $this->softDelete = false;
        }

        if ($this->option('form')) {
            $this->compileForm($this->nameNormalized);
            return;
        }

        if ($this->option('view')) {
            $this->compileView($this->nameNormalized);
            return;
        }

        if (!$this->option('no-view') && !$this->option('no-ui')) $this->compileView($this->nameNormalized);
        if (!$this->option('no-controller') && !$this->option('no-ui')) $this->compileController($this->nameNormalized);
        if (!$this->option('no-model')) $this->compileModel($this->nameNormalized);
        if (!$this->option('no-form') && !$this->option('no-ui')) $this->compileForm($this->nameNormalized);
        if (!$this->option('no-migration')) $this->compileMigration($this->nameNormalized);

        $this->line("");
        $this->info("CRUD Generated successfully.");

        if (!$this->option('no-controller') && !$this->option('no-ui')) {
            $this->info("Now please add route to config/web.php");
            $this->warn("\\{$this->controllerNamespace}\\{$this->controllerName}::routes('/{$this->urlName}', '\\{$this->controllerNamespace}\\{$this->controllerName}', '{$this->viewPrefix}');");
        }
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

    protected function compileView($name)
    {
        $viewDirectoryPath = $this->getViewPath($name);
        \File::makeDirectory($viewDirectoryPath, 0755, true, true);

        $columns = DB::getDoctrineSchemaManager()->listTableColumns($this->tableName);
        $tableExists = sizeof($columns) != 0;

        foreach(['layout.blade.php', 'form.blade.php', 'index.blade.php', 'create.blade.php', 'edit.blade.php', 'show.blade.php', 'delete.blade.php'] As $filename) {
            $content = $this->getStubContent("views/{$filename}");
            $content = $this->replaceTokens($content);

            if ($tableExists) {
                $viewHeaders  = '';

                foreach ($columns as $name => $column) {
                    $viewHeaders .= str_repeat($this->indentation, 3) . "<th data-data=\"{$name}\">{{ trans('\$TRANSLATION_PREFIX$.{$name}') }}</th>" . PHP_EOL;
                }
            } else {
                $viewHeaders = '';
                $viewHeaders .= str_repeat($this->indentation, 3) . '<th data-data="id" width="50">{{ trans(\'$TRANSLATION_PREFIX$.id\') }}</th>' . PHP_EOL;
                $viewHeaders .= str_repeat($this->indentation, 3) . '<th data-data="name">{{ trans(\'$TRANSLATION_PREFIX$.name\') }}</th>' . PHP_EOL;
                $viewHeaders .= str_repeat($this->indentation, 3) . '<th data-data="actions" data-searchable="false" data-sortable="false" data-class-name="text-center" width="180">{{ trans(\'$TRANSLATION_PREFIX$.actions\') }}</th>' . PHP_EOL;
            }

            $content = strtr($content, [
                '$VIEW_HEADERS$' => $viewHeaders
            ]);

            $content = $this->replaceTokens($content);

            file_put_contents("{$viewDirectoryPath}/{$filename}", $content);
            $this->line("Created View: {$viewDirectoryPath}/{$filename}");
        }
    }

    protected function compileController($name)
    {
        $this->controllerPath = $this->getControllerPath($this->controllerNamespace . '/' . $this->controllerName);

        $content = $this->getStubContent("Controller.php");
        $content = $this->replaceTokens($content);
        file_put_contents("{$this->controllerPath}", $content);

        $this->line("Created Controller: {$this->controllerPath}");
    }

    protected function compileModel($name)
    {
        $this->modelPath = $this->getModelPath($this->modelNamespace . '/' . $this->modelName);

        $content = $this->getStubContent("Model.php");
        $content = $this->replaceTokens($content);

        $modelContent = '';
        if ($this->softDelete) {
            $modelContent .= $this->indentation . "use SoftDeletes;" . PHP_EOL;
            $modelContent .= PHP_EOL;
        }

        $modelContent .= $this->indentation . 'protected $fillable = [\'name\'];';

        $content = strtr($content, [
            '$MODEL_CONTENT$' => $modelContent
        ]);

        file_put_contents("{$this->modelPath}", $content);

        $this->line("Created Model: {$this->modelPath}");
    }

    protected function compileForm($name)
    {
        $this->formPath = $this->getFormPath($this->formNamespace . '/' . $this->formName);

        $content = $this->getStubContent("Form.php");
        $content = $this->replaceTokens($content);

        $columns = DB::getDoctrineSchemaManager()->listTableColumns($this->tableName);
        $tableExists = sizeof($columns) != 0;

        if ($tableExists) {
            $formContent = '';
            foreach ($columns as $name => $column) {
                if (in_array($name, ['id', 'deleted_at', 'created_at', 'updated_at'])) continue;

                $rules = [];
                if ($column->getNotnull()) {
                    $rules[] = 'required';
                }
                if (get_class($column->getType()) == \Doctrine\DBAL\Types\StringType::class) {
                    $rules[] = 'max:' . $column->getLength();
                }

                array_walk($rules, function (&$value) {
                    $value = "'{$value}'";
                });

                $rulesString = implode(', ', $rules);

                $formContent .= str_repeat($this->indentation, 2) . "\$this->add('{$name}', 'text', [" . PHP_EOL;
                $formContent .= str_repeat($this->indentation, 3) . "'label' => trans('\$TRANSLATION_PREFIX$.{$name}')," . PHP_EOL;
                $formContent .= str_repeat($this->indentation, 3) . "'rules' => [{$rulesString}]" . PHP_EOL;
                $formContent .= str_repeat($this->indentation, 2) . "]);" . PHP_EOL;
                $formContent .= PHP_EOL;
            }
        } else {
            $formContent = '';
            $formContent .= str_repeat($this->indentation, 2) . "\$this->add('name', 'text', [" . PHP_EOL;
            $formContent .= str_repeat($this->indentation, 3) . "'label' => trans('\$TRANSLATION_PREFIX$.name')," . PHP_EOL;
            $formContent .= str_repeat($this->indentation, 3) . "'rules' => ['required', 'max:255']" . PHP_EOL;
            $formContent .= str_repeat($this->indentation, 2) . "]);" . PHP_EOL;
        }

		$content = strtr($content, [
            '$FORM_CONTENT$' => $formContent
        ]);
		
        $content = $this->replaceTokens($content);
		
        file_put_contents("{$this->formPath}", $content);

        $this->line("Created Form: {$this->formPath} from " . ($tableExists ? 'table' : 'placeholder'));
    }

    protected function compileMigration($name)
    {
        $this->migrationPath = $this->getMigrationPath($name);

        $content = $this->getStubContent("migration.php");
        $content = $this->replaceTokens($content);

        $migrationContent = '';
        $migrationContent .= str_repeat($this->indentation, 3) . '$table->increments(\'id\');' . PHP_EOL;
        $migrationContent .= str_repeat($this->indentation, 3) . '$table->string(\'name\');' . PHP_EOL;
        if ($this->softDelete) {
            $migrationContent .= str_repeat($this->indentation, 3) . '$table->softDeletes();' . PHP_EOL;
        }
        $migrationContent .= str_repeat($this->indentation, 3) . '$table->timestamps();' . PHP_EOL;

        $content = strtr($content, [
            '$MIGRATION_CONTENT$' => $migrationContent
        ]);

        file_put_contents("{$this->migrationPath}", $content);

        $this->line("Created Migration: {$this->migrationPath}");
    }
}

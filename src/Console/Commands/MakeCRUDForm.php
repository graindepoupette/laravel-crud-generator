<?php

namespace Imtigger\LaravelCRUD\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MakeCRUDForm extends CRUDCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:crud:form {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate CRUD Datatables Forms';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
		$this->init();

        $columns = DB::getDoctrineSchemaManager()->listTableColumns($this->tableName);

        if (sizeof($columns) == 0) {
            $this->error("Table `{$this->tableName}` not found");
        }

		$content = $this->getStubContent("Form.php");
        $content = $this->replaceTokens($content);
		
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
		
		$content = strtr($content, [
            '$FORM_CONTENT$' => $formContent
        ]);
		
		$content = $this->replaceTokens($content);
		
		$this->formPath = $this->getFormPath($this->formNamespace . '/' . $this->formName);
		file_put_contents("{$this->formPath}", $content);
		$this->line("Updated Form: {$this->formPath}");
    }
}

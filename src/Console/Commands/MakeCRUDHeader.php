<?php

namespace Imtigger\LaravelCRUD\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MakeCRUDHeader extends CRUDCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:crud:header {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate CRUD Datatables View Table Headers';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

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

        foreach ($columns as $name => $column) {			
            $this->line("<th data-data=\"{$name}\">{{ trans('backend.{$this->nameSingular}.label.{$name}') }}</th>");
        }
    }
}

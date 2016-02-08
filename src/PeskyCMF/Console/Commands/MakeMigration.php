<?php

namespace PeskyCMF\Console\Commands;

use Illuminate\Database\Console\Migrations\BaseCommand;
use Illuminate\Support\Composer;
use Illuminate\Support\Str;
use PeskyCMF\Db\MigrationByQuery;
use PeskyCMF\Db\MigrationForTableCreation;
use Swayok\Utils\File;
use Swayok\Utils\Folder;

class MakeMigration extends BaseCommand {

    use OpenFileInPhpStormTrait;

    protected $composer;
    protected $description = 'Create a new migration file';
    protected $signature = 'make:migration
            {name : The name of the migration.}
            {table : table to update/create}
            {is_create_table=0 : 1 - Migration will create table; 0 - Migration will run sql queries}
            {schema=public : 1 - Migration will update non-public scheams provided by companies table; 0 - update public schema only}';

    protected $sqlSubdir = 'sql';
    protected $sqlRollbackSubdir = 'rollback';
    protected $migrationClassView = 'cmf::console.db_migration';
    protected $fileTime = null;

    public function __construct(Composer $composer) {
        parent::__construct();

        $this->composer = $composer;
    }

    public function fire() {
        $name = $this->input->getArgument('name');
        $isCreateTable = !!$this->input->getArgument('is_create_table');

        $this->writeMigration($name, $isCreateTable);

        $this->composer->dumpAutoloads();
    }

    protected function writeMigration($name, $isCreateTable) {
        $classPath = $this->getFilePath($name);
        $dataForView = $this->getDataForClassView($name, $isCreateTable);
        File::save($classPath, view($this->migrationClassView, $dataForView)->render(), 0664);
//        $this->openFileInPhpStorm($classPath);
        $this->line("<info>Created Migration:</info> " . pathinfo($classPath, PATHINFO_FILENAME));
        $this->line("Migration File: $classPath");

        $sqlFilePath = $this->getFilePath($name, true);
        File::save($sqlFilePath, "-- sql\n", 0664);
        $this->openFileInPhpStorm($sqlFilePath);
        $this->line("Migration SQL File: $sqlFilePath");

        if (!$isCreateTable) {
            $rollbackSqlFilePath = $this->getFilePath($name, true, true);
            File::save($rollbackSqlFilePath, "-- rollback\n", 0664);
            $this->openFileInPhpStorm($rollbackSqlFilePath);
            $this->line("Migration Rollback SQL File: $rollbackSqlFilePath");
        }
        $this->openFileInPhpStorm($sqlFilePath);
    }

    protected function getDataForClassView($name, $isCreateTable) {
        $dataForView = [
            'class' => Str::studly($name),
            'tables' => preg_split('%\s*,\s*%', $this->input->getArgument('table')),
            'isCreateTable' => $isCreateTable,
            'schema' => $this->input->getArgument('schema'),
            'parentClass' => $this->getMigrationParentClass($isCreateTable),
            'title' => ucfirst(str_replace('_', ' ', $name))
        ];
        return $dataForView;
    }

    protected function getMigrationParentClass($isCreateTable) {
        return $isCreateTable ? MigrationForTableCreation::class : MigrationByQuery::class;
    }

    protected function getFilePath($name, $forSqlFile = false, $forRollbackSqlFile = false) {
        $basePath = preg_replace('%[/\\\]%', DIRECTORY_SEPARATOR, parent::getMigrationPath()) . DIRECTORY_SEPARATOR;
        if ($forSqlFile) {
            $basePath .= $this->sqlSubdir . DIRECTORY_SEPARATOR;
            if ($forRollbackSqlFile) {
                $basePath .= $this->sqlRollbackSubdir . DIRECTORY_SEPARATOR;
            }
        }
        if (!Folder::exist($basePath)) {
            Folder::add($basePath, 0775);
        }
        return $basePath . $this->getFileTime() . '_' . $name . ($forSqlFile ? '.sql' : '.php');
    }

    protected function getFileTime() {
        if (!$this->fileTime) {
            $this->fileTime = date('Y_m_d_His');
        }
        return $this->fileTime;
    }

}

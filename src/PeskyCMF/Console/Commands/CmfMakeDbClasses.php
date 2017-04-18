<?php

namespace PeskyCMF\Console\Commands;

use Illuminate\Console\Command;
use PeskyCMF\CMS\Traits\AdminIdColumn;
use PeskyCMF\Db\CmfDbRecord;
use PeskyCMF\Db\CmfDbTable;
use PeskyCMF\Db\Traits\IdColumn;
use PeskyCMF\Db\Traits\IsActiveColumn;
use PeskyCMF\Db\Traits\IsPublishedColumn;
use PeskyCMF\Db\Traits\PasswordColumn;
use PeskyCMF\Db\Traits\TimestampColumns;
use PeskyCMF\Db\Traits\UserAuthColumns;
use PeskyORM\Core\DbConnectionsManager;
use PeskyORM\ORM\ClassBuilder;
use PeskyORM\ORM\TableStructure;
use Swayok\Utils\File;
use Swayok\Utils\Folder;

class CmfMakeDbClasses extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cmf:make-db-classes {table_name} {schema?} {database_classes_app_subfolder=Db}'
                            . ' {--overwrite= : 1|0|y|n|yes|no; what to do if classes already exist}'
                            . ' {--only= : table|record|structure; create only specified class}'
                            . ' {--connection= : name of connection to use}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create classes for DB table.';

    protected function getTableParentClass() {
        $abstractTableClass = $this->buildBaseNamespace() . '\\AbstractTable';
        if (class_exists($abstractTableClass)) {
            return $abstractTableClass;
        }
        return CmfDbTable::class;
    }

    protected function getRecordParentClass() {
        $abstractRecordClass = $this->buildBaseNamespace() . '\\AbstractRecord';
        if (class_exists($abstractRecordClass)) {
            return $abstractRecordClass;
        }
        return CmfDbRecord::class;
    }

    protected function getTableStructureParentClass() {
        $abstractStructureClass = $this->buildBaseNamespace() . '\\AbstractTableStructure';
        if (class_exists($abstractStructureClass)) {
            return $abstractStructureClass;
        }
        return TableStructure::class;
    }

    protected function getClassBuilderClass() {
        $customClassBuilderClass = $this->buildBaseNamespace() . '\\ClassBuilder';
        if (class_exists($customClassBuilderClass)) {
            return $customClassBuilderClass;
        }
        return ClassBuilder::class;
    }

    /**
     * Execute the console command.
     *
     * @throws \InvalidArgumentException
     */
    public function handle() {
        $connectionName = $this->option('connection');
        if (!empty($connectionName)) {
            $connectionInfo = config('database.connections.' . $connectionName);
            if (!is_array($connectionInfo)) {
                $this->line("- There is no configuration info for connection '{$connectionName}'");
                return;
            }
            $connection = DbConnectionsManager::createConnectionFromArray($connectionName, $connectionInfo);
        } else {
            $connection = DbConnectionsManager::getConnection('default');
        }
        /** @var ClassBuilder $builder */
        $builderClass = $this->getClassBuilderClass();
        $builder = new $builderClass(
            $this->argument('table_name'),
            $connection
        );
        $builder->setDbSchemaName($this->argument('schema'));

        $only = $this->option('only');
        $overwrite = null;
        if (in_array($this->option('overwrite'), ['1', 'yes', 'y'], true)) {
            $overwrite = true;
        } else if (in_array($this->option('overwrite'), ['0', 'no', 'n'], true)) {
            $overwrite = false;
        }

        $info = $this->preapareAndGetDataForViews();

        if (!$only || $only === 'table') {
            $this->createTableClassFile($builder, $info, $overwrite);
        }
        if (!$only || $only === 'record') {
            $this->createRecordClassFile($builder, $info, $overwrite);
        }
        if (!$only || $only === 'structure') {
            $this->createTableStructureClassFile($builder, $info, $overwrite);
        }

        $this->line('Done');
    }

    protected function preapareAndGetDataForViews() {
        $tableName = $this->argument('table_name');
        $namespace = $this->buildNamespaceForClasses($tableName);
        /** @var ClassBuilder $builderClass */
        $builderClass = $this->getClassBuilderClass();
        $dataForViews = [
            'folder' => $this->getFolder($tableName, $namespace),
            'table' => $tableName,
            'namespace' => $namespace,
            'table_class_name' => $builderClass::makeTableClassName($tableName),
            'record_class_name' => $builderClass::makeRecordClassName($tableName),
            'structure_class_name' => $builderClass::makeTableStructureClassName($tableName),
        ];
        $dataForViews['table_file_path'] = $dataForViews['folder'] . $dataForViews['table_class_name'] . '.php';
        $dataForViews['record_file_path'] = $dataForViews['folder'] . $dataForViews['record_class_name'] . '.php';
        $dataForViews['structure_file_path'] = $dataForViews['folder'] . $dataForViews['structure_class_name'] . '.php';
        Folder::load($dataForViews['folder'], true, 0775);
        return $dataForViews;
    }

    protected function buildBaseNamespace() {
        $subfolder = trim(preg_replace('%[\/]+%', '\\', $this->argument('database_classes_app_subfolder')), ' /\\');
        return rtrim('App\\' . $subfolder, ' /\\');
    }

    protected function buildNamespaceForClasses($tableName) {
        /** @var ClassBuilder $builderClass */
        $builderClass = $this->getClassBuilderClass();
        return $this->buildBaseNamespace() . '\\' . $builderClass::convertTableNameToClassName($tableName);
    }

    protected function getFolder($tableName, $namespace) {
        /** @var ClassBuilder $builderClass */
        $builderClass = $this->getClassBuilderClass();
        $folder = preg_replace(
            ['%[\\/]%', '%^App%'],
            [DIRECTORY_SEPARATOR, $this->getBasePathToApp()],
            $namespace
        );
        return $folder . DIRECTORY_SEPARATOR;
    }

    protected function getBasePathToApp() {
        return app_path();
    }

    /**
     * @return array (
     *      NameOfTrait1::class,
     *      NameOfTrait2::class,
     * )
     */
    protected function getTraitsForTableConfig() {
        return [
            IdColumn::class,
            UserAuthColumns::class,
            PasswordColumn::class,
            AdminIdColumn::class,
            IsActiveColumn::class,
            IsPublishedColumn::class,
            TimestampColumns::class
        ];
    }

    protected function createTableClassFile(ClassBuilder $builder, array $info, $overwrite) {
        $filePath = $info['table_file_path'];
        if (File::exist($filePath)) {
            if ($overwrite === false) {
                $this->line('Table class creation cancelled');
                return;
            } else if ($overwrite === true) {
                File::remove();
            } else if ($this->confirm("Table file {$filePath} already exists. Overwrite?")) {
                File::remove();
            } else {
                $this->line('Table class creation cancelled');
                return;
            }
        }
        $fileContents = $builder->buildTableClass($info['namespace'], $this->getTableParentClass());
        File::save($filePath, $fileContents, 0664, 0755);
        $this->line("Table class created ({$filePath})");
    }

    protected function createRecordClassFile(ClassBuilder $builder, array $info, $overwrite) {
        $filePath = $info['record_file_path'];
        if (File::exist($filePath)) {
            if ($overwrite === false) {
                $this->line('Record class creation cancelled');
                return;
            } else if ($overwrite === true) {
                File::remove();
            } else if ($this->confirm("Record file {$filePath} already exists. Overwrite?")) {
                File::remove();
            } else {
                $this->line('Record class creation cancelled');
                return;
            }
        }
        $fileContents = $builder->buildRecordClass($info['namespace'], $this->getRecordParentClass());
        File::save($filePath, $fileContents, 0664, 0755);
        $this->line("Record class created ($filePath)");
    }

    protected function createTableStructureClassFile(ClassBuilder $builder, array $info, $overwrite) {
        $filePath = $info['structure_file_path'];
        if (File::exist($filePath)) {
            if ($overwrite === false) {
                $this->line('TableStructure class creation cancelled');
                return;
            } else if ($overwrite === true) {
                File::remove();
            } else if ($this->confirm("TableStructure file {$filePath} already exists. Overwrite?")) {
                File::remove();
            } else {
                $this->line('TableStructure class creation cancelled');
                return;
            }
        }
        $fileContents = $builder->buildStructureClass(
            $info['namespace'],
            $this->getTableStructureParentClass(),
            $this->getTraitsForTableConfig()
        );
        File::save($filePath, $fileContents, 0664, 0755);
        $this->line("TableStructure class created ($filePath)");
    }

}

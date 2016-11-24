<?php

namespace PeskyCMF\Console\Commands;

use Illuminate\Console\Command;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Db\CmfDbTable;
use PeskyCMF\Db\CmfDbRecord;
use PeskyCMF\Db\Traits\AdminIdColumn;
use PeskyCMF\Db\Traits\IdColumn;
use PeskyCMF\Db\Traits\IsActiveColumn;
use PeskyCMF\Db\Traits\TimestampColumns;
use PeskyCMF\Db\Traits\UserAuthColumns;
use PeskyCMF\Scaffold\ScaffoldSectionConfig;
use PeskyORM\Core\DbExpr;
use PeskyORM\ORM\TableStructure;
use Swayok\Utils\File;
use Swayok\Utils\Folder;
use Swayok\Utils\Set;

class MakeDbClasses extends Command {

    use OpenFileInPhpStormTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:db_classes {table_name} {schema?} {--with-scaffold}'
                            . ' {--overwrite= : 1|0|yes|no; what to do if classes already exist}'
                            . ' {--only= : model|object|table_config|scaffold; create only specified class}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create classes for DB table.';

    protected $dbModelView = 'cmf::console.db_model';
    protected $dbObjectView = 'cmf::console.db_object';
    protected $dbTableStructureView = 'cmf::console.db_table_config';
    protected $scaffoldConfigView = 'cmf::console.db_scaffold_config';

    protected $modelParentClass = CmfDbTable::class;
    protected $objectParentClass = CmfDbRecord::class;
    protected $tableStructureParentClass = TableStructure::class;
    protected $scaffoldConfigParentClass = ScaffoldSectionConfig::class;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $dataForViews = $this->preapareAndGetDataForViews();
        if (empty($dataForViews)) {
            return;
        }

        $only = $this->option('only');

        if (!$only || $only === 'model') {
            $this->createDbModel($dataForViews);
        }
        if (!$only || $only === 'object') {
            $this->createDbObject($dataForViews);
        }
        if (!$only || $only === 'table_config') {
            $this->createDbTableStructure($dataForViews);
        }
        if ((!$only && $this->option('with-scaffold')) || $only === 'scaffold') {
            $this->createScaffoldConfig($dataForViews);
        }

        $this->openFileInPhpStorm($dataForViews['files']['table_config']);

        $this->line('Done');
    }

    protected function preapareAndGetDataForViews() {
        $tableName = $this->argument('table_name');
        $modelClass = call_user_func([$this->modelParentClass, 'getFullModelClassByTableName'], $tableName);
        $folder = $this->getFolderAndValidate($tableName, $modelClass);
        if (empty($folder)) {
            return false;
        }
        $tableSchema = $this->getDbSchema();
        $columns = $this->getColumns($tableName, $tableSchema);
        if (empty($columns)) {
            return false;
        }
        $dataForViews = [
            'folder' => $folder,
            'table' => $tableName,
            'schema' => $tableSchema,
            'columns' => $columns,
            'modelParentClass' => $this->modelParentClass,
            'objectParentClass' => $this->objectParentClass,
            'tableConfigParentClass' => $this->tableStructureParentClass,
            'scaffoldConfigParentClass' => $this->scaffoldConfigParentClass,
            'modelClassName' => call_user_func([$this->modelParentClass, 'getModelNameByTableName'], $tableName),
            'objectClassName' => call_user_func([$this->modelParentClass, 'getObjectNameByTableName'], $tableName),
            'tableConfigClassName' => call_user_func([$this->modelParentClass, 'getTableConfigNameByTableName'], $tableName),
            'scaffoldConfigClassName' => CmfConfig::getInstance()->getScaffoldConfigNameByTableName($tableName),
            'traitsForTableConfig' => $this->getTraitsForTableConfig()
        ];
        $dataForViews['modelAlias'] = $dataForViews['objectClassName'];
        $dataForViews['namespace'] = call_user_func([$this->modelParentClass, 'getRootNamespace']) . '\\' . $dataForViews['objectClassName'];
        $dataForViews['files']['model'] = $folder . DIRECTORY_SEPARATOR . $dataForViews['modelClassName'] . '.php';
        $dataForViews['files']['object'] = $folder . DIRECTORY_SEPARATOR . $dataForViews['objectClassName'] . '.php';
        $dataForViews['files']['table_config'] = $folder . DIRECTORY_SEPARATOR . $dataForViews['tableConfigClassName'] . '.php';
        $dataForViews['files']['scaffold_config'] = $folder . DIRECTORY_SEPARATOR . $dataForViews['scaffoldConfigClassName'] . '.php';
        Folder::load($folder, true, 0775);
        return $dataForViews;
    }

    protected function getColumns($tableName, $tableSchema = 'public') {
        $dataSource = call_user_func([$this->modelParentClass, '_getDataSource'], 'default');
        $query = "SELECT * FROM `information_schema`.`columns` WHERE `table_name` = ``$tableName`` AND `table_schema` = ``$tableSchema``";
        $columns = $dataSource->processRecords($dataSource->query(DbExpr::create($query)));
        if (empty($columns)) {
            $this->line("Table [$tableName] possibly not exists");
            return false;
        }
        $columns = Set::combine($columns, '/column_name', '/.');
        return $columns;
    }

    protected function getFolderAndValidate($tableName, $modelClass) {
        $folder = preg_replace(
            ['%\\\[^\\\]+?' . call_user_func([$this->modelParentClass, 'getModelClassSuffix']) . '$%is', '%\\\%', '%^App%'],
            ['', DIRECTORY_SEPARATOR, $this->getBasePathToApp()],
            $modelClass
        );
        if (file_exists($folder) && is_dir($folder)) {
            $overwriteOption = $this->option('overwrite');
            if ($overwriteOption === '1' || $overwriteOption !== 'yes') {
                // overwrite
            } else if ($overwriteOption === '0' || $overwriteOption === 'no') {
                $this->line('Overwriting not allowed. Operation rejected.');
                return false;
            } else if ($this->ask("Classes for table [$tableName] already exist. Overwrite?", 'no') === 'no') {
                $this->line('Operation rejected');
                return false;
            }
        }
        return $folder;
    }

    protected function getBasePathToApp() {
        return base_path('app');
    }

    protected function getDbSchema($default = 'public') {
        return $this->argument('schema') ?: $default;
    }

    /**
     * @return array (
     *      NameOfTrait1::class,
     *      NameOfTrait2::class => ['col1_name', 'col2_name']
     * )
     */
    protected function getTraitsForTableConfig() {
        return [
            UserAuthColumns::class,
            IdColumn::class,
            AdminIdColumn::class,
            IsActiveColumn::class,
            TimestampColumns::class
        ];
    }

    protected function createDbModel($dataForViews) {
        $modelFile = $dataForViews['files']['model'];
        if (File::exist($modelFile)) {
            File::remove();
        }
        File::save($modelFile, view($this->dbModelView, $dataForViews)->render(), 0664);
        $this->openFileInPhpStorm($modelFile);
        $this->line("Model class created ($modelFile)");
    }

    protected function createDbObject($dataForViews) {
        $objectFile = $dataForViews['files']['object'];
        if (File::exist($objectFile)) {
            File::remove();
        }
        File::save($objectFile, view($this->dbObjectView, $dataForViews)->render(), 0664);
        $this->openFileInPhpStorm($objectFile);
        $this->line("Object class created ($objectFile)");
    }

    protected function createDbTableStructure($dataForViews) {
        $tableConfigFile = $dataForViews['files']['table_config'];
        if (File::exist($tableConfigFile)) {
            File::remove();
        }
        File::save($tableConfigFile, view($this->dbTableStructureView, $dataForViews)->render(), 0664);
        $this->openFileInPhpStorm($tableConfigFile);
        $this->line("TableConfig class created ($tableConfigFile)");
    }

    protected function createScaffoldConfig($dataForViews) {
        $scaffoldConfigFile = $dataForViews['files']['scaffold_config'];
        if (File::exist($scaffoldConfigFile)) {
            File::remove();
        }
        File::save($scaffoldConfigFile, view($this->scaffoldConfigView, $dataForViews)->render(), 0664);
        $this->openFileInPhpStorm($scaffoldConfigFile);
        $this->line("ScaffoldConfig class created ({$scaffoldConfigFile})");
    }

}

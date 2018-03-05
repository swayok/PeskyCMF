<?php

namespace PeskyCMF\Console\Commands;

use Illuminate\Console\Command;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Scaffold\KeyValueTableScaffoldConfig;
use PeskyCMF\Scaffold\NormalTableScaffoldConfig;
use PeskyORM\ORM\Column;
use PeskyORM\ORM\TableInterface;
use Swayok\Utils\File;
use Swayok\Utils\StringUtils;

class CmfMakeScaffoldCommand extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cmf:make-scaffold {table_name}'
                            . ' {--resource= : name of resource if it differs from table_name}'
                            . ' {--cmf-config-class= : full class name to a class that extends CmfConfig}'
                            . ' {--class-name= : short scaffold class name}'
                            . ' {--keyvalue : table is key-value storage}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create scaffold class for DB table.';

    /**
     * @var CmfConfig
     */
    protected $cmfConfigClass;

    protected function getScaffoldConfigParentClass() {
        if ($this->option('keyvalue')) {
            return $this->getCmfConfigClass()->config('scaffold_configs_base_class_for_key_value_tables') ?: KeyValueTableScaffoldConfig::class;
        } else {
            return $this->getCmfConfigClass()->config('scaffold_configs_base_class') ?: NormalTableScaffoldConfig::class;
        }
    }

    /**
     * @return CmfConfig
     * @throws \InvalidArgumentException
     */
    protected function getCmfConfigClass() {
        if (!$this->cmfConfigClass) {
            $class = $this->option('cmf-config-class');
            if ($class) {
                if (!class_exists($class)) {
                    throw new \InvalidArgumentException(
                        'Class ' . $class . ' provided through option --cmf-config-class does not exist'
                    );
                }
                $this->cmfConfigClass = new $class();
                if (!($this->cmfConfigClass instanceof CmfConfig)) {
                    throw new \InvalidArgumentException(
                        'Class ' . $class . ' provided through option --cmf-config-class must be instance of CmfConfig class'
                    );
                }
            } else {
                $this->cmfConfigClass = CmfConfig::getDefault();
                if (get_class($this->cmfConfigClass) === CmfConfig::class) {
                    throw new \InvalidArgumentException(
                        'Child class for CmfConfig was not found. You need to provide it through --cmf-config-class option '
                    );
                }
            }
        }
        return $this->cmfConfigClass;
    }

    /**
     * @param TableInterface $table
     * @return string - short name of scaffold class to be created
     */
    protected function getScaffoldClassName(TableInterface $table, $resourceName = null) {
        $scaffoldClassName = $this->option('class-name');
        if (empty($scaffoldClassName)) {
            return (empty($resourceName) ? $table::getAlias() : StringUtils::classify($resourceName)) . 'ScaffoldConfig';
        }
        return $scaffoldClassName;
    }

    /**
     * Execute the console command.
     *
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \UnexpectedValueException
     */
    public function handle() {
        $table = $this->getCmfConfigClass()->getTableByUnderscoredName($this->argument('table_name'));

        $namespace = $this->getNamespaceByTable($table);
        $className = $this->getScaffoldClassName($table, $this->option('resource'));

        $filePath = $this->getFolder($namespace) . $className . '.php';
        if (File::exist($filePath)) {
            if ($this->confirm("Scaffold class file {$filePath} already exists. Overwrite?")) {
                File::remove($filePath);
            } else {
                $this->line('Terminated');
                return;
            }
        }

        $this->createScaffoldClassFile($table, $namespace, $className, $filePath);


        $this->line('Done');

        $columnsTranslations = [];
        foreach ($table->getTableStructure()->getColumns() as $column) {
            $columnsTranslations[] = "'{$column->getName()}' => ''";
        }
        $columnsTranslationsFilter = implode(",\n                    ", $columnsTranslations) . ",";
        $columnsTranslations = implode(",\n                ", $columnsTranslations) . ",";

        $this->comment(<<<INFO
Menu item for CmfConfig:
[
    'label' => cmfTransCustom('{$table->getTableStructure()->getTableName()}.menu_title'),
    'url' => routeToCmfItemsTable('{$table->getTableStructure()->getTableName()}'),
    'icon' => ''
],

Translations:
    '{$table->getTableStructure()->getTableName()}' => [
        'menu_title' => '',
        'datagrid' => [
            'header' => '',
            'column' => [
                $columnsTranslations
            ],
            'filter' => [
                '{$table->getTableStructure()->getTableName()}' => [
                    $columnsTranslationsFilter
                ],
            ],
        ],
        'form' => [
            'header_create' => '',
            'header_edit' => '',
            'input' => [
                $columnsTranslations
            ],
        ],
        'item_details' => [
            'header' => '',
            'field' => [
                $columnsTranslations
            ],
        ]
    ]

INFO
);
    }

    protected function getBaseNamespace() {
    }

    protected function getNamespaceByTable($table) {
        $namespace = (new \ReflectionClass($table))->getNamespaceName();
        return preg_replace('%^PeskyCM[FS]\\\Db\\\%', config('peskyorm.classes_namespace', '\\App\\Db') . '\\', $namespace);
    }

    protected function getFolder($namespace) {
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

    protected function createScaffoldClassFile(TableInterface $table, $namespace, $className, $filePath) {
        $parentClass = $this->getScaffoldConfigParentClass();
        $parentClassShort = class_basename($parentClass);
        $tableClassShort = class_basename($table);

        $contents = <<<VIEW
<?php

namespace $namespace;

use {$parentClass};
use PeskyCMF\Scaffold\DataGrid\DataGridColumn;
use PeskyCMF\Scaffold\Form\FormInput;
use PeskyCMF\Scaffold\Form\InputRenderer;
use PeskyCMF\Scaffold\ItemDetails\ValueCell;

class {$className} extends {$parentClassShort} {

    protected \$isDetailsViewerAllowed = true;
    protected \$isCreateAllowed = true;
    protected \$isEditAllowed = true;
    protected \$isCloningAllowed = false;
    protected \$isDeleteAllowed = true;
    
    static public function getTable() {
        return {$tableClassShort}::getInstance();
    }
    
    static protected function getIconForMenuItem() {
        return ''; //< icon classes like: 'fa fa-cog' or just delete if you do not want an icon
    }
    
    protected function createDataGridConfig() {
        return parent::createDataGridConfig()
            ->readRelations([
                {$this->makeContainsForDataGrid($table)}
            ])
            ->setOrderBy('{$table::getPkColumnName()}', 'asc')
            ->setColumns([
                {$this->makeFieldsListForDataGrid($table)}
            ]);
    }
    
    protected function createDataGridFilterConfig() {
        return parent::createDataGridFilterConfig()
            ->setFilters([
                {$this->makeFiltersList($table)}
            ]);
    }

    protected function createItemDetailsConfig() {
        return parent::createItemDetailsConfig()
            ->readRelations([
                {$this->makeContainsForItemDetailsViewer($table)}
            ])
            ->setValueCells([
                {$this->makeFieldsListForItemDetailsViewer($table)}
            ]);
    }
    
    protected function createFormConfig() {
        return parent::createFormConfig()
            ->setWidth(50)
            ->setFormInputs([
                {$this->makeFieldsListForItemForms($table)}
            ]);
    }
}
VIEW;
        File::save($filePath, $contents, 0664, 0755);
    }

    protected function makeContainsForDataGrid(TableInterface $table) {
        $contains = [];
        foreach ($this->getJoinableRelationNames($table) as $relationName) {
            $contains[] = "'{$relationName}' => ['*'],";
        }
        return implode("\n                ", $contains);
    }

    protected function makeContainsForItemDetailsViewer(TableInterface $table) {
        $contains = [];
        foreach ($this->getJoinableRelationNames($table) as $relationName) {
            $contains[] = "'{$relationName}',";
        }
        return implode('', $contains);
    }

    protected function getJoinableRelationNames(TableInterface $table) {
        $ret = [];
        foreach ($table->getTableStructure()->getRelations() as $relation) {
            if ($relation->getType() !== $relation::HAS_MANY) {
                $ret[] = $relation->getName();
            }
        }
        return $ret;
    }

    protected function makeFieldsListForDataGrid(TableInterface $table) {
        $valueViewers = [];
        foreach ($table->getTableStructure()->getColumns() as $column) {
            if ($column->isItAForeignKey()) {
                $valueViewers[] = <<<VIEW
'{$column->getName()}' => DataGridColumn::create()
                    ->setType(DataGridColumn::TYPE_LINK),
VIEW;
            } else if (!in_array($column->getType(), [Column::TYPE_TEXT, Column::TYPE_JSON, Column::TYPE_JSONB, Column::TYPE_BLOB], true)){
                $valueViewers[] = "'{$column->getName()}',";
            }
        }
        return implode("\n                ", $valueViewers);
    }

    protected function makeFiltersList(TableInterface $table) {
        $valueViewers = [];
        foreach ($table->getTableStructure()->getColumns() as $column) {
            if (!in_array($column->getType(), [Column::TYPE_TEXT, Column::TYPE_JSON, Column::TYPE_JSONB, Column::TYPE_BLOB], true)) {
                $valueViewers[] = "'{$column->getName()}',";
            }
        }
        return implode("\n                ", $valueViewers);
    }

    protected function makeFieldsListForItemDetailsViewer(TableInterface $table) {
        $valueViewers = [];
        foreach ($table->getTableStructure()->getColumns() as $column) {
            if ($column->isItAForeignKey()) {
                $valueViewers[] = <<<VIEW
'{$column->getName()}' => ValueCell::create()
                    ->setType(ValueCell::TYPE_LINK),
VIEW;
            } else {
                $valueViewers[] = "'{$column->getName()}',";
            }
        }
        return implode("\n                ", $valueViewers);
    }

    protected function makeFieldsListForItemForms(TableInterface $table) {
        $valueViewers = [];
        foreach ($table->getTableStructure()->getColumns() as $column) {
            if ($column->isValueCanBeSetOrChanged() && !$column->isAutoUpdatingValue() && !$column->isItPrimaryKey()) {
                if ($column->getName() === 'admin_id') {
                    $valueViewers[] = <<<VIEW
'{$column->getName()}' => FormInput::create()
                    ->setType(FormInput::TYPE_HIDDEN)
                    ->setSubmittedValueModifier(function () {
                        return \Auth::guard()->user()->getAuthIdentifier();
                    }),
VIEW;

                } else {
                    $valueViewers[] = "'{$column->getName()}',";
                }
            }
        }
        return implode("\n                ", $valueViewers);
    }

}

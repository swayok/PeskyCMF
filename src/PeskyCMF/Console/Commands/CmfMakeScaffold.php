<?php

namespace PeskyCMF\Console\Commands;

use Illuminate\Console\Command;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Scaffold\ScaffoldConfig;
use PeskyORM\ORM\Column;
use PeskyORM\ORM\TableInterface;
use Swayok\Utils\File;

class CmfMakeScaffold extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cmf:make-scaffold {table_name}'
                            . ' {--cmf-config-class= : full class name to a class that extends CmfConfig}'
                            . ' {--class-name= : short scaffold class name}';

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
        $abstractTableClass = $this->getBaseNamespace() . '\\AbstractScaffoldConfig';
        if (class_exists($abstractTableClass)) {
            return $abstractTableClass;
        }
        return ScaffoldConfig::class;
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
                $this->cmfConfigClass = CmfConfig::getInstance();
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
    protected function getScaffoldClassName(TableInterface $table) {
        $scaffoldClassName = $this->option('class-name');
        if (empty($scaffoldClassName)) {
            return $table::getAlias() . 'ScaffoldConfig';
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

        $namespace = (new \ReflectionClass($table))->getNamespaceName();
        $className = $this->getScaffoldClassName($table);

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

        $this->comment(<<<INFO
Menu item for CmfConfig:
[
    'label' => self::transCustom('.{$table->getTableStructure()->getTableName()}.menu_title'),
    'url' => routeToCmfItemsTable('{$table->getTableStructure()->getTableName()}'),
    'icon' => ''
]
INFO
);
    }

    protected function getBaseNamespace() {
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

        $contents = <<<VIEW
<?php

namespace $namespace;

use {$parentClass};

use PeskyCMF\Scaffold\DataGrid\DataGridFieldConfig;
use PeskyCMF\Scaffold\ItemDetails\ItemDetailsFieldConfig;
use PeskyCMF\Scaffold\Form\FormFieldConfig;
use PeskyCMF\Scaffold\Form\InputRendererConfig;

class {$className} extends {$parentClassShort} {

    protected \$isDetailsViewerAllowed = true;
    protected \$isCreateAllowed = true;
    protected \$isEditAllowed = true;
    protected \$isDeleteAllowed = true;
    
    protected function createDataGridConfig() {
        return parent::createDataGridConfig()
            ->readRelations([
                {$this->makeContainsForDataGrid($table)}
            ])
            ->setOrderBy('{$table::getPkColumnName()}', 'asc')
            ->setColumns([
                {$this->makeFieldsListForDataGrid($table)}
            ])
            ->closeFilterByDefault();
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
        File::save($filePath, $contents, 0664);
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
                    ->setType(DataGridColumn::TYPE_LINK)
VIEW;
            } else if (!in_array($column->getType(), [Column::TYPE_TEXT, Column::TYPE_JSON, Column::TYPE_JSONB, Column::TYPE_BLOB], true)){
                $valueViewers[] = "'{$column->getName()}'";
            }
        }
        return implode(",\n                ", $valueViewers);
    }

    protected function makeFiltersList(TableInterface $table) {
        $valueViewers = [];
        foreach ($table->getTableStructure()->getColumns() as $column) {
            if (!in_array($column->getType(), [Column::TYPE_TEXT, Column::TYPE_JSON, Column::TYPE_JSONB, Column::TYPE_BLOB], true)) {
                $valueViewers[] = "'{$column->getName()}'";
            }
        }
        return implode(",\n                ", $valueViewers);
    }

    protected function makeFieldsListForItemDetailsViewer(TableInterface $table) {
        $valueViewers = [];
        foreach ($table->getTableStructure()->getColumns() as $column) {
            if ($column->isItAForeignKey()) {
                $valueViewers[] = <<<VIEW
'{$column->getName()}' => ValueCell::create()
                    ->setType(ValueCell::TYPE_LINK)
VIEW;
            } else {
                $valueViewers[] = "'{$column->getName()}'";
            }
        }
        return implode(",\n                ", $valueViewers);
    }

    protected function makeFieldsListForItemForms(TableInterface $table) {
        $valueViewers = [];
        foreach ($table->getTableStructure()->getColumns() as $column) {
            if ($column->isValueCanBeSetOrChanged() && !$column->isAutoUpdatingValue() && !$column->isItPrimaryKey()) {
                if ($column->getName() === 'admin_id') {
                    $valueViewers[] = <<<VIEW
'{$column->getName()}' => FormInput::create()
                    ->setRenderer(function () {
                        return InputRenderer::create('cmf::input/hidden');
                    })->setValueConverter(function () {
                        return \Auth::guard()->user()->id;
                    })
VIEW;

                } else {
                    $valueViewers[] = "'{$column->getName()}'";
                }
            }
        }
        return implode(",\n                ", $valueViewers);
    }

}

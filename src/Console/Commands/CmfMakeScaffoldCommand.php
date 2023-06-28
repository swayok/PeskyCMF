<?php

declare(strict_types=1);

namespace PeskyCMF\Console\Commands;

use Illuminate\Config\Repository as ConfigsRepository;
use PeskyCMF\Scaffold\KeyValueTableScaffoldConfig;
use PeskyCMF\Scaffold\NormalTableScaffoldConfig;
use PeskyORM\ORM\ClassBuilder\ClassBuilder;
use PeskyORM\ORM\Table\TableInterface;
use PeskyORM\ORM\TableStructure\TableColumn\TableColumnDataType;
use PeskyORM\Utils\StringUtils;
use Swayok\Utils\File;

class CmfMakeScaffoldCommand extends CmfCommand
{
    protected $signature = 'cmf:make-scaffold
        {table_name}
        {cmf-section? : cmf section name (key) that exists in config(\'peskycmf.cmf_configs\')}
        {--resource= : name of resource if it differs from table_name}
        {--cmf-config-class= : full class name to a class that extends CmfConfig}
        {--class-name= : short scaffold class name}
        {--keyvalue : table is key-value storage}';

    protected $description = 'Create scaffold class for DB table.';

    public function handle(): int
    {
        $table = $this->getTableInstanceByTableName($this->argument('table_name'));

        $namespace = $this->getScaffoldsNamespace();
        $className = $this->getScaffoldClassName($table, $this->option('resource'));

        $filePath = $this->getFolder() . $className . '.php';
        if (File::exist($filePath)) {
            if ($this->confirm("Scaffold class file {$filePath} already exists. Overwrite?")) {
                File::remove($filePath);
            } else {
                $this->line('Terminated');
                return 0;
            }
        }

        $this->createScaffoldClassFile($table, $namespace, $className, $filePath);


        $this->line($filePath . ' created');

        $columnsTranslations = [];
        foreach ($table->getTableStructure()->getColumns() as $column) {
            $columnsTranslations[] = "'{$column->getName()}' => ''";
        }
        $columnsTranslationsFilter = implode(",\n                    ", $columnsTranslations) . ",";
        $columnsTranslations = implode(",\n                ", $columnsTranslations) . ",";

        $this->comment(
            <<<INFO

Translations:
    '{$table->getTableStructure()->getTableName()}' => [
        'menu_title' => ,
        'datagrid' => [
            'header' => ,
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
            'header_create' => ,
            'header_edit' => ,
            'input' => [
                $columnsTranslations
            ],
        ],
        'item_details' => [
            'header' => ,
            'field' => [
                $columnsTranslations
            ],
        ]
    ]

INFO
        );
        return 0;
    }

    protected function getScaffoldConfigParentClass(): string
    {
        if ($this->option('keyvalue')) {
            return $this->getCmfConfig()->config(
                'ui.scaffold_configs_base_class_for_key_value_tables'
            ) ?: KeyValueTableScaffoldConfig::class;
        }
        return $this->getCmfConfig()->config('ui.scaffold_configs_base_class') ?: NormalTableScaffoldConfig::class;
    }

    /**
     * @return string - short name of scaffold class to be created
     */
    protected function getScaffoldClassName(TableInterface $table, ?string $resourceName = null): string
    {
        $scaffoldClassName = $this->option('class-name');
        if (empty($scaffoldClassName)) {
            return StringUtils::toPascalCase($resourceName ?: $table->getTableName()) . 'ScaffoldConfig';
        }
        return $scaffoldClassName;
    }

    protected function getTableInstanceByTableName(string $tableName): TableInterface
    {
        /** @var ClassBuilder $tableClass */
        $dbClassBuilder = $this->getConfigsRepository()->get(
            'peskyorm.class_builder',
            ClassBuilder::class
        );
        $namespace = $this->getConfigsRepository()->get(
            'peskyorm.classes_namespace',
            'App\\Db'
        );
        /** @var TableInterface $tableClass */
        $tableClass = trim($namespace, ' \\')
            . '\\' . StringUtils::toPascalCase($tableName)
            . '\\' . $dbClassBuilder::makeClassName($tableName, $dbClassBuilder::TEMPLATE_TABLE);
        return $tableClass::getInstance();
    }

    protected function getScaffoldsNamespace(): string
    {
        $appSubfolder = str_replace('/', '\\', $this->getCmfConfig()->appSubfolder());
        $scaffoldsSubfolder = str_replace('/', '\\', $this->getScaffoldsFolderName());
        return 'App\\' . $appSubfolder . '\\' . $scaffoldsSubfolder;
    }

    protected function getScaffoldsFolderName(): string
    {
        return 'Scaffolds';
    }

    protected function getFolder(): string
    {
        $appSubfolder = str_replace('/', '\\', $this->getCmfConfig()->appSubfolder());
        return $this->app->path() . DIRECTORY_SEPARATOR
            . $appSubfolder . DIRECTORY_SEPARATOR
            . $this->getScaffoldsFolderName() . DIRECTORY_SEPARATOR;
    }

    protected function createScaffoldClassFile(
        TableInterface $table,
        string $namespace,
        string $className,
        string $filePath
    ): void {
        $parentClass = $this->getScaffoldConfigParentClass();
        $parentClassShort = class_basename($parentClass);
        $tableClass = get_class($table);
        $tableClassShort = class_basename($table);

        $contents = <<<VIEW
<?php

namespace $namespace;

use {$parentClass};
use {$tableClass};
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

    public static function getTable() {
        return {$tableClassShort}::getInstance();
    }

    protected static function getIconForMenuItem() {
        // icon classes like: 'fa fa-cog' or just delete if you do not want an icon
        return '';
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

    protected function makeContainsForDataGrid(TableInterface $table): string
    {
        $contains = [];
        foreach ($this->getJoinableRelationNames($table) as $relationName) {
            $contains[] = "'{$relationName}' => ['*'],";
        }
        return implode("\n                ", $contains);
    }

    protected function makeContainsForItemDetailsViewer(TableInterface $table): string
    {
        $contains = [];
        foreach ($this->getJoinableRelationNames($table) as $relationName) {
            $contains[] = "'{$relationName}' => ['*'],";
        }
        return implode("\n                ", $contains);
    }

    protected function getJoinableRelationNames(TableInterface $table): array
    {
        $ret = [];
        foreach ($table->getTableStructure()->getRelations() as $relation) {
            if ($relation->getType() !== $relation::HAS_MANY) {
                $ret[] = $relation->getName();
            }
        }
        return $ret;
    }

    protected function makeFieldsListForDataGrid(TableInterface $table): string
    {
        $valueViewers = [];
        foreach ($table->getTableStructure()->getColumns() as $column) {
            $valueViewers[] = "'{$column->getName()}',";
        }
        return implode("\n                ", $valueViewers);
    }

    protected function makeFiltersList(TableInterface $table): string
    {
        $valueViewers = [];
        foreach ($table->getTableStructure()->getColumns() as $column) {
            if (
                !in_array(
                    $column->getDataType(),
                    [
                        TableColumnDataType::TEXT,
                        TableColumnDataType::JSON,
                        TableColumnDataType::BLOB,
                    ],
                    true
                )
            ) {
                $valueViewers[] = "'{$column->getName()}',";
            }
        }
        return implode("\n                ", $valueViewers);
    }

    protected function makeFieldsListForItemDetailsViewer(TableInterface $table): string
    {
        $valueViewers = [];
        foreach ($table->getTableStructure()->getColumns() as $column) {
            $valueViewers[] = "'{$column->getName()}',";
        }
        return implode("\n                ", $valueViewers);
    }

    protected function makeFieldsListForItemForms(TableInterface $table): string
    {
        $valueViewers = [];
        foreach ($table->getTableStructure()->getColumns() as $column) {
            if (
                $column->isReadonly()
                && !$column->isAutoUpdatingValues()
                && !$column->isPrimaryKey()
            ) {
                if ($column->getName() === 'admin_id') {
                    $valueViewers[] = <<<VIEW
'{$column->getName()}' => FormInput::create()
                    ->setType(FormInput::TYPE_HIDDEN)
                    ->setSubmittedValueModifier(function () {
                        return static::getUser()->id;
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

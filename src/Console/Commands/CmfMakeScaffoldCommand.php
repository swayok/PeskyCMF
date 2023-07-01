<?php

declare(strict_types=1);

namespace PeskyCMF\Console\Commands;

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

        $dataForView = [
            'tableName' => $table->getTableStructure()->getTableName(),
            'columns' => array_keys($table->getTableStructure()->getColumns())
        ];
        $this->comment(
            "\n\nTranslations:\n"
            . $this->renderStubView('scaffold_translations', $dataForView)
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
        $tableClass = get_class($table);

        $dataForView = [
            'namespace' => $namespace,
            'parentClass' => $parentClass,
            'className' => $className,
            'tableClass' => $tableClass,
            'pkName' => $table::getPkColumnName(),
            'contains' => $this->getJoinableRelationNames($table),
            'columns' => $table->getTableStructure()->getColumns(),
            'filters' => $this->getFilterableColumns($table),
            'inputs' => $this->makeFieldsListForItemForms($table),
        ];

        File::save(
            $filePath,
            $this->renderStubView('scaffold_config', $dataForView),
            0664,
            0755
        );
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

    protected function getFilterableColumns(TableInterface $table): array
    {
        $filters = [];
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
                $filters[] = $column->getName();
            }
        }
        return $filters;
    }

    protected function makeFieldsListForItemForms(TableInterface $table): array
    {
        $inputs = [];
        foreach ($table->getTableStructure()->getColumns() as $column) {
            if (
                $column->isReadonly()
                && !$column->isAutoUpdatingValues()
                && !$column->isPrimaryKey()
            ) {
                $inputs[] = $column->getName();
            }
        }
        return $inputs;
    }
}

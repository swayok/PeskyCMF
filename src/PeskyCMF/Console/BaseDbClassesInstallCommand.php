<?php

namespace PeskyCMF\Console;

use Illuminate\Console\Command;
use PeskyCMF\Db\CmfDbRecord;
use PeskyCMF\Db\CmfDbTable;
use PeskyORM\ORM\TableStructure;
use Swayok\Utils\File;
use Swayok\Utils\Folder;
use Swayok\Utils\StringUtils;

abstract class BaseDbClassesInstallCommand extends Command {

    protected $description = 'Install :command_suffix into CMF (classes and migration)';
    protected $signature = 'cmf:install-:command_suffix
            {base_class_name=:default_base_class_name : base class name for all classes that will be created} 
            {app_section_name=Admin : subfolder name in your app folder where CMF installed (by default: /app/Admin)} 
            {database_classes_app_subfolder=Db : subfolder name in your app folder where all ORM classes strored (by default: /app/Db)}';

    public function __construct() {
        $suffix = static::getCommandSuffix();
        $this->signature = StringUtils::insert($this->signature, [
            'command_suffix' => strtolower($suffix),
            'default_base_class_name' => $this->getDefaultBaseClassName()
        ]);
        $this->description = StringUtils::insert($this->description, [
            'command_suffix' => $suffix,
        ]);
        parent::__construct();
    }

    /**
     * Suffix for command name and templates dir name.
     * Command name is "cmf::install-{suffix}"
     * Default templates path is "{cmf_path}/resources/views/install/db/{suffix}"
     * For example if suffix is "admins" command name will be "cmf::install-admins" and
     * templates path will be "{cmf_path}/resources/views/install/db/admins"
     * @return string
     */
    abstract protected function getCommandSuffix();

    protected function getDefaultBaseClassName() {
        return ucfirst($this->getCommandSuffix());
    }

    protected function getPathToTemplates() {
        return __DIR__ . '/../resources/views/install/db/' . $this->getCommandSuffix() . DIRECTORY_SEPARATOR;
    }

    public function fire() {
        $dataForTemplates = $this->getDataForTemplates();
        $pathToTemplates = $this->getPathToTemplates();
        $this->createDbClasses($dataForTemplates, $pathToTemplates);
        $this->createMigration($dataForTemplates, $pathToTemplates);
        $this->line('Done');
    }

    static protected function getParentClasses($dbClassesAppSubfolder) {
        $ret = [
            'parentFullClassNameForRecord' => CmfDbRecord::class,
            'parentClassNameForRecord' => class_basename(CmfDbRecord::class),
            'parentFullClassNameForTable' => CmfDbTable::class,
            'parentClassNameForTable' => class_basename(CmfDbTable::class),
            'parentFullClassNameForTableStructure' => TableStructure::class,
            'parentClassNameForTableStructure' => class_basename(TableStructure::class),
        ];
        if (File::exist(app_path($dbClassesAppSubfolder) . DIRECTORY_SEPARATOR . 'AbstractRecord.php')) {
            $ret['parentClassNameForRecord'] = 'AbstractRecord';
            $ret['parentFullClassNameForRecord'] = "App\\{$dbClassesAppSubfolder}\\AbstractRecord";
        }
        if (File::exist(app_path($dbClassesAppSubfolder) . DIRECTORY_SEPARATOR . 'AbstractTable.php')) {
            $ret['parentClassNameForTable'] = 'AbstractTable';
            $ret['parentFullClassNameForTable'] = "App\\{$dbClassesAppSubfolder}\\AbstractTable";
        }
        if (File::exist(app_path($dbClassesAppSubfolder) . DIRECTORY_SEPARATOR . 'AbstractTableStructure.php')) {
            $ret['parentClassNameForTableStructure'] = 'AbstractTableStructure';
            $ret['parentFullClassNameForTableStructure'] = "App\\{$dbClassesAppSubfolder}\\AbstractTableStructure";
        }
        return $ret;
    }

    /**
     * @return array
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function getDataForTemplates() {
        $baseClassName = ucfirst(trim(trim($this->input->getArgument('base_class_name')), '/\\'));
        $dataForViews = [
            'sectionName' => ucfirst(trim(trim($this->input->getArgument('app_section_name')), '/\\')),
            'baseClassNameSingular' => StringUtils::singularize($baseClassName),
            'baseClassNamePlural' => StringUtils::pluralize($baseClassName),
            'baseClassNameUnderscored' => StringUtils::underscore(StringUtils::pluralize($baseClassName)),
            'dbClassesAppSubfolder' => $this->input->getArgument('database_classes_app_subfolder')
        ];
        return array_merge(
            $dataForViews,
            static::getParentClasses($dataForViews['dbClassesAppSubfolder'])
        );
    }

    protected function createDbClasses(array $dataForTemplates, $pathToTemplates) {
        $dbFolder = Folder::load(app_path($dataForTemplates['dbClassesAppSubfolder']), true, 0755);
        $adminDbClassesFolderPath = $dbFolder->pwd() . DIRECTORY_SEPARATOR . $dataForTemplates['baseClassNamePlural'];
        $writeAdminDbClasses = (
            !Folder::exist($adminDbClassesFolderPath)
            || $this->confirm("Classes for {$dataForTemplates['baseClassNameUnderscored']} table in {$adminDbClassesFolderPath} folder already exist. Overwrite?")
        );
        if ($writeAdminDbClasses) {
            $subfolder = Folder::load($adminDbClassesFolderPath, true, 0755);
            File::load($subfolder->pwd() . DIRECTORY_SEPARATOR . $dataForTemplates['baseClassNameSingular'] . '.php', true, 0755, 0644)
                ->write(\View::file($pathToTemplates . 'record_class.php', $dataForTemplates)->render());
            File::load($subfolder->pwd() . DIRECTORY_SEPARATOR . $dataForTemplates['baseClassNamePlural'] . 'Table.php', true, 0755, 0644)
                ->write(\View::file($pathToTemplates . 'table_class.php', $dataForTemplates)->render());
            File::load($subfolder->pwd() . DIRECTORY_SEPARATOR . $dataForTemplates['baseClassNamePlural'] . 'TableStructure.php', true, 0755, 0644)
                ->write(\View::file($pathToTemplates . 'table_structure_class.php', $dataForTemplates)->render());
            File::load($subfolder->pwd() . DIRECTORY_SEPARATOR . $dataForTemplates['baseClassNamePlural'] . 'ScaffoldConfig.php', true, 0755, 0644)
                ->write(\View::file($pathToTemplates . 'scaffold_config_class.php', $dataForTemplates)->render());
        }
    }

    protected function createMigration(array $dataForTemplates, $pathToTemplates) {
        $migrationFilePath = database_path('migrations/2014_01_01_000000_create_' . $dataForTemplates['baseClassNameUnderscored'] . '_table.php');
        $writeMigration = (
            !File::exist($migrationFilePath)
            || $this->confirm("Migration {$migrationFilePath} for {$dataForTemplates['baseClassNamePlural']} table already exists. Overwrite?")
        );
        if ($writeMigration) {
            File::load($migrationFilePath, true, 0755, 0644)
                ->write(\View::file($pathToTemplates . 'table_migration.php', $dataForTemplates));
        }
    }

}
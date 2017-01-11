<?php

namespace PeskyCMF\Console\Commands;

use Illuminate\Database\Console\Migrations\BaseCommand;
use Swayok\Utils\File;
use Swayok\Utils\Folder;
use Swayok\Utils\StringUtils;

class CmfInstallSettings extends BaseCommand {

    protected $description = 'Install Settings into CMF (classes and migration)';
    protected $signature = 'cmf:install-settings  
            {app_section_name=Admin : subfolder name in your app folder where CMF installed (by default: /app/Admin)} 
            {base_class_name=Settings : base class name for all classes} 
            {database_classes_app_subfolder=Db : subfolder name in your app folder where all ORM classes strored (by default: /app/Db)}';

    public function fire() {
        $viewsPath = __DIR__ . '/../../resources/views/install/db/settings/';
        $baseClassName = ucfirst(trim(trim($this->input->getArgument('base_class_name')), '/\\'));
        $dataForViews = [
            'sectionName' => ucfirst(trim(trim($this->input->getArgument('section_name')), '/\\')),
            'baseClassNameSingular' => StringUtils::singularize($baseClassName),
            'baseClassNamePlural' => StringUtils::pluralize($baseClassName),
            'baseClassNameUnderscored' => StringUtils::underscore(StringUtils::pluralize($baseClassName)),
            'dbClassesAppSubfolder' => $this->input->getArgument('database_classes_app_subfolder')
        ];
        // classes
        $dbFolder = Folder::load(app_path('/' . $dataForViews['dbClassesAppSubfolder']), true, 0755);
        $adminDbClassesFolderPath = $dbFolder->pwd() . $dataForViews['baseClassNamePlural'];
        $writeAdminDbClasses = !Folder::exist($adminDbClassesFolderPath) || $this->confirm('Classes for setttings table ' . $adminDbClassesFolderPath . ' already exist. Overwrite?');
        if ($writeAdminDbClasses) {
            $subfolder = Folder::load($adminDbClassesFolderPath, true, 0755);
            File::load($subfolder->pwd() . $dataForViews['baseClassNameSingular'] . '.php', true, 0755, 0644)
                ->write(\View::file($viewsPath . 'settings_record_class.php', $dataForViews)->render());
            File::load($subfolder->pwd() . $dataForViews['baseClassNamePlural'] . 'Table.php', true, 0755, 0644)
                ->write(\View::file($viewsPath . 'settings_table_class.php', $dataForViews)->render());
            File::load($subfolder->pwd() . $dataForViews['baseClassNamePlural'] . 'TableStructure.php', true, 0755, 0644)
                ->write(\View::file($viewsPath . 'settings_table_structure_class.php', $dataForViews)->render());
            File::load($subfolder->pwd() . $dataForViews['baseClassNamePlural'] . 'ScaffoldConfig.php', true, 0755, 0644)
                ->write(\View::file($viewsPath . 'settings_scaffold_config_class.php', $dataForViews)->render());
        }
        // migration
        $migrationFilePath = database_path('migrations/2014_01_01_000000_create_' . strtolower($dataForViews['baseClassNamePlural']) . '_table');
        $writeMigration = !File::exist($migrationFilePath) || $this->confirm('Migration for setttings table ' . $migrationFilePath . ' already exists. Overwrite?');
        if ($writeMigration) {
            File::load($migrationFilePath, true, 0755, 0644)
                ->write(\View::file($viewsPath . 'settings_table_migration.php', $dataForViews));
        }
        $this->line('Done');
    }
}
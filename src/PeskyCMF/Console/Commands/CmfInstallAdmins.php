<?php

namespace PeskyCMF\Console\Commands;

use Illuminate\Database\Console\Migrations\BaseCommand;
use Swayok\Utils\File;
use Swayok\Utils\Folder;
use Swayok\Utils\StringUtils;

class CmfInstallAdmins extends BaseCommand {

    protected $description = 'Install Admins into CMF (classes and migration)';
    protected $signature = 'cmf:install-admins {app_section_name=Admins} {base_class_name=Admins} {database_classes_app_subfolder=Db}';

    public function fire() {
        $viewsPath = __DIR__ . '/../../resources/views/install/db/admins/';
        $baseClassName = ucfirst(trim(trim($this->input->getArgument('admins_base_class_name')), '/\\'));
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
        $writeAdminDbClasses = !Folder::exist($adminDbClassesFolderPath) || $this->confirm('Classes for admins table ' . $adminDbClassesFolderPath . ' already exist. Overwrite?');
        if ($writeAdminDbClasses) {
            $subfolder = Folder::load($adminDbClassesFolderPath, true, 0755);
            File::load($subfolder->pwd() . $dataForViews['baseClassNameSingular'] . '.php', true, 0755, 0644)
                ->write(\View::file($viewsPath . 'admin_record_class.php', $dataForViews)->render());
            File::load($subfolder->pwd() . $dataForViews['baseClassNamePlural'] . 'Table.php', true, 0755, 0644)
                ->write(\View::file($viewsPath . 'admins_table_class.php', $dataForViews)->render());
            File::load($subfolder->pwd() . $dataForViews['baseClassNamePlural'] . 'TableStructure.php', true, 0755, 0644)
                ->write(\View::file($viewsPath . 'admins_table_structure_class.php', $dataForViews)->render());
            File::load($subfolder->pwd() . $dataForViews['baseClassNamePlural'] . 'ScaffoldConfig.php', true, 0755, 0644)
                ->write(\View::file($viewsPath . 'admins_scaffold_config_class.php', $dataForViews)->render());
        }
        // migration
        $migrationFilePath = database_path('migrations/2014_01_01_000000_create_' . strtolower($dataForViews['baseClassNamePlural']) . '_table');
        $writeMigration = !File::exist($migrationFilePath) || $this->confirm('Migration for admins table ' . $migrationFilePath . ' already exists. Overwrite?');
        if ($writeMigration) {
            File::load($migrationFilePath, true, 0755, 0644)
                ->write(\View::file($viewsPath . 'admins_table_migration.php', $dataForViews));
        }
        $this->line('Done');
    }
}
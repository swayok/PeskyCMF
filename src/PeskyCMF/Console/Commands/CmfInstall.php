<?php

namespace PeskyCMF\Console\Commands;

use Illuminate\Database\Console\Migrations\BaseCommand;
use Swayok\Utils\File;
use Swayok\Utils\Folder;

class CmfInstall extends BaseCommand {

    protected $description = 'Install CMF';
    protected $signature = 'cmf:install {app_subfolder=Admin} {url_prefix=admin} {database_classes_app_subfolder=Db}'
                                        . '{--no-db-classes}';

    public function fire() {
        $appSubfolder = ucfirst(trim(trim($this->input->getArgument('app_subfolder')), '/\\'));
        $baseFolderPath = app_path($appSubfolder);
        if (Folder::exist($baseFolderPath)) {
            $this->line('Terminated. Folder [' . $baseFolderPath . '] already exist');
            return;
        }
        $viewsPath = __DIR__ . '/../../resources/views/install/';
        $dataForViews = [
            'sectionName' => $appSubfolder,
            'urlPrefix' => trim(trim($this->input->getArgument('url_prefix'), '/\\')),
            'dbClassesAppSubfolder' => $this->input->getArgument('database_classes_app_subfolder')
        ];
        // create site loader
        $siteLoaderFilePath = app_path('SiteLoaders/' . $appSubfolder . 'SiteLoader.php');
        $writeSiteLoaderFile = !File::exist($siteLoaderFilePath) || $this->ask('SiteLoader file ' . $siteLoaderFilePath . ' already exist. Overwrite? (y/n)', 'n') === 'y';
        if ($writeSiteLoaderFile) {
            File::load($siteLoaderFilePath, true, 0755, 0644)
                ->write(\View::file($viewsPath . 'cmf_site_loader.blade.php', $dataForViews)->render());
        }
        // copy configs
        File::load($baseFolderPath . '/' . $appSubfolder . 'Config.php', true, 0755, 0644)
            ->write(\View::file($viewsPath . 'cmf_config.blade.php', $dataForViews)->render());
        $routesFilePath = base_path('routes/' . snake_case($appSubfolder) . '.php');
        $writeRoutesFile = !File::exist($routesFilePath) || $this->ask('Routes file ' . $routesFilePath . ' already exist. Overwrite? (y/n)', 'n') === 'y';
        if ($writeRoutesFile) {
            File::load($routesFilePath, true, 0755, 0644)
                ->write(\View::file($viewsPath . 'cmf_routes.blade.php', $dataForViews)->render());
        }
        // copy pages controller
        File::load($baseFolderPath . '/Http/Controllers/PagesController.php', true, 0755, 0644)
            ->write(\View::file($viewsPath . 'pages_controller.blade.php', $dataForViews)->render());
        if (!$this->input->getOption('no-db-classes')) {
            // copy base db classes
            $dbFolder = Folder::load(app_path('/' . $dataForViews['dbClassesAppSubfolder']), true, 0755);
            $abstractModelFilePath = $dbFolder->pwd() . 'AbstractTable.php';
            $writeAbstractModelFile = !File::exist($abstractModelFilePath) || $this->ask('AbstractTable file ' . $abstractModelFilePath . ' already exist. Overwrite? (y/n)', 'n') === 'y';
            if ($writeAbstractModelFile) {
                File::load($abstractModelFilePath, true, 0755, 0644)
                    ->write(\View::file($viewsPath . 'db/base_db_model.php', $dataForViews)->render());
            }
            $abstractRecordFilePath = $dbFolder->pwd() . 'AbstractRecord.php';
            $writeAbstractRecordFile = !File::exist($abstractRecordFilePath) || $this->ask('AbstractRecord file ' . $abstractRecordFilePath . ' already exist. Overwrite? (y/n)', 'n') === 'y';
            if ($writeAbstractRecordFile) {
                File::load($abstractRecordFilePath, true, 0755, 0644)
                    ->write(\View::file($viewsPath . 'db/base_db_record.php', $dataForViews)->render());
            }
            // copy admin table classes
            $adminDbClassesFolderPath = $dbFolder->pwd() . 'Admin';
            $writeAdminDbClasses = !Folder::exist($adminDbClassesFolderPath) || $this->ask('Classes for admins table ' . $adminDbClassesFolderPath . ' already exist. Overwrite? (y/n)', 'n') === 'y';
            if ($writeAdminDbClasses) {
                $subfolder = Folder::load($adminDbClassesFolderPath, true, 0755);
                $file = File::load($subfolder->pwd() . 'Admin.php', true, 0755, 0644);
                $file->write(\View::file($viewsPath . 'db/admin_object.php', $dataForViews)->render());
                $file = File::load($subfolder->pwd() . 'AdminsTable.php', true, 0755, 0644);
                $file->write(\View::file($viewsPath . 'db/admin_model.php', $dataForViews)->render());
                $file = File::load($subfolder->pwd() . 'AdminsTableStructure.php', true, 0755, 0644);
                $file->write(\View::file($viewsPath . 'db/admin_table_config.php', $dataForViews)->render());
                $file = File::load($subfolder->pwd() . 'AdminsScaffoldConfig.php', true, 0755, 0644);
                $file->write(\View::file($viewsPath . 'db/admin_scaffold_config.php', $dataForViews)->render());
            }
        }

        $this->line('Done');
    }
}
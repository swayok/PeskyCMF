<?php

namespace PeskyCMF\Console\Commands;

use Illuminate\Console\Command;
use Swayok\Utils\File;
use Swayok\Utils\Folder;

class CmfInstall extends Command {

    protected $description = 'Install PeskyCMF';
    protected $signature = 'cmf:install {app_subfolder=Admin} {url_prefix=admin} {database_classes_app_subfolder=Db}';

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
        $writeSiteLoaderFile = !File::exist($siteLoaderFilePath) || $this->confirm('SiteLoader file ' . $siteLoaderFilePath . ' already exist. Overwrite?');
        if ($writeSiteLoaderFile) {
            File::load($siteLoaderFilePath, true, 0755, 0644)
                ->write(\View::file($viewsPath . 'cmf_site_loader.blade.php', $dataForViews)->render());
        }
        // copy configs
        $cmfConfigFilePath = $baseFolderPath . '/' . $appSubfolder . 'Config.php';
        $writeCmfConfigFile = !File::exist($cmfConfigFilePath) || $this->confirm('CmfConfig file ' . $cmfConfigFilePath . ' already exist. Overwrite?');
        if ($writeCmfConfigFile) {
            File::load($cmfConfigFilePath, true, 0755, 0644)
                ->write(\View::file($viewsPath . 'cmf_config.blade.php', $dataForViews)->render());
        }
        $routesFilePath = base_path('routes/' . snake_case($appSubfolder) . '.php');
        $writeRoutesFile = !File::exist($routesFilePath) || $this->confirm('Routes file ' . $routesFilePath . ' already exist. Overwrite?');
        if ($writeRoutesFile) {
            File::load($routesFilePath, true, 0755, 0644)
                ->write(\View::file($viewsPath . 'cmf_routes.blade.php', $dataForViews)->render());
        }
        $sectionConfigFilePath = config_path(snake_case($appSubfolder) . '.php');
        $writeSectionConfigFile = !File::exist($sectionConfigFilePath) || $this->confirm('Site section config file ' . $sectionConfigFilePath . ' already exist. Overwrite?');
        if ($writeSectionConfigFile) {
            File::load($sectionConfigFilePath, true, 0755, 0644)
                ->write(\View::file($viewsPath . 'site_section_config.blade.php', $dataForViews)->render());
        }
        // copy pages controller
        File::load($baseFolderPath . '/Http/Controllers/PagesController.php', true, 0755, 0644)
            ->write(\View::file($viewsPath . 'pages_controller.blade.php', $dataForViews)->render());
        // copy base db classes
        /*$dbFolder = Folder::load(app_path('/' . $dataForViews['dbClassesAppSubfolder']), true, 0755);
        $abstractModelFilePath = $dbFolder->pwd() . '/AbstractTable.php';
        $writeAbstractModelFile = !File::exist($abstractModelFilePath) || $this->confirm('AbstractTable file ' . $abstractModelFilePath . ' already exist. Overwrite?');
        if ($writeAbstractModelFile) {
            File::load($abstractModelFilePath, true, 0755, 0644)
                ->write(\View::file($viewsPath . 'db/base_db_model.php', $dataForViews)->render());
        }
        $abstractRecordFilePath = $dbFolder->pwd() . '/AbstractRecord.php';
        $writeAbstractRecordFile = !File::exist($abstractRecordFilePath) || $this->confirm('AbstractRecord file ' . $abstractRecordFilePath . ' already exist. Overwrite?');
        if ($writeAbstractRecordFile) {
            File::load($abstractRecordFilePath, true, 0755, 0644)
                ->write(\View::file($viewsPath . 'db/base_db_record.php', $dataForViews)->render());
        }*/
        // create js, less and css files
        $pathToPublicFiles = public_path('packages/' . $dataForViews['urlPrefix']) . '/';
        File::save($pathToPublicFiles . 'js/' . $dataForViews['urlPrefix'] . '.custom.js', '', 0664, 0755);
        File::save($pathToPublicFiles . 'css/' . $dataForViews['urlPrefix'] . '.custom.css', '', 0664, 0755);
        File::save($pathToPublicFiles . 'less/' . $dataForViews['urlPrefix'] . '.custom.less', '', 0664, 0755);
        $this->line('Done');
    }
}
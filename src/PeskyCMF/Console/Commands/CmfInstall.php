<?php

namespace PeskyCMF\Console\Commands;

use Illuminate\Database\Console\Migrations\BaseCommand;
use Swayok\Utils\File;
use Swayok\Utils\Folder;

class CmfInstall extends BaseCommand {

    protected $description = 'Install CMF';
    protected $signature = 'cmf:install {app_subfolder=Admin} {url_prefix=admin} {database_classes_app_subfolder=Db}';

    public function fire() {
        $appSubfolder = ucfirst(trim(trim($this->input->getArgument('app_subfolder')), '/\\'));
        $lowercasedSubfolder = snake_case($appSubfolder);
        $folderPath = app_path('/' . $appSubfolder);
        if (Folder::exist($folderPath)) {
            $this->line('Terminated. Folder [' . $folderPath . '] already exist');
        }
        $folder = Folder::load($folderPath, true, 0755);
        $dataForViews = [
            'sectionName' => $appSubfolder,
            'urlPrefix' => trim(trim($this->input->getArgument('url_prefix'), '/\\')),
            'dbClassesAppSubfolder' => $this->input->getArgument('database_classes_app_subfolder')
        ];
        // copy service provider
        $file = File::load($folder->pwd() . '/' . $appSubfolder . 'ServiceProvider.php', true, 0755, 0644);
        $file->write(view('cmf::install.cmf_service_provider', $dataForViews)->render());
        // copy configs
        $subfolder = Folder::load($folder->pwd() . '/Config', true, 0755);
        $file = File::load($subfolder->pwd() . '/' . $appSubfolder . 'Config.php', true, 0755, 0644);
        $file->write(view('cmf::install.cmf_config', $dataForViews)->render());
        $file = File::load($subfolder->pwd() . '/' . $lowercasedSubfolder . '.routes.php', true, 0755, 0644);
        $file->write(view('cmf::install.cmf_routes', $dataForViews)->render());
        // copy pages controller
        $subfolder = Folder::load($folder->pwd() . '/Http/Controllers', true, 0755);
        $file = File::load($subfolder->pwd() . '/PagesController.php', true, 0755, 0644);
        $file->write(view('cmf::install.pages_controller', $dataForViews)->render());
        // create middleware folder
        File::load($folder->pwd() . '/Http/Middleware/empty', true, 0755, 0644);
        // make folder in resources
        File::load(resource_path('/views/' . $lowercasedSubfolder . '/empty'), true, 0755, 0644);
        // copy base db classes
        $dbFolder = Folder::load(app_path('/' . $this->input->getArgument('database_classes_app_subfolder')), true, 0755);
        $file = File::load($dbFolder->pwd() . '/BaseDbModel.php', true, 0755, 0644);
        $file->write(view('cmf::install.db.base_db_model')->render());
        $file = File::load($dbFolder->pwd() . '/BaseDbObject.php', true, 0755, 0644);
        $file->write(view('cmf::install.db.base_db_object')->render());
        // copy admin table classes
        $subfolder = Folder::load($dbFolder->pwd() . '/Admin', true, 0755);
        $file = File::load($subfolder->pwd() . '/Admin.php', true, 0755, 0644);
        $file->write(view('cmf::install.db.admin_object')->render());
        $file = File::load($subfolder->pwd() . '/AdminModel.php', true, 0755, 0644);
        $file->write(view('cmf::install.db.admin_model')->render());
        $file = File::load($subfolder->pwd() . '/AdminTableConfig.php', true, 0755, 0644);
        $file->write(view('cmf::install.db.admin_table_config')->render());
        $file = File::load($subfolder->pwd() . '/AdminScaffoldConfig.php', true, 0755, 0644);
        $file->write(view('cmf::install.db.admin_scaffold_config')->render());

        $this->line('Done');
    }
}
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
            'dbClassesAppSubfolder' => $this->input->getArgument('database_classes_app_subfolder'),
            'cmfCongigClassName' => $appSubfolder . 'Config'
        ];
        // copy configs
        $cmfConfigFilePath = $baseFolderPath . '/' . $dataForViews['cmfCongigClassName'] . '.php';
        $writeCmfConfigFile = !File::exist($cmfConfigFilePath) || $this->confirm('CmfConfig file ' . $cmfConfigFilePath . ' already exist. Overwrite?');
        if ($writeCmfConfigFile) {
            File::load($cmfConfigFilePath, true, 0755, 0644)
                ->write(\View::file($viewsPath . 'cmf_config.blade.php', $dataForViews)->render());
        }
        $routesFileRelativePath = 'routes/' . snake_case($appSubfolder) . '.php';
        $routesFilePath = base_path($routesFileRelativePath);
        $writeRoutesFile = !File::exist($routesFilePath) || $this->confirm('Routes file ' . $routesFilePath . ' already exist. Overwrite?');
        if ($writeRoutesFile) {
            File::load($routesFilePath, true, 0755, 0644)
                ->write(\View::file($viewsPath . 'cmf_routes.blade.php', $dataForViews)->render());
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
        $relativePathToPublicFiles = 'packages/' . $dataForViews['urlPrefix'] . '/';
        $publicFiles = [
            'js' => $relativePathToPublicFiles . 'js/' . $dataForViews['urlPrefix'] . '.custom.js',
            'css' => $relativePathToPublicFiles . 'css/' . $dataForViews['urlPrefix'] . '.custom.css',
            'less' => $relativePathToPublicFiles . 'less/' . $dataForViews['urlPrefix'] . '.custom.less',
        ];
        foreach ($publicFiles as $relativePath) {
            File::save(public_path($relativePath), '', 0664, 0755);
        }
        $this->line("Done. Update next keys in 'configs/peskycmf.php' file to activate created files:");
        $this->line(' ');

        $subfolderName = preg_replace('%[^a-zA-Z0-9]+%', '_', $dataForViews['urlPrefix']);
        $this->line("'cmf_config' => \\App\\" . $appSubfolder . '\\' . $dataForViews['cmfCongigClassName'] . '::class,');
        $this->line("'url_prefix' => '{$dataForViews['urlPrefix']}',");
        $this->line("'routes_files' => ['{$routesFileRelativePath}'],");
        $this->line("'views_subfolder' => ['{$subfolderName}'],");
        $this->line("'css_files' => ['{$publicFiles['css']}'],");
        $this->line("'js_files' => ['{$publicFiles['js']}'],");
        $this->line("'user_object_class' => Admin::class,");
        $this->line("'dictionary' => '{$subfolderName}',");

        $this->line(' ');
        $this->line("Also you may need to change configs in 'config/peskyorm.php' file");
    }
}
<?php

namespace PeskyCMF\Console\Commands;

use Illuminate\Console\Command;
use PeskyCMF\Providers\PeskyCmfServiceProvider;
use PeskyORMLaravel\Providers\PeskyOrmServiceProvider;
use Swayok\Utils\File;
use Swayok\Utils\Folder;
use Swayok\Utils\StringUtils;

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

        $migrationsPath = database_path('migrations') . DIRECTORY_SEPARATOR;
        // remove laravel's migrations
        if (File::exist($migrationsPath . '2014_10_12_000000_create_users_table.php')) {
            File::remove();
        }
        if (File::exist($migrationsPath . '2014_10_12_100000_create_password_resets_table.php')) {
            File::remove();
        }
        // remove laravel's User model
        if (File::exist(app_path('User.php'))) {
            File::remove();
        }

        // add AppSettings class if not exists
        $appSettingsPath = app_path('AppSettings.php');
        if (!File::exist($appSettingsPath)) {
            File::save($appSettingsPath, $this->getAppSettignsClassContents());
            $this->line('Added ' . $appSettingsPath);
        } else {
            $this->line('- ' . $appSettingsPath . ' already exist. skipped');
        }

        // create peskyorm.php in config_path() dir
        $peskyOrmConfigFilePath = config_path('peskyorm.php');
        $writeOrmConfigFile = !File::exist($peskyOrmConfigFilePath) || $this->confirm('PeskyORM config file ' . $peskyOrmConfigFilePath . ' already exist. Overwrite?');
        if ($writeOrmConfigFile) {
            File::load(__DIR__ . '/../../Config/peskyorm.config.php')->copy($peskyOrmConfigFilePath, true, 0664);
        }
        // create peskycmf.php in config_path() dir
        $peskyCmfConfigFilePath = config_path('peskycmf.php');
        $writeCmfConfigFile = !File::exist($peskyCmfConfigFilePath) || $this->confirm('PeskyCMF config file ' . $peskyCmfConfigFilePath . ' already exist. Overwrite?');
        $updateKeysInConfigs = null;
        if ($writeCmfConfigFile) {
            File::load(__DIR__ . '/../../Config/cmf.config.php')->copy($peskyCmfConfigFilePath, true, 0664);
            $updateKeysInConfigs = true;
        }
        if ($updateKeysInConfigs === null) {
            $updateKeysInConfigs = $this->confirm('Do you wish to update some keys in ' . $peskyCmfConfigFilePath . ' by actual values?');
        }
        if ($updateKeysInConfigs) {
            $configContents = file_get_contents($peskyCmfConfigFilePath);
            $subfolderName = preg_replace('%[^a-zA-Z0-9]+%', '_', $dataForViews['urlPrefix']);
            $replacements = [
                "%('cmf_config')\s*=>\s*.*%im" => '$1 => \\App\\' . $appSubfolder . '\\' . $dataForViews['cmfCongigClassName'] . '::class,',
                "%('url_prefix')\s*=>\s*.*%im" => "$1 => '{$dataForViews['urlPrefix']}',",
                "%('views_subfolder')\s*=>\s*.*%im" => "$1 => '{$subfolderName}',",
                "%('dictionary')\s*=>\s*.*%im" => "$1 => '{$subfolderName}',",
                "%('routes_files')\s*=>\s*\[[^\]]*\],%is" => "$1 => [\n        '{$routesFileRelativePath}',\n    ],",
                "%('css_files')\s*=>\s*\[[^\]]*\],%is" => "$1 => [\n        '{$publicFiles['css']}',\n    ],",
                "%('js_files')\s*=>\s*\[[^\]]*\],%is" => "$1 => [\n        '{$publicFiles['js']}',\n    ],",
            ];
            $configContents = preg_replace(array_keys($replacements), array_values($replacements), $configContents);
            File::save($peskyCmfConfigFilePath, $configContents, 0664, 0755);
            $this->line($peskyCmfConfigFilePath . ' updated');
        }

        // add migrations for admins and settings tables
        foreach (['settings', 'admins'] as $index => $tableName) {
            $this->addMigrationForTable($tableName, $index, $migrationsPath);
        }

        $this->extender();

        if ($updateKeysInConfigs) {
            $this->line('Done');
        } else {
            $this->line("Done. Update next keys in 'configs/peskycmf.php' file to activate created files:");
            $this->line(' ');

            $subfolderName = preg_replace('%[^a-zA-Z0-9]+%', '_', $dataForViews['urlPrefix']);
            $this->line("'cmf_config' => \\App\\" . $appSubfolder . '\\' . $dataForViews['cmfCongigClassName'] . '::class,');
            $this->line("'url_prefix' => '{$dataForViews['urlPrefix']}',");
            $this->line("'routes_files' => ['{$routesFileRelativePath}'],");
            $this->line("'views_subfolder' => ['{$subfolderName}'],");
            $this->line("'css_files' => ['{$publicFiles['css']}'],");
            $this->line("'js_files' => ['{$publicFiles['js']}'],");
            $this->line("'dictionary' => '{$subfolderName}',");

            $this->line(' ');
            $this->line("Also you may need to change configs in {$peskyOrmConfigFilePath} file");
        }

        $this->outro();
    }

    /**
     * Used in CmsInstall
     */
    protected function extender() {

    }

    protected function outro() {
        $this->line('Remeber to perform next steps to activate cms:');
        $this->line('1. Add ' . PeskyCmfServiceProvider::class . ' to you app.providers config');
        $this->line('2. Remove ' . PeskyOrmServiceProvider::class . ' from you app.providers config');
        $this->line('3. Run "php artisan migrate" to create tables in database');
        $this->line('4. Run "php artisan cmf::add-admin your-email@address.com" to create superadmin for CMS');
    }

    protected function getAppSettignsClassContents() {
        return <<<FILE
<?php

namespace App;

use PeskyCMF\PeskyCmfAppSettings;

class AppSettings extends PeskyCmfAppSettings {

}

FILE;
    }

    protected function addMigrationForTable($tableName, $index, $migrationsPath, $prefix = 'Cmf', $namespace = 'PeskyCMF') {
        $filePath = $migrationsPath . "2014_10_12_{$index}00000_create_{$tableName}_table.php";
        if (File::exist($filePath)) {
            $this->line('- migration ' . $filePath . ' already exist. skipped.');
            return;
        }
        $groupName = StringUtils::classify($tableName);
        $className = 'Create' . $groupName . 'Table';
        $extendsClass = $prefix . $groupName . 'Migration';
        $fileContents = <<<FILE
<?php 

use {$namespace}\\DB\\{$groupName}\\{$extendsClass};

class {$className} extends {$extendsClass} {

}

FILE;
        File::save($filePath, $fileContents, 0664, 0755);
        $this->line('Added migration ' . $migrationsPath);
    }
}
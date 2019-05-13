<?php

namespace PeskyCMF\Console\Commands;

use PeskyCMF\Providers\PeskyCmfServiceProvider;
use PeskyORMLaravel\Providers\PeskyOrmServiceProvider;
use Swayok\Utils\File;
use Swayok\Utils\Folder;

class CmfInstallCommand extends CmfCommand {

    protected $description = 'Install PeskyCMF';
    protected $signature = 'cmf:install 
        {app_subfolder=Admin} 
        {url_prefix=admin} 
        {database_classes_app_subfolder=Db}';

    public function fire() {
        // compatibility with Laravel <= 5.4
        $this->handle();
    }

    public function handle() {
        $appSubfolder = ucfirst(trim(trim($this->input->getArgument('app_subfolder')), '/\\'));
        $baseFolderPath = app_path($appSubfolder);
        if (Folder::exist($baseFolderPath)) {
            $this->line('Terminated. Folder [' . $baseFolderPath . '] already exist');
            return -1;
        }
        $viewsPath = __DIR__ . '/../../resources/views/install/';
        $dataForViews = [
            'sectionName' => $appSubfolder,
            'urlPrefix' => trim(trim($this->input->getArgument('url_prefix'), '/\\')),
            'dbClassesAppSubfolder' => $this->input->getArgument('database_classes_app_subfolder'),
            'cmfConfigClassName' => $appSubfolder . 'Config'
        ];
        $this->createPagesController($baseFolderPath, $viewsPath, $dataForViews);
        $this->copyBaseDbClasses($viewsPath, $dataForViews);
        $publicFiles = $this->createJsCssLessFiles($dataForViews);
        $this->cleanLaravelOrmClassesAndMigrations();
        $this->createAppSettingsClass();
        $peskyOrmConfigFilePath = $this->createPeskyOrmConfigFile();
        $this->createPeskyCmfConfigFile($appSubfolder, $dataForViews);
        $cmfSectionConfigFileNameWithoutExtension = $this->createCmfSectionConfigFile($appSubfolder, $dataForViews, $publicFiles);
        $this->createCmfConfigClassForCmfSection(
            $appSubfolder,
            $viewsPath,
            $baseFolderPath,
            $dataForViews,
            $cmfSectionConfigFileNameWithoutExtension
        );
        $this->createMigrations();
        $this->extender();

        $this->line('Done');

        $this->suggestions($peskyOrmConfigFilePath);
        $this->outro();
        return 0;
    }

    /**
     * To be used in subclasses
     */
    protected function extender() {

    }

    protected function outro() {
        $this->line('You will need to perform next steps to activate CMF:');
        $this->line('1. Add ' . PeskyCmfServiceProvider::class . ' to you app.providers config (not needed if you use PeskyCMS)');
        $this->line('2. Remove ' . PeskyOrmServiceProvider::class . ' from you app.providers config');
        $this->line('3. Run "php artisan vendor:publish --tag=public --force" to publish vendor public files');
        $this->line('4. Add "php artisan vendor:publish --tag=public --force" to you composer.json into "scripts"."post-autoload-dump" array to make all public vendor files be up to date');
        $this->line('5. Run "php artisan migrate" to create tables in database');
        $this->line('6. Run "php artisan cmf:add-admin your-email@address.com" to create superadmin for CMS');
    }

    protected function suggestions($peskyOrmConfigFilePath) {
        $this->line("You may need to change configs in {$peskyOrmConfigFilePath} file");
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

    protected function createPagesController($baseFolderPath, $viewsPath, array $dataForViews) {
        File::load($baseFolderPath . '/Http/Controllers/PagesController.php', true, 0755, 0644)
            ->write(\View::file($viewsPath . 'pages_controller.blade.php', $dataForViews)->render());
    }

    protected function copyBaseDbClasses($viewsPath, array $dataForViews) {
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
    }

    protected function createJsCssLessFiles($dataForViews) {
        $relativePathToPublicFiles = '/packages/' . $dataForViews['urlPrefix'] . '/';
        $publicFilesContents = [
            'js' => $this->getCustomJsFileContents(),
            'css' => '',
            'less' => '',
        ];
        $createdFilesByType = [];
        foreach ($publicFilesContents as $type => $contents) {
            $relativePath = $relativePathToPublicFiles . $type . '/' . $dataForViews['urlPrefix'] . '.custom.' . $type;
            File::save(public_path($relativePath), $contents, 0664, 0755);
            $createdFilesByType[$type] = $relativePath;
        }
        return $createdFilesByType;
    }

    protected function getCustomJsFileContents() {
        return <<<JS
var CmfApp = {};

/* Declare custom routes using page.route()
CmfApp.addRoutes = function () {
    
};
*/

/* Do something before application is started by page.start()
CmfApp.beforeStart = function () {
    
};
*/

/* Do something after application is started by page.start()
CmfApp.afterStart = function () {
    
};
*/

/* Extend functionality of the router
CmfApp.extendRouter = function () {
    
};
*/
JS;
    }

    protected function createMigrations() {
        $migrationsPath = $this->getMigrationsPath();
        foreach (['settings', 'admins'] as $index => $tableName) {
            $this->addMigrationForTable($tableName, $migrationsPath, strtotime("2014-01-01 0{$index}:00:00"));
        }
    }

    protected function getMigrationsPath() {
        return database_path('migrations') . DIRECTORY_SEPARATOR;
    }

    protected function cleanLaravelOrmClassesAndMigrations() {
        $migrationsPath = $this->getMigrationsPath();
        // remove laravel's migrations
        if (File::exist($migrationsPath . '2014_10_12_000000_create_users_table.php')) {
            File::remove();
        }
        if (File::exist($migrationsPath . '2014_10_12_100000_create_password_resets_table.php')) {
            File::remove();
        }
        if (File::exist(app_path('User.php'))) {
            File::remove();
        }
    }

    protected function createAppSettingsClass() {
        $appSettingsPath = app_path('AppSettings.php');
        if (!File::exist($appSettingsPath)) {
            File::save($appSettingsPath, $this->getAppSettignsClassContents());
            $this->line($appSettingsPath . ' created');
        } else {
            $this->line('- ' . $appSettingsPath . ' already exist. skipped');
        }
    }

    protected function createPeskyOrmConfigFile() {
        $peskyOrmConfigFilePath = config_path('peskyorm.php');
        $writeOrmConfigFile = (
            !File::exist($peskyOrmConfigFilePath)
            || $this->confirm('PeskyORM config file ' . $peskyOrmConfigFilePath . ' already exist. Overwrite?')
        );
        if ($writeOrmConfigFile) {
            File::load(__DIR__ . '/../../Config/peskyorm.config.php')->copy($peskyOrmConfigFilePath, true, 0664);
            $this->line($peskyOrmConfigFilePath . ' created');
        }
        return $peskyOrmConfigFilePath;
    }

    protected function createPeskyCmfConfigFile($appSubfolder, array $dataForViews) {
        $generalCmfConfigFilePath = config_path('peskycmf.php');
        if (!File::exist($generalCmfConfigFilePath)) {
            $configContents = File::contents(__DIR__ . '/../../Config/peskycmf.config.php');
            $replacements = [
                // rename 'cmf_configs.default' to 'cmf_configs.{urlPrefix}'
                "%('default')\s*=>\s*.*%im" => '\'' . $dataForViews['urlPrefix'] . '\' => \\App\\' . $appSubfolder . '\\' . $dataForViews['cmfConfigClassName'] . '::class,',
                // replace 'default_cmf_config' value by '{urlPrefix}'
                "%('default_cmf_config')\s*=>\s*.*%im" => "$1 => '{$dataForViews['urlPrefix']}',",
                // replace 'app_settings_class' value by '\App\AppSettings::class'
                "%('app_settings_class')\s*=>\s*.*%im" => "$1 => '\\App\\AppSettings::class',",
            ];
            $configContents = preg_replace(array_keys($replacements), array_values($replacements), $configContents);
            File::save($generalCmfConfigFilePath, $configContents, 0664, 0755);
            $this->line($generalCmfConfigFilePath . ' created');
            unset($configContents);
        } else {
            $this->line("Update next keys in 'configs/peskycmf.php' file to activate CMF:");
            $this->line(' ');

            $this->line("'default_cmf_config' => 'cmf',");
            $this->line("'cmf_configs' => ['cmf' => \\App\\" . $appSubfolder . '\\' . $dataForViews['cmfConfigClassName'] . "::class,\n]");
            $this->line("'app_settings_class' => \\App\\AppSettings::class,");
        }
    }

    protected function createCmfSectionConfigFile($appSubfolder, array $dataForViews, array $publicFiles) {
        $cmfSectionConfigFileNameWithoutExtension = snake_case($appSubfolder);
        $cmfSectionConfigFilePath = config_path($cmfSectionConfigFileNameWithoutExtension . '.php');
        $writeCmfSectionConfigFile = (
            !File::exist($cmfSectionConfigFilePath)
            || $this->confirm('CMF section config file ' . $cmfSectionConfigFilePath . ' already exist. Overwrite?')
        );
        if (!$writeCmfSectionConfigFile) {
            if ($this->confirm('Would you like to provide custom name for a cmf section config file?')) {
                $customConfigFileName = $this->ask('Enter file name without ".php" or emty string to skip');
                if (trim($customConfigFileName) !== '') {
                    $cmfSectionConfigFileNameWithoutExtension = $customConfigFileName;
                    $writeCmfSectionConfigFile = true;
                    $cmfSectionConfigFilePath = config_path($cmfSectionConfigFileNameWithoutExtension . '.php');
                }
            }
        }
        $updateKeysInConfigs = null;
        if ($writeCmfSectionConfigFile) {
            File::load(__DIR__ . '/../../Config/cmf_section.config.php')->copy($cmfSectionConfigFilePath, true, 0664);
            $updateKeysInConfigs = true;
        }
        if ($updateKeysInConfigs === null) {
            $updateKeysInConfigs = $this->confirm('Do you wish to update some keys in ' . $cmfSectionConfigFilePath . ' by actual values?');
        }
        $subfolderName = preg_replace('%[^a-zA-Z0-9]+%', '_', $dataForViews['urlPrefix']);
        $controllersNamespace = 'App\\' . $appSubfolder . '\\Http\\Controllers';
        $routesFileRelativePath = $this->getRoutesFileRelativePath($appSubfolder);
        if ($updateKeysInConfigs) {
            $configContents = file_get_contents($cmfSectionConfigFilePath);
            $replacements = [
                "%('url_prefix')\s*=>\s*.*%im" => "$1 => '{$dataForViews['urlPrefix']}',",
                "%('app_subfolder')\s*=>\s*.*%im" => "$1 => '$appSubfolder',",
                "%('controllers_namespace')\s*=>\s*.*%im" => "$1 => '{$controllersNamespace}',",
                "%('views_subfolder')\s*=>\s*.*%im" => "$1 => '{$subfolderName}',",
                "%('dictionary')\s*=>\s*.*%im" => "$1 => '{$subfolderName}',",
                "%('routes_files')\s*=>\s*\[[^\]]*\],%is" => "$1 => [\n        '{$routesFileRelativePath}',\n    ],",
                "%('css_files')\s*=>\s*\[[^\]]*\],%is" => "$1 => [\n            '{$publicFiles['css']}',\n        ],",
                "%('js_files')\s*=>\s*\[[^\]]*\],%is" => "$1 => [\n            '{$publicFiles['js']}',\n        ],",
            ];
            $configContents = preg_replace(array_keys($replacements), array_values($replacements), $configContents);
            File::save($cmfSectionConfigFilePath, $configContents, 0664, 0755);
            $this->line($cmfSectionConfigFilePath . ' updated');
        } else {
            $this->line("Update next keys in '$cmfSectionConfigFilePath' file to activate CMF:");
            $this->line(' ');

            $this->line("'url_prefix' => '{$dataForViews['urlPrefix']}',");
            $this->line("'app_subfolder' => '$appSubfolder',");
            $this->line("'routes_files' => ['{$routesFileRelativePath}'],");
            $this->line("'controllers_namespace' => '{$controllersNamespace}',");
            $this->line("'views_subfolder' => ['{$subfolderName}'],");
            $this->line("'css_files' => ['{$publicFiles['css']}'],");
            $this->line("'js_files' => ['{$publicFiles['js']}'],");
            $this->line("'dictionary' => '{$subfolderName}',");
        }
        return $cmfSectionConfigFileNameWithoutExtension;
    }

    protected function getRoutesFileRelativePath($appSubfolder) {
        return 'routes/' . snake_case($appSubfolder) . '.php';
    }

    protected function createCmfConfigClassForCmfSection($appSubfolder, $viewsPath, $baseFolderPath, array $dataForViews, $cmfSectionConfigFileNameWithoutExtension) {
        $cmfConfigFilePath = $baseFolderPath . '/' . $dataForViews['cmfConfigClassName'] . '.php';
        $writeCmfSectionConfigFile = (
            !File::exist($cmfConfigFilePath)
            || $this->confirm('CmfConfig file ' . $cmfConfigFilePath . ' already exist. Overwrite?')
        );
        if ($writeCmfSectionConfigFile) {
            $dataForViews['configsFileName'] = $cmfSectionConfigFileNameWithoutExtension;
            File::load($cmfConfigFilePath, true, 0755, 0644)
                ->write(\View::file($viewsPath . 'cmf_config.blade.php', $dataForViews)->render());
            $this->line($cmfConfigFilePath . ' created');
        }
        // create routes file
        $routesFileRelativePath = $this->getRoutesFileRelativePath($appSubfolder);
        $routesFilePath = base_path($routesFileRelativePath);
        $writeRoutesFile = (
            !File::exist($routesFilePath)
            || $this->confirm('Routes file ' . $routesFilePath . ' already exist. Overwrite?')
        );
        if ($writeRoutesFile) {
            File::load($routesFilePath, true, 0755, 0644)
                ->write(\View::file($viewsPath . 'cmf_routes.blade.php', $dataForViews)->render());
            $this->line($routesFilePath . ' created');
        }
    }


}
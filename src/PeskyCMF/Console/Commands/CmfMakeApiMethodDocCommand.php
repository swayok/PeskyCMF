<?php

namespace PeskyCMF\Console\Commands;

use Illuminate\Console\Command;
use PeskyCMF\Config\CmfConfig;
use Swayok\Utils\File;
use Swayok\Utils\Folder;

class CmfMakeApiMethodDocCommand extends Command {

    protected $description = 'Create class that extends CmfApiMethodDocumentation class';

    protected $signature = 'cmf:make-api-method-doc {class_name} {docs_group} 
                                {folder? : folder path relative to app_path(); default = CmfConfig::getPrimary()->api_docs_classes_folder()}';

    public function fire() {
        // compatibility with Laravel <= 5.4
        $this->handle();
    }

    public function handle() {
        $className = $this->argument('class_name');
        $folder = $this->argument('folder');
        if (trim($folder) === '') {
            $folder = CmfConfig::getPrimary()->api_methods_documentation_classes_folder();
        } else {
            $folder = app_path($folder);
        }
        $folder .= DIRECTORY_SEPARATOR . $this->argument('docs_group');
        $namespace = '\\App' . rtrim(str_ireplace([app_path(), '/'], ['', '\\'], $folder), '\\ ');
        $folder = Folder::load($folder, true, 0755);
        $filePath = $folder->pwd() . DIRECTORY_SEPARATOR . $className . '.php';
        $this->makeClass($className, $namespace, $filePath);
    }

    protected function makeClass($className, $namespace, $filePath) {
        $this->line('Writing class ' .  $namespace . '\\' . $className . ' to file ' . $filePath);
        $namespace = ltrim($namespace, '\\');
        $translationGroup = snake_case(str_ireplace(['ApiDocs', 'ApiDoc'], ['', ''], $className));
        $baseClass = CmfConfig::getPrimary()->api_method_documentation_base_class();
        $baseClassName = class_basename($baseClass);
        $fileContents = <<<CLASS
<?php

namespace {$namespace};

use {$baseClass};

class {$className} extends {$baseClassName} {

    protected \$title = '{method.{$translationGroup}.title}';
    protected \$description = '{method.{$translationGroup}.description}';

    protected \$url = '/api/v1/resource/{id}';
    public \$httpMethod = 'GET';

    public \$headers = [
        'Accept' => 'application/json',
        'Accept-Language' => '{{language}}',
        'Authorization' => 'Bearer {{auth_token}}'
    ];
    public \$urlParameters = [
//        'url_parameter' => 'int'
    ];
    public \$urlQueryParameters = [
//        '_method' => 'PUT',
//        'token' => 'string',
    ];
    public \$postParameters = [
//        'id' => 'int',
    ];
    protected \$validationErrors = [
//        'token' => ['required', 'string'],
//        'id' => ['required', 'integer', 'min:1']
    ];

    public \$onSuccess = [
//        'name' => 'string',
    ];

    /**
     * @return array
     */
    protected function getPossibleErrors() {
        /* Example:
            [
                'code' => HttpCode::NOT_FOUND,
                'title' => '{error.item_not_found}',
                'response' => [
                    'error' => 'not_found'
                ]
            ],
        */
        return [];
    }

}
CLASS;
        File::save($filePath, $fileContents, 0644, 0755);
        $this->line('File created');
    }
}
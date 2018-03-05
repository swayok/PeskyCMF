<?php

namespace PeskyCMF\Console\Commands;

use Illuminate\Console\Command;
use PeskyCMF\Config\CmfConfig;
use Swayok\Utils\File;
use Swayok\Utils\Folder;

class CmfMakeApiMethodDocsCommand extends Command {

    protected $description = 'Create class that extends CmfApiDocsSection class';

    protected $signature = 'cmf:make-api-method-docs {class_name} {docs_group} 
                                {folder? : folder path relative to app_path(); default: CmfConfig::getPrimary()->api_docs_classes_folder()}';

    public function fire() {
        // compatibility with Laravel <= 5.4
        $this->handle();
    }

    public function handle() {
        $className = $this->argument('class_name');
        $folder = $this->argument('folder');
        if (trim($folder) === '') {
            $folder = CmfConfig::getPrimary()->api_docs_classes_folder();
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
        $fileContents = <<<CLASS
<?php

namespace {$namespace};

use PeskyCMF\ApiDocs\CmfApiDocsSection;

class {$className} extends CmfApiDocsSection {

    public \$title = '{api_docs.method.{$translationGroup}.title}';
    public \$description = '{api_docs.method.{$translationGroup}.description}';

    public \$url = '/api/v1/resource/{id}';
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
    public \$validationErrors = [
//        'token' => ['required', 'string'],
//        'id' => ['required', 'integer', 'min:1']
    ];

    public \$onSuccess = [
//        'name' => 'string',
    ];

    /**
     * @return array
     */
    public function getPossibleErrors() {
        /* Example:
            [
                'code' => HttpCode::NOT_FOUND,
                'title' => \$this->getTranslation('{api_docs.error.item_not_found}'),
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
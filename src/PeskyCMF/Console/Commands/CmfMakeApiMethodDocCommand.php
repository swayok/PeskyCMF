<?php

namespace PeskyCMF\Console\Commands;

use Illuminate\Console\Command;
use PeskyCMF\Config\CmfConfig;
use Swayok\Utils\File;
use Swayok\Utils\Folder;

class CmfMakeApiMethodDocCommand extends Command {

    protected $description = 'Create class that extends CmfApiMethodDocumentation class';

    protected $signature = 'cmf:make-api-method-doc {class_name} {docs_group} {--auto : automatically set url, http method, translation paths, etc..}
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
        if (File::exist($filePath) && !$this->confirm("File $filePath already exists. Overwrite?")) {
            $this->line('Cancelled');
            return;
        }
        $this->makeClass($className, $namespace, $filePath);
    }

    protected function guessUrl($group, $method) {
        $url = '/api/';
        if ($group) {
            $url .= $group . '/';
        }
        return $url . $method;
    }

    protected function makeClass($className, $namespace, $filePath) {
        $this->line('Writing class ' .  $namespace . '\\' . $className . ' to file ' . $filePath);
        $namespace = ltrim($namespace, '\\');
        $baseClass = CmfConfig::getPrimary()->api_method_documentation_base_class();
        $baseClassName = class_basename($baseClass);
        $translationSubGroup = snake_case(preg_replace('%(ApiDocs?|(Method)?Documentation)%', '', $className));
        $docsGroup = $this->argument('docs_group');
        $translationGroup = empty($docsGroup) ? 'method' : snake_case($docsGroup);
        $url = $this->guessUrl($translationGroup === 'method' ? null : $translationGroup, $translationSubGroup);
        $translationGroup .= '.' . $translationSubGroup;
        $httpMethod = 'GET';
        $httpMethodUrlQueryParam = '//        \'_method\' => \'PUT\',';
        $postParams = '';
        if (!$this->option('auto') && $this->confirm('Do you want to configure documentation class?', true)) {
            list($url, $httpMethod, $translationGroup) = $this->askQuestions($url, $httpMethod, $translationGroup);
            if (!in_array($httpMethod, ['GET', 'POST'], true)) {
                $httpMethodUrlQueryParam = "        '_method' => '$httpMethod',";
                $httpMethod = $httpMethod === 'PUT' ? 'POST' : 'GET';
            }
            if ($httpMethod === 'POST') {
                $postParams = "public \$postParameters = [\n        'id' => 'int'\n    ];\n";
            }
        }

        $fileContents = <<<CLASS
<?php

namespace {$namespace};

use {$baseClass};

class {$className} extends {$baseClassName} {

    protected \$title = '{{$translationGroup}.title}';
    protected \$description = '{{$translationGroup}.description}';

    protected \$url = '{$url}';
    public \$httpMethod = '{$httpMethod}';

    public \$headers = [
        'Accept' => 'application/json',
        'Accept-Language' => '{{language}}',
        'Authorization' => 'Bearer {{auth_token}}'
    ];
    public \$urlParameters = [
//        'url_parameter' => 'int'
    ];
    public \$urlQueryParameters = [
$httpMethodUrlQueryParam
    ];
    $postParams
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
                'title' => '{{$translationGroup}.item_not_found}',
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
        $this->line('Add next translations to you dictionaries:');
        $translations = [];
        array_set($translations, $translationGroup . '.title', '');
        array_set($translations, $translationGroup . '.description', '');
        $this->line($this->arrayToString($translations));
    }

    protected function askQuestions($url, $httpMethod, $translationGroup) {
        $url = $this->ask('API method url:', $url);
        $httpMethod = $this->askWithCompletion('HTTP method:', ['GET', 'POST', 'PUT', 'DELETE'], $httpMethod);
        $translationGroup = $this->ask('Translation path:', $translationGroup);
        return [$url, $httpMethod, $translationGroup];
    }

    protected function arrayToString(array $array, $depth = 0) {
        $ret = "[\n";
        foreach ($array as $key => $value) {
            $ret .= str_pad('', $depth * 4 + 4, ' ') . "'$key' => ";
            if (is_array($value)) {
                $ret .= $this->arrayToString($value, $depth + 1);
            } else {
                $ret .= "'$value',\n";
            }
        }
        return $ret . str_pad('', $depth * 4, ' ') . "],\n";
    }
}
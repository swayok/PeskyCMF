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
        if (!$this->option('auto') && $this->confirm('Do you want to configure documentation class?', true)) {
            list($url, $httpMethod, $translationGroup) = $this->askQuestions($url, $translationGroup);
            list($urlParams, $urlQueryParams, $postParams, $validationErrors) = $this->askParams($url, $httpMethod);
            if (!in_array($httpMethod, ['GET', 'POST'], true)) {
                $httpMethod = $httpMethod === 'PUT' ? 'POST' : 'GET';
            }
        } else {
            $postParams = "    public \$postParameters = [\n        //'key' => 'value',\n    ];\n";
            $urlParams = "    public \$urlParameters = [\n        //'key' => 'value',\n    ];\n";
            $urlQueryParams = "    public \$urlQueryParameters = [\n        //'_method' => 'PUT',\n    ];\n";
            $validationErrors = "    public \$validationErrors = [\n        //'key' => 'required|string',\n    ];\n";
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

{$urlParams}{$urlQueryParams}{$postParams}
{$validationErrors}
    
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
        $this->line("File $filePath created");
        $this->line('Add next translations to you dictionaries:');
        $translations = [];
        array_set($translations, $translationGroup . '.title', '');
        array_set($translations, $translationGroup . '.description', '');
        $this->line($this->arrayToString($translations));
    }

    protected function askQuestions($url, $translationGroup) {
        $url = $this->ask('API method url (use "{param}" to mark variable part of URL):', $url);
        $httpMethod = $this->choice('HTTP method:', ['GET', 'POST', 'PUT', 'DELETE'], 0);
        $translationGroup = $this->ask('Translation path:', $translationGroup);
        return [$url, $httpMethod, $translationGroup];
    }

    protected function arrayToString(array $array, $depth = 0) {
        $ret = "[\n";
        foreach ($array as $key => $value) {
            $ret .= str_pad('', $depth * 4 + 4, ' ');
            if (!is_int($key)) {
                $ret .= "'$key' => ";
            }
            if (is_array($value)) {
                $ret .= $this->arrayToString($value, $depth + 1);
            } else {
                $ret .= "'$value',\n";
            }
        }
        return $ret . str_pad('', $depth * 4, ' ') . "],\n";
    }

    protected function askParams($url, $httpMethod) {
        $urlParams = [];
        $urlQueryParams = [];
        $postParams = '';
        // url params
        if (preg_match_all('%\{([^}]+)\}%', $url, $matches)) {
            foreach ($matches[1] as $paramName) {
                $urlParams[$paramName] = 'string';
            }
        }
        $urlParams = '    public $urlParameters = ' . rtrim($this->arrayToString($urlParams, 1), " ,\n") . ";\n";
        // url query params
        if (!in_array($httpMethod, ['GET', 'POST'], true)) {
            $urlQueryParams['_method'] = $httpMethod;
        }
        list($urlQueryParams, $validationErrors) = $this->askParamsFor('URL Query', $urlQueryParams);
        $urlQueryParams = '    public $urlQueryParameters = ' . rtrim($this->arrayToString($urlQueryParams, 1), " ,\n") . ";\n";
        // post params
        if (in_array($httpMethod, ['POST', 'PUT'], true)) {
            list($postParams, $validationErrors) = $this->askParamsFor('POST');
            $postParams = '    public $postParameters = ' . rtrim($this->arrayToString($postParams, 1), " ,\n") . ";\n";
        }
        $validationErrors = '    protected $validationErrors = ' . rtrim($this->arrayToString($validationErrors, 1), " ,\n") . ";\n";
        return [$urlParams, $urlQueryParams, $postParams, $validationErrors];
    }

    protected function askParamsFor($type, array $predefinedParams = []) {
        $validationErrors = [];
        $params = $predefinedParams;
        if ($this->confirm("Do you want to provide $type parameters?", true)) {
            $this->line('Format: "param_name:comment" (ex: "id:integer, required") or "parent.key:comment" to nest params');
            if (!empty($predefinedParams)) {
                $this->line('Predefined parameters: ' . $this->arrayToString($predefinedParams, 0));
            }
            do {
                $param = $this->ask("Input $type parameter or press Enter to stop");
                if (trim($param) === '') {
                    break;
                }
                $parts = explode(':', $param, 2);
                if (count($parts) !== 2) {
                    $this->error('Invalid parameter format. "param_name:comment" expected');
                    continue;
                }
                array_set($params, $parts[0], $parts[1]);
                array_set($validationErrors, $parts[0], ['required', 'string']);
                $this->line('Parameters: ' . $this->arrayToString($params));
            } while (trim($param) !== '');
        }
        return [$params, $validationErrors];
    }
}
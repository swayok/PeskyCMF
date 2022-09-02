<?php

declare(strict_types=1);

namespace PeskyCMF\Console\Commands;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Swayok\Utils\File;

class CmfMakeApiMethodDocCommand extends CmfMakeApiDocCommand
{
    
    protected $description = 'Create class that extends CmfApiMethodDocumentation class';
    
    protected $signature = 'cmf:make-api-method-doc
        {class_name}
        {docs_group}
        {cmf-section? : cmf section name (key) that exists in config(\'peskycmf.cmf_configs\') and accessiblr by CmfManager}
        {--folder= : folder path relative to app_path(); default = CmfConfig::getPrimary()->api_documentation_classes_folder()}
        {--cmf-config-class= : full class name to a class that extends CmfConfig}
        {--auto : automatically set url, http method, translation paths, etc..}';
    
    protected function makeClass(string $className, string $namespace, string $filePath): void
    {
        $this->line('Writing class ' . $namespace . '\\' . $className . ' to file ' . $filePath);
        $namespace = ltrim($namespace, '\\');
        $baseClass = $this->getCmfConfig()->getApiDocumentationModule()->getMethodBaseClass();
        /** @noinspection DuplicatedCode */
        $baseClassName = class_basename($baseClass);
        $classSuffix = $this->getCmfConfig()->getApiDocumentationModule()->getClassNameSuffix();
        $translationSubGroup = Str::snake(
            preg_replace(
                '%(ApiDocs?|(Method)?(Doc(umentation)?)?|' . preg_quote($classSuffix, '%') . '$)%',
                '',
                $className
            )
        );
        $docsGroup = $this->argument('docs_group');
        $translationGroup = empty($docsGroup) ? 'method' : Str::snake($docsGroup);
        $url = $this->guessUrl($translationGroup === 'method' ? null : $translationGroup, $translationSubGroup);
        $translationGroup .= '.' . $translationSubGroup;
        $httpMethod = 'GET';
        if (!$this->option('auto') && $this->confirm('Do you want to configure documentation class?', true)) {
            [$url, $httpMethod, $translationGroup] = $this->askQuestions($url, $translationGroup);
            [$urlParams, $urlQueryParams, $postParams, $validationErrors] = $this->askParams($url, $httpMethod);
            if (!in_array($httpMethod, ['GET', 'POST'], true)) {
                $httpMethod = in_array($httpMethod, ['PUT', 'DELETE'], true) ? 'POST' : 'GET';
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
use PeskyCMF\ApiDocs\ApiMethodErrorResponseInfo;

class {$className} extends {$baseClassName} {

    //protected static \$position = 10;

    protected \$translationsBasePath = '{$translationGroup}';

    //protected \$title = '{{$translationGroup}.title}';
    //protected \$titleForPostman = '{{$translationGroup}.title_for_postman}';
    //protected \$description = '{{$translationGroup}.description}';

    protected \$url = '{$url}';
    public \$httpMethod = '{$httpMethod}';

{$urlParams}{$urlQueryParams}{$postParams}
{$validationErrors}

    public \$onSuccess = [
//        'name' => 'string',
    ];

    /**
     * @return array|ApiMethodErrorResponseInfo[]
     */
    protected function getPossibleErrors() {
        /* Example:
            ApiMethodErrorResponseInfo::create()
                ->setTitle('{{$translationGroup}.error.item_not_found.title}')
                ->setDescription('{{$translationGroup}.error.item_not_found.description}')
                ->setHttpCode(HttpCode::NOT_FOUND)
                ->setResponse([
                    'message' => 'Record not found in DB.'
                ]),
        */
        return [];
    }

}
CLASS;
        File::save($filePath, $fileContents, 0644, 0755);
        $this->line("File $filePath created");
        $this->line('Add next translations to you dictionaries:');
        $translations = [];
        Arr::set($translations, $translationGroup . '.title', '');
        Arr::set($translations, $translationGroup . '.title_for_postman', '');
        Arr::set($translations, $translationGroup . '.description', '');
        Arr::set($translations, $translationGroup . '.params.url', []);
        Arr::set($translations, $translationGroup . '.params.url_query', []);
        Arr::set($translations, $translationGroup . '.params.post', []);
        Arr::set($translations, $translationGroup . '.header', []);
        Arr::set($translations, $translationGroup . '.error', []);
        $this->line($this->arrayToString($translations));
    }
    
    protected function guessUrl(string $group, string $method): string
    {
        $url = '/api/';
        if ($group) {
            $url .= $group . '/';
        }
        return $url . $method;
    }
    
    protected function askQuestions(string $url, string $translationGroup): array
    {
        $url = $this->ask('API method url (use "{param}" to mark variable part of URL):', $url);
        $httpMethod = $this->choice('HTTP method:', ['GET', 'POST', 'PUT', 'DELETE'], 0);
        $translationGroup = $this->ask('Translation path:', $translationGroup);
        return [$url, $httpMethod, $translationGroup];
    }
    
    protected function askParams(string $url, string $httpMethod): array
    {
        $urlParams = [];
        $urlQueryParams = [];
        $postParams = '';
        // url params
        if (preg_match_all('%\{([^}]+)}%', $url, $matches)) {
            foreach ($matches[1] as $paramName) {
                $urlParams[$paramName] = 'string';
            }
        }
        $urlParams = '    public $urlParameters = ' . rtrim($this->arrayToString($urlParams, 1), " ,\n") . ";\n";
        // url query params
        if (!in_array($httpMethod, ['GET', 'POST'], true)) {
            $urlQueryParams['_method'] = $httpMethod;
        }
        [$urlQueryParams, $validationErrors] = $this->askParamsFor('URL Query', $urlQueryParams);
        $urlQueryParams = '    public $urlQueryParameters = ' . rtrim($this->arrayToString($urlQueryParams, 1), " ,\n") . ";\n";
        // post params
        if (in_array($httpMethod, ['POST', 'PUT'], true)) {
            [$postParams, $validationErrors] = $this->askParamsFor('POST');
            $postParams = '    public $postParameters = ' . rtrim($this->arrayToString($postParams, 1), " ,\n") . ";\n";
        }
        $validationErrors = '    protected $validationErrors = ' . rtrim($this->arrayToString($validationErrors, 1), " ,\n") . ";\n";
        return [$urlParams, $urlQueryParams, $postParams, $validationErrors];
    }
    
    protected function askParamsFor(string $type, array $predefinedParams = []): array
    {
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
                Arr::set($params, $parts[0], $parts[1]);
                Arr::set($validationErrors, $parts[0], ['required', 'string']);
                $this->line('Parameters: ' . $this->arrayToString($params));
            } while (true);
        }
        return [$params, $validationErrors];
    }
}

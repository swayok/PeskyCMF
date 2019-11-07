<?php

namespace PeskyCMF\ApiDocs;

use PeskyCMF\HttpCode;

/**
 * Extend this class to describe an API method
 */
abstract class CmfApiMethodDocumentation extends CmfApiDocumentation {

    // override next properties and methods

    //static protected $position = 10;

    //protected $translationsBasePath = 'group.method';
    //protected $title = '{group.method.title}';
    //protected $description = '{group.method.description}';

    /**
     * You can use simple string or translation path in format: '{method.some_name.title_for_postman}'
     * Note that translation path will be passed to CmfConfig::transCustom() so you do not need to add dictionary name
     * to translation path - it will be added automatically using CmfConfig::getPrimary()->custom_dictionary_name().
     * Resulting path will be: 'admin.api_docs.method.some_name.title' if dictionary name is 'admin'
     * When null: $this->translationsBasePath . '.title_for_postman' or $this->getUrl() will be used
     * @var string|null
     */
    protected $titleForPostman;
    //protected $titleForPostman = '{group.method.title_for_postman}';

    /**
     * You can use '{url_parameter}' or ':url_parameter' to insert parameters into url and be able to
     * export it to postman properly (postman uses ':url_parameter' format but it is not expressive
     * enough unlike '{url_parameter}' variant)
     * @var string
     */
    protected $url = '/api/example/{url_parameter}/list';
    protected $httpMethod = 'GET';

    protected $headers = [
        'Accept' => 'application/json',
        'Accept-Language' => '{{language}}',
        'Authorization' => 'Bearer {{auth_token}}'
    ];
    /**
     * List of parameters used inside URL
     * For url: '/api/items/{id}/list' 'id' is url parameter (brackets needed only to highlight url parameter)
     * @var array
     */
    protected $urlParameters = [
//        'url_parameter' => 'int'
    ];
    protected $urlQueryParameters = [
//        '_method' => 'PUT',
//        'token' => 'string',
    ];
    protected $postParameters = [
//        'id' => 'int',
    ];
    protected $validationErrors = [
//        'token' => ['required', 'string'],
//        'id' => ['required', 'integer', 'min:1']
    ];

    protected $onSuccess = [
//        'name' => 'string',
    ];

    /**
     * @return array
     */
    protected function getPossibleErrors() {
        /* Example:
            ApiMethodErrorResponseInfo::create()
                ->setTitle('Not found')
                ->setDescription('Happens if record not exists in DB')
                ->setHttpCode(HttpCode::NOT_FOUND)
                ->setResponse([
                    'message' => 'item_not_found'
                ])

            OR

            [
                'code' => HttpCode::NOT_FOUND,
                'title' => 'Not found',
                'description' => 'Happens if record not exists in DB',
                'response' => [
                    'message' => 'item_not_found'
                ]
            ]

            or if you want localized API docs:
            ApiMethodErrorResponseInfo::create()
                ->setTitle('{error.item_not_found.title}')
                ->setDescription('{error.item_not_found.description}')
                ->setHttpCode(HttpCode::NOT_FOUND)
                ->setResponse([
                    'message' => 'item_not_found'
                ])

            OR
            [
                'code' => HttpCode::NOT_FOUND,
                'title' => '{error.item_not_found.title}',
                'description' => '{error.item_not_found.description}',
                'response' => [
                    'message' => 'item_not_found'
                ]
            ],
        */
        return [];
    }

    // service properties and methods

    /**
     * @return array
     */
    protected function getCommonErrors() {
        return [
            static::$authFailError,
            static::$accessDeniedError,
            static::$serverError,
        ];
    }

    /**
     * @return array
     */
    public function getErrors() {
        $additionalErrors = [];
        if (count($this->validationErrors)) {
            $error = static::$dataValidationError;
            array_set($error, 'response.errors', $this->getValidationErrors());
            $additionalErrors[] = $error;
        }
        $errors = array_merge($this->getCommonErrors(), $additionalErrors, $this->getPossibleErrors());
        // translate titles and descriptions
        foreach ($errors as &$error) {
            if ($error instanceof ApiMethodErrorResponseInfo) {
                $error = $error->toArray();
            }
            $error['title'] = $this->translateInserts(array_get($error, 'title', ''));
            $error['description'] = $this->translateInserts(array_get($error, 'description', ''));
        }
        unset($error);
        usort($errors, function ($err1, $err2) {
            return (int)array_get($err1, 'code', 0) <=> (int)array_get($err2, 'code', 0);
        });
        return $errors;
    }

    static protected $authFailError = [
        'code' => HttpCode::UNAUTHORISED,
        'title' => '{error.auth_failure.title}',
        'description' => '{error.auth_failure.description}',
        'response' => [
            'message' => 'Unauthenticated.'
        ]
    ];

    static protected $accessDeniedError = [
        'code' => HttpCode::FORBIDDEN,
        'title' => '{error.access_denied.title}',
        'description' => '{error.auth_failure.description}',
        'response' => [
            'message' => 'Unauthorized.'
        ]
    ];

    static protected $dataValidationError = [
        'code' => HttpCode::CANNOT_PROCESS,
        'title' => '{error.validation_errors.title}',
        'description' => '{error.auth_failure.description}',
        'response' => [
            'message' => 'The given data was invalid.',
            'errors' => []
        ]
    ];

    static protected $serverError = [
        'code' => HttpCode::SERVER_ERROR,
        'title' => '{error.server_error.title}',
        'description' => '{error.auth_failure.description}',
        'response' => [
            'message' => 'Server error.',
        ]
    ];

    static protected $itemNotFound = [
        'code' => HttpCode::NOT_FOUND,
        'title' => '{error.item_not_found.title}',
        'description' => '{error.item_not_found.description}',
        'response' => [
            'message' => 'Record not found in DB.',
        ]
    ];

    public function getTitleForPostman() {
        $title = $this->titleForPostman
            ? $this->translateInserts($this->titleForPostman)
            : $this->translatePath(rtrim($this->translationsBasePath, '.') . '.title_for_postman');
        return !empty(trim($title)) && stripos($title, '.title_for_postman') === false ? $title : $this->getUrl();
    }

    public function getUrl() {
        return trim((string)$this->url);
    }

    public function getHttpMethod() {
        return $this->httpMethod;
    }

    public function getHttpMethodForPostman() {
        return strtoupper(
            preg_replace('%^\s*(get|post|put|delete|patch|head|options|connect|trace).*$%i', '$1', $this->httpMethod)
        );
    }

    public function getHeaders() {
        return $this->prepareUrlVarsForTable(
            rtrim($this->translationsBasePath, '.') . '.header',
            $this->headers,
            []
        );
    }

    public function getUrlParameters() {
        return $this->prepareUrlVarsForTable(
            rtrim($this->translationsBasePath, '.') . '.params.url',
            $this->urlParameters,
            $this->getDefaultParamsValuesForPostman('url')
        );
    }

    public function getUrlQueryParameters() {
        return $this->prepareUrlVarsForTable(
            rtrim($this->translationsBasePath, '.') . '.params.url_query',
            $this->urlQueryParameters,
            $this->getDefaultParamsValuesForPostman('url_query')
        );
    }

    public function getPostParameters() {
        return $this->prepareUrlVarsForTable(
            rtrim($this->translationsBasePath, '.') . '.params.post',
            $this->postParameters,
            $this->getDefaultParamsValuesForPostman('post')
        );
    }

    public function getValidationErrors() {
        return $this->translateArrayValues($this->validationErrors);
    }

    public function getOnSuccessData() {
        return $this->translateArrayValues($this->onSuccess);
    }

    /**
     * @param string $group - one of: 'url', 'url_query', 'post'
     * @return array
     */
    public function getDefaultParamsValuesForPostman(string $group): array {
        return [];
    }

    /**
     * Translate values of the $array recursively
     * @param array $array
     * @return array
     */
    protected function translateArrayValues(array $array) {
        foreach ($array as &$value) {
            if (is_string($value)) {
                $value = $this->translateInserts($value);
            } else if (is_array($value)) {
                $value = $this->translateArrayValues($value);
            }
        }
        return $array;
    }

    /**
     * Prepare url variables to be displayed in docs as table with 3 columns: name, type, description
     * @param string $group
     * @param array $params
     * @param array $defaultValues
     * @return array
     */
    protected function prepareUrlVarsForTable(string $group, array $params, array $defaultValues) {
        $params = $this->translateArrayValues($params);
        $ret = [];
        $descriptions = $this->translatePath($group);
        if (!is_array($descriptions)) {
            $descriptions = [];
        }
        foreach ($params as $key => $value) {
            $ret[$key] = [
                'name' => $key,
                'type' => $value,
                'description' => array_get($descriptions, $key, ''),
                'value' => array_get($defaultValues, $key, '')
            ];
        }
        return $ret;
    }

    final public function isMethodDocumentation() {
        return true;
    }

    public function getConfigForPostman(): array {
        $queryParams = [];
        foreach ($this->getUrlQueryParameters() as $name => $info) {
            if ($name === '_method') {
                $queryParams[] = urlencode($name) . '=' . $info['type'];
            } else {
                $queryParams[] = urlencode($name) . '=' . array_get($info, 'value', '');
            }
        }
        $queryParams = empty($queryParams) ? '' : '?' . implode('&', $queryParams);
        $item = [
            'name' => $this->getTitleForPostman(),
            'request' => [
                'url' => url(
                    preg_replace('%\{([^/]+?)\}%', ':$1', $this->getUrl()) . $queryParams
                ),
                'method' => $this->getHttpMethodForPostman(),
                'description' => $this->cleanTextForPostman($this->getTitle() . "\n" . $this->getDescription()),
                'header' => [],
                'body' => [
                    'mode' => 'formdata',
                    'formdata' => [
                    ]
                ],

            ],
            'response' => []
        ];
        foreach ($this->getHeaders() as $key => $info) {
            $item['request']['header'][] = [
                'key' => $key,
                'value' => $info['type'],
                'description' => $this->cleanTextForPostman($info['description'])
            ];
        }
        foreach ($this->getPostParameters() as $key => $info) {
            $item['request']['body']['formdata'][] = [
                'key' => $key,
                'value' => ($key === '_method') ? $info['type'] : array_get($info, 'value', ''),
                'description' => $this->cleanTextForPostman($info['description']),
                'type' => 'text',
                'enabled' => true
            ];
        }
        return $item;
    }

    protected function cleanTextForPostman(string $text): string {
        return preg_replace(
            ['% +%', "%\n\s+%s"],
            [' ', "\n"],
            trim(strip_tags(
                preg_replace(
                    ["%\n+%m", '%</(p|div|li|ul)>|<br>%'],
                    [' ', "\n"],
                    $text
                )
            ))
        );
    }

}

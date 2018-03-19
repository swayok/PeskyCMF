<?php

namespace PeskyCMF\ApiDocs;

use PeskyCMF\Config\CmfConfig;
use PeskyCMF\HttpCode;
use Ramsey\Uuid\Uuid;

abstract class CmfApiMethodDocumentation {

    // override next properties and methods

    /**
     * Position of this method within the group.
     * Used only by CmfConfig::loadApiMethodsDocumentationClassesFromFileSystem().
     * @var int|null
     */
    static protected $position;

    /**
     * You can use simple string or translation path in format: '{method.some_name.title}'
     * Note that translation path will be passed to CmfConfig::transCustom() so you do not need to add dictionary name
     * to translation path - it will be added automatically using CmfConfig::getPrimary()->custom_dictionary_name().
     * Resulting path will be: 'admin.api_docs.method.some_name.title' if dictionary name is 'admin'
     * @var string
     */
    protected $title = '';

    /**
     * You can use simple string or translation path in format: '{method.some_name.description}'
     * Note that translation path will be passed to CmfConfig::transCustom() so you do not need to add dictionary name
     * to translation path - it will be added automatically using CmfConfig::getPrimary()->custom_dictionary_name().
     * Resulting path will be: 'admin.api_docs.method.some_name.title' if dictionary name is 'admin'
     * @var string
     */
    protected $description = <<<HTML

HTML;

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
            [
                'code' => HttpCode::NOT_FOUND,
                'title' => 'Not found',
                'response' => [
                    'message' => 'item_not_found'
                ]
            ]
            or if you want localized API docs:
            [
                'code' => HttpCode::NOT_FOUND,
                'title' => '{error.item_not_found}',
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
            $additionalErrors[] = array_merge(static::$dataValidationError, ['response' => $this->validationErrors]);
        }
        $errors = array_merge($this->getCommonErrors(), $additionalErrors, $this->getPossibleErrors());
        // translate titles
        foreach ($errors as &$error) {
            $error['title'] = $this->translate($error['title']);
        }
        return $errors;
    }

    protected $uuid;

    static protected $authFailError = [
        'code' => HttpCode::UNAUTHORISED,
        'title' => '{error.auth_failure}',
        'response' => [
            'message' => 'Unauthenticated.'
        ]
    ];

    static protected $accessDeniedError = [
        'code' => HttpCode::FORBIDDEN,
        'title' => '{error.access_denied}',
        'response' => [
            'message' => 'Unauthorized.'
        ]
    ];

    static protected $dataValidationError = [
        'code' => HttpCode::CANNOT_PROCESS,
        'title' => '{error.validation_errors}',
        'response' => [
            'message' => 'The given data was invalid.',
        ]
    ];

    static protected $serverError = [
        'code' => HttpCode::SERVER_ERROR,
        'title' => '{error.server_error}',
        'response' => [
            'message' => 'Server error.',
        ]
    ];

    static public function create() {
        return new static();
    }

    public function __construct() {
        $this->uuid = Uuid::uuid4()->toString();
    }

    static public function getPosition() {
        return static::$position;
    }

    public function getTitle() {
        return $this->translate($this->title);
    }

    public function getDescription() {
        return $this->translate($this->description);
    }

    public function hasDescription() {
        return trim(preg_replace('%</?[^>]+>%', '', $this->description)) !== '';
    }

    public function getUuid() {
        return $this->uuid;
    }

    public function getUrl() {
        return trim((string)$this->url);
    }

    public function getHttpMethod() {
        return $this->httpMethod;
    }

    public function getHeaders() {
        return $this->headers;
    }

    public function getUrlParameters() {
        return $this->translateArrayValues($this->urlParameters);
    }

    public function getUrlQueryParameters() {
        return $this->translateArrayValues($this->urlQueryParameters);
    }

    public function getPostParameters() {
        return $this->translateArrayValues($this->postParameters);
    }

    public function getValidationErrors() {
        return $this->translateArrayValues($this->validationErrors);
    }

    public function getOnSuccessData() {
        return $this->translateArrayValues($this->onSuccess);
    }

    /**
     * Translate blocks like "{method.name.title}" placed inside the $string
     * @param string $string
     * @return string
     */
    protected function translate($string) {
        return preg_replace_callback(
            '%\{([^{}]*)\}%',
            function ($matches) {
                return CmfConfig::transApiDoc($matches[1]);
            },
            $string
        );
    }

    /**
     * Translate values of the $array recursively
     * @param array $array
     * @return array
     */
    protected function translateArrayValues(array $array) {
        foreach ($array as &$value) {
            if (is_string($value)) {
                $value = $this->translate($value);
            } else if (is_array($value)) {
                $value = $this->translateArrayValues($value);
            }
        }
        return $array;
    }

    public function getConfigForPostman() {
        $queryParams = [];
        foreach ($this->getUrlQueryParameters() as $name => $info) {
            if ($name === '_method') {
                $queryParams[] = urlencode($name) . '=' . $info;
            } else {
                $queryParams[] = urlencode($name) . '={{' . $name . '}}';
            }
        }
        $queryParams = empty($queryParams) ? '' : '?' . implode('&', $queryParams);
        $url = $this->getUrl();
        $item = [
            'name' => $url,
            'request' => [
                'url' => url(
                    preg_replace('%\{([^/]+?)\}%', ':$1', $url) . $queryParams
                ),
                'method' => strtoupper($this->getHttpMethod()),
                'description' => preg_replace(
                    ['% +%', "%\n\s+%s"],
                    [' ', "\n"],
                    trim(strip_tags(
                        preg_replace(
                            ["%\n+%m", '%</(p|div|li|ul)>|<br>%'],
                            [' ', "\n"],
                            $this->getTitle() . "\n" . $this->getDescription()
                        )
                    ))
                ),
                'header' => [],
                'body' => [
                    'mode' => 'formdata',
                    'formdata' => [
                    ]
                ],

            ],
            'response' => []
        ];
        foreach ($this->getHeaders() as $key => $value) {
            $item['request']['header'][] = [
                'key' => $key,
                'value' => $value,
                'description' => ''
            ];
        }
        foreach ($this->getPostParameters() as $key => $value) {
            $item['request']['body']['formdata'][] = [
                'key' => $key,
                'value' => ($key === '_method') ? $value : '{{' . $key . '}}',
                'type' => 'text',
                'enabled' => true
            ];
        }
        return $item;
    }

}
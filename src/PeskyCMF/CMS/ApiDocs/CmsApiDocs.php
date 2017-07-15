<?php

namespace PeskyCMF\CMS\ApiDocs;

use PeskyCMF\HttpCode;
use Ramsey\Uuid\Uuid;

abstract class CmsApiDocs {

    // override next properties and methods

    public $title = '';
    public $description = <<<HTML

HTML;

    /**
     * You can use '{url_parameter}' or ':url_parameter' to insert parameters into url and be able to
     * export it to postman properly (postman uses ':url_parameter' format but it is not expressive
     * enough unlike '{url_parameter}' variant)
     * @var string
     */
    public $url = '/api/example/{url_parameter}/list';
    public $httpMethod = 'GET';

    public $headers = [
        'Accept' => 'application/json',
        'Authorization' => 'Bearer {{token}}'
    ];
    /**
     * List of parameters used inside URL
     * For url: '/api/items/{id}/list' 'id' is url parameter (brackets needed only to highlight url parameter)
     * @var array
     */
    public $urlParameters = [
//        'url_parameter' => 'int'
    ];
    public $urlQueryParameters = [
//        '_method' => 'PUT',
//        'token' => 'string',
    ];
    public $postParameters = [
//        'id' => 'int',
    ];
    public $validationErrors = [
//        'token' => ['required', 'string'],
//        'id' => ['required', 'integer', 'min:1']
    ];

    public $onSuccess = [
//        'name' => 'string',
    ];

    /**
     * @return array
     */
    public function getPossibleErrors() {
        /* Example:
            [
                'code' => HttpCode::NOT_FOUND,
                'title' => 'Not found',
                'response' => [
                    'error' => 'item_not_found'
                ]
            ],
        */
        return [];
    }

    // service properties and methods

    /**
     * @return array
     */
    public function getCommonErrors() {
        $errors = [
            static::$authFailError,
            static::$accessDeniedError,
            static::$serverError,
        ];
        if (count($this->validationErrors)) {
            $errors[] = array_merge(static::$dataValidationError, [
                'response' => $this->validationErrors
            ]);
        }
        return $errors;
    }

    protected $uuid;

    static protected $authFailError = [
        'code' => HttpCode::UNAUTHORISED,
        'title' => 'Не удалось авторизовать пользователя',
        'response' => [
            'error' => 'Unauthenticated.'
        ]
    ];

    static protected $accessDeniedError = [
        'code' => HttpCode::FORBIDDEN,
        'title' => 'Доступ запрещен',
        'response' => []
    ];

    static protected $dataValidationError = [
        'code' => HttpCode::CANNOT_PROCESS,
        'title' => 'Ошибки валидации данных',
        'response' => []
    ];

    static protected $serverError = [
        'code' => HttpCode::SERVER_ERROR,
        'title' => 'Критическая ошибка на стороне сервера',
        'response' => []
    ];

    static public function create() {
        return new static();
    }

    public function __construct() {
        $this->uuid = Uuid::uuid4()->toString();
        // load data from class methods
        foreach (['headers', 'onSuccess', 'validationErrors', 'postParameters', 'urlQueryParameters', 'urlParameters'] as $field) {
            if (method_exists($this, $field)) {
                $this->$field = $this->$field();
                if (!is_array($this->$field)) {
                    throw new \UnexpectedValueException(get_class($this) . '->' . $field . '() method must return an array');
                }
            }
        }
    }

    public function getUuid() {
        return $this->uuid;
    }

    public function getConfigForPostman() {
        $queryParams = [];
        foreach ($this->urlQueryParameters as $name => $info) {
            if ($name === '_method') {
                $queryParams[] = urlencode($name) . '=' . $info;
            } else {
                $queryParams[] = urlencode($name) . '={{' . $name . '}}';
            }
        }
        $queryParams = empty($queryParams) ? '' : '?' . implode('&', $queryParams);
        $item = [
            'name' => $this->url,
            'request' => [
                'url' => url(
                    preg_replace('%\{([^/]+?)\}%', ':$1', $this->url) . $queryParams
                ),
                'method' => strtoupper($this->httpMethod),
                'description' => preg_replace(
                    ['% +%', "%\n\s+%s"],
                    [' ', "\n"],
                    trim(strip_tags(
                        preg_replace(["%\n+%m", '%</(p|div|li|ul)>|<br>%'], [' ', "\n"], $this->description)
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
        foreach ($this->headers as $key => $value) {
            $item['request']['header'][] = [
                'key' => $key,
                'value' => $value,
                'description' => ''
            ];
        }
        foreach ($this->postParameters as $key => $value) {
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
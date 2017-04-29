<?php

namespace PeskyCMF\CMS\ApiDocs;

use PeskyCMF\HttpCode;
use Ramsey\Uuid\Uuid;

abstract class CmsApiDocs {

    // override next properties and methods

    public $title = '';
    public $description = <<<HTML

HTML;

    public $url = '/api/';
    public $httpMethod = 'GET';

    public $headers = [
        'Accept' => 'application/json',
        'Authorisation' => 'Bearer {token}'
    ];
    public $urlQueryParams = [
        '_method' => 'PUT',
        'token' => 'string',
    ];
    public $postParams = [
        'id' => 'int',
    ];
    public $validationErrors = [
        'token' => ['required', 'string'],
        'id' => ['required', 'integer', 'min:1']
    ];

    public $onSuccess = [
        'name' => 'string',
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
    }

    public function getUuid() {
        return $this->uuid;
    }

}
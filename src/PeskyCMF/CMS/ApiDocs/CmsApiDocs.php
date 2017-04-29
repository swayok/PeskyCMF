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

    public $urlQueryParams = [
        '_method' => 'PUT',
        'token' => 'string',
    ];
    public $postParams = [
        'id' => 'int',
    ];
    public $validators = [
        'token' => 'required|string',
        'id' => 'required|integer|min:1,'
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
                    '_clarification' => 'item_not_found'
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
        ];
        if (count($this->validators)) {
            $errors[] = array_merge(static::$dataValidationError, [
                'response' => $this->validators
            ]);
        }
        return $errors;
    }

    protected $uuid;

    static protected $authFailError = [
        'code' => HttpCode::UNAUTHORISED,
        'title' => 'Не удалось авторизовать пользователя',
        'response' => []
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
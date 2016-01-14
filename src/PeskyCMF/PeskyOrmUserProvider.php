<?php

namespace PeskyCMF;

use Hash;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Contracts\Auth\UserProvider;
use PeskyORM\DbObject;
use PeskyORM\Exception\DbUtilsException;

class PeskyOrmUserProvider implements UserProvider {

    /**
     * The PeskyORM user object (DbObject).
     *
     * @var string
     */
    protected $model;

    /**
     * Create a new database user provider.
     *
     * @param  string $dbObjectName
     * @throws DbUtilsException
     */
    public function __construct($dbObjectName) {
        if (empty($dbObjectName)) {
            throw new DbUtilsException('PeskyOrmUserProvider received empty class name of DbObject');
        }
        $this->model = $dbObjectName;
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier) {
        if (empty($identifier) || intval($identifier) <= 0) {
            return null;
        }
        /** @var DbObject $user */
        $user = $this->createDbObject()->read($identifier);
        return $this->validateUser($user, null);
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param  mixed $identifier
     * @param  string $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token) {
        /** @var DbObject|Authenticatable $dbObject */
        $dbObject = $this->createDbObject();
        /** @var DbObject $user */
        $user = $dbObject->find([
            $dbObject->_getPkFieldName() => $identifier,
            $dbObject->getRememberTokenName() => $token
        ]);
        return $this->validateUser($user, null);
    }

    /**
     * @param DbObject $user
     * @param mixed $onFailReturn
     * @return mixed|DbObject
     */
    protected function validateUser(DbObject $user, $onFailReturn = null) {
        return $user->exists() && (!$user->_hasField('is_active') || $user->is_active) ? $user : $onFailReturn;
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  string $token
     * @return void
     */
    public function updateRememberToken(UserContract $user, $token) {
        $user->setRememberToken($token);
        /** @var DbObject $user */
        $user->save();
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials) {

        $conditions = array();

        foreach ($credentials as $key => $value) {
            if (!str_contains($key, 'password')) {
                $conditions[$key] = $value;
            }
        }
        $user = $this->createDbObject()->find($conditions);

        return $this->validateUser($user, null);
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  array $credentials
     * @return bool
     */
    public function validateCredentials(UserContract $user, array $credentials) {
        $plain = $credentials['password'];
        return Hash::check($plain, $user->getAuthPassword());
    }

    /**
     * Create a new instance of the model.
     *
     * @return DbObject
     */
    public function createDbObject() {
        $class = '\\' . ltrim($this->model, '\\');

        return $class::create();
    }
}
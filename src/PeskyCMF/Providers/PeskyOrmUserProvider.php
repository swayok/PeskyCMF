<?php

namespace PeskyCMF\Providers;

use Hash;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Contracts\Auth\UserProvider;
use PeskyORM\ORM\RecordInterface;

class PeskyOrmUserProvider implements UserProvider {

    /**
     * The PeskyORM user object (DbObject).
     *
     * @var string
     */
    protected $dbRecordClass;

    /**
     * Create a new database user provider.
     *
     * @param  string $dbRecordClass
     * @throws \InvalidArgumentException
     */
    public function __construct($dbRecordClass) {
        if (empty($dbRecordClass)) {
            throw new \InvalidArgumentException('PeskyOrmUserProvider received empty class name of DbObject');
        }
        $this->dbRecordClass = $dbRecordClass;
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier) {
        if (empty($identifier) || (int)$identifier <= 0) {
            return null;
        }
        /** @var RecordInterface $user */
        $user = $this->createDbObject()->fromPrimaryKey($identifier);
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
        /** @var RecordInterface|Authenticatable $dbObject */
        $dbObject = $this->createDbObject();
        /** @var RecordInterface $user */
        $user = $dbObject->fromDb([
            $dbObject->getAuthIdentifierName() => $identifier,
            $dbObject->getRememberTokenName() => $token
        ]);
        return $this->validateUser($user, null);
    }

    /**
     * @param RecordInterface $user
     * @param mixed $onFailReturn
     * @return mixed|RecordInterface
     */
    protected function validateUser(RecordInterface $user, $onFailReturn = null) {
        if (
            $user->existsInDb()
            && (!$user::hasColumn('is_active') || $user->getValue('is_active'))
            && (!$user::hasColumn('is_banned') || !$user->getValue('is_banned'))
        ) {
            return $user;
        }
        return $onFailReturn;
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  string $token
     * @return void
     * @throws \BadMethodCallException
     */
    public function updateRememberToken(UserContract $user, $token) {
        $user->setRememberToken($token);
        /** @var RecordInterface $user */
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
        $user = $this->createDbObject()->fromDb($conditions);

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
        foreach ($credentials as $columnName => $value) {
            if (is_string($columnName) && !is_numeric($columnName)) {
                if ($columnName === 'password') {
                    if (!Hash::check($value, $user->getAuthPassword())) {
                        return false;
                    }
                } else if ($user->$columnName !== $value) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Create a new instance of the db record.
     *
     * @return RecordInterface
     */
    public function createDbObject() {
        /** @var RecordInterface $class */
        $class = $this->dbRecordClass;
        return new $class();
    }

    /**
     * Needed for JWTGuard
     *
     * @return string
     */
    public function getModel() {
        return $this->dbRecordClass;
    }
}

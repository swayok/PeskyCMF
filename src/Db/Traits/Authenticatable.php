<?php

namespace PeskyCMF\Db\Traits;

use PeskyORM\DbObject;

trait Authenticatable {
    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier() {
        return $this->getKey();
    }

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName() {
        return $this->getKeyName();
    }

    /**
     * Needed to fit eloquent ORM
     * @return int|string
     */
    public function getKey() {
        /** @var DbObject|Authenticatable $this */
        return $this->_getPkValue();
    }

    /**
     * Needed to fit eloquent ORM
     * @return string
     */
    public function getKeyName() {
        /** @var DbObject|Authenticatable $this */
        return $this->_getPkFieldName();
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword() {
        return $this->password;
    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string
     */
    public function getRememberToken() {
        /** @var DbObject|Authenticatable $this */
        return $this->_getFieldValue($this->getRememberTokenName());
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param  string $value
     * @return $this
     */
    public function setRememberToken($value) {
        /** @var $this DbObject|Authenticatable */
        return $this->_setFieldValue($this->getRememberTokenName(), $value);
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName() {
        return 'remember_token';
    }
}

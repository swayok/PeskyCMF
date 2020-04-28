<?php

namespace PeskyCMF\Db\Traits;

use PeskyCMF\Db\CmfDbObject;

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
        /** @var CmfDbObject|Authenticatable $this */
        return $this->getPrimaryKeyValue();
    }

    /**
     * Needed to fit eloquent ORM
     * @return string
     */
    public function getKeyName() {
        /** @var CmfDbObject|Authenticatable $this */
        return static::getPrimaryKeyColumnName();
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
        /** @var CmfDbObject|Authenticatable $this */
        return $this->getValue($this->getRememberTokenName());
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param  string $value
     * @return $this
     */
    public function setRememberToken($value) {
        /** @var $this CmfDbObject|Authenticatable */
        $this->updateValue($this->getRememberTokenName(), $value, false);
        return $this;
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

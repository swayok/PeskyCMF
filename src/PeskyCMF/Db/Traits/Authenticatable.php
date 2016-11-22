<?php

namespace PeskyCMF\Db\Traits;

use PeskyORM\ORM\RecordInterface;

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
        /** @var RecordInterface|Authenticatable $this */
        return $this->getPrimaryKeyValue();
    }

    /**
     * Needed to fit eloquent ORM
     * @return string
     */
    public function getKeyName() {
        /** @var RecordInterface|Authenticatable $this */
        return $this::getTable()->getPkColumnName();
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword() {
        /** @var RecordInterface|Authenticatable $this */
        return $this->getValue('password');
    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string
     */
    public function getRememberToken() {
        /** @var RecordInterface|Authenticatable $this */
        return $this->getValue($this->getRememberTokenName());
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param  string $value
     * @return $this
     */
    public function setRememberToken($value) {
        /** @var RecordInterface|Authenticatable $this */
        return $this->updateValue($this->getRememberTokenName(), $value, false);
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

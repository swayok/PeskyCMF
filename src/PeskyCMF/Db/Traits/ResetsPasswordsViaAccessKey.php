<?php

namespace PeskyCMF\Db\Traits;

use PeskyCMF\Db\CmfDbObject;

trait ResetsPasswordsViaAccessKey {

    /**
     * @return string
     * @throws \PeskyORM\Exception\DbObjectException
     */
    public function getPasswordRecoveryAccessKey() {
        /** @var CmfDbObject|ResetsPasswordsViaAccessKey $this */
        $data = [
            'account_id' => $this->_getPkValue(),
            'expires_at' => time() + config('auth.passwords.' . \Auth::getDefaultDriver() . 'expire', 60) * 60,
        ];
        foreach ($this->getAdditionalFieldsForPasswordRecoveryAccessKey() as $fieldName) {
            $data[$fieldName] = $this->_getFieldValue($fieldName);
        }
        return \Crypt::encrypt(json_encode($data));
    }

    public function getAdditionalFieldsForPasswordRecoveryAccessKey() {
        /** @var CmfDbObject|ResetsPasswordsViaAccessKey $this */
        $fields = [];
        if ($this->_hasField('updated_at')) {
            $fields[] = 'updated_at';
        } else if ($this->_hasField('password')) {
            $fields[] = 'password';
        }
        return $fields;
    }

    /**
     * Vlidate access key and find user
     * @param string $accessKey
     * @return CmfDbObject|bool - false = failed to parse access key, validate data or load user
     */
    static public function loadFromPasswordRecoveryAccessKey($accessKey) {
        $data = \Crypt::decrypt($accessKey);
        if (empty($data)) {
            return false;
        }
        $data = json_decode($data, true);
        if (
            empty($data)
            || !is_array($data)
            || empty($data['account_id'])
            || empty($data['expires_at'])
            || $data['expires_at'] < time()
        ) {
            return false;
        }
        /** @var CmfDbObject|ResetsPasswordsViaAccessKey $user */
        $user = static::create();
        $conditions = [
            $user->_getPkFieldName() => $data['account_id'],
        ];
        foreach ($user->getAdditionalFieldsForPasswordRecoveryAccessKey() as $fieldName) {
            if (empty($data[$fieldName])) {
                return false;
            }
            $conditions[$fieldName] = $data[$fieldName];
        }
        if (!$user->find($conditions)->exists()) {
            return false;
        }
        return $user;
    }
}

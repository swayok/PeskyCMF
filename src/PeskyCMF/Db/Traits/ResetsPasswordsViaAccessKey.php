<?php

namespace PeskyCMF\Db\Traits;

use Illuminate\Contracts\Encryption\DecryptException;
use PeskyCMF\Db\CmfDbObject;
use PeskyORM\DbColumnConfig;
use PeskyORM\DbExpr;

trait ResetsPasswordsViaAccessKey {

    /**
     * @return string
     */
    public function getPasswordRecoveryAccessKey() {
        /** @var CmfDbObject|ResetsPasswordsViaAccessKey $this */
        $data = [
            'account_id' => $this->_getPkValue(),
            'expires_at' => time() + config('auth.passwords.' . \Auth::getDefaultDriver() . 'expire', 60) * 60,
        ];
        $this->reload(); //< needed to exclude situation with outdated data
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
        try {
            $data = \Crypt::decrypt($accessKey);
        } catch (DecryptException $exc) {
            return false;
        }
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
            $fieldType = $user->_getField($fieldName)->getType();
            switch ($fieldType) {
                case DbColumnConfig::TYPE_DATE:
                    $conditions[$fieldName . '::date'] = DbExpr::create("``$data[$fieldName]``::date");
                    break;
                case DbColumnConfig::TYPE_TIME:
                    $conditions[$fieldName . '::time'] = DbExpr::create("``$data[$fieldName]``::time");
                    break;
                case DbColumnConfig::TYPE_TIMESTAMP:
                    $conditions[] = DbExpr::create("`{$fieldName}`::timestamp(0) = ``{$data[$fieldName]}``::timestamp(0)");
                    break;
                default:
                    $conditions[$fieldName] = $data[$fieldName];
            }
        }
        if (!$user->find($conditions)->exists()) {
            return false;
        }
        return $user;
    }
}

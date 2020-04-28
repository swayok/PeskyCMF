<?php

namespace PeskyCMF\Db\Traits;

use Illuminate\Contracts\Encryption\DecryptException;
use PeskyCMF\Db\CmfDbObject;
use PeskyORM\Core\DbExpr;
use PeskyORM\ORM\Column;

trait ResetsPasswordsViaAccessKey {

    /**
     * @return string
     */
    public function getPasswordRecoveryAccessKey() {
        /** @var CmfDbObject|ResetsPasswordsViaAccessKey $this */
        $data = [
            'account_id' => $this->getPrimaryKeyValue(),
            'expires_at' => time() + config('auth.passwords.' . \Auth::getDefaultDriver() . 'expire', 60) * 60,
        ];
        $this->reload(); //< needed to exclude situation with outdated data
        foreach ($this->getAdditionalFieldsForPasswordRecoveryAccessKey() as $fieldName) {
            $data[$fieldName] = $this->getValue($fieldName);
        }
        return \Crypt::encrypt(json_encode($data));
    }

    public function getAdditionalFieldsForPasswordRecoveryAccessKey() {
        /** @var CmfDbObject|ResetsPasswordsViaAccessKey $this */
        $fields = [];
        if ($this::hasColumn('updated_at')) {
            $fields[] = 'updated_at';
        } else if ($this::hasColumn('password')) {
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
            $user::getPrimaryKeyColumnName() => $data['account_id'],
        ];
        foreach ($user->getAdditionalFieldsForPasswordRecoveryAccessKey() as $fieldName) {
            if (empty($data[$fieldName])) {
                return false;
            }
            $fieldType = $user::getColumn($fieldName)->getType();
            switch ($fieldType) {
                case Column::TYPE_DATE:
                    $conditions[$fieldName . '::date'] = DbExpr::create("``$data[$fieldName]``::date");
                    break;
                case Column::TYPE_TIME:
                    $conditions[$fieldName . '::time'] = DbExpr::create("``$data[$fieldName]``::time");
                    break;
                case Column::TYPE_TIMESTAMP:
                    $conditions[] = DbExpr::create("`{$fieldName}`::timestamp(0) = ``{$data[$fieldName]}``::timestamp(0)");
                    break;
                default:
                    $conditions[$fieldName] = $data[$fieldName];
            }
        }
        if (!$user->fromDb($conditions)->existsInDb()) {
            return false;
        }
        return $user;
    }
}

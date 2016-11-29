<?php

namespace PeskyCMF\Db\Traits;

use Illuminate\Contracts\Encryption\DecryptException;
use PeskyCMF\Db\CmfDbRecord;
use PeskyORM\Core\DbExpr;
use PeskyORM\ORM\Column;

trait ResetsPasswordsViaAccessKey {

    /**
     * @return string
     * @throws \PeskyORM\Exception\RecordNotFoundException
     * @throws \PeskyORM\Exception\InvalidDataException
     * @throws \PDOException
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws \Illuminate\Contracts\Encryption\EncryptException
     */
    public function getPasswordRecoveryAccessKey() {
        /** @var CmfDbRecord|ResetsPasswordsViaAccessKey $this */
        $data = [
            'account_id' => $this->getPrimaryKeyValue(),
            'expires_at' => time() + config('auth.passwords.' . \Auth::getDefaultDriver() . 'expire', 60) * 60,
        ];
        $this->reload(); //< needed to exclude situation with outdated data
        foreach ($this->getAdditionalColumnsForPasswordRecoveryAccessKey() as $columnName) {
            $data[$columnName] = $this->getValue($columnName);
        }
        return \Crypt::encrypt(json_encode($data));
    }

    public function getAdditionalColumnsForPasswordRecoveryAccessKey() {
        /** @var CmfDbRecord|ResetsPasswordsViaAccessKey $this */
        $columns = [];
        if ($this::hasColumn('updated_at')) {
            $columns[] = 'updated_at';
        } else if ($this::hasColumn('password')) {
            $columns[] = 'password';
        }
        return $columns;
    }

    /**
     * Vlidate access key and find user
     * @param string $accessKey
     * @return CmfDbRecord|bool - false = failed to parse access key, validate data or load user
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \PeskyORM\Exception\InvalidDataException
     * @throws \PDOException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
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
        /** @var ResetsPasswordsViaAccessKey|CmfDbRecord $user */
        $user = static::newEmptyRecord();
        $conditions = [
            $user->getPrimaryKeyColumnName() => $data['account_id'],
        ];
        foreach ($user->getAdditionalColumnsForPasswordRecoveryAccessKey() as $columnName) {
            if (empty($data[$columnName])) {
                return false;
            }
            $fieldType = $user::getColumn($columnName)->getType();
            switch ($fieldType) {
                case Column::TYPE_DATE:
                    $conditions[$columnName . '::date'] = DbExpr::create("``$data[$columnName]``::date");
                    break;
                case Column::TYPE_TIME:
                    $conditions[$columnName . '::time'] = DbExpr::create("``$data[$columnName]``::time");
                    break;
                case Column::TYPE_TIMESTAMP:
                    $conditions[] = DbExpr::create("`{$columnName}`::timestamp(0) = ``{$data[$columnName]}``::timestamp(0)");
                    break;
                default:
                    $conditions[$columnName] = $data[$columnName];
            }
        }
        if (!$user->fromDb($conditions)->existsInDb()) {
            return false;
        }
        return $user;
    }
}

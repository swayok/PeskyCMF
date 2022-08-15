<?php

namespace PeskyCMF\Db\Traits;

use Illuminate\Contracts\Encryption\DecryptException;
use PeskyCMF\Db\CmfDbRecord;
use PeskyORM\Core\DbExpr;
use PeskyORM\ORM\Column;

trait ResetsPasswordsViaAccessKey {

    /**
     * @param int|null $expiresIn - minutes until expiration. Default: config('auth.passwords.' . \Auth::getDefaultDriver() . '.expire', 60)
     * @param array|null $additionalColumns - additional columns to encode. Default: $this->getAdditionalColumnsForPasswordRecoveryAccessKey()
     * @return string
     */
    public function getPasswordRecoveryAccessKey(?int $expiresIn = null, ?array $additionalColumns = null): string {
        /** @var CmfDbRecord|ResetsPasswordsViaAccessKey $this */
        $expiresInMinutes = $expiresIn > 0 ? $expiresIn : (int)config('auth.passwords.' . \Auth::getDefaultDriver() . '.expire', 60);
        $data = [
            'account_id' => $this->getPrimaryKeyValue(),
            'expires_at' => (new \DateTime('now', new \DateTimeZone('UTC')))->getTimestamp() + $expiresInMinutes * 60,
        ];
        $this->reload(); //< needed to exclude situation with outdated data
        if ($additionalColumns === null) {
            $additionalColumns = $this->getAdditionalColumnsForPasswordRecoveryAccessKey();
        }
        $data['added_keys'] = $additionalColumns;
        foreach ($additionalColumns as $columnName) {
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
     * Validate access key and find user
     * @param string $accessKey
     * @param null|array $requiredAdditionalColumns - list of $additionalColumns (see getPasswordRecoveryAccessKey) which must be present in $accessKey
     * @return CmfDbRecord|null - null = failed to parse access key, validate data or load user
     */
    public static function loadFromPasswordRecoveryAccessKey(string $accessKey, ?array $requiredAdditionalColumns = null) {
        try {
            $data = \Crypt::decrypt($accessKey);
        } catch (DecryptException $exc) {
            return null;
        }
        if (empty($data)) {
            return null;
        }
        $data = json_decode($data, true);
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        if (
            empty($data)
            || !is_array($data)
            || !isset($data['added_keys'])
            || !is_array($data['added_keys'])
            || empty($data['account_id'])
            || empty($data['expires_at'])
            || $data['expires_at'] < $now->getTimestamp()
        ) {
            return null;
        }
        /** @var ResetsPasswordsViaAccessKey|CmfDbRecord $user */
        $user = static::newEmptyRecord();
        $conditions = [
            $user::getPrimaryKeyColumnName() => $data['account_id'],
        ];
        $additionalColumns = $data['added_keys'];
        if ($requiredAdditionalColumns && empty(array_intersect($requiredAdditionalColumns, $additionalColumns))) {
            // there are not enough required additional columns in this key
            return null;
        }
        foreach ($additionalColumns as $columnName) {
            if (!array_key_exists($columnName, $data)) {
                return null;
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
        if (!$user->fetch($conditions)->existsInDb()) {
            return null;
        }
        return $user;
    }
}

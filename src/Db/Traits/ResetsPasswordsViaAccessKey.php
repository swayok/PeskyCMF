<?php

declare(strict_types=1);

namespace PeskyCMF\Db\Traits;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use PeskyORM\DbExpr;
use PeskyORM\ORM\Record\RecordInterface;
use PeskyORM\ORM\TableStructure\TableColumn\TableColumnDataType;

trait ResetsPasswordsViaAccessKey
{
    /**
     * {@inheritDoc}
     * If $additionalColumns is null then $this->getAdditionalColumnsForPasswordRecoveryAccessKey() will be used
     */
    public function getPasswordRecoveryAccessKey(?int $expiresIn = null, ?array $additionalColumns = null): string
    {
        $expiresInMinutes = $expiresIn > 0 ? $expiresIn : (int)config(
            'auth.passwords.' . app('auth')->getDefaultDriver() . '.expire',
            60
        );
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
        return Crypt::encrypt(json_encode($data));
    }

    public function getAdditionalColumnsForPasswordRecoveryAccessKey(): array
    {
        $columns = [];
        if ($this->hasColumn('updated_at')) {
            $columns[] = 'updated_at';
        } elseif ($this->hasColumn('password')) {
            $columns[] = 'password';
        }
        return $columns;
    }

    public static function loadFromPasswordRecoveryAccessKey(
        string $accessKey,
        ?array $requiredAdditionalColumns = null
    ): ?static {
        try {
            $data = Crypt::decrypt($accessKey);
        } catch (DecryptException) {
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
        /** @var static|RecordInterface $user */
        $user = new static();
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
            $fieldType = $user->getTableStructure()->getColumn($columnName)->getDataType();
            switch ($fieldType) {
                case TableColumnDataType::DATE:
                    $conditions[$columnName . '::date'] = DbExpr::create("``$data[$columnName]``::date");
                    break;
                case TableColumnDataType::TIME:
                    $conditions[$columnName . '::time'] = DbExpr::create("``$data[$columnName]``::time");
                    break;
                case TableColumnDataType::TIMESTAMP:
                    $conditions[] = DbExpr::create(
                        "`{$columnName}`::timestamp(0) = ``{$data[$columnName]}``::timestamp(0)"
                    );
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

<?php

declare(strict_types=1);

namespace PeskyCMF\Db\Settings;

use PeskyCMF\Db\Admins\CmfAdmin;
use PeskyCMF\Db\CmfDbRecord;
use PeskyORM\ORM\TableInterface;
use PeskyORMLaravel\Db\LaravelKeyValueTableHelpers\LaravelKeyValueRecordHelpers;

/**
 * @property-read int $id
 * @property-read string $key
 * @property-read string $value
 * @property-read CmfAdmin $Admin
 *
 * @method $this    setId($value, $isFromDb = false)
 * @method $this    setKey($value, $isFromDb = false)
 * @method $this    setValue($value, $isFromDb = false)
 */
class CmfSetting extends CmfDbRecord
{
    
    use LaravelKeyValueRecordHelpers;
    
    /** @var CmfSettingsTable */
    private static TableInterface $table;
    
    /**
     * @return CmfSettingsTable
     */
    public static function getTable(): TableInterface
    {
        if (static::$table === null) {
            static::$table = app()->bound(CmfSettingsTable::class)
                ? app(CmfSettingsTable::class)
                : CmfSettingsTable::getInstance();
        }
        return static::$table;
    }
    
}
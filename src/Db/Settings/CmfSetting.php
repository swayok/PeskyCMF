<?php

declare(strict_types=1);

namespace PeskyCMF\Db\Settings;

use PeskyCMF\Db\Admins\CmfAdmin;
use PeskyCMF\Db\CmfDbRecord;
use PeskyORMLaravel\Db\LaravelKeyValueTableHelpers\LaravelKeyValueRecordHelpers;
use PeskyORMLaravel\Db\LaravelKeyValueTableHelpers\LaravelKeyValueTableInterface;

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
    
    /**
     * @return CmfSettingsTable
     */
    public static function getTable(): LaravelKeyValueTableInterface
    {
        return app(CmfSettingsTable::class);
    }
    
}
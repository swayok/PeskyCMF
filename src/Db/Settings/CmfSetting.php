<?php

declare(strict_types=1);

namespace PeskyCMF\Db\Settings;

use PeskyCMF\Db\Admins\CmfAdmin;
use PeskyORM\ORM\Record\Record;
use PeskyORM\ORM\Table\TableInterface;

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
class CmfSetting extends Record
{
    /**
     * @return CmfSettingsTable
     */
    public function getTable(): TableInterface
    {
        return app(CmfSettingsTable::class);
    }
}

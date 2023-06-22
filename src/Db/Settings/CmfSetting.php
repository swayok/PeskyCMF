<?php

declare(strict_types=1);

namespace PeskyCMF\Db\Settings;

use PeskyORM\ORM\Record\Record;

/**
 * @property-read int $id
 * @property-read string $key
 * @property-read string $value
 *
 * @method $this    setId($value, $isFromDb = false)
 * @method $this    setKey($value, $isFromDb = false)
 * @method $this    setValue($value, $isFromDb = false)
 */
class CmfSetting extends Record
{
    public function __construct()
    {
        parent::__construct(CmfSettingsTable::getInstance());
    }
}

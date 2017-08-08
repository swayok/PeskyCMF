<?php

namespace PeskyCMF\CMS;

use PeskyCMF\CMS\Settings\CmsSettingsTable;
use PeskyCMF\Db\CmfDbRecord;
use PeskyCMF\Db\KeyValueTableInterface;

/**
 * @method static CmsTableStructure getTableStructure()
 */
abstract class CmsRecord extends CmfDbRecord {

    /**
     * Class name of the table
     * @var string
     */
    static protected $tableClass;

    static public function getSingletonInstanceOfDbClassFromServiceContainer($class) {
        return CmsTable::getSingletonInstanceOfDbClassFromServiceContainer($class);
    }

    /**
     * @return CmsSettingsTable|KeyValueTableInterface
     */
    static public function getTable() {
        if (empty(static::$tableClass)) {
            throw new \UnexpectedValueException('You need to provide ' . static::class . '::$tableClass property');
        }
        return static::getSingletonInstanceOfDbClassFromServiceContainer(static::$tableClass);
    }
}
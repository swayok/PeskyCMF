<?php
declare(strict_types=1);

namespace PeskyCMF\Db;

use PeskyORM\ORM\Column;
use PeskyORM\ORM\RecordInterface;
use PeskyORM\ORM\RecordsArray;
use PeskyORM\ORM\RecordsSet;
use PeskyORM\ORM\Relation;
use PeskyORM\ORM\TableInterface;

class TempRecord implements RecordInterface {
    
    protected array $data = [];
    protected bool $existsInDb = false;
    protected ?string $tableName = null;
    
    public static function newEmptyRecord(): TempRecord
    {
        return new static();
    }
    
    public static function newTempRecord(array $data, bool $existsInDb = false, ?string $tableName = null): TempRecord
    {
        return static::newEmptyRecord()
            ->fromData($data, $existsInDb)
            ->setTableName($tableName);
    }
    
    public static function getTable(): TableInterface {
        throw new \BadMethodCallException('Temp Record has not Table');
    }
    
    /**
     * @param string $name
     * @return bool
     */
    public static function hasColumn($name): bool
    {
        return false;
    }
    
    public static function getColumn(string $name, string &$format = null): Column
    {
        throw new \BadMethodCallException('TempRecord has no Columns');
    }
    
    public function setTableName(string $name): TempRecord
    {
        $this->tableName = $name;
        return $this;
    }
    
    public function getTableName(): ?string
    {
        return $this->tableName;
    }
    
    public function reset(): TempRecord
    {
        $this->data = [];
        return $this;
    }
    
    /**
     * @param string|Column $column
     * @param string|null $format
     * @return mixed
     */
    public function getValue($column, ?string $format = null)
    {
        return $this->data[is_object($column) ? $column->getName() : $column] ?? null;
    }
    
    /**
     * @param string|Column $column
     * @param bool $trueIfThereIsDefaultValue
     * @return bool
     */
    public function hasValue($column, bool $trueIfThereIsDefaultValue = false): bool
    {
        return array_key_exists(is_object($column) ? $column->getName() : $column, $this->data);
    }
    
    /**
     * @param string|Column $column
     * @param mixed $value
     * @param bool $isFromDb
     * @return $this
     */
    public function updateValue($column, $value, bool $isFromDb): TempRecord
    {
        $this->data[is_object($column) ? $column->getName() : $column] = $value;
        return $this;
    }
    
    /**
     * @return int|float|string|null
     */
    public function getPrimaryKeyValue()
    {
        return null;
    }
    
    public function hasPrimaryKeyValue(): bool
    {
        return false;
    }
    
    public function existsInDb(bool $useDbQuery = false): bool
    {
        return $this->existsInDb;
    }
    
    public function setExistsInDb(bool $exists): TempRecord
    {
        $this->existsInDb = $exists;
        return $this;
    }
    
    /**
     * @param string $relationName
     * @param bool $loadIfNotSet
     * @return RecordsSet|RecordsArray|RecordInterface
     */
    public function getRelatedRecord(string $relationName, bool $loadIfNotSet = false)
    {
        throw new \BadMethodCallException('TempRecord has no Relations');
    }
    
    public function readRelatedRecord(string $relationName): TempRecord
    {
        return $this;
    }
    
    public function isRelatedRecordAttached(string $relationName): bool
    {
        return false;
    }
    
    /**
     * @param string|Relation $relationName
     * @param array|RecordInterface|RecordsArray|RecordsSet $relatedRecord
     * @param bool|null $isFromDb
     * @param bool $haltOnUnknownColumnNames
     * @return static
     */
    public function updateRelatedRecord(
        $relationName,
        $relatedRecord,
        ?bool $isFromDb = null,
        bool $haltOnUnknownColumnNames = true
    ): TempRecord {
        return $this;
    }
    
    public function unsetRelatedRecord(string $relationName): TempRecord
    {
        return $this;
    }
    
    public function fromData(array $data, bool $isFromDb = false, bool $haltOnUnknownColumnNames = true): TempRecord
    {
        $this->data = $data;
        $this->setExistsInDb($isFromDb);
        return $this;
    }
    
    public function fromDbData(array $data): TempRecord
    {
        return $this->fromData($data, true);
    }
    
    /**
     * @param int|float|string $pkValue
     * @param array $columns
     * @param array $readRelatedRecords
     * @return TempRecord
     */
    public function fetchByPrimaryKey($pkValue, array $columns = [], array $readRelatedRecords = []): TempRecord
    {
        throw new \BadMethodCallException('Method cannot be used for this class (' . get_class($this) . ')');
    }
    
    public function fetch(array $conditionsAndOptions, array $columns = [], array $readRelatedRecords = []): TempRecord
    {
        throw new \BadMethodCallException('Method cannot be used for this class (' . get_class($this) . ')');
    }
    
    public function reload(array $columns = [], array $readRelatedRecords = []): TempRecord
    {
        throw new \BadMethodCallException('Method cannot be used for this class (' . get_class($this) . ')');
    }
    
    public function readColumns(array $columns = []): TempRecord
    {
        throw new \BadMethodCallException('Method cannot be used for this class (' . get_class($this) . ')');
    }
    
    public function updateValues(array $data, bool $isFromDb = false, bool $haltOnUnknownColumnNames = true): TempRecord
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }
    
    public function begin(): TempRecord
    {
        throw new \BadMethodCallException('Method cannot be used for this class (' . get_class($this) . ')');
    }
    
    public function rollback(): TempRecord
    {
        throw new \BadMethodCallException('Method cannot be used for this class (' . get_class($this) . ')');
    }
    
    public function commit(array $relationsToSave = [], bool $deleteNotListedRelatedRecords = false): TempRecord
    {
        throw new \BadMethodCallException('Method cannot be used for this class (' . get_class($this) . ')');
    }
    
    public function save(array $relationsToSave = [], bool $deleteNotListedRelatedRecords = false): TempRecord
    {
        throw new \BadMethodCallException('Method cannot be used for this class (' . get_class($this) . ')');
    }
    
    public function saveRelations(array $relationsToSave = [], bool $deleteNotListedRelatedRecords = false): void
    {
        throw new \BadMethodCallException('Method cannot be used for this class (' . get_class($this) . ')');
    }
    
    public function delete(bool $resetAllValuesAfterDelete = true, bool $deleteFiles = true): TempRecord
    {
        throw new \BadMethodCallException('Method cannot be used for this class (' . get_class($this) . ')');
    }
    
    public function toArray(
        array $columnsNames = [],
        array $relatedRecordsNames = [],
        bool $loadRelatedRecordsIfNotSet = false,
        bool $withFilesInfo = true
    ): array {
        if (
            empty($columnsNames)
            || (count($columnsNames) === 1 && $columnsNames[0] === '*')
            || in_array('*', $columnsNames, true)
        ) {
            return $this->data;
        }
        
        $ret = [];
        foreach ($columnsNames as $key) {
            $ret[$key] = $this->getValue($key);
        }
        return $ret;
    }
    
    public function toArrayWithoutFiles(
        array $columnsNames = [],
        array $relatedRecordsNames = [],
        bool $loadRelatedRecordsIfNotSet = false
    ): array {
        return $this->toArray($columnsNames, $relatedRecordsNames, $loadRelatedRecordsIfNotSet, false);
    }
    
    public function getDefaults(array $columns = [], bool $ignoreColumnsThatCannotBeSetManually = true, bool $nullifyDbExprValues = true): array
    {
        return [];
    }
    
    public function enableReadOnlyMode(): TempRecord
    {
        return $this;
    }
    
    public function disableReadOnlyMode(): TempRecord
    {
        return $this;
    }
    
    public function isReadOnly(): bool
    {
        return true;
    }
    
    public function enableTrustModeForDbData(): TempRecord
    {
        return $this;
    }
    
    public function disableTrustModeForDbData(): TempRecord
    {
        return $this;
    }
    
    public function isTrustDbDataMode(): bool
    {
        return true;
    }
    
    public static function getPrimaryKeyColumnName(): string
    {
        throw new \BadMethodCallException('Method cannot be used for this class (' . static::class . ')');
    }
    
    public static function hasPrimaryKeyColumn(): bool
    {
        return false;
    }
    
    public static function getPrimaryKeyColumn(): Column
    {
        throw new \BadMethodCallException('Method cannot be used for this class (' . static::class . ')');
    }
    
    public static function getRelations(): array
    {
        return [];
    }
    
    public static function hasRelation(string $name): bool
    {
        return false;
    }
    
    public static function getRelation(string $name): Relation
    {
        throw new \BadMethodCallException('Method cannot be used for this class (' . static::class . ')');
    }
    
    public function isCollectingUpdates(): bool
    {
        return false;
    }
}
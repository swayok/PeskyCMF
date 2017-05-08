<?php

namespace PeskyCMF\Scaffold\Form;

use Doctrine\Instantiator\Exception\UnexpectedValueException;
use PeskyORM\Core\DbExpr;
use PeskyORM\Exception\InvalidDataException;
use PeskyORM\ORM\RecordInterface;
use PeskyORM\ORM\Relation;

class HasManyRelatedRecordsFormInput extends FormInput {

    /**
     * @var array
     */
    protected $dbQueryConditionsForDefaultOptionsLoader = [];
    /**
     * @var null|string|DbExpr
     */
    protected $optionLabelColumnForDefaultOptionsLoader;

    /**
     * @return string
     */
    public function getType() {
        return static::TYPE_MULTISELECT;
    }

    public function getValidators() {
        return [
            $this->getName() => 'array',
        ];
    }

    public function hasOwnValueSavingMethod() {
        return true;
    }

    public function getValueSaver() {
        return function ($value, RecordInterface $record, $created = false) {
            if (empty($value)) {
                if (!$created) {
                    // delete all related records
                    $this->getRelation()->getForeignTable()->delete([
                        $this->getRelation()->getForeignColumnName() => $record->getValue($this->getRelation()->getLocalColumnName())
                    ]);
                }
                return;
            }
            if (!is_array($value)) {
                throw new InvalidDataException([
                    $this->getName() => 'Value must be an array'
                ]);
            }
            $newFkValues = array_values($value);
            array_walk($newFkValues, function ($value) {
                return (int)$value;
            });
            if (!$created) {
                $existingRelatedRecords = $this->getRelation()->getForeignTable()->selectAssoc(
                    $this->getRelation()->getForeignTable()->getPkColumnName(),
                    $this->getRelationColumn(),
                    [$this->getRelation()->getForeignColumnName() => $record->getValue($this->getRelation()->getLocalColumnName())]
                );
                $itemsToDelete = [];
                $fkValuesToIgnore = [];
                // filter existing to leave only new ones and find items to delete
                foreach ($existingRelatedRecords as $pkValue => $fkValue) {
                    if (in_array($fkValue, $newFkValues, true)) {
                        $fkValuesToIgnore[] = $fkValue;
                    } else {
                        $itemsToDelete[] = $fkValue;
                    }
                }
                $newFkValues = array_diff($newFkValues, $fkValuesToIgnore);
                if (!empty($itemsToDelete)) {
                    // delete unlinked records
                    $this->getRelation()->getForeignTable()->delete([
                        $this->getRelation()->getForeignTable()->getPkColumnName() => $itemsToDelete
                    ]);
                }
            }
            if (empty($newFkValues)) {
                return;
            }
            $recordConstantData = [
                $this->getRelation()->getForeignColumnName() => $record->getValue($this->getRelation()->getLocalColumnName())
            ];
            $foreignRecord = $this->getRelation()->getForeignTable()->newRecord();
            foreach ($newFkValues as $fkValue) {
                $foreignRecord
                    ->fromData(array_merge(
                        $recordConstantData,
                        [
                            $this->getRelationColumn() => $fkValue
                        ]
                    ))
                    ->save();
            }
        };
    }

    public function doDefaultValueConversionByType($value, $type, array $record) {
        if (!is_array($value)) {
            if (!is_string($value)) {
                throw new \InvalidArgumentException('$value argument must be a string or array');
            }
            $value = json_decode($value, true);
            if (!is_array($value)) {
                $value = [];
            }
        }
        return array_values($value);
    }

    public function hasOptionsLoader() {
        return true;
    }

    public function setOptions($options) {
        throw new \BadMethodCallException(
            "Plain options is forbidden for HasManyRelatedRecordsFormInput '{$this->getName()}'. Use options loader."
        );
    }

    public function getOptionsLoader() {
        if (!$this->hasRelation()) {
            throw new \BadMethodCallException(
                "HasManyRelatedRecordsFormInput '{$this->getName()}' must be linked to Relation in order to funciton properly"
            );
        }
        if (!$this->optionsLoader) {
            /** @var Relation|null $dataSourceRelation */
            $dataSourceRelation = null;
            $relations = $this
                ->getRelation()
                ->getForeignTable()
                ->getTableStructure()
                ->getColumn($this->getRelationColumn())
                ->getRelations();
            foreach ($relations as $relation) {
                if ($relation->getType() === Relation::HAS_ONE) {
                    $dataSourceRelation = $relation;
                    break;
                }
            }
            if (!$dataSourceRelation) {
                throw new \UnexpectedValueException(
                    "Failed to detect data source Relation for HasManyRelatedRecordsFormInput '{$this->getName()}'. "
                    . "Column '{$this->getRelation()->getForeignTable()->getName()}.{$this->getRelationColumn()}'"
                    . ' has no Relation with type HAS ONE'
                );
            }
            $table = $dataSourceRelation->getForeignTable();
            $this->optionsLoader = function () use ($dataSourceRelation, $table) {
                $labelColumn = $this->getOptionLabelColumnForDefaultOptionsLoader($dataSourceRelation->getDisplayColumnName());
                if ($labelColumn instanceof \Closure) {
                    $records = $table::select('*', value($this->getDbQueryConditionsForDefaultOptionsLoader()));
                    $records->enableDbRecordInstanceReuseDuringIteration(true);
                    $options = [];
                    /** @var RecordInterface $record */
                    foreach ($records as $record) {
                        $options[$record->getPrimaryKeyValue()] = $labelColumn($record);
                    }
                    return $options;
                } else {
                    return $table::selectAssoc(
                        $table::getPkColumnName(),
                        $labelColumn,
                        value($this->getDbQueryConditionsForDefaultOptionsLoader())
                    );
                }
            };
        }
        return $this->optionsLoader;
    }

    /**
     * @return array
     */
    protected function getDbQueryConditionsForDefaultOptionsLoader() {
        return $this->dbQueryConditionsForDefaultOptionsLoader;
    }

    /**
     * Set conditions for default options loader
     * @param array|\Closure $conditonsAndOptions
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setDbQueryConditionsForDefaultOptionsLoader($conditonsAndOptions) {
        if (!is_array($conditonsAndOptions) && !($conditonsAndOptions instanceof DbExpr)) {
            throw new \InvalidArgumentException(
                '$conditonsAndOptions argument must be an array or a closure'
            );
        }
        $this->dbQueryConditionsForDefaultOptionsLoader = $conditonsAndOptions;
        return $this;
    }

    /**
     * Set source for options labels
     * @param string|\Closure $columnNameOrClosure
     *      - string: column name
     *      - \Closure: function (RecordInterface $record) { return 'value' }
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setOptionLabelColumnForDefaultOptionsLoader($columnNameOrClosure) {
        if (!is_string($columnNameOrClosure) && !($columnNameOrClosure instanceof DbExpr)) {
            throw new \InvalidArgumentException(
                '$columnNameOrClosure argument must be a string or a closure'
            );
        }
        $this->optionLabelColumnForDefaultOptionsLoader = $columnNameOrClosure;
        return $this;
    }

    /**
     * @param mixed $default
     * @return string|DbExpr|null
     */
    protected function getOptionLabelColumnForDefaultOptionsLoader($default = null) {
        return $this->optionLabelColumnForDefaultOptionsLoader ?: $default;
    }


}
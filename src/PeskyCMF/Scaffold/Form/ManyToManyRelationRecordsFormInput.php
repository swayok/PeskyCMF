<?php

namespace PeskyCMF\Scaffold\Form;

use PeskyORM\Core\DbExpr;
use PeskyORM\Exception\InvalidDataException;
use PeskyORM\ORM\RecordInterface;
use PeskyORM\ORM\Relation;

class ManyToManyRelationRecordsFormInput extends FormInput {

    /**
     * @var array
     */
    protected $dbQueryConditionsForDefaultOptionsLoader = [];
    /**
     * @var null|string|DbExpr
     */
    protected $optionLabelColumnForDefaultOptionsLoader;

    /**
     * Name of the column in 'linker table' that contains primary key values of the foreign table
     * For example you have 3 tables: items (main table), categories (foreing table), item_categories (linker table);
     * Here you have many-to-many relation between items and categories that resolved via 'linker table'
     * item_categories that contains only 3 columns: id, item_id (link to items.id), category_id (link to categoris.id);
     * You need to pass 'category_id' via $columnName argument
     * @param $columnName
     * @return $this
     */
    public function setRelationsLinkingColumn($columnName) {
        $this->relationColumn = $columnName;
        return $this;
    }

    /**
     * @return string
     */
    public function getType() {
        return static::TYPE_MULTISELECT;
    }

    public function getValidators($isCreation) {
        return [
            $this->getName() => 'array|nullable',
            $this->getName(). '.' . $this->getRelationColumn() => 'integer',
        ];
    }

    public function setRelation(Relation $relation, $columnName) {
        $this->relation = $relation;
        if ($columnName !== $relation->getForeignColumnName()) {
            $this->relationColumn = $columnName;
        }
        return $this;
    }

    public function getRelationColumn() {
        if (empty($this->relationColumn)) {
            throw new \UnexpectedValueException(
                "Relations linking column was not provided for '{$this->getName()}' input. "
                . 'Use setRelationsLinkingColumn(\'column_name\') method to provide it'
            );
        }
        return parent::getRelationColumn();
    }

    public function modifySubmitedValueBeforeValidation($value, array $data) {
        if (empty($value)) {
            $value = [];
        }
        if (is_array($value)) {
            $newFkValues = array_values($value);
            $value = [];
            foreach ($newFkValues as $fkValue) {
                $value[] = [$this->getRelationColumn() => (int)$fkValue];
            }
        }
        return parent::modifySubmitedValueBeforeValidation($value, $data);
    }

    public function doDefaultValueConversionByType($value, $type, array $record) {
        if (!is_array($value)) {
            throw new \InvalidArgumentException("Invalid data received for relation '{$this->getRelation()->getName()}'. Array expected.");
        }
        $ret = [];
        $column = $this->getRelationColumn();
        /** @var array $value */
        foreach ($value as $foreignRecord) {
            if (!array_key_exists($column, $foreignRecord)) {
                throw new \InvalidArgumentException(
                    "Invalid data received for relation '{$this->getRelation()->getName()}'. Value for column {$column} not found."
                );
            }
            $ret[] = $foreignRecord[$column];
        }
        return $ret;
    }

    public function hasOptionsLoader() {
        return true;
    }

    public function setOptions($options) {
        throw new \BadMethodCallException(
            "Plain options is forbidden for ManyToManyRelationRecordsFormInput '{$this->getName()}'. Use options loader."
        );
    }

    public function getOptionsLoader() {
        if (!$this->hasRelation()) {
            throw new \BadMethodCallException(
                "ManyToManyRelationRecordsFormInput '{$this->getName()}' must be linked to Relation in order to funciton properly"
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
                    "Failed to detect data source Relation for ManyToManyRelationRecordsFormInput '{$this->getName()}'. "
                    . "Column '{$this->getRelation()->getForeignTable()->getName()}.{$this->getRelationColumn()}'"
                    . ' has no Relation with type HAS ONE'
                );
            }
            $table = $dataSourceRelation->getForeignTable();
            $this->optionsLoader = function () use ($dataSourceRelation, $table) {
                $labelColumn = $this->getOptionLabelColumnForDefaultOptionsLoader($dataSourceRelation->getDisplayColumnName());
                if ($labelColumn instanceof \Closure) {
                    $records = $table::select('*', value($this->getDbQueryConditionsForDefaultOptionsLoader()));
                    $records->optimizeIteration();
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
        if (
            !is_array($conditonsAndOptions)
            && !($conditonsAndOptions instanceof DbExpr)
            && !($conditonsAndOptions instanceof \Closure)
        ) {
            throw new \InvalidArgumentException(
                '$conditonsAndOptions argument must be a string, DbExpr or a Closure'
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
        if (
            !is_string($columnNameOrClosure)
            && !($columnNameOrClosure instanceof DbExpr)
            && !($columnNameOrClosure instanceof \Closure)
        ) {
            throw new \InvalidArgumentException(
                '$columnNameOrClosure argument must be a string, DbExpr or a Closure'
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
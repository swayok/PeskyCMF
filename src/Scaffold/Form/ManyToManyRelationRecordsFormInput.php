<?php

declare(strict_types=1);

namespace PeskyCMF\Scaffold\Form;

use Closure;
use PeskyORM\Core\DbExpr;
use PeskyORM\ORM\RecordInterface;
use PeskyORM\ORM\Relation;

class ManyToManyRelationRecordsFormInput extends FormInput
{
    
    protected Closure|array $dbQueryConditionsForDefaultOptionsLoader = [];
    protected string|null|DbExpr $optionLabelColumnForDefaultOptionsLoader = null;
    
    /**
     * Name of the column in 'linker table' that contains primary key values of the foreign table
     * For example you have 3 tables: items (main table), categories (foreing table), item_categories (linker table);
     * Here you have many-to-many relation between items and categories that resolved via 'linker table'
     * item_categories that contains only 3 columns: id, item_id (link to items.id), category_id (link to categoris.id);
     * You need to pass 'category_id' via $columnName argument
     */
    public function setRelationsLinkingColumn(string $columnName): static
    {
        $this->relationColumn = $columnName;
        return $this;
    }
    
    public function getType(): string
    {
        return static::TYPE_MULTISELECT;
    }
    
    public function getValidators(bool $isCreation): array
    {
        return [
            $this->getName() => 'array|nullable',
            $this->getName() . '.' . $this->getRelationColumn() => 'integer',
        ];
    }
    
    public function setRelation(Relation $relation, string $columnName): static
    {
        $this->relation = $relation;
        if ($columnName !== $relation->getForeignColumnName()) {
            $this->relationColumn = $columnName;
        }
        return $this;
    }
    
    /**
     * @throws \UnexpectedValueException
     */
    public function getRelationColumn(): ?string
    {
        if (empty($this->relationColumn)) {
            throw new \UnexpectedValueException(
                "Relations linking column was not provided for '{$this->getName()}' input. "
                . 'Use setRelationsLinkingColumn(\'column_name\') method to provide it'
            );
        }
        return parent::getRelationColumn();
    }
    
    public function modifySubmitedValueBeforeValidation(mixed $value, array $data): array
    {
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
    
    /**
     * @throws \InvalidArgumentException
     */
    public function doDefaultValueConversionByType(mixed $value, string $type, array $record): array
    {
        $relation = $this->getRelation();
        if (!is_array($value)) {
            throw new \InvalidArgumentException("Invalid data received for relation '{$relation->getName()}'. Array expected.");
        }
        $ret = [];
        $column = $this->getRelationColumn();
        foreach ($value as $foreignRecord) {
            if (!array_key_exists($column, $foreignRecord)) {
                throw new \InvalidArgumentException(
                    "Invalid data received for relation '{$relation->getName()}'. Value for column {$column} not found."
                );
            }
            $ret[] = $foreignRecord[$column];
        }
        return $ret;
    }
    
    public function getRelation(): Relation
    {
        if (!$this->relation) {
            throw new \BadMethodCallException(
                "ManyToManyRelationRecordsFormInput '{$this->getName()}' must be linked to Relation in order to funciton properly"
            );
        }
        return $this->relation;
    }
    
    public function hasOptionsLoader(): bool
    {
        return true;
    }
    
    public function setOptions(array|Closure $options): static
    {
        throw new \BadMethodCallException(
            "Plain options is forbidden for ManyToManyRelationRecordsFormInput '{$this->getName()}'. Use options loader."
        );
    }
    
    public function getOptionsLoader(): \Closure
    {
        $relation = $this->getRelation();
        if (!$this->optionsLoader) {
            /** @var Relation|null $dataSourceRelation */
            $dataSourceRelation = null;
            $relations = $relation
                ->getForeignTable()
                ->getTableStructure()
                ->getColumn($this->getRelationColumn())
                ->getRelations();
            foreach ($relations as $otherRelation) {
                if ($otherRelation->getType() === Relation::HAS_ONE) {
                    $dataSourceRelation = $otherRelation;
                    break;
                }
            }
            if (!$dataSourceRelation) {
                throw new \UnexpectedValueException(
                    "Failed to detect data source Relation for ManyToManyRelationRecordsFormInput '{$this->getName()}'. "
                    . "Column '{$relation->getForeignTable()->getName()}.{$this->getRelationColumn()}'"
                    . ' has no Relation with type HAS ONE'
                );
            }
            $table = $dataSourceRelation->getForeignTable();
            $this->optionsLoader = function () use ($dataSourceRelation, $table) {
                $labelColumn = $this->getOptionLabelColumnForDefaultOptionsLoader($dataSourceRelation->getDisplayColumnName());
                if ($labelColumn instanceof \Closure) {
                    $records = $table::select('*', $this->getDbQueryConditionsForDefaultOptionsLoader());
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
                        $this->getDbQueryConditionsForDefaultOptionsLoader()
                    );
                }
            };
        }
        return $this->optionsLoader;
    }
    
    protected function getDbQueryConditionsForDefaultOptionsLoader(): array
    {
        return is_array($this->dbQueryConditionsForDefaultOptionsLoader)
            ? $this->dbQueryConditionsForDefaultOptionsLoader
            : ($this->dbQueryConditionsForDefaultOptionsLoader)();
    }
    
    /**
     * Set conditions for default options loader
     * \Closure -> function(): array { return []; }
     */
    public function setDbQueryConditionsForDefaultOptionsLoader(array|Closure $conditonsAndOptions): static
    {
        $this->dbQueryConditionsForDefaultOptionsLoader = $conditonsAndOptions;
        return $this;
    }
    
    /**
     * Set source for options labels.
     * $columnNameOrClosure:
     * - string: column name
     * - DbExpr: some complex column name
     * - \Closure: function (RecordInterface $record): string|DbExpr { return 'value'; }
     */
    public function setOptionLabelColumnForDefaultOptionsLoader(Closure|DbExpr|string $columnNameOrClosure): static
    {
        $this->optionLabelColumnForDefaultOptionsLoader = $columnNameOrClosure;
        return $this;
    }
    
    protected function getOptionLabelColumnForDefaultOptionsLoader(mixed $default = null): mixed
    {
        return $this->optionLabelColumnForDefaultOptionsLoader ?: $default;
    }
    
    
}
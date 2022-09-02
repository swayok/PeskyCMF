<?php

declare(strict_types=1);

namespace PeskyCMF\Scaffold\ItemDetails;

use PeskyORM\ORM\Relation;
use Swayok\Html\Tag;

class ManyToManyRelationRecordsValueCell extends ValueCell
{
    
    protected string|\Closure|null $columnForLinksLabels = null;
    private ?Relation $dataSourceRelation = null;
    
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
    
    public function setRelation(Relation $relation, string $columnName): static
    {
        $this->relation = $relation;
        if ($columnName !== $relation->getForeignColumnName()) {
            $this->relationColumn = $columnName;
        }
        $this->dataSourceRelation = null;
        return $this;
    }
    
    public function getRelationColumn(): string
    {
        if (empty($this->relationColumn)) {
            throw new \UnexpectedValueException(
                "Relations linking column was not provided for '{$this->getName()}' input. "
                . 'Use setRelationsLinkingColumn(\'column_name\') method to provide it'
            );
        }
        return parent::getRelationColumn();
    }
    
    public function getAdditionalRelationsToRead(): array
    {
        /** @noinspection NullPointerExceptionInspection */
        return [$this->getRelation()->getName() => ['*', $this->getDataSourceRelation()->getName() => ['*']]];
    }
    
    public function doDefaultValueConversionByType(mixed $value, string $type, array $record): string
    {
        if (is_array($value) && !empty($value)) {
            $links = [];
            $dataSourceRelation = $this->getDataSourceRelation();
            $labelColumn = $this->getColumnForLinksLabels($dataSourceRelation->getDisplayColumnName());
            $resourceName = $this->getResourceNameForRouteToRelatedRecord() ?: $dataSourceRelation->getForeignTable()->getName();
            foreach ($value as $relatedRecord) {
                $links[] = Tag::a()
                    ->setHref(
                        $this->getCmfConfig()
                            ->getScaffoldConfig($resourceName)
                            ->getUrlToItemDetails($relatedRecord[$this->getRelationColumn()])
                    )
                    ->setContent(
                        $labelColumn instanceof \Closure
                            ? $labelColumn($relatedRecord[$dataSourceRelation->getName()])
                            : $relatedRecord[$dataSourceRelation->getName()][$labelColumn]
                    )
                    ->build();
            }
            return implode('<br>', $links);
        }
        return '';
    }
    
    /**
     * @throws \UnexpectedValueException
     */
    protected function getDataSourceRelation(): Relation
    {
        if (!$this->dataSourceRelation) {
            /** @var Relation $relation */
            $relation = $this->getRelation();
            $relations = $relation
                ->getForeignTable()
                ->getTableStructure()
                ->getColumn($this->getRelationColumn())
                ->getRelations();
            foreach ($relations as $dataSourceRelation) {
                if ($relation->getType() === Relation::HAS_ONE) {
                    $this->dataSourceRelation = $dataSourceRelation;
                    break;
                }
            }
            if (!$this->dataSourceRelation) {
                throw new \UnexpectedValueException(
                    "Failed to detect data source Relation for ManyToManyRelationRecordsFormInput '{$this->getName()}'. "
                    . "Column '{$relation->getForeignTable()->getName()}.{$this->getRelationColumn()}'"
                    . ' has no Relation with type HAS ONE'
                );
            }
        }
        return $this->dataSourceRelation;
    }
    
    /**
     * Set column name or closure that will be used as label for links
     * @param \Closure|string $columnNameOrClosure
     *      - string: column name
     *      - \Closure: function (RecordInterface $record) { return 'value' }
     * @throws \InvalidArgumentException
     */
    public function setColumnForLinksLabels(\Closure|string $columnNameOrClosure): static
    {
        $this->columnForLinksLabels = $columnNameOrClosure;
        return $this;
    }
    
    protected function getColumnForLinksLabels(mixed $default = null): mixed
    {
        return $this->columnForLinksLabels ?: $default;
    }
    
}
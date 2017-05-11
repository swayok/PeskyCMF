<?php

namespace PeskyCMF\Scaffold\ItemDetails;

use PeskyORM\Core\DbExpr;
use PeskyORM\ORM\Relation;
use Swayok\Html\Tag;

class ManyToManyRelationRecordsValueCell extends ValueCell {

    protected $columnForLinksLabels;

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

    public function getAdditionalRelationsToRead() {
        return [$this->getRelation()->getName() => ['*', $this->getDataSourceRelation()->getName() => ['*']]];
    }

    public function doDefaultValueConversionByType($value, $type, array $record) {
        if (is_array($value) && !empty($value)) {
            $links = [];
            $dataSourceRelation = $this->getDataSourceRelation();
            $labelColumn = $this->getColumnForLinksLabels($dataSourceRelation->getDisplayColumnName());
            $tableNameForRoute = $this->getTableNameForRouteToRelatedRecord() ?: $dataSourceRelation->getForeignTable()->getName();
            foreach ($value as $relatedRecord) {
                $links[] = Tag::a()
                    ->setHref(routeToCmfItemDetails(
                        $tableNameForRoute,
                        $relatedRecord[$this->getRelationColumn()]
                    ))
                    ->setContent($labelColumn instanceof \Closure
                        ? $labelColumn($relatedRecord[$this->getDataSourceRelation()->getName()])
                        : $relatedRecord[$this->getDataSourceRelation()->getName()][$labelColumn]
                    )
                    ->build();
            }
            return implode('<br>', $links);
        }
        return '';
    }

    protected function getDataSourceRelation() {
        /** @var Relation|null $dataSourceRelation */
        static $dataSourceRelation;
        if (!$dataSourceRelation) {
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
        }
        return $dataSourceRelation;
    }

    /**
     * Set column name or closure that will be used as label for links
     * @param string|\Closure $columnNameOrClosure
     *      - string: column name
     *      - \Closure: function (RecordInterface $record) { return 'value' }
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setColumnForLinksLabels($columnNameOrClosure) {
        if (!is_string($columnNameOrClosure) && !($columnNameOrClosure instanceof DbExpr)) {
            throw new \InvalidArgumentException(
                '$columnNameOrClosure argument must be a string or a closure'
            );
        }
        $this->columnForLinksLabels = $columnNameOrClosure;
        return $this;
    }

    /**
     * @param mixed $default
     * @return string|DbExpr|null
     */
    protected function getColumnForLinksLabels($default = null) {
        return $this->columnForLinksLabels ?: $default;
    }

}
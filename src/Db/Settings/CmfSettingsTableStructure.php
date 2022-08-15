<?php

namespace PeskyCMF\Db\Settings;

use PeskyCMF\Db\CmfDbTableStructure;
use PeskyORM\ORM\Column;
use PeskyORM\ORM\DefaultColumnClosures;
use PeskyORMLaravel\Db\TableStructureTraits\IdColumn;

/**
 * @property-read Column    $id
 * @property-read Column    $key
 * @property-read Column    $value
 */
class CmfSettingsTableStructure extends CmfDbTableStructure {

    use IdColumn;

    public static function getTableName(): string {
        return 'settings';
    }

    private function key() {
        return Column::create(Column::TYPE_STRING)
            ->uniqueValues()
            ->disallowsNullValues();
    }

    private function value() {
        // DO NOT USE TYPE_JSON/TYPE_JSONB here. It will add duplicate json encoding and cause
        // unnecessary problems with numeric values
        return Column::create(Column::TYPE_TEXT)
            ->convertsEmptyStringToNull();
    }

    private function default_language() {
        return Column::create(Column::TYPE_STRING)
            ->doesNotExistInDb()
            ->setDefaultValue('en')
            ;
    }

    private function languages() {
        return Column::create(Column::TYPE_JSON)
            ->doesNotExistInDb()
            ->setDefaultValue([
                'en' => 'English'
            ])
            ->setValueNormalizer(function ($value, $isFromDb, Column $column) {
                return $this->languagesMapNormalizer($value, $isFromDb, $column);
            })
            ;
    }

    private function fallback_languages() {
        return Column::create(Column::TYPE_JSON)
            ->doesNotExistInDb()
            ->setDefaultValue([])
            ->setValueNormalizer(function ($value, $isFromDb, Column $column) {
                return $this->languagesMapNormalizer($value, $isFromDb, $column);
            })
            ;
    }

    protected function languagesMapNormalizer($value, $isFromDb, Column $column) {
        if (!is_array($value) && is_string($value)) {
            $value = json_decode($value, true);
        }
        if (!is_array($value)) {
            $value = $column->getValidDefaultValue();
        }
        $normalized = [];
        /** @var array $value */
        foreach ($value as $key => $keyValue) {
            if (
                is_int($key)
                && is_array($keyValue)
                && array_has($keyValue, 'key')
                && array_has($keyValue, 'value')
                && trim($keyValue['key']) !== ''
            ) {
                $normalized[strtolower(trim($keyValue['key']))] = $keyValue['value'];
            } else if (is_string($keyValue) && trim($key) !== '') {
                $normalized[strtolower(trim($key))] = $keyValue;
            }
        }
        return DefaultColumnClosures::valueNormalizer($normalized, $isFromDb, $column);
    }

}

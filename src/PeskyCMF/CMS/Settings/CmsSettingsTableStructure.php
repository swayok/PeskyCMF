<?php

namespace PeskyCMF\CMS\Settings;

use PeskyCMF\CMS\CmsTableStructure;
use PeskyCMF\Db\Traits\IdColumn;
use PeskyORM\ORM\Column;
use PeskyORM\ORM\DefaultColumnClosures;

/**
 * @property-read Column    $id
 * @property-read Column    $key
 * @property-read Column    $value
 */
class CmsSettingsTableStructure extends CmsTableStructure {

    use IdColumn;

    /**
     * @return string
     */
    static public function getTableName() {
        return 'settings';
    }

    private function key() {
        return Column::create(Column::TYPE_STRING)
            ->uniqueValues()
            ->disallowsNullValues();
    }

    private function value() {
        return Column::create(Column::TYPE_TEXT)
            ->disallowsNullValues();
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
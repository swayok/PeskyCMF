<?php

declare(strict_types=1);

namespace PeskyCMF\Db\Settings;

use Illuminate\Support\Arr;
use PeskyCMF\Db\CmfDbTableStructure;
use PeskyORM\ORM\Column;
use PeskyORM\ORM\DefaultColumnClosures;
use PeskyORMColumns\TableStructureTraits\IdColumn;

class CmfSettingsTableStructure extends CmfDbTableStructure
{
    
    use IdColumn;
    
    public static function getTableName(): string
    {
        return 'settings';
    }
    
    private function key(): Column
    {
        return Column::create(Column::TYPE_STRING)
            ->uniqueValues()
            ->disallowsNullValues();
    }
    
    private function value(): Column
    {
        // DO NOT USE TYPE_JSON/TYPE_JSONB here. It will add duplicate json encoding and cause
        // unnecessary problems with numeric values
        return Column::create(Column::TYPE_TEXT)
            ->convertsEmptyStringToNull();
    }
    
    private function default_language(): Column
    {
        return Column::create(Column::TYPE_STRING)
            ->doesNotExistInDb()
            ->setDefaultValue('en');
    }
    
    private function languages(): Column
    {
        return Column::create(Column::TYPE_JSON)
            ->doesNotExistInDb()
            ->setDefaultValue([
                'en' => 'English',
            ])
            ->setValueNormalizer(function ($value, $isFromDb, Column $column) {
                return $this->languagesMapNormalizer($value, $isFromDb, $column);
            });
    }
    
    private function fallback_languages(): Column
    {
        return Column::create(Column::TYPE_JSON)
            ->doesNotExistInDb()
            ->setDefaultValue([])
            ->setValueNormalizer(function ($value, $isFromDb, Column $column) {
                return $this->languagesMapNormalizer($value, $isFromDb, $column);
            });
    }
    
    protected function languagesMapNormalizer($value, $isFromDb, Column $column)
    {
        if (is_string($value)) {
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
                && Arr::has($keyValue, 'key')
                && Arr::has($keyValue, 'value')
                && trim($keyValue['key']) !== ''
            ) {
                $normalized[strtolower(trim($keyValue['key']))] = $keyValue['value'];
            } elseif (is_string($keyValue) && trim($key) !== '') {
                $normalized[strtolower(trim($key))] = $keyValue;
            }
        }
        return DefaultColumnClosures::valueNormalizer($normalized, $isFromDb, $column);
    }
    
}

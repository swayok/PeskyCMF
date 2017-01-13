<?php echo "<?php\n"; ?>

namespace App\<?php echo $dbClassesAppSubfolder ?>\<?php echo $baseClassNamePlural; ?>;

use PeskyCMF\Db\Traits\AdminIdColumn;
use PeskyCMF\Db\Traits\IdColumn;
use PeskyORM\ORM\Column;
use <?php echo $parentFullClassNameForTableStructure ?>;

/**
 * @property-read Column    $id
 * @property-read Column    $key
 * @property-read Column    $admin_id
 * @property-read Column    $value
 */
class <?php echo $baseClassNamePlural; ?>TableStructure extends <?php echo $parentClassNameForTableStructure ?> {

    use IdColumn,
        AdminIdColumn;

    /**
     * @return string
     */
    static public function getTableName() {
        return '<?php echo $baseClassNameUnderscored; ?>';
    }

    /**
     * @return string|null
     */
    static public function getSchema() {
        return null;
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

    private function languages() {
        return Column::create(Column::TYPE_JSON)
            ->doesNotExistInDb()
            ->setDefaultValue([
                'en' => 'English'
            ])
            ->setValueNormalizer(function ($value, $isFromDb, Column $column) {
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
            })
            ;
    }

}
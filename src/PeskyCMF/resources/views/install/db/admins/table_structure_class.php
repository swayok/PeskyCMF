<?php echo "<?php\n"; ?>

namespace App\<?php echo $dbClassesAppSubfolder ?>\<?php echo $baseClassNamePlural; ?>;

use App\<?php echo $sectionName; ?>\<?php echo $sectionName; ?>Config;
use PeskyCMF\Db\Traits\IdColumn;
use PeskyCMF\Db\Traits\IsActiveColumn;
use PeskyCMF\Db\Traits\TimestampColumns;
use PeskyCMF\Db\Traits\UserAuthColumns;
use PeskyORM\ORM\Column;
use PeskyORM\ORM\Relation;
use <?php echo $parentFullClassNameForTableStructure ?>;

/**
 * @property-read Column    $id
 * @property-read Column    $parent_id
 * @property-read Column    $name
 * @property-read Column    $email
 * @property-read Column    $password
 * @property-read Column    $ip
 * @property-read Column    $is_superadmin
 * @property-read Column    $is_active
 * @property-read Column    $role
 * @property-read Column    $language
 * @property-read Column    $created_at
 * @property-read Column    $updated_at
 * @property-read Column    $timezone
 * @property-read Column    $remember_token
 */
class <?php echo $baseClassNamePlural; ?>TableStructure extends <?php echo $parentClassNameForTableStructure ?> {

    use IdColumn,
        TimestampColumns,
        IsActiveColumn,
        UserAuthColumns
        ;

    /**
     * @return string
     */
    static public function getTableName() {
        return '<?php echo $baseClassNameUnderscored; ?>';
    }

    static public function getSchema() {
        return null;
    }

    private function parent_id() {
        return Column::create(Column::TYPE_INT)
            ->convertsEmptyStringToNull();
    }

    private function email() {
        return Column::create(Column::TYPE_EMAIL)
            ->disallowsNullValues()
            ->convertsEmptyStringToNull()
            ->trimsValue()
            ->uniqueValues();
    }

    private function name() {
        return Column::create(Column::TYPE_STRING)
            ->disallowsNullValues();
    }

    private function ip() {
        return Column::create(Column::TYPE_IPV4_ADDRESS)
            ->convertsEmptyStringToNull();
    }

    private function is_superadmin() {
        return Column::create(Column::TYPE_BOOL)
            ->disallowsNullValues()
            ->convertsEmptyStringToNull()
            ->setDefaultValue(false);
    }

    private function role() {
        return Column::create(Column::TYPE_ENUM)
            ->setAllowedValues(<?php echo $sectionName; ?>Config::roles_list())
            ->disallowsNullValues()
            ->convertsEmptyStringToNull()
            ->setDefaultValue(<?php echo $sectionName; ?>Config::default_role());
    }

    private function language() {
        return Column::create(Column::TYPE_ENUM)
            ->setAllowedValues(<?php echo $sectionName; ?>Config::locales())
            ->disallowsNullValues()
            ->convertsEmptyStringToNull()
            ->setDefaultValue(<?php echo $sectionName; ?>Config::default_locale());
    }

    private function timezone() {
        return Column::create(Column::TYPE_STRING)
            ->convertsEmptyStringToNull();
    }

    private function Parent<?php echo $baseClassNameSingular; ?>() {
        return Relation::create(
                'parent_id',
                Relation::BELONGS_TO,
                <?php echo $baseClassNamePlural; ?>Table::class,
                'id'
            )
            ->setDisplayColumnName(<?php echo $sectionName; ?>Config::user_login_column());
    }

}
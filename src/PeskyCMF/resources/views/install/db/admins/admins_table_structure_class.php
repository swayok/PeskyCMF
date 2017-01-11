<?php echo "<?php\n"; ?>

namespace App\<?php echo $dbClassesAppSubfolder ?>\<?php echo $baseClassNamePlural; ?>;

use App\<?php echo $sectionName; ?>\AdminConfig;
use PeskyCMF\Db\Traits\IdColumn;
use PeskyCMF\Db\Traits\IsActiveColumn;
use PeskyCMF\Db\Traits\TimestampColumns;
use PeskyCMF\Db\Traits\UserAuthColumns;
use PeskyORM\ORM\Column;
use PeskyORM\ORM\Relation;
use PeskyORM\ORM\TableStructure;

class <?php echo $baseClassNamePlural; ?>TableStructure extends TableStructure {

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

    private function parent_id() {
        return Column::create(Column::TYPE_INT)
            ->convertsEmptyStringToNull();
    }

    private function email() {
        return Column::create(Column::TYPE_EMAIL)
            ->disallowsNullValues()
            ->trimsValue()
            ->uniqueValues();
    }

    private function name() {
        return Column::create(Column::TYPE_STRING)
            ->disallowsNullValues();
    }

    private function ip() {
        return Column::create(Column::TYPE_IPV4_ADDRESS)
            ->allowsNullValues();
    }

    private function is_superadmin() {
        return Column::create(Column::TYPE_BOOL)
            ->disallowsNullValues()
            ->setDefaultValue(false);
    }

    private function role() {
        return Column::create(Column::TYPE_ENUM)
            ->setAllowedValues(AdminConfig::roles_list())
            ->disallowsNullValues()
            ->setDefaultValue(AdminConfig::default_role());
    }

    private function language() {
        return Column::create(Column::TYPE_ENUM)
            ->setAllowedValues(AdminConfig::locales())
            ->disallowsNullValues()
            ->setDefaultValue(AdminConfig::default_locale());
    }

    private function timezone() {
        return Column::create(Column::TYPE_STRING)
            ->allowsNullValues();
    }

    private function Parent<?php echo $baseClassNameSingular; ?>() {
        return Relation::create(
                'parent_id',
                Relation::BELONGS_TO,
                AdminConfig::getModelByTableName(static::getTableName()),
                'id'
            )
            ->setDisplayColumnName(AdminConfig::user_login_column());
    }

}
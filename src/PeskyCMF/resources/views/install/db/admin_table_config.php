<?php echo "<?php\n"; ?>

namespace App\<?php echo $dbClassesAppSubfolder ?>\Admins;

use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Db\Traits\IdColumn;
use PeskyCMF\Db\Traits\IsActiveColumn;
use PeskyCMF\Db\Traits\TimestampColumns;
use PeskyCMF\Db\Traits\UserAuthColumns;
use PeskyORM\ORM\Column;
use PeskyORM\ORM\Relation;
use PeskyORM\ORM\TableStructure;

class AdminsTableStructure extends TableStructure {

    use IdColumn,
        TimestampColumns,
        IsActiveColumn,
        UserAuthColumns
        ;

    /**
     * @return string
     */
    static public function getTableName() {
        return 'admins';
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
            ->disallowsNullValues();
    }

    private function is_superadmin() {
        return Column::create(Column::TYPE_BOOL)
            ->disallowsNullValues()
            ->setDefaultValue(false);
    }

    private function role() {
        return Column::create(Column::TYPE_ENUM)
            ->setAllowedValues(CmfConfig::getInstance()->roles_list())
            ->disallowsNullValues()
            ->setDefaultValue(CmfConfig::getInstance()->default_role());
    }

    private function language() {
        return Column::create(Column::TYPE_ENUM)
            ->setAllowedValues(CmfConfig::getInstance()->locales())
            ->disallowsNullValues()
            ->setDefaultValue(CmfConfig::getInstance()->default_locale());
    }

    private function ParentAdmin() {
        return Relation::create('parent_id', Relation::BELONGS_TO, __CLASS__, 'id')
            ->setDisplayColumnName(CmfConfig::getInstance()->user_login_column());
    }

}
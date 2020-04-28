<?php echo "<?php\n"; ?>

namespace App\Db\Admin;

use PeskyCMF\Config\CmfConfig;
use PeskyORMLaravel\Db\TableStructureTraits\IdColumn;
use PeskyORMLaravel\Db\TableStructureTraits\IsActiveColumn;
use PeskyORMLaravel\Db\TableStructureTraits\TimestampColumns;
use PeskyORMLaravel\Db\TableStructureTraits\UserAuthColumns;
use PeskyORM\ORM\Column;
use PeskyORM\ORM\Relation;
use App\Db\BaseDbTableConfig;

class AdminTableConfig extends BaseDbTableConfig {

    const TABLE_NAME = 'admins';
    protected $name = self::TABLE_NAME;

    use IdColumn,
        TimestampColumns,
        IsActiveColumn,
        UserAuthColumns
        ;

    private function parent_id() {
        return Column::create(Column::TYPE_INT)
            ->allowsNullValues();
    }

    private function email() {
        return Column::create(Column::TYPE_EMAIL)
            ->disallowsNullValues()
            ->trimsValue()
            ->setIsUnique(true);
    }

    private function name() {
        return Column::create(Column::TYPE_STRING)
            ->disallowsNullValues()
            ->setMaxLength(200);
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
        return Column::create(Column::TYPE_STRING)
            ->setAllowedValues(CmfConfig::getInstance()->roles_list())
            ->disallowsNullValues()
            ->setDefaultValue(CmfConfig::getInstance()->default_role());
    }

    private function language() {
        return Column::create(Column::TYPE_STRING)
            ->setAllowedValues(CmfConfig::getInstance()->locales())
            ->disallowsNullValues()
            ->setMaxLength(2)
            ->setMinLength(2)
            ->setDefaultValue(CmfConfig::getInstance()->default_locale());
    }

    private function ParentAdmin() {
        return Relation::create($this, 'parent_id', Relation::BELONGS_TO, self::TABLE_NAME, 'id')
            ->setDisplayColumnName(CmfConfig::getInstance()->user_login_column());
    }

}
<?php echo "<?php\n"; ?>

namespace App\Db\Admin;

use PeskyCMF\Config\CmfConfig;
use PeskyORMLaravel\Db\TableStructureTraits\IdColumn;
use PeskyORMLaravel\Db\TableStructureTraits\IsActiveColumn;
use PeskyORMLaravel\Db\TableStructureTraits\TimestampColumns;
use PeskyORMLaravel\Db\TableStructureTraits\UserAuthColumns;
use App\Db\DbColumnConfig;
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
        return DbColumnConfig::create(DbColumnConfig::TYPE_INT)
            ->setIsRequired(false)
            ->setIsNullable(true);
    }

    private function email() {
        return DbColumnConfig::create(DbColumnConfig::TYPE_EMAIL)
            ->setIsNullable(false)
            ->setIsRequired(true)
            ->setTrimValue(true)
            ->setIsUnique(true);
    }

    private function name() {
        return DbColumnConfig::create(DbColumnConfig::TYPE_STRING)
            ->setIsNullable(false)
            ->setIsRequired(false)
            ->setMaxLength(200);
    }

    private function ip() {
        return DbColumnConfig::create(DbColumnConfig::TYPE_IPV4_ADDRESS)
            ->setIsRequired(false)
            ->setIsNullable(false);
    }

    private function is_superadmin() {
        return DbColumnConfig::create(DbColumnConfig::TYPE_BOOL)
            ->setIsNullable(false)
            ->setIsRequired(false)
            ->setDefaultValue(false);
    }

    private function role() {
        return DbColumnConfig::create(DbColumnConfig::TYPE_STRING)
            ->setAllowedValues(CmfConfig::getInstance()->roles_list())
            ->setIsNullable(false)
            ->setIsRequired(true)
            ->setDefaultValue(CmfConfig::getInstance()->default_role());
    }

    private function language() {
        return DbColumnConfig::create(DbColumnConfig::TYPE_STRING)
            ->setAllowedValues(CmfConfig::getInstance()->locales())
            ->setIsRequired(true)
            ->setIsNullable(false)
            ->setMaxLength(2)
            ->setMinLength(2)
            ->setDefaultValue(CmfConfig::getInstance()->default_locale());
    }

    private function ParentAdmin() {
        return Relation::create($this, 'parent_id', Relation::BELONGS_TO, self::TABLE_NAME, 'id')
            ->setDisplayColumnName(CmfConfig::getInstance()->user_login_column());
    }

}
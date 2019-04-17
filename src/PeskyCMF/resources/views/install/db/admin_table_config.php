<?php echo "<?php\n"; ?>

namespace App\Db\Admin;

use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Db\Traits\IdColumn;
use PeskyCMF\Db\Traits\IsActiveColumn;
use PeskyCMF\Db\Traits\TimestampColumns;
use PeskyCMF\Db\Traits\UserAuthColumns;
use PeskyORM\DbColumnConfig;
use PeskyORM\DbColumnConfig\EnumColumnConfig;
use PeskyORM\DbRelationConfig;
use PeskyORM\DbTableConfig;

class AdminTableConfig extends DbTableConfig {

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
        return EnumColumnConfig::create()
            ->setAllowedValues(CmfConfig::getInstance()->roles_list())
            ->setIsNullable(false)
            ->setIsRequired(true)
            ->setDefaultValue(CmfConfig::getInstance()->default_role());
    }

    private function language() {
        return EnumColumnConfig::create()
            ->setAllowedValues(CmfConfig::getInstance()->locales())
            ->setIsRequired(true)
            ->setIsNullable(false)
            ->setMaxLength(2)
            ->setMinLength(2)
            ->setDefaultValue(CmfConfig::getInstance()->default_locale());
    }

    private function ParentAdmin() {
        return DbRelationConfig::create($this, 'parent_id', DbRelationConfig::BELONGS_TO, self::TABLE_NAME, 'id')
            ->setDisplayField(CmfConfig::getInstance()->user_login_column());
    }

}
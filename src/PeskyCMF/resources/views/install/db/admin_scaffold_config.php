<?php echo "<?php\n"; ?>

namespace App\Db\Admin;

use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Scaffold\ScaffoldSectionConfig;
use PeskyCMF\Scaffold\DataGrid\DataGridFieldConfig;
use PeskyCMF\Scaffold\Form\FormConfig;
use PeskyCMF\Scaffold\Form\FormFieldConfig;
use PeskyCMF\Scaffold\Form\InputRendererConfig;
use PeskyCMF\Scaffold\ItemDetails\ItemDetailsFieldConfig;
use PeskyCMF\Core\Column;

class AdminScaffoldConfig extends ScaffoldSectionConfig {

    protected $isDetailsViewerAllowed = true;
    protected $isCreateAllowed = true;
    protected $isEditAllowed = true;
    protected $isDeleteAllowed = true;

    protected function createDataGridConfig() {
        return parent::createDataGridConfig()
            ->setContains(['ParentAdmin'])
            ->setOrderBy('id', 'desc')
            ->setFields(array(
                'id',
                'email',
                'name',
                'is_active',
                'is_superadmin',
                'role' => DataGridFieldConfig::create()
                    ->setIsSortable(true)
                    ->setValueConverter(function ($value, Column $columnConfig, $record) {
                        return CmfConfig::transCustom(".admins.role.$value");
                    }),
                'parent_id' => DataGridFieldConfig::create()
                    ->setType(ItemDetailsFieldConfig::TYPE_LINK),
                'created_at'
            ));
    }

    protected function createItemDetailsConfig() {
        return parent::createItemDetailsConfig()
            ->setContains(['ParentAdmin'])
            ->setFields([
                'id',
                'email',
                'name',
                'language' => ItemDetailsFieldConfig::create()
                    ->setValueConverter(function ($value, Column $columnConfig, array $record) {
                        return CmfConfig::transCustom(".language.$value");
                    }),
                'is_active',
                'role' => ItemDetailsFieldConfig::create()
                    ->setValueConverter(function ($value, Column $columnConfig, array $record) {
                        return CmfConfig::transCustom(".admins.role.$value");
                    }),
                'is_superadmin' => ItemDetailsFieldConfig::create(),
                'parent_id' => ItemDetailsFieldConfig::create()
                    ->setType(ItemDetailsFieldConfig::TYPE_LINK),
                'created_at',
                'updated_at',
            ]);
    }

    protected function createFormConfig() {
        return parent::createFormConfig()
            ->setHasFiles(false)
            ->setWidth(50)
            ->setFields([
                'email',
                'password' => FormFieldConfig::create()
                    ->setDefaultRendererConfigurator(function (InputRendererConfig $renderer) {
                        $renderer
                            ->setIsRequiredForCreate(true)
                            ->setIsRequiredForEdit(false);
                    }),
                'name',
                'language' => FormFieldConfig::create()
                    ->setOptions(function () {
                        $options = array();
                        foreach (CmfConfig::getInstance()->locales() as $lang) {
                            $options[$lang] = CmfConfig::transCustom(".language.$lang");
                        }
                        return $options;
                    })
                    ->setRenderer(function (FormFieldConfig $config, FormConfig $scaffoldAction) {
                        return InputRendererConfig::create('cmf::input/select')
                            ->required()
                            ->setOptions($config->getOptions());
                    }),
                'is_active',
                'is_superadmin',
                'role' => FormFieldConfig::create()
                    ->setOptions(function () {
                        $options = array();
                        foreach (CmfConfig::getInstance()->roles_list() as $roleId) {
                            $options[$roleId] = CmfConfig::transCustom(".admins.role.$roleId");
                        }
                        return $options;
                    })
                    ->setRenderer(function (FormFieldConfig $config, FormConfig $scaffoldAction) {
                        return InputRendererConfig::create('cmf::input/select')
                            ->required()
                            ->setOptions($config->getOptions());
                    }),
                'parent_id' => FormFieldConfig::create()
                    ->setRenderer(function () {
                        return InputRendererConfig::create('cmf::input/hidden');
                    })->setValueConverter(function ($value, Column $columnConfig, array $record) {
                        if (empty($record['id']) && empty($value)) {
                            return \Auth::guard()->user()->id;
                        } else {
                            return $value;
                        }
                    })
            ])
            ->setValidators([
                'role' => 'required|in:' . implode(',', CmfConfig::getInstance()->roles_list()),
                'language' => 'required|in:' . implode(',', CmfConfig::getInstance()->locales()),
                'is_active' => 'boolean',
                'is_superadmin' => 'boolean',
            ])
            ->addValidatorsForCreate([
                'email' => 'required|email|min:4|max:100|unique:' . AdminTableConfig::getTableName() . ',email',
                //'login' => 'required|regex:%^[a-zA-Z0-9@_.-]+$%is|min:4|max:100|unique:' . AdminTableConfig::getTableName() . ',login',
                'password' => 'required|min:6',
            ])
            ->addValidatorsForEdit([
                'id' => FormConfig::VALIDATOR_FOR_ID,
                'email' => 'required|email|min:4|max:100|unique:' . AdminTableConfig::getTableName() . ',email,{{id}},id',
                //'login' => 'required|regex:%^[a-zA-Z0-9@_.-]+$%is|min:4|max:100|unique:' . AdminTableConfig::getTableName() . ',login,{{id}},id',
                'password' => 'min:6',
            ])
            ;
    }

}
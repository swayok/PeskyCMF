<?php echo "<?php\n"; ?>

namespace App\<?php echo $dbClassesAppSubfolder ?>\<?php echo $baseClassNamePlural; ?>;

use App\<?php echo $sectionName; ?>\<?php echo $sectionName; ?>Config;
use PeskyCMF\Scaffold\DataGrid\DataGridColumn;
use PeskyCMF\Scaffold\Form\FormConfig;
use PeskyCMF\Scaffold\Form\FormInput;
use PeskyCMF\Scaffold\Form\InputRenderer;
use PeskyCMF\Scaffold\ItemDetails\ValueCell;
use PeskyCMF\Scaffold\NormalTableScaffoldConfig;
use PeskyORM\ORM\Column;

class <?php echo $baseClassNamePlural; ?>ScaffoldConfig extends NormalTableScaffoldConfig {

    protected $isDetailsViewerAllowed = true;
    protected $isCreateAllowed = true;
    protected $isEditAllowed = true;
    protected $isDeleteAllowed = true;

    protected function createDataGridConfig() {
        return parent::createDataGridConfig()
            ->readRelations(['Parent<?php echo $baseClassNameSingular; ?>' => ['id', 'email']])
            ->setOrderBy('id', 'desc')
            ->setColumns([
                'id',
                'email',
                'name',
                'is_active',
                'is_superadmin',
                'role' => DataGridColumn::create()
                    ->setIsSortable(true)
                    ->setValueConverter(function ($value, Column $columnConfig, $record) {
                        return cmfTransCustom(".<?php echo $baseClassNameUnderscored; ?>.role.$value");
                    }),
                'parent_id' => DataGridColumn::create()
                    ->setType(ValueCell::TYPE_LINK),
                'created_at'
            ]);
    }

    protected function createItemDetailsConfig() {
        return parent::createItemDetailsConfig()
            ->readRelations(['Parent<?php echo $baseClassNameSingular; ?>'])
            ->setValueCells([
                'id',
                'email',
                'name',
                'language' => ValueCell::create()
                    ->setValueConverter(function ($value, Column $columnConfig, array $record) {
                        return cmfTransCustom(".language.$value");
                    }),
                'is_active',
                'role' => ValueCell::create()
                    ->setValueConverter(function ($value, Column $columnConfig, array $record) {
                        return cmfTransCustom(".<?php echo $baseClassNameUnderscored; ?>.role.$value");
                    }),
                'is_superadmin' => ValueCell::create(),
                'parent_id' => ValueCell::create()
                    ->setType(ValueCell::TYPE_LINK),
                'created_at',
                'updated_at',
            ]);
    }

    protected function createFormConfig() {
        return parent::createFormConfig()
            ->setWidth(50)
            ->setFormInputs([
                'email',
                'password' => FormInput::create()
                    ->setDefaultRendererConfigurator(function (InputRenderer $renderer) {
                        $renderer
                            ->setIsRequiredForCreate(true)
                            ->setIsRequiredForEdit(false);
                    }),
                'name',
                'language' => FormInput::create()
                    ->setOptions(function () {
                        $options = array();
                        foreach (<?php echo $sectionName; ?>Config::locales() as $lang) {
                            $options[$lang] = cmfTransCustom(".language.$lang");
                        }
                        return $options;
                    })
                    ->setRenderer(function (FormInput $config) {
                        return InputRenderer::create('cmf::input/select')
                            ->required()
                            ->setOptions($config->getOptions());
                    }),
                'is_active',
                'is_superadmin',
                'role' => FormInput::create()
                    ->setOptions(function () {
                        $options = array();
                        foreach (<?php echo $sectionName; ?>Config::roles_list() as $roleId) {
                            $options[$roleId] = cmfTransCustom(".<?php echo $baseClassNameUnderscored; ?>.role.$roleId");
                        }
                        return $options;
                    })
                    ->setRenderer(function (FormInput $config) {
                        return InputRenderer::create('cmf::input/select')
                            ->required()
                            ->setOptions($config->getOptions());
                    }),
                'parent_id' => FormInput::create()
                    ->setRenderer(function () {
                        return InputRenderer::create('cmf::input/hidden');
                    })->setValueConverter(function ($value, Column $columnConfig, array $record) {
                        if (empty($record['id']) && empty($value)) {
                            return \Auth::guard()->user()->id;
                        } else {
                            return $value;
                        }
                    })
            ])
            ->setValidators(function () {
                return [
                    'role' => 'required|in:' . implode(',', <?php echo $sectionName; ?>Config::roles_list()),
                    'language' => 'required|in:' . implode(',', <?php echo $sectionName; ?>Config::locales()),
                    'is_active' => 'boolean',
                    'is_superadmin' => 'boolean',
                ];
            })
            ->addValidatorsForCreate(function () {
                return [
                    'email' => 'required|email|min:4|max:100|unique:' . <?php echo $baseClassNamePlural; ?>TableStructure::getTableName() . ',email',
                    //'login' => 'required|regex:%^[a-zA-Z0-9@_.-]+$%is|min:4|max:100|unique:' . <?php echo $baseClassNamePlural; ?>TableStructure::getTableName() . ',login',
                    'password' => 'required|min:6',
                ];
            })
            ->addValidatorsForEdit(function () {
                return [
                    'id' => FormConfig::VALIDATOR_FOR_ID,
                    'email' => 'required|email|min:4|max:100|unique:' . <?php echo $baseClassNamePlural; ?>TableStructure::getTableName() . ',email,{{id}},id',
                    //'login' => 'required|regex:%^[a-zA-Z0-9@_.-]+$%is|min:4|max:100|unique:' . <?php echo $baseClassNamePlural; ?>TableStructure::getTableName() . ',login,{{id}},id',
                    'password' => 'min:6',
                ];
            })
            ;
    }

}
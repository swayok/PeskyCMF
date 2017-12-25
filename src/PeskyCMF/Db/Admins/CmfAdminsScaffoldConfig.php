<?php

namespace PeskyCMF\Db\Admins;

use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Scaffold\DataGrid\ColumnFilter;
use PeskyCMF\Scaffold\DataGrid\DataGridColumn;
use PeskyCMF\Scaffold\Form\FormConfig;
use PeskyCMF\Scaffold\Form\FormInput;
use PeskyCMF\Scaffold\Form\InputRenderer;
use PeskyCMF\Scaffold\ItemDetails\ValueCell;
use PeskyCMF\Scaffold\NormalTableScaffoldConfig;
use PeskyORM\ORM\Column;
use Swayok\Html\Tag;

class CmfAdminsScaffoldConfig extends NormalTableScaffoldConfig {

    protected $isDetailsViewerAllowed = true;
    protected $isCreateAllowed = true;
    protected $isEditAllowed = true;
    protected $isDeleteAllowed = true;

    protected $notLoggableRecordColumns = ['password'];

    /**
     * @return CmfAdminsTable
     */
    static public function getTable() {
        return app(CmfAdminsTable::class);
    }

    static protected function getIconForMenuItem() {
        return 'fa fa-group';
    }

    protected function createDataGridConfig() {
        $loginColumn = CmfConfig::getPrimary()->user_login_column();
        return parent::createDataGridConfig()
            ->readRelations(['ParentAdmin' => ['id', 'email']])
            ->setOrderBy('id', 'desc')
            ->setColumns([
                'id',
                $loginColumn,
                'name',
                'is_active',
                'is_superadmin',
                'role' => DataGridColumn::create()
                    ->setIsSortable(true)
                    ->setValueConverter(function ($value) {
                        return cmfTransCustom(".admins.role.$value");
                    }),
                'parent_id' => DataGridColumn::create()
                    ->setType(ValueCell::TYPE_LINK),
                'created_at'
            ])
            ->setRowActions(function () {
                $actions = [];
                if (\Gate::allows('cmf_page', ['login_as'])) {
                    $actions[] = Tag::a()
                        ->setContent('<i class="glyphicon glyphicon-log-in"></i>')
                        ->setClass('row-action text-muted')
                        ->setTitle(cmfTransCustom('.admins.datagrid.action.login_as'))
                        ->setDataAttr('toggle', 'tooltip')
                        ->setDataAttr('block-datagrid', '1')
                        ->setDataAttr('action', 'request')
                        ->setDataAttr('url', cmfRouteTpl('cmf_login_as_other_admin', [], ['id'], false))
                        ->setHref('javascript: void(0)')
                        ->build();
                }
                return $actions;
            });
    }

    protected function createDataGridFilterConfig() {
        $loginColumn = CmfConfig::getPrimary()->user_login_column();
        $filters = [
            'id',
            $loginColumn,
            'email' => ColumnFilter::create(),
            'role' => ColumnFilter::create()
                ->setInputType(ColumnFilter::INPUT_TYPE_SELECT)
                ->setAllowedValues(function () {
                    $options = array();
                    foreach (CmfConfig::getPrimary()->roles_list() as $roleId) {
                        $options[$roleId] = cmfTransCustom(".admins.role.$roleId");
                    }
                    return $options;
                }),
            'name',
            'is_active',
            'is_superadmin',
            'parent_id',
            'ParentAdmin.email',
            'ParentAdmin.login',
            'ParentAdmin.name',
        ];
        if ($loginColumn === 'email') {
            unset($filters['email']);
        }
        return parent::createDataGridFilterConfig()
            ->setFilters($filters);
    }

    protected function createItemDetailsConfig() {
        $loginColumn = CmfConfig::getPrimary()->user_login_column();
        $valueCells = [
            'id',
            $loginColumn,
            'email',
            'name',
            'language' => ValueCell::create()
                ->setValueConverter(function ($value) {
                    return cmfTransCustom(".language.$value");
                }),
            'is_active',
            'role' => ValueCell::create()
                ->setValueConverter(function ($value) {
                    return cmfTransCustom(".admins.role.$value");
                }),
            'is_superadmin',
            'parent_id' => ValueCell::create()
                ->setType(ValueCell::TYPE_LINK),
            'created_at',
            'updated_at',
        ];
        if ($loginColumn === 'email') {
            unset($valueCells['email']);
        }
        return parent::createItemDetailsConfig()
            ->readRelations(['ParentAdmin'])
            ->setValueCells($valueCells)
            ->setWidth(60)
            ->setToolbarItems(function () {
                $actions = [];
                if (\Gate::allows('cmf_page', ['login_as'])) {
                    $actions[] = Tag::button()
                        ->setContent('<i class="glyphicon glyphicon-log-in"></i>')
                        ->setClass('btn btn-default')
                        ->setTitle(cmfTransCustom('.admins.item_details.action.login_as'))
                        ->setDataAttr('toggle', 'tooltip')
                        ->setDataAttr('action', 'request')
                        ->setDataAttr('url', cmfRouteTpl('cmf_login_as_other_admin', [], ['id'], false));
                }
                return $actions;
            });
    }

    protected function createFormConfig() {
        $loginColumn = CmfConfig::getPrimary()->user_login_column();
        $formInputs = [
            $loginColumn,
            'email' => FormInput::create(),
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
                    foreach (CmfConfig::getPrimary()->locales() as $lang) {
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
            'is_superadmin' => FormInput::create()
                ->setType(CmfConfig::getPrimary()->getUser()->is_superadmin ? FormInput::TYPE_BOOL : FormInput::TYPE_HIDDEN),
            'role' => FormInput::create()
                ->setOptions(function () {
                    $options = array();
                    foreach (CmfConfig::getPrimary()->roles_list() as $roleId) {
                        $options[$roleId] = cmfTransCustom(".admins.role.$roleId");
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
                    if (empty($value) && empty($record['id'])) {
                        return \Auth::guard()->user()->getAuthIdentifier();
                    } else {
                        return $value;
                    }
                })
        ];
        if ($loginColumn === 'email') {
            unset($formInputs['email']);
        }
        return parent::createFormConfig()
            ->setWidth(50)
            ->setFormInputs($formInputs)
            ->setIncomingDataModifier(function (array $data, $isCreation) {
                if (!CmfConfig::getPrimary()->getUser()->is_superadmin) {
                    if ($isCreation) {
                        $data['is_superadmin'] = false;
                    } else {
                        unset($data['is_superadmin']);
                    }
                }
                return $data;
            })
            ->setValidators(function () {
                return $this->getBaseValidators();
            })
            ->addValidatorsForCreate(function () {
                return $this->getValidatorsForCreate();
            })
            ->addValidatorsForEdit(function () {
                return $this->getValidatorsForEdit();
            })
            ;
    }

    protected function getBaseValidators() {
        return [
            'role' => 'required|in:' . implode(',', CmfConfig::getPrimary()->roles_list()),
            'language' => 'required|in:' . implode(',', CmfConfig::getPrimary()->locales()),
            'is_active' => 'boolean',
            'is_superadmin' => 'boolean',
        ];
    }

    protected function getValidatorsForEdit() {
        $validators = [
            'id' => FormConfig::VALIDATOR_FOR_ID,
            'password' => 'nullable|min:6',
            'email' => 'email|min:4|max:100|unique:' . static::getTable()->getName() . ',email,{{id}},id',
        ];
        $loginColumn = CmfConfig::getPrimary()->user_login_column();
        if ($loginColumn === 'email') {
            $validators['email'] = 'required|' . $validators['email'];
        } else {
            $validators['login'] = 'required|regex:%^[a-zA-Z0-9@_.-]+$%is|min:4|max:100|unique:' . static::getTable()->getName() . ',login,{{id}},id';
        }
        return $validators;
    }

    protected function getValidatorsForCreate() {
        $validators = [
            'password' => 'required|min:6',
            'email' => 'email|min:4|max:100|unique:' . static::getTable()->getName() . ',email',
        ];
        $loginColumn = CmfConfig::getPrimary()->user_login_column();
        if ($loginColumn === 'email') {
            $validators['email'] = 'required|' . $validators['email'];
        } else {
            $validators['login'] = 'required|regex:%^[a-zA-Z0-9@_.-]+$%is|min:4|max:100|unique:' . static::getTable()->getName() . ',login';
        }
        return $validators;
    }

}
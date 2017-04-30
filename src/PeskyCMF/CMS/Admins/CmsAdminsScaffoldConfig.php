<?php

namespace PeskyCMF\CMS\Admins;

use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Scaffold\DataGrid\ColumnFilter;
use PeskyCMF\Scaffold\DataGrid\DataGridColumn;
use PeskyCMF\Scaffold\DataGrid\FilterConfig;
use PeskyCMF\Scaffold\Form\FormConfig;
use PeskyCMF\Scaffold\Form\FormInput;
use PeskyCMF\Scaffold\Form\InputRenderer;
use PeskyCMF\Scaffold\ItemDetails\ValueCell;
use PeskyCMF\Scaffold\NormalTableScaffoldConfig;
use PeskyORM\ORM\Column;

class CmsAdminsScaffoldConfig extends NormalTableScaffoldConfig {

    protected $isDetailsViewerAllowed = true;
    protected $isCreateAllowed = true;
    protected $isEditAllowed = true;
    protected $isDeleteAllowed = true;

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
            ]);
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
            'email' => ValueCell::create(),
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
            'is_superadmin' => ValueCell::create(),
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
            ->setValueCells($valueCells);
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
            'is_superadmin',
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
                    if (empty($record['id']) && empty($value)) {
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
            ->setWidth(60)
            ->setFormInputs($formInputs)
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
            'password' => 'min:6',
            'email' => 'email|min:4|max:100|unique:' . CmsAdminsTableStructure::getTableName() . ',email,{{id}},id',
        ];
        $loginColumn = CmfConfig::getPrimary()->user_login_column();
        if ($loginColumn === 'email') {
            $validators['email'] = 'required|' . $validators['email'];
        } else {
            $validators['login'] = 'required|regex:%^[a-zA-Z0-9@_.-]+$%is|min:4|max:100|unique:' . CmsAdminsTableStructure::getTableName() . ',login,{{id}},id';
        }
        return $validators;
    }

    protected function getValidatorsForCreate() {
        $validators = [
            'password' => 'required|min:6',
            'email' => 'email|min:4|max:100|unique:' . CmsAdminsTableStructure::getTableName() . ',email',
        ];
        $loginColumn = CmfConfig::getPrimary()->user_login_column();
        if ($loginColumn === 'email') {
            $validators['email'] = 'required|' . $validators['email'];
        } else {
            $validators['login'] = 'required|regex:%^[a-zA-Z0-9@_.-]+$%is|min:4|max:100|unique:' . CmsAdminsTableStructure::getTableName() . ',login';
        }
        return $validators;
    }

}
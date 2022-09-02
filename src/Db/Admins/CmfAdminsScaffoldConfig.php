<?php

declare(strict_types=1);

namespace PeskyCMF\Db\Admins;

use PeskyCMF\CmfManager;
use PeskyCMF\Scaffold\DataGrid\ColumnFilter;
use PeskyCMF\Scaffold\DataGrid\DataGridColumn;
use PeskyCMF\Scaffold\DataGrid\DataGridConfig;
use PeskyCMF\Scaffold\DataGrid\FilterConfig;
use PeskyCMF\Scaffold\Form\FormConfig;
use PeskyCMF\Scaffold\Form\FormInput;
use PeskyCMF\Scaffold\Form\InputRenderer;
use PeskyCMF\Scaffold\ItemDetails\ItemDetailsConfig;
use PeskyCMF\Scaffold\ItemDetails\ValueCell;
use PeskyCMF\Scaffold\NormalTableScaffoldConfig;
use PeskyORM\ORM\Column;
use PeskyORM\ORM\TableInterface;
use Swayok\Html\Tag;

class CmfAdminsScaffoldConfig extends NormalTableScaffoldConfig
{
    
    protected bool $isDetailsViewerAllowed = true;
    protected bool $isCreateAllowed = true;
    protected bool $isEditAllowed = true;
    protected bool $isDeleteAllowed = true;
    
    protected ?array $notLoggableRecordColumns = ['password'];
    
    /**
     * @return CmfAdminsTable|TableInterface
     * @noinspection PhpDocSignatureInspection
     */
    public static function getTable(): TableInterface
    {
        return app()->make(CmfManager::class)->getCurrentCmfConfig()->getAuthModule()->getUsersTable();
    }
    
    protected static function getIconForMenuItem(): ?string
    {
        return 'fa fa-group';
    }
    
    protected function createDataGridConfig(): DataGridConfig
    {
        $loginColumn = $this->getUserLoginColumnName();
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
                'created_at',
            ])
            ->setRowActions(function () {
                $actions = [];
                if ($this->authGate->allows('cmf_page', ['login_as'])) {
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
    
    protected function createDataGridFilterConfig(): FilterConfig
    {
        $loginColumn = $this->getUserLoginColumnName();
        $filters = [
            'id',
            $loginColumn,
            'email' => ColumnFilter::create(),
            'role' => ColumnFilter::create()
                ->setInputType(ColumnFilter::INPUT_TYPE_SELECT)
                ->setAllowedValues(function () {
                    $options = [];
                    foreach ($this->cmfConfig->getAuthModule()->getUserRolesList() as $roleId) {
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
    
    protected function createItemDetailsConfig(): ItemDetailsConfig
    {
        $loginColumn = $this->getUserLoginColumnName();
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
                if ($this->authGate->allows('cmf_page', ['login_as'])) {
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
    
    protected function createFormConfig(): FormConfig
    {
        $loginColumn = $this->getUserLoginColumnName();
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
                    $options = [];
                    foreach ($this->cmfConfig->locales() as $lang) {
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
                ->setType($this->getUser()->is_superadmin ? FormInput::TYPE_BOOL : FormInput::TYPE_HIDDEN),
            'role' => FormInput::create()
                ->setOptions(function () {
                    $options = [];
                    foreach ($this->cmfConfig->getAuthModule()->getUserRolesList() as $roleId) {
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
                        return $this->getUser()->id;
                    } else {
                        return $value;
                    }
                }),
        ];
        if ($loginColumn === 'email') {
            unset($formInputs['email']);
        }
        return parent::createFormConfig()
            ->setWidth(50)
            ->setFormInputs($formInputs)
            ->setIncomingDataModifier(function (array $data, $isCreation) {
                if (!$this->getUser()->is_superadmin) {
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
            });
    }
    
    protected function getBaseValidators(): array
    {
        return [
            'role' => 'required|in:' . implode(',', $this->cmfConfig->getAuthModule()->getUserRolesList()),
            'language' => 'required|in:' . implode(',', $this->cmfConfig->locales()),
            'is_active' => 'boolean',
            'is_superadmin' => 'boolean',
        ];
    }
    
    protected function getValidatorsForEdit(): array
    {
        $validators = [
            'id' => FormConfig::VALIDATOR_FOR_ID,
            'password' => 'nullable|min:6',
            'email' => 'email|min:4|max:100|unique:' . static::getTable()->getName() . ',email,{{id}},id',
        ];
        $loginColumn = $this->getUserLoginColumnName();
        if ($loginColumn === 'email') {
            $validators['email'] = 'required|' . $validators['email'];
        } else {
            $validators['login'] = 'required|regex:%^[a-zA-Z0-9@_.-]+$%is|min:4|max:100|unique:' . static::getTable()->getName() . ',login,{{id}},id';
        }
        return $validators;
    }
    
    protected function getValidatorsForCreate(): array
    {
        $validators = [
            'password' => 'required|min:6',
            'email' => 'email|min:4|max:100|unique:' . static::getTable()->getName() . ',email',
        ];
        $loginColumn = $this->getUserLoginColumnName();
        if ($loginColumn === 'email') {
            $validators['email'] = 'required|' . $validators['email'];
        } else {
            $validators['login'] = 'required|regex:%^[a-zA-Z0-9@_.-]+$%is|min:4|max:100|unique:' . static::getTable()->getName() . ',login';
        }
        return $validators;
    }
    
    protected function getUserLoginColumnName(): string
    {
        return $this->cmfConfig->getAuthModule()->getUserLoginColumnName();
    }
    
}
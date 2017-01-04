<?php


namespace PeskyCMF\Scaffold;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Http\CmfJsonResponse;
use PeskyCMF\HttpCode;
use PeskyCMF\Scaffold\DataGrid\DataGridConfig;
use PeskyCMF\Scaffold\DataGrid\FilterConfig;
use PeskyCMF\Scaffold\Form\FormConfig;
use PeskyCMF\Scaffold\ItemDetails\ItemDetailsConfig;
use PeskyCMF\Traits\DataValidationHelper;
use PeskyORM\Core\DbExpr;
use PeskyORM\Exception\InvalidDataException;
use PeskyORM\ORM\TableInterface;

abstract class ScaffoldConfig {

    use DataValidationHelper;

    /** @var TableInterface */
    protected $table;
    /** @var string */
    protected $tableNameForRoutes;
    /** @var DataGridConfig */
    protected $dataGridConfig = null;
    /** @var FilterConfig */
    protected $dataGridFilterConfig = null;
    /** @var ItemDetailsConfig */
    protected $itemDetailsConfig = null;
    /** @var FormConfig */
    protected $formConfig = null;

    /** @var bool */
    protected $isDetailsViewerAllowed = true;
    /** @var bool */
    protected $isCreateAllowed = true;
    /** @var bool */
    protected $isEditAllowed = true;
    /** @var bool */
    protected $isDeleteAllowed = true;
    /**
     * Path to localization of views.
     * Usage: see $this->getLocalizationBasePath() method.
     * By default if $localizationKey is empty - cmf::scaffold.templates view
     * will call $this->getLocalizationBasePath($tableNameForRoutes)
     * @return null|string
     */
    protected $viewsLocalizationKey = null;

    /**
     * ScaffoldConfig constructor.
     * @param TableInterface $table
     * @param string $tableNameForRoutes - table name to be used to build routes to resources of the $table
     */
    public function __construct(TableInterface $table, $tableNameForRoutes) {
        $this->table = $table;
        $this->tableNameForRoutes = $tableNameForRoutes;
    }

    /**
     * @return TableInterface
     */
    public function getTable() {
        return $this->table;
    }

    /**
     * @return string
     */
    public function getTableNameForRoutes() {
        return $this->tableNameForRoutes;
    }

    /**
     * @return Request
     */
    public function getRequest() {
        return request();
    }

    /**
     * @return array
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     * @throws ScaffoldSectionException
     */
    public function getConfigs() {
        $configs = [
            'model' => $this->getTable(),
            'scaffoldConfig' => $this
        ];
        $configs['dataGridConfig'] = $this->getDataGridConfig();
        if (!($configs['dataGridConfig'] instanceof DataGridConfig)) {
            throw new ScaffoldSectionException(null, 'createDataGridConfig() should return instance of DataGridConfig class');
        }
        $configs['dataGridFilterConfig'] = $this->getDataGridFilterConfig();
        if (!($configs['dataGridFilterConfig'] instanceof FilterConfig)) {
            throw new ScaffoldSectionException(null, 'createDataGridFilterConfig() should return instance of FilterConfig class');
        }
        $configs['itemDetailsConfig'] = $this->getItemDetailsConfig();
        if (!($configs['itemDetailsConfig'] instanceof ItemDetailsConfig)) {
            throw new ScaffoldSectionException(null, 'createItemDetailsConfig() should return instance of ItemDetailsConfig class');
        }
        $configs['formConfig'] = $this->getFormConfig();
        if (!($configs['formConfig'] instanceof FormConfig)) {
            throw new ScaffoldSectionException(null, 'createFormConfig() should return instance of FormConfig class');
        }
        return $configs;
    }

    /**
     * @return DataGridConfig
     */
    protected function createDataGridConfig() {
        return DataGridConfig::create($this->getTable(), $this);
    }

    /**
     * @return FilterConfig
     */
    protected function createDataGridFilterConfig() {
        return FilterConfig::create($this->getTable());
    }

    /**
     * @return ItemDetailsConfig
     */
    protected function createItemDetailsConfig() {
        return ItemDetailsConfig::create($this->getTable(), $this);
    }

    /**
     * @return FormConfig
     */
    protected function createFormConfig() {
        return FormConfig::create($this->getTable(), $this);
    }

    /**
     * @return DataGridConfig
     * @throws \PeskyCMF\Scaffold\ScaffoldSectionException
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     */
    public function getDataGridConfig() {
        if (empty($this->dataGridConfig)) {
            $this->dataGridConfig = $this->createDataGridConfig();
            $this->dataGridConfig->finish();
        }
        return $this->dataGridConfig;
    }

    /**
     * @return FilterConfig
     */
    public function getDataGridFilterConfig() {
        if (empty($this->dataGridFilterConfig)) {
            $this->dataGridFilterConfig = $this->createDataGridFilterConfig();
            $this->dataGridFilterConfig->finish();
        }
        return $this->dataGridFilterConfig;
    }

    /**
     * @return ItemDetailsConfig
     */
    public function getItemDetailsConfig() {
        if (empty($this->itemDetailsConfig)) {
            $this->itemDetailsConfig = $this->createItemDetailsConfig();
            $this->itemDetailsConfig->finish();
        }
        return $this->itemDetailsConfig;
    }

    /**
     * @return FormConfig
     */
    public function getFormConfig() {
        if (empty($this->formConfig)) {
            $this->formConfig = $this->createFormConfig();
            $this->formConfig->finish();
        }
        return $this->formConfig;
    }

    /**
     * @return boolean
     */
    public function isCreateAllowed() {
        return $this->isCreateAllowed;
    }

    /**
     * @return boolean
     */
    public function isEditAllowed() {
        return $this->isEditAllowed;
    }

    /**
     * @return boolean
     */
    public function isDetailsViewerAllowed() {
        return $this->isDetailsViewerAllowed;
    }

    /**
     * @return boolean
     */
    public function isDeleteAllowed() {
        return $this->isDeleteAllowed;
    }

    /**
     * Detects if $record deletable or not.
     * Used in child classes to add possibility to disable action depending on record data
     * @param array $record
     * @return bool
     */
    public function isRecordDeleteAllowed(array $record) {
        return $this->isDeleteAllowed();
    }

    /**
     * Detects if $record editable or not.
     * Used in child classes to add possibility to disable action depending on record data
     * @param array $record
     * @return bool
     */
    public function isRecordEditAllowed(array $record) {
        return $this->isEditAllowed();
    }

    /**
     * Detects if $record details can be displayed or not.
     * Used in child classes to add possibility to disable action depending on record data
     * @param array $record
     * @return bool
     */
    public function isRecordDetailsAllowed(array $record) {
        return $this->isDetailsViewerAllowed();
    }

    /**
     * Base path to localization of scaffold views for this resource
     * @param $defaultLocalizationKey
     * @return string
     */
    public function getLocalizationBasePath($defaultLocalizationKey) {
        $key = $this->viewsLocalizationKey ?: $defaultLocalizationKey;
        return '.' . $key;
    }

    public function renderTemplates() {
        return view(
            CmfConfig::getInstance()->scaffold_templates_view(),
            array_merge(
                $this->getConfigs(),
                ['tableNameForRoutes' => $this->getTableNameForRoutes()]
            )
        )->render();
    }

    public function getRecordsForDataGrid() {
        $request = $this->getRequest();
        $dataGridConfig = $this->getDataGridConfig();
        $dataGridFilterConfig = $this->getDataGridFilterConfig();
        $conditions = [
            'LIMIT' => $request->query('length', $dataGridConfig->getRecordsPerPage()),
            'OFFSET' => (int)$request->query('start', 0),
            'ORDER' => []
        ];
        $conditions = array_merge($dataGridConfig->getSpecialConditions(), $conditions);
        $searchInfo = $request->query('search');
        if (!empty($searchInfo) && !empty($searchInfo['value'])) {
            $search = json_decode($searchInfo['value'], true);
            if (!empty($search) && is_array($search) && !empty($search['r'])) {
                $conditions = array_replace($dataGridFilterConfig->buildConditionsFromSearchRules($search), $conditions);
            }
        }
        $order = $request->query('order', [[
            'column' => $dataGridConfig->getOrderBy(),
            'dir' => $dataGridConfig->getOrderDirection()
        ]]);
        $columns = $request->query('columns', array());
        /** @var array $order */
        foreach ($order as $config) {
            if (is_numeric($config['column']) && !empty($columns[$config['column']])) {
                $config['column'] = $columns[$config['column']]['name'];
            }
            if (!empty($config['column']) && !is_numeric($config['column'])) {
                if ($config['column'] instanceof DbExpr) {
                    $conditions['ORDER'][] = DbExpr::create($config['column']->get() . ' ' . $config['dir']);
                } else {
                    $conditions['ORDER'][$config['column']] = $config['dir'];
                }
            }
        }
        $columnsToSelect = array_merge(
            array_keys($dataGridConfig->getViewersLinkedToDbColumns()),
            $dataGridConfig->getRelationsToRead()
        );
        $result = $this->getTable()->select($columnsToSelect, $conditions);
        $records = [];
        if ($result->count()) {
            $records = $dataGridConfig->prepareRecords($result->toArrays());
        }
        return cmfJsonResponse()->setData([
            'draw' => $request->query('draw'),
            'recordsTotal' => $result->countTotal(),
            'recordsFiltered' => $result->countTotal(),
            'data' => $records,
        ]);
    }

    public function getRecordValuesForFormInputs($id = null) {
        $isItemDetails = (bool)$this->getRequest()->query('details', false);
        $table = $this->getTable();
        if (!$this->isDetailsViewerAllowed()) {
            return $this->makeAccessDeniedReponse(
                cmfTransGeneral('.action.' . ($isItemDetails ? 'item_details' : 'edit') . '.forbidden'));
        }
        $object = $table->newRecord();
        if (count($object::getPrimaryKeyColumn()->validateValue($id)) > 0) {
            return $this->makeRecordNotFoundResponse($table);
        }
        if ($isItemDetails) {
            $actionConfig = $this->getItemDetailsConfig();
        } else {
            $actionConfig = $this->getFormConfig();
        }
        $conditions = $actionConfig->getSpecialConditions();
        $conditions[$table->getPkColumnName()] = $id;
        if (!$object->fromDb($conditions, [], $actionConfig->getRelationsToRead())->existsInDb()) {
            return $this->makeRecordNotFoundResponse($table);
        }
        $data = $object->toArray([], $actionConfig->getRelationsToRead(), false);
        if (
            (
                $isItemDetails
                && !$this->isRecordDetailsAllowed($data)
            )
            ||
            (
                !$isItemDetails
                && !$this->isRecordEditAllowed($data)
            )
        ) {
            return $this->makeAccessDeniedReponse(
                cmfTransGeneral('.action.' . ($isItemDetails ? 'item_details' : 'edit') . '.forbidden_for_record')
            );
        }
        return cmfJsonResponse()->setData($data);
    }

    public function getDefaultValuesForFormInputs() {
        /** @var FormConfig $config */
        if (!$this->isCreateAllowed() && !$this->isEditAllowed()) {
            return $this->makeAccessDeniedReponse(cmfTransGeneral('.action.create.forbidden'));
        }
        $formConfig = $this->getFormConfig();
        $data = $formConfig->alterDefaultValues($this->getTable()->newRecord()->getDefaults([], false, true));
        return new JsonResponse($formConfig->prepareRecord($data));
    }

    public function getHtmlOptionsForFormInputs() {
        if (!$this->isEditAllowed() && !$this->isCreateAllowed()) {
            return $this->makeAccessDeniedReponse(cmfTransGeneral('.action.edit.forbidden'));
        }
        $columnsOptions = $this->getFormConfig()->loadOptions($this->getRequest()->query('id'));
        foreach ($columnsOptions as $columnName => $options) {
            if (is_array($options)) {
                $columnsOptions[$columnName] = $this->renderOptionsForSelectInput($options);
            } else if (!is_string($options)) {
                unset($columnsOptions[$columnName]);
            }
        }
        return cmfJsonResponse()->setData($columnsOptions);
    }

    /**
     * @param array $options
     * @return string
     * @throws \Swayok\Html\HtmlTagException
     */
    protected function renderOptionsForSelectInput(array $options) {
        $ret = '';
        foreach ($options as $value => $label) {
            if (!is_array($label)) {
                $ret .= '<option value="' . htmlentities($value) . '">' . $label . '</option>';
            } else {
                $ret .= '<optgroup label="' . htmlentities($value) . '">' . $this->renderOptionsForSelectInput($label) . '</optgroup>';
            }
        }
        return $ret;
    }

    public function addRecord() {
        if (!$this->isCreateAllowed()) {
            return $this->makeAccessDeniedReponse(cmfTransGeneral('.action.create.forbidden'));
        }
        $table = $this->getTable();
        $formConfig = $this->getFormConfig();
        $data = array_intersect_key($this->getRequest()->all(), $formConfig->getValueViewers());
        $errors = $formConfig->validateDataForCreate($data);
        if (count($errors) !== 0) {
            return $this->sendValidationErrorsResponse($errors);
        }
        unset($data[$table->getPkColumnName()]);
        if ($formConfig->hasBeforeSaveCallback()) {
            $data = call_user_func($formConfig->getBeforeSaveCallback(), true, $data, $formConfig);
            if (empty($data)) {
                throw new ScaffoldException('Empty $data received from beforeSave callback');
            }
            if ($formConfig->shouldRevalidateDataAfterBeforeSaveCallback(true)) {
                // revalidate
                $errors = $formConfig->validateDataForCreate($data, [], true);
                if (count($errors) !== 0) {
                    return $this->sendValidationErrorsResponse($errors);
                }
            }
        }
        unset($data[$table->getPkColumnName()]); //< to be 100% sure =)
        if (!empty($data)) {
            $table::beginTransaction();
            try {
                $dataToSave = array_diff_key($data, $formConfig->getStandaloneViewers());
                $object = $table->newRecord()->fromData($dataToSave, false);
                $success = $object->save();
                if (!$success) {
                    $table::rollBackTransaction();
                    return cmfJsonResponse(HttpCode::SERVER_ERROR)
                        ->setMessage(cmfTransGeneral('.form.failed_to_save_data'));
                } else if ($formConfig->hasAfterSaveCallback()) {
                    $success = call_user_func($formConfig->getAfterSaveCallback(), true, $data, $object, $formConfig);
                    if ($success instanceof \Symfony\Component\HttpFoundation\JsonResponse) {
                        if ($success->getStatusCode() < 400) {
                            $table::commitTransaction();
                        } else {
                            $table::rollBackTransaction();
                        }
                        return $success;
                    } else if ($success !== true) {
                        $table::rollBackTransaction();
                        throw new ScaffoldException(
                            'afterSave callback must return true or instance of \Symfony\Component\HttpFoundation\JsonResponse'
                        );
                    }
                }
                $table::commitTransaction();
            } catch (InvalidDataException $exc) {
                if ($table->inTransaction()) {
                    $table::rollBackTransaction();
                }
                return $this->sendValidationErrorsResponse($exc->getErrors());
            } catch (\Exception $exc) {
                if ($table->inTransaction()) {
                    $table::rollBackTransaction();
                }
                throw $exc;
            }
        }
        return cmfJsonResponse()->setMessage(cmfTransGeneral('.form.resource_created_successfully'));
    }

    public function updateRecord() {
        if (!$this->isEditAllowed()) {
            return $this->makeAccessDeniedReponse(cmfTransGeneral('.action.edit.forbidden'));
        }
        $table = $this->getTable();
        $formConfig = $this->getFormConfig();
        $expectedFields = array_keys($formConfig->getValueViewers());
        $expectedFields[] = $table->getPkColumnName();
        $data = array_intersect_key($this->getRequest()->all(), array_flip($expectedFields));
        $errors = $formConfig->validateDataForEdit($data);
        if (count($errors) !== 0) {
            return $this->sendValidationErrorsResponse($errors);
        }
        if (!$this->getRequest()->input($table->getPkColumnName())) {
            return $this->makeRecordNotFoundResponse($table);
        }
        $id = $this->getRequest()->input($table->getPkColumnName());
        $object = $table->newRecord();
        if (count($object::getPrimaryKeyColumn()->validateValue($id)) > 0) {
            return $this->makeRecordNotFoundResponse($table);
        }
        $conditions = $formConfig->getSpecialConditions();
        $conditions[$table->getPkColumnName()] = $id;
        if (!$object->fromDb($conditions)->existsInDb()) {
            return $this->makeRecordNotFoundResponse($table);
        }
        if (!$this->isRecordEditAllowed($object->toArrayWithoutFiles())) {
            return $this->makeAccessDeniedReponse(cmfTransGeneral('.action.edit.forbidden_for_record'));
        }
        if ($formConfig->hasBeforeSaveCallback()) {
            $data = call_user_func($formConfig->getBeforeSaveCallback(), false, $data, $formConfig);
            if (empty($data)) {
                throw new ScaffoldException('Empty $data received from beforeSave callback');
            }
            if ($formConfig->shouldRevalidateDataAfterBeforeSaveCallback(false)) {
                // revalidate
                $errors = $formConfig->validateDataForEdit($data, [], true);
                if (count($errors) !== 0) {
                    return $this->sendValidationErrorsResponse($errors);
                }
            }
        }
        unset($data[$table->getPkColumnName()]);
        if (!empty($data)) {
            $table::beginTransaction();
            try {
                $dbData = array_diff_key($data, $formConfig->getStandaloneViewers());
                $success = $object->begin()->updateValues($dbData)->commit();
                if (!$success) {
                    $table::rollBackTransaction();
                    return cmfJsonResponse(HttpCode::SERVER_ERROR)
                        ->setMessage(cmfTransGeneral('.form.failed_to_save_data'));
                } else if ($formConfig->hasAfterSaveCallback()) {
                    $success = call_user_func($formConfig->getAfterSaveCallback(), false, $data, $object, $formConfig);
                    if ($success instanceof \Symfony\Component\HttpFoundation\JsonResponse) {
                        if ($success->getStatusCode() < 400) {
                            $table::commitTransaction();
                        } else {
                            $table::rollBackTransaction();
                        }
                        return $success;
                    } else if ($success !== true) {
                        $table::rollBackTransaction();
                        throw new ScaffoldException(
                            'afterSave callback must return true or instance of \Symfony\Component\HttpFoundation\JsonResponse'
                        );
                    }
                }
                $table::commitTransaction();
            } catch (InvalidDataException $exc) {
                if ($table->inTransaction()) {
                    $table::rollBackTransaction();
                }
                return $this->sendValidationErrorsResponse($exc->getErrors());
            } catch (\Exception $exc) {
                if ($table->inTransaction()) {
                    $table::rollBackTransaction();
                }
                throw $exc;
            }
        }
        return cmfJsonResponse()
            ->setMessage(cmfTransGeneral('.form.resource_updated_successfully'));
    }

    public function updateBulkOfRecords() {
        if (!$this->isEditAllowed()) {
            return $this->makeAccessDeniedReponse(cmfTransGeneral('.action.edit.forbidden'));
        }
        $table = $this->getTable();
        $formConfig = $this->getFormConfig();
        $data = $this->getRequest()->only($formConfig->getBulkEditableColumns());
        if (empty($data)) {
            return cmfJsonResponse(HttpCode::INVALID)
                ->setMessage(cmfTransGeneral('.action.bulk_edit.no_data_to_save'));
        }
        $errors = $formConfig->validateDataForBulkEdit($data);
        if (count($errors) !== 0) {
            return $this->sendValidationErrorsResponse($errors);
        }
        if ($formConfig->hasBeforeBulkEditDataSaveCallback()) {
            $data = call_user_func($formConfig->getBeforeBulkEditDataSaveCallback(), $data, $formConfig);
            if (empty($data)) {
                throw new ScaffoldException('Empty $data received from beforeBulkEditDataSave callback');
            }
            if ($formConfig->shouldRevalidateDataAfterBeforeSaveCallback(false)) {
                // revalidate
                $errors = $formConfig->validateDataForBulkEdit($data, [], true);
                if (count($errors) !== 0) {
                    return $this->sendValidationErrorsResponse($errors);
                }
            }
        }
        $conditions = $this->getSelectConditionsForBulkActions('_');
        if (!is_array($conditions)) {
            return $conditions; //< response
        }
        $table::beginTransaction();
        $updatedCount = $table->update($data, $conditions);
        if ($formConfig->hasAfterBulkEditDataAfterSaveCallback()) {
            $success = call_user_func($formConfig->getAfterBulkEditDataAfterSaveCallback(), $data);
            if ($success instanceof \Symfony\Component\HttpFoundation\JsonResponse) {
                if ($success->getStatusCode() < 400) {
                    $table::commitTransaction();
                } else {
                    $table::rollBackTransaction();
                }
                return $success;
            } else if ($success !== true) {
                $table::rollBackTransaction();
                throw new ScaffoldException(
                    'afterBulkEditDataAfterSave callback must return true or instance of \Symfony\Component\HttpFoundation\JsonResponse'
                );
            }
        }
        $table::commitTransaction();
        $message = $updatedCount
            ? cmfTransGeneral('.action.bulk_edit.success', ['count' => $updatedCount])
            : cmfTransGeneral('.action.bulk_edit.nothing_updated');
        return cmfJsonResponse()
            ->setMessage($message)
            ->goBack(routeToCmfItemsTable($this->getTableNameForRoutes()));
    }

    public function deleteRecord($id) {
        if (!$this->isDeleteAllowed()) {
            return $this->makeAccessDeniedReponse(cmfTransGeneral('.action.delete.forbidden'));
        }
        $table = $this->getTable();
        $object = $table->newRecord();
        if (count($object::getPrimaryKeyColumn()->validateValue($id)) > 0) {
            return $this->makeRecordNotFoundResponse($table);
        }
        $formConfig = $this->getFormConfig();
        $conditions = $formConfig->getSpecialConditions();
        $conditions[$table->getPkColumnName()] = $id;
        if (!$object->fromDb($conditions)->existsInDb()) {
            return $this->makeRecordNotFoundResponse($table);
        }
        if (!$this->isRecordDeleteAllowed($object->toArrayWithoutFiles())) {
            return $this->makeAccessDeniedReponse(cmfTransGeneral('.action.delete.forbidden_for_record'));
        }
        $object->delete();
        return cmfJsonResponse()
            ->setMessage(cmfTransGeneral('.action.delete.success'))
            ->goBack(routeToCmfItemsTable($this->getTableNameForRoutes()));
    }

    public function deleteBulkOfRecords() {
        if (!$this->isDeleteAllowed()) {
            return $this->makeAccessDeniedReponse(cmfTransGeneral('.action.delete.forbidden'));
        }
        $conditions = $this->getSelectConditionsForBulkActions($this->getRequest());
        if (!is_array($conditions)) {
            return $conditions; //< response
        }
        $deletedCount = $this->getTable()->delete($conditions);
        $message = $deletedCount
            ? cmfTransGeneral('.action.delete_bulk.success', ['count' => $deletedCount])
            : cmfTransGeneral('.action.delete_bulk.nothing_deleted');
        return cmfJsonResponse()
            ->setMessage($message)
            ->goBack(routeToCmfItemsTable($this->getTableNameForRoutes()));
    }

    /**
     * @param string $inputNamePrefix - input name prefix
     *      For example if you use '_ids' instead of 'ids' - use prefix '_'
     * @return array|Response
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    protected function getSelectConditionsForBulkActions($inputNamePrefix = '') {
        $specialConditions = $this->getFormConfig()->getSpecialConditions();
        $conditions = $specialConditions;
        $idsField = $inputNamePrefix . 'ids';
        $conditionsField = $inputNamePrefix . 'conditions';
        if ($this->getRequest()->has($idsField)) {
            $this->validate($this->getRequest()->input(), [
                $idsField => 'required|array',
                $idsField . '.*' => 'integer|min:1'
            ]);
            $conditions[$this->getTable()->getPkColumnName()] = $this->getRequest()->input($idsField);
        } else if ($this->getRequest()->has($conditionsField)) {
            $this->validate($this->getRequest()->input(), [
                $conditionsField => 'string|regex:%^[\{\[].*[\}\]]$%s',
            ]);
            $encodedConditions = $this->getRequest()->input($conditionsField) !== ''
                ? json_decode($this->getRequest()->input($conditionsField), true)
                : [];
            if ($encodedConditions === false || !is_array($encodedConditions) || empty($encodedConditions['r'])) {
                return cmfJsonResponseForValidationErrors([$conditionsField => 'JSON expected']);
            }
            if (!empty($encodedConditions)) {
                //$dataGridConfig = $this->getDataGridConfig();
                // todo: take $dataGridConfig->contains in account
                $filterConditions = $this
                    ->getDataGridFilterConfig()
                    ->buildConditionsFromSearchRules($encodedConditions);
                $conditions = array_merge($filterConditions, $specialConditions);
            }
        } else {
            return cmfJsonResponseForValidationErrors([
                $idsField => 'List of items IDs of filtering conditions expected',
                $conditionsField => 'List of items IDs of filtering conditions expected',
            ]);
        }
        return $conditions;
    }

    /**
     * @param TableInterface $table
     * @param null|string $message
     * @return $this
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     */
    protected function makeRecordNotFoundResponse(TableInterface $table, $message = null) {
        if (empty($message)) {
            $message = cmfTransGeneral('.error.resource_item_not_found');
        }
        return cmfJsonResponseForHttp404(
            routeToCmfItemsTable($this->getTableNameForRoutes()),
            $message
        );
    }

    /**
     * @param string $message
     * @return CmfJsonResponse
     */
    protected function makeAccessDeniedReponse($message) {
        return cmfJsonResponse(HttpCode::FORBIDDEN)
            ->setMessage($message)
            ->goBack(routeToCmfItemsTable($this->getTableNameForRoutes()));
    }

}
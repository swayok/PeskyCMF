<?php

namespace PeskyCMF\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Http\Request;
use PeskyCMF\HttpCode;
use PeskyCMF\Scaffold\Form\FormConfig;
use PeskyCMF\Scaffold\ScaffoldException;
use PeskyCMF\Scaffold\ScaffoldConfig;
use PeskyCMF\Traits\DataValidationHelper;
use PeskyORM\Core\DbExpr;
use PeskyORM\Exception\InvalidDataException;
use PeskyORM\ORM\TableInterface;
use Swayok\Html\Tag;

class CmfScaffoldApiController extends Controller {

    use DataValidationHelper;

    protected $tableNameForRoutes = null;
    protected $table = null;
    protected $scaffoldConfig = null;

    /**
     * @return TableInterface
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     */
    public function getTable() {
        if ($this->table === null) {
            $this->table = CmfConfig::getInstance()->getTableByUnderscoredName($this->getTableNameForRoutes());
        }
        return $this->table;
    }

    /**
     * @return ScaffoldConfig
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     * @throws \UnexpectedValueException
     */
    public function getScaffoldConfig() {
        if ($this->scaffoldConfig === null) {
            $cmfConfig = CmfConfig::getInstance();
            $customScaffoldConfig = $cmfConfig::getScaffoldConfig($this->getTable(), $this->getTableNameForRoutes());
            if ($customScaffoldConfig instanceof ScaffoldConfig) {
                $this->scaffoldConfig = $customScaffoldConfig;
            } else if (!empty($customScaffoldConfig)) {
                throw new \UnexpectedValueException(
                    get_class($cmfConfig) . '::getScaffoldConfig() must return null or instance of ScaffoldConfig class. '
                        . (is_object($customScaffoldConfig) ? get_class($customScaffoldConfig) : gettype($customScaffoldConfig))
                        . ' Received'
                );
            }
        }
        return $this->scaffoldConfig;
    }

    /**
     * @return string
     * @throws ScaffoldException
     */
    public function getTableNameForRoutes() {
        if ($this->tableNameForRoutes === null) {
            if (!request()->route()->hasParameter('table_name')) {
                throw new ScaffoldException('There is no [table_name] parameter in current route');
            }
            $tableName = request()->route()->parameter('table_name');
            if (empty($tableName)) {
                abort(404, 'Table name not found in route');
            }
            $this->tableNameForRoutes = $tableName;
        }
        return $this->tableNameForRoutes;
    }

    public function __construct() {

    }

    public function getTemplates() {
        return view(
            CmfConfig::getInstance()->scaffold_templates_view(),
            array_merge(
                $this->getScaffoldConfig()->getConfigs(),
                ['tableNameForRoutes' => $this->getTableNameForRoutes()]
            )
        )->render();
    }

    public function getItemsList(Request $request) {
        return new JsonResponse($this->getDataGridItems($request));
    }

    public function getItem(Request $request, $tableName, $id = null) {
        $isItemDetails = (bool)$request->query('details', false);
        $table = $this->getTable();
        if (!$this->getScaffoldConfig()->isDetailsViewerAllowed()) {
            return cmfServiceJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(cmfTransGeneral(
                    '.action.' . ($isItemDetails ? 'item_details' : 'edit') . '.forbidden'
                ))
                ->goBack(routeToCmfItemsTable($this->getTableNameForRoutes()));
        }
        $object = $table->newRecord();
        if (count($object::getPrimaryKeyColumn()->validateValue($id)) > 0) {
            return $this->sendItemNotFoundResponse($table);
        }
        if ($isItemDetails) {
            $actionConfig = $this->getScaffoldConfig()->getItemDetailsConfig();
        } else {
            $actionConfig = $this->getScaffoldConfig()->getFormConfig();
        }
        $conditions = $actionConfig->getSpecialConditions();
        $conditions[$table->getPkColumnName()] = $id;
        if (!$object->fromDb($conditions, [], $actionConfig->getContains())->existsInDb()) {
            return $this->sendItemNotFoundResponse($table);
        }
        $data = $object->toArray([], $actionConfig->getContains(), false);
        if (
            (
                $isItemDetails
                && !$this->getScaffoldConfig()->isRecordDetailsAllowed($data)
            )
            ||
            (
                !$isItemDetails
                && !$this->getScaffoldConfig()->isRecordEditAllowed($data)
            )
        ) {
            return cmfServiceJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(cmfTransGeneral(
                    '.action.' . ($isItemDetails ? 'item_details' : 'edit') . '.forbidden_for_record'
                ))
                ->goBack(routeToCmfItemsTable($this->getTableNameForRoutes()));
        }
        return new JsonResponse($actionConfig->prepareRecord($data));
    }

    public function getItemDefaults() {
        /** @var FormConfig $config */
        if (!$this->getScaffoldConfig()->isCreateAllowed() && !$this->getScaffoldConfig()->isEditAllowed()) {
            return cmfServiceJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(cmfTransGeneral('.action.create.forbidden'))
                ->goBack(routeToCmfItemsTable($this->getTableNameForRoutes()));
        }
        $formConfig = $this->getScaffoldConfig()->getFormConfig();
        $data = $formConfig->alterDefaultValues($this->getTable()->newRecord()->getDefaults([], false, true));
        return new JsonResponse($formConfig->prepareRecord($data));
    }

    public function getOptions(Request $request) {
        if (!$this->getScaffoldConfig()->isEditAllowed() && !$this->getScaffoldConfig()->isCreateAllowed()) {
            return cmfServiceJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(cmfTransGeneral('.action.edit.forbidden'))
                ->goBack(routeToCmfItemsTable($this->getTableNameForRoutes()));
        }
        $optionsByFields = $this->getScaffoldConfig()->getFormConfig()->loadOptions($request->query('id'));
        foreach ($optionsByFields as $fieldName => $fieldOptions) {
            if (is_array($fieldOptions)) {
                $optionsByFields[$fieldName] = $this->buildFieldOptions($fieldOptions);
            } else if (!is_string($fieldOptions)) {
                unset($optionsByFields[$fieldName]);
            }
        }
        return new JsonResponse($optionsByFields);
    }

    public function addItem(Request $request) {
        if (!$this->getScaffoldConfig()->isCreateAllowed()) {
            return cmfServiceJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(cmfTransGeneral('.action.create.forbidden'))
                ->goBack(routeToCmfItemsTable($this->getTableNameForRoutes()));
        }
        $table = $this->getTable();
        $formConfig = $this->getScaffoldConfig()->getFormConfig();
        $data = array_intersect_key($request->data(), $formConfig->getFields());
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
                $dataToSave = array_diff_key($data, $formConfig->getNonDbFields());
                $object = $table->newRecord()->fromData($dataToSave, false);
                $success = $object->save();
                if (!$success) {
                    $table::rollBackTransaction();
                    return cmfServiceJsonResponse(HttpCode::SERVER_ERROR)
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
        return cmfServiceJsonResponse()
            ->setMessage(cmfTransGeneral('.form.resource_created_successfully'));
    }

    public function updateItem(Request $request) {
        if (!$this->getScaffoldConfig()->isEditAllowed()) {
            return cmfServiceJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(cmfTransGeneral('.action.edit.forbidden'))
                ->goBack(routeToCmfItemsTable($this->getTableNameForRoutes()));
        }
        $table = $this->getTable();
        $formConfig = $this->getScaffoldConfig()->getFormConfig();
        $expectedFields = array_keys($formConfig->getFields());
        $expectedFields[] = $table->getPkColumnName();
        $data = array_intersect_key($request->data(), array_flip($expectedFields));
        $errors = $formConfig->validateDataForEdit($data);
        if (count($errors) !== 0) {
            return $this->sendValidationErrorsResponse($errors);
        }
        if (!$request->data($table->getPkColumnName())) {
            return $this->sendItemNotFoundResponse($table);
        }
        $id = $request->data($table->getPkColumnName());
        $object = $table->newRecord();
        if (count($object::getPrimaryKeyColumn()->validateValue($id)) > 0) {
            return $this->sendItemNotFoundResponse($table);
        }
        $conditions = $formConfig->getSpecialConditions();
        $conditions[$table->getPkColumnName()] = $id;
        if (!$object->fromDb($conditions)->existsInDb()) {
            return $this->sendItemNotFoundResponse($table);
        }
        if (!$this->getScaffoldConfig()->isRecordEditAllowed($object->toArrayWithoutFiles())) {
            return cmfServiceJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(cmfTransGeneral('.action.edit.forbidden_for_record'))
                ->goBack(routeToCmfItemsTable($this->getTableNameForRoutes()));
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
                $dbData = array_diff_key($data, $formConfig->getNonDbFields());
                $success = $object->begin()->updateValues($dbData)->commit();
                if (!$success) {
                    $table::rollBackTransaction();
                    return cmfServiceJsonResponse(HttpCode::SERVER_ERROR)
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
        return cmfServiceJsonResponse()
            ->setMessage(cmfTransGeneral('.form.resource_updated_successfully'));
    }

    public function updateBulk(Request $request) {
        if (!$this->getScaffoldConfig()->isEditAllowed()) {
            return cmfServiceJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(cmfTransGeneral('.action.edit.forbidden'))
                ->goBack(routeToCmfItemsTable($this->getTableNameForRoutes()));
        }
        $table = $this->getTable();
        $formConfig = $this->getScaffoldConfig()->getFormConfig();
        $expectedFields = array_keys($formConfig->getBulkEditableFields());
        $data = array_intersect_key($request->data(), array_flip($expectedFields));
        if (empty($data)) {
            return cmfServiceJsonResponse(HttpCode::INVALID)
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
        $conditions = $this->getConditionsForBulkActions($request, $table, '_');
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
        return cmfServiceJsonResponse()
            ->setMessage($message)
            ->goBack(routeToCmfItemsTable($this->getTableNameForRoutes()));
    }

    public function deleteItem($tableName, $id) {
        if (!$this->getScaffoldConfig()->isDeleteAllowed()) {
            return cmfServiceJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(cmfTransGeneral('.action.delete.forbidden'))
                ->goBack(routeToCmfItemsTable($this->getTableNameForRoutes()));
        }
        $table = $this->getTable();
        $object = $table->newRecord();
        if (count($object::getPrimaryKeyColumn()->validateValue($id)) > 0) {
            return $this->sendItemNotFoundResponse($table);
        }
        $formConfig = $this->getScaffoldConfig()->getFormConfig();
        $conditions = $formConfig->getSpecialConditions();
        $conditions[$table->getPkColumnName()] = $id;
        if (!$object->fromDb($conditions)->existsInDb()) {
            return $this->sendItemNotFoundResponse($table);
        }
        if (!$this->getScaffoldConfig()->isRecordDeleteAllowed($object->toArrayWithoutFiles())) {
            return cmfServiceJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(cmfTransGeneral('.action.delete.forbidden_for_record'))
                ->goBack(routeToCmfItemsTable($this->getTableNameForRoutes()));
        }
        $object->delete();
        return cmfServiceJsonResponse()
            ->setMessage(cmfTransGeneral('.action.delete.success'))
            ->goBack(routeToCmfItemsTable($this->getTableNameForRoutes()));
    }

    public function deleteBulk(Request $request) {
        if (!$this->getScaffoldConfig()->isDeleteAllowed()) {
            return cmfServiceJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(cmfTransGeneral('.action.delete.forbidden'))
                ->goBack(routeToCmfItemsTable($this->getTableNameForRoutes()));
        }
        $table = $this->getTable();
        $conditions = $this->getConditionsForBulkActions($request, $table);
        if (!is_array($conditions)) {
            return $conditions; //< response
        }
        $deletedCount = $table->delete($conditions);
        $message = $deletedCount
            ? cmfTransGeneral('.action.delete_bulk.success', ['count' => $deletedCount])
            : cmfTransGeneral('.action.delete_bulk.nothing_deleted');
        return cmfServiceJsonResponse()
            ->setMessage($message)
            ->goBack(routeToCmfItemsTable($this->getTableNameForRoutes()));
    }

    /**
     * @param Request $request
     * @param TableInterface $table
     * @param string $inputNamePrefix - input name prefix
     *      For example if you use '_ids' instead of 'ids' - use prefix '_'
     * @return array|Response
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    private function getConditionsForBulkActions(Request $request, TableInterface $table, $inputNamePrefix = '') {
        $specialConditions = $this->getScaffoldConfig()->getFormConfig()->getSpecialConditions();
        $conditions = $specialConditions;
        $idsField = $inputNamePrefix . 'ids';
        $conditionsField = $inputNamePrefix . 'conditions';
        if ($request->has($idsField)) {
            $this->validate($request->data(), [
                $idsField => 'required|array',
                $idsField . '.*' => 'integer|min:1'
            ]);
            $conditions[$table::getPkColumnName()] = $request->data($idsField);
        } else if ($request->has($conditionsField)) {
            $this->validate($request->data(), [
                $conditionsField => 'string|regex:%^[\{\[].*[\}\]]$%s',
            ]);
            $encodedConditions = $request->data($conditionsField) !== ''
                ? json_decode($request->data($conditionsField), true)
                : [];
            if ($encodedConditions === false || !is_array($encodedConditions) || empty($encodedConditions['r'])) {
                return cmfJsonResponseForValidationErrors([$conditionsField => 'JSON expected']);
            }
            if (!empty($encodedConditions)) {
                //$dataGridConfig = $this->getScaffoldConfig()->getDataGridConfig();
                // todo: take $dataGridConfig->contains in account
                $filterConditions = $this
                    ->getScaffoldConfig()
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

    private function getDataGridItems(Request $request) {
        $dataGridConfig = $this->getScaffoldConfig()->getDataGridConfig();
        $dataGridFilterConfig = $this->getScaffoldConfig()->getDataGridFilterConfig();
        $conditions = [
            'LIMIT' => $request->query('length', $dataGridConfig->getLimit()),
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
            array_keys($dataGridConfig->getDbFields()),
            $dataGridConfig->getContains()
        );
        $result = $this->getTable()->select($columnsToSelect, $conditions);
        $records = [];
        if ($result->count()) {
            $records = $dataGridConfig->prepareRecords($result->toArrays());
        }
        return [
            'draw' => $request->query('draw'),
            'recordsTotal' => $result->countTotal(),
            'recordsFiltered' => $result->countTotal(),
            'data' => $records,
        ];
    }

    /**
     * @param array $options
     * @return string
     * @throws \Swayok\Html\HtmlTagException
     */
    private function buildFieldOptions(array $options) {
        $ret = '';
        foreach ($options as $value => $label) {
            if (!is_array($label)) {
                $ret .= Tag::option()
                    ->setContent($label)
                    ->setValue($value)
                    ->build();
            } else {
                $ret .= Tag::create()
                    ->setName('optgroup')
                    ->setAttribute('label', $value)
                    ->setContent($this->buildFieldOptions($label))
                    ->build();
            }
        }
        return $ret;
    }

    /**
     * @param TableInterface $table
     * @param null|string $message
     * @return $this
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     */
    protected function sendItemNotFoundResponse(TableInterface $table, $message = null) {
        if (empty($message)) {
            $message = cmfTransGeneral('.error.resource_item_not_found');
        }
        return cmfJsonResponseForHttp404(
            routeToCmfItemsTable($this->getTableNameForRoutes()),
            $message
        );
    }

}

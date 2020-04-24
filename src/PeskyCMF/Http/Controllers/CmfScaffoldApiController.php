<?php

namespace PeskyCMF\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Db\CmfDbModel;
use PeskyCMF\Http\Request;
use PeskyCMF\HttpCode;
use PeskyCMF\Scaffold\Form\FormConfig;
use PeskyCMF\Scaffold\ScaffoldException;
use PeskyCMF\Scaffold\ScaffoldSectionConfig;
use PeskyCMF\Traits\DataValidationHelper;
use PeskyORM\DbExpr;
use Swayok\Html\Tag;

class CmfScaffoldApiController extends Controller {

    use DataValidationHelper;

    protected $tableNameForRoutes = null;
    protected $model = null;
    protected $scaffoldConfig = null;

    /**
     * @return CmfDbModel
     */
    public function getModel() {
        if ($this->model === null) {
            $this->model = CmfConfig::getInstance()->getModelByTableName($this->getTableNameForRoutes());
        }
        return $this->model;
    }

    /**
     * @return ScaffoldSectionConfig
     * @throws \UnexpectedValueException
     */
    public function getScaffoldConfig() {
        if ($this->scaffoldConfig === null) {
            $cmfConfig = CmfConfig::getInstance();
            $customScaffoldConfig = $cmfConfig::getScaffoldConfig($this->getModel(), $this->getTableNameForRoutes());
            if ($customScaffoldConfig instanceof ScaffoldSectionConfig) {
                $this->scaffoldConfig = $customScaffoldConfig;
            } else if (!empty($customScaffoldConfig)) {
                throw new \UnexpectedValueException(
                    get_class($cmfConfig) . '::getCustomScaffoldSectionConfigForTable() must return '
                        . 'null or instance of ScaffoldSectionConfig class'
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
        $model = $this->getModel();
        if ($isItemDetails && !$this->getScaffoldConfig()->isDetailsViewerAllowed()) {
            return cmfServiceJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(CmfConfig::transBase('.action.item_details.forbidden'))
                ->goBack(route('cmf_items_table', ['table_name' => $this->getTableNameForRoutes()]));
        } else if (!$isItemDetails && !$this->getScaffoldConfig()->isEditAllowed()) {
            return cmfServiceJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(CmfConfig::transBase('.action.edit.forbidden'))
                ->goBack(route('cmf_items_table', ['table_name' => $this->getTableNameForRoutes()]));
        }
        $object = $model::getOwnDbObject();
        if (!$object->_getPkField()->isValidValueFormat($id)) {
            return $this->sendItemNotFoundResponse($model);
        }
        if ($isItemDetails) {
            $actionConfig = $this->getScaffoldConfig()->getItemDetailsConfig();
        } else {
            $actionConfig = $this->getScaffoldConfig()->getFormConfig();
        }
        $conditions = $actionConfig->getSpecialConditions();
        $conditions[$model->getPkColumnName()] = $id;
        if ($actionConfig->hasContains()) {
            $conditions['CONTAIN'] = $actionConfig->getContains();
        }
        if (!$object->find($conditions)->exists()) {
            return $this->sendItemNotFoundResponse($model);
        }
        $data = $object->toPublicArray(null, true, false);
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
                ->setMessage(CmfConfig::transBase(
                    '.action.' . ($isItemDetails ? 'item_details' : 'edit') . '.forbidden_for_record'
                ))
                ->goBack(route('cmf_items_table', ['table_name' => $this->getTableNameForRoutes()]));
        }
        return new JsonResponse($actionConfig->prepareRecord($data));
    }

    public function getItemDefaults() {
        /** @var FormConfig $config */
        if (!$this->getScaffoldConfig()->isCreateAllowed() && !$this->getScaffoldConfig()->isEditAllowed()) {
            return cmfServiceJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(CmfConfig::transBase('.action.create.forbidden'))
                ->goBack(route('cmf_items_table', ['table_name' => $this->getTableNameForRoutes()]));
        }
        $formConfig = $this->getScaffoldConfig()->getFormConfig();
        $data = $formConfig->alterDefaultValues($this->getModel()->getOwnDbObject()->getDefaultsArray());
        return new JsonResponse($formConfig->prepareRecord($data));
    }

    public function getOptions(Request $request) {
        if (!$this->getScaffoldConfig()->isEditAllowed() && !$this->getScaffoldConfig()->isCreateAllowed()) {
            return cmfServiceJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(CmfConfig::transBase('.action.edit.forbidden'))
                ->goBack(route('cmf_items_table', ['table_name' => $this->getTableNameForRoutes()]));
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
                ->setMessage(CmfConfig::transBase('.action.create.forbidden'))
                ->goBack(route('cmf_items_table', ['table_name' => $this->getTableNameForRoutes()]));
        }
        $model = $this->getModel();
        $formConfig = $this->getScaffoldConfig()->getFormConfig();
        $data = array_intersect_key($request->data(), $formConfig->getFields());
        $errors = $formConfig->validateDataForCreate($data);
        if (count($errors) !== 0) {
            return $this->sendValidationErrorsResponse($errors);
        }
        unset($data[$model->getPkColumnName()]);
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
        unset($data[$model->getPkColumnName()]); //< to be 100% sure =)
        if (!empty($data)) {
            $model->begin();
            try {
                $dbData = array_diff_key($data, $formConfig->getNonDbFields());
                $object = $model::getOwnDbObject($dbData);
                $success = $object->save();
                if (!$success) {
                    $model->rollback();
                    return cmfServiceJsonResponse(HttpCode::SERVER_ERROR)
                        ->setMessage(CmfConfig::transBase('.form.failed_to_save_data'));
                } else if ($formConfig->hasAfterSaveCallback()) {
                    $success = call_user_func($formConfig->getAfterSaveCallback(), true, $data, $object, $formConfig);
                    if ($success instanceof \Symfony\Component\HttpFoundation\JsonResponse) {
                        if ($success->getStatusCode() < 400) {
                            $model->commit();
                        } else {
                            $model->rollback();
                        }
                        return $success;
                    } else if ($success !== true) {
                        $model->rollback();
                        throw new ScaffoldException(
                            'afterSave callback must return true or instance of \Symfony\Component\HttpFoundation\JsonResponse'
                        );
                    }
                }
                $model->commit();
            } catch (DbObjectValidationException $exc) {
                if ($model->inTransaction()) {
                    $model->rollback();
                }
                return $this->sendValidationErrorsResponse($exc->getValidationErrors());
            } catch (\Exception $exc) {
                if ($model->inTransaction()) {
                    $model->rollback();
                }
                throw $exc;
            }
        }
        return cmfServiceJsonResponse()
            ->setMessage(CmfConfig::transBase('.form.resource_created_successfully'));
    }

    public function updateItem(Request $request) {
        if (!$this->getScaffoldConfig()->isEditAllowed()) {
            return cmfServiceJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(CmfConfig::transBase('.action.edit.forbidden'))
                ->goBack(route('cmf_items_table', ['table_name' => $this->getTableNameForRoutes()]));
        }
        $model = $this->getModel();
        $formConfig = $this->getScaffoldConfig()->getFormConfig();
        $expectedFields = array_keys($formConfig->getFields());
        $expectedFields[] = $model->getPkColumnName();
        $data = array_intersect_key($request->data(), array_flip($expectedFields));
        $errors = $formConfig->validateDataForEdit($data);
        if (count($errors) !== 0) {
            return $this->sendValidationErrorsResponse($errors);
        }
        if (!$request->data($model->getPkColumnName())) {
            return $this->sendItemNotFoundResponse($model);
        }
        $id = $request->data($model->getPkColumnName());
        $object = $model::getOwnDbObject();
        if (!$object->_getPkField()->isValidValueFormat($id)) {
            return $this->sendItemNotFoundResponse($model);
        }
        $conditions = $formConfig->getSpecialConditions();
        $conditions[$model->getPkColumnName()] = $id;
        if (!$object->find($conditions)->exists()) {
            return $this->sendItemNotFoundResponse($model);
        }
        if (!$this->getScaffoldConfig()->isRecordEditAllowed($object->toPublicArrayWithoutFiles())) {
            return cmfServiceJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(CmfConfig::transBase('.action.edit.forbidden_for_record'))
                ->goBack(route('cmf_items_table', ['table_name' => $this->getTableNameForRoutes()]));
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
        unset($data[$model->getPkColumnName()]);
        if (!empty($data)) {
            $model->begin();
            try {
                $dbData = array_diff_key($data, $formConfig->getNonDbFields());
                $success = $object->begin()->updateValues($dbData)->commit();
                if (!$success) {
                    $model->rollback();
                    return cmfServiceJsonResponse(HttpCode::SERVER_ERROR)
                        ->setMessage(CmfConfig::transBase('.form.failed_to_save_data'));
                } else if ($formConfig->hasAfterSaveCallback()) {
                    $success = call_user_func($formConfig->getAfterSaveCallback(), false, $data, $object, $formConfig);
                    if ($success instanceof \Symfony\Component\HttpFoundation\JsonResponse) {
                        if ($success->getStatusCode() < 400) {
                            $model->commit();
                        } else {
                            $model->rollback();
                        }
                        return $success;
                    } else if ($success !== true) {
                        $model->rollback();
                        throw new ScaffoldException(
                            'afterSave callback must return true or instance of \Symfony\Component\HttpFoundation\JsonResponse'
                        );
                    }
                }
                $model->commit();
            } catch (DbObjectValidationException $exc) {
                if ($model->inTransaction()) {
                    $model->rollback();
                }
                return $this->sendValidationErrorsResponse($exc->getValidationErrors());
            } catch (\Exception $exc) {
                if ($model->inTransaction()) {
                    $model->rollback();
                }
                throw $exc;
            }
        }
        return cmfServiceJsonResponse()
            ->setMessage(CmfConfig::transBase('.form.resource_updated_successfully'));
    }

    public function updateBulk(Request $request) {
        if (!$this->getScaffoldConfig()->isEditAllowed()) {
            return cmfServiceJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(CmfConfig::transBase('.action.edit.forbidden'))
                ->goBack(route('cmf_items_table', ['table_name' => $this->getTableNameForRoutes()]));
        }
        $model = $this->getModel();
        $formConfig = $this->getScaffoldConfig()->getFormConfig();
        $expectedFields = array_keys($formConfig->getBulkEditableFields());
        $data = array_intersect_key($request->data(), array_flip($expectedFields));
        if (empty($data)) {
            return cmfServiceJsonResponse(HttpCode::INVALID)
                ->setMessage(CmfConfig::transBase('.action.bulk_edit.no_data_to_save'));
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
        $conditions = $this->getConditionsForBulkActions($request, $model, '_');
        if (!is_array($conditions)) {
            return $conditions; //< response
        }
        $model->begin();
        $updatedCount = $model->update($data, $conditions);
        if ($formConfig->hasAfterBulkEditDataAfterSaveCallback()) {
            $success = call_user_func($formConfig->getAfterBulkEditDataAfterSaveCallback(), $data);
            if ($success instanceof \Symfony\Component\HttpFoundation\JsonResponse) {
                if ($success->getStatusCode() < 400) {
                    $model->commit();
                } else {
                    $model->rollback();
                }
                return $success;
            } else if ($success !== true) {
                $model->rollback();
                throw new ScaffoldException(
                    'afterBulkEditDataAfterSave callback must return true or instance of \Symfony\Component\HttpFoundation\JsonResponse'
                );
            }
        }
        $model->commit();
        $message = $updatedCount
            ? CmfConfig::transBase('.action.bulk_edit.success', ['count' => $updatedCount])
            : CmfConfig::transBase('.action.bulk_edit.nothing_updated');
        return cmfServiceJsonResponse()
            ->setMessage($message)
            ->goBack(route('cmf_items_table', ['table_name' => $this->getTableNameForRoutes()]));
    }

    public function deleteItem($tableName, $id) {
        if (!$this->getScaffoldConfig()->isDeleteAllowed()) {
            return cmfServiceJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(CmfConfig::transBase('.action.delete.forbidden'))
                ->goBack(route('cmf_items_table', ['table_name' => $this->getTableNameForRoutes()]));
        }
        $model = $this->getModel();
        $object = $model::getOwnDbObject();
        if (!$object->_getPkField()->isValidValueFormat($id)) {
            return $this->sendItemNotFoundResponse($model);
        }
        $formConfig = $this->getScaffoldConfig()->getFormConfig();
        $conditions = $formConfig->getSpecialConditions();
        $conditions[$model->getPkColumnName()] = $id;
        if (!$object->find($conditions)->exists()) {
            return $this->sendItemNotFoundResponse($model);
        }
        if (!$this->getScaffoldConfig()->isRecordDeleteAllowed($object->toPublicArrayWithoutFiles())) {
            return cmfServiceJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(CmfConfig::transBase('.action.delete.forbidden_for_record'))
                ->goBack(route('cmf_items_table', ['table_name' => $this->getTableNameForRoutes()]));
        }
        $object->delete();
        return cmfServiceJsonResponse()
            ->setMessage(CmfConfig::transBase('.action.delete.success'))
            ->goBack(route('cmf_items_table', ['table_name' => $this->getTableNameForRoutes()]));
    }

    public function deleteBulk(Request $request) {
        if (!$this->getScaffoldConfig()->isDeleteAllowed()) {
            return cmfServiceJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(CmfConfig::transBase('.action.delete.forbidden'))
                ->goBack(route('cmf_items_table', ['table_name' => $this->getTableNameForRoutes()]));
        }
        $model = $this->getModel();
        $conditions = $this->getConditionsForBulkActions($request, $model);
        if (!is_array($conditions)) {
            return $conditions; //< response
        }
        $deletedCount = $model->delete($conditions);
        $message = $deletedCount
            ? CmfConfig::transBase('.action.delete_bulk.success', ['count' => $deletedCount])
            : CmfConfig::transBase('.action.delete_bulk.nothing_deleted');
        return cmfServiceJsonResponse()
            ->setMessage($message)
            ->goBack(route('cmf_items_table', ['table_name' => $this->getTableNameForRoutes()]));
    }

    /**
     * @param Request $request
     * @param CmfDbModel $model
     * @param string $inputNamePrefix - input name prefix
     *      For example if you use '_ids' instead of 'ids' - use prefix '_'
     * @return array|Response
     */
    private function getConditionsForBulkActions(Request $request, CmfDbModel $model, $inputNamePrefix = '') {
        $specialConditions = $this->getScaffoldConfig()->getFormConfig()->getSpecialConditions();
        $conditions = $specialConditions;
        $idsField = $inputNamePrefix . 'ids';
        $conditionsField = $inputNamePrefix . 'conditions';
        if ($request->has($idsField)) {
            $this->validate($request->data(), [
                $idsField => 'required|array',
                $idsField . '.*' => 'integer|min:1'
            ]);
            $conditions[$model->getPkColumnName()] = $request->data($idsField);
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
                $dataGridConfig = $this->getScaffoldConfig()->getDataGridConfig();
                $filterConditions = $this->getScaffoldConfig()
                    ->getDataGridFilterConfig()
                    ->buildConditionsFromSearchRules($encodedConditions);
                if ($dataGridConfig->hasContains()) {
                    $subQueryConditions = array_merge(
                        ['CONTAIN' => $dataGridConfig->getContains()],
                        $filterConditions,
                        $specialConditions
                    );
                    [$columns, $subQueryConditions] = $model::resolveContains([], $subQueryConditions);
                    $subQuery = $model::makeSelect([$model::getPkColumnName()], $subQueryConditions)->getQuery();
                    $conditions = [DbExpr::create("`{$model->getPkColumnName()}` IN ({$subQuery})")];
                } else {
                    $conditions = array_merge($filterConditions, $specialConditions);
                }
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
        if ($dataGridConfig->hasContains()) {
            $conditions['CONTAIN'] = $dataGridConfig->getContains();
        }
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
        $result = $this->getModel()->selectWithCount(array_keys($dataGridConfig->getDbFields()), $conditions);
        if ($result['count'] > 0) {
            $result['records'] = $dataGridConfig->prepareRecords($result['records']);
        }
        return [
            'draw' => $request->query('draw'),
            'recordsTotal' => $result['count'],
            'recordsFiltered' => $result['count'],
            'data' => $result['records'],
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
                $ret .= Tag::create(
                        ['label' => $value],
                        'optgroup'
                    )
                    ->setContent($this->buildFieldOptions($label))
                    ->build();
            }
        }
        return $ret;
    }

    /**
     * @param CmfDbModel $model
     * @param null|string $message
     * @return $this
     */
    protected function sendItemNotFoundResponse(CmfDbModel $model, $message = null) {
        if (empty($message)) {
            $message = CmfConfig::transBase('.error.resource_item_not_found');
        }
        return cmfJsonResponseForHttp404(
            route('cmf_items_table', ['table_name' => $this->getTableNameForRoutes()]),
            $message
        );
    }

}

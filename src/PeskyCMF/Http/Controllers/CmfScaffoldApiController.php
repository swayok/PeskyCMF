<?php

namespace PeskyCMF\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Db\CmfDbModel;
use PeskyCMF\Http\Request;
use PeskyCMF\HttpCode;
use PeskyCMF\PeskyCmfException;
use PeskyCMF\Scaffold\Form\FormConfig;
use PeskyCMF\Scaffold\ScaffoldException;
use PeskyCMF\Scaffold\ScaffoldSectionConfig;
use PeskyCMF\Traits\DataValidationHelper;
use PeskyORM\DbExpr;
use PeskyORM\Exception\DbObjectValidationException;
use Swayok\Html\Tag;

class CmfScaffoldApiController extends Controller {

    use DataValidationHelper;

    static protected $model;
    static protected $tableNameForRoutes;
    static protected $scaffoldConfig;

    /**
     * @param CmfDbModel $model
     */
    static public function setModel(CmfDbModel $model) {
        static::$model = $model;
    }

    /**
     * @return bool
     */
    static public function hasModel() {
        return !empty(static::$model);
    }

    /**
     * @return CmfDbModel
     * @throws PeskyCmfException
     */
    static public function getModel() {
        if (!static::hasModel()) {
            throw new PeskyCmfException('Model not found');
        }
        return static::$model;
    }
    
    /**
     * @param ScaffoldSectionConfig $scaffoldConfig
     */
    static public function setScaffoldConfig(ScaffoldSectionConfig $scaffoldConfig) {
        static::$scaffoldConfig = $scaffoldConfig;
    }

    /**
     * @return ScaffoldSectionConfig
     * @throws \PeskyCMF\PeskyCmfException
     */
    static private function getScaffoldConfig() {
        if (!static::$scaffoldConfig) {
            static::$scaffoldConfig = static::getModel()->getScaffoldConfig();
        }
        return static::$scaffoldConfig;
    }

    static public function setTableNameForRoutes($tableName) {
        static::$tableNameForRoutes = $tableName;
    }

    static public function getTableNameForRoutes() {
        if (!static::$tableNameForRoutes) {
            static::$tableNameForRoutes = static::getModel()->getTableName();
        }
        return static::$tableNameForRoutes;
    }

    public function __construct() {

    }

    public function getTemplates() {
        return view(
            CmfConfig::getInstance()->scaffold_templates_view(),
            array_merge(
                static::getScaffoldConfig()->getConfigs(),
                ['tableNameForRoutes' => static::getTableNameForRoutes()]
            )
        )->render();
    }

    public function getItemsList(Request $request) {
        return new JsonResponse($this->getDataGridItems($request));
    }

    public function getItem(Request $request, $tableName, $id = null) {
        $isItemDetails = (bool)$request->query('details', false);
        $model = static::getModel();
        if (!static::getScaffoldConfig()->isDetailsViewerAllowed()) {
            return cmfServiceJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(CmfConfig::transBase(
                    '.action.' . ($isItemDetails ? 'item_details' : 'edit') . '.forbidden'
                ))
                ->goBack(route('cmf_items_table', [static::getTableNameForRoutes()]));
        }
        $object = $model::getOwnDbObject();
        if (!$object->_getPkField()->isValidValueFormat($id)) {
            return static::sendItemNotFoundResponse($model);
        }
        if ($isItemDetails) {
            $actionConfig = static::getScaffoldConfig()->getItemDetailsConfig();
        } else {
            $actionConfig = static::getScaffoldConfig()->getFormConfig();
        }
        $conditions = $actionConfig->getSpecialConditions();
        $conditions[$model->getPkColumnName()] = $id;
        if ($actionConfig->hasContains()) {
            $conditions['CONTAIN'] = $actionConfig->getContains();
        }
        if (!$object->find($conditions)->exists()) {
            return static::sendItemNotFoundResponse($model);
        }
        $data = $object->toPublicArray(null, true, false);
        if (
            (
                $isItemDetails
                && !static::getScaffoldConfig()->isRecordDetailsAllowed($data)
            )
            ||
            (
                !$isItemDetails
                && !static::getScaffoldConfig()->isRecordEditAllowed($data)
            )
        ) {
            return cmfServiceJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(CmfConfig::transBase(
                    '.action.' . ($isItemDetails ? 'item_details' : 'edit') . '.forbidden_for_record'
                ))
                ->goBack(route('cmf_items_table', [static::getTableNameForRoutes()]));
        }
        return new JsonResponse($actionConfig->prepareRecord($data));
    }

    public function getItemDefaults() {
        /** @var FormConfig $config */
        if (!static::getScaffoldConfig()->isCreateAllowed() && !static::getScaffoldConfig()->isEditAllowed()) {
            return cmfServiceJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(CmfConfig::transBase('.action.create.forbidden'))
                ->goBack(route('cmf_items_table', [static::getTableNameForRoutes()]));
        }
        $formConfig = static::getScaffoldConfig()->getFormConfig();
        $data = $formConfig->alterDefaultValues(static::getModel()->getOwnDbObject()->getDefaultsArray());
        return new JsonResponse($formConfig->prepareRecord($data));
    }

    public function getOptions(Request $request) {
        if (!static::getScaffoldConfig()->isEditAllowed() && !static::getScaffoldConfig()->isCreateAllowed()) {
            return cmfServiceJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(CmfConfig::transBase('.action.edit.forbidden'))
                ->goBack(route('cmf_items_table', [static::getTableNameForRoutes()]));
        }
        $optionsByFields = static::getScaffoldConfig()->getFormConfig()->loadOptions($request->query('id'));
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
        if (!static::getScaffoldConfig()->isCreateAllowed()) {
            return cmfServiceJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(CmfConfig::transBase('.action.create.forbidden'))
                ->goBack(route('cmf_items_table', [static::getTableNameForRoutes()]));
        }
        $model = static::getModel();
        $formConfig = static::getScaffoldConfig()->getFormConfig();
        $data = array_intersect_key($request->data(), $formConfig->getFields());
        $errors = $formConfig->validateDataForCreate($data);
        if (!empty($errors)) {
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
                if (!empty($errors)) {
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
        if (!static::getScaffoldConfig()->isEditAllowed()) {
            return cmfServiceJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(CmfConfig::transBase('.action.edit.forbidden'))
                ->goBack(route('cmf_items_table', [static::getTableNameForRoutes()]));
        }
        $model = static::getModel();
        $formConfig = static::getScaffoldConfig()->getFormConfig();
        $expectedFields = array_keys($formConfig->getFields());
        $expectedFields[] = $model->getPkColumnName();
        $data = array_intersect_key($request->data(), array_flip($expectedFields));
        $errors = $formConfig->validateDataForEdit($data);
        if (!empty($errors)) {
            return $this->sendValidationErrorsResponse($errors);
        }
        if (!$request->data($model->getPkColumnName())) {
            return static::sendItemNotFoundResponse($model);
        }
        $id = $request->data($model->getPkColumnName());
        $object = $model::getOwnDbObject();
        if (!$object->_getPkField()->isValidValueFormat($id)) {
            return static::sendItemNotFoundResponse($model);
        }
        $conditions = $formConfig->getSpecialConditions();
        $conditions[$model->getPkColumnName()] = $id;
        if (!$object->find($conditions)->exists()) {
            return static::sendItemNotFoundResponse($model);
        }
        if (!static::getScaffoldConfig()->isRecordEditAllowed($object->toPublicArrayWithoutFiles())) {
            return cmfServiceJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(CmfConfig::transBase('.action.edit.forbidden_for_record'))
                ->goBack(route('cmf_items_table', [static::getTableNameForRoutes()]));
        }
        if ($formConfig->hasBeforeSaveCallback()) {
            $data = call_user_func($formConfig->getBeforeSaveCallback(), false, $data, $formConfig);
            if (empty($data)) {
                throw new ScaffoldException('Empty $data received from beforeSave callback');
            }
            if ($formConfig->shouldRevalidateDataAfterBeforeSaveCallback(false)) {
                // revalidate
                $errors = $formConfig->validateDataForEdit($data, [], true);
                if (!empty($errors)) {
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
        if (!static::getScaffoldConfig()->isEditAllowed()) {
            return cmfServiceJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(CmfConfig::transBase('.action.edit.forbidden'))
                ->goBack(route('cmf_items_table', [static::getTableNameForRoutes()]));
        }
        $model = static::getModel();
        $formConfig = static::getScaffoldConfig()->getFormConfig();
        $expectedFields = array_keys($formConfig->getBulkEditableFields());
        $data = array_intersect_key($request->data(), array_flip($expectedFields));
        if (empty($data)) {
            return cmfServiceJsonResponse(HttpCode::INVALID)
                ->setMessage(CmfConfig::transBase('.action.bulk_edit.no_data_to_save'));
        }
        $errors = $formConfig->validateDataForBulkEdit($data);
        if (!empty($errors)) {
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
                if (!empty($errors)) {
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
            ->goBack(route('cmf_items_table', [static::getTableNameForRoutes()]));
    }

    public function deleteItem($tableName, $id) {
        if (!static::getScaffoldConfig()->isDeleteAllowed()) {
            return cmfServiceJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(CmfConfig::transBase('.action.delete.forbidden'))
                ->goBack(route('cmf_items_table', [static::getTableNameForRoutes()]));
        }
        $model = static::getModel();
        $object = $model::getOwnDbObject();
        if (!$object->_getPkField()->isValidValueFormat($id)) {
            return static::sendItemNotFoundResponse($model);
        }
        $formConfig = static::getScaffoldConfig()->getFormConfig();
        $conditions = $formConfig->getSpecialConditions();
        $conditions[$model->getPkColumnName()] = $id;
        if (!$object->find($conditions)->exists()) {
            return static::sendItemNotFoundResponse($model);
        }
        if (!static::getScaffoldConfig()->isRecordDeleteAllowed($object->toPublicArrayWithoutFiles())) {
            return cmfServiceJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(CmfConfig::transBase('.action.delete.forbidden_for_record'))
                ->goBack(route('cmf_items_table', [static::getTableNameForRoutes()]));
        }
        $object->delete();
        return cmfServiceJsonResponse()
            ->setMessage(CmfConfig::transBase('.action.delete.success'))
            ->goBack(route('cmf_items_table', [static::getTableNameForRoutes()]));
    }

    public function deleteBulk(Request $request) {
        if (!static::getScaffoldConfig()->isDeleteAllowed()) {
            return cmfServiceJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(CmfConfig::transBase('.action.delete.forbidden'))
                ->goBack(route('cmf_items_table', [static::getTableNameForRoutes()]));
        }
        $model = static::getModel();
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
            ->goBack(route('cmf_items_table', [static::getTableNameForRoutes()]));
    }

    /**
     * @param Request $request
     * @param CmfDbModel $model
     * @param string $inputNamePrefix - input name prefix
     *      For example if you use '_ids' instead of 'ids' - use prefix '_'
     * @return array|Response
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     * @throws \PeskyCMF\PeskyCmfException
     * @throws \PeskyORM\Exception\DbQueryException
     * @throws \PeskyORM\Exception\DbException
     * @throws \PeskyORM\Exception\DbTableConfigException
     * @throws \PeskyORM\Exception\DbUtilsException
     * @throws \PeskyORM\Exception\DbModelException
     */
    private function getConditionsForBulkActions(Request $request, CmfDbModel $model, $inputNamePrefix = '') {
        $specialConditions = static::getScaffoldConfig()->getFormConfig()->getSpecialConditions();
        $conditions = $specialConditions;
        $idsField = $inputNamePrefix . 'ids';
        $conditionsField = $inputNamePrefix . 'conditions';
        if ($request->has($idsField)) {
            $this->validate($request->data(), [
                $idsField => 'required|array',
                $idsField .'.*' => 'integer|min:1'
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
                $dataGridConfig = static::getScaffoldConfig()->getDataGridConfig();
                $filterConditions = static::getScaffoldConfig()
                    ->getDataGridFilterConfig()
                    ->buildConditionsFromSearchRules($encodedConditions);
                if ($dataGridConfig->hasContains()) {
                    $subQueryConditions = array_merge(
                        ['CONTAIN' => $dataGridConfig->getContains()],
                        $filterConditions,
                        $specialConditions
                    );
                    $subQuery = $model->builder()
                        ->fromOptions($model->resolveContains($subQueryConditions))
                        ->fields(['id'])
                        ->buildQuery(DbExpr::create("`{$model->getAlias()}`.`id`"), false, false);
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
        $dataGridConfig = static::getScaffoldConfig()->getDataGridConfig();
        $dataGridFilterConfig = static::getScaffoldConfig()->getDataGridFilterConfig();
        $conditions = [
            'LIMIT' => $request->query('length', $dataGridConfig->getLimit()),
            'OFFSET' => (int)$request->query('start', 0),
            'ORDER' => []
        ];
        if ($dataGridConfig->hasContains()) {
            $conditions['CONTAIN'] = $dataGridConfig->getContains();
        }
        $conditions = array_merge($dataGridConfig->getSpecialConditions(), $conditions);
        $search = $request->query('search');
        if (!empty($search) && !empty($search['value'])) {
            $search = json_decode($search['value'], true);
            if (!empty($search) && is_array($search) && !empty($search['r'])) {
                $conditions = array_replace($dataGridFilterConfig->buildConditionsFromSearchRules($search), $conditions);
            }
        }
        $order = $request->query('order', [[
            'column' => $dataGridConfig->getOrderBy(),
            'dir' => $dataGridConfig->getOrderDirection()
        ]]);
        $columns = $request->query('columns', array());
        foreach ($order as $config) {
            if (is_numeric($config['column']) && !empty($columns[$config['column']])) {
                $config['column'] = $columns[$config['column']]['name'];
            }
            if (!empty($config['column']) && !is_numeric($config['column'])) {
                if ($config['column'] instanceof DbExpr) {
                    $conditions['ORDER'] = DbExpr::create($config['column']->get() . ' ' . $config['dir']);
                } else {
                    $conditions['ORDER'][$config['column']] = $config['dir'];
                }
            }
        }
        $result = static::getModel()->selectWithCount(array_keys($dataGridConfig->getDbFields()), $conditions);
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
                    ->setValue($value);
            } else {
                $ret .= Tag::create()
                    ->setName('optgroup')
                    ->setAttribute('label', $value)
                    ->setContent($this->buildFieldOptions($label));
            }
        }
        return $ret;
    }

    /**
     * @param CmfDbModel $model
     * @param null|string $message
     * @return $this
     */
    static public function sendItemNotFoundResponse(CmfDbModel $model, $message = null) {
        if (empty($message)) {
            $message = CmfConfig::transBase('.error.resource_item_not_found');
        }
        return cmfJsonResponseForHttp404(
            route('cmf_items_table', [static::getTableNameForRoutes()]),
            $message
        );
    }

}

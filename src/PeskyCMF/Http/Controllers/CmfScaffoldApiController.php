<?php

namespace PeskyCMF\Http\Controllers;

use Illuminate\Routing\Controller;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Db\CmfDbModel;
use PeskyCMF\Http\Request;
use PeskyCMF\HttpCode;
use PeskyCMF\PeskyCmfException;
use PeskyCMF\Scaffold\ScaffoldException;
use PeskyCMF\Scaffold\ScaffoldSectionConfig;
use PeskyORM\Exception\DbObjectValidationException;
use Swayok\Html\Tag;

class CmfScaffoldApiController extends Controller {

    static protected $model;

    /**
     * @param CmfDbModel $model
     */
    static public function setModel(CmfDbModel $model) {
        self::$model = $model;
    }

    /**
     * @return bool
     */
    static public function hasModel() {
        return !empty(self::$model);
    }

    /**
     * @return CmfDbModel
     * @throws PeskyCmfException
     */
    static public function getModel() {
        if (!self::hasModel()) {
            throw new PeskyCmfException('Model not found');
        }
        return self::$model;
    }

    public function __construct() {

    }

    public function getTemplates() {
        return view(
            CmfConfig::getInstance()->scaffold_templates_view(),
            $this->getScaffoldConfig()->getConfigs()
        )->render();
    }

    public function getItemsList(Request $request) {
        return response()->json($this->getDataGridItems($request));
    }

    public function getItem(Request $request, $tableName, $id = null) {
        $isItemDetails = !!$request->query('details', false);
        $model = self::getModel();
        if (!$this->getScaffoldConfig()->isDetailsViewerAllowed()) {
            return cmfServiceJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(CmfConfig::transBase(
                    '.action.' . ($isItemDetails ? 'item_details' : 'edit') . '.forbidden'
                ))
                ->goBack(route('cmf_items_table', [$model->getTableName()]));
        }
        $object = $model->getOwnDbObject();
        if (!$object->_getPkField()->isValidValueFormat($id)) {
            return self::sendItemNotFoundResponse($model);
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
            return self::sendItemNotFoundResponse($model);
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
                ->goBack(route('cmf_items_table', [$model->getTableName()]));
        }
        return response()->json($actionConfig->prepareRecord($data));
    }

    public function getItemDefaults() {
        $model = self::getModel();
        if (!$this->getScaffoldConfig()->isDetailsViewerAllowed()) {
            return cmfServiceJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(CmfConfig::transBase('.action.item_details.forbidden'))
                ->goBack(route('cmf_items_table', [$model->getTableName()]));
        }
        $data = $model->getOwnDbObject()->getDefaultsArray();
        return response()->json($this->getScaffoldConfig()->getFormConfig()->prepareRecord($data));
    }

    public function getOptions() {
        if (!$this->getScaffoldConfig()->isEditAllowed() && !$this->getScaffoldConfig()->isCreateAllowed()) {
            return cmfServiceJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(CmfConfig::transBase('.action.edit.forbidden'))
                ->goBack(route('cmf_items_table', [self::getModel()->getTableName()]));
        }
        $optionsByFields = $this->getScaffoldConfig()->getFormConfig()->loadOptions();
        foreach ($optionsByFields as $fieldName => $fieldOptions) {
            if (is_array($fieldOptions)) {
                $optionsByFields[$fieldName] = $this->buildFieldOptions($fieldOptions);
            } else if (!is_string($fieldOptions)) {
                unset($optionsByFields[$fieldName]);
            }
        }
        return response()->json($optionsByFields);
    }

    public function addItem(Request $request) {
        $model = self::getModel();
        if (!$this->getScaffoldConfig()->isCreateAllowed()) {
            return cmfServiceJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(CmfConfig::transBase('.action.create.forbidden'))
                ->goBack(route('cmf_items_table', [$model->getTableName()]));
        }
        $formConfig = $this->getScaffoldConfig()->getFormConfig();
        $data = array_intersect_key($request->data(), $formConfig->getFields());
        $errors = $formConfig->validateDataForCreate($data);
        if (!empty($errors)) {
            return cmfJsonResponseForValidationErrors($errors);
        }
        unset($data[$model->getPkColumnName()]);
        if ($formConfig->hasBeforeSaveCallback()) {
            $data = call_user_func($formConfig->getBeforeSaveCallback(), true, $data, $formConfig);
            if (empty($data)) {
                throw new ScaffoldException('Empty $data received from beforeSave callback');
            }
        }
        if ($formConfig->shouldRevalidateDataAfterBeforeSaveCallback(true)) {
            // revalidate
            $errors = $formConfig->validateDataForCreate($data, [], true);
            if (!empty($errors)) {
                return cmfJsonResponseForValidationErrors($errors);
            }
        }
        unset($data[$model->getPkColumnName()]); //< to be 100% sure =)
        if (!empty($data)) {
            try {
                $success = $model->getOwnDbObject($data)->save();
                if (!$success) {
                    return cmfServiceJsonResponse(HttpCode::SERVER_ERROR)
                        ->setMessage(CmfConfig::transBase('.form.failed_to_save_data'));
                }
            } catch (DbObjectValidationException $exc) {
                return cmfJsonResponseForValidationErrors($exc->getValidationErrors());
            }
        }
        return cmfServiceJsonResponse()
            ->setMessage(CmfConfig::transBase('.form.resource_created_successfully'));
    }

    public function updateItem(Request $request) {
        $model = self::getModel();
        if (!$this->getScaffoldConfig()->isEditAllowed()) {
            return cmfServiceJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(CmfConfig::transBase('.action.edit.forbidden'))
                ->goBack(route('cmf_items_table', [$model->getTableName()]));
        }
        $formConfig = $this->getScaffoldConfig()->getFormConfig();
        $expectedFields = array_keys($formConfig->getFields());
        $expectedFields[] = $model->getPkColumnName();
        $data = array_intersect_key($request->data(), array_flip($expectedFields));
        $errors = $formConfig->validateDataForEdit($data);
        if (!empty($errors)) {
            return cmfJsonResponseForValidationErrors($errors);
        }
        if (!$request->data($model->getPkColumnName())) {
            return self::sendItemNotFoundResponse($model);
        }
        $id = $request->data($model->getPkColumnName());
        $object = $model->getOwnDbObject();
        if (!$object->_getPkField()->isValidValueFormat($id)) {
            return self::sendItemNotFoundResponse($model);
        }
        $conditions = $formConfig->getSpecialConditions();
        $conditions[$model->getPkColumnName()] = $id;
        if (!$object->find($conditions)->exists()) {
            return self::sendItemNotFoundResponse($model);
        }
        if (!$this->getScaffoldConfig()->isRecordEditAllowed($object->toPublicArrayWithoutFiles())) {
            return cmfServiceJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(CmfConfig::transBase('.action.edit.forbidden_for_record'))
                ->goBack(route('cmf_items_table', [$model->getTableName()]));
        }
        if ($formConfig->hasBeforeSaveCallback()) {
            $data = call_user_func($formConfig->getBeforeSaveCallback(), false, $data, $formConfig);
            if (empty($data)) {
                throw new ScaffoldException('Empty $data received from beforeSave callback');
            }
        }
        if ($formConfig->shouldRevalidateDataAfterBeforeSaveCallback(false)) {
            // revalidate
            $errors = $formConfig->validateDataForCreate($data);
            if (!empty($errors)) {
                return cmfJsonResponseForValidationErrors($errors);
            }
        }
        unset($data[$model->getPkColumnName()]);
        if (!empty($data)) {
            try {
                $success = $object->begin()->updateValues($data)->commit();
                if (!$success) {
                    return cmfServiceJsonResponse(HttpCode::SERVER_ERROR)
                        ->setMessage(CmfConfig::transBase('.form.failed_to_save_data'));
                }
            } catch (DbObjectValidationException $exc) {
                return cmfJsonResponseForValidationErrors($exc->getValidationErrors());
            }
        }
        return cmfServiceJsonResponse()
            ->setMessage(CmfConfig::transBase('.form.resource_updated_successfully'));
    }

    public function deleteItem($tableName, $id) {
        $model = self::getModel();
        if (!$this->getScaffoldConfig()->isDeleteAllowed()) {
            return cmfServiceJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(CmfConfig::transBase('.action.delete.forbidden'))
                ->goBack(route('cmf_items_table', [$model->getTableName()]));
        }
        $object = $model->getOwnDbObject();
        if (!$object->_getPkField()->isValidValueFormat($id)) {
            return self::sendItemNotFoundResponse($model);
        }
        $formConfig = $this->getScaffoldConfig()->getFormConfig();
        $conditions = $formConfig->getSpecialConditions();
        $conditions[$model->getPkColumnName()] = $id;
        if (!$object->find($conditions)->exists()) {
            return self::sendItemNotFoundResponse($model);
        }
        if (!$this->getScaffoldConfig()->isRecordDeleteAllowed($object->toPublicArrayWithoutFiles())) {
            return cmfServiceJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(CmfConfig::transBase('.action.delete.forbidden_for_record'))
                ->goBack(route('cmf_items_table', [$model->getTableName()]));
        }
        $object->delete();
        return cmfServiceJsonResponse()
            ->setMessage(CmfConfig::transBase('.action.delete.success'))
            ->goBack(route('cmf_items_table', ['table_name' => $model->getTableName()]));
    }

    /**
     * @return ScaffoldSectionConfig
     */
    private function getScaffoldConfig() {
        return self::getModel()->getScaffoldConfig();
    }

    private function getDataGridItems(Request $request) {
        $dataGridConfig = $this->getScaffoldConfig()->getDataGridConfig();
        $dataGridFilterConfig = $this->getScaffoldConfig()->getDataGridFilterConfig();
        $conditions = [
            'LIMIT' => $request->query('length', $dataGridConfig->getLimit()),
            'OFFSET' => intval($request->query('start', 0)),
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
        $columns = $request->query("columns", array());
        foreach ($order as $config) {
            if (is_numeric($config['column']) && !empty($columns[$config['column']])) {
                $config['column'] = $columns[$config['column']]['name'];
            }
            if (!empty($config['column']) && !is_numeric($config['column'])) {
                $conditions['ORDER'][$config['column']] = $config['dir'];
            }
        }
        $result = self::getModel()->selectWithCount(array_keys($dataGridConfig->getDbFields()), $conditions);
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

    static public function sendItemNotFoundResponse(CmfDbModel $model, $message = null) {
        if (empty($message)) {
            $message = CmfConfig::transBase('.error.resource_item_not_found');
        }
        return cmfJsonResponseForHttp404(
            route('cmf_items_table', ['table_name' => $model->getTableName()]),
            $message
        );
    }

}

<?php

namespace PeskyCMF\Scaffold;

use Illuminate\Http\Response;
use PeskyCMF\HttpCode;
use PeskyCMF\Scaffold\Form\FormConfig;
use PeskyORM\Core\DbExpr;
use PeskyORM\Exception\InvalidDataException;

abstract class NormalTableScaffoldConfig extends ScaffoldConfig {

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
        $columnsToSelect = array_keys($dataGridConfig->getViewersLinkedToDbColumns(false));
        foreach ($dataGridConfig->getRelationsToRead() as $relationName => $columns) {
            if (is_int($relationName)) {
                $relationName = $columns;
                $columns = ['*'];
            }
            $columnsToSelect[$relationName] = $columns;
        }
        foreach ($dataGridConfig->getViewersForRelations() as $viewer) {
            if (!array_key_exists($viewer->getRelation()->getName(), $columnsToSelect)) {
                $columnsToSelect[$viewer->getRelation()->getName()] = ['*'];
            }
        }
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

    public function getRecordValues($id = null) {
        $isItemDetails = (bool)$this->getRequest()->query('details', false);
        $table = $this->getTable();
        if (
            ($isItemDetails && !$this->isDetailsViewerAllowed())
            || (!$isItemDetails && !$this->isEditAllowed())
        ) {
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
        $relationsToRead = [];
        foreach ($actionConfig->getRelationsToRead() as $relationName => $columns) {
            if (is_int($relationName)) {
                $relationName = $columns;
                $columns = ['*'];
            }
            $relationsToRead[$relationName] = $columns;
        }
        foreach ($actionConfig->getViewersForRelations() as $viewer) {
            if (!array_key_exists($viewer->getRelation()->getName(), $relationsToRead)) {
                $relationsToRead[$viewer->getRelation()->getName()] = ['*'];
            }
        }
        if (!$object->fromDb($conditions, [], array_keys($relationsToRead))->existsInDb()) {
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
        return cmfJsonResponse()->setData($actionConfig->prepareRecord($data));
    }

    public function getDefaultValuesForFormInputs() {
        /** @var FormConfig $config */
        if (!$this->isCreateAllowed() && !$this->isEditAllowed()) {
            return $this->makeAccessDeniedReponse(cmfTransGeneral('.action.create.forbidden'));
        }
        $formConfig = $this->getFormConfig();
        $data = $formConfig->alterDefaultValues($this->getTable()->newRecord()->getDefaults([], false, true));
        return cmfJsonResponse()->setData($formConfig->prepareRecord($data));
    }

    public function addRecord() {
        if (!$this->isCreateAllowed()) {
            return $this->makeAccessDeniedReponse(cmfTransGeneral('.action.create.forbidden'));
        }
        $table = $this->getTable();
        $formConfig = $this->getFormConfig();
        $data = $formConfig->modifyIncomingDataBeforeValidation(
            $this->getRequest()->only(array_keys($formConfig->getValueViewers()))
        );
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
        $data = $formConfig->modifyIncomingDataBeforeValidation(
            $this->getRequest()->only(array_keys($expectedFields))
        );
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

}
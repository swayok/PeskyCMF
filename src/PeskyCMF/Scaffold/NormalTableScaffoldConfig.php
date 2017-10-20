<?php

namespace PeskyCMF\Scaffold;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use PeskyCMF\HttpCode;
use PeskyCMF\Scaffold\Form\FormConfig;
use PeskyCMF\Scaffold\Form\FormInput;
use PeskyORM\Core\DbExpr;
use PeskyORM\Exception\DbException;
use PeskyORM\Exception\InvalidDataException;
use PeskyORM\ORM\RecordInterface;
use PeskyORM\ORM\TableInterface;

abstract class NormalTableScaffoldConfig extends ScaffoldConfig {

    public function getRecordsForDataGrid() {
        if (!$this->isSectionAllowed()) {
            return $this->makeAccessDeniedReponse(cmfTransGeneral('.error.access_denied_to_scaffold'));
        }
        $request = $this->getRequest();
        $dataGridConfig = $this->getDataGridConfig();
        $dataGridFilterConfig = $this->getDataGridFilterConfig();
        $conditions = [
            'LIMIT' => (int)$request->query('length', $dataGridConfig->getRecordsPerPage()),
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
        if ($dataGridConfig->isNestedViewEnabled()) {
            // set parent id column value to null when nested view is enabled
            $parentPkValue = $request->get('parent');
            $conditions[$dataGridConfig->getColumnNameForNestedView()] = empty($parentPkValue) ? null : $parentPkValue;
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
                    $conditions['ORDER'][] = DbExpr::create($config['column']->get() . ' ' . $config['dir'], false);
                } else {
                    $conditions['ORDER'][$config['column']] = $config['dir'];
                    if ($dataGridConfig->hasValueViewer($config['column'])) {
                        $additionalOrders = $dataGridConfig->getValueViewer($config['column'])->getAdditionalOrderBy();
                        if (!empty($additionalOrders)) {
                            $conditions['ORDER'] = array_merge($conditions['ORDER'], $additionalOrders);
                        }
                    }
                }
            }
        }
        $dbColumns = static::getTable()->getTableStructure()->getColumns();
        $columnsToSelect = [];
        $virtualColumns = [];
        foreach (array_keys($dataGridConfig->getViewersLinkedToDbColumns(false)) as $colName) {
            if (array_key_exists($colName, $dbColumns)) {
                if ($dbColumns[$colName]->isItExistsInDb()) {
                    $columnsToSelect[] = $colName;
                } else {
                    $virtualColumns[] = $colName;
                }
            } else {
                throw new \UnexpectedValueException("Column '{$colName}' does not exist in DB");
            }
        }
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
        $result = static::getTable()->select($columnsToSelect, $conditions);
        $records = [];
        if ($result->count()) {
            $records = $dataGridConfig->prepareRecords($result->toArrays(), $virtualColumns);
        }
        return cmfJsonResponse()->setData([
            'draw' => $request->query('draw'),
            'recordsTotal' => $result->totalCount(),
            'recordsFiltered' => $result->totalCount(),
            'data' => $records,
        ]);
    }

    public function getRecordValues($id = null) {
        $isItemDetails = (bool)$this->getRequest()->query('details', false);
        $table = static::getTable();
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
            $sectionConfig = $this->getItemDetailsConfig();
        } else {
            $sectionConfig = $this->getFormConfig();
        }
        $conditions = $sectionConfig->getSpecialConditions();
        $conditions[$table->getPkColumnName()] = $id;
        $relationsToRead = [];
        if ($id !== null) {
            foreach ($sectionConfig->getRelationsToRead() as $relationName => $columns) {
                if (is_int($relationName)) {
                    $relationName = $columns;
                    $columns = ['*'];
                }
                $relationsToRead[$relationName] = $columns;
            }
            foreach ($sectionConfig->getViewersForRelations() as $viewer) {
                if (!array_key_exists($viewer->getRelation()->getName(), $relationsToRead)) {
                    $relationsToRead[$viewer->getRelation()->getName()] = ['*'];
                }
            }
        }
        if (!$object->fromDb($conditions, [], array_keys($relationsToRead))->existsInDb()) {
            return $this->makeRecordNotFoundResponse($table);
        }
        $this->logDbRecordLoad($object, static::getResourceName());
        $data = $object->toArray([], $relationsToRead, true);
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
        return cmfJsonResponse()->setData($sectionConfig->prepareRecord($data));
    }

    public function getDefaultValuesForFormInputs() {
        /** @var FormConfig $config */
        if (!$this->isCreateAllowed() && !$this->isEditAllowed()) {
            return $this->makeAccessDeniedReponse(cmfTransGeneral('.action.create.forbidden'));
        }
        $formConfig = $this->getFormConfig();
        $data = $formConfig->alterDefaultValues(static::getTable()->newRecord()->getDefaults([], false, true));
        return cmfJsonResponse()->setData($formConfig->prepareRecord($data));
    }

    public function addRecord() {
        if (!$this->isCreateAllowed()) {
            return $this->makeAccessDeniedReponse(cmfTransGeneral('.action.create.forbidden'));
        }
        $table = static::getTable();
        $formConfig = $this->getFormConfig();
        $data = $formConfig->modifyIncomingDataBeforeValidation(
            $this->getRequest()->only(array_keys($formConfig->getValueViewers())),
            true
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
                $dataToSave = $this->getDataToSaveIntoMainRecord($data, $formConfig);
                $object = $table->newRecord()->fromData($dataToSave, false);
                $this->logDbRecordBeforeChange($object, static::getResourceName());
                $object->save(['*'], true);
                $ret = $this->afterDataSaved($data, $object, true, $table, $formConfig);
                $this->logDbRecordAfterChange($object);
                return $ret;
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
            } finally {
                if ($table::inTransaction()) {
                    $table::rollBackTransaction();
                    /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
                    throw new DbException('Transaction was not closed');
                }
            }
        }
        throw new \BadMethodCallException('There is no data to save');
    }

    public function updateRecord() {
        if (!$this->isEditAllowed()) {
            return $this->makeAccessDeniedReponse(cmfTransGeneral('.action.edit.forbidden'));
        }
        $table = static::getTable();
        $formConfig = $this->getFormConfig();
        $expectedFields = array_keys($formConfig->getValueViewers());
        $expectedFields[] = $table->getPkColumnName();
        $data = $formConfig->modifyIncomingDataBeforeValidation(
            $this->getRequest()->only($expectedFields),
            false
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
                $this->logDbRecordBeforeChange($object, static::getResourceName());
                $dataToSave = $this->getDataToSaveIntoMainRecord($data, $formConfig);
                $object->begin()->updateValues($dataToSave)->commit(['*'], true);
                $ret = $this->afterDataSaved($data, $object, false, $table, $formConfig);
                $this->logDbRecordAfterChange($object);
                return $ret;
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
            } finally {
                if ($table::inTransaction()) {
                    $table::rollBackTransaction();
                    /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
                    throw new DbException('Transaction was not closed');
                }
            }
        }
        throw new \BadMethodCallException('There is no data to save');
    }

    /**
     * @param array $data
     * @param FormConfig $formConfig
     * @return array
     */
    protected function getDataToSaveIntoMainRecord(array $data, FormConfig $formConfig) {
        $viewersWithOwnValueSavingMethods = $formConfig->getInputsWithOwnValueSavingMethods();
        /** @var FormInput $viewer */
        foreach ($formConfig->getStandaloneViewers() as $key => $viewer) {
            array_forget($data, $key);
            if ($viewer->hasRelation() && empty($data[$viewer->getRelation()->getName()])) {
                unset($data[$viewer->getRelation()->getName()]);
            }
        }
        /** @var FormInput $viewer */
        foreach ($viewersWithOwnValueSavingMethods as $key => $viewer) {
            array_forget($data, $key);
            if ($viewer->hasRelation() && empty($data[$viewer->getRelation()->getName()])) {
                unset($data[$viewer->getRelation()->getName()]);
            }
        }
        return $data;
    }

    /**
     * @param array $data
     * @param RecordInterface $object
     * @param bool $created
     * @param TableInterface $table
     * @param FormConfig $formConfig
     * @return JsonResponse
     * @throws ScaffoldException
     */
    protected function afterDataSaved(array $data, RecordInterface $object, $created, TableInterface $table, FormConfig $formConfig) {
        foreach ($formConfig->getInputsWithOwnValueSavingMethods() as $formInput) {
            call_user_func($formInput->getValueSaver(), array_get($data, $formInput->getName()), $object, $created);
        }
        if ($formConfig->hasAfterSaveCallback()) {
            $success = call_user_func($formConfig->getAfterSaveCallback(), $created, $data, $object, $formConfig);
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
        return cmfJsonResponse()
            ->setMessage(
                cmfTransGeneral($created ? '.form.resource_created_successfully' : '.form.resource_updated_successfully')
            )
            ->setRedirect(
                $created
                    ? routeToCmfItemEditForm(static::getResourceName(), $object->getPrimaryKeyValue())
                    : 'reload'
            );
    }

    public function updateBulkOfRecords() {
        if (!$this->isEditAllowed()) {
            return $this->makeAccessDeniedReponse(cmfTransGeneral('.action.edit.forbidden'));
        }
        $table = static::getTable();
        $formConfig = $this->getFormConfig();
        $data = $formConfig->modifyIncomingDataBeforeValidation(
            array_intersect_key($this->getRequest()->input(), $formConfig->getBulkEditableColumns()), //< do not use request->only() !!!
            false,
            true
        );
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
        if (\Gate::denies('resource.update_bulk', [static::getResourceName(), $conditions])) {
            return $this->makeAccessDeniedReponse(cmfTransGeneral('.action.edit.forbidden'));
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
            ->goBack(routeToCmfItemsTable(static::getResourceName()));
    }

    public function deleteRecord($id) {
        if (!$this->isDeleteAllowed()) {
            return $this->makeAccessDeniedReponse(cmfTransGeneral('.action.delete.forbidden'));
        }
        $table = static::getTable();
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
        $this->logDbRecordBeforeChange($object, static::getResourceName());
        $object->delete();
        $this->logDbRecordAfterChange($object);
        return cmfJsonResponse()
            ->setMessage(cmfTransGeneral('.action.delete.success'))
            ->goBack(routeToCmfItemsTable(static::getResourceName()));
    }

    public function deleteBulkOfRecords() {
        if (!$this->isDeleteAllowed()) {
            return $this->makeAccessDeniedReponse(cmfTransGeneral('.action.delete.forbidden'));
        }
        $conditions = $this->getSelectConditionsForBulkActions();
        if (!is_array($conditions)) {
            return $conditions; //< response
        }
        if (\Gate::denies('resource.delete_bulk', [static::getResourceName(), $conditions])) {
            return $this->makeAccessDeniedReponse(cmfTransGeneral('.action.delete.forbidden'));
        }
        $deletedCount = static::getTable()->delete($conditions);
        $message = $deletedCount
            ? cmfTransGeneral('.action.delete_bulk.success', ['count' => $deletedCount])
            : cmfTransGeneral('.action.delete_bulk.nothing_deleted');
        return cmfJsonResponse()
            ->setMessage($message)
            ->goBack(routeToCmfItemsTable(static::getResourceName()));
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
            $conditions[static::getTable()->getPkColumnName()] = $this->getRequest()->input($idsField);
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

    public function changeItemPosition($id, $beforeId, $columnName, $direction) {
        $dataGridConfig = $this->getDataGridConfig();
        if (!$dataGridConfig->isRowsReorderingEnabled()) {
            return $this->makeAccessDeniedReponse(cmfTransGeneral('.action.change_position.forbidden'));
        }
        $table = static::getTable();
        $movedObject = $table->newRecord();
        $beforeObject = $table->newRecord();
        if (
            count($movedObject::getPrimaryKeyColumn()->validateValue($id)) > 0
            || count($beforeObject::getPrimaryKeyColumn()->validateValue($beforeId)) > 0
        ) {
            return $this->makeRecordNotFoundResponse($table);
        }
        $specialConditions = $dataGridConfig->getSpecialConditions();
        if (
            !$movedObject->fromDb(array_merge($specialConditions, [$table->getPkColumnName() => $id]))->existsInDb()
            || !$beforeObject->fromDb(array_merge($specialConditions, [$table->getPkColumnName() => $beforeId]))->existsInDb()
        ) {
            return $this->makeRecordNotFoundResponse($table);
        }
        // todo: get 2 nearest record to $beforeId according to direction and decide if there is a place to insert $movedObject between

        $specialConditions = $dataGridConfig->getSpecialConditions();
        $specialConditions['ORDER'] = '';
        $positionColumnName = $dataGridConfig->getRowsPositioningColumns();
        $movedObject::getTable()->beginTransaction();
        $movedObject->updateValue($positionColumnName, $postion, false);
        $movedObject::getTable()->commitTransaction();
        return cmfJsonResponse()
            ->setMessage(cmfTransGeneral('.action.change_position.success'));
    }

}
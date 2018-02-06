<?php

namespace PeskyCMF\Scaffold;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use PeskyCMF\HttpCode;
use PeskyCMF\Scaffold\DataGrid\DataGridColumn;
use PeskyCMF\Scaffold\Form\FormConfig;
use PeskyCMF\Scaffold\Form\FormInput;
use PeskyORM\Core\DbExpr;
use PeskyORM\Exception\DbException;
use PeskyORM\Exception\InvalidDataException;
use PeskyORM\ORM\RecordInterface;
use PeskyORM\ORM\TableInterface;
use PeskyORMLaravel\Db\Column\RecordPositionColumn;

abstract class NormalTableScaffoldConfig extends ScaffoldConfig {

    public function getRecordsForDataGrid() {
        if (!$this->isSectionAllowed()) {
            return $this->makeAccessDeniedReponse($this->translateGeneral('message.access_denied_to_scaffold'));
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
                    if (AbstractValueViewer::isComplexViewerName($config['column'])) {
                        list($colName, $keyName) = AbstractValueViewer::splitComplexViewerName($config['column']);
                        $conditions['ORDER'][] = DbExpr::create("`$colName`->>``$keyName`` {$config['dir']}", false);
                    } else {
                        $conditions['ORDER'][$config['column']] = $config['dir'];
                    }

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
        foreach (array_keys($dataGridConfig->getViewersLinkedToDbColumns(false)) as $originalColName) {
            list($colName, $keyName) = AbstractValueViewer::splitComplexViewerName($originalColName);
            if (array_key_exists($colName, $dbColumns)) {
                if ($dbColumns[$colName]->isItExistsInDb()) {
                    if ($keyName) {
                        $columnsToSelect[$originalColName] = DbExpr::create("`{$colName}`->>``{$keyName}``", false);
                    } else {
                        $columnsToSelect[] = $colName;
                    }
                } else {
                    $virtualColumns[] = implode('.', $colName);
                }
            } else {
                throw new \UnexpectedValueException(
                    "Column '{$colName}' does not exist in " . get_class(static::getTable()->getTableStructure())
                );
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
        if ($isItemDetails) {
            $sectionConfig = $this->getItemDetailsConfig();
        } else {
            $sectionConfig = $this->getFormConfig();
        }
        if (
            ($isItemDetails && !$this->isDetailsViewerAllowed())
            || (!$isItemDetails && !$this->isEditAllowed())
        ) {
            return $this->makeAccessDeniedReponse(
                $sectionConfig->translateGeneral($isItemDetails ? 'message.forbidden' : 'message.edit.forbidden')
            );
        }
        $object = $table->newRecord();
        if (count($object::getPrimaryKeyColumn()->validateValue($id)) > 0) {
            return $this->makeRecordNotFoundResponse($table);
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
        $dbColumns = static::getTable()->getTableStructure()->getColumns();
        $columnsToSelect = $sectionConfig->getAdditionalColumnsToSelect();
        foreach (array_keys($sectionConfig->getViewersLinkedToDbColumns(false)) as $colName) {
            if (array_key_exists($colName, $dbColumns)) {
                if ($dbColumns[$colName]->isItExistsInDb()) {
                    $columnsToSelect[] = $colName;
                }
            } else {
                throw new \UnexpectedValueException(
                    "Column '{$colName}' does not exist in " . get_class(static::getTable()->getTableStructure())
                );
            }
        }
        if (!$object->fromDb($conditions, array_unique($columnsToSelect), array_keys($relationsToRead))->existsInDb()) {
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
                $sectionConfig->translateGeneral($isItemDetails ? 'message.forbidden_for_record' : 'message.edit.forbidden_for_record')
            );
        }
        return cmfJsonResponse()->setData($sectionConfig->prepareRecord($data));
    }

    public function getDefaultValuesForFormInputs() {
        $formConfig = $this->getFormConfig();
        if (!$this->isCreateAllowed() && !$this->isEditAllowed()) {
            return $this->makeAccessDeniedReponse($formConfig->translateGeneral('message.create.forbidden'));
        }
        $data = $formConfig->alterDefaultValues(static::getTable()->newRecord()->getDefaults([], false, true));
        return cmfJsonResponse()->setData($formConfig->prepareRecord($data));
    }

    public function addRecord() {
        $formConfig = $this->getFormConfig();
        if (!$this->isCreateAllowed()) {
            return $this->makeAccessDeniedReponse($formConfig->translate('message.create.forbidden'));
        }
        $table = static::getTable();
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
        $formConfig = $this->getFormConfig();
        if (!$this->isEditAllowed()) {
            return $this->makeAccessDeniedReponse($formConfig->translateGeneral('message.edit.forbidden'));
        }
        $table = static::getTable();
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
            return $this->makeAccessDeniedReponse($formConfig->translateGeneral('message.edit.forbidden_for_record'));
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
        $url = 'reload';
        if ($created && empty($this->getRequest()->input('create_another'))) {
            $url = routeToCmfItemEditForm(static::getResourceName(), $object->getPrimaryKeyValue());
        }
        return cmfJsonResponse()
            ->setMessage(
                $formConfig->translateGeneral($created ? 'message.create.success' : 'message.edit.success')
            )
            ->setRedirect($url);
    }

    public function updateBulkOfRecords() {
        $formConfig = $this->getFormConfig();
        if (!$this->isEditAllowed()) {
            return $this->makeAccessDeniedReponse($formConfig->translateGeneral('message.forbidden'));
        }
        $table = static::getTable();
        $data = $formConfig->modifyIncomingDataBeforeValidation(
            array_intersect_key($this->getRequest()->input(), $formConfig->getBulkEditableColumns()), //< do not use request->only() !!!
            false,
            true
        );
        if (empty($data)) {
            return cmfJsonResponse(HttpCode::INVALID)
                ->setMessage($formConfig->translateGeneral('bulk_edit.message.no_data_to_save'));
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
            return $this->makeAccessDeniedReponse($formConfig->translateGeneral('bulk_edit.message.forbidden'));
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
            ? $formConfig->translateGeneral('bulk_edit.message.success', ['count' => $updatedCount])
            : $formConfig->translateGeneral('bulk_edit.message.nothing_updated');
        return cmfJsonResponse()
            ->setMessage($message)
            ->goBack(routeToCmfItemsTable(static::getResourceName()));
    }

    public function deleteRecord($id) {
        if (!$this->isDeleteAllowed()) {
            return $this->makeAccessDeniedReponse($this->translateGeneral('message.delete.forbidden'));
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
            return $this->makeAccessDeniedReponse($this->translateGeneral('message.delete.forbidden_for_record'));
        }
        $this->logDbRecordBeforeChange($object, static::getResourceName());
        $object->delete();
        $this->logDbRecordAfterChange($object);
        return cmfJsonResponse()
            ->setMessage($this->translateGeneral('message.delete.success'))
            ->goBack(routeToCmfItemsTable(static::getResourceName()));
    }

    public function deleteBulkOfRecords() {
        if (!$this->isDeleteAllowed()) {
            return $this->makeAccessDeniedReponse($this->translateGeneral('message.delete.forbidden'));
        }
        $conditions = $this->getSelectConditionsForBulkActions();
        if (!is_array($conditions)) {
            return $conditions; //< response
        }
        if (\Gate::denies('resource.delete_bulk', [static::getResourceName(), $conditions])) {
            return $this->makeAccessDeniedReponse(
                $this->getDataGridConfig()->translateGeneral('bulk_actions.message.delete_bulk.forbidden')
            );
        }
        $deletedCount = static::getTable()->delete($conditions);
        $message = $deletedCount
            ? $this->getDataGridConfig()->translateGeneral('bulk_actions.message.delete_bulk.success', ['count' => $deletedCount])
            : $this->getDataGridConfig()->translateGeneral('bulk_actions.message.delete_bulk.nothing_deleted');
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
        $formConfig = $this->getFormConfig();
        $specialConditions = $formConfig->getSpecialConditions();
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
                return cmfJsonResponseForValidationErrors(
                    [$conditionsField => 'JSON expected'],
                    $formConfig->translateGeneral('message.validation_errors')
                );
            }
            if (!empty($encodedConditions)) {
                $filterConditions = $this
                    ->getDataGridFilterConfig()
                    ->buildConditionsFromSearchRules($encodedConditions);
                $conditions = array_merge($filterConditions, $specialConditions);
            }
        } else {
            return cmfJsonResponseForValidationErrors(
                [
                    $idsField => 'List of items IDs of filtering conditions expected',
                    $conditionsField => 'List of items IDs of filtering conditions expected',
                ],
                $formConfig->translateGeneral('message.validation_errors')
            );
        }
        return $conditions;
    }

    public function changeItemPosition($id, $beforeOrAfter, $otherId, $columnName, $sortDirection) {
        $dataGridConfig = $this->getDataGridConfig();
        if (
            !$dataGridConfig->isRowsReorderingEnabled()
            || !in_array($columnName, $dataGridConfig->getRowsPositioningColumns(), true)
        ) {
            return $this->makeAccessDeniedReponse($dataGridConfig->translateGeneral('message.change_position.forbidden'));
        }
        $table = static::getTable();
        if (
            count($table::getPkColumn()->validateValue($id)) > 0
            || count($table::getPkColumn()->validateValue($otherId)) > 0
        ) {
            return $this->makeRecordNotFoundResponse($table);
        }
        $specialConditions = $dataGridConfig->getSpecialConditions();
        /** @var RecordPositionColumn $columnConfig */
        $columnConfig = static::getTable()->getTableStructure()->getColumn($columnName);
        $movedRecord = $table::getInstance()
            ->newRecord()
            ->fromDb(array_merge($specialConditions, [$table->getPkColumnName() => $id]));
        if (!$movedRecord->existsInDb()) {
            return $this->makeRecordNotFoundResponse($table);
        }
        $position1 = $table::selectValue(
            DbExpr::create("`$columnName`"),
            array_merge($specialConditions, [$table->getPkColumnName() => $otherId])
        );
        if ($position1 === null) {
            // this value must be present to continue
            return $this->makeRecordNotFoundResponse($table);
        }
        $isFloat = $columnConfig->getType() === $columnConfig::TYPE_FLOAT;
        $position1 = $isFloat ? (float)$position1 : (int)$position1;
        // We have next situations here:
        // 1. $sortDirection = 'asc', $beforeOrAfter = 'before':
        //      We need to find out previous record's position using ORDER BY $columnName ASC and <= equation on $columnName
        // 2. $sortDirection = 'asc', $beforeOrAfter = 'after':
        //      We need to find out next record's position using ORDER BY $columnName ASC and <= equation on $columnName
        // 3. $sortDirection = 'desc', $beforeOrAfter = 'before':
        //      same as 2
        // 4. $sortDirection = 'desc', $beforeOrAfter = 'after':
        //      same as 1
        $sortDirection = strtolower($sortDirection);
        $beforeOrAfter = strtolower($beforeOrAfter);
        $isAscending = ($sortDirection === 'asc' && $beforeOrAfter === 'before') || ($sortDirection === 'desc' && $beforeOrAfter === 'after');
        $position2 = $table::selectValue(
            DbExpr::create("`$columnName`"),
            [
                $columnName . ($isAscending ? ' <=' : ' >=') => $position1,
                $table::getPkColumnName() . ' !=' => [$otherId, $movedRecord->getPrimaryKeyValue()],
                'ORDER' => [$columnName => 'ASC']
            ]
        );
        $increment = $columnConfig instanceof RecordPositionColumn ? $columnConfig->getIncrement() : 100;
        if ($position2 === null) {
            $position2 = $isAscending ? 0 : ($position1 + $increment * 2);
        } else {
            $position2 = $isFloat ? (float)$position2 : (int)$position2;
        }
        // calculate distance between records to find out if there is any space to add $movedRecord between them
        $distance = abs($position1 - $position2);
        $table::beginTransaction();
        if ($distance > 1) {
            $newPosition = min($position1, $position2) + ($isFloat ? (float)($distance / 2) : (int)($distance / 2));
        } else {
            // no free space - shift other records and insert $movedRecord to freed space
            $table::update(
                [$columnName => DbExpr::create("`{$columnName}` + ``{$increment}`` + ``{$increment}``")],
                [
                    $columnName . '>=' => max($position1, $position2)
                ]
            );
            $newPosition = max($position1, $position2) + $increment;
        }
        $movedRecord->begin()->updateValue($columnConfig, $newPosition, false)->commit();
        $table::commitTransaction();
        return cmfJsonResponse()
            ->setMessage($dataGridConfig->translateGeneral('message.change_position.success'));
    }

}
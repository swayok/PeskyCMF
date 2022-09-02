<?php

declare(strict_types=1);

namespace PeskyCMF\Scaffold;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use PeskyCMF\Http\CmfJsonResponse;
use PeskyCMF\HttpCode;
use PeskyCMF\Scaffold\Form\FormConfig;
use PeskyCMF\Scaffold\Form\FormInput;
use PeskyCMF\Scaffold\ItemDetails\ItemDetailsConfig;
use PeskyORM\Core\DbExpr;
use PeskyORM\Exception\DbException;
use PeskyORM\Exception\InvalidDataException;
use PeskyORM\ORM\RecordInterface;
use PeskyORM\ORM\RecordValueHelpers;
use PeskyORM\ORM\TableInterface;
use PeskyORMColumns\Column\RecordPositionColumn;
use Symfony\Component\HttpFoundation\Response;

abstract class NormalTableScaffoldConfig extends ScaffoldConfig
{
    
    protected bool $goBackAfterCreation = false;
    protected bool $goBackAfterEditing = false;
    
    public function getRecordsForDataGrid(): JsonResponse
    {
        if (!$this->isSectionAllowed()) {
            return $this->makeAccessDeniedReponse($this->translateGeneral('message.access_denied_to_scaffold'));
        }
        $request = $this->getRequest();
        $dataGridConfig = $this->getDataGridConfig();
        $dataGridFilterConfig = $this->getDataGridFilterConfig();
        $conditions = [
            'LIMIT' => (int)$request->query('length', $dataGridConfig->getRecordsPerPage()),
            'OFFSET' => (int)$request->query('start', 0),
            'ORDER' => [],
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
        $defaultOrderByColumn = $dataGridConfig->getOrderBy();
        $defaultOrderByDirection = $dataGridConfig->getOrderDirection();
        $defaultDirectionWithNulls = stripos($defaultOrderByDirection, ' nulls ') !== false;
        $order = $request->query('order', [
            [
                'column' => $defaultOrderByColumn,
                'dir' => $defaultOrderByDirection,
            ],
        ]);
        $columns = $request->query('columns', []);
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
                        [$colName, $keyName] = AbstractValueViewer::splitComplexViewerName($config['column']);
                        if (static::getTable()->getTableStructure()->hasRelation($colName)) {
                            $conditions['ORDER'][$colName . '.' . $keyName] = $config['dir'];
                        } else {
                            $conditions['ORDER'][] = DbExpr::create("`$colName`->>``$keyName`` {$config['dir']}", false);
                        }
                    } elseif (
                        $defaultDirectionWithNulls
                        && $config['column'] === $defaultOrderByColumn
                        && stripos($defaultOrderByDirection, $config['dir']) !== false
                    ) {
                        $conditions['ORDER'][$config['column']] = $defaultOrderByDirection;
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
        $columnsToSelect = [static::getTable()->getTableStructure()->getPkColumnName()];
        $virtualColumns = [];
        foreach (array_keys($dataGridConfig->getViewersLinkedToDbColumns(false)) as $originalColName) {
            [$colName, $keyName] = AbstractValueViewer::splitComplexViewerName($originalColName);
            if (array_key_exists($colName, $dbColumns)) {
                if ($dbColumns[$colName]->isItExistsInDb()) {
                    if ($keyName) {
                        $columnsToSelect[$originalColName] = DbExpr::create("`{$colName}`->>``{$keyName}``", false);
                    } else {
                        $columnsToSelect[] = $colName;
                    }
                } else {
                    $virtualColumns[] = implode('.', (array)$colName);
                }
            } else {
                throw new \UnexpectedValueException(
                    "Column '{$colName}' does not exist in " . get_class(static::getTable()->getTableStructure())
                );
            }
        }
        $columnsToSelect = array_merge(
            $columnsToSelect,
            $this->getRelationsToRead($dataGridConfig)
        );
        $records = [];
        if ($dataGridConfig->isBigTable()) {
            $pkColumnName = static::getTable()->getTableStructure()->getPkColumnName();
            // note: joins will be automatically loaded from WHERE conditions in most cases
            $idsSelect = static::getTable()->select([$pkColumnName], $conditions);
            $totalCount = $idsSelect->totalCount();
            if ($idsSelect->count()) {
                $mainQueryConditions = array_intersect_key($conditions, ['ORDER' => '']);
                $mainQueryConditions['id'] = $idsSelect->getValuesForColumn($pkColumnName);
                $result = static::getTable()->select($columnsToSelect, $mainQueryConditions);
                $records = $dataGridConfig->prepareRecords($result->toArrays(), $virtualColumns);
            }
        } else {
            $result = static::getTable()->select($columnsToSelect, $conditions);
            $totalCount = $result->totalCount();
            if ($result->count()) {
                $records = $dataGridConfig->prepareRecords($result->toArrays(), $virtualColumns);
            }
        }
        
        
        return new CmfJsonResponse([
            'draw' => $request->query('draw'),
            'recordsTotal' => $totalCount,
            'recordsFiltered' => $totalCount,
            'data' => $records,
        ]);
    }
    
    public function getRecordValues(?string $id = null): JsonResponse
    {
        $sectionConfig = $this->getScaffoldSectionConfigForRecordInfoAndValidateAccess();
        $table = static::getTable();
        $object = $table->newRecord();
        if (count($object::getPrimaryKeyColumn()->validateValue($id, false, false)) > 0) {
            return $this->makeRecordNotFoundResponse();
        }
        $conditions = $sectionConfig->getSpecialConditions();
        $conditions[$table::getPkColumnName()] = $id;
        $relationsToRead = $id !== null ? $this->getRelationsToRead($sectionConfig) : [];
        $columnsToSelect = $sectionConfig->getAdditionalColumnsToSelect();
        /** @var RenderableValueViewer $valueViewer */
        foreach ($sectionConfig->getViewersLinkedToDbColumns(false) as $valueViewer) {
            if ($valueViewer->getTableColumn()->isItExistsInDb()) {
                $columnsToSelect[] = $valueViewer->getTableColumn()->getName();
            }
        }
        if (!$object->fetch($conditions, array_unique($columnsToSelect), $relationsToRead)->existsInDb()) {
            return $this->makeRecordNotFoundResponse();
        }
        $this->logDbRecordLoad($object);
        $data = $object->toArray([], $relationsToRead, true);
        $isItemDetails = $sectionConfig instanceof ItemDetailsConfig;
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
        return new CmfJsonResponse($sectionConfig->prepareRecord($data));
    }
    
    public function getDefaultValuesForFormInputs(): JsonResponse
    {
        $formConfig = $this->getFormConfig();
        if (!$this->isCreateAllowed() && !$this->isEditAllowed()) {
            return $this->makeAccessDeniedReponse($formConfig->translateGeneral('message.create.forbidden'));
        }
        $data = $formConfig->alterDefaultValues(static::getTable()->newRecord()->getDefaults([], false, true));
        return new CmfJsonResponse($formConfig->prepareRecord($data));
    }
    
    public function addRecord(): JsonResponse
    {
        $formConfig = $this->getFormConfig();
        if (!$this->isCreateAllowed()) {
            return $this->makeAccessDeniedReponse($formConfig->translateGeneral('message.create.forbidden'));
        }
        $table = static::getTable();
        $data = $formConfig->modifyIncomingDataBeforeValidation(
            $this->getFilteredIncomingData($formConfig, true),
            true
        );
        unset($data[$table::getPkColumnName()]);
        $errors = $formConfig->validateDataForCreate($data);
        if (count($errors) !== 0) {
            return $this->makeValidationErrorsJsonResponse($errors);
        }
        if ($formConfig->hasBeforeSaveCallback()) {
            $data = call_user_func($formConfig->getBeforeSaveCallback(), true, $data, $formConfig);
            if (empty($data)) {
                throw new ScaffoldException('Empty $data received from beforeSave callback');
            }
            if ($formConfig->shouldRevalidateDataAfterBeforeSaveCallback(true)) {
                // revalidate
                $errors = $formConfig->validateDataForCreate($data, [], true);
                if (count($errors) !== 0) {
                    return $this->makeValidationErrorsJsonResponse($errors);
                }
            }
        }
        unset($data[$table::getPkColumnName()]); //< to be 100% sure =)
        if (!empty($data)) {
            try {
                $dataToSave = $this->getDataToSaveIntoMainRecord($data, $formConfig);
                $record = $table->newRecord()->fromData($dataToSave, false);
                $this->logDbRecordBeforeChange($record);
                $table::beginTransaction();
                $response = $this->doRecordSave($record, true);
                if ($response) {
                    if ($table::inTransaction()) {
                        $table::commitTransaction();
                    }
                    $this->logDbRecordAfterChange($record);
                    return $response;
                }
                $ret = $this->afterDataSaved($data, $record, true, $table, $formConfig);
                $this->logDbRecordAfterChange($record);
                return $ret;
            } catch (InvalidDataException $exc) {
                if ($table::inTransaction()) {
                    $table::rollBackTransaction();
                }
                return $this->makeValidationErrorsJsonResponse($exc->getErrors());
            } catch (\Throwable $exc) {
                if ($table::inTransaction()) {
                    $table::rollBackTransaction();
                }
                throw $exc;
            } finally {
                if ($table::inTransaction()) {
                    $table::rollBackTransaction();
                    throw new DbException(
                        'Transaction was not closed. Check if afterDataSaved method called and transaction is closed within it.'
                    );
                }
            }
        }
        throw new \BadMethodCallException('There is no data to save');
    }
    
    public function updateRecord(string $idFromRoute): JsonResponse
    {
        $formConfig = $this->getFormConfig();
        if (!$this->isEditAllowed()) {
            return $this->makeAccessDeniedReponse($formConfig->translateGeneral('message.edit.forbidden'));
        }
        $table = static::getTable();
        $data = $formConfig->modifyIncomingDataBeforeValidation(
            $this->getFilteredIncomingData($formConfig, false),
            false
        );
        if (!$this->getRequest()->input($table::getPkColumnName())) {
            return $this->makeRecordNotFoundResponse();
        }
        $id = $this->getRequest()->input($table::getPkColumnName());
        if ($idFromRoute !== (string)$id) {
            return $this->makeAccessDeniedReponse($formConfig->translateGeneral('message.edit.ids_missmatch'));
        }
        $record = $table->newRecord();
        if (count($record::getPrimaryKeyColumn()->validateValue($id, false, false)) > 0) {
            return $this->makeRecordNotFoundResponse();
        }
        $conditions = $formConfig->getSpecialConditions();
        $conditions[$table::getPkColumnName()] = $id;
        if (!$record->fetch($conditions)->existsInDb()) {
            return $this->makeRecordNotFoundResponse();
        }
        if (!$this->isRecordEditAllowed($record->toArrayWithoutFiles())) {
            return $this->makeAccessDeniedReponse($formConfig->translateGeneral('message.edit.forbidden_for_record'));
        }
        $this->validateDataForEdit($formConfig, $data, $record);
        unset($data[$table::getPkColumnName()]);
        if (!empty($data)) {
            try {
                $this->logDbRecordBeforeChange($record);
                $dataToSave = $this->getDataToSaveIntoMainRecord($data, $formConfig);
                $relationsData = array_intersect_key($dataToSave, $record::getRelations());
                $dataToSave = array_diff_key($dataToSave, $relationsData);
                $record->begin()->updateValues($dataToSave);
                $table::beginTransaction();
                $response = $this->doRecordSave($record, false);
                if ($response instanceof Response) {
                    if ($table::inTransaction()) {
                        $table::commitTransaction();
                    }
                    $this->logDbRecordAfterChange($record);
                    return $response;
                }
                $this->updateRelatedRecords($record, $relationsData);
                $ret = $this->afterDataSaved($data, $record, false, $table, $formConfig);
                $this->logDbRecordAfterChange($record);
                return $ret;
            } catch (InvalidDataException $exc) {
                if ($table::inTransaction()) {
                    $table::rollBackTransaction();
                }
                return $this->makeValidationErrorsJsonResponse($exc->getErrors());
            } catch (\Exception $exc) {
                if ($table::inTransaction()) {
                    $table::rollBackTransaction();
                }
                throw $exc;
            } finally {
                if ($table::inTransaction()) {
                    $table::rollBackTransaction();
                    throw new DbException(
                        'Transaction was not closed. Check if afterDataSaved method called and transaction is closed within it.'
                    );
                }
            }
        }
        throw new \BadMethodCallException('There is no data to save');
    }
    
    /**
     * You may return instance of JsonResponse to immediately finish request (for example when validation error happens)
     */
    protected function doRecordSave(RecordInterface $record, bool $isCreation): ?JsonResponse
    {
        if ($isCreation) {
            $record->save(['*'], true);
        } else {
            $record->commit(['*'], true);
        }
        return null;
    }
    
    protected function getFilteredIncomingData(FormConfig $formConfig, bool $isCreation): array
    {
        $expectedDataKeys = [];
        /** @var FormInput $valueViewer */
        foreach ($formConfig->getValueViewers() as $valueViewer) {
            if (($isCreation && $valueViewer->isShownOnCreate()) || (!$isCreation && $valueViewer->isShownOnEdit())) {
                if ($valueViewer::isComplexViewerName($valueViewer->getName())) {
                    [$colName,] = $valueViewer::splitComplexViewerName($valueViewer->getName());
                    $expectedDataKeys[$colName] = $colName;
                } else {
                    $expectedDataKeys[$valueViewer->getName()] = $valueViewer->getName();
                }
            }
        }
        if (!$isCreation) {
            $expectedDataKeys[static::getTable()->getPkColumnName()] = static::getTable()->getPkColumnName();
        }
        
        $data = $this->getRequest()->all();
        return array_intersect_key($data, $expectedDataKeys);
    }
    
    protected function updateRelatedRecords(RecordInterface $object, array $updatesForRelations): void
    {
        foreach ($updatesForRelations as $relationName => $relationUpdates) {
            $relation = $object::getRelation($relationName);
            if ($relation->getType() === $relation::HAS_ONE) {
                $relatedRecord = $object->getRelatedRecord($relationName, true);
                $relationPk = RecordValueHelpers::normalizeValue(
                    Arr::get($relationUpdates, $relatedRecord::getPrimaryKeyColumnName()),
                    $relatedRecord::getPrimaryKeyColumn()->getType()
                );
                unset($relationUpdates[$relatedRecord::getPrimaryKeyColumnName()]);
                $relationUpdates[$relation->getForeignColumnName()] = $object->getPrimaryKeyValue();
                if ($relatedRecord->existsInDb()) {
                    if ($relationPk && $relationPk === $relatedRecord->getPrimaryKeyValue()) {
                        // same related record - just update
                        $relatedRecord->begin()->updateValues($relationUpdates, true)->commit();
                        continue;
                    } elseif (!$relationPk || $relationPk !== $relatedRecord->getPrimaryKeyValue()) {
                        // related record changed - unset existing and proceed to attach new one
                        $relatedRecord
                            ->begin()
                            ->updateValue($relation->getForeignColumnName(), null, false)
                            ->commit();
                        $object->unsetRelatedRecord($relationName);
                        $relatedRecord->reset();
                    }
                }
                // attach new related record
                if ($relationPk) {
                    // related record already exists in DB but may belong to other record or to none - reattach it
                    $relatedRecord->reset()->fetchByPrimaryKey($relationPk);
                    unset($relationUpdates[$relatedRecord::getPrimaryKeyColumnName()]);
                    $relatedRecord->begin()->updateValues($relationUpdates, false)->commit();
                } else {
                    // create new related record and attach it
                    $relatedRecord->reset()->updateValues($relationUpdates, false)->save();
                }
            } elseif ($relation->getType() === $relation::BELONGS_TO) {
                $relatedRecord = $object->getRelatedRecord($relationName, true);
                $relationPk = RecordValueHelpers::normalizeValue(
                    Arr::get($relationUpdates, $relatedRecord::getPrimaryKeyColumnName()),
                    $relatedRecord::getPrimaryKeyColumn()->getType()
                );
                unset($relationUpdates[$relatedRecord::getPrimaryKeyColumnName()]);
                if ($relatedRecord->existsInDb()) {
                    if (!$relationPk) {
                        $relationPk = $relatedRecord->getPrimaryKeyValue();
                    }
                    if ($relationPk === $relatedRecord->getPrimaryKeyValue()) {
                        // just update
                        $relatedRecord->begin()->updateValues($relationUpdates)->commit();
                        continue;
                    } else {
                        // related record changed - unset relation and continue to attach new 1
                        $object->unsetRelatedRecord($relationName);
                    }
                }
                $relatedRecord->reset();
                if (!$relationPk) {
                    // create new related record
                    $relatedRecord->updateValues($relationUpdates)->save();
                    $relationPk = $relatedRecord->getPrimaryKeyValue();
                } else {
                    // read existing related record from db and update it
                    $relatedRecord->reset()->fetchByPrimaryKey($relationPk);
                    $relatedRecord->begin()->updateValues($relationUpdates)->commit();
                }
                // attach new related record to this object
                $object->begin()->updateValue($relation->getLocalColumnName(), $relationPk, false)->commit();
            } else {
                // has many relations should work normally this way
                $object->begin()->updateRelatedRecord($relationName, $relationUpdates, false)->commit([$relationName], true);
            }
        }
    }
    
    protected function getDataToSaveIntoMainRecord(array $data, FormConfig $formConfig): array
    {
        $viewersWithOwnValueSavingMethods = $formConfig->getInputsWithOwnValueSavingMethods();
        /** @var FormInput $viewer */
        foreach ($formConfig->getStandaloneViewers() as $key => $viewer) {
            Arr::forget($data, $key);
            $relation = $viewer->getRelation();
            if ($relation && empty($data[$relation->getName()])) {
                unset($data[$relation->getName()]);
            }
        }
        foreach ($viewersWithOwnValueSavingMethods as $key => $viewer) {
            Arr::forget($data, $key);
            $relation = $viewer->getRelation();
            if ($relation && empty($data[$relation->getName()])) {
                unset($data[$relation->getName()]);
            }
        }
        return $data;
    }
    
    /**
     * @param array $data
     * @param RecordInterface $record
     * @param bool $isCreated
     * @param TableInterface $table
     * @param FormConfig $formConfig
     * @return JsonResponse
     * @throws ScaffoldException
     */
    protected function afterDataSaved(
        array $data,
        RecordInterface $record,
        bool $isCreated,
        TableInterface $table,
        FormConfig $formConfig
    ): JsonResponse {
        foreach ($formConfig->getInputsWithOwnValueSavingMethods() as $formInput) {
            call_user_func($formInput->getValueSaver(), Arr::get($data, $formInput->getName()), $record, $isCreated);
        }
        $this->runAfterSaveCallback($formConfig, $isCreated, $data, $record);
        $table::commitTransaction();
        return CmfJsonResponse::create()
            ->setMessage(
                $formConfig->translateGeneral($isCreated ? 'message.create.success' : 'message.edit.success')
            )
            ->setRedirect($this->getRedirectUrlAfterDataSaved($isCreated, $record) ?: 'back', $this->getUrlToItemsTable());
    }
    
    /**
     * @param bool $created
     * @param RecordInterface $object
     * @return string|null - null will close modal or return to previous page
     */
    protected function getRedirectUrlAfterDataSaved(bool $created, RecordInterface $object): ?string
    {
        if ($created && empty($this->getRequest()->input('create_another'))) {
            $url = $this->goBackAfterCreation ? 'back' : $this->getUrlToItemEditForm($object->getPrimaryKeyValue());
        } else {
            $url = (!$created && $this->goBackAfterEditing) ? 'back' : 'reload';
        }
        return $url;
    }
    
    public function updateBulkOfRecords(): JsonResponse
    {
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
            return CmfJsonResponse::create(HttpCode::INVALID)
                ->setMessage($formConfig->translateGeneral('bulk_edit.message.no_data_to_save'));
        }
        $errors = $formConfig->validateDataForBulkEdit($data);
        if (count($errors) !== 0) {
            return $this->makeValidationErrorsJsonResponse($errors);
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
                    return $this->makeValidationErrorsJsonResponse($errors);
                }
            }
        }
        $conditions = $this->getSelectConditionsForBulkActions('_');
        if ($conditions === null) {
            $message = $formConfig->translateGeneral('bulk_edit.message.nothing_updated');
        } else {
            if ($this->authGate->denies('resource.update_bulk', [static::getResourceName(), $conditions])) {
                return $this->makeAccessDeniedReponse($formConfig->translateGeneral('bulk_edit.message.forbidden'));
            }
            $table::beginTransaction();
            $updatedCount = $table::update($data, $conditions);
            $this->runBulkEditDataAfterSaveCallback($formConfig, $data);
            $table::commitTransaction();
            $message = $updatedCount
                ? $formConfig->translateGeneral('bulk_edit.message.success', ['count' => $updatedCount])
                : $formConfig->translateGeneral('bulk_edit.message.nothing_updated');
        }
        return CmfJsonResponse::create()
            ->setMessage($message)
            ->goBack($this->getUrlToItemsTable());
    }
    
    public function deleteRecord(string $id): JsonResponse
    {
        if (!$this->isDeleteAllowed()) {
            return $this->makeAccessDeniedReponse($this->translateGeneral('message.delete.forbidden'));
        }
        $table = static::getTable();
        $record = $table->newRecord();
        if (count($record::getPrimaryKeyColumn()->validateValue($id, false, false)) > 0) {
            return $this->makeRecordNotFoundResponse();
        }
        $formConfig = $this->getFormConfig();
        $conditions = $formConfig->getSpecialConditions();
        $conditions[$table::getPkColumnName()] = $id;
        if (!$record->fetch($conditions)->existsInDb()) {
            return $this->makeRecordNotFoundResponse();
        }
        if (!$this->isRecordDeleteAllowed($record->toArrayWithoutFiles())) {
            return $this->makeAccessDeniedReponse($this->translateGeneral('message.delete.forbidden_for_record'));
        }
        $this->logDbRecordBeforeChange($record);
        $response = $this->doRecordDelete($record);
        $this->logDbRecordAfterChange($record);
        if ($response) {
            return $response;
        }
        
        return CmfJsonResponse::create()
            ->setMessage($this->translateGeneral('message.delete.success'))
            ->goBack($this->getUrlToItemsTable());
    }
    
    /**
     * You may return instance of Response to immediately finish request (for example when validation error happens)
     */
    protected function doRecordDelete(RecordInterface $record): ?JsonResponse
    {
        $record->delete();
        return null;
    }
    
    public function deleteBulkOfRecords(): JsonResponse
    {
        if (!$this->isDeleteAllowed()) {
            return $this->makeAccessDeniedReponse($this->translateGeneral('message.delete.forbidden'));
        }
        $conditions = $this->getSelectConditionsForBulkActions();
        if ($conditions === null) {
            $message = $this->getDataGridConfig()->translateGeneral('bulk_actions.message.delete_bulk.nothing_deleted');
        } else {
            if ($this->authGate->denies('resource.delete_bulk', [static::getResourceName(), $conditions])) {
                return $this->makeAccessDeniedReponse(
                    $this->getDataGridConfig()->translateGeneral('bulk_actions.message.delete_bulk.forbidden')
                );
            }
            $deletedCount = static::getTable()->delete($conditions);
            $message = $deletedCount
                ? $this->getDataGridConfig()->translateGeneral('bulk_actions.message.delete_bulk.success', ['count' => $deletedCount])
                : $this->getDataGridConfig()->translateGeneral('bulk_actions.message.delete_bulk.nothing_deleted');
        }
        return CmfJsonResponse::create()
            ->setMessage($message)
            ->goBack($this->getUrlToItemsTable());
    }
    
    /**
     * @param string $inputNamePrefix - input name prefix
     *      For example if you use '_ids' instead of 'ids' - use prefix '_'
     */
    protected function getSelectConditionsForBulkActions(string $inputNamePrefix = ''): ?array
    {
        $specialConditions = $this->getDataGridConfig()->getSpecialConditions();
        $idsField = $inputNamePrefix . 'ids';
        $conditionsField = $inputNamePrefix . 'conditions';
        $pkColumnName = static::getTable()->getPkColumnName();
        $request = $this->getRequest();
        if ($request->has($idsField)) {
            if (is_string($request->get($idsField))) {
                $this->validate($request, [
                    $idsField => 'required|json',
                ]);
                $ids = json_decode($request->get($idsField), true);
                if ($request->query($idsField)) {
                    $request->query->set($idsField, $ids);
                } else {
                    $request->request->set($idsField, $ids);
                }
            }
            $this->validate($request, [
                $idsField => 'required|array',
                $idsField . '.*' => 'integer|min:1',
            ]);
            $conditions = $specialConditions;
            $conditions[$pkColumnName] = $request->get($idsField);
            return $conditions;
        } elseif ($request->has($conditionsField)) {
            $this->validate($request, [
                $conditionsField => 'string|json',
            ]);
            $encodedConditions = json_decode($request->get($conditionsField), true);
            if ($encodedConditions === false || !is_array($encodedConditions) || empty($encodedConditions['r'])) {
                abort(
                    CmfJsonResponse::create()
                        ->setErrors(
                            [$conditionsField => 'Not empty JSON array expected'],
                            $this->translateGeneral('message.validation_errors')
                        )
                );
            }
            if (empty($encodedConditions)) {
                return $specialConditions;
            }
            $filterConditions = $this
                ->getDataGridFilterConfig()
                ->buildConditionsFromSearchRules($encodedConditions);
            $ids = static::getTable()->selectColumn(
                $pkColumnName,
                array_merge($filterConditions, $specialConditions)
            );
            return empty($ids)
                ? null
                : [$pkColumnName => $ids];
        } else {
            abort(
                CmfJsonResponse::create()
                    ->setErrors(
                        [
                            $idsField => 'List of items IDs of filtering conditions expected',
                            $conditionsField => 'List of items IDs of filtering conditions expected',
                        ],
                        $this->translateGeneral('message.validation_errors')
                    )
            );
        }
    }
    
    public function changeItemPosition(
        string $id,
        string $beforeOrAfter,
        string $otherId,
        string $columnName,
        string $sortDirection
    ): JsonResponse {
        $dataGridConfig = $this->getDataGridConfig();
        if (
            !$dataGridConfig->isRowsReorderingEnabled()
            || !in_array($columnName, $dataGridConfig->getRowsPositioningColumns(), true)
        ) {
            return $this->makeAccessDeniedReponse($dataGridConfig->translateGeneral('message.change_position.forbidden'));
        }
        $table = static::getTable();
        if (
            count($table::getPkColumn()->validateValue($id, false, false)) > 0
            || count($table::getPkColumn()->validateValue($otherId, false, false)) > 0
        ) {
            return $this->makeRecordNotFoundResponse();
        }
        $specialConditions = $dataGridConfig->getSpecialConditions();
        /** @var RecordPositionColumn $columnConfig */
        $columnConfig = static::getTable()->getTableStructure()->getColumn($columnName);
        $movedRecord = $table::getInstance()
            ->newRecord()
            ->fetch(array_merge($specialConditions, [$table::getPkColumnName() => $id]));
        if (!$movedRecord->existsInDb()) {
            return $this->makeRecordNotFoundResponse();
        }
        $position1 = $table::selectValue(
            DbExpr::create("`$columnName`"),
            array_merge($specialConditions, [$table::getPkColumnName() => $otherId])
        );
        if ($position1 === null) {
            // this value must be present to continue
            return $this->makeRecordNotFoundResponse();
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
                'ORDER' => [$columnName => 'ASC'],
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
                    $columnName . '>=' => max($position1, $position2),
                ]
            );
            $newPosition = max($position1, $position2) + $increment;
        }
        $movedRecord->begin()->updateValue($columnConfig, $newPosition, false)->commit();
        $table::commitTransaction();
        return CmfJsonResponse::create()
            ->setMessage($dataGridConfig->translateGeneral('message.change_position.success'));
    }
    
    public function getRelationsToRead(ScaffoldSectionConfig $sectionConfig): array
    {
        $relationsToRead = [];
        foreach ($sectionConfig->getRelationsToRead() as $relationName => $columns) {
            if (is_int($relationName)) {
                $relationName = $columns;
                $columns = ['*'];
            }
            $relationsToRead[$relationName] = $columns;
        }
        foreach ($sectionConfig->getViewersForRelations() as $viewer) {
            $relation = $viewer->getRelation();
            if ($relation && !array_key_exists($relation->getName(), $relationsToRead)) {
                $relationsToRead[$relation->getName()] = ['*'];
            }
        }
        return $relationsToRead;
    }
    
}
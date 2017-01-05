<?php


namespace PeskyCMF\Scaffold;

use Illuminate\Http\JsonResponse;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Db\Traits\KeyValueTableHelpers;
use PeskyCMF\HttpCode;
use PeskyCMF\Scaffold\Form\FormConfig;
use PeskyCMF\Scaffold\ItemDetails\ItemDetailsConfig;
use PeskyORM\Exception\InvalidDataException;
use PeskyORM\ORM\TableInterface;

abstract class KeyValueTableScaffoldConfig extends ScaffoldConfig {

    protected $isCreateAllowed = false;
    protected $keysColumnName = 'key';
    protected $valuesColumnName = 'value';

    /**
     * @return FormConfig
     */
    protected function createFormConfig() {
        return parent::createFormConfig()->thereIsNoDbColumns();
    }

    /**
     * @return ItemDetailsConfig
     */
    protected function createItemDetailsConfig() {
        return parent::createItemDetailsConfig()->thereIsNoDbColumns();
    }

    public function renderTemplates() {
        return view(
            CmfConfig::getInstance()->scaffold_templates_view_for_key_value_table(),
            array_merge(
                $this->getConfigs(),
                ['tableNameForRoutes' => $this->getTableNameForRoutes()]
            )
        )->render();
    }

    public function getRecordValuesForFormInputs($ownerRecordId = null) {
        $isItemDetails = (bool)$this->getRequest()->query('details', false);
        /** @var TableInterface|KeyValueTableHelpers $table */
        $table = $this->getTable();
        if (!method_exists($table, 'getMainForeignKeyColumnName')) {
            throw new \UnexpectedValueException(
                'Table ' . get_class($table) . ' must use ' . KeyValueTableHelpers::class . ' class'
            );
        }
        if (
            ($isItemDetails && !$this->isDetailsViewerAllowed())
            || (!$isItemDetails && !$this->isEditAllowed())
        ) {
            return $this->makeAccessDeniedReponse(
                cmfTransGeneral('.action.' . ($isItemDetails ? 'item_details' : 'edit') . '.forbidden'));
        }
        $fkColumn = $table->getMainForeignKeyColumnName();
        if (empty($ownerRecordId) && !empty($fkColumn)) {
            return $this->makeRecordNotFoundResponse($table);
        }
        // todo: read requested related records?
        $conditions = [];
        if (!empty($ownerRecordId)) {
            $conditions[$fkColumn] = $ownerRecordId;
        }
        $keysAndValues = $table->selectAssoc($this->keysColumnName, $this->valuesColumnName, $conditions);
        if ($isItemDetails) {
            $actionConfig = $this->getItemDetailsConfig();
        } else {
            $actionConfig = $this->getFormConfig();
        }
        return cmfJsonResponse()->setData($actionConfig->prepareRecord($keysAndValues));
    }

    public function getDefaultValuesForFormInputs() {
        /** @var FormConfig $config */
        if (!$this->isCreateAllowed() && !$this->isEditAllowed()) {
            return $this->makeAccessDeniedReponse(cmfTransGeneral('.action.create.forbidden'));
        }
        return new JsonResponse([]);
    }

    public function addRecord() {
        throw new \BadMethodCallException('Single record creation is not allowed for key-value table');
    }

    public function updateRecord() {
        if (!$this->isEditAllowed()) {
            return $this->makeAccessDeniedReponse(cmfTransGeneral('.action.edit.forbidden'));
        }
        /** @var TableInterface|KeyValueTableHelpers $table */
        $table = $this->getTable();
        $formConfig = $this->getFormConfig();
        $fkColumn = $table->getMainForeignKeyColumnName();
        $request = $this->getRequest();
        if (!empty($fkColumn) && empty($request->input($fkColumn))) {
            return $this->makeRecordNotFoundResponse(
                $table,
                cmfTransGeneral('.action.edit.key_value_table.no_foreign_key_value')
            );
        }
        $fkValue = empty($fkColumn) ? null : $request->input($fkColumn);
        $inputConfigs = $formConfig->getValueViewers();
        $data = array_intersect_key($this->getRequest()->all(), array_keys($inputConfigs));
        $errors = $formConfig->validateDataForEdit($data);
        if (count($errors) !== 0) {
            return $this->sendValidationErrorsResponse($errors);
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
        if (!empty($data)) {
            $table::beginTransaction();
            try {
                $success = $table->updateOrCreateRecords(
                    $table::convertToDataForRecords($data, $fkValue, $formConfig->getCustomDataForRecord([]))
                );
                if (!$success) {
                    $table::rollBackTransaction();
                    return cmfJsonResponse(HttpCode::SERVER_ERROR)
                        ->setMessage(cmfTransGeneral('.form.failed_to_save_data'));
                } else if ($formConfig->hasAfterSaveCallback()) {
                    $success = call_user_func($formConfig->getAfterSaveCallback(), false, $data, $table->newRecord(), $formConfig);
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

}
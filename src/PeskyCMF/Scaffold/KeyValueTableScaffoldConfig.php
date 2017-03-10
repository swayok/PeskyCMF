<?php


namespace PeskyCMF\Scaffold;

use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Db\KeyValueDataSaver;
use PeskyCMF\Db\KeyValueTableInterface;
use PeskyCMF\Scaffold\Form\FormConfig;
use PeskyCMF\Scaffold\Form\FormInput;
use PeskyCMF\Scaffold\ItemDetails\ItemDetailsConfig;
use PeskyORM\Exception\InvalidDataException;

/**
 * @method KeyValueTableInterface getTable()
 */
abstract class KeyValueTableScaffoldConfig extends ScaffoldConfig {

    protected $isCreateAllowed = false;

    public function __construct(KeyValueTableInterface $table, $tableNameForRoutes) {
        parent::__construct($table, $tableNameForRoutes);
    }

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

    /**
     * @return FormConfig
     */
    public function getFormConfig() {
        $formConfig = parent::getFormConfig();
        $fkName = $this->getTable()->getMainForeignKeyColumnName();
        if ($fkName && !$formConfig->hasFormInput($fkName)) {
            $formConfig->addValueViewer($fkName, FormInput::create()->setType(FormInput::TYPE_HIDDEN));
        }
        return $formConfig;
    }

    public function renderTemplates() {
        return view(
            CmfConfig::getPrimary()->scaffold_templates_view_for_key_value_table(),
            array_merge(
                $this->getConfigsForTemplatesRendering(),
                ['tableNameForRoutes' => $this->getTableNameForRoutes()]
            )
        )->render();
    }

    public function getRecordValues($ownerRecordId = null) {
        $isItemDetails = (bool)$this->getRequest()->query('details', false);
        $table = $this->getTable();
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
        $keysAndValues = $table::getValuesForForeignKey(empty($fkColumn) ? null : $ownerRecordId, true);
        if ($isItemDetails) {
            $sectionConfig = $this->getItemDetailsConfig();
        } else {
            $sectionConfig = $this->getFormConfig();
        }
        $keysAndValues[$table::getPkColumnName()] = 0;
        return cmfJsonResponse()->setData($sectionConfig->prepareRecord($keysAndValues));
    }

    public function getDefaultValuesForFormInputs() {
        throw new \BadMethodCallException('Default values getter is not allowed for ' . self::class);
    }

    public function addRecord() {
        return $this->updateRecord();
    }

    public function updateRecord() {
        if (!$this->isEditAllowed()) {
            return $this->makeAccessDeniedReponse(cmfTransGeneral('.action.edit.forbidden'));
        }
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
        $data = $formConfig->modifyIncomingDataBeforeValidation(
            array_intersect_key($this->getRequest()->all(), $inputConfigs)
        );
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
                KeyValueDataSaver::saveKeyValuePairs(
                    $table,
                    $data,
                    $fkValue,
                    $formConfig->getCustomDataForRecord([])
                );
                if ($formConfig->hasAfterSaveCallback()) {
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
                if ($table::inTransaction()) {
                    $table::rollBackTransaction();
                }
                return $this->sendValidationErrorsResponse($exc->getErrors());
            } catch (\Exception $exc) {
                if ($table::inTransaction()) {
                    $table::rollBackTransaction();
                }
                throw $exc;
            }
        }
        return cmfJsonResponse()
            ->setMessage(cmfTransGeneral('.form.resource_updated_successfully'))
            ->setRedirect('reload');
    }

}
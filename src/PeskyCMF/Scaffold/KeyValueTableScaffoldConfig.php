<?php


namespace PeskyCMF\Scaffold;

use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Scaffold\Form\FormConfig;
use PeskyCMF\Scaffold\Form\FormInput;
use PeskyCMF\Scaffold\ItemDetails\ItemDetailsConfig;
use PeskyORM\Exception\InvalidDataException;
use PeskyORM\ORM\TempRecord;
use PeskyORMLaravel\Db\KeyValueTableUtils\KeyValueDataSaver;
use PeskyORMLaravel\Db\KeyValueTableUtils\KeyValueTableInterface;

abstract class KeyValueTableScaffoldConfig extends ScaffoldConfig {

    protected $isCreateAllowed = false;

    public function __construct() {
        $table = static::getTable();
        if (!($table instanceof KeyValueTableInterface)) {
            throw new \UnexpectedValueException(
                'Class ' . get_class($table) . ' returned by '
                . static::class . '->getTable() must implement of KeyValueTableInterface interface'
            );
        }
        parent::__construct();
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
        $fkName = static::getTable()->getMainForeignKeyColumnName();
        if ($fkName && !$formConfig->hasFormInput($fkName)) {
            $formConfig->addValueViewer($fkName, FormInput::create()->setType(FormInput::TYPE_HIDDEN));
        }
        return $formConfig;
    }

    public function renderTemplates() {
        return view(
            static::getCmfConfig()->scaffold_templates_view_for_key_value_table(),
            array_merge(
                $this->getConfigsForTemplatesRendering(),
                ['tableNameForRoutes' => static::getResourceName()]
            )
        )->render();
    }

    public function getRecordValues($ownerRecordId = null) {
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
        $fkColumn = $table->getMainForeignKeyColumnName();
        if (empty($ownerRecordId) && !empty($fkColumn)) {
            return $this->makeRecordNotFoundResponse($table);
        }
        $keysAndValues = $table::getValuesForForeignKey(empty($fkColumn) ? null : $ownerRecordId, true);
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
        $formConfig = $this->getFormConfig();
        if (!$this->isEditAllowed()) {
            return $this->makeAccessDeniedReponse($formConfig->translateGeneral('message.edit.forbidden'));
        }
        $table = static::getTable();
        $fkColumn = $table->getMainForeignKeyColumnName();
        $request = $this->getRequest();
        if (!empty($fkColumn) && empty($request->input($fkColumn))) {
            return $this->makeRecordNotFoundResponse(
                $table,
                $formConfig->translateGeneral('message.edit.key_value_table.no_foreign_key_value')
            );
        }
        $fkValue = empty($fkColumn) ? null : $request->input($fkColumn);
        if (\Gate::denies('resource.update', [static::getResourceName(), $fkValue])) {
            return $this->makeAccessDeniedReponse($formConfig->translateGeneral('message.edit.forbidden'));
        }
        $inputConfigs = $formConfig->getValueViewers();
        $data = $formConfig->modifyIncomingDataBeforeValidation(
            array_intersect_key($this->getRequest()->all(), $inputConfigs),
            false
        );
        $errors = $formConfig->validateDataForEdit($data);
        if (count($errors) !== 0) {
            return $this->makeValidationErrorsJsonResponse($errors);
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
                    return $this->makeValidationErrorsJsonResponse($errors);
                }
            }
        }
        if (!empty($data)) {
            $table::beginTransaction();
            try {
                $originalValues = $table::getValuesForForeignKey($fkValue, true);
                if ($this->hasLogger()) {
                    $tempRecord = TempRecord::newTempRecord($originalValues, true);
                    $this->logDbRecordBeforeChange($tempRecord, static::getResourceName());
                }
                KeyValueDataSaver::saveKeyValuePairs(
                    $table,
                    $originalValues,
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
                if ($this->hasLogger()) {
                    $tempRecord = TempRecord::newTempRecord($table::getValuesForForeignKey($fkValue, true), true);
                    $this->logDbRecordAfterChange($tempRecord);
                }
                $table::commitTransaction();
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
            }
        }
        return cmfJsonResponse()
            ->setMessage($formConfig->translateGeneral('message.edit.success'))
            ->setRedirect('reload');
    }

    public function changeItemPosition($id, $beforeOrAfter, $otherId, $columnName, $sortDirection) {
        throw new \BadMethodCallException('Rows reordering is not allowed for ' . self::class);
    }

}
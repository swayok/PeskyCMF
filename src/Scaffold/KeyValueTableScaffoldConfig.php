<?php

declare(strict_types=1);

namespace PeskyCMF\Scaffold;

use Illuminate\Http\JsonResponse;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Http\CmfJsonResponse;
use PeskyCMF\Scaffold\Form\FormConfig;
use PeskyCMF\Scaffold\Form\FormInput;
use PeskyCMF\Scaffold\ItemDetails\ItemDetailsConfig;
use PeskyORM\Exception\InvalidDataException;

abstract class KeyValueTableScaffoldConfig extends ScaffoldConfig
{

    protected bool $isCreateAllowed = false;

    public function __construct(CmfConfig $cmfConfig)
    {
        parent::__construct($cmfConfig);
        $table = static::getTable();
        if (!($table instanceof KeyValueTableInterface)) {
            throw new \UnexpectedValueException(
                'Class ' . get_class($table) . ' returned by '
                . static::class . '::getTable() must implement ' . KeyValueTableInterface::class
            );
        }
    }

    protected function createFormConfig(): FormConfig
    {
        return parent::createFormConfig()->thereIsNoDbColumns();
    }

    protected function createItemDetailsConfig(): ItemDetailsConfig
    {
        return parent::createItemDetailsConfig()->thereIsNoDbColumns();
    }

    public function getFormConfig(): FormConfig
    {
        $formConfig = parent::getFormConfig();
        $fkName = static::getTable()->getMainForeignKeyColumnName();
        if ($fkName && !$formConfig->hasFormInput($fkName)) {
            $viewer = FormInput::create()->setType(FormInput::TYPE_HIDDEN);
            $formConfig->addValueViewer($fkName, $viewer);
        }
        return $formConfig;
    }

    public function getRecordValues(?string $id = null): JsonResponse
    {
        $sectionConfig = $this->getScaffoldSectionConfigForRecordInfoAndValidateAccess();
        $table = static::getTable();
        $fkColumn = $table->getMainForeignKeyColumnName();
        if (empty($id) && !empty($fkColumn)) {
            return $this->makeRecordNotFoundResponse();
        }
        $keysAndValues = $table::getValuesForForeignKey(empty($fkColumn) ? null : $id, true);
        $keysAndValues[$table::getPkColumnName()] = 0;
        return new CmfJsonResponse($sectionConfig->prepareRecord($keysAndValues));
    }

    public function getDefaultValuesForFormInputs(): JsonResponse
    {
        throw new \BadMethodCallException('Default values getter is not allowed for ' . self::class);
    }

    public function addRecord(): JsonResponse
    {
        return $this->updateRecord();
    }

    /** @noinspection PhpParameterNameChangedDuringInheritanceInspection */
    public function updateRecord(?string $notUsed = null): JsonResponse
    {
        $formConfig = $this->getFormConfig();
        if (!$this->isEditAllowed()) {
            return $this->makeAccessDeniedReponse($formConfig->translateGeneral('message.edit.forbidden'));
        }
        $table = static::getTable();
        $fkColumn = $table->getMainForeignKeyColumnName();
        $request = $this->getRequest();
        if (!empty($fkColumn) && empty($request->input($fkColumn))) {
            return $this->makeRecordNotFoundResponse(
                $formConfig->translateGeneral('message.edit.key_value_table.no_foreign_key_value')
            );
        }
        $fkValue = empty($fkColumn) ? null : $request->input($fkColumn);
        if ($this->authGate->denies('resource.update', [static::getResourceName(), $fkValue])) {
            return $this->makeAccessDeniedReponse($formConfig->translateGeneral('message.edit.forbidden'));
        }
        $inputConfigs = $formConfig->getValueViewers();
        $data = $formConfig->modifyIncomingDataBeforeValidation(
            array_intersect_key($this->getRequest()->all(), $inputConfigs),
            false
        );
        $originalValues = $table::getValuesForForeignKey($fkValue, true);
        $tempRecord = TempRecord::newTempRecord($originalValues, true);
        $this->validateDataForEdit($formConfig, $data, $tempRecord);
        if (!empty($data)) {
            $table::beginTransaction();
            try {
                if ($this->hasLogger()) {
                    $this->logDbRecordBeforeChange($tempRecord);
                }
                KeyValueDataSaver::saveKeyValuePairs(
                    $table,
                    $originalValues,
                    $data,
                    $fkValue,
                    $formConfig->getCustomDataForRecord([])
                );
                $this->runAfterSaveCallback($formConfig, false, $data, $table->newRecord());
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
        return CmfJsonResponse::create()
            ->setMessage($formConfig->translateGeneral('message.edit.success'))
            ->setRedirect('reload');
    }

    public function changeItemPosition(string $id, string $beforeOrAfter, string $otherId, string $columnName, string $sortDirection): JsonResponse
    {
        throw new \BadMethodCallException('Rows reordering is not allowed for ' . self::class);
    }

}

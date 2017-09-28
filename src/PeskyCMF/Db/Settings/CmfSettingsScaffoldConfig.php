<?php

namespace PeskyCMF\Db\Settings;

use PeskyCMF\Http\CmfJsonResponse;
use PeskyCMF\HttpCode;
use PeskyCMF\PeskyCmfAppSettings;
use PeskyCMF\Scaffold\KeyValueTableScaffoldConfig;

class CmfSettingsScaffoldConfig extends KeyValueTableScaffoldConfig {

    protected $isDetailsViewerAllowed = false;
    protected $isCreateAllowed = false;
    protected $isEditAllowed = true;
    protected $isDeleteAllowed = false;

    static public function getTable() {
        static $table;
        if ($table === null) {
            $table = app()->bound(CmfSettingsTable::class)
                ? app(CmfSettingsTable::class)
                : CmfSettingsTable::getInstance();
        }
        return $table;
    }

    protected function createFormConfig() {
        $formConfig = parent::createFormConfig()
            ->setWidth(50)
            ->setShowAsDialog(false);
        /** @var PeskyCmfAppSettings $appSettings */
        $appSettings = app(PeskyCmfAppSettings::class);
        $appSettings::configureScaffoldFormConfig($formConfig);
        $formConfig
            ->setValidators(function () use ($appSettings) {
                return $appSettings::getValidatorsForScaffoldFormConfig();
            })
            ->setIncomingDataModifier(function (array $data) use ($appSettings) {
                return $appSettings::modifyIncomingData($data);
            });
        return $formConfig;
    }

    public function renderTemplates() {
        return view(
            'cmf::scaffold.templates_for_settings',
            array_merge(
                $this->getConfigsForTemplatesRendering(),
                ['tableNameForRoutes' => static::getResourceName()]
            )
        )->render();
    }

    public function getRecordsForDataGrid() {
        throw new \BadMethodCallException('Section is not allowed');
    }

    public function getDefaultValuesForFormInputs() {
        return CmfJsonResponse::create([], HttpCode::MOVED_PERMANENTLY)
            ->setRedirect(routeToCmfItemEditForm(static::getResourceName(), 'all'));
    }

    public function getRecordValues($ownerRecordId = null) {
        /** @var PeskyCmfAppSettings $appSettings */
        $appSettings = app(PeskyCmfAppSettings::class);
        $settings = $appSettings::getAllValues(true);
        $settings[static::getTable()->getPkColumnName()] = 0;
        return cmfJsonResponse()->setData($this->getFormConfig()->prepareRecord($settings));
    }

    public function updateBulkOfRecords() {
        throw new \BadMethodCallException('Action is not allowed');
    }

    public function deleteRecord($id) {
        throw new \BadMethodCallException('Action is not allowed');
    }

    public function deleteBulkOfRecords() {
        throw new \BadMethodCallException('Action is not allowed');
    }
}
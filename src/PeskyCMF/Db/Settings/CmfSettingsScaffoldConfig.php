<?php

namespace PeskyCMF\Db\Settings;

use PeskyCMF\PeskyCmfAppSettings;
use PeskyCMF\Scaffold\KeyValueTableScaffoldConfig;

class CmfSettingsScaffoldConfig extends KeyValueTableScaffoldConfig {

    protected $isDetailsViewerAllowed = false;
    protected $isCreateAllowed = false;
    protected $isEditAllowed = true;
    protected $isDeleteAllowed = false;

    /** @var CmfSettingsTable */
    static private $table;

    /**
     * @return CmfSettingsTable|\PeskyORM\ORM\TableInterface|\PeskyORMLaravel\Db\KeyValueTableUtils\KeyValueTableInterface
     */
    static public function getTable() {
        if (static::$table === null) {
            static::$table = app()->bound(CmfSettingsTable::class)
                ? app(CmfSettingsTable::class)
                : CmfSettingsTable::getInstance();
        }
        return static::$table;
    }

    static public function getMainMenuItem() {
        $resoureName = static::getResourceName();
        $url = routeToCmfItemEditForm(static::getResourceName(), 'all');
        if ($url === null) {
            // access to this menu item was denied
            return null;
        }
        return [
            'label' => cmfTransCustom($resoureName . '.menu_title'),
            'icon' => static::getIconForMenuItem(),
            'url' => $url
        ];
    }

    static public function getIconForMenuItem() {
        return 'fa fa-cog';
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
        return $this->getRecordValues('all');
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
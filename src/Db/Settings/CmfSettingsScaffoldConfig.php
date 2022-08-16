<?php

namespace PeskyCMF\Db\Settings;

use Illuminate\Http\JsonResponse;
use PeskyCMF\PeskyCmfAppSettings;
use PeskyCMF\Scaffold\KeyValueTableScaffoldConfig;
use PeskyORM\ORM\TableInterface;

class CmfSettingsScaffoldConfig extends KeyValueTableScaffoldConfig {

    protected $isDetailsViewerAllowed = false;
    protected $isCreateAllowed = false;
    protected $isEditAllowed = true;
    protected $isDeleteAllowed = false;

    /** @var CmfSettingsTable */
    private static $table;

    /**
     * @return TableInterface
     */
    public static function getTable(): TableInterface
    {
        if (static::$table === null) {
            static::$table = app()->bound(CmfSettingsTable::class)
                ? app(CmfSettingsTable::class)
                : CmfSettingsTable::getInstance();
        }
        return static::$table;
    }

    public static function getMainMenuItem(): ?array
    {
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

    public static function getIconForMenuItem(): ?string
    {
        return 'fa fa-cog';
    }

    protected function createFormConfig() {
        $formConfig = parent::createFormConfig()
            ->setWidth(50)
            ->setModalConfig(false);
        /** @var PeskyCmfAppSettings $appSettings */
        $appSettings = static::getCmfConfig()->getAppSettings();
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

    public function renderTemplates(): string {
        return view(
            'cmf::scaffold.templates_for_settings',
            array_merge(
                $this->getConfigsForTemplatesRendering(),
                ['tableNameForRoutes' => static::getResourceName()]
            )
        )->render();
    }

    public function getRecordsForDataGrid(): JsonResponse
    {
        throw new \BadMethodCallException('Section is not allowed');
    }

    public function getDefaultValuesForFormInputs(): JsonResponse
    {
        return $this->getRecordValues('all');
    }

    public function getRecordValues(?string $id = null): JsonResponse
    {
        $settings = static::getCmfConfig()->getAppSettings()->getAllValues(true);
        $settings[static::getTable()->getPkColumnName()] = 0;
        return cmfJsonResponse()->setData($this->getFormConfig()->prepareRecord($settings));
    }

    public function updateBulkOfRecords(): JsonResponse
    {
        throw new \BadMethodCallException('Action is not allowed');
    }

    public function deleteRecord(string $id): JsonResponse
    {
        throw new \BadMethodCallException('Action is not allowed');
    }

    public function deleteBulkOfRecords(): JsonResponse
    {
        throw new \BadMethodCallException('Action is not allowed');
    }
}

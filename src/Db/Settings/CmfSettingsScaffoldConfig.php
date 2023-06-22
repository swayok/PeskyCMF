<?php

declare(strict_types=1);

namespace PeskyCMF\Db\Settings;

use Illuminate\Http\JsonResponse;
use PeskyCMF\Http\CmfJsonResponse;
use PeskyCMF\PeskyCmfAppSettings;
use PeskyCMF\Scaffold\Form\FormConfig;
use PeskyCMF\Scaffold\KeyValueTableScaffoldConfig;
use PeskyORM\ORM\Table\TableInterface;

class CmfSettingsScaffoldConfig extends KeyValueTableScaffoldConfig
{
    protected bool $isDetailsViewerAllowed = false;
    protected bool $isCreateAllowed = false;
    protected bool $isEditAllowed = true;
    protected bool $isDeleteAllowed = false;

    public static function getTable(): TableInterface
    {
        return app(PeskyCmfAppSettings::class)->getTable();
    }

    public function getMainMenuItem(): ?array
    {
        $resoureName = static::getResourceName();
        $url = $this->getUrlToItemEditForm('all');
        if ($url === null) {
            // access to this menu item was denied
            return null;
        }
        return [
            'label' => $this->cmfConfig->transCustom($resoureName . '.menu_title'),
            'icon' => static::getIconForMenuItem(),
            'url' => $url,
        ];
    }

    public static function getIconForMenuItem(): ?string
    {
        return 'fa fa-cog';
    }

    protected function createFormConfig(): FormConfig
    {
        $formConfig = parent::createFormConfig()
            ->setWidth(50)
            ->setModalConfig(false);
        $appSettings = $this->cmfConfig->getAppSettings();
        $appSettings::configureScaffoldFormConfig($formConfig);
        $formConfig
            ->setValidators(function () use ($appSettings) {
                return $appSettings::getValidatorsForScaffoldFormConfig();
            })
            ->setIncomingDataModifier(function (array $data) use ($appSettings) {
                return $appSettings->modifyIncomingData($data);
            });
        return $formConfig;
    }

    public function renderTemplates(): string
    {
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
        $settings = $this->cmfConfig->getAppSettings()->getSettings(true);
        return new CmfJsonResponse($this->getFormConfig()->prepareRecord($settings));
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

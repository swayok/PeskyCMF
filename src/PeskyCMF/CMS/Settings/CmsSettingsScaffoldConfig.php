<?php

namespace PeskyCMF\CMS\Settings;

use PeskyCMF\Http\CmfJsonResponse;
use PeskyCMF\HttpCode;
use PeskyCMF\Scaffold\Form\FormInput;
use PeskyCMF\Scaffold\Form\KeyValueSetFormInput;
use PeskyCMF\Scaffold\KeyValueTableScaffoldConfig;

class CmsSettingsScaffoldConfig extends KeyValueTableScaffoldConfig {

    protected $isDetailsViewerAllowed = false;
    protected $isCreateAllowed = false;
    protected $isEditAllowed = true;
    protected $isDeleteAllowed = false;

    protected function createFormConfig() {
        /** @var CmsSetting $cmsSetting */
        $cmsSetting = app(CmsSetting::class);
        return parent::createFormConfig()
            ->setWidth(50)
            ->addTab(cmfTransCustom('.settings.form.tab.general'), [
                $cmsSetting::DEFAULT_BROWSER_TITLE,
                $cmsSetting::BROWSER_TITLE_ADDITION,
            ])
            ->addTab(cmfTransCustom('.settings.form.tab.localization'), [
                $cmsSetting::LANGUAGES => KeyValueSetFormInput::create()
                    ->setMinValuesCount(1)
                    ->setAddRowButtonLabel(cmfTransCustom('.settings.form.input.languages_add'))
                    ->setDeleteRowButtonLabel(cmfTransCustom('.settings.form.input.languages_delete')),
                $cmsSetting::DEFAULT_LANGUAGE => FormInput::create()
                    ->setType(FormInput::TYPE_SELECT)
                    ->setOptions(function () use ($cmsSetting) {
                        return $cmsSetting::languages(null, []);
                    }),
                $cmsSetting::FALLBACK_LANGUAGES => KeyValueSetFormInput::create()
                    ->setAddRowButtonLabel(cmfTransCustom('.settings.form.input.fallback_languages_add'))
                    ->setDeleteRowButtonLabel(cmfTransCustom('.settings.form.input.fallback_languages_delete')),
            ])
            ->setDataToAddToRecord(function () {
                return ['admin_id' => \Auth::guard()->user()->getAuthIdentifier()];
            })
            ->setValidators(function () use ($cmsSetting) {
                return [
                    $cmsSetting::DEFAULT_LANGUAGE => 'required|string|size:2|alpha|regex:%^[a-zA-Z]{2}$%|in:' . implode(',', array_keys($cmsSetting::languages(null, []))),
                    $cmsSetting::LANGUAGES . '.*.key' => 'required|string|size:2|alpha|regex:%^[a-zA-Z]{2}$%',
                    $cmsSetting::LANGUAGES . '.*.value' => 'required|string|max:88',
                    $cmsSetting::FALLBACK_LANGUAGES . '.*.key' => 'required|string|size:2|alpha|regex:%^[a-zA-Z]{2}$%',
                    $cmsSetting::FALLBACK_LANGUAGES . '.*.value' => 'required|string|size:2|alpha|regex:%^[a-zA-Z]{2}$%'
                ];
            });
    }

    public function renderTemplates() {
        return view(
            'cmf::scaffold.templates_for_settings',
            array_merge(
                $this->getConfigsForTemplatesRendering(),
                ['tableNameForRoutes' => $this->getTableNameForRoutes()]
            )
        )->render();
    }

    public function getRecordsForDataGrid() {
        throw new \BadMethodCallException('Section is not allowed');
    }

    public function getDefaultValuesForFormInputs() {
        return CmfJsonResponse::create([], HttpCode::MOVED_PERMANENTLY)
            ->setRedirect(routeToCmfItemEditForm($this->getTableNameForRoutes(), 'all'));
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
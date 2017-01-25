<?php echo "<?php\n"; ?>

namespace App\<?php echo $dbClassesAppSubfolder ?>\<?php echo $baseClassNamePlural; ?>;

use PeskyCMF\Http\CmfJsonResponse;
use PeskyCMF\HttpCode;
use PeskyCMF\Scaffold\Form\FormInput;
use PeskyCMF\Scaffold\Form\KeyValueSetFormInput;
use PeskyCMF\Scaffold\KeyValueTableScaffoldConfig;

class <?php echo $baseClassNamePlural; ?>ScaffoldConfig extends KeyValueTableScaffoldConfig {

    protected $isDetailsViewerAllowed = false;
    protected $isCreateAllowed = false;
    protected $isEditAllowed = true;
    protected $isDeleteAllowed = false;

    protected function createFormConfig() {
        return parent::createFormConfig()
            ->setWidth(50)
            ->addTab(cmfTransCustom('.settings.form.tab.general'), [
                <?php echo $baseClassNameSingular; ?>::DEFAULT_BROWSER_TITLE,
                <?php echo $baseClassNameSingular; ?>::BROWSER_TITLE_ADDITION,
            ])
            ->addTab(cmfTransCustom('.settings.form.tab.localization'), [
                <?php echo $baseClassNameSingular; ?>::LANGUAGES => KeyValueSetFormInput::create()
                    ->setMinValuesCount(1)
                    ->setAddRowButtonLabel(cmfTransCustom('.settings.form.input.languages_add'))
                    ->setDeleteRowButtonLabel(cmfTransCustom('.settings.form.input.languages_delete')),
                <?php echo $baseClassNameSingular; ?>::DEFAULT_LANGUAGE => FormInput::create()
                    ->setType(FormInput::TYPE_SELECT)
                    ->setOptions(function () {
                        return <?php echo $baseClassNameSingular; ?>::languages(null, []);
                    }),
                <?php echo $baseClassNameSingular; ?>::FALLBACK_LANGUAGES => KeyValueSetFormInput::create()
                    ->setAddRowButtonLabel(cmfTransCustom('.settings.form.input.fallback_languages_add'))
                    ->setDeleteRowButtonLabel(cmfTransCustom('.settings.form.input.fallback_languages_delete')),
            ])
            ->setDataToAddToRecord(function () {
                return ['admin_id' => \Auth::guard()->user()->id];
            })
            ->setValidators(function () {
                return [
                    <?php echo $baseClassNameSingular; ?>::DEFAULT_LANGUAGE => 'required|string|size:2|alpha|regex:%^[a-zA-Z]{2}$%|in:' . implode(',', array_keys(<?php echo $baseClassNameSingular; ?>::languages(null, []))),
                    <?php echo $baseClassNameSingular; ?>::LANGUAGES . '.*.key' => 'required|string|size:2|alpha|regex:%^[a-zA-Z]{2}$%',
                    <?php echo $baseClassNameSingular; ?>::LANGUAGES . '.*.value' => 'required|string|max:88',
                    <?php echo $baseClassNameSingular; ?>::FALLBACK_LANGUAGES . '.*.key' => 'required|string|size:2|alpha|regex:%^[a-zA-Z]{2}$%',
                    <?php echo $baseClassNameSingular; ?>::FALLBACK_LANGUAGES . '.*.value' => 'required|string|size:2|alpha|regex:%^[a-zA-Z]{2}$%'
                ];
            });
    }

    public function renderTemplates() {
        return view(
            'cmf::scaffold.templates_for_settings',
            array_merge(
                $this->getConfigs(),
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
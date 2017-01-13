<?php echo "<?php\n"; ?>

namespace App\<?php echo $dbClassesAppSubfolder ?>\<?php echo $baseClassNamePlural; ?>;

use PeskyCMF\Http\CmfJsonResponse;
use PeskyCMF\HttpCode;
use PeskyCMF\Scaffold\KeyValueTableScaffoldConfig;

class <?php echo $baseClassNamePlural; ?>ScaffoldConfig extends KeyValueTableScaffoldConfig {

    protected $isDetailsViewerAllowed = false;
    protected $isCreateAllowed = false;
    protected $isEditAllowed = true;
    protected $isDeleteAllowed = false;

    protected function createFormConfig() {
        return parent::createFormConfig()
            ->setWidth(50)
            ->setFormInputs([
                Setting::DEFAULT_BROWSER_TITLE,
                Setting::BROWSER_TITLE_ADDITION,
                Setting::LANGUAGES => KeyValueSetFormInput::create()
                    ->setMinValuesCount(1)
                    ->setAddRowButtonLabel(cmfTransCustom('.settings.form.input.languages_add'))
                    ->setDeleteRowButtonLabel(cmfTransCustom('.settings.form.input.languages_delete'))
            ])
            ->setDataToAddToRecord(function () {
                return ['admin_id' => \Auth::guard()->user()->id];
            })
            ->setValidators(function () {
                return [
                    Setting::LANGUAGES . '.*.key' => 'required|string|size:2|alpha|regex:%^[a-zA-Z]{2}$%',
                    Setting::LANGUAGES . '.*.value' => 'required|string|max:88'
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
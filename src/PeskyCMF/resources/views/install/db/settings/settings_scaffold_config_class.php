<?php echo "<?php\n"; ?>

namespace App\<?php echo $dbClassesAppSubfolder ?>\Settings;

use PeskyCMF\Http\CmfJsonResponse;
use PeskyCMF\HttpCode;
use PeskyCMF\Scaffold\KeyValueTableScaffoldConfig;

class SettingsScaffoldConfig extends KeyValueTableScaffoldConfig {

    protected $isDetailsViewerAllowed = false;
    protected $isCreateAllowed = false;
    protected $isEditAllowed = true;
    protected $isDeleteAllowed = false;

    protected function createFormConfig() {
        return parent::createFormConfig()
            ->setWidth(50)
            ->setFormInputs([
                'default_browser_title',
                'browser_title_addition',
            ])
            ->setDataToAddToRecord(function () {
                return ['admin_id' => \Auth::guard()->user()->id];
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
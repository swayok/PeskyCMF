<?php

namespace PeskyCMF\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Scaffold\ScaffoldConfig;
use PeskyORM\ORM\TableInterface;

class CmfScaffoldApiController extends Controller {

    use AuthorizesRequests;

    protected $requestedResourceName;
    protected $table;
    protected $scaffoldConfig;

    /**
     * @return TableInterface
     * @throws \UnexpectedValueException
     * @throws \Symfony\Component\Debug\Exception\ClassNotFoundException
     * @throws \InvalidArgumentException
     */
    public function getTable() {
        return $this->getScaffoldConfig()->getTable();
    }

    /**
     * @return ScaffoldConfig
     * @throws \Symfony\Component\Debug\Exception\ClassNotFoundException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function getScaffoldConfig() {
        if ($this->scaffoldConfig === null) {
            $cmfConfig = CmfConfig::getPrimary();
            $customScaffoldConfig = $cmfConfig::getScaffoldConfig($this->getRequestedResourceName());
            if ($customScaffoldConfig instanceof ScaffoldConfig) {
                $this->scaffoldConfig = $customScaffoldConfig;
            } else {
                throw new \UnexpectedValueException(
                    get_class($cmfConfig) . '::getScaffoldConfig() must instance of ScaffoldConfig class. '
                        . (is_object($customScaffoldConfig) ? get_class($customScaffoldConfig) : gettype($customScaffoldConfig))
                        . ' Received'
                );
            }
        }
        return $this->scaffoldConfig;
    }

    /**
     * @return string
     * @throws \UnexpectedValueException
     */
    public function getRequestedResourceName() {
        if ($this->requestedResourceName === null) {
            $tableName = CmfConfig::getPrimary()->getResourceNameFromCurrentRoute();
            if (empty($tableName)) {
                abort(404, 'Table name not found in route');
            }
            $this->requestedResourceName = $tableName;
        }
        return $this->requestedResourceName;
    }

    public function __construct() {

    }

    public function getTemplates() {
        return $this->getScaffoldConfig()->renderTemplates();
    }

    public function getItemsList() {
        return $this->getScaffoldConfig()->getRecordsForDataGrid();
    }

    public function getItem($resourceName, $id = null) {
        return $this->getScaffoldConfig()->getRecordValues($id);
    }

    public function getItemDefaults() {
        return $this->getScaffoldConfig()->getDefaultValuesForFormInputs();
    }

    public function getOptions() {
        return $this->getScaffoldConfig()->getHtmlOptionsForFormInputs();
    }

    public function getOptionsAsJson($resourceName, $inputName) {
        return $this->getScaffoldConfig()->getJsonOptionsForFormInput($inputName);
    }

    public function addItem() {
        return $this->getScaffoldConfig()->addRecord();
    }

    public function updateItem() {
        return $this->getScaffoldConfig()->updateRecord();
    }

    public function changeItemPosition($resourceName, $id, $beforeOrAfter, $otherId, $columnName, $sortDirection) {
        return $this->getScaffoldConfig()->changeItemPosition($id, $beforeOrAfter, $otherId, $columnName, $sortDirection);
    }

    public function updateBulk() {
        return $this->getScaffoldConfig()->updateBulkOfRecords();
    }

    public function deleteItem($resourceName, $id) {
        return $this->getScaffoldConfig()->deleteRecord($id);
    }

    public function deleteBulk() {
        return $this->getScaffoldConfig()->deleteBulkOfRecords();
    }

    public function getCustomData($resourceName, $dataId) {
        $this->authorize('resource.view', [$resourceName]);
        return $this->getScaffoldConfig()->getCustomData($dataId);
    }

    public function getCustomPage($resourceName, $pageName) {
        $this->authorize('resource.custom_page', [$resourceName, $pageName]);
        return $this->getScaffoldConfig()->getCustomPage($pageName);
    }

    public function getCustomPageForItem($resourceName, $itemId, $pageName) {
        $this->authorize('resource.custom_page_for_item', [$resourceName, $pageName, $itemId]);
        return $this->getScaffoldConfig()->getCustomPageForRecord($itemId, $pageName);
    }

    public function performActionForItem($resourceName, $itemId, $actionName) {
        $this->authorize('resource.custom_action_for_item', [$resourceName, $actionName, $itemId]);
        return $this->getScaffoldConfig()->performActionForRecord($itemId, $actionName);
    }

    public function performAction($resourceName, $actionName) {
        $this->authorize('resource.custom_action', [$resourceName, $actionName]);
        return $this->getScaffoldConfig()->performAction($actionName);
    }

}

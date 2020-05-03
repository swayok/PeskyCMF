<?php

namespace PeskyCMF\Http\Controllers;

use PeskyCMF\Scaffold\ScaffoldConfig;
use PeskyORM\ORM\TableInterface;

class CmfScaffoldApiController extends CmfController {

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
            $cmfConfig = static::getCmfConfig();
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
            $resourceName = request()->route()->parameter('resource');
            if (empty($resourceName)) {
                abort(404, 'Resource name not found in route');
            }
            $this->requestedResourceName = $resourceName;
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

    public function uploadTempFileForInput($resourceName, $inputName) {
        return $this->getScaffoldConfig()->uploadTempFileForInput($inputName);
    }

    public function deleteTempFileForInput($resourceName, $inputName) {
        return $this->getScaffoldConfig()->deleteTempFileForInput($inputName);
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

    public function performAction($resourceName, $actionName) {
        $this->authorize('resource.custom_action', [$resourceName, $actionName]);
        return $this->getScaffoldConfig()->performAction($actionName);
    }
    
    public function performActionForItem($resourceName, $itemId, $actionName) {
        $this->authorize('resource.custom_action_for_item', [$resourceName, $actionName, $itemId]);
        return $this->getScaffoldConfig()->performActionForRecord($itemId, $actionName);
    }
    
    public function performDownload($resourceName, $downloadName) {
        $this->authorize('resource.custom_page', [$resourceName, $downloadName]);
        return $this->getScaffoldConfig()->downloadFile($downloadName);
    }
    
    public function performDownloadForItem($resourceName, $itemId, $downloadName) {
        $this->authorize('resource.custom_page_for_item', [$resourceName, $downloadName, $itemId]);
        return $this->getScaffoldConfig()->downloadFileForRecord($itemId, $downloadName);
    }

}

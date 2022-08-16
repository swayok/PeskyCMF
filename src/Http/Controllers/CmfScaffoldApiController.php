<?php

declare(strict_types=1);

namespace PeskyCMF\Http\Controllers;

use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Scaffold\ScaffoldConfigInterface;
use PeskyORM\ORM\TableInterface;

class CmfScaffoldApiController extends CmfController
{
    
    /**
     * @var string
     */
    private $requestedResourceName;
    /**
     * @var ScaffoldConfigInterface
     */
    private $scaffoldConfig;
    
    public function __construct(CmfConfig $cmfConfig, Application $app, Request $request)
    {
        parent::__construct($cmfConfig, $app);
    
        $route = $request->route();
        $resourceName = $route->parameter('resource');
        if (empty($resourceName)) {
            abort(404, 'Resource name not found in route');
        }
        $this->requestedResourceName = $resourceName;
        $this->scaffoldConfig = $this->getCmfConfig()->getScaffoldConfig($this->getRequestedResourceName());
    }
    
    public function getTable(): TableInterface
    {
        return $this->getScaffoldConfig()->getTable();
    }
    
    public function getScaffoldConfig(): ScaffoldConfigInterface
    {
        return $this->scaffoldConfig;
    }
    
    public function getRequestedResourceName(): string
    {
        return $this->requestedResourceName;
    }
    
    public function getTemplates(): string
    {
        return $this->getScaffoldConfig()->renderTemplates();
    }
    
    public function getItemsList(): JsonResponse
    {
        return $this->getScaffoldConfig()->getRecordsForDataGrid();
    }
    
    public function getItem(string $resourceName, string $id): JsonResponse
    {
        return $this->getScaffoldConfig()->getRecordValues($id);
    }
    
    public function getItemDefaults(): JsonResponse
    {
        return $this->getScaffoldConfig()->getDefaultValuesForFormInputs();
    }
    
    public function getOptions(): JsonResponse
    {
        return $this->getScaffoldConfig()->getHtmlOptionsForFormInputs();
    }
    
    public function getOptionsAsJson(string $resourceName, string $inputName): JsonResponse
    {
        return $this->getScaffoldConfig()->getJsonOptionsForFormInput($inputName);
    }
    
    public function addItem(): JsonResponse
    {
        return $this->getScaffoldConfig()->addRecord();
    }
    
    public function updateItem(string $resourceName, string $id): JsonResponse
    {
        return $this->getScaffoldConfig()->updateRecord($id);
    }
    
    public function uploadTempFileForInput(string $resourceName, $inputName): JsonResponse
    {
        return $this->getScaffoldConfig()->uploadTempFileForInput($inputName);
    }
    
    public function deleteTempFileForInput(string $resourceName, $inputName): JsonResponse
    {
        return $this->getScaffoldConfig()->deleteTempFileForInput($inputName);
    }
    
    public function changeItemPosition(
        string $resourceName,
        string $id,
        string $beforeOrAfter,
        string $otherId,
        string $columnName,
        string $sortDirection
    ): JsonResponse {
        return $this->getScaffoldConfig()->changeItemPosition($id, $beforeOrAfter, $otherId, $columnName, $sortDirection);
    }
    
    public function updateBulk(): JsonResponse
    {
        return $this->getScaffoldConfig()->updateBulkOfRecords();
    }
    
    public function deleteItem(string $resourceName, string $id): JsonResponse
    {
        return $this->getScaffoldConfig()->deleteRecord($id);
    }
    
    public function deleteBulk(): JsonResponse
    {
        return $this->getScaffoldConfig()->deleteBulkOfRecords();
    }
    
    public function getCustomData(string $resourceName, string $dataId)
    {
        $this->authorize('resource.view', [$resourceName]);
        return $this->getScaffoldConfig()->getCustomData($dataId);
    }
    
    public function getCustomPage(string $resourceName, string $pageName)
    {
        $this->authorize('resource.custom_page', [$resourceName, $pageName]);
        return $this->getScaffoldConfig()->getCustomPage($pageName);
    }
    
    public function getCustomPageForItem(string $resourceName, string $itemId, string $pageName)
    {
        $this->authorize('resource.custom_page_for_item', [$resourceName, $pageName, $itemId]);
        return $this->getScaffoldConfig()->getCustomPageForRecord($itemId, $pageName);
    }
    
    public function performCustomAction(Request $request, string $resourceName, string $actionName)
    {
        $this->authorize('resource.custom_action', [$resourceName, $actionName]);
        if ($request->ajax()) {
            return $this->getScaffoldConfig()->performCustomAjaxAction($actionName);
        } else {
            return $this->getScaffoldConfig()->performCustomDirectAction($actionName);
        }
    }
    
    public function performCustomActionForItem(Request $request, string $resourceName, string $itemId, string $actionName)
    {
        $this->authorize('resource.custom_action_for_item', [$resourceName, $actionName, $itemId]);
        if ($request->ajax()) {
            return $this->getScaffoldConfig()->performCustomAjaxActionForRecord($itemId, $actionName);
        } else {
            return $this->getScaffoldConfig()->performCustomDirectActionForRecord($itemId, $actionName);
        }
    }
    
}

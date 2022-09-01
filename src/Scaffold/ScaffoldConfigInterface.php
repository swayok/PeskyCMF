<?php

declare(strict_types=1);

namespace PeskyCMF\Scaffold;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;
use PeskyCMF\Scaffold\DataGrid\DataGridConfig;
use PeskyCMF\Scaffold\DataGrid\FilterConfig;
use PeskyCMF\Scaffold\Form\FormConfig;
use PeskyCMF\Scaffold\ItemDetails\ItemDetailsConfig;
use PeskyORM\ORM\KeyValueTableHelpers\KeyValueTableInterface;
use PeskyORM\ORM\TableInterface;
use PeskyORMLaravel\Db\LaravelKeyValueTableHelpers\LaravelKeyValueTableInterface;

interface ScaffoldConfigInterface
{
    
    /**
     * @return TableInterface|KeyValueTableInterface|LaravelKeyValueTableInterface
     * @noinspection PhpDocSignatureInspection
     */
    public static function getTable(): TableInterface;
    
    public static function getResourceName(): string;
    
    /**
     * Main menu item info. Return null if you do not want to add item to menu
     * Details in CmfConfig::menu()
     */
    public function getMainMenuItem(): ?array;
    
    public static function getMenuItemCounterName(): ?string;
    
    /**
     * Get value for menu item counter (some html code to display near menu item button: new items count, etc)
     * More info: CmfConfig::menu()
     * You may return an HTML string or \Closure that returns that string.
     * Note that self::getMenuItemCounterName() uses this method to decide if it should return null or counter name.
     * If you want to return HTML string consider overwriting of self::getMenuItemCounterName()
     */
    public static function getMenuItemCounterValue(): null|Closure|string;
    
    public function getDataGridConfig(): DataGridConfig;
    
    public function getDataGridFilterConfig(): FilterConfig;
    
    public function getItemDetailsConfig(): ItemDetailsConfig;
    
    public function getFormConfig(): FormConfig;
    
    public function isSectionAllowed(): bool;
    
    public function isCreateAllowed(): bool;
    
    public function isEditAllowed(): bool;
    
    public function isCloningAllowed(): bool;
    
    public function isDetailsViewerAllowed(): bool;
    
    public function isDeleteAllowed(): bool;
    
    public function isRecordDeleteAllowed(array $record): bool;
    
    public function isRecordEditAllowed(array $record): bool;
    
    public function isRecordDetailsAllowed(array $record): bool;
    
    public function getRecordsForDataGrid(): JsonResponse;
    
    public function getRecordValues(?string $id = null): JsonResponse;
    
    public function getDefaultValuesForFormInputs(): JsonResponse;
    
    public function addRecord(): JsonResponse;
    
    public function updateRecord(string $id): JsonResponse;
    
    public function uploadTempFileForInput(string $inputName): JsonResponse;
    
    public function deleteTempFileForInput(string $inputName): JsonResponse;
    
    public function changeItemPosition(
        string $id,
        string $beforeOrAfter,
        string $otherId,
        string $columnName,
        string $sortDirection
    ): JsonResponse;
    
    public function updateBulkOfRecords(): JsonResponse;
    
    public function deleteRecord(string $id): JsonResponse;
    
    public function deleteBulkOfRecords(): JsonResponse;
    
    public function getCustomData(string $dataId): Response|string|View;
    
    public function getCustomPage(string $pageName): Response|string|View;
    
    public function performCustomAjaxAction(string $actionName): Response|string|View;
    
    public function performCustomDirectAction(string $actionName): Response|string|View;
    
    public function getCustomPageForRecord(string $itemId, string $pageName): Response|string|View;
    
    public function performCustomAjaxActionForRecord(string $itemId, string $actionName): Response|string|View;
    
    public function performCustomDirectActionForRecord(string $itemId, string $actionName): Response|string|View;
    
    /**
     * Get selects options as html for each select-like input in form
     */
    public function getHtmlOptionsForFormInputs(): JsonResponse;
    
    /**
     * Get select options as arrays for $inputName in form
     */
    public function getJsonOptionsForFormInput(string $inputName): JsonResponse;
    
}

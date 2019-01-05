<?php

namespace PeskyCMF\Scaffold;

use PeskyCMF\Scaffold\DataGrid\DataGridConfig;
use PeskyORM\ORM\TableInterface;
use PeskyORMLaravel\Db\KeyValueTableUtils\KeyValueTableInterface;

interface ScaffoldConfigInterface {

    /**
     * @return TableInterface|KeyValueTableInterface
     */
    static public function getTable();

    /**
     * @return string
     */
    static public function getResourceName();

    /**
     * Main menu item info. Return null if you do not want to add item to menu
     * Details in CmfConfig::menu()
     * @return array|null
     */
    static public function getMainMenuItem();

    /**
     * @return string
     */
    static public function getMenuItemCounterName();

    /**
     * Get value for menu item counter (some html code to display near menu item button: new items count, etc)
     * More info: CmfConfig::menu()
     * You may return an HTML string or \Closure that returns that string.
     * Note that self::getMenuItemCounterName() uses this method to decide if it should return null or counter name.
     * If you want to return HTML string consider overwriting of self::getMenuItemCounterName()
     * @return null|\Closure|string
     */
    static public function getMenuItemCounterValue();

    /**
     * @return DataGridConfig
     */
    public function getDataGridConfig();

    public function getDataGridFilterConfig();

    public function getItemDetailsConfig();

    public function getFormConfig();

    public function isSectionAllowed();

    public function isCreateAllowed();

    public function isEditAllowed();

    public function isDetailsViewerAllowed();

    public function isDeleteAllowed();

    public function isRecordDeleteAllowed(array $record);

    public function isRecordEditAllowed(array $record);

    public function isRecordDetailsAllowed(array $record);

    public function getRecordsForDataGrid();

    public function getRecordValues($id = null);

    public function getDefaultValuesForFormInputs();

    public function addRecord();

    public function updateRecord();

    public function changeItemPosition($id, $beforeOrAfter, $otherId, $columnName, $sortDirection);

    public function updateBulkOfRecords();

    public function deleteRecord($id);

    public function deleteBulkOfRecords();

    public function getCustomData($dataId);

    public function getCustomPage($pageName);

    public function getCustomPageForRecord($itemId, $pageName);

    public function performActionForRecord($itemId, $actionName);

}
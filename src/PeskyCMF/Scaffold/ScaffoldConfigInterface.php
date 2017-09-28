<?php

namespace PeskyCMF\Scaffold;

use PeskyCMF\Scaffold\DataGrid\DataGridConfig;
use PeskyORM\ORM\TableInterface;

interface ScaffoldConfigInterface {

    /**
     * @return TableInterface
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

    public function updateBulkOfRecords();

    public function deleteRecord($id);

    public function deleteBulkOfRecords();

    public function getCustomData($dataId);

}
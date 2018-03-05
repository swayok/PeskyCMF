<?php

namespace PeskyCMF\Db\HttpRequestLogs;

use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Scaffold\DataGrid\ColumnFilter;
use PeskyCMF\Scaffold\DataGrid\DataGridColumn;
use PeskyCMF\Scaffold\ItemDetails\ValueCell;
use PeskyCMF\Scaffold\NormalTableScaffoldConfig;
use Swayok\Html\Tag;
use Symfony\Component\Debug\Exception\ClassNotFoundException;

class CmfHttpRequestLogsScaffoldConfig extends NormalTableScaffoldConfig {

    protected $isDetailsViewerAllowed = true;
    protected $isCreateAllowed = false;
    protected $isEditAllowed = false;
    protected $isDeleteAllowed = false;

    static public function getTable() {
        return CmfHttpRequestLogsTable::getInstance();
    }

    static public function getIconForMenuItem() {
        return 'fa fa-exchange';
    }

    protected function createDataGridConfig() {
        return parent::createDataGridConfig()
            ->readRelations([
                'Admin' => ['*'],
            ])
            ->setOrderBy('id', 'desc')
            ->setInvisibleColumns('table', 'http_method', 'response_type')
            ->setColumns([
                'id',
                'url' => DataGridColumn::create()
                    ->setValueConverter(function ($value, $columnConfig, array $record) {
                        return $record['http_method'] . ': ' . $value;
                    }),
                'filter',
                'section',
                'response_code' => DataGridColumn::create()
                    ->setValueConverter(function ($value, $columnConfig, array $record) {
                        return $value . ' (' . $record['response_type'] . ')';
                    }),
                'ip',
                'item_id' => DataGridColumn::create()
                    ->setValueConverter(function ($value, $columnConfig, array $record) {
                        if (!empty($record['table'])) {
                            if ($record['http_method'] !== 'DELETE') {
                                try {
                                    CmfConfig::getInstance()->getTableByUnderscoredName($record['table']);
                                    if (!empty($record['item_id'])) {
                                        $url = routeToCmfItemDetails($record['table'], $record['item_id']);
                                    } else {
                                        $url = routeToCmfItemsTable($record['table']);
                                    }

                                    return Tag::a(rtrim($record['table'] . ' -> ' . $record['item_id'], '-> '))
                                        ->setHref($url)
                                        ->setTarget('_blank')
                                        ->build();
                                } catch (ClassNotFoundException $exc) {}
                            }
                            return rtrim($record['table'] . ' -> ' . $record['item_id'], '-> ');
                        }
                        return '';
                    }),
                'created_at',
                'admin_id' => DataGridColumn::create()
                    ->setType(DataGridColumn::TYPE_LINK)
            ]);
    }
    
    protected function createDataGridFilterConfig() {
        return parent::createDataGridFilterConfig()
            ->setFilters([
                'created_at',
                'id',
                'url',
                'http_method',
                'ip',
                'filter',
                'section',
                'response_code',
                'response_type',
                'table',
                'item_id',
                'admin_email',
                'admin_id'
            ])
            ->addDefaultCondition('created_at', ColumnFilter::OPERATOR_EQUAL, date('Y-m-d'));
    }

    protected function createItemDetailsConfig() {
        return parent::createItemDetailsConfig()
            ->setWidth(80)
            ->readRelations([
                'Admin',
            ])
            ->setValueCells([
                'id',
                'url',
                'http_method',
                'filter',
                'section',
                'response_code',
                'response_type',
                'table',
                'item_id',
                'data_before',
                'data_after',
                'created_at',
                'responded_at',
                'ip',
                'admin_id' => ValueCell::create()
                    ->setType(ValueCell::TYPE_LINK),
                'admin_email',
                'request',
                'response' => ValueCell::create()
                    ->setValueConverter(function ($value, $columnConfig, array $record, ValueCell $valueViewer) {
                        if (!empty($value) && $value[0] === '{') {
                            $json = json_decode($value, true);
                            if (is_array($json)) {
                                return $valueViewer->doDefaultValueConversionByType($json, $valueViewer::TYPE_JSON, $record);
                            }
                        }
                        return $valueViewer->doDefaultValueConversionByType(htmlentities($value), $valueViewer::TYPE_MULTILINE, $record);
                    }),
                'debug',
            ]);
    }

}
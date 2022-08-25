<?php

declare(strict_types=1);

namespace PeskyCMF\Db\HttpRequestLogs;

use PeskyCMF\Scaffold\DataGrid\ColumnFilter;
use PeskyCMF\Scaffold\DataGrid\DataGridColumn;
use PeskyCMF\Scaffold\DataGrid\DataGridConfig;
use PeskyCMF\Scaffold\DataGrid\FilterConfig;
use PeskyCMF\Scaffold\ItemDetails\ItemDetailsConfig;
use PeskyCMF\Scaffold\ItemDetails\ValueCell;
use PeskyCMF\Scaffold\NormalTableScaffoldConfig;
use Swayok\Html\Tag;

class CmfHttpRequestLogsScaffoldConfig extends NormalTableScaffoldConfig
{
    
    protected bool $isDetailsViewerAllowed = true;
    protected bool $isCreateAllowed = false;
    protected bool $isEditAllowed = false;
    protected bool $isDeleteAllowed = false;
    
    public static function getTable(): CmfHttpRequestLogsTable
    {
        return CmfHttpRequestLogsTable::getInstance();
    }
    
    public static function getIconForMenuItem(): ?string
    {
        return 'fa fa-exchange';
    }
    
    protected function createDataGridConfig(): DataGridConfig
    {
        return parent::createDataGridConfig()
            ->setOrderBy('id', 'desc')
            ->setInvisibleColumns('table', 'http_method', 'response_type', 'requester_table', 'requester_info')
            ->setColumns([
                'id',
                'url' => DataGridColumn::create()
                    ->setValueConverter(function ($value, $_, array $record) {
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
                    ->setValueConverter(function ($_, $__, array $record) {
                        return $this->getLinkToItem($record);
                    }),
                'created_at',
                'requester_id' => DataGridColumn::create()
                    ->setValueConverter(function ($_, $__, array $record) {
                        return $this->getLinkToRequester($record);
                    }),
            ]);
    }
    
    protected function createDataGridFilterConfig(): FilterConfig
    {
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
                'requester_table',
                'requester_id',
                'requester_info',
            ])
            ->addDefaultCondition('created_at', ColumnFilter::OPERATOR_EQUAL, date('Y-m-d'));
    }
    
    protected function createItemDetailsConfig(): ItemDetailsConfig
    {
        return parent::createItemDetailsConfig()
            ->setWidth(80)
            ->setAdditionalColumnsToSelect('requester_info', 'requester_table')
            ->setValueCells([
                'id',
                'url',
                'http_method',
                'filter',
                'section',
                'response_code',
                'response_type',
                'table',
                'item_id' => ValueCell::create()
                    ->setValueConverter(function ($_, $__, array $record) {
                        return $this->getLinkToItem($record);
                    }),
                'data_before',
                'data_after',
                'created_at',
                'responded_at',
                'ip',
                'requester_id' => ValueCell::create()
                    ->setValueConverter(function ($_, $__, array $record) {
                        return $this->getLinkToRequester($record);
                    }),
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
                'debug' => ValueCell::create()
                    ->setType(ValueCell::TYPE_MULTILINE),
            ]);
    }
    
    protected function getLinkToItem(array $record): string
    {
        if (!empty($record['table'])) {
            if ($record['http_method'] !== 'DELETE') {
                try {
                    $this->cmfConfig->getScaffoldConfigClass($record['table']);
                    if (!empty($record['item_id'])) {
                        $url = routeToCmfItemDetails($record['table'], $record['item_id']);
                    } else {
                        $url = routeToCmfItemsTable($record['table']);
                    }
                    
                    return Tag::a(rtrim($record['table'] . ' -> ' . $record['item_id'], '-> '))
                        ->setHref($url)
                        ->setTarget('_blank')
                        ->build();
                } catch (\Throwable $exc) {
                }
            }
            return rtrim($record['table'] . ' -> ' . $record['item_id'], '-> ');
        }
        return '';
    }
    
    protected function getLinkToRequester(array $record)
    {
        if (!empty($record['requester_table'])) {
            $label = $record['requester_table'] . ' -> ' . $record['requester_id'];
            if (!empty($record['requester_info'])) {
                $label .= " ({$record['requester_info']})";
            }
            try {
                $this->cmfConfig->getScaffoldConfigClass($record['requester_table']);
                if (!empty($record['requester_id'])) {
                    $url = routeToCmfItemDetails($record['requester_table'], $record['requester_id']);
                } else {
                    $url = routeToCmfItemsTable($record['requester_table']);
                }
                
                return Tag::a($label)
                    ->setHref($url)
                    ->setTarget('_blank')
                    ->build();
            } catch (\Throwable $exc) {
            }
            return $label;
        }
        return $record['requester_info'];
    }
    
}
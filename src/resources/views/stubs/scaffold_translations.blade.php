<?php
/**
 * @var string $tableName
 * @var array $columns
 */
?>
    '{{ $tableName }}' => [
        'menu_title' => ,
        'datagrid' => [
            'header' => ,
            'column' => [@foreach($columns as $columnName)
                '{{ $columnName }}' => '',
@endforeach            ],
            'filter' => [
                '{$table->getTableStructure()->getTableName()}' => [@foreach($columns as $columnName)
                    '{{ $columnName }}' => '',
@endforeach            ],
            ],
        ],
        'form' => [
            'header_create' => ,
            'header_edit' => ,
            'input' => [@foreach($columns as $columnName)
                '{{ $columnName }}' => '',
@endforeach            ],
        ],
        'item_details' => [
            'header' => ,
            'field' => [@foreach($columns as $columnName)
                '{{ $columnName }}' => '',
@endforeach            ],
        ],
    ],

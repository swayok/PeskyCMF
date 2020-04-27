<?php
/**
 * @var array $columns
 * @var string $modelAlias
 * @var string $objectClassName
 * @var string $namespace
 * @var string $parentClass
 */

/**
 * @param $column
 * @return string
 */
$convertDbTypeToPhpType = function ($column) {
    $dataTypes = [
        \PeskyORM\DbColumnConfig::DB_TYPE_INT => 'int',
        \PeskyORM\DbColumnConfig::DB_TYPE_FLOAT => 'float',
        \PeskyORM\DbColumnConfig::DB_TYPE_BOOL => 'bool',
    ];
    if (isset($dataTypes[$column['data_type']])) {
        $type = $dataTypes[$column['data_type']];
    } else {
        $type = 'string';
    }
    $type .= $column['is_nullable'] === 'YES' ? '|null' : '';
    return sprintf('%-13s', $type);
};

$timestampTypes = [
    'timestamp without time zone',
    'timestamp with time zone',
];
echo '<?php';
?>


namespace {{ $namespace }};

use {{ $objectParentClass }};

/**
@foreach($columns as $column)
 * @property-read {{ $convertDbTypeToPhpType($column) }} ${{ $column['column_name'] }}
@if (in_array($column['data_type'], $timestampTypes))
 * @property-read string        ${{ $column['column_name'] }}_as_date
 * @property-read string        ${{ $column['column_name'] }}_as_time
 * @property-read int           ${{ $column['column_name'] }}_as_unix_ts
@endif
@if ($column['data_type'] === \PeskyORM\DbColumnConfig::DB_TYPE_JSONB)
 * @property-read array         ${{ $column['column_name'] }}_as_array
@endif
@endforeach
 *
@foreach($columns as $column)
@if (!in_array($column['column_name'], ['created_at', 'updated_at']))
 * {{ '@' . 'method' }} $this    set{{ \Swayok\Utils\StringUtils::classify($column['column_name']) }}($value)
@endif
@endforeach
 */
class {{ $objectClassName }} extends {{ class_basename($objectParentClass) }} {

}
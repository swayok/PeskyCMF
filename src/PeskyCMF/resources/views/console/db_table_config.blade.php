<?php
/**
 * @var array $columns
 * @var string $modelAlias
 * @var string $tableConfigClassName
 * @var string $table
 * @var string $namespace
 * @var string $tableConfigParentClass
 * @var array $traitsForTableConfig
 */

$classesToInclude = [
    'PeskyORM\DbColumnConfig'
];
if (empty($traitsForTableConfig)) {
    $traitsForTableConfig = [];
} else {
    $traitsForTableConfig = array_unique($traitsForTableConfig);
    $columnsNames = array_keys($columns);
    foreach ($traitsForTableConfig as $traitClass => $traitColumns) {
        if (is_int($traitClass)) {
            $traitClass = $traitColumns;
            $traitColumns = null;
        }
        if (empty($traitColumns)) {
            $reflection = new ReflectionClass($traitClass);
            $traitMethods = $reflection->getMethods(ReflectionMethod::IS_PRIVATE);
            if (empty($traitMethods)) {
                continue;
            }
            foreach ($traitMethods as $reflectionMethod) {
                if (preg_match('%^[a-z_0-9]+$%', $reflectionMethod->getName())) {
                    $traitColumns[] = $reflectionMethod->getName();
                }
            }
        }

        if (!empty($traitColumns) && count(array_intersect($columnsNames, $traitColumns)) === count($traitColumns)) {
            $classesToInclude[] = $traitClass;
            $traits[] = class_basename($traitClass);
            foreach ($traitColumns as $traitColumnName) {
                unset($columns[$traitColumnName]);
            }
        }
    }
}

$dbDataTypeToColumnConfigDataType = function ($column) {
    $dataTypes = [
        \PeskyORM\DbColumnConfig::DB_TYPE_INT => 'TYPE_INT',
        \PeskyORM\DbColumnConfig::DB_TYPE_SMALLINT => 'TYPE_INT',
        \PeskyORM\DbColumnConfig::DB_TYPE_BIGINT => 'TYPE_INT',
        \PeskyORM\DbColumnConfig::DB_TYPE_FLOAT => 'TYPE_FLOAT',
        \PeskyORM\DbColumnConfig::DB_TYPE_BOOL => 'TYPE_BOOL',
        \PeskyORM\DbColumnConfig::DB_TYPE_TIMESTAMP => 'DB_TYPE_TIMESTAMP',
        'timestamp without time zone' => 'DB_TYPE_TIMESTAMP',
        \PeskyORM\DbColumnConfig::DB_TYPE_TIME => 'DB_TYPE_TIME',
        \PeskyORM\DbColumnConfig::DB_TYPE_DATE => 'DB_TYPE_DATE',
        \PeskyORM\DbColumnConfig::DB_TYPE_IP_ADDRESS => 'TYPE_IPV4_ADDRESS',
        \PeskyORM\DbColumnConfig::DB_TYPE_TEXT => 'TYPE_TEXT',
        \PeskyORM\DbColumnConfig::DB_TYPE_JSONB => 'TYPE_JSON',
    ];
    if (isset($dataTypes[$column['data_type']])) {
        return $dataTypes[$column['data_type']];
    } else {
        return 'TYPE_STRING';
    }
};
?>

<?php echo '<?php';?>

namespace {{ $namespace }};

use {{ $tableConfigParentClass }};
@foreach($classesToInclude as $includeClass)
use {{ $includeClass }};
@endforeach

class {{ $tableConfigClassName }} extends {{ class_basename($tableConfigParentClass) }} {

    const TABLE_NAME = '{{ $table }}';
    protected $name = self::TABLE_NAME;

@if(!empty($traits))
    use {{ implode(",\n        ", $traits) }};
@endif

@foreach($columns as $name => $column)
    private function {{$name}}() {
        return DbColumnConfig::create(DbColumnConfig::{{ $dbDataTypeToColumnConfigDataType($column) }})
            ->setIsNullable({{ $column['is_nullable'] === 'YES' ? 'true' : 'false' }})
            ->setIsRequired({{ $column['is_nullable'] === 'YES' || $column['column_default'] !== null ? 'false' : 'true' }})
@if (!in_array($column['column_name'], ['created_at', 'updated_at']))
@if ($column['column_default'] !== null)
<?php
    if (
        $column['data_type'] === \PeskyORM\DbColumnConfig::DB_TYPE_BOOL
        && is_string($column['column_default'])
        && in_array(strtolower($column['column_default']), ['t', 'f', 'true', 'false'])
    ) {
        $default = in_array(strtolower($column['column_default']), ['t', 'true']) ? 'true' : 'false';
    } else if (is_string($column['column_default']) && !is_numeric($column['column_default'])) {
        $default = "'" . preg_replace(['%::.+?$%i', "%'%"], ['', ''], $column['column_default']) . "'";
    } else {
        $default = $column['column_default'];
    }
?>
            ->setDefaultValue({!! $default !!})
@else
            ->setConvertEmptyValueToNull(true)
@endif
@else
            ->setIsExcluded(true)
@endif
            ;
    }

@endforeach
}
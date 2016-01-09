<?php
/**
 * @var array $columns
 * @var string $modelAlias
 * @var string $scaffoldConfigClassName
 * @var string $namespace
 * @var string $parentClass
 */

$fkColumns = [];
$contains = [];
foreach ($columns as $name => $column) {
    if (preg_match('%^(.+)_id$%', $name, $parts)) {
        $fkColumns[] = $name;
        $contains[] = "'" . \Swayok\Utils\StringUtils::modelize($parts[1]) . "'";
    }
}
$contains = implode(", ", $contains);
?>

<?php echo '<?php'; ?>


namespace {{ $namespace }};

use {{ $scaffoldConfigParentClass }};
use PeskyCMF\Scaffold\DataGrid\DataGridFieldConfig;
use PeskyCMF\Scaffold\ItemDetails\ItemDetailsFieldConfig;

class {{ $scaffoldConfigClassName }} extends {{ class_basename($scaffoldConfigParentClass) }} {

    protected $isItemDetailsAllowed = true;
    protected $isCreateAllowed = true;
    protected $isEditAllowed = true;
    protected $isDeleteAllowed = true;

    protected function createDataGridConfig() {
        return parent::createDataGridConfig()
@if (!empty($contains))
            ->setContains([{!! $contains !!}])
@endif
            ->setOrderBy('id', 'asc')
            ->setFields([
@foreach($columns as $name => $column)
@if (!in_array($name, $fkColumns))
                '{{ $name }}',
@else
                '{{ $name }}' => DataGridFieldConfig::create()
                    ->setType(ItemDetailsFieldConfig::TYPE_LINK),
@endif
@endforeach
            ]);
    }

    protected function createDataGridFilterConfig() {
        return parent::createDataGridFilterConfig()
            ->addDefaultConditionForPk()
            ->setFilters([
@foreach($columns as $name => $column)
                '{{ $name }}',
@endforeach
            ]);
    }

    protected function createItemDetailsConfig() {
        return parent::createItemDetailsConfig()
@if (!empty($contains))
            ->setContains([{!! $contains !!}])
@endif
            ->setFields([
@foreach($columns as $name => $column)
                '{{ $name }}',
@endforeach
            ]);
    }

    protected function createFormConfig() {
        return parent::createFormConfig()
            ->setHasFiles(false)
            ->setWidth(50)
            ->setFields([
@foreach($columns as $name => $column)
@if (!in_array($name, ['created_at', 'updated_at', 'id']))
                '{{ $name }}',
@endif
@endforeach
            ]);
    }
}
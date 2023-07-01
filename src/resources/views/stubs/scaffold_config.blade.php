<?php
/**
 * @var string $namespace
 * @var string $className
 * @var string $parentClass
 * @var string $tableClass
 * @var string $pkName
 * @var array $contains
 * @var array $columns
 * @var array $filters
 * @var array $inputs
 */
echo '<?php'
?>

declare(strict_types=1);

namespace $namespace;

use {{ $parentClass }};
use {{ $tableClass }};
use PeskyCMF\Scaffold\DataGrid\DataGridColumn;
use PeskyCMF\Scaffold\Form\FormInput;
use PeskyCMF\Scaffold\Form\InputRenderer;
use PeskyCMF\Scaffold\ItemDetails\ValueCell;

class {{ $className }} extends {{ class_basename($parentClass) }} {

    protected $isDetailsViewerAllowed = true;
    protected $isCreateAllowed = true;
    protected $isEditAllowed = true;
    protected $isCloningAllowed = false;
    protected $isDeleteAllowed = true;

    public static function getTable() {
        return {{ class_basename($tableClass) }}::getInstance();
    }

    protected static function getIconForMenuItem() {
        // icon classes like: 'fa fa-cog' or just delete if you do not want an icon
        return '';
    }

    protected function createDataGridConfig() {
        return parent::createDataGridConfig()
            ->readRelations([@foreach($contains as $relationName)
                '{{ $relationName }}' => ['*'],
@endforeach            ])
            ->setOrderBy('{{ $pkName }}', 'asc')
            ->setColumns([@foreach($columns as $columnName)
                '{{ $columnName }}',
@endforeach            ]);
    }

    protected function createDataGridFilterConfig() {
        return parent::createDataGridFilterConfig()
            ->setFilters([@foreach($filters as $columnName)
                '{{ $columnName }}',
@endforeach            ]);
    }

    protected function createItemDetailsConfig() {
        return parent::createItemDetailsConfig()
            ->readRelations([@foreach($contains as $relationName)
                '{{ $relationName }}' => ['*'],
@endforeach            ])
            ->setValueCells([@foreach($columns as $columnName)
                '{{ $columnName }}',
@endforeach            ]);
    }

    protected function createFormConfig() {
        return parent::createFormConfig()
            ->setWidth(50)
            ->setFormInputs([@foreach($inputs as $columnName)
                '{{ $columnName }}'@if($columnName === 'admin_id') => FormInput::create()
                    ->setType(FormInput::TYPE_HIDDEN)
                    ->setSubmittedValueModifier(function () {
                        return static::getUser()->id;
                    })@endif,
@endforeach            ]);
    }
}

<?php
/**
 * @var array $columns
 * @var string $modelAlias
 * @var string $modelClassName
 * @var string $namespace
 * @var string $modelParentClass
 */
echo '<?php';
?>

namespace {{ $namespace  }};

use {{ $modelParentClass }};

class {{ $modelClassName }} extends {{ class_basename($modelParentClass) }} {

    protected $orderField = 'id';
    protected $orderDirection = self::ORDER_ASCENDING;
    protected $alias = '{{ $modelAlias }}';
}
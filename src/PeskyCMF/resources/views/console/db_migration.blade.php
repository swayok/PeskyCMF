<?php
/**
 * @var array $class
 * @var string $parentClass
 * @var string $title
 * @var array $tables
 * @var bool $isCreateTable
 * @var bool $schema
 */
echo '<?php';
?>

class {{ $class }} extends {{ $parentClass }} {

    public $schema = '{{ $schema }}';
@if ($isCreateTable)
    public $table = '{{ $tables[0] }}';
    public $file = __FILE__;
@else
    public $tables = ['{{ implode('\', \'', $tables) }}'];
    public $file = __FILE__;
    public $upIntro = '{{ $title }}';
    public $downIntro = 'Undo {{ $title }}';
    public $ignoreErrors = false;
@endif

}
<?php
/**
* @var string $namespace
* @var string $groupName
* @var string $extendsClass
*/

echo '<?php'
?>

declare(strict_types=1);

use {{ $namespace }}\Db\{{ $groupName }}\{{ $extendsClass }};

return new class () extends {{ $extendsClass }} {
};

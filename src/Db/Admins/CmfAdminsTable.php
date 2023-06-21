<?php

declare(strict_types=1);

namespace PeskyCMF\Db\Admins;

use PeskyORM\ORM\Table\Table;

class CmfAdminsTable extends Table
{
    public function __construct(?string $tableAlias = 'CmfAdmins')
    {
        parent::__construct(
            new CmfAdminsTableStructure(),
            CmfAdmin::class,
            $tableAlias
        );
    }
}

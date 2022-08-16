<?php

declare(strict_types=1);

namespace PeskyCMF\Db\Admins;

use PeskyCMF\Db\CmfDbTable;

class CmfAdminsTable extends CmfDbTable
{
    
    public function getTableAlias(): string
    {
        return 'CmfAdmins';
    }
    
    public function getTableStructure(): CmfAdminsTableStructure
    {
        return CmfAdminsTableStructure::getInstance();
    }
    
    public function newRecord(): CmfAdmin
    {
        return CmfAdmin::newEmptyRecord();
    }
    
    
}
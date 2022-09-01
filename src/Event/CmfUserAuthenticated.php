<?php

declare(strict_types=1);

namespace PeskyCMF\Event;

use PeskyCMF\Config\CmfConfig;
use PeskyORM\ORM\RecordInterface;

class CmfUserAuthenticated
{
    
    public RecordInterface $user;
    public CmfConfig $cmfConfig;
    
    public function __construct(RecordInterface $user, CmfConfig $cmfConfig)
    {
        $this->user = $user;
        $this->cmfConfig = $cmfConfig;
    }
    
    public function broadcastOn(): array
    {
        return [];
    }
}
<?php

declare(strict_types=1);

namespace PeskyCMF\Event;

use Illuminate\Contracts\Auth\Authenticatable;
use PeskyCMF\Config\CmfConfig;
use PeskyORM\ORM\RecordInterface;

class CmfUserAuthenticated
{
    
    /** @var RecordInterface|Authenticatable */
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
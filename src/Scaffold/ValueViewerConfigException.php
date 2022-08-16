<?php

declare(strict_types=1);

namespace PeskyCMF\Scaffold;

class ValueViewerConfigException extends ScaffoldSectionConfigException
{
    
    private AbstractValueViewer $valueViewer;
    
    public function __construct(AbstractValueViewer $viewer, $message)
    {
        $this->valueViewer = $viewer;
        parent::__construct($viewer->getScaffoldSectionConfig(), $message);
    }
    
    public function getValueViewer(): AbstractValueViewer
    {
        return $this->valueViewer;
    }
    
}
<?php


namespace PeskyCMF\Scaffold;


class ValueViewerException extends ScaffoldSectionException {

    /** @var AbstractValueViewer */
    private $valueViewer;

    public function __construct(AbstractValueViewer $viewer, $message) {
        $this->valueViewer = $viewer;
        parent::__construct($viewer->getScaffoldSectionConfig(), $message);
    }

    /**
     * @return AbstractValueViewer
     */
    public function getValueViewer() {
        return $this->valueViewer;
    }

}
<?php

namespace PeskyCMF\Scaffold\DataGrid;

use PeskyORM\ORM\TableInterface;
use Swayok\Html\Tag;

class DataGridRendererHelper {

    /** @var DataGridConfig  */
    protected $dataGridConfig;
    /** @var string */
    protected $tableNameForRoutes;
    /** @var TableInterface  */
    protected $table;
    /** @var FilterConfig */
    protected $dataGridFilterConfig;
    /** @var DataGridColumn */
    protected $sortedColumnConfigs;

    /**
     * DataGridRendererHelper constructor.
     * @param DataGridConfig $dataGridConfig
     * @param FilterConfig $dataGridFilterConfig
     * @param TableInterface $table
     * @param string $tableNameForRoutes
     */
    public function __construct(
        DataGridConfig $dataGridConfig,
        FilterConfig $dataGridFilterConfig,
        TableInterface $table,
        $tableNameForRoutes
    ) {
        $this->dataGridConfig = $dataGridConfig;
        $this->dataGridFilterConfig = $dataGridFilterConfig;
        $this->table = $table;
        $this->tableNameForRoutes = $tableNameForRoutes;
    }

    /**
     * @return string
     */
    public function getId() {
        return 'scaffold-data-grid-' . str_slug(strtolower($this->tableNameForRoutes));
    }

    /**
     * @return string;
     * @throws \Swayok\Html\HtmlTagException
     */
    public function getHtmlTableMultiselectColumnHeader() {
        if ($this->dataGridConfig->isAllowedMultiRowSelection()) {
            $dropdownBtn = Tag::button()
                ->setType('button')
                ->setClass('rows-selection-options-dropdown-btn')
                ->setDataAttr('toggle' , 'dropdown')
                ->setAttribute('aria-haspopup', 'true')
                ->setAttribute('aria-expanded', 'false')
                ->setContent('<span class="glyphicon glyphicon-menu-hamburger fs15"></span>')
                ->build();

            $selectionActions = [
                Tag::a()
                    ->setContent($this->dataGridConfig->translateGeneral('actions.select_all'))
                    ->setClass('select-all')
                    ->setHref('javascript: void(0)')
                    ->build(),
                Tag::a()
                    ->setContent($this->dataGridConfig->translateGeneral('actions.select_none'))
                    ->setClass('select-none')
                    ->setHref('javascript: void(0)')
                    ->build(),
                Tag::a()
                    ->setContent($this->dataGridConfig->translateGeneral('actions.invert_selection'))
                    ->setClass('invert-selection')
                    ->setHref('javascript: void(0)')
                    ->build()
            ];
            $dropdownMenu = Tag::ul()
                ->setClass('dropdown-menu')
                ->setContent('<li>' . implode('</li><li>', $selectionActions) . '</li>')
                ->build();

            return Tag::th()
                ->setContent(
                    Tag::div()
                        ->setClass('btn-group rows-selection-options float-none')
                        ->setContent($dropdownBtn . $dropdownMenu)
                        ->build()
                )
                ->setClass('text-nowrap text-center')
                ->build();
        }
        return '';
    }

    /**
     * @return \PeskyCMF\Scaffold\AbstractValueViewer[]|DataGridColumn|DataGridColumn[]
     * @throws \PeskyCMF\Scaffold\ValueViewerConfigException
     */
    public function getSortedColumnConfigs() {
        if (!$this->sortedColumnConfigs) {
            $this->sortedColumnConfigs = $this->dataGridConfig->getDataGridColumns();
            uasort($this->sortedColumnConfigs, function ($a, $b) {
                /** @var \PeskyCMF\Scaffold\DataGrid\DataGridColumn $a */
                /** @var \PeskyCMF\Scaffold\DataGrid\DataGridColumn $b */
                return ($a->getPosition() > $b->getPosition());
            });
        }
        return $this->sortedColumnConfigs;
    }

    /**
     * @return string;
     * @throws \UnexpectedValueException
     * @throws \PeskyCMF\Scaffold\ValueViewerConfigException
     * @throws \Swayok\Html\HtmlTagException
     */
    public function getHtmlTableColumnsHeaders() {
        $miltiselectColumn = $this->getHtmlTableMultiselectColumnHeader() . "\n";
        $invisibleColumns = $visibleColumns = '';
        $columns = $this->getSortedColumnConfigs();
        /** @var \PeskyCMF\Scaffold\DataGrid\DataGridColumn $config */
        foreach ($columns as $config) {
            $th = Tag::th()
                ->setContent($config->isVisible() ? $config->getLabel() : '&nbsp;')
                ->setClass('text-nowrap')
                ->setDataAttr('visible', $config->isVisible() ? null : 'false')
                ->setDataAttr('orderable', $config->isVisible() && $config->isSortable() ? 'true' : 'false')
                ->setDataAttr('name', $config->getName())
                ->setDataAttr('data', $config->getName())
                ->build();
            if ($config->isVisible()) {
                $visibleColumns .= $th . "\n";
            } else {
                $invisibleColumns .= $th . "\n";
            }
        }
        return $miltiselectColumn . $visibleColumns . $invisibleColumns;
    }
}
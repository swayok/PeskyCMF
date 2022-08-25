<?php

declare(strict_types=1);
/**
 * @var \PeskyCMF\Scaffold\DataGrid\DataGridCellRenderer $rendererConfig
 * @var \PeskyCMF\Scaffold\DataGrid\DataGridConfig $sectionConfig
 * @var \PeskyCMF\Scaffold\DataGrid\DataGridColumn $valueViewer
 * @var \PeskyORM\ORM\TableInterface $table
 */
echo $valueViewer->getDotJsInsertForValue([], 'srting', null, false);
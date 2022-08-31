<?php
declare(strict_types=1);
/**
 * @var \PeskyCMF\Scaffold\Form\InputRenderer $rendererConfig
 * @var \PeskyCMF\Scaffold\ItemDetails\ItemDetailsConfig $sectionConfig
 * @var \PeskyCMF\Scaffold\ItemDetails\ValueCell $valueViewer
 * @var \PeskyORM\ORM\TableInterface $table
 */
echo $valueViewer->getDotJsInsertForValue([], 'srting', null, false);
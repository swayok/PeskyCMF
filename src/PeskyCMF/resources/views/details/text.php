<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRenderer $rendererConfig
 * @var \PeskyCMF\Scaffold\ItemDetails\ItemDetailsConfig $actionConfig
 * @var \PeskyCMF\Scaffold\ItemDetails\ValueCell $fieldConfig
 * @var \PeskyCMF\Db\CmfDbTable $model
 */
echo '{{= it.' . $fieldConfig->getName() . ' || "" }}';


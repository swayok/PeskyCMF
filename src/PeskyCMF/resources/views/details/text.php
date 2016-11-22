<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRendererConfig $rendererConfig
 * @var \PeskyCMF\Scaffold\ItemDetails\ItemDetailsConfig $actionConfig
 * @var \PeskyCMF\Scaffold\ItemDetails\ItemDetailsFieldConfig $fieldConfig
 * @var \PeskyCMF\Db\CmfDbTable $model
 * @var string $translationPrefix
 */
echo '{{= it.' . $fieldConfig->getName() . ' || "" }}';


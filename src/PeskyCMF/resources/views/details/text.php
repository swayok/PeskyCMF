<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRendererConfig $rendererConfig
 * @var \PeskyCMF\Scaffold\ItemDetails\ItemDetailsConfig $actionConfig
 * @var \PeskyCMF\Scaffold\ItemDetails\ItemDetailsFieldConfig $fieldConfig
 * @var \App\Db\BaseDbModel $model
 * @var string $translationPrefix
 */
echo '{{= it.' . $fieldConfig->getName() . ' || "" }}';


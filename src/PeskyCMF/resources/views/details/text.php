<?php
/**
 * @var \App\Admin\Scaffold\Form\InputRendererConfig $rendererConfig
 * @var \App\Admin\Scaffold\ItemDetails\ItemDetailsConfig $actionConfig
 * @var \App\Admin\Scaffold\ItemDetails\ItemDetailsFieldConfig $fieldConfig
 * @var \App\Db\BaseDbModel $model
 * @var string $translationPrefix
 */
echo '{{= it.' . $fieldConfig->getName() . ' || "" }}';


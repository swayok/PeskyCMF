<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRendererConfig $rendererConfig
 * @var \PeskyCMF\Scaffold\ItemDetails\ItemDetailsConfig $actionConfig
 * @var \PeskyCMF\Scaffold\ItemDetails\ItemDetailsFieldConfig $fieldConfig
 * @var \PeskyCMF\Db\CmfDbModel $model
 * @var string $translationPrefix
 */
?>
{{? !!it.<?php echo $fieldConfig->getName() ?> && it.<?php echo $fieldConfig->getName() ?>.length > 0 }}
<div class="image-previews-container">
    {{? $.isArray(it.<?php echo $fieldConfig->getName() ?>) }}
        {{~ it.<?php echo $fieldConfig->getName() ?> :imageInfo}}
            <div class="img-thumbnail image-preview mb5" style="vertical-align: top">
            {{? !!imageInfo.url }}
                {{? imageInfo.label }}<p class="text-center">{{= imageInfo.label }}</p>{{?}}
                <img src="{{= imageInfo.url || imageInfo }}?_=<?php echo time(); ?>">
            {{??}}
                <img src="{{= imageInfo }}?_=<?php echo time(); ?>">
            {{?}}
            </div>
        {{~}}
    {{??}}
        <div class="img-thumbnail image-preview">
            <img src="{{= it.<?php echo $fieldConfig->getName() ?> }}?_=<?php echo time(); ?>">
        </div>
    {{?}}
</div>
{{?}}

<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRenderer $rendererConfig
 * @var \PeskyCMF\Scaffold\ItemDetails\ItemDetailsConfig $sectionConfig
 * @var \PeskyCMF\Scaffold\ItemDetails\ValueCell $valueViewer
 * @var \PeskyORM\ORM\TableInterface $table
 */
?>
{{? !!it.<?php echo $valueViewer->getName() ?> && it.<?php echo $valueViewer->getName() ?>.length > 0 }}
<div class="image-previews-container">
    {{? $.isArray(it.<?php echo $valueViewer->getName() ?>) }}
        {{~ it.<?php echo $valueViewer->getName() ?> :imageInfo}}
            <div class="img-thumbnail image-preview" style="vertical-align: top">
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
            <img src="{{= it.<?php echo $valueViewer->getName() ?> }}?_=<?php echo time(); ?>">
        </div>
    {{?}}
</div>
{{?}}

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
    {{? $.isArray(<?php echo $valueViewer->getFailsafeValueForDotJs([], 'array', 'null') ?>) }}
        {{~ <?php echo $valueViewer->getFailsafeValueForDotJs([], 'array') ?> :imageInfo}}
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
            <img src="<?php echo $valueViewer->getDotJsInsertForValue() ?>?_=<?php echo time(); ?>">
        </div>
    {{?}}
</div>
{{?}}

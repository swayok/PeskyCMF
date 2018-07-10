<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRenderer $rendererConfig
 * @var \PeskyCMF\Scaffold\ItemDetails\ItemDetailsConfig $sectionConfig
 * @var \PeskyCMF\Scaffold\ItemDetails\ValueCell $valueViewer
 * @var \PeskyORM\ORM\TableInterface $table
 */
?>
{{? !!it.<?php echo $valueViewer->getName() ?> }}
<div class="image-previews-container">
    {{? $.isArray(<?php echo $valueViewer->getFailsafeValueForDotJs([], 'array', 'null') ?>) }}
        {{~ <?php echo $valueViewer->getFailsafeValueForDotJs([], 'array') ?> :imageInfo}}
            <div class="img-thumbnail image-preview" style="vertical-align: top">
            {{? !!imageInfo.url }}
                {{? imageInfo.label }}<p class="text-center image-preview-label">{{= imageInfo.label }}</p>{{?}}
                <img src="{{= imageInfo.url || imageInfo }}?_=<?php echo time(); ?>" class="img-responsive">
            {{??}}
                <img src="{{= imageInfo }}?_=<?php echo time(); ?>" class="img-responsive">
            {{?}}
            </div>
        {{~}}
    {{??}}
        {{? $.isPlainObject(<?php echo $valueViewer->getFailsafeValueForDotJs([], 'object', 'null') ?>) }}
            {{? <?php echo $valueViewer->getFailsafeValueForDotJs(['url']) ?> }}
                <div class="img-thumbnail image-preview">
                    {{? <?php echo $valueViewer->getFailsafeValueForDotJs(['label']) ?> }}
                        <p class="text-center image-preview-label"><?php echo $valueViewer->getDotJsInsertForValue(['label']) ?></p>
                    {{?}}
                    <img src="<?php echo $valueViewer->getDotJsInsertForValue(['url']) ?>?_=<?php echo time(); ?>" class="img-responsive">
                </div>
            {{?}}
        {{??}}
            {{? <?php echo $valueViewer->getFailsafeValueForDotJs() ?> }}
                <div class="img-thumbnail image-preview">
                    <img src="<?php echo $valueViewer->getDotJsInsertForValue() ?>?_=<?php echo time(); ?>" class="img-responsive">
                </div>
            {{?}}
        {{?}}
    {{?}}
</div>
{{?}}

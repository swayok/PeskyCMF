<?php
declare(strict_types=1);
/**
 * @var \PeskyCMF\Scaffold\Form\InputRenderer $rendererConfig
 * @var \PeskyCMF\Scaffold\ItemDetails\ItemDetailsConfig $sectionConfig
 * @var \PeskyCMF\Scaffold\ItemDetails\ValueCell $valueViewer
 * @var \PeskyORM\ORM\TableInterface $table
 * @var bool $noTimestamp
 */
$urlQuery = empty($noTimestamp) ? '?_=' . time() : ''
?>
{{? !!it.<?php echo $valueViewer->getName() ?> }}
<div class="image-previews-container">
    {{? $.isArray(<?php echo $valueViewer->getFailsafeValueForDotJs([], 'array', 'null') ?>) }}
        {{~ <?php echo $valueViewer->getFailsafeValueForDotJs([], 'array') ?> :imageInfo}}
            <div class="img-thumbnail image-preview" style="vertical-align: top">
            {{? !!imageInfo.url }}
                {{? imageInfo.label }}<p class="text-center image-preview-label">{{= imageInfo.label }}</p>{{?}}
                <img src="{{= imageInfo.url || imageInfo }}<?php echo $urlQuery; ?>" class="img-responsive">
            {{??}}
                <img src="{{= imageInfo }}<?php echo $urlQuery; ?>" class="img-responsive">
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
                    <img src="<?php echo $valueViewer->getDotJsInsertForValue(['url']) . $urlQuery ?>" class="img-responsive">
                </div>
            {{?}}
        {{??}}
            {{? <?php echo $valueViewer->getFailsafeValueForDotJs() ?> }}
                <div class="img-thumbnail image-preview">
                    <img src="<?php echo $valueViewer->getDotJsInsertForValue() . $urlQuery ?>" class="img-responsive">
                </div>
            {{?}}
        {{?}}
    {{?}}
</div>
{{?}}

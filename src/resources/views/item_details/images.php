<?php
declare(strict_types=1);
/**
 * @var \PeskyCMF\Scaffold\Form\InputRenderer $rendererConfig
 * @var \PeskyCMF\Scaffold\ItemDetails\ItemDetailsConfig $sectionConfig
 * @var \PeskyCMF\Scaffold\ItemDetails\ValueCell $valueViewer
 * @var \PeskyORM\ORM\TableInterface $table
 * @var array $names
 */
?>

{{? <?php echo $valueViewer->getFailsafeValueForDotJs([], 'array', '[]') ?>.length }}
    {{~ <?php echo $valueViewer->getVarNameForDotJs() ?> :configFiles }}
        {{? $.isPlainObject(configFiles) && $.isArray(configFiles.files) && configFiles.files.length > 0 }}
        <div class="image-previews-container">
            {{? configFiles.label }}<p class="text-center">{{= configFiles.label }}</p>{{?}}
            {{~ configFiles.files :imageInfo}}
                {{? imageInfo.name }}
                <div class="img-thumbnail image-preview" style="vertical-align: top">
                    <img src="{{= imageInfo.url }}" title="{{= imageInfo.url }}" class="mw200 mh-200">
                </div>
                {{?}}
            {{~}}
        </div>
        {{?}}
    {{~}}
{{?}}

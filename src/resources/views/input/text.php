<?php
declare(strict_types=1);
/**
 * @var \PeskyCMF\Scaffold\Form\InputRenderer $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\FormInput $valueViewer
 * @var \PeskyCMF\Scaffold\Form\FormConfig $sectionConfig
 * @var \PeskyORM\ORM\TableInterface $table
 */
$rendererConfig
    ->addAttribute('name', $valueViewer->getName(true), false)
    ->addAttribute('id', $valueViewer->getDefaultId(), false)
    ->addAttribute('class', 'form-control', false)
    ->addAttribute('type', 'text', false);
$attributesForCreate = \Swayok\Html\Tag::buildAttributes($rendererConfig->getAttributesForCreate());
$attributesForEdit = \Swayok\Html\Tag::buildAttributes($rendererConfig->getAttributesForEdit());
$hasAddons = $rendererConfig->hasPrefixText() || $rendererConfig->hasSuffixText();
if ($valueViewer->hasOptionsOrOptionsLoader() && $valueViewer->isOptionsFilteringEnabled()) {
    $routeName = $sectionConfig->getScaffoldConfig()->getCmfConfig()->getRouteName('cmf_api_get_options_as_json');
    $routeData = [
        'resource' => $sectionConfig->getScaffoldConfig()->getResourceName(),
        'input_name' => $valueViewer->getName(),
    ];
    $additionalOptions = implode(' ', [
        'data-abs-ajax-url="' . route($routeName, $routeData) . '?id={{= it.id || \'\' }}"',
        'data-abs-ajax-type="GET"',
    ]);
    $attributesForCreate .= ' ' . $additionalOptions;
    $attributesForEdit .= ' ' . $additionalOptions;
}
?>

<div class="form-group">
    <label for="<?php echo $rendererConfig->getAttribute('id'); ?>"><?php echo $valueViewer->getLabel($rendererConfig); ?></label>
    <?php if ($hasAddons) : ?>
        <div class="input-group">
            <?php if ($rendererConfig->hasPrefixText()) : ?>
                <span class="input-group-addon"><?php echo $rendererConfig->getPrefixText(); ?></span>
            <?php endif;?>
    <?php endif; ?>
            <?php if ($rendererConfig->getAttribute('type') === 'password'): ?>
                <input type="password" name="<?php echo $rendererConfig->getAttribute('name') ?>" class="hidden" formnovalidate disabled>
            <?php endif; ?>
            <input value="<?php echo $valueViewer->getDotJsInsertForValue() ?>"
                {{? !!it.isCreation }}<?php echo $attributesForCreate; ?>{{??}}<?php echo $attributesForEdit; ?>{{?}}>

    <?php if ($hasAddons) : ?>
            <?php if ($rendererConfig->hasSuffixText()) : ?>
                <span class="input-group-addon"><?php echo $rendererConfig->getSuffixText(); ?></span>
            <?php endif ;?>
        </div>
    <?php endif; ?>
    <?php echo $valueViewer->getFormattedTooltip(); ?>
</div>

<?php if ($valueViewer->hasOptionsOrOptionsLoader()) : ?>
<script type="application/javascript">
    $(function () {
        var $input = $('#<?php echo $rendererConfig->getAttribute('id'); ?>');
        {{? typeof it._options !== undefined && $.isPlainObject(it._options) && typeof it._options['<?php echo $valueViewer->getName(); ?>'] !== undefined }}
            var options = [];
            $('<select></select>')
                .html('{{= it._options['<?php echo $valueViewer->getName(); ?>'].replace(/\'/, "\\\'") }}')
                .find('option')
                    .each(function (index, item) {
                        var text = $(item).text();
                        if (text && text.length > 0) {
                            options.push(text);
                        }
                    });
        {{??}}
            var options = <?php echo json_encode(value($valueViewer->getOptions())); ?>;
        {{?}}
        var timeout, lastQuery = $input.val();
        $input.typeahead(
            {
                minLength: <?php echo $rendererConfig->getAttribute('data-min-length', $valueViewer->getMinCharsRequiredToInitOptionsFiltering()); ?>,
                highlight: <?php echo $rendererConfig->getAttribute('data-highlight', true) ? 'true' : 'false'; ?>,
                hint: <?php echo $rendererConfig->getAttribute('data-hint', true) ? 'true' : 'false'; ?>,
            },
            {
                async: <?php echo $valueViewer->hasOptionsLoader() ? 'true' : 'false'; ?>,
                limit: <?php echo $rendererConfig->getAttribute('data-limit', 8); ?>,
                source: function (query, syncResults, asyncResults) {
                    <?php if ($valueViewer->isOptionsFilteringEnabled()) : ?>
                        if (timeout) {
                            clearTimeout(timeout);
                            timeout = null;
                        }
                        if (!lastQuery || lastQuery !== query) {
                            timeout = setTimeout(function () {
                                $.ajax({
                                        url: $input.attr('data-abs-ajax-url'),
                                        method: $input.attr('data-abs-ajax-type') || 'GET',
                                        type: 'json',
                                        data: {keywords: query}
                                    })
                                    .done(function (options) {
                                        var ret = [];
                                        for (var i = 0; i < options.length; i++) {
                                            ret.push(options[i].text);
                                        }
                                        lastQuery = query;
                                        asyncResults(ret);
                                    })
                                    .fail(function () {
                                        asyncResults([]);
                                    });
                            }, 500);
                        }
                    <?php else : ?>
                        var matches, substringRegex;

                        matches = [];

                        // regex used to determine if a string contains the substring `q`
                        substringRegex = new RegExp(query, 'i');

                        // iterate through the pool of strings and for any string that
                        // contains the substring `q`, add it to the `matches` array
                        $.each(options, function (i, str) {
                            if (substringRegex.test(str)) {
                                matches.push(str);
                            }
                        });

                        syncResults(matches);
                    <?php endif; ?>
                }
            }
        );
    });
</script>
<?php endif; ?>

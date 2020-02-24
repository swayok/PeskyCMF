<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRenderer $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\JsonListFormInput $valueViewer
 * @var \PeskyCMF\Scaffold\Form\FormConfig $sectionConfig
 * @var \PeskyORM\ORM\TableInterface $table
 */
$defaultId = $valueViewer->getDefaultId();
$dotJsValueGetter = $valueViewer->getDotJsInsertForValue([], 'array_encode');
$valueViewer->setVarNameForDotJs('value');
$rendererConfig
    ->addAttribute('name', $valueViewer->getName(true), false)
    ->addAttribute('id', $valueViewer->getDefaultId(), false)
    ->addAttribute('class', 'form-control input-sm', false)
    ->addAttribute('type', 'text', false);
$rendererConfig->addAttribute('id', $rendererConfig->getAttribute('id') . '-{{= it.index }}', true);
$inputName = $rendererConfig->getAttribute('name');
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

<div id="<?php echo $defaultId; ?>-container" class="json-list-input-container">
    <div class="section-divider">
        <span><?php echo $valueViewer->getLabel($rendererConfig) ?></span>
    </div>
    <script type="text/html" id="<?php echo $defaultId ?>-row-tpl">
        <tr>
            <td class="sub-input form-group">
                <?php if ($hasAddons) : ?>
                    <div class="input-group">
                        <?php if ($rendererConfig->hasPrefixText()) : ?>
                            <span class="input-group-addon"><?php echo $rendererConfig->getPrefixText(); ?></span>
                        <?php endif;?>
                <?php endif; ?>
                        <input value="<?php echo $valueViewer->getDotJsInsertForValue() ?>"
                            {{? !!it.isCreation }}<?php echo $attributesForCreate; ?>{{??}}<?php echo $attributesForEdit; ?>{{?}}
                           data-index="{{= it.index }}"
                        >
                <?php if ($hasAddons) : ?>
                        <?php if ($rendererConfig->hasSuffixText()) : ?>
                            <span class="input-group-addon"><?php echo $rendererConfig->getSuffixText(); ?></span>
                        <?php endif ;?>
                    </div>
                <?php endif; ?>
            </td>
            <td class="delete text-center">
                <a
                    href="javascript: void(0)"
                    class="text-danger delete-row"
                    data-toggle="tooltip"
                    title="<?php echo $valueViewer->getDeleteRowButtonLabel() ?>"
                    tabindex="-1"
                >
                    <i class="glyphicon glyphicon-remove fs16 lh30"></i>
                </a>
            </td>
        </tr>
    </script>
    <table class="table table-condensed table-bordered table-striped mbn json-array-input-table">
        <thead>
            <tr>
                <th><?php echo $valueViewer->getTableHeaderForValue() ?></th>
                <th width="60">&nbsp;</th>
            </tr>
        </thead>
        <tbody id="<?php echo $defaultId ?>-rows-container">

        </tbody>
    </table>
    <div class="mv15 text-center">
        <button type="button" class="btn btn-default btn-sm" id="<?php echo $defaultId; ?>-add-row">
            <?php echo $valueViewer->getAddRowButtonLabel() ?>
        </button>
    </div>
    <?php echo $valueViewer->getFormattedTooltip(); ?>
    <hr>
</div>

<script type="application/javascript">
    $(function () {
        var initTypeAhead = function ($input) {};
        <?php if ($valueViewer->hasOptionsOrOptionsLoader()) : ?>
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
            initTypeAhead = function ($input) {
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
            };
        <?php endif; ?>
        var $rowsContainer = $('#<?php echo $defaultId ?>-rows-container');
        var rowTpl = doT.template($('#<?php echo $defaultId ?>-row-tpl').html());
        var defaultTplData = {isCreation: {{= it.isCreation }}};
        var values = <?php echo $dotJsValueGetter ?>;
        var maxRows = <?php echo $valueViewer->getMaxValuesCount(); ?>;
        var minRows = <?php echo $valueViewer->getMinValuesCount(); ?>;
        var initialRowsCount = <?php echo $valueViewer->getInitialRowsCount(); ?>;
        var rowIndex = 0;
        var rowsCount = 0;
        var addRow = function (tplData) {
            if (maxRows > 0 && rowsCount + 1 > maxRows) {
                return false;
            }
            if (!$.isPlainObject(tplData)) {
                tplData = {};
            }
            tplData = $.extend({}, defaultTplData, tplData, {index: rowIndex});
            rowIndex++;
            $rowsContainer.append(rowTpl(tplData));
            rowsCount++;
            initTypeAhead($rowsContainer.find('input[data-index="' + tplData.index + '"]'));
            return true;
        };
        var $addRowBtn = $('#<?php echo $defaultId; ?>-add-row');
        $addRowBtn.on('click', function () {
            if (!addRow({}) || maxRows > 0 && rowsCount >= maxRows) {
                $addRowBtn.hide();
            }
        });
        $rowsContainer.on('click', 'a.delete-row', function () {
            $(this).tooltip('hide');
            if (rowsCount <= minRows) {
                // required number of rows is less or equal to currently added rows
                toastr.error('<?php echo cmfTransGeneral('.form.input.key_value_set.row_delete_action_forbidden') ?>');
                return false;
            }
            $(this).tooltip('destroy');
            $(this).closest('tr').remove();
            rowsCount--;
            if (maxRows > 0 && rowsCount <= maxRows) {
                $addRowBtn.show();
            }
            return false;
        });
        if ($.isArray(values)) {
            for (var i = 0; i < values.length; i++) {
                addRow({value: values[i]});
            }
        }
        if (rowsCount < minRows) {
            for (i = rowsCount; i < minRows; i++) {
                addRow({});
            }
        }
        if (defaultTplData.isCreation || rowsCount === 0 && rowsCount < initialRowsCount) {
            for (i = rowsCount; i < initialRowsCount; i++) {
                addRow({});
            }
        }
    });
</script>

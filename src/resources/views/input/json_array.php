<?php
declare(strict_types=1);
/**
 * @var \PeskyCMF\Scaffold\Form\InputRenderer $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\JsonArrayFormInput $valueViewer
 * @var \PeskyCMF\Scaffold\Form\FormConfig $sectionConfig
 * @var \PeskyORM\ORM\TableInterface $table
 */
$defaultId = $valueViewer->getDefaultId();
$inputName = $valueViewer->getName(true);
$subInputs = $valueViewer->getSubInputs();
?>

<div id="<?php echo $defaultId; ?>-container" class="json-array-input-container">
    <div class="section-divider">
        <span><?php echo $valueViewer->getLabel($rendererConfig) ?></span>
    </div>
    <script type="text/html" id="<?php echo $defaultId ?>-row-tpl">
        <tr>
            <?php foreach($subInputs as $subInputName => $subInput): ?>
                <td class="sub-input form-group">
                    <?php
                        $subInputNameForHtml = $subInput->getName(true);
                        $subInputIdForHtml = $subInput->getDefaultId();
                        $subInputHtml = preg_replace(
                            [
                                '%(<div[^>]+?class=".*?)form-group(.*?"[^>]*?>)%is',
                                '%<label for="' . preg_quote($subInputIdForHtml, '%') . '">.*?</label>%is',
                                '%name="' . preg_quote($subInputNameForHtml, '%') . '%is',
                                '%id="' . preg_quote($subInputIdForHtml, '%') . '"%is',
                                '%((?:data-)?value=")[^"]*?(")%i',
                                '%class="form-control"%i'
                            ],
                            [
                                '$1$2',
                                '',
                                'name="' . str_replace('[][' . $subInputName, '[{{= it.index }}][' . $subInputName, $subInputNameForHtml),
                                'id="' . str_replace('-input', '-{{= it.index }}-input', $subInputIdForHtml) . '"',
                                "$1{{= it.{$subInputName} || '' }}$2",
                                'class="form-control input-sm"'
                            ],
                            $subInput->render()
                        );
                        echo $subInputHtml;
                    ?>
                </td>
            <?php endforeach; ?>
            <td class="delete text-center">
                <a
                    href="javascript: void(0)"
                    class="text-danger delete-row"
                    data-toggle="tooltip"
                    title="<?php echo $valueViewer->getDeleteRowButtonLabel() ?>"
                    data-disabler-on-disable="hide"
                >
                    <i class="glyphicon glyphicon-remove fs16 lh30"></i>
                </a>
            </td>
        </tr>
    </script>
    <div class="form-group">
        <input
            type="hidden"
            name="<?php echo $inputName; ?>"
            id="<?php echo $defaultId; ?>-hidden1"
            value=""
            data-disabler-mode="nested-inputs"
            data-disabler-inputs-container="#<?php echo $defaultId; ?>-container"
        >
        <input
            type="hidden"
            disabled
            name="<?php echo $inputName; ?>[]"
            id="<?php echo $defaultId; ?>-hidden2"
            value=""
            data-disabler-mode="nested-inputs"
            data-disabler-inputs-container="#<?php echo $defaultId; ?>-container"
            data-disabler-ignore-this-input="1"
        >
    </div>
    <table class="table table-condensed table-bordered table-striped mbn json-array-input-table">
        <thead>
            <tr>
                <?php foreach($subInputs as $subInputName => $subInput): ?>
                    <th><?php echo $sectionConfig->translate($valueViewer, 'input.' . $subInputName); ?></th>
                <?php endforeach; ?>
                <th width="60">&nbsp;</th>
            </tr>
        </thead>
        <tbody id="<?php echo $defaultId ?>-rows-container">

        </tbody>
    </table>
    <div class="mv15 text-center">
        <button
            type="button"
            class="btn btn-default btn-sm"
            id="<?php echo $defaultId; ?>-add-row"
            data-disabler-on-disable="hide"
        >
            <?php echo $valueViewer->getAddRowButtonLabel() ?>
        </button>
    </div>
    <?php echo $valueViewer->getFormattedTooltip(); ?>
    <hr>
</div>

<script type="application/javascript">
    $(function () {
        var $rowsContainer = $('#<?php echo $defaultId ?>-rows-container');
        var rowTpl = doT.template($('#<?php echo $defaultId ?>-row-tpl').html());
        var defaultTplData = {isCreation: {{= it.isCreation }}};
        var values = <?php echo $valueViewer->getDotJsInsertForValue([], 'array_encode') ?>;
        var maxRows = <?php echo $valueViewer->getMaxValuesCount(); ?>;
        var minRows = <?php echo $valueViewer->getMinValuesCount(); ?>;
        var rowIndex = 0;
        var rowsCount = 0;
        var $disablerTargetInput = $('#<?php echo $defaultId; ?>-hidden1');
        var addRow = function (tplData) {
            if (maxRows > 0 && rowsCount + 1 > maxRows) {
                return false;
            }
            if (!$.isPlainObject(tplData)) {
                tplData = {};
            }
            tplData = $.extend({}, defaultTplData, tplData, {index: rowIndex});
            rowIndex++;
            var $tr = $(rowTpl(tplData));
            $rowsContainer.append($tr);
            FormHelper.initInputPlugins($tr);
            FormHelper.inputsDisablers.runDisablerOnTargetInput($disablerTargetInput);
            rowsCount++;
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
                addRow(values[i]);
            }
        }
        if (rowsCount < minRows) {
            for (i = rowsCount; i <= minRows; i++, rowsCount++) {
                addRow({});
            }
        }
    });
</script>

<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRenderer $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\KeyValueSetFormInput $valueViewer
 * @var \PeskyCMF\Scaffold\Form\FormConfig $sectionConfig
 * @var \PeskyORM\ORM\TableInterface $table
 */
$keysLabel = $valueViewer->getKeysLabel();
$valuesLabel = $valueViewer->getValuesLabel();
$defaultId = $valueViewer->getDefaultId();
$inputName = $valueViewer->getName(true);
$hasKeysOptions = $valueViewer->hasKeysOptions();
$keysOptions = $hasKeysOptions ? $valueViewer->getKeysOptions() : [];
?>

<div id="<?php echo $defaultId; ?>-container">
    <div class="section-divider">
        <span><?php echo $valueViewer->getLabel($rendererConfig) ?></span>
    </div>
    <script type="text/html" id="<?php echo $defaultId ?>-row-tpl">
        <tr>
            <td class="key form-group">
                <?php if ($hasKeysOptions): ?>
                    <select name="<?php echo $inputName; ?>[{{= it.index }}][key]" class="form-control input-sm"
                    id="<?php echo $defaultId; ?>-{{= it.index }}-key">
                        <?php foreach ($keysOptions as $optionValue => $optionLabel): ?>
                            <option value="<?php echo $optionValue ?>" {{? it.key === '<?php echo $optionValue ?>' }}selected{{?}}>
                                <?php echo $optionLabel ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <input type="text" name="<?php echo $inputName; ?>[{{= it.index }}][key]" class="form-control input-sm"
                    id="<?php echo $defaultId; ?>-{{= it.index }}-key" value="{{= it.key || '' }}">
                <?php endif; ?>
            </td>
            <td class="value form-group">
                <input type="text" name="<?php echo $inputName; ?>[{{= it.index }}][value]" class="form-control input-sm"
                id="<?php echo $defaultId; ?>-{{= it.index }}-value" value="{{= it.value || '' }}">
            </td>
            <td class="delete text-center">
                <a href="javascript: void(0)" class="text-danger delete-row" data-toggle="tooltip"
                 title="<?php echo $valueViewer->getDeleteRowButtonLabel() ?>">
                    <i class="glyphicon glyphicon-remove fs16 lh30"></i>
                </a>
            </td>
        </tr>
    </script>
    <div class="form-group">
        <input type="hidden" name="<?php echo $inputName; ?>[]" id="<?php echo $defaultId; ?>" value="">
        <input type="hidden" disabled name="<?php echo $inputName; ?>" id="<?php echo $defaultId; ?>">
    </div>
    <table class="table table-condensed table-bordered table-striped mbn">
        <?php if (!empty($keysLabel) || !empty($valuesLabel)): ?>
        <thead>
            <tr>
                <th><?php echo $keysLabel; ?></th>
                <th><?php echo $valuesLabel; ?></th>
                <th width="60">&nbsp;</th>
            </tr>
        </thead>
        <?php endif ?>
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
        var $rowsContainer = $('#<?php echo $defaultId ?>-rows-container');
        var rowTpl = doT.template($('#<?php echo $defaultId ?>-row-tpl').html());
        var values = <?php echo $valueViewer->getDotJsInsertForValue([], 'array_encode') ?>;
        var maxRows = <?php echo $valueViewer->getMaxValuesCount(); ?>;
        var minRows = <?php echo $valueViewer->getMinValuesCount(); ?>;
        var rowIndex = 0;
        var rowsCount = 0;
        var addRow = function (tplData) {
            if (maxRows > 0 && rowsCount + 1 > maxRows) {
                return false;
            }
            if (!$.isPlainObject(tplData)) {
                tplData = {};
            }
            tplData.index = rowIndex;
            rowIndex++;
            $rowsContainer.append(rowTpl(tplData));
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
            addRow({});
        }
    });
</script>
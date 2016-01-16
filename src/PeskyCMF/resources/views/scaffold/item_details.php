<?php
/**
 * @var \App\Db\BaseDbModel $model
 * @var \PeskyCMF\Scaffold\ItemDetails\ItemDetailsConfig $itemDetailsConfig
 * @var string $translationPrefix
 * @var string $idSuffix
 */
$dataGridId = "scaffold-data-grid-{$idSuffix}";
$fieldConfigs = $itemDetailsConfig->getFields();
$backUrl = route('cmf_items_table', ['table_name' => $model->getTableName()], false);
try {
?>

<script type="text/html" id="item-details-tpl">
    <div class="content-header">
        <h1><?php echo trans("$translationPrefix.item_details.header"); ?></h1>
        <ol class="breadcrumb">
            <li>
                <a href="#" data-nav="back" data-default-url="<?php echo $backUrl; ?>">
                    <i class="fa fa-reply"></i>
                    <?php echo \PeskyCMF\Config\CmfConfig::transBase('.action.back'); ?>
                </a>
            </li>
            <li>
                <a href="#" data-nav="reload">
                    <i class="glyphicon glyphicon-refresh"></i>
                    <?php echo \PeskyCMF\Config\CmfConfig::transBase('.action.reload_page'); ?>
                </a>
            </li>
        </ol>
    </div>
    <div class="content">
        <div class="row"><div class="col-xs-12">
            <div class="box">
                <div class="box-body">
                    <table class="table table-striped table-bordered">
                        <?php foreach ($fieldConfigs as $config) : ?>
                        <tr id="item-details-<?php echo $config->getName(); ?>">
                            <th class="text-nowrap">
                                <?php
                                    if ($config->hasLabel()) {
                                        echo $config->getLabel();
                                    } else {
                                        echo trans("$translationPrefix.item_details.field.{$config->getName()}");
                                    }
                                ?>
                            </th>
                            <td width="80%">
                                <?php
                                    try {
                                        echo $config->render(['translationPrefix' => $translationPrefix]);
                                    } catch (Exception $exc) {
                                        echo '<div>' . $exc->getMessage() . '</div>';
                                        echo '<pre>' . nl2br($exc->getTraceAsString()) . '</pre>';
                                    }
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <div class="box-footer">
                    <div class="row">
                        <div class="col-xs-3">
                            <a class="btn btn-default" href="#" data-nav="back" data-default-url="<?php echo $backUrl; ?>">
                                <?php echo \PeskyCMF\Config\CmfConfig::transBase('.item_details.toolbar.cancel'); ?>
                            </a>
                            <?php if ($itemDetailsConfig->isCreateAllowed()) : ?>
                                <?php
                                    $createUrl = route('cmf_item_add_form', [$model->getTableName()]);
                                ?>
                                <a class="btn btn-primary" href="<?php echo $createUrl; ?>">
                                    <?php echo \PeskyCMF\Config\CmfConfig::transBase('.item_details.toolbar.create'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="col-xs-9 text-right">
                            <?php
                                foreach ($itemDetailsConfig->getToolbarItems() as $toolbarItem) {
                                    echo ' ' . preg_replace('%(:|\%3A)([a-zA-Z0-9_]+)\1%is', '{{= it.$2 }}', $toolbarItem) . ' ';
                                }
                            ?>
                            <?php if ($itemDetailsConfig->isDeleteAllowed()) : ?>
                                <?php
                                    $deleteUrl = str_ireplace(
                                        ':id:',
                                        "{{= it.{$model->getPkColumnName()} }}",
                                        route('cmf_api_delete_item', [$model->getTableName(), ':id:'])
                                    );
                                ?>
                                <a class="btn btn-danger" href="#"
                                data-action="request" data-method="delete" data-url="<?php echo $deleteUrl; ?>"
                                data-confirm="<?php echo \PeskyCMF\Config\CmfConfig::transBase('.action.delete.please_confirm'); ?>">
                                    <?php echo \PeskyCMF\Config\CmfConfig::transBase('.item_details.toolbar.delete'); ?>
                                </a>
                            <?php endif; ?>
                            <?php if ($itemDetailsConfig->isEditAllowed()) : ?>
                                <?php
                                    $editUrl = str_ireplace(
                                        ':id:',
                                        "{{= it.{$model->getPkColumnName()} }}",
                                        route('cmf_item_edit_form', [$model->getTableName(), ':id:'])
                                    );
                                ?>
                                <a class="btn btn-success" href="<?php echo $editUrl; ?>">
                                    <?php echo \PeskyCMF\Config\CmfConfig::transBase('.item_details.toolbar.edit'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div></div>
    </div>
</script>
<?php } catch (Exception $exc) {
    echo $exc->getMessage();
    echo '<pre>' . $exc->getTraceAsString() . '</pre>';
}?>
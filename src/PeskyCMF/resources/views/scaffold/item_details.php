<?php
/**
 * @var \PeskyORM\ORM\TableInterface $table
 * @var \PeskyCMF\Scaffold\ItemDetails\ItemDetailsConfig $itemDetailsConfig
 * @var string $tableNameForRoutes
 * @var string $idSuffix
 */
$backUrl = routeToCmfItemsTable($tableNameForRoutes);
$tabs = $itemDetailsConfig->getTabs();
$hasTabs = count($tabs) > 1 || !empty($tabs[0]['label']);
?>

<?php View::startSection('item-detials-tabsheet') ;?>
    <?php
        $groups = $itemDetailsConfig->getRowsGroups();
        $jsInitiator = '';
        if ($itemDetailsConfig->hasJsInitiator()) {
            $jsInitiator = 'data-initiator="' . addslashes($itemDetailsConfig->getJsInitiator()) . '"';
        }
        $containerId = "scaffold-item-details-{$idSuffix}";
    ?>
    <div id="<?php echo $containerId; ?>" class="item-details-tabsheet-container" <?php echo $jsInitiator; ?>>
        <div class="nav-tabs-custom mn">
            <?php if ($hasTabs): ?>
                <ul class="nav nav-tabs">
                    <?php foreach ($tabs as $idx => $tabInfo): ?>
                        <li class="<?php echo $idx === 0 ? 'active' : '' ?>">
                            <a href="#<?php echo $containerId . '-' . (string)($idx + 1) ?>" data-toggle="tab" role="tab">
                                <?php echo empty($tabInfo['label']) ? '*' : $tabInfo['label']; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <div class="<?php echo $hasTabs ? 'tab-content' : 'box box-primary mn pn' ?>">
            <?php foreach ($tabs as $idx => $tabInfo) : ?>
                <?php $class = $hasTabs ? 'tab-pane' . ($idx === 0 ? ' active' : '') : 'box-body mn pn'; ?>
                <div role="tabpanel" class=" <?php echo $class ?>" id="<?php echo $containerId . '-' . (string)($idx + 1) ?>">
                    <table class="table table-striped table-bordered mn item-details-table br-l-n br-r-n">
                        <?php
                            $class = $hasTabs ? 'tab-pane' . ($idx === 0 ? ' active' : '') : 'box-body';
                            foreach ($tabInfo['groups'] as $groupIndex) {
                                $groupInfo = $groups[$groupIndex];
                                if (empty($groupInfo['keys_for_values']) || !is_array($groupInfo['keys_for_values'])) {
                                    continue;
                                }
                                // group label
                                if (!empty($groupInfo['label'])) {
                                    echo \Swayok\Html\Tag::tr(['class' => 'table-group'])
                                        ->append(
                                            \Swayok\Html\Tag::th([
                                                    'colspan' => 2,
                                                    'class' => 'fw400 fs18 text-center text-primary'
                                                ])
                                                ->setContent($groupInfo['label'])
                                                ->build()
                                        )
                                        ->build() . "\n";
                                }
                                // group values
                                foreach ($groupInfo['keys_for_values'] as $keyForValue) {
                                    try {
                                        $viewer = $itemDetailsConfig->getValueCell($keyForValue);
                                        echo Swayok\Html\Tag::tr(['id' => 'item-details-' . $viewer->getName()])
                                            ->append(
                                                Swayok\Html\Tag::th(['class' => 'text-nowrap'])
                                                    ->setContent($viewer->getLabel())
                                                    ->build()
                                            )
                                            ->append(
                                                \Swayok\Html\Tag::td(['width' => '80%'])
                                                    ->setContent($viewer->render())
                                                    ->build()
                                            )
                                            ->build() . "\n";
                                    } catch (Exception $exc) {
                                        echo '<tr><td colspan="2">';
                                        echo '<div>Key: <b>' . $keyForValue . '</b></div>';
                                        echo '<div>' . htmlspecialchars($exc->getMessage()) . '</div>';
                                        echo '<pre>' . nl2br(htmlspecialchars($exc->getTraceAsString())) . '</pre>';
                                        echo '</td></tr>';
                                    }
                                }
                            }
                        ?>
                    </table>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php View::stopSection(); ?>

<?php View::startSection('item-detials-footer') ;?>
    <div class="row">
        <div class="col-xs-3 text-left">
            {{? it._modal }}
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <?php echo cmfTransGeneral('.form.toolbar.close'); ?>
                </button>
            {{??}}
                <button type="button" class="btn btn-default" data-nav="back" data-default-url="<?php echo $backUrl; ?>">
                    <?php echo cmfTransGeneral('.item_details.toolbar.cancel'); ?>
                </button>
            {{?}}
            <?php if ($itemDetailsConfig->isCreateAllowed()) : ?>
                <a class="btn btn-primary" href="<?php echo routeToCmfItemAddForm($tableNameForRoutes); ?>">
                    <?php echo cmfTransGeneral('.item_details.toolbar.create'); ?>
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
                        "{{= it.{$table->getPkColumnName()} }}",
                        route('cmf_api_delete_item', [$tableNameForRoutes, ':id:'])
                    );
                ?>
                {{? !!it.___delete_allowed }}
                <a class="btn btn-danger" href="#"
                data-action="request" data-method="delete" data-url="<?php echo $deleteUrl; ?>"
                data-confirm="<?php echo cmfTransGeneral('.action.delete.please_confirm'); ?>">
                    <?php echo cmfTransGeneral('.item_details.toolbar.delete'); ?>
                </a>
                {{?}}
            <?php endif; ?>
            <?php if ($itemDetailsConfig->isEditAllowed()) : ?>
                <?php
                    $editUrl = str_ireplace(
                        ':id:',
                        "{{= it.{$table->getPkColumnName()} }}",
                        routeToCmfItemEditForm($tableNameForRoutes, ':id:')
                    );
                ?>
                {{? !!it.___edit_allowed }}
                <a class="btn btn-success" href="<?php echo $editUrl; ?>">
                    <?php echo cmfTransGeneral('.item_details.toolbar.edit'); ?>
                </a>
                {{?}}
            <?php endif; ?>
        </div>
    </div>
<?php View::stopSection(); ?>

<script type="text/html" id="item-details-tpl">
    {{##def.tabsheet:
        <?php echo View::yieldContent('item-detials-tabsheet'); ?>
    #}}
    {{##def.footer:
        <?php echo View::yieldContent('item-detials-footer'); ?>
    #}}
    {{? it._modal }}
        <div class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"
                        aria-label="<?php echo cmfTransGeneral('.form.toolbar.close'); ?>">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title"><?php echo $itemDetailsConfig->translate(null, 'header'); ?></h4>
                    </div>
                    <div class="modal-body pn">
                        {{# def.tabsheet }}
                    </div>
                    <div class="modal-footer">
                        {{# def.footer }}
                    </div>
                </div>
            </div>
        </div>
    {{??}}
        <?php echo view('cmf::ui.default_page_header', [
            'header' => $itemDetailsConfig->translate(null, 'header'),
            'defaultBackUrl' => $backUrl,
        ])->render(); ?>
        <div class="content">
            <div class="row">
                <div class="<?php echo $itemDetailsConfig->getCssClassesForContainer() ?>">
                    <div class="box br-t-n">
                        <div class="box-body pn">
                            {{# def.tabsheet }}
                        </div>
                        <div class="box-footer">
                            {{# def.footer }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    {{?}}
</script>
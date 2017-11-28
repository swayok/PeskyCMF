<?php
/**
 * @var \PeskyORM\ORM\TableInterface $table
 * @var \PeskyCMF\Scaffold\ItemDetails\ItemDetailsConfig $itemDetailsConfig
 * @var string $tableNameForRoutes
 * @var string $idSuffix
 */
$pageUrl = routeToCmfItemDetails($tableNameForRoutes, '{{= it.___pk_value }}');
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

        <div class="<?php echo $hasTabs ? 'tab-content' : 'box {{? it.__modal }} br-t-n {{??}} box-primary {{?}} mn pn' ?>">
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
                                        $label = $viewer->getLabel();
                                        $tr = Swayok\Html\Tag::tr(['id' => 'item-details-' . $viewer->getName()]);
                                        $contentTd = \Swayok\Html\Tag::td()
                                            ->setContent($viewer->render())
                                            ->setAttributes(
                                                array_merge($viewer->getValueContainerAttributes(), ['width' => '80%'])
                                            );

                                        if (trim($label) === '') {
                                            // do not display label column
                                            $contentTd
                                                ->setAttribute('colspan', '2')
                                                ->setAttribute('width', '100%');
                                        } else {
                                            $tr->append(
                                                Swayok\Html\Tag::th(['class' => 'text-nowrap'])
                                                    ->setContent($viewer->getLabel())
                                                    ->build()
                                            );
                                        }
                                        echo $tr->append($contentTd->build())->build() . "\n";
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
    <div class="row toolbar">
        <div class="{{? it.__modal }} col-xs-4 {{??}} col-xs-3 {{?}} text-left toolbar-left">
            {{? it.__modal }}
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <?php echo $itemDetailsConfig->translateGeneral('toolbar.close'); ?>
                </button>
            {{??}}
                <button type="button" class="btn btn-default" data-nav="back" data-default-url="<?php echo $backUrl; ?>">
                    <?php echo $itemDetailsConfig->translateGeneral('toolbar.cancel'); ?>
                </button>
            {{?}}
            <?php if ($itemDetailsConfig->isCreateAllowed()) : ?>
                <a class="btn btn-primary" href="<?php echo routeToCmfItemAddForm($tableNameForRoutes); ?>">
                    <?php echo $itemDetailsConfig->translateGeneral('toolbar.create'); ?>
                </a>
            <?php endif; ?>
        </div>
        <div class="{{? it.__modal }} col-xs-8 {{??}} col-xs-9 {{?}} text-right toolbar-right">
            <?php echo implode(' ', $itemDetailsConfig->getToolbarItems()); ?>
            <?php if ($itemDetailsConfig->isDeleteAllowed()) : ?>
                {{? !!it.___delete_allowed }}
                    <a class="btn btn-danger" href="#"
                       data-action="request" data-method="delete"
                       data-url="<?php echo routeToCmfItemDelete($tableNameForRoutes, '{{= it.___pk_value }}'); ?>"
                       data-confirm="<?php echo $itemDetailsConfig->translateGeneral('message.delete_item_confirm'); ?>"
                       data-on-success="CmfRoutingHelpers.closeCurrentModalAndReloadDataGrid">
                        <?php echo $itemDetailsConfig->translateGeneral('toolbar.delete'); ?>
                    </a>
                {{?}}
            <?php endif; ?>
            <?php if ($itemDetailsConfig->isEditAllowed()) : ?>
                {{? !!it.___edit_allowed }}
                    <a class="btn btn-success"
                       href="<?php echo routeToCmfItemEditForm($tableNameForRoutes, '{{= it.___pk_value }}'); ?>">
                        <?php echo $itemDetailsConfig->translateGeneral('toolbar.edit'); ?>
                    </a>
                {{?}}
            <?php endif; ?>
        </div>
    </div>
<?php View::stopSection(); ?>

<script type="text/html" id="item-details-tpl">
    {{##def.tabsheet = function () {
        return Base64.decode('<?php echo base64_encode(View::yieldContent('item-detials-tabsheet')); ?>');
    }
    #}}

    {{##def.footer = function () {
        return Base64.decode('<?php echo base64_encode(View::yieldContent('item-detials-footer')); ?>');
    }
    #}}
    {{? it.__modal }}
        <div class="modal fade" tabindex="-1" role="dialog" data-pk-name="<?php echo $table::getPkColumnName() ?>">
            <div class="modal-dialog <?php echo $itemDetailsConfig->getWidth() >= 60 ? 'modal-lg' : 'modal-md' ?>">
                <div class="modal-content item-details-modal-content">
                    <div class="modal-header pv10">
                        <div class="box-tools pull-right">
                            <a class="btn btn-box-tool fs13 va-t ptn mt5"
                               href="<?php echo $pageUrl ?>"
                               data-toggle="tooltip" title="<?php echo $itemDetailsConfig->translateGeneral('modal.reload'); ?>">
                                <i class="glyphicon glyphicon-refresh"></i>
                            </a>
                            <a class="btn btn-box-tool fs13 va-t ptn mt5" target="_blank"
                               href="<?php echo $pageUrl ?>"
                               data-toggle="tooltip" title="<?php echo $itemDetailsConfig->translateGeneral('modal.open_in_new_tab'); ?>">
                                <i class="glyphicon glyphicon-share"></i>
                            </a>
                            <button type="button" data-dismiss="modal" class="btn btn-box-tool va-t pbn ptn mt5"
                                    data-toggle="tooltip" title="<?php echo $itemDetailsConfig->translateGeneral('modal.close'); ?>">
                                <span class="fs24 lh15">&times;</span>
                            </button>
                        </div>
                        <h4 class="modal-title lh30"><?php echo $itemDetailsConfig->translate(null, 'header'); ?></h4>
                    </div>
                    <div class="modal-body pn">
                        <button type="button" class="prev-item" disabled
                                data-toggle="tooltip" title="<?php echo $itemDetailsConfig->translateGeneral('previous_item') ?>">
                            <i class="glyphicon glyphicon-arrow-left"></i>
                        </button>
                        {{# def.tabsheet() }}
                        <button type="button" class="next-item" disabled
                                data-toggle="tooltip" title="<?php echo $itemDetailsConfig->translateGeneral('next_item') ?>">
                            <i class="glyphicon glyphicon-arrow-right"></i>
                        </button>
                    </div>
                    <div class="modal-footer">
                        {{# def.footer() }}
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
                            {{# def.tabsheet() }}
                        </div>
                        <div class="box-footer">
                            {{# def.footer() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    {{?}}
</script>
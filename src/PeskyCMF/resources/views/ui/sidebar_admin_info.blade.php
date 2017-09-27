<div id="user-panel">
    <div class="info">

    </div>
    <div class="actions">
        <?php if (\Gate::allows('resource.details', ['cmf_profile', \PeskyCMF\Config\CmfConfig::getPrimary()->getUser()])): ?>
            <a href="{{ cmfRoute('cmf_profile', [], false) }}">
                <i class="fa fa-fw fa-user"></i>{{ cmfTransCustom('.user.profile_label') }}
            </a>
        <?php endif; ?>
        <a href="{{ \PeskyCMF\Config\CmfConfig::getPrimary()->logout_page_url() }}">
            <i class="fa fa-fw fa-sign-out"></i>{{ cmfTransCustom('.user.logout_label') }}
        </a>
    </div>
</div>

<script type="text/html" id="user-panel-tpl">
    <div class="user-name">
        @{{? it.name && it.name.length }}@{{= it.name }}@{{??}}<?php echo '{{= it.role || "' . cmfTransCustom('.admins.role.admin') . '" }}' ?>@{{?}}
    </div>
    <div class="user-{{ \PeskyCMF\Config\CmfConfig::getPrimary()->user_login_column() }}">
        <?php echo '{{= it.' . \PeskyCMF\Config\CmfConfig::getPrimary()->user_login_column() . ' }}'; ?>
    </div>
</script>
<?php
declare(strict_types=1);
/**
 * @var \PeskyCMF\Config\CmfConfig $cmfConfig
 */
?>
<div id="user-panel">
    <div class="info">

    </div>
    <div class="actions">
        <?php if ($cmfConfig->getAuthModule()->getAuthGate()->allows('resource.details', ['cmf_profile', $cmfConfig->getUser()])): ?>
            <a href="{{ $cmfConfig->getAuthModule()->getProfilePageUrl(false) }}">
                <i class="fa fa-fw fa-user"></i>{{ $cmfConfig->transCustom('user.profile_label') }}
            </a>
        <?php endif; ?>
        <a href="{{ $cmfConfig->getAuthModule()->getLogoutPageUrl(false) }}">
            <i class="fa fa-fw fa-sign-out"></i>{{ $cmfConfig->transCustom('user.logout_label') }}
        </a>
    </div>
</div>

<script type="text/html" id="user-panel-tpl">
    <div class="user-name">
        @{{? it.name && it.name.length }}@{{= it.name }}@{{??}}<?php echo '{{= it.role || "' . $cmfConfig->transCustom('admins.role.admin') . '" }}' ?>@{{?}}
    </div>
    <div class="user-{{ $cmfConfig->getAuthModule()->getUserLoginColumnName() }}">
        <?php echo '{{= it.' . $cmfConfig->getAuthModule()->getUserLoginColumnName() . ' }}'; ?>
    </div>
</script>
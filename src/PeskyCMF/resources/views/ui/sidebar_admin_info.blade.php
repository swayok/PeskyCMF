<div id="user-panel">
    <div class="info">

    </div>
    <div class="actions">
        <a href="{{ route('cmf_profile', [], false) }}">
            <i class="fa fa-fw fa-user"></i>{{ cmfTransCustom('.user.profile_label') }}
        </a>
        <a href="{{ route(\PeskyCMF\Config\CmfConfig::getInstance()->logout_route(), [], false) }}">
            <i class="fa fa-fw fa-sign-out"></i>{{ cmfTransCustom('.user.logout_label') }}
        </a>
    </div>
</div>

<script type="text/html" id="user-panel-tpl">
    <div class="user-name">@{{? it.name.length }}@{{= it.name }}@{{??}}@{{= it.role }}@{{?}}</div>
    <div class="user-{{ \PeskyCMF\Config\CmfConfig::getInstance()->user_login_column() }}">
        <?php echo '{{= it.' . \PeskyCMF\Config\CmfConfig::getInstance()->user_login_column() . ' }}'; ?>
    </div>
</script>
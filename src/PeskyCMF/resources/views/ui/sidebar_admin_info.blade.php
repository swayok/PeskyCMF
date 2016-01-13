<div id="user-panel">
    <div class="info">

    </div>
    <div class="actions">
        <a href="{{ route('cmf_profile', [], false) }}"><i class="fa fa-fw fa-user"></i>{{ trans('cmf::cmf.user.profile_label') }}</a>
        <a href="{{ route(\PeskyCMF\Config\CmfConfig::getInstance()->logout_route(), [], false) }}"><i class="fa fa-fw fa-sign-out"></i>{{ trans('cmf::cmf.user.logout_label') }}</a>
    </div>
</div>

<script type="text/html" id="user-panel-tpl">
    <div class="name">@{{? it.name.length }}@{{= it.name }}@{{??}}@{{= it.role }}@{{?}}</div>
    <div class="email">@{{= it.email }}</div>
</script>
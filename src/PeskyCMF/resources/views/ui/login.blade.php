<?php
$dictionary = \PeskyCMF\Config\CmfConfig::getInstance()->custom_dictionary_name();
$loginInputName = \PeskyCMF\Config\CmfConfig::getInstance()->user_login_column();
/** @var \PeskyCMF\Http\Controllers\CmfGeneralController $generalController */
$generalController = \PeskyCMF\Config\CmfConfig::getInstance()->cmf_general_controller_class();
?>
<div class="login-box">
    <div class="login-logo">
        {!! \PeskyCMF\Config\CmfConfig::getInstance()->login_logo() !!}
        <b>{!! trans("{$dictionary}.login_form.header") !!}</b>
    </div>
    <div class="login-box-body" id="login-form-container">
        <form action="{{ route(\PeskyCMF\Config\CmfConfig::getInstance()->login_route(), [], false) }}" method="post" id="login-form">
            <input type="hidden" name="{{ $generalController::BACK_URL_PARAM }}" value="{{ \PeskyCMF\Config\CmfConfig::getInstance()->home_page_url() }}">
            <div class="form-group has-feedback">
                <input type="{{ $loginInputName === 'email' ? 'email' : 'text' }}" name="{{ $loginInputName }}" required
                    class="form-control" placeholder="{{ trans("{$dictionary}.login_form.{$loginInputName}_label") }}">
                <span class="glyphicon glyphicon-{{ $loginInputName === 'email' ? 'envelope' : 'user' }} form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input type="password" name="password" required class="form-control"
                    placeholder="{{ trans("{$dictionary}.login_form.password_label") }}">
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
            <div class="row login-submit">
                <div class="col-xs-8 forgot-password">
                    @if (\PeskyCMF\Config\CmfConfig::getInstance()->is_password_restore_allowed())
                        <a href="{{ route('cmf_forgot_password') }}">{{ trans("{$dictionary}.login_form.forgot_password_label") }}</a>
                    @endif
                </div>
                <div class="col-xs-4">
                    <button type="submit" class="btn btn-primary btn-block btn-flat">
                        {{ trans("{$dictionary}.login_form.button_label") }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script type="application/javascript">
    $(function () {
        var queryArgName = '{{ $generalController::BACK_URL_PARAM }}';
        if (window.adminApp.activeRequest && window.adminApp.activeRequest.query && window.adminApp.activeRequest.query[queryArgName]) {
            var backUrl = String(window.adminApp.activeRequest.query[queryArgName]);
            if (backUrl.length > 1 && (backUrl[0] === '/' || (backUrl[0] === 'h' && backUrl[6] === '/'))) {
                $('input[name="' + queryArgName + '"]').val(backUrl);
            }
        }
    });
</script>

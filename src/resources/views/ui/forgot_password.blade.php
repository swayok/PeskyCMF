<?php
/**
 * @var \PeskyCMF\Auth\CmfAuthModule $authModule
 * @var \PeskyCMF\Config\CmfConfig $cmfConfig
 */

?>
<script type="application/javascript">
    Utils.requireFiles(['{{ $cmfConfig->recaptcha_script() }}']);
</script>
<div class="login-box">
    <div class="login-logo">
        {!! $authModule->getLoginPageLogo() !!}
        <div><b>{!! $cmfConfig::transCustom('.forgot_password.header') !!}</b></div>
    </div>
    <div
        class="login-box-body"
        id="forgot-password-form-container"
    >
        <form
            action="{{ cmfRoute('cmf_forgot_password', [], false, $cmfConfig) }}"
            method="post"
            id="forgot-password-form"
        >
            <div class="form-group has-feedback">
                <input
                    type="email"
                    name="email"
                    required
                    class="form-control"
                    placeholder="{{ $cmfConfig::transCustom('.forgot_password.email_label') }}"
                >
                <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
            </div>
            @if ($authModule->isRecaptchaAvailable())
                <div class="form-group text-center">
                    <div
                        class="g-recaptcha ib"
                        data-sitekey="{{ $cmfConfig->recaptcha_public_key() }}"
                    ></div>
                </div>
            @endif
            <div class="login-submit text-right">
                <a
                    class="btn btn-default btn-flat pull-left"
                    href="{{ $authModule->getLoginPageUrl() }}"
                >
                    {{ $cmfConfig::transGeneral('.action.back') }}
                </a>
                <button
                    type="submit"
                    class="btn btn-primary btn-flat"
                >
                    {{ $cmfConfig::transCustom('.forgot_password.button_label') }}
                </button>
            </div>
        </form>
    </div>
</div>

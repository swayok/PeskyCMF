<?php
/**
 * @var \PeskyCMF\Auth\CmfAuthModule $authModule
 * @var \PeskyCMF\Config\CmfConfig $cmfConfig
 */
$loginInputName = $authModule->getUserLoginColumnName();
$usersTableStructure = $authModule->getUsersTable()->getTableStructure();
?>
<script type="application/javascript">
    Utils.requireFiles(['{{ $cmfConfig::recaptcha_script() }}']);
</script>
<div class="register-box">
    <div class="register-logo">
        {!! $authModule->getLoginPageLogo() !!}
        <div><b>{!! $cmfConfig::transCustom('.registration_form.header') !!}</b></div>
    </div>
    <div class="register-box-body" id="login-form-container">
        <form action="{{ cmfRoute('cmf_register') }}" method="post" id="registration-form">
            @if ($loginInputName !== 'email')
                <div class="form-group">
                    <label class="control-label" for="{{ $loginInputName }}">
                        {{ $cmfConfig::transCustom(".registration_form.{$loginInputName}_label") }}*
                    </label>
                    <input type="text" name="{{ $loginInputName }}" required class="form-control" id="{{ $loginInputName }}-input">
                </div>
            @endif
            @if ($usersTableStructure::hasColumn('name'))
                <div class="form-group">
                    <label class="control-label" for="name-input">
                        {{ $cmfConfig::transCustom(".registration_form.name_label") }}
                    </label>
                    <input type="text" name="name" class="form-control" id="name-input">
                </div>
            @endif
            <div class="form-group">
                <label class="control-label" for="email-input">
                    {{ $cmfConfig::transCustom(".registration_form.email_label") }}@if ($loginInputName === 'email')* @endif
                </label>
                <div class="has-feedback">
                    <input type="email" name="email" id="email-input" @if ($loginInputName === 'email') required @endif class="form-control">
                    <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label" for="password-input">
                    {{ $cmfConfig::transCustom('.registration_form.password_label') }}*
                </label>
                <div class="has-feedback">
                    <input type="password" name="password" required class="form-control" id="password-input">
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label" for="password-confirmation-input">
                    {{ $cmfConfig::transCustom('.registration_form.password_confirmation_label') }}*
                </label>
                <div class="has-feedback">
                    <input type="password" name="password_confirmation" required class="form-control" id="password-confirmation-input">
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                </div>
            </div>
            @if ($authModule->isRecaptchaAvailable())
                <script src="https://www.google.com/recaptcha/api.js?hl={{ $cmfConfig::getShortLocale() }}" async defer></script>
                <div class="form-group text-center">
                    <div class="g-recaptcha ib" data-sitekey="{{ $cmfConfig::recaptcha_public_key() }}"></div>
                </div>
            @endif
            <div class="login-submit">
                <button type="submit" class="btn btn-primary btn-block btn-flat">
                    {{ $cmfConfig::transCustom('.registration_form.button_label') }}
                </button>
            </div>
        </form>
    </div>
    <div class="text-center mt20 register">
        <a href="{{ cmfRoute('cmf_login', [], false, $cmfConfig) }}">{{ $cmfConfig::transCustom('.registration_form.login_to_account_label') }}</a>
    </div>
</div>

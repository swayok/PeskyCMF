<?php
declare(strict_types=1);
/**
 * @var \PeskyCMF\Auth\CmfAuthModule $authModule
 */
$cmfConfig = $authModule->getCmfConfig();
$loginInputName = $authModule->getUserLoginColumnName();
?>
<div class="login-box">
    <div class="login-logo">
        {!! $authModule->getLoginPageLogo() !!}
        <div><b>{!! $cmfConfig->transCustom('.login_form.header') !!}</b></div>
    </div>
    <div
        class="login-box-body"
        id="login-form-container"
    >
        <form
            action="{{ $authModule->getLoginPageUrl() }}"
            method="post"
            id="login-form"
        >
            <div class="form-group has-feedback">
                <input
                    type="{{ $loginInputName === 'email' ? 'email' : 'text' }}"
                    name="{{ $loginInputName }}"
                    required
                    class="form-control"
                    placeholder="{{ $cmfConfig->transCustom(".login_form.{$loginInputName}_label") }}"
                >
                <span class="glyphicon glyphicon-{{ $loginInputName === 'email' ? 'envelope' : 'user' }} form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input
                    type="password"
                    name="password"
                    required
                    class="form-control"
                    placeholder="{{ $cmfConfig->transCustom('.login_form.password_label') }}"
                >
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
            <div class="row login-submit">
                @if ($authModule->isPasswordRestoreAllowed())
                    <div class="col-xs-8 forgot-password">
                        <a href="{{ cmfRoute('cmf_forgot_password', [], false, $cmfConfig) }}">{{ $cmfConfig->transCustom('.login_form.forgot_password_label') }}</a>
                    </div>
                @endif
                <div class="col-xs-4">
                    <button
                        type="submit"
                        class="btn btn-primary btn-block btn-flat"
                    >
                        {{ $cmfConfig->transCustom('.login_form.button_label') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
    @if ($authModule->isRegistrationAllowed())
        <div class="text-center mt20 register">
            <a href="{{ cmfRoute('cmf_register', [], false, $cmfConfig) }}">{{ $cmfConfig->transCustom('.login_form.registration_label') }}</a>
        </div>
    @endif
</div>

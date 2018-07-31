<?php
/**
 * @var \PeskyCMF\Auth\CmfAuthModule $authModule
 */
$cmfConfig = $authModule->getCmfConfig();
$loginInputName = $authModule->getUserLoginColumnName();
$usersTableStructure = $authModule->getUsersTable()->getTableStructure();
?>
<div class="register-box">
    <div class="register-logo">
        {!! $authModule->getLoginPageLogo() !!}
        <div><b>{!! $cmfConfig::transCustom('.registration_form.header') !!}</b></div>
    </div>
    <div class="register-box-body" id="login-form-container">
        <form action="{{ cmfRoute('cmf_register') }}" method="post" id="registration-form">
            @if ($loginInputName !== 'email')
                <div class="form-group">
                    <input type="email" name="{{ $loginInputName }}" required
                        class="form-control" placeholder="{{ $cmfConfig::transCustom(".registration_form.{$loginInputName}_label") }}*">
                </div>
            @endif
            @if ($usersTableStructure::hasColumn('name'))
                <div class="form-group">
                    <input type="email" name="name"
                        class="form-control" placeholder="{{ $cmfConfig::transCustom(".registration_form.name_label") }}">
                </div>
            @endif
            <div class="form-group has-feedback">
                <input type="email" name="email" @if ($loginInputName === 'email') required @endif
                    class="form-control" placeholder="{{ $cmfConfig::transCustom(".registration_form.email_label") }}@if ($loginInputName === 'email')* @endif">
                <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input type="password" name="password" required class="form-control"
                    placeholder="{{ $cmfConfig::transCustom('.registration_form.password_label') }}*">
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input type="password" name="password_confirmation" required class="form-control"
                    placeholder="{{ $cmfConfig::transCustom('.registration_form.password_confirmation_label') }}*">
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
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

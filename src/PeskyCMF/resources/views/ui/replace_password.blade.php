<?php
/**
 * @var \PeskyCMF\Auth\CmfAuthModule $authModule
 * @var string $accessKey
 * @var int|string $userId
 * @var string $userLogin
 */
$cmfConfig = $authModule->getCmfConfig();
?>
<div class="login-box">
    <div class="login-logo">
        {!! $authModule->getLoginPageLogo() !!}
        <div><b>{!! $cmfConfig::transCustom('.replace_password.header') !!}</b></div>
    </div>
    <div class="login-box-body" id="replace-password-form-container">
        <form action="{{ cmfRoute('cmf_replace_password', [$accessKey], false, $cmfConfig) }}" method="post" id="replace-password-form">
            <input type="hidden" name="_method" value="PUT">
            <input type="hidden" name="id" value="{{ $userId }}">
            <div class="form-group text-center fs16 fw400">
                {{ $userLogin }}
            </div>
            <div class="form-group has-feedback">
                <input type="password" name="password" required class="form-control"
                    placeholder="{{ $cmfConfig::transCustom('.replace_password.password_label') }}">
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input type="password" name="password_confirm" required class="form-control"
                    placeholder="{{ $cmfConfig::transCustom('.replace_password.password_confirm_label') }}">
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
            <div class="login-submit text-right">
                <button type="submit" class="btn btn-primary btn-flat">
                    {{ $cmfConfig::transCustom('.replace_password.button_label') }}
                </button>
            </div>
        </form>
    </div>
</div>

<?php
/**
 * @var $accessKey
 * @var $userId
 */
?>
<div class="login-box">
    <div class="login-logo">
        {!! \PeskyCMF\Config\CmfConfig::getPrimary()->login_logo() !!}
        <b>{!! cmfTransCustom('.replace_password.header') !!}</b>
    </div>
    <div class="login-box-body" id="replace-password-form-container">
        <form action="{{ cmfRoute('cmf_replace_password', [$accessKey], false) }}" method="post" id="replace-password-form">
            <input type="hidden" name="_method" value="PUT">
            <input type="hidden" name="id" value="{{ $userId }}">
            <div class="form-group has-feedback">
                <input type="password" name="password" required class="form-control"
                    placeholder="{{ cmfTransCustom('.replace_password.password_label') }}">
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input type="password" name="password_confirm" required class="form-control"
                    placeholder="{{ cmfTransCustom('.replace_password.password_confirm_label') }}">
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
            <div class="login-submit text-right">
                <button type="submit" class="btn btn-primary btn-flat">
                    {{ cmfTransCustom('.replace_password.button_label') }}
                </button>
            </div>
        </form>
    </div>
</div>

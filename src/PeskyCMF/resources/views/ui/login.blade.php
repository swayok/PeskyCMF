<?php
$loginInputName = cmfConfig()->user_login_column();
?>
<div class="login-box">
    <div class="login-logo">
        {!! cmfConfig()->login_logo() !!}
        <div><b>{!! cmfTransCustom('.login_form.header') !!}</b></div>
    </div>
    <div class="login-box-body" id="login-form-container">
        <form action="{{ cmfConfig()->login_page_url() }}" method="post" id="login-form">
            <div class="form-group has-feedback">
                <input type="{{ $loginInputName === 'email' ? 'email' : 'text' }}" name="{{ $loginInputName }}" required
                    class="form-control" placeholder="{{ cmfTransCustom(".login_form.{$loginInputName}_label") }}">
                <span class="glyphicon glyphicon-{{ $loginInputName === 'email' ? 'envelope' : 'user' }} form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input type="password" name="password" required class="form-control"
                    placeholder="{{ cmfTransCustom('.login_form.password_label') }}">
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
            <div class="row login-submit">
                <div class="col-xs-8 forgot-password">
                    @if (cmfConfig()->is_password_restore_allowed())
                        <a href="{{ cmfRoute('cmf_forgot_password') }}">{{ cmfTransCustom('.login_form.forgot_password_label') }}</a>
                    @endif
                </div>
                <div class="col-xs-4">
                    <button type="submit" class="btn btn-primary btn-block btn-flat">
                        {{ cmfTransCustom('.login_form.button_label') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

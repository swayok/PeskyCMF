<div class="login-box">
    <div class="login-logo">
        {!! \PeskyCMF\Config\CmfConfig::getInstance()->login_logo() !!}
        <b>{!! cmfTransCustom('.forgot_password.header') !!}</b>
    </div>
    <div class="login-box-body" id="forgot-password-form-container">
        <form action="{{ route('cmf_forgot_password', [], false) }}" method="post" id="forgot-password-form">
            <div class="form-group has-feedback">
                <input type="email" name="email" required class="form-control"
                    placeholder="{{ cmfTransCustom('.forgot_password.email_label') }}">
                <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
            </div>
            <div class="login-submit text-right">
                <button type="submit" class="btn btn-primary btn-flat">
                    {{ cmfTransCustom('.forgot_password.button_label') }}
                </button>
            </div>
        </form>
    </div>
</div>

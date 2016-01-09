<div class="login-box">
    <div class="login-logo">
        {!! \PeskyCMF\Config\CmfConfig::getInstance()->login_logo() !!}
        <b>{!! trans('admin_area.login_form.header') !!}</b>
    </div>
    <div class="login-box-body" id="login-form-container">
        <form action="{{ route(\PeskyCMF\Config\CmfConfig::getInstance()->login_route(), [], false) }}" method="post" id="login-form">
            <div class="form-group has-feedback">
                <input type="email" name="email" required class="form-control" placeholder="{{ trans('cmf::cmf.login_form.email_label') }}">
                <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input type="password" name="password" required class="form-control" placeholder="{{ trans('cmf::cmf.login_form.password_label') }}">
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
            <div class="row login-submit">
                <div class="col-xs-8 forgot-password">
                    <a href="#">{{ trans('cmf::cmf.login_form.forgot_password_label') }}</a>
                </div>
                <div class="col-xs-4">
                    <button type="submit" class="btn btn-primary btn-block btn-flat">{{ trans('cmf::cmf.login_form.button_label') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

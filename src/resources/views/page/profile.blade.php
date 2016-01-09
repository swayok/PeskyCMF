<?php
/**
 * @var \App\Db\Admin\Admin $admin
 */
?>
<div class="content-header">
    <h1>
        {{ trans('cmf::cmf.page.profile.header') }}
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="#" data-nav="back" data-default-url="{{ route('cmf_start_page') }}">
                <i class="glyphicon fa fa-reply"></i>
                {{ trans('cmf::cmf.action.back') }}
            </a>
        </li>
        <li>
            <a href="#" data-nav="reload">
                <i class="glyphicon glyphicon-refresh"></i>
                {{ trans('cmf::cmf.action.reload_page') }}
            </a>
        </li>
    </ol>
</div>

<div class="content">
    <div class="row"><div class="col-xs-6 col-xs-offset-3">
        <div class="box box-primary">
            <form role="form" method="post" action="{{ route('cmf_profile', [], false) }}" id="admin-profile-form">
                <input type="hidden" name="_method" value="PUT">
                <!-- disable chrome email & password autofill -->
                <input type="email" class="hidden">
                <input type="password" class="hidden">
                <!-- end of autofill disabler -->
                <div class="box-body">
                    <div class="form-group">
                        <label for="email-input">{{ trans('cmf::cmf.page.profile.input.email') }}*</label>
                        <input class="form-control" value="{{ $admin->email }}" name="email" id="email-input" type="email" required="required">
                    </div>
                    <div class="form-group">
                        <label for="new-password-input">{{ trans('cmf::cmf.page.profile.input.new_password') }}</label>
                        <input class="form-control" value="" name="new_password" id="new-password-input" type="password" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label for="name-input">{{ trans('cmf::cmf.page.profile.input.name') }}</label>
                        <input class="form-control" value="{{ $admin->name }}" name="name" id="name-input" type="text">
                    </div>
                    <div class="form-group">
                        <label for="language-input">{{ trans('cmf::cmf.page.profile.input.language') }}</label>
                        <select class="form-control" data-value=" {{ $admin->language }}" name="language" id="language-input" required="required">
                            @foreach(\PeskyCMF\Config\CmfConfig::getInstance()->locales() as $lang)
                                <option value="{{ $lang }}">{{ trans('cmf::cmf.language.' . $lang) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="old-password-input">{{ trans('cmf::cmf.page.profile.input.old_password') }}*</label>
                        <input class="form-control" value="" name="old_password" id="old-password-input" type="password" autocomplete="off" required="required">
                    </div>
                </div>
                <div class="box-footer">
                    <div class="row">
                        <div class="col-xs-6">
                            <a class="btn btn-default" href="#" data-nav="back" data-default-url="{{ route('cmf_start_page') }}">
                                {{ trans('cmf::cmf.form.toolbar.cancel') }}
                            </a>
                        </div>
                        <div class="col-xs-6">
                            <button type="submit" class="btn btn-success pull-right">
                                {{ trans('cmf::cmf.form.toolbar.submit') }}
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div></div>
</div>

<script type="application/javascript">
    FormHelper.initForm('#admin-profile-form', '#admin-profile-form');
</script>
<?php
/**
 * @var \App\Db\Admin\Admin $admin
 */
?>
<div class="content-header">
    <h1>
        {{ \PeskyCMF\Config\CmfConfig::transCustom('.page.profile.header') }}
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="#" data-nav="back" data-default-url="{{ route('cmf_start_page') }}">
                <i class="glyphicon fa fa-reply"></i>
                {{ \PeskyCMF\Config\CmfConfig::transBase('.action.back') }}
            </a>
        </li>
        <li>
            <a href="#" data-nav="reload">
                <i class="glyphicon glyphicon-refresh"></i>
                {{ \PeskyCMF\Config\CmfConfig::transBase('.action.reload_page') }}
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
                <input type="text" name="login" class="hidden" formnovalidate>
                <input type="email" class="hidden" formnovalidate value="test@test.com">
                <input type="password" class="hidden" formnovalidate>
                <!-- end of autofill disabler -->
                <div class="box-body">
                    <?php $loginColumn = \PeskyCMF\Config\CmfConfig::getInstance()->user_login_column(); ?>
                    @if ($loginColumn !== 'email')
                        <div class="form-group">
                            <label for="{{ $loginColumn }}-input">{{ \PeskyCMF\Config\CmfConfig::transCustom('.page.profile.input.' . $loginColumn) }}*</label>
                            <input class="form-control" value="{{ $admin->$loginColumn }}" name="{{ $loginColumn }}" id="{{ $loginColumn }}-input" type="text" required="required">
                        </div>
                    @endif
                    @if ($admin->_hasField('email'))
                        <?php $emailRequired = $admin->_getField('email')->isRequiredOnAnyAction(); ?>
                        <div class="form-group">
                            <label for="email-input">{{ \PeskyCMF\Config\CmfConfig::transCustom('.page.profile.input.email') . ($emailRequired ? '*' : '') }}</label>
                            <input class="form-control" value="{{ $admin->email }}" name="email" id="email-input" type="email" @if($emailRequired) required="required" @endif>
                        </div>
                    @endif
                    <div class="form-group">
                        <label for="new-password-input">{{ \PeskyCMF\Config\CmfConfig::transCustom('.page.profile.input.new_password') }}</label>
                        <input class="form-control" value="" name="new_password" id="new-password-input" type="password" autocomplete="off">
                    </div>
                    @if ($admin->_hasField('name'))
                        <div class="form-group">
                            <label for="name-input">{{ \PeskyCMF\Config\CmfConfig::transCustom('.page.profile.input.name') }}</label>
                            <input class="form-control" value="{{ $admin->name }}" name="name" id="name-input" type="text">
                        </div>
                    @endif
                    @if ($admin->_hasField('language'))
                        <div class="form-group">
                            <label for="language-input">{{ \PeskyCMF\Config\CmfConfig::transCustom('.page.profile.input.language') }}</label>
                            <select class="form-control" data-value=" {{ $admin->language }}" name="language" id="language-input" required="required">
                                @foreach(\PeskyCMF\Config\CmfConfig::getInstance()->locales() as $lang)
                                    <option value="{{ $lang }}">{{ \PeskyCMF\Config\CmfConfig::transCustom('.language.' . $lang) }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    @if ($admin->_hasField('timezone'))
                        <div class="form-group">
                            <label for="timezone-input">{{ \PeskyCMF\Config\CmfConfig::transCustom('.page.profile.input.timezone') }}</label>
                            <select class="form-control" data-value="{{ $admin->timezone }}" name="timezone" id="timezone-input" required="required">
                                @foreach(\PeskyCMF\Db\CmfDbModel::getTimezonesList(true) as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="form-group">
                        <label for="old-password-input">{{ \PeskyCMF\Config\CmfConfig::transCustom('.page.profile.input.old_password') }}*</label>
                        <input class="form-control" value="" name="old_password" id="old-password-input" type="password" autocomplete="off" required="required">
                    </div>
                </div>
                <div class="box-footer">
                    <div class="row">
                        <div class="col-xs-6">
                            <a class="btn btn-default" href="#" data-nav="back" data-default-url="{{ route('cmf_start_page') }}">
                                {{ \PeskyCMF\Config\CmfConfig::transBase('.form.toolbar.cancel') }}
                            </a>
                        </div>
                        <div class="col-xs-6">
                            <button type="submit" class="btn btn-success pull-right">
                                {{ \PeskyCMF\Config\CmfConfig::transBase('.form.toolbar.submit') }}
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
    $('#admin-profile-form')
        .find('#timezone-input, #language-input')
        .each(function () {
            $(this).val($(this).attr('data-value'));
        })
        .filter('#timezone-input')
        .selectpicker({
            liveSearch: true,
            liveSearchPlaceholder: "{{ \PeskyCMF\Config\CmfConfig::transCustom('.page.profile.input.timezone_search') }}"
        });
</script>
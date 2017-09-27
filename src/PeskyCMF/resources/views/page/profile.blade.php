<?php
/**
 * @var \PeskyCMF\Db\Admins\CmfAdmin|\PeskyORM\ORM\RecordInterface|\Illuminate\Contracts\Auth\Authenticatable $admin
 */
?>
<div class="content-header">
    <h1>
        {{ cmfTransCustom('.page.profile.header') }}
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="#" data-nav="back" data-default-url="{{ \PeskyCMF\Config\CmfConfig::getPrimary()->home_page_url() }}">
                <i class="glyphicon fa fa-reply"></i>
                {{ cmfTransGeneral('.action.back') }}
            </a>
        </li>
        <li>
            <a href="#" data-nav="reload">
                <i class="glyphicon glyphicon-refresh"></i>
                {{ cmfTransGeneral('.action.reload_page') }}
            </a>
        </li>
    </ol>
</div>

<div class="content">
    <div class="row"><div class="col-xs-6 col-xs-offset-3">
        <div class="box box-primary">
            <?php $canSubmit = \Gate::allows('resource.update', ['cmf_profile', \PeskyCMF\Config\CmfConfig::getPrimary()->getUser()]) ?>
            <form role="form" method="post" action="{{ cmfRoute('cmf_profile', [], false) }}" id="admin-profile-form">
                <input type="hidden" name="_method" value="PUT">
                <!-- disable chrome email & password autofill -->
                <input type="text" name="login" class="hidden" formnovalidate disabled>
                <input type="password" class="hidden" formnovalidate disabled>
                <input type="text" name="email" class="hidden" formnovalidate value="test@test.com" disabled>
                <input type="password" class="hidden" formnovalidate disabled>
                <!-- end of autofill disabler -->
                <div class="box-body">
                    <?php $loginColumn = \PeskyCMF\Config\CmfConfig::getPrimary()->user_login_column(); ?>
                    @if ($loginColumn !== 'email')
                        <div class="form-group">
                            <label for="{{ $loginColumn }}-input">{{ cmfTransCustom('.page.profile.input.' . $loginColumn) }}*</label>
                            <input class="form-control" value="{{ $admin->$loginColumn }}" name="{{ $loginColumn }}" id="{{ $loginColumn }}-input"
                                   type="text" required="required" @if(!$canSubmit) disabled @endif>
                        </div>
                    @endif
                    @if ($admin::hasColumn('email'))
                        <?php $emailRequired = !$admin::getColumn('email')->isValueCanBeNull(); ?>
                        <div class="form-group">
                            <label for="email-input">{{ cmfTransCustom('.page.profile.input.email') . ($emailRequired ? '*' : '') }}</label>
                            <input class="form-control" value="{{ $admin->email }}" name="email" id="email-input"
                                   type="email" @if($emailRequired) required="required" @endif @if(!$canSubmit) disabled @endif>
                        </div>
                    @endif
                    <div class="form-group">
                        <label for="new-password-input">{{ cmfTransCustom('.page.profile.input.new_password') }}</label>
                        <input class="form-control" value="" name="new_password" id="new-password-input"
                               type="password" autocomplete="off" @if(!$canSubmit) disabled @endif>
                    </div>
                    @if ($admin::hasColumn('name'))
                        <div class="form-group">
                            <label for="name-input">{{ cmfTransCustom('.page.profile.input.name') }}</label>
                            <input class="form-control" value="{{ $admin->name }}" name="name" id="name-input"
                                   type="text" @if(!$canSubmit) disabled @endif>
                        </div>
                    @endif
                    @if ($admin::hasColumn('language'))
                        <div class="form-group">
                            <label for="language-input">{{ cmfTransCustom('.page.profile.input.language') }}</label>
                            <select class="form-control" data-value="{{ $admin->language }}" name="language" id="language-input"
                                    required="required" @if(!$canSubmit) disabled @endif>
                                @foreach(\PeskyCMF\Config\CmfConfig::getPrimary()->locales() as $lang)
                                    <option value="{{ $lang }}">{{ cmfTransCustom('.language.' . $lang) }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    @if ($admin::hasColumn('timezone'))
                        <div class="form-group">
                            <label for="timezone-input">{{ cmfTransCustom('.page.profile.input.timezone') }}</label>
                            <?php $isRequired = !$admin::getColumn('timezone')->allowsNullValues(); ?>
                            <select class="form-control selectpicker" data-value="{{ $admin->timezone }}" name="timezone" id="timezone-input"
                                    @if ($isRequired) required="required" @endif
                                    @if(!$canSubmit) disabled @endif
                                    data-live-search-placeholder="{{ cmfTransCustom('.page.profile.input.timezone_search') }}">
                                <?php
                                    /** @var \PeskyORM\ORM\RecordInterface|\PeskyCMF\Db\Admins\CmfAdmin $userClass */
                                    $userClass = \PeskyCMF\Config\CmfConfig::getPrimary()->user_record_class();
                                    $usersTable = $userClass::getTable();
                                ?>
                                <?php if (!$isRequired) ?>
                                <option value="">{{ cmfTransCustom('.page.profile.input.no_timezone') }}</option>
                                @foreach($usersTable::getTimezonesList(true) as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="form-group">
                        <label for="old-password-input">{{ cmfTransCustom('.page.profile.input.old_password') }}*</label>
                        <input class="form-control" value="" name="old_password" id="old-password-input"
                               type="password" autocomplete="off" required="required" @if(!$canSubmit) disabled @endif>
                    </div>
                </div>
                <div class="box-footer">
                    <div class="row">
                        <div class="col-xs-6">
                            <a class="btn btn-default" href="#" data-nav="back" data-default-url="{{ \PeskyCMF\Config\CmfConfig::getPrimary()->home_page_url() }}">
                                {{ cmfTransGeneral('.form.toolbar.cancel') }}
                            </a>
                        </div>
                        <div class="col-xs-6">
                            <button type="submit" class="btn btn-success pull-right" @if(!$canSubmit) disabled @endif>
                                {{ cmfTransGeneral('.form.toolbar.submit') }}
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
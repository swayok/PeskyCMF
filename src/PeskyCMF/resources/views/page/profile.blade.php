<?php
/**
 * @var \PeskyCMF\Db\Admins\CmfAdmin|\PeskyORM\ORM\RecordInterface|\Illuminate\Contracts\Auth\Authenticatable $user
 * @var bool|null $canSubmit
 * @var \PeskyCMF\Auth\CmfAuthModule $authModule
 */
$cmfConfig = $authModule->getCmfConfig();
$canSubmit = $canSubmit || $canSubmit === null;
?>
<div class="content-header">
    <h1>
        {{ $cmfConfig::transCustom('.page.profile.header') }}
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="#" data-nav="back" data-default-url="{{ $cmfConfig::home_page_url() }}">
                <i class="glyphicon fa fa-reply"></i>
                {{ $cmfConfig::transGeneral('.action.back') }}
            </a>
        </li>
        <li>
            <a href="#" data-nav="reload">
                <i class="glyphicon glyphicon-refresh"></i>
                {{ $cmfConfig::transGeneral('.action.reload_page') }}
            </a>
        </li>
    </ol>
</div>

<div class="content">
    <div class="row"><div class="col-xs-6 col-xs-offset-3">
        <div class="box box-primary">
            <form role="form" method="post" action="{{ cmfRoute('cmf_profile', [], false, $cmfConfig) }}" id="cmf-user-profile-form">
                <input type="hidden" name="_method" value="PUT">
                <!-- disable chrome email & password autofill -->
                <input type="text" name="login" class="hidden" formnovalidate disabled>
                <input type="password" class="hidden" formnovalidate disabled>
                <input type="text" name="email" class="hidden" formnovalidate value="test@test.com" disabled>
                <input type="password" class="hidden" formnovalidate disabled>
                <input type="email" name="email" class="hidden" formnovalidate value="test@test.com" disabled>
                <input type="password" class="hidden" formnovalidate disabled>
                <input type="email" formnovalidate style="display: block; width: 0; height: 0; margin: 0; padding: 0; border: 0;" value="test@test.com">
                <input type="password" formnovalidate style="display: block; width: 0; height: 0; margin: 0; padding: 0; border: 0;">
                <!-- end of autofill disabler -->
                <div class="box-body">
                    @php($loginColumn = $authModule->getUserLoginColumnName())
                    @if ($loginColumn !== 'email')
                        <div class="form-group">
                            <label for="{{ $loginColumn }}-input">{{ $cmfConfig::transCustom('.page.profile.input.' . $loginColumn) }}*</label>
                            <input class="form-control" value="{{ $user->$loginColumn }}" name="{{ $loginColumn }}" id="{{ $loginColumn }}-input"
                                   type="text" required="required" @if(!$canSubmit) disabled @endif>
                        </div>
                    @endif
                    @if ($user::hasColumn('email'))
                        @php($emailRequired = !$user::getColumn('email')->isValueCanBeNull())
                        <div class="form-group">
                            <label for="email-input">{{ $cmfConfig::transCustom('.page.profile.input.email') . ($emailRequired ? '*' : '') }}</label>
                            <input class="form-control" value="{{ $user->email }}" name="email" id="email-input"
                                   type="email" @if($emailRequired) required="required" @endif @if(!$canSubmit) disabled @endif>
                        </div>
                    @endif
                    <div class="form-group">
                        <!-- disable chrome email & password autofill -->
                        <input type="password" class="hidden" name="new_password" formnovalidate disabled>
                        <!-- end of autofill disabler -->
                        <label for="new-password-input">{{ $cmfConfig::transCustom('.page.profile.input.new_password') }}</label>
                        <input class="form-control" value="" name="new_password" id="new-password-input"
                               type="password" autocomplete="new-password" @if(!$canSubmit) disabled @endif>
                    </div>
                    @if ($user::hasColumn('name'))
                        <div class="form-group">
                            <label for="name-input">{{ $cmfConfig::transCustom('.page.profile.input.name') }}</label>
                            <input class="form-control" value="{{ $user->name }}" name="name" id="name-input"
                                   type="text" @if(!$canSubmit) disabled @endif>
                        </div>
                    @endif
                    @if ($user::hasColumn('language'))
                        <div class="form-group">
                            <label for="language-input">{{ $cmfConfig::transCustom('.page.profile.input.language') }}</label>
                            <select class="form-control" data-value="{{ $user->language }}" name="language" id="language-input"
                                    required="required" @if(!$canSubmit) disabled @endif>
                                @foreach($cmfConfig::locales() as $lang)
                                    <option value="{{ $lang }}">{{ $cmfConfig::transCustom('.language.' . $lang) }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    @if ($user::hasColumn('timezone'))
                        <div class="form-group">
                            <label for="timezone-input">{{ $cmfConfig::transCustom('.page.profile.input.timezone') }}</label>
                            @php($isRequired = !$user::getColumn('timezone')->allowsNullValues())
                            <select class="form-control selectpicker" data-value="{{ $user->timezone }}" name="timezone" id="timezone-input"
                                    @if ($isRequired) required="required" @endif
                                    @if(!$canSubmit) disabled @endif
                                    data-live-search-placeholder="{{ $cmfConfig::transCustom('.page.profile.input.timezone_search') }}">
                                @if (!$isRequired)
                                    <option value="">{{ $cmfConfig::transCustom('.page.profile.input.no_timezone') }}</option>
                                @endif
                                @foreach($authModule->getUsersTable()->getTimezonesList(true) as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="form-group">
                        <!-- disable chrome email & password autofill -->
                        <input type="password" class="hidden" name="old_password" formnovalidate disabled>
                        <!-- end of autofill disabler -->
                        <label for="old-password-input">{{ $cmfConfig::transCustom('.page.profile.input.old_password') }}*</label>
                        <input class="form-control" value="" name="old_password" id="old-password-input"
                               type="password" autocomplete="new-password" required="required" @if(!$canSubmit) disabled @endif>
                    </div>
                </div>
                <div class="box-footer">
                    <div class="row">
                        <div class="col-xs-6">
                            <a class="btn btn-default" href="#" data-nav="back" data-default-url="{{ $cmfConfig::home_page_url() }}">
                                {{ $cmfConfig::transGeneral('.form.toolbar.cancel') }}
                            </a>
                        </div>
                        <div class="col-xs-6">
                            <button type="submit" class="btn btn-success pull-right" @if(!$canSubmit) disabled @endif>
                                {{ $cmfConfig::transGeneral('.form.toolbar.submit') }}
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div></div>
</div>

<script type="application/javascript">
    FormHelper.initForm('#cmf-user-profile-form', '#cmf-user-profile-form');
</script>

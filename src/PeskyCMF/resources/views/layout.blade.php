<!DOCTYPE html>
<html>
@php($scriptsVersion = '2.2.11')
<head>
    <meta charset="UTF-8">
    <title>@section('page-title') {{ cmfConfig()->default_page_title() }} @show</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <link rel="icon" type="image/x-icon" href="/favicon.ico"/>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    @yield('html-head')

    <link href="/packages/cmf-vendors/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>

    @if(!config('peskycmf.assets_are_mixed', false))
        <link href="/packages/cmf-vendors/scrollbar/simple-scrollbar.css" rel="stylesheet" type="text/css"/>
        <link href="/packages/cmf-vendors/bootstrap/switches/css/bootstrap3/bootstrap-switch.css" rel="stylesheet" type="text/css"/>
        <link href="/packages/cmf-vendors/bootstrap/datetimepicker/css/bootstrap-datetimepicker.css" rel="stylesheet" type="text/css"/>
        <link href="/packages/cmf-vendors/bootstrap/select/css/bootstrap-select.css" rel="stylesheet" type="text/css"/>
        <link href="/packages/cmf-vendors/bootstrap/select/css/ajax-bootstrap-select.css" rel="stylesheet" type="text/css"/>
        <link href="/packages/cmf-vendors/bootstrap/fileinput/css/fileinput.min.css" rel="stylesheet" type="text/css"/>
        <link href="/packages/cmf-vendors/bootstrap/awesome-bootstrap-checkbox.css" rel="stylesheet" type="text/css"/>

        <link href="/packages/cmf-vendors/datatables/css/dataTables.bootstrap.css" rel="stylesheet" type="text/css"/>
        <link href="/packages/cmf-vendors/datatables/extensions/FixedColumns/css/fixedColumns.bootstrap.min.css" rel="stylesheet" type="text/css"/>
        <link href="/packages/cmf-vendors/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
        <link href="/packages/cmf-vendors/toastr/toastr.css" rel="stylesheet" type="text/css"/>
        {{--<link href="/packages/cmf-vendors/cropperjs/cropper.css" rel="stylesheet" type="text/css"/>--}}

        <link href="/packages/adminlte/css/AdminLTE.min.css" rel="stylesheet" type="text/css"/>
        <link href="/packages/adminlte/css/skins/{{ cmfConfig()->ui_skin() }}.min.css" rel="stylesheet" type="text/css"/>

        <link href="/packages/cmf-vendors/db-query-builder/css/query-builder.default.css" rel="stylesheet" type="text/css"/>

        <link href="/packages/cmf/css/helpers.css" rel="stylesheet" type="text/css"/>
        <link href="/packages/cmf/css/cmf.app.css?v={{ $scriptsVersion }}" rel="stylesheet" type="text/css"/>
    @else
        <link href="/packages/cmf/compiled/css/bootstrap-plugins.min.css?v={{ $scriptsVersion }}" rel="stylesheet" type="text/css"/>
        <link href="/packages/cmf/compiled/css/libs.min.css?v={{ $scriptsVersion }}" rel="stylesheet" type="text/css"/>
        <link href="/packages/adminlte/css/AdminLTE.min.css" rel="stylesheet" type="text/css"/>
        <link href="/packages/adminlte/css/skins/{{ cmfConfig()->ui_skin() }}.min.css" rel="stylesheet" type="text/css"/>
        <link href="/packages/cmf/compiled/css/cmf.min.css?v={{ $scriptsVersion }}" rel="stylesheet" type="text/css"/>
    @endif

    <link href="/packages/cmf-vendors/fonticons/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>

    @foreach(cmfConfig()->layout_css_includes() as $cssPath)
        <link href="{{ $cssPath }}" rel="stylesheet" type="text/css"/>
    @endforeach

    @stack('styles')

</head>

@php($localeShort = \PeskyCMF\Config\CmfConfig::getShortLocale())
<body class="{{ cmfConfig()->ui_skin() }}" data-locale="{{ $localeShort }}">
    <div class="wrapper has-preloader loading" id="page-wrapper">

    </div>

    <script type="text/html" id="modal-template">
        <div class="modal fade" tabindex="-1" role="dialog" id="@{{= it.id }}">
            <div class="modal-dialog @{{? it.size === 'large' }} modal-lg @{{?}} @{{? it.size === 'small' }} modal-sm @{{?}}" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="box-tools pull-right">
                            <a class="btn btn-box-tool fs13 va-t ptn mt5 reload-url-button hidden" href=""
                               data-toggle="tooltip" title="{{ cmfTransGeneral('ui.modal.reload') }}">
                                <i class="glyphicon glyphicon-refresh"></i>
                            </a>
                            <button type="button" data-dismiss="modal" class="btn btn-box-tool va-t pbn ptn mt5"
                                    data-toggle="tooltip" title="{{ cmfTransGeneral('ui.modal.close') }}">
                                <span class="fs24 lh15">&times;</span>
                            </button>
                        </div>
                        <h4 class="modal-title">@{{= it.title || '' }}</h4>
                    </div>
                    <div class="modal-body">
                        @{{= it.content || '' }}
                    </div>
                    @{{? it.footer }}
                    <div class="modal-footer">
                        @{{= it.footer }}
                    </div>
                    @{{?}}
                </div>
            </div>
        </div>
    </script>

    <script type="application/javascript">
        var CmfSettings = {!! json_encode(cmfConfig()->js_app_settings(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!};
        var AppData = {!! json_encode(cmfConfig()->js_app_data(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!};
    </script>

    <script src="/packages/cmf-vendors/jquery3/jquery.min.js" type="text/javascript"></script>

    @php($localeWithSuffixDashed = \PeskyCMF\Config\CmfConfig::getLocaleWithSuffix('-'))
    @php($localeWithSuffixUnderscored = \PeskyCMF\Config\CmfConfig::getLocaleWithSuffix('_'))

    @if(!config('peskycmf.assets_are_mixed', false))
        <script src="/packages/cmf-vendors/scrollbar/simple-scrollbar.js" type="text/javascript"></script>
        <script src="/packages/cmf-vendors/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
        <script src="/packages/cmf-vendors/bootstrap/switches/js/bootstrap-switch.min.js" type="text/javascript"></script>
        <script src="/packages/cmf-vendors/datatables/js/jquery.dataTables.min.js" type="text/javascript"></script>
        <script src="/packages/cmf-vendors/datatables/js/dataTables.bootstrap.min.js" type="text/javascript"></script>
        <script src="/packages/cmf-vendors/datatables/extensions/Select/js/dataTables.select.min.js" type="text/javascript"></script>
        <script src="/packages/cmf-vendors/datatables/extensions/FixedColumns/js/dataTables.fixedColumns.min.js" type="text/javascript"></script>
        <script src="/packages/adminlte/js/app.js" type="text/javascript"></script>

        <script src="/packages/cmf-vendors/modernizr.custom.js" type="text/javascript"></script>
        <script src="/packages/cmf-vendors/router/page.js?v={{ $scriptsVersion }}" type="text/javascript"></script>
        <script src="/packages/cmf-vendors/dotjs/doT.js" type="text/javascript"></script>
        <script src="/packages/cmf-vendors/moment/moment.js" type="text/javascript"></script>
        <script src="/packages/cmf-vendors/jQuery-extendext/jQuery.extendext.js" type="text/javascript"></script>
        <script src="/packages/cmf-vendors/db-query-builder/js/query-builder.js" type="text/javascript"></script>
        <script src="/packages/cmf-vendors/db-query-builder/plugins/bt-selectpicker-values/plugin.js" type="text/javascript"></script>
        <script src="/packages/cmf-vendors/jquery.plugins.js" type="text/javascript"></script>
        <script src="/packages/cmf-vendors/jquery-form/jquery.form.min.js" type="text/javascript"></script>
        <script src="/packages/cmf-vendors/toastr/toastr.min.js" type="text/javascript"></script>
        <script src="/packages/cmf-vendors/base64.js" type="text/javascript"></script>
        <script src="/packages/cmf-vendors/ckeditor/ckeditor.js" type="text/javascript"></script>
        <script src="/packages/cmf-vendors/ckeditor/adapters/jquery.js" type="text/javascript"></script>

        <script src="/packages/cmf-vendors/bootstrap/select/js/bootstrap-select.min.js" type="text/javascript"></script>
        <script src="/packages/cmf-vendors/bootstrap/select/js/i18n/defaults-en_US.min.js" type="text/javascript"></script>
        <script src="/packages/cmf-vendors/bootstrap/select/js/ajax-bootstrap-select.min.js" type="text/javascript"></script>
        <script src="/packages/cmf-vendors/bootstrap/select/js/locale/ajax-bootstrap-select.en-US.min.js" type="text/javascript"></script>
        <script src="/packages/cmf-vendors/bootstrap/fileinput/js/fileinput.min.js" type="text/javascript"></script>

        <script src="/packages/cmf-vendors/bootstrap/datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
        <script src="/packages/cmf-vendors/jquery.inputmask/jquery.inputmask.bundle.min.js" type="text/javascript"></script>
        <script src="/packages/cmf-vendors/sortable/Sortable.js" type="text/javascript"></script>
        {{--<script src="/packages/cmf-vendors/cropperjs/cropper.min.js" type="text/javascript"></script>--}}

        <script src="/packages/cmf/js/cmf.config.js?v={{ $scriptsVersion }}" type="text/javascript"></script>
        <script src="/packages/cmf/js/debug.dialog.js?v={{ $scriptsVersion }}" type="text/javascript"></script>
        <script src="/packages/cmf/js/cmf.helpers.js?v={{ $scriptsVersion }}" type="text/javascript"></script>
        <script src="/packages/cmf/js/cmf.utils.js?v={{ $scriptsVersion }}" type="text/javascript"></script>
        <script src="/packages/cmf/js/cmf.routing.js?v={{ $scriptsVersion }}" type="text/javascript"></script>
        <script src="/packages/cmf/js/cmf.scaffolds.js?v={{ $scriptsVersion }}" type="text/javascript"></script>
        <script src="/packages/cmf/js/cmf.app.js?v={{ $scriptsVersion }}" type="text/javascript"></script>
    @else
        <script src="/packages/cmf/compiled/js/libs.min.js?v={{ $scriptsVersion }}" type="text/javascript"></script>
        <script src="/packages/cmf/compiled/js/bootstrap-and-plugins.min.js?v={{ $scriptsVersion }}" type="text/javascript"></script>
        <script src="/packages/cmf/compiled/js/datatables-and-plugins.min.js?v={{ $scriptsVersion }}" type="text/javascript"></script>
        <script src="/packages/cmf/compiled/js/jquery-plugins.min.js?v={{ $scriptsVersion }}" type="text/javascript"></script>
        <script src="/packages/cmf/compiled/js/cmf.min.js?v={{ $scriptsVersion }}" type="text/javascript"></script>
    @endif

    @if (cmfConfig()->config('optimize_ui_templates.enabled', false))
        <script src="{{ cmfRoute('cmf_cached_templates_js', ['_' => time()]) }}" type="text/javascript"></script>
    @endif

    <script src="{{ cmfRoute('cmf_ckeditor_config_js', ['_' => csrf_token()]) }}" type="text/javascript"></script>
    <script src="/packages/cmf-vendors/db-query-builder/i18n/query-builder.{{ $localeShort }}.js" type="text/javascript"></script>
    <script src="/packages/cmf-vendors/moment/locale/{{ $localeShort }}.js" type="text/javascript"></script>
    @if ($localeShort !== 'en')
        <script src="/packages/cmf-vendors/bootstrap/select/js/i18n/defaults-{{ $localeWithSuffixUnderscored }}.js" type="text/javascript"></script>
        <script src="/packages/cmf-vendors/bootstrap/select/js/locale/ajax-bootstrap-select.{{ $localeWithSuffixDashed }}.min.js" type="text/javascript"></script>
        <script src="/packages/cmf-vendors/bootstrap/fileinput/js/locales/{{ strtolower(substr($localeShort, 0, 2)) }}.js" type="text/javascript"></script>
    @endif

    @foreach(cmfConfig()->layout_js_includes() as $jsPath)
        <script src="{!! $jsPath !!}" type="text/javascript"></script>
    @endforeach

    @foreach(cmfConfig()->layout_js_code_blocks() as $jsCode)
        {!! $jsCode !!}
    @endforeach

    @stack('scripts')

    @stack('jscode')

    <?php $message = Session::pull(cmfConfig()->session_message_key(), false); ?>
    @if (!empty($message) && (is_string($message) || (is_array($message) && !empty($message['message']))))
        <script type="application/javascript">
            <?php $type = is_string($message) || empty($message['type']) || !in_array($message['type'], ['success', 'info', 'warning', 'error']) ? 'info' : $message['type'] ?>
            $(document).ready(function () {
                if (typeof toastr !== 'undefined') {
                    toastr.{{ $type }}('{{ is_string($message) ? $message : $message['message'] }}');
                }
            });
        </script>
    @endif
</body>
</html>
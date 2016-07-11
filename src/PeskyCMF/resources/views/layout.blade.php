<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>@section('page-title') {{ \PeskyCMF\Config\CmfConfig::getInstance()->default_page_title() }} @show</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <link rel="icon" type="image/x-icon" href="/favicon.ico"/>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    @yield('html-head')

    <link href="/packages/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/packages/bootstrap/switches/css/bootstrap3/bootstrap-switch.css" rel="stylesheet" type="text/css"/>
    <link href="/packages/bootstrap/datetimepicker/css/bootstrap-datetimepicker.css" rel="stylesheet" type="text/css"/>
    <link href="/packages/cmf/js/lib/datatables/css/dataTables.bootstrap.css" rel="stylesheet" type="text/css"/>
    <link href="/packages/cmf/js/lib/datatables/extensions/Select/css/select.bootstrap.css" rel="stylesheet" type="text/css"/>
    <link href="/packages/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <link href="/packages/ionicons/css/ionicons.min.css" rel="stylesheet" type="text/css"/>
    <link href="/packages/cmf/css/fonts/source_sans_pro/source_sans_pro.css" rel="stylesheet" type="text/css"/>
    <link href="/packages/adminlte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/packages/cmf/css/bootstrap-datepicker3.css" rel="stylesheet" type="text/css"/>
    <link href="/packages/cmf/js/lib/bootstrap-select/bootstrap-select.css" rel="stylesheet" type="text/css"/>
    <link href="/packages/cmf/css/awesome-bootstrap-checkbox.css" rel="stylesheet" type="text/css"/>
    <link href="/packages/cmf/js/lib/toastr/toastr.css" rel="stylesheet" type="text/css"/>
    <link href="/packages/adminlte/css/AdminLTE.min.css" rel="stylesheet" type="text/css"/>
    <link href="/packages/adminlte/css/AdminLTE.min.css" rel="stylesheet" type="text/css"/>
    <link href="/packages/adminlte/css/skins/skin-blue.min.css" rel="stylesheet" type="text/css"/>

    <link href="/packages/cmf/css/helpers.css" rel="stylesheet" type="text/css" id="place-dynamic-css-files-before"/>
    <link href="/packages/cmf/js/lib/query_builder/css/query-builder.default.css" rel="stylesheet" type="text/css"/>
    <link href="/packages/cmf/css/cmf.app.css" rel="stylesheet" type="text/css"/>

    @foreach(\PeskyCMF\Config\CmfConfig::getInstance()->layout_css_includes() as $cssPath)
        <link href="{{ $cssPath }}" rel="stylesheet" type="text/css"/>
    @endforeach

    @yield('css')
    @yield('css2')

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
</head>

<body class="skin-blue" data-locale="{{ app()->getLocale() }}">
    <div class="wrapper has-preloader loading" id="page-wrapper">

    </div>

    <script type="application/javascript">
        <?php $urlPrefix = \PeskyCMF\Config\CmfConfig::getInstance()->url_prefix(); ?>
        var CmfSettings = {
            isDebug: {{ config('app.debug') ? 'true' : 'false' }},
            rootUrl: '/{{ $urlPrefix }}',
            uiUrl: '{{ str_ireplace("/{$urlPrefix}/", '', route('cmf_main_ui', [], false)) }}',
            userDataUrl: '{{ str_ireplace("/{$urlPrefix}/", '', route('cmf_profile_data', [], false)) }}',
            defaultPageTitle: '{{ \PeskyCMF\Config\CmfConfig::getInstance()->default_page_title() }}'
        };
    </script>

    <script src="/packages/cmf/js/lib/jquery/jquery-2.1.3.min.js" type="text/javascript"></script>

    <script src="/packages/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="/packages/bootstrap/switches/js/bootstrap-switch.min.js" type="text/javascript"></script>
    <script src="/packages/adminlte/plugins/slimScroll/jquery.slimscroll.min.js" type="text/javascript"></script>
    <script src='/packages/adminlte/plugins/fastclick/fastclick.min.js'></script>
    <script src="/packages/cmf/js/lib/datatables/js/jquery.dataTables.min.js" type="text/javascript"></script>
    <script src="/packages/cmf/js/lib/datatables/js/dataTables.bootstrap.min.js" type="text/javascript"></script>
    <script src="/packages/cmf/js/lib/datatables/extensions/Select/js/Select/js/dataTables.select.min.js" type="text/javascript"></script>
    <script src="/packages/adminlte/plugins/datepicker/bootstrap-datepicker.js" type="text/javascript"></script>
    <script src="/packages/adminlte/plugins/datepicker/locales/bootstrap-datepicker.ru.js" type="text/javascript"></script>
    <script src="/packages/adminlte/js/app.js" type="text/javascript"></script>

    <script src="/packages/cmf/js/lib/modernizr.custom.js" type="text/javascript"></script>
    <script src="/packages/cmf/js/lib/pilot.router.js" type="text/javascript"></script>
    <script src="/packages/cmf/js/lib/jquery.observable.js" type="text/javascript"></script>
    <script src="/packages/cmf/js/lib/rison.object.coder.js" type="text/javascript"></script>
    <script src="/packages/cmf/js/lib/dotjs/doT.js" type="text/javascript"></script>
    <script src="/packages/cmf/js/lib/moment/moment.js" type="text/javascript"></script>
    <script src="/packages/cmf/js/lib/moment/locale/{{ app()->getLocale() }}.js" type="text/javascript"></script>
    <script src="/packages/cmf/js/lib/jQuery.extendext.js" type="text/javascript"></script>
    <script src="/packages/cmf/js/lib/query_builder/query-builder.js" type="text/javascript"></script>
    <script src="/packages/cmf/js/lib/query_builder/i18n/query-builder.{{ app()->getLocale() }}.js" type="text/javascript"></script>
    <script src="/packages/cmf/js/lib/jquery.plugins.js" type="text/javascript"></script>
    <script src="/packages/cmf/js/lib/jquery.form.js" type="text/javascript"></script>
    <script src="/packages/cmf/js/lib/toastr/toastr.js" type="text/javascript"></script>
    <script src="/packages/cmf/js/lib/base64.js" type="text/javascript"></script>
    <script src="/packages/cmf/js/lib/ckeditor/ckeditor.js" type="text/javascript"></script>
    <script src="/packages/cmf/js/lib/ckeditor/adapters/jquery.js" type="text/javascript"></script>
    <script src="/packages/cmf/js/lib/ckeditor/config.js" type="text/javascript"></script>
    <script src="/packages/cmf/js/lib/bootstrap-select/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/packages/bootstrap/datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
    <script src="/packages/cmf/js/cmf.global.vars.js" type="text/javascript"></script>
    <script src="/packages/cmf/js/debug.dialog.js" type="text/javascript"></script>
    <script src="/packages/cmf/js/cmf.helpers.js" type="text/javascript"></script>
    <script src="/packages/cmf/js/cmf.utils.js" type="text/javascript"></script>
    <script src="/packages/cmf/js/cmf.controllers.js" type="text/javascript"></script>
    <script src="/packages/cmf/js/scaffold/scaffold.controllers.js?_=20151008" type="text/javascript"></script>
    <script src="/packages/cmf/js/scaffold/scaffold.manager.js" type="text/javascript"></script>
    <script src="/packages/cmf/js/cmf.app.js" type="text/javascript"></script>

    @foreach(\PeskyCMF\Config\CmfConfig::getInstance()->layout_js_includes() as $jsPath)
        <script src="{{ $jsPath }}" rel="stylesheet" type="text/javascript"></script>
    @endforeach

    @yield('js')
    @yield('js2')

    <?php $message = Session::pull(\PeskyCMF\Config\CmfConfig::getInstance()->session_message_key(), false); ?>
    @if (!empty($message) && (is_string($message) || is_array($message) && !empty($message['message'])))
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
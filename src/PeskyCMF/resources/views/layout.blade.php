<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>@section('page-title') {{ \PeskyCMF\Config\CmfConfig::getInstance()->default_page_title() }} @show</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <link rel="icon" type="image/x-icon" href="/favicon.ico"/>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    @yield('html-head')

    <link href="/packages/cmf-vendors/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/packages/cmf-vendors/bootstrap/switches/css/bootstrap3/bootstrap-switch.css" rel="stylesheet" type="text/css"/>
    <link href="/packages/cmf-vendors/bootstrap/datetimepicker/css/bootstrap-datetimepicker.css" rel="stylesheet" type="text/css"/>
    <link href="/packages/cmf-vendors/bootstrap/select/css/bootstrap-select.css" rel="stylesheet" type="text/css"/>

    <link href="/packages/cmf-vendors/datatables/css/dataTables.bootstrap.css" rel="stylesheet" type="text/css"/>
    <link href="/packages/cmf-vendors/fontions/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <link href="/packages/cmf-vendors/fontions/ionicons/css/ionicons.min.css" rel="stylesheet" type="text/css"/>
    <link href="/packages/cmf-vendors/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/packages/cmf-vendors/toastr/toastr.css" rel="stylesheet" type="text/css"/>

    <link href="/packages/adminlte/css/AdminLTE.min.css" rel="stylesheet" type="text/css"/>
    <link href="/packages/adminlte/css/skins/skin-blue.min.css" rel="stylesheet" type="text/css"/>

    <link href="/packages/cmf-vendors/db-query-builder/css/query-builder.default.css" rel="stylesheet" type="text/css"/>
    <link href="/packages/cmf/css/font-replace.css" rel="stylesheet" type="text/css"/>
    <link href="/packages/cmf/css/helpers.css" rel="stylesheet" type="text/css" id="place-dynamic-css-files-before"/>
    <link href="/packages/cmf/css/cmf.app.css" rel="stylesheet" type="text/css"/>

    @foreach(\PeskyCMF\Config\CmfConfig::getInstance()->layout_css_includes() as $cssPath)
        <link href="{{ $cssPath }}" rel="stylesheet" type="text/css"/>
    @endforeach

    @yield('css')
    @yield('css2')

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="/packages/cmf-vendors/html5shiv.min.js"></script>
    <script src="/packages/cmf-vendors/respond.min.js"></script>
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

    <script src="/packages/cmf-vendors/jquery/jquery.min.js" type="text/javascript"></script>

    <script src="/packages/cmf-vendors/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="/packages/cmf-vendors/bootstrap/switches/js/bootstrap-switch.min.js" type="text/javascript"></script>
    <script src='/packages/adminlte/plugins/fastclick/fastclick.min.js'></script>
    <script src="/packages/cmf-vendors/datatables/js/jquery.dataTables.min.js" type="text/javascript"></script>
    <script src="/packages/cmf-vendors/datatables/js/dataTables.bootstrap.min.js" type="text/javascript"></script>
    <script src="/packages/cmf-vendors/datatables/extensions/Select/js/dataTables.select.min.js" type="text/javascript"></script>
    <script src="/packages/adminlte/js/app.js" type="text/javascript"></script>

    <script src="/packages/cmf-vendors/modernizr.custom.js" type="text/javascript"></script>
    <script src="/packages/cmf-vendors/pilot.router.js" type="text/javascript"></script>
    <script src="/packages/cmf-vendors/jquery.observable.js" type="text/javascript"></script>
    <script src="/packages/cmf-vendors/rison.object.coder.js" type="text/javascript"></script>
    <script src="/packages/cmf-vendors/dotjs/doT.js" type="text/javascript"></script>
    <script src="/packages/cmf-vendors/moment/moment.js" type="text/javascript"></script>
    <script src="/packages/cmf-vendors/moment/locale/{{ app()->getLocale() }}.js" type="text/javascript"></script>
    <script src="/packages/cmf-vendors/jQuery-extendext/jQuery.extendext.js" type="text/javascript"></script>
    <script src="/packages/cmf-vendors/db-query-builder/js/query-builder.js" type="text/javascript"></script>
    <script src="/packages/cmf-vendors/db-query-builder/i18n/query-builder.{{ app()->getLocale() }}.js" type="text/javascript"></script>
    <script src="/packages/cmf-vendors/jquery.plugins.js" type="text/javascript"></script>
    <script src="/packages/cmf-vendors/jquery.form.js" type="text/javascript"></script>
    <script src="/packages/cmf-vendors/toastr/toastr.min.js" type="text/javascript"></script>
    <script src="/packages/cmf-vendors/base64.js" type="text/javascript"></script>
    <script src="/packages/cmf-vendors/ckeditor/ckeditor.js" type="text/javascript"></script>
    <script src="/packages/cmf-vendors/ckeditor/adapters/jquery.js" type="text/javascript"></script>
    <script src="/packages/cmf-vendors/ckeditor/config.js" type="text/javascript"></script>
    <script src="/packages/cmf-vendors/bootstrap/select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/packages/cmf-vendors/bootstrap/datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
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
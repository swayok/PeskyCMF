<?php
/**
 * @var \PeskyCMF\Config\CmfConfig $cmfConfig
 */
?>
<!DOCTYPE html>
<html>
@php($scriptsVersion = '2.3.3')
@php($scriptsMinificationSuffix = config('app.debug') ? '' : '.min')
@php($layoutFiles = $cmfConfig::layout_cmf_core_includes())
<head>
    <meta charset="UTF-8">
    <title>@section('page-title') {{ $cmfConfig::default_page_title() }} @show</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <link rel="icon" type="image/x-icon" href="/favicon.ico"/>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    @yield('html-head')

    @foreach($layoutFiles['css'] as $cssPath)
        @if (is_array($cssPath))
            @foreach($cssPath as $filePath)
                <link href="{!! $filePath !!}?v={{ $scriptsVersion }}" rel="stylesheet" type="text/css"/>
            @endforeach
        @else
            <link href="{!! $cssPath !!}?v={{ $scriptsVersion }}" rel="stylesheet" type="text/css"/>
        @endif
    @endforeach

    @foreach($cmfConfig::layout_css_includes() as $cssPath)
        <link href="{!! $cssPath !!}" rel="stylesheet" type="text/css"/>
    @endforeach

    @stack('styles')

    @foreach($layoutFiles['js-head'] as $jsPath)
        <script src="{!! $jsPath !!}" type="text/javascript"></script>
    @endforeach

</head>

@php($localeShort = $cmfConfig::getShortLocale())
<body class="{{ $cmfConfig::ui_skin() }}" data-locale="{{ $localeShort }}">
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
        var CmfSettings = {!! json_encode($cmfConfig::js_app_settings(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!};
        var AppData = {!! json_encode($cmfConfig::js_app_data(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!};
    </script>

    @foreach($layoutFiles['js'] as $jsPath)
        @if (is_array($jsPath))
            @foreach($jsPath as $filePath)
                <script src="{!! $filePath !!}?v={{ $scriptsVersion }}" type="text/javascript"></script>
            @endforeach
        @else
            <script src="{!! $jsPath !!}?v={{ $scriptsVersion }}" type="text/javascript"></script>
        @endif
    @endforeach

    @if ($cmfConfig::config('optimize_ui_templates.enabled', false))
        <script src="{{ cmfRoute('cmf_cached_templates_js', ['_' => time()]) }}" type="text/javascript"></script>
    @endif

    @foreach($cmfConfig::layout_js_includes() as $jsPath)
        <script src="{!! $jsPath !!}" type="text/javascript"></script>
    @endforeach

    @foreach($cmfConfig::layout_js_code_blocks() as $jsCode)
        {!! $jsCode !!}
    @endforeach

    <script type="application/javascript">
        // fix for CKEditor textarea inside bootstrap modal
        $.fn.modal.Constructor.prototype.enforceFocus = function() {
            var modal = this;
            $(document).on('focusin.modal', function (e) {
                if (modal.$element[0] !== e.target && !modal.$element.has(e.target).length
                    && !$(e.target.parentNode).hasClass('cke_dialog_ui_input_select')
                    && !$(e.target.parentNode).hasClass('cke_dialog_ui_input_textarea')
                    && !$(e.target.parentNode).hasClass('cke_dialog_ui_input_text')) {
                    modal.$element.focus()
                }
            });
        };
    </script>

    @stack('scripts')

    @stack('jscode')

    <?php $message = Session::pull($cmfConfig::session_message_key(), false); ?>
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
<?php
/**
 * @var \PeskyCMF\Config\CmfConfig $cmfConfig
 */
?>
<div class="content-header">
    <h1>{{ cmfTransCustom('.api_docs.header') }}</h1>
    <ol class="breadcrumb">
        <li>
            <a href="{{ cmfRoute('cmf_api_docs_download_postman_collection') }}" download>
                <i class="glyphicon glyphicon-download-alt"></i>
                {{ cmfTransCustom('.api_docs.download_postman_collection') }}
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
<div class="content" id="api-docs">
    @foreach($cmfConfig::getApiDocumentationModule()->getDocumentationClassesList() as $header => $methods)
        <div class="row"><div class="col-xs-12">
            <div class="box box-solid box-primary api-docs-section">
                <div class="box-header">
                    <h3 class="box-title">{{ $header }}</h3>
                    <div class="box-tools pull-right">
                        <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    </div>
                </div>
                <div class="box-body">
                    <div class="box-group">
                        @foreach($methods as $method)
                            @include('cmf::page.api_docs_for_method', ['method' => $method::create()])
                        @endforeach
                    </div>
                </div>
            </div>
        </div></div>
    @endforeach
</div>

<script type="application/javascript">
    $(function () {
        var $apiDocs = $("#api-docs");
        $apiDocs.find('.api-docs-section').boxWidget({
            animationSpeed: 0
        });
        $apiDocs.on('click', '.api-documentation-for-method-header[data-target]', function () {
            if ($(this).attr('aria-expanded') === 'true') {
                document.location.hash = '';
            } else {
                document.location.hash = $(this).attr('data-target');
            }
        });
        if (document.location.hash) {
            var $el = $apiDocs.find(document.location.hash);
            if ($el.length) {
                $apiDocs.find(document.location.hash).collapse('show');
                $('html, body').animate({
                    scrollTop: Math.max(0, $el.offset().top - 100)
                });
            }
        }
    });
</script>




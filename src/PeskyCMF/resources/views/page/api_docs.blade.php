<div class="content-header">
    <h1>{{ cmfTransCustom('.api_docs.header') }}</h1>
    <ol class="breadcrumb">
        <li>
            <a href="#" data-nav="reload">
                <i class="glyphicon glyphicon-refresh"></i>
               {{ cmfTransGeneral('.action.reload_page') }}
            </a>
        </li>
    </ol>
</div>
<div class="content" id="api-docs">
    @foreach(\PeskyCMF\Config\CmfConfig::getInstance()->getApiDocsSections() as $header => $methods)
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
                            @include('cmf::page.api_docs_for_method', ['method' => $method])
                        @endforeach
                    </div>
                </div>
            </div>
        </div></div>
    @endforeach
</div>

<script type="application/javascript">
    $("#api-docs").find('.api-docs-section').activateBox();
</script>




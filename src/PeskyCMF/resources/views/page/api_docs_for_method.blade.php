<?php
/**
 * @var \PeskyCMF\CMS\ApiDocs\CmsApiDocs $method
 */
?>
<div class="panel box box-solid box-default">
    <div class="box-header with-border" style="cursor:pointer" data-toggle="collapse" data-target="#{{ $method->getUuid() }}">
        <div class="col-xs-6 text-bold">{{ $method->title }}</div>
        <div class="col-xs-2">{{ $method->httpMethod }}</div>
        <div class="col-xs-4">/api/v1{{ $method->url }}</div>
    </div>
    <div id="{{ $method->getUuid() }}" class="panel-collapse collapse">
        <div class="box-body">
            <div class="row">
                @if(trim($method->description) !== '')
                <div class="col-xs-12 col-xl-6">
                    <div class="box box-solid box-default">
                        <div class="box-header">
                            <div class="box-title">
                                {{ cmfTransCustom('.api_docs.description') }}
                            </div>
                        </div>
                        <div class="box-body">
                            {!! $method->description !!}
                        </div>
                    </div>
                </div>
                @endif
                @if (!empty($method->headers))
                <div class="col-xs-12 col-xl-6">
                    <div class="box box-solid box-default">
                        <div class="box-header">
                            <div class="box-title">
                                {{ cmfTransCustom('.api_docs.headers') }}
                            </div>
                        </div>
                        <div class="box-body">
                            @foreach($method->headers as $header => $value)
                                {{ $header }}: {{ $value }}
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>
            <div class="row">
                @if (!empty($method->urlQueryParams))
                <div class="col-xs-12 col-xl-6">
                    <div class="box box-solid box-warning">
                        <div class="box-header">
                            <div class="box-title">
                                {{ cmfTransCustom('.api_docs.url_query_params') }}
                            </div>
                        </div>
                        <div class="box-body">
                            <pre>{{ json_encode($method->urlQueryParams, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    </div>
                </div>
                @endif
                @if (!empty($method->postParams))
                <div class="col-xs-12 col-xl-6">
                    <div class="box box-solid box-info">
                        <div class="box-header">
                            <div class="box-title">
                                {{ cmfTransCustom('.api_docs.post_params') }}
                            </div>
                        </div>
                        <div class="box-body">
                            <pre>{{ json_encode($method->postParams, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            <div class="row">
                <div class="col-xs-6">
                    <div class="box box-solid box-success">
                        <div class="box-header">
                            <div class="box-title">
                                {{ cmfTransCustom('.api_docs.response') }}
                            </div>
                        </div>
                        <div class="box-body">
                            <pre>{{ json_encode($method->onSuccess, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    </div>
                </div>
                <div class="col-xs-6">
                    <div class="box box-solid box-danger">
                        <div class="box-header">
                            <div class="box-title">
                                {{ cmfTransCustom('.api_docs.errors') }}
                            </div>
                        </div>
                        <div class="box-body">
                            <dl>
                                @foreach(array_merge($method->getCommonErrors(), $method->getPossibleErrors()) as $failInfo)
                                    <dt>
                                        <div>{{ array_get($failInfo, 'title', '*no title*') }}</div>
                                        <small class="text-danger">HTTP code: {{ array_get($failInfo, 'code', '*no HTTP code*') }}</small>
                                    </dt>
                                    <dd><pre>{{ json_encode(array_get($failInfo, 'response', ''), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></dd>
                                @endforeach
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
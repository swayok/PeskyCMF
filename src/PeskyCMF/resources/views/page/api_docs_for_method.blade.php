<?php
/**
 * @var \PeskyCMF\ApiDocs\CmfApiMethodDocumentation $method
 */
$url = $method->getUrl();
$hasUrl = $url !== '';
?>
<div class="panel box box-solid box-default">
    <div class="box-header with-border" style="cursor:pointer" data-toggle="collapse" data-target="#{{ $method->getUuid() }}">
        @if ($hasUrl)
            <div class="col-xs-6 text-bold">{{ $method->getTitle() }}</div>
            <div class="col-xs-6 text-nowrap of-h">
                <div class="http-method ib" style="width: 65px;">{{ $method->httpMethod }}</div>
                <span class="url">{{ $url }}</span>
            </div>
        @else
            <div class="col-xs-12 text-bold">{{ $method->getTitle() }}</div>
        @endif
    </div>
    <div id="{{ $method->getUuid() }}" class="panel-collapse collapse">
        <div class="box-body">
            @if($hasUrl)
                <div class="row">
                    <div class="col-xs-12 fs16 fw600">
                        <div class="box box-solid box-default">
                            <div class="box-body">
                                <span class="http-method">{{ $method->httpMethod }}</span>
                                <span class="fa fa-long-arrow-right"></span>
                                <span class="url">{{ $url }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="row">
                @if($method->hasDescription())
                <div class="col-xs-12 col-xl-6">
                    <div class="box box-solid box-default">
                        <div class="box-header">
                            <div class="box-title">
                                {{ cmfTransCustom('.api_docs.description') }}
                            </div>
                        </div>
                        <div class="box-body">
                            {!! $method->getDescription() !!}
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
                                <div>{{ $header }}: {{ $value }}</div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>
            <div class="row">
                @if (!empty($method->urlParameters))
                    <div class="col-xs-12 col-xl-6">
                        <div class="box box-solid box-warning">
                            <div class="box-header">
                                <div class="box-title">
                                    {{ cmfTransCustom('.api_docs.url_params') }}
                                </div>
                            </div>
                            <div class="box-body ptn pbn">
                                @foreach($method->urlParameters as $name => $comment)
                                    <div class="mv10"><span class="label label-default fs14">{{ $name }}</span> - {{ $comment }}</div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
                @if (!empty($method->urlQueryParameters))
                <div class="col-xs-12 col-xl-6">
                    <div class="box box-solid box-warning">
                        <div class="box-header">
                            <div class="box-title">
                                {{ cmfTransCustom('.api_docs.url_query_params') }}
                            </div>
                        </div>
                        <div class="box-body ptn pbn">
                            @foreach($method->urlQueryParameters as $name => $comment)
                                <div class="mv10"><span class="label label-default fs14">{{ $name }}</span> - {{ $comment }}</div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
                @if (!empty($method->postParameters))
                <div class="col-xs-12 col-xl-6">
                    <div class="box box-solid box-info">
                        <div class="box-header">
                            <div class="box-title">
                                {{ cmfTransCustom('.api_docs.post_params') }}
                            </div>
                        </div>
                        <div class="box-body ptn pbn">
                            @foreach($method->postParameters as $name => $comment)
                                <div class="mv10"><span class="label label-default fs14">{{ $name }}</span> - {{ $comment }}</div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>
            <div class="row">
                @php($errors = $method->getErrors())

                @if (!empty($method->onSuccess))
                    <div class="@if(empty($errors)) col-xs-12 @else col-xs-6 @endif">
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
                @endif

                @if(!empty($errors))
                    <div class="@if(empty($method->onSuccess)) col-xs-12 @else col-xs-6 @endif">
                        <div class="box box-solid box-danger">
                            <div class="box-header">
                                <div class="box-title">
                                    {{ cmfTransCustom('.api_docs.errors') }}
                                </div>
                            </div>
                            <div class="box-body">
                                <dl>
                                    @foreach($errors as $failInfo)
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
                @endif
            </div>
        </div>
    </div>
</div>
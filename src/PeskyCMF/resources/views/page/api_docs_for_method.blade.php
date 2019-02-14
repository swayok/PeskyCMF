<?php
/**
 * @var \PeskyCMF\ApiDocs\CmfApiDocumentation $method
 */
$url = $method->getUrl();
$hasUrl = $url !== '';
$headers = $method->getHeaders();
$urlParams = $method->getUrlParameters();
$urlQueryParams = $method->getUrlQueryParameters();
$postParams = $method->getPostParameters();
$successData = $method->getOnSuccessData();
$errors = $method->getErrors()
?>
<div class="panel box box-solid box-default">
    <div class="box-header with-border" style="cursor:pointer" data-toggle="collapse" data-target="#{{ $method->getUuid() }}">
        @if ($hasUrl)
            <div class="col-xs-6 text-bold">{{ $method->getTitle() }}</div>
            <div class="col-xs-6 text-nowrap of-h">
                <div class="http-method ib" style="width: 65px;">{{ $method->getHttpMethod() }}</div>
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
                                <span class="http-method">{{ $method->getHttpMethod() }}</span>
                                <span class="fa fa-long-arrow-right"></span>
                                <span class="url">{{ $url }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="row">
                @if($method->hasDescription())
                    @if (!$method->isMethodDocumentation())
                        <div class="col-xs-12">
                            {!! $method->getDescription() !!}
                        </div>
                    @else
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
                @endif
                @if (!empty($headers))
                <div class="col-xs-12 col-xl-6">
                    <div class="box box-solid box-default">
                        <div class="box-header">
                            <div class="box-title">
                                {{ cmfTransCustom('.api_docs.headers') }}
                            </div>
                        </div>
                        <div class="box-body">
                            @foreach($headers as $header => $value)
                                <div>{{ $header }}: {{ $value }}</div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>
            <div class="row">
                @if (!empty($urlParams))
                    <div class="col-xs-12 col-xl-6">
                        <div class="box box-solid box-warning">
                            <div class="box-header">
                                <div class="box-title">
                                    {{ cmfTransCustom('.api_docs.url_params') }}
                                </div>
                            </div>
                            <div class="box-body ptn pbn">
                                @foreach($urlParams as $name => $comment)
                                    <div class="mv10"><span class="label label-default fs14">{{ $name }}</span> - {!! $comment !!}</div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
                @if (!empty($urlQueryParams))
                <div class="col-xs-12 col-xl-6">
                    <div class="box box-solid box-warning">
                        <div class="box-header">
                            <div class="box-title">
                                {{ cmfTransCustom('.api_docs.url_query_params') }}
                            </div>
                        </div>
                        <div class="box-body ptn pbn">
                            @foreach($urlQueryParams as $name => $comment)
                                <div class="mv10"><span class="label label-default fs14">{{ $name }}</span> - {!! $comment !!}</div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
                @if (!empty($postParams))
                <div class="col-xs-12 col-xl-6">
                    <div class="box box-solid box-info">
                        <div class="box-header">
                            <div class="box-title">
                                {{ cmfTransCustom('.api_docs.post_params') }}
                            </div>
                        </div>
                        <div class="box-body ptn pbn">
                            @foreach($postParams as $name => $comment)
                                <div class="mv10">
                                    <span class="label label-default fs14">{{ $name }}</span>
                                    - {!! is_array($comment) ? '<pre>' . json_encode($comment, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . '</pre>' : $comment !!}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>
            <div class="row">
                @if (!empty($successData))
                    <div class="@if(empty($errors)) col-xs-12 @else col-xs-6 @endif">
                        <div class="box box-solid box-success">
                            <div class="box-header">
                                <div class="box-title">
                                    {{ cmfTransCustom('.api_docs.response') }}
                                </div>
                            </div>
                            <div class="box-body">
                                <pre>{{ json_encode($successData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                        </div>
                    </div>
                @endif

                @if(!empty($errors))
                    <div class="@if(empty($successData)) col-xs-12 @else col-xs-6 @endif">
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
<?php
declare(strict_types=1);
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
    <div
        class="box-header with-border api-documentation-for-method-header"
        style="cursor:pointer"
        data-toggle="collapse"
        data-target="#{{ $method->getUuid() }}"
    >
        @if ($hasUrl)
            <div class="col-xs-6 text-bold">{{ $method->getTitle() }}</div>
            <div class="col-xs-6 text-nowrap of-h">
                <div class="http-method ib" style="width: 100px;">{{ $method->getHttpMethod() }}</div>
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
                    @if(!$method->isMethodDocumentation())
                        <div class="col-xs-12">
                            {!! $method->getDescription() !!}
                        </div>
                    @else
                        <div class="col-xs-12 col-xl-6">
                            <div class="box box-default">
                                <div class="box-header br-r br-l br-b">
                                    <div class="box-title">
                                        {{ $method->getCmfConfig()->transCustom('api_docs.description') }}
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
                    <div class="box box-default">
                        <div class="box-header br-r br-l br-b">
                            <div class="box-title">
                                {{ $method->getCmfConfig()->transCustom('api_docs.headers') }}
                            </div>
                        </div>
                        <div class="box-body pn">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th width="150" class="api-documentation-header-name-cell">
                                        {{ $method->getCmfConfig()->transCustom('api_docs.header_name') }}
                                    </th>
                                    <th width="200" class="api-documentation-header-value-cell">
                                        {{ $method->getCmfConfig()->transCustom('api_docs.header_value') }}
                                    </th>
                                    <th class="api-documentation-header-description-cell">
                                        {{ $method->getCmfConfig()->transCustom('api_docs.header_description') }}
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($headers as $headerInfo)
                                    <tr>
                                        <td class="api-documentation-header-name-cell">
                                            <span class="label label-default fs14">{{ $headerInfo['name'] }}</span>
                                        </td>
                                        <td class="api-documentation-header-value-cell">
                                            {!! $headerInfo['type'] !!}
                                        </td>
                                        <td class="api-documentation-header-description-cell">
                                            {!! $headerInfo['description'] !!}
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            <div class="row">
                @if (!empty($urlParams))
                    <div class="col-xs-12 col-xl-6">
                        <div class="box box-solid box-warning">
                            <div class="box-header br-b bg-none">
                                <div class="box-title text-orange">
                                    {{ $method->getCmfConfig()->transCustom('.api_docs.url_params') }}
                                </div>
                            </div>
                            <div class="box-body pn">
                                <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th width="150" class="api-documentation-param-name-cell">
                                            {{ $method->getCmfConfig()->transCustom('.api_docs.param_name') }}
                                        </th>
                                        <th width="200" class="api-documentation-param-type-cell">
                                            {{ $method->getCmfConfig()->transCustom('.api_docs.param_type') }}
                                        </th>
                                        <th class="api-documentation-param-description-cell">
                                            {{ $method->getCmfConfig()->transCustom('.api_docs.param_description') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($urlParams as $paramInfo)
                                    <tr>
                                        <td class="api-documentation-param-name-cell">
                                            <span class="label label-default fs14">{{ $paramInfo['name'] }}</span>
                                        </td>
                                        <td class="api-documentation-param-type-cell">
                                            {!! $paramInfo['type'] !!}
                                        </td>
                                        <td class="api-documentation-param-description-cell">
                                            {!! $paramInfo['description'] !!}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
                @if (!empty($urlQueryParams))
                <div class="col-xs-12 col-xl-6">
                    <div class="box box-solid box-primary">
                        <div class="box-header bg-none br-b">
                            <div class="box-title text-primary">
                                {{ $method->getCmfConfig()->transCustom('.api_docs.url_query_params') }}
                            </div>
                        </div>
                        <div class="box-body pn">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th width="150" class="api-documentation-param-name-cell">
                                            {{ $method->getCmfConfig()->transCustom('.api_docs.param_name') }}
                                        </th>
                                        <th width="200" class="api-documentation-param-type-cell">
                                            {{ $method->getCmfConfig()->transCustom('.api_docs.param_type') }}
                                        </th>
                                        <th class="api-documentation-param-description-cell">
                                            {{ $method->getCmfConfig()->transCustom('.api_docs.param_description') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($urlQueryParams as $paramInfo)
                                    <tr>
                                        <td class="api-documentation-param-name-cell">
                                            <span class="label label-default fs14">{{ $paramInfo['name'] }}</span>
                                        </td>
                                        <td class="api-documentation-param-type-cell">
                                            {!! $paramInfo['type'] !!}
                                        </td>
                                        <td class="api-documentation-param-description-cell">
                                            {!! $paramInfo['description'] !!}
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
                @if (!empty($postParams))
                <div class="col-xs-12 col-xl-6">
                    <div class="box box-solid box-info">
                        <div class="box-header bg-none br-b">
                            <div class="box-title text-aqua">
                                {{ $method->getCmfConfig()->transCustom('.api_docs.post_params') }}
                            </div>
                        </div>
                        <div class="box-body pn">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th width="150" class="api-documentation-param-name-cell">
                                            {{ $method->getCmfConfig()->transCustom('.api_docs.param_name') }}
                                        </th>
                                        <th width="200" class="api-documentation-param-type-cell">
                                            {{ $method->getCmfConfig()->transCustom('.api_docs.param_type') }}
                                        </th>
                                        <th class="api-documentation-param-description-cell">
                                            {{ $method->getCmfConfig()->transCustom('.api_docs.param_description') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($postParams as $paramInfo)
                                    <tr>
                                        <td class="api-documentation-param-name-cell">
                                            <span class="label label-default fs14">{{ $paramInfo['name'] }}</span>
                                        </td>
                                        <td class="api-documentation-param-type-cell">
                                            @if(is_array($paramInfo['type']))
                                                {!! json_encode($paramInfo['type'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
                                            @elseif(is_string($paramInfo['type']))
                                                {!! $paramInfo['type'] !!}
                                            @endif
                                        </td>
                                        <td class="api-documentation-param-description-cell">
                                            {!! $paramInfo['description'] !!}
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            <div class="row">
                @if (!empty($successData))
                    <div class="col-xs-12 col-xl-4">
                        <div class="box box-solid box-success">
                            <div class="box-header">
                                <div class="box-title">
                                    {{ $method->getCmfConfig()->transCustom('api_docs.response') }}
                                </div>
                            </div>
                            <div class="box-body">
                                <pre>{!! json_encode($successData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) !!}</pre>
                            </div>
                        </div>
                    </div>
                @endif

                @if(!empty($errors))
                    <div class="col-xs-12 col-xl-8">
                        <div class="box box-solid box-danger">
                            <div class="box-header">
                                <div class="box-title">
                                    {{ $method->getCmfConfig()->transCustom('api_docs.errors') }}
                                </div>
                            </div>
                            <div class="box-body pn">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th width="90" class="api-documentation-error-code-cell">
                                                {{ $method->getCmfConfig()->transCustom('api_docs.error_http_code') }}
                                            </th>
                                            <th width="200" class="api-documentation-error-title-cell">
                                                {{ $method->getCmfConfig()->transCustom('api_docs.error_title') }}
                                            </th>
                                            <th width="320" class="api-documentation-error-response-cell">
                                                {{ $method->getCmfConfig()->transCustom('api_docs.error_response') }}
                                            </th>
                                            <th class="api-documentation-error-description-cell">
                                                {{ $method->getCmfConfig()->transCustom('api_docs.error_description') }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($errors as $failInfo)
                                        <tr>
                                            <td class="api-documentation-error-code-cell">
                                                <span class="label label-default fs14">
                                                    {{ \Illuminate\Support\Arr::get($failInfo, 'code', '*no HTTP code*') }}
                                                </span>
                                            </td>
                                            <td class="api-documentation-error-title-cell">
                                                {{ \Illuminate\Support\Arr::get($failInfo, 'title', '*no title*') }}
                                            </td>
                                            <td class="api-documentation-error-response-cell pn">
                                                <pre style="max-width: 320px; margin: 0">{!! json_encode(\Illuminate\Support\Arr::get($failInfo, 'response', ''), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) !!}</pre>
                                            </td>
                                            <td class="api-documentation-error-description-cell">
                                                {!! \Illuminate\Support\Arr::get($failInfo, 'description') !!}
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

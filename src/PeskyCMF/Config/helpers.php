<?php

if (!function_exists('routeTpl')) {
    function routeTpl($routeName, $parameters = [], $tplParams = [], $absolute = false, $route = null) {
        $replacements = [];
        foreach ($tplParams as $name => $tplName) {
            if (is_numeric($name)) {
                $name = $tplName;
            }
            $parameters[$name] = '__' . $name . '__';
            $replacements['%' . preg_quote($parameters[$name], '%') . '%'] = '{{= it.' . $tplName . ' }}';
        }
        $url = route($routeName, $parameters, $absolute, $route);
        return preg_replace(array_keys($replacements), array_values($replacements), $url);
    }
}

if (!function_exists('cmfServiceJsonResponse')) {
    function cmfServiceJsonResponse($httpCode = \PeskyCMF\HttpCode::OK, $headers = [], $options = 0) {
        return new \PeskyCMF\Http\CmfJsonResponse([], $httpCode, $headers, $options);
    }
}

if (!function_exists('cmfJsonResponseForValidationErrors')) {
    function cmfJsonResponseForValidationErrors(array $errors = [], $message = null) {
        if (empty($message)) {
            $message = \PeskyCMF\Config\CmfConfig::transBase('.form.validation_errors');
        }
        return cmfServiceJsonResponse(\PeskyCMF\HttpCode::INVALID)
            ->setErrors($errors, $message);
    }
}

if (!function_exists('cmfJsonResponseForHttp404')) {
    function cmfJsonResponseForHttp404($fallbackUrl = null, $message = null) {
        if (empty($message)) {
            $message = \PeskyCMF\Config\CmfConfig::transBase('.error.http404');
        }
        if (empty($fallbackUrl)) {
            $fallbackUrl = route('cmf_start_page');
        }
        return cmfServiceJsonResponse(\PeskyCMF\HttpCode::NOT_FOUND)
            ->setMessage($message)
            ->goBack($fallbackUrl);
    }
}

if (!function_exists('cmfRedirectResponseWithMessage')) {
    function cmfRedirectResponseWithMessage($url, $message, $type = 'info') {
        return Redirect::to($url)->with(\PeskyCMF\Config\CmfConfig::getInstance()->session_message_key(), [
            'message' => $message,
            'type' => $type
        ]);
    }
}
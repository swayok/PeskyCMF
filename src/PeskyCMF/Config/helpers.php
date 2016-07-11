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
        if (request()->ajax()) {
            return cmfServiceJsonResponse()
                ->setMessage($message)
                ->setRedirect($url);
        } else {
            return Redirect::to($url)->with(\PeskyCMF\Config\CmfConfig::getInstance()->session_message_key(), [
                'message' => $message,
                'type' => $type
            ]);
        }
    }
}

if (!function_exists('modifyDotJsTemplateToAllowInnerScriptsAndTemplates')) {
    function modifyDotJsTemplateToAllowInnerScriptsAndTemplates($dotJsTemplate) {
        return preg_replace_callback('%<script([^>]*)>(.*?)</script>%is', function ($matches) {
            if (preg_match('%type="text/html"%i', $matches[1])) {
                // inner dotjs template - needs to be encoded and decoded later
                $encoded = base64_encode($matches[2]);
                return "{{= '<' + 'script{$matches[1]}>' }}{{= Base64.decode('$encoded') }}{{= '</' + 'script>'}}";
            } else {
                $script = preg_replace('%//.*$%m', '', $matches[2]);
                return "{{= '<' + 'script{$matches[1]}>' }}$script{{= '</' + 'script>'}}";
            }
        }, $dotJsTemplate);
    }
}

if (!function_exists('pickLocalization')) {
    /**
     * @param array $translations - format: ['lang1_code' => 'translation1', 'lang2_code' => 'translation2', ...]
     * @param null|string $default - default value to return when there is no translation for app()->getLocale()
     *      language and for CmfConfig::getInstance()->default_locale()
     * @return string|null
     */
    function pickLocalization(array $translations, $default = null) {
        $langCodes = [app()->getLocale(), \PeskyCMF\Config\CmfConfig::getInstance()->default_locale()];
        foreach ($langCodes as $langCode) {
            if (
                array_key_exists($langCode, $translations)
                && is_string($translations[$langCode])
                && trim($translations[$langCode]) !== ''
            ) {
                return $translations[$langCode];
            }
        }
        return $default;
    }

}
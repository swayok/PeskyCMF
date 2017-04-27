<?php

if (!function_exists('routeTpl')) {
    /**
     * @param string $routeName
     * @param array $parameters
     * @param array $tplParams
     * @param bool $absolute
     * @return mixed
     */
    function routeTpl($routeName, array $parameters = [], array $tplParams = [], $absolute = false) {
        $replacements = [];
        foreach ($tplParams as $name => $tplName) {
            if (is_numeric($name)) {
                $name = $tplName;
            }
            $parameters[$name] = '__' . $name . '__';
            $replacements['%' . preg_quote($parameters[$name], '%') . '%'] = '{{= it.' . $tplName . ' }}';
        }
        $url = route($routeName, $parameters, $absolute);
        return preg_replace(array_keys($replacements), array_values($replacements), $url);
    }
}

if (!function_exists('routeToCmfPage')) {
    /**
     * @param string $pageId
     * @param array $queryArgs
     * @param bool $absolute
     * @return mixed
     */
    function routeToCmfPage($pageId, array $queryArgs = [], $absolute = false) {
        return route('cmf_page', array_merge(['page' => $pageId], $queryArgs), $absolute);
    }
}

if (!function_exists('routeToCmfItemsTable')) {
    /**
     * @param string $tableName
     * @param array $filters
     * @param bool $absolute
     * @return mixed
     */
    function routeToCmfItemsTable($tableName, array $filters = [], $absolute = false) {
        // todo: implement filters processing for route
        return route('cmf_items_table', array_merge(['table_name' => $tableName], $filters), $absolute);
    }
}

if (!function_exists('routeToCmfTableCustomData')) {
    /**
     * @param string $tableName
     * @param string $dataId - identifier of data to be returned. For example: 'special_options'
     * @param bool $absolute
     * @return mixed
     */
    function routeToCmfTableCustomData($tableName, $dataId, $absolute = false) {
        return route('cmf_api_get_custom_data', array_merge(['table_name' => $tableName, 'data_id' => $dataId]), $absolute);
    }
}

if (!function_exists('routeToCmfItemAddForm')) {
    /**
     * @param string $tableName
     * @param bool $absolute
     * @return mixed
     */
    function routeToCmfItemAddForm($tableName, $absolute = false) {
        return route('cmf_item_add_form', ['table_name' => $tableName], $absolute);
    }
}

if (!function_exists('routeToCmfItemEditForm')) {
    /**
     * @param string $tableName
     * @param int|string $itemId
     * @param bool $absolute
     * @return mixed
     */
    function routeToCmfItemEditForm($tableName, $itemId, $absolute = false) {
        return route('cmf_item_edit_form', ['table_name' => $tableName, 'id' => $itemId], $absolute);
    }
}

if (!function_exists('routeToCmfItemDetails')) {
    /**
     * @param string $tableName
     * @param int|string $itemId
     * @param bool $absolute
     * @return mixed
     */
    function routeToCmfItemDetails($tableName, $itemId, $absolute = false) {
        return route('cmf_item_details', ['table_name' => $tableName, 'id' => $itemId], $absolute);
    }
}

if (!function_exists('routeToCmfItemCustomPage')) {
    /**
     * @param string $tableName
     * @param int|string $itemId
     * @param string $pageId
     * @param array $queryArgs
     * @param bool $absolute
     * @return mixed
     */
    function routeToCmfItemCustomPage($tableName, $itemId, $pageId, array $queryArgs = [], $absolute = false) {
        return route(
            'cmf_item_custom_page',
            array_merge(
                ['table_name' => $tableName, 'id' => $itemId, 'page' => $pageId],
                $queryArgs
            ),
            $absolute
        );
    }
}

if (!function_exists('transChoiceRu')) {
    function transChoiceRu($id, $number, array $parameters = [], $domain = 'messages', $locale = null) {
        $trans = \Swayok\Utils\StringUtils::pluralizeRu($number, trans($id, [], $domain, $locale));
        if (!empty($parameters)) {
            $trans = \Swayok\Utils\StringUtils::insert($trans, $parameters, ['before' => ':']);
        }
        return $trans;
    }
}

if (!function_exists('cmfTransGeneral')) {

    /**
     * @param $path - must strat with '.'
     * @param array $parameters
     * @param string $domain
     * @param null|string $locale
     * @return string|array
     */
    function cmfTransGeneral($path, array $parameters = [], $domain = 'messages', $locale = null) {
        return \PeskyCMF\Config\CmfConfig::transGeneral($path, $parameters, $domain, $locale);
    }
}

if (!function_exists('cmfTransCustom')) {

    /**
     * @param $path - must strat with '.'
     * @param array $parameters
     * @param string $domain
     * @param null|string $locale
     * @return string|array
     */
    function cmfTransCustom($path, array $parameters = [], $domain = 'messages', $locale = null) {
        return \PeskyCMF\Config\CmfConfig::transCustom($path, $parameters, $domain, $locale);
    }
}

if (!function_exists('cmfJsonResponse')) {
    /**
     * @param int $httpCode
     * @param array $headers
     * @param int $options
     * @return \PeskyCMF\Http\CmfJsonResponse
     */
    function cmfJsonResponse($httpCode = \PeskyCMF\HttpCode::OK, array $headers = [], $options = 0) {
        return new \PeskyCMF\Http\CmfJsonResponse([], $httpCode, $headers, $options);
    }
}

if (!function_exists('cmfJsonResponseForValidationErrors')) {
    /**
     * @param array $errors
     * @param null|string $message
     * @return \PeskyCMF\Http\CmfJsonResponse
     */
    function cmfJsonResponseForValidationErrors(array $errors = [], $message = null) {
        if (empty($message)) {
            $message = cmfTransGeneral('.form.validation_errors');
        }
        return cmfJsonResponse(\PeskyCMF\HttpCode::INVALID)
            ->setErrors($errors, $message);
    }
}

if (!function_exists('cmfJsonResponseForHttp404')) {
    /**
     * @param null|string $fallbackUrl
     * @param null|string $message
     * @return \PeskyCMF\Http\CmfJsonResponse
     */
    function cmfJsonResponseForHttp404($fallbackUrl = null, $message = null) {
        if (empty($message)) {
            $message = cmfTransGeneral('.error.http404');
        }
        if (empty($fallbackUrl)) {
            $fallbackUrl = route('cmf_start_page');
        }
        return cmfJsonResponse(\PeskyCMF\HttpCode::NOT_FOUND)
            ->setMessage($message)
            ->goBack($fallbackUrl);
    }
}

if (!function_exists('cmfRedirectResponseWithMessage')) {
    /**
     * @param string $url
     * @param string $message
     * @param string $type
     * @return \Illuminate\Http\RedirectResponse|\PeskyCMF\Http\CmfJsonResponse
     */
    function cmfRedirectResponseWithMessage($url, $message, $type = 'info') {
        if (request()->ajax()) {
            return cmfJsonResponse()
                ->setMessage($message)
                ->setRedirect($url);
        } else {
            return Redirect::to($url)->with(\PeskyCMF\Config\CmfConfig::getPrimary()->session_message_key(), [
                'message' => $message,
                'type' => $type
            ]);
        }
    }
}

if (!function_exists('modifyDotJsTemplateToAllowInnerScriptsAndTemplates')) {
    /**
     * @param string $dotJsTemplate
     * @return string
     */
    function modifyDotJsTemplateToAllowInnerScriptsAndTemplates($dotJsTemplate) {
        return preg_replace_callback('%<script([^>]*)>(.*?)</script>%is', function ($matches) {
            if (preg_match('%type="text/html"%i', $matches[1])) {
                // inner dotjs template - needs to be encoded and decoded later
                $encoded = base64_encode($matches[2]);
                return "{{= '<' + 'script{$matches[1]}>' }}{{= Base64.decode('$encoded') }}{{= '</' + 'script>'}}";
            } else {
                $script = preg_replace('%(^|\s)//.*$%m', '$1', $matches[2]); //< remove "//" comments from a script
                return "{{= '<' + 'script{$matches[1]}>' }}$script{{= '</' + 'script>'}}";
            }
        }, $dotJsTemplate);
    }
}

if (!function_exists('pickLocalization')) {
    /**
     * @param array $translations - format: ['lang1_code' => 'translation1', 'lang2_code' => 'translation2', ...]
     * @param null|string $default - default value to return when there is no translation for app()->getLocale()
     *      language and for CmfConfig::getPrimary()->default_locale()
     * @return string|null
     */
    function pickLocalization(array $translations, $default = null) {
        $langCodes = [app()->getLocale(), \PeskyCMF\Config\CmfConfig::getPrimary()->default_locale()];
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

if (!function_exists('insertPageData')) {

    /**
     * @param int $pageId - ID of the page
     * @param string $columnName - page's column name
     * @return mixed
     */
    function insertPageData($pageId, $columnName = 'content') {
        return \PeskyCMF\CMS\CmsFrontendUtils::getPageData($pageId, $columnName);
    }
}

if (!function_exists('insertLinkToPage')) {

    /**
     * @param int $pageId - ID of the page
     * @param null|string $linkLabel - content of the <a> tag
     * @return string
     */
    function insertLinkToPage($pageId, $linkLabel = null) {
        return \PeskyCMF\CMS\CmsFrontendUtils::makeHtmlLinkToPageForInsert($pageId, $linkLabel)->build();
    }
}

if (!function_exists('setting')) {

    /**
     * Get value for CmsSetting called $name (CmsSetting->key === $name)
     * @param string $name - setting name
     * @param mixed $default - default value
     * @return mixed|\PeskyCMF\CMS\CmsAppSettings|\App\AppSettings
     */
    function setting($name = null, $default = null) {
        /** @var \PeskyCMF\CMS\CmsAppSettings $class */
        $class = app(\PeskyCMF\CMS\CmsAppSettings::class);
        if ($name === null) {
            return $class::getInstance();
        } else {
            return $class::$name($default);
        }
    }
}
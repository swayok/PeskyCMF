<?php

if (!function_exists('cmfRoute')) {
    /**
     * @param string $routeName
     * @param array $parameters
     * @param bool $absolute
     * @param null|\PeskyCMF\Config\CmfConfig $cmfConfig
     * @return string
     */
    function cmfRoute($routeName, array $parameters = [], $absolute = false, $cmfConfig = null) {
        if (!$cmfConfig) {
            $cmfConfig = \PeskyCMF\Config\CmfConfig::getPrimary();
        }
        return route($cmfConfig::getRouteName($routeName), $parameters, $absolute);
    }
}

if (!function_exists('cmfRouteTpl')) {
    /**
     * @param string $routeName
     * @param array $parameters
     * @param array $tplParams
     * @param bool $absolute
     * @param null|\PeskyCMF\Config\CmfConfig $cmfConfig
     * @return string
     */
    function cmfRouteTpl($routeName, array $parameters = [], array $tplParams = [], $absolute = false, $cmfConfig = null) {
        $replacements = [];
        foreach ($tplParams as $name => $tplName) {
            $dotJsVarPrefix = '';
            if (is_numeric($name)) {
                $name = $tplName;
                $dotJsVarPrefix = 'it.';
            }
            $parameters[$name] = '__' . $name . '__';
            $replacements['%' . preg_quote($parameters[$name], '%') . '%'] = "{{= {$dotJsVarPrefix}{$tplName} }}";
        }
        if (!$cmfConfig) {
            $cmfConfig = \PeskyCMF\Config\CmfConfig::getPrimary();
        }
        $url = route($cmfConfig::getRouteName($routeName), $parameters, $absolute);
        return preg_replace(array_keys($replacements), array_values($replacements), $url);
    }
}

if (!function_exists('routeToCmfPage')) {
    /**
     * @param string $pageId
     * @param array $queryArgs
     * @param bool $absolute
     * @param null|\PeskyCMF\Config\CmfConfig $cmfConfig
     * @return string
     */
    function routeToCmfPage($pageId, array $queryArgs = [], $absolute = false, $cmfConfig = null) {
        if (Gate::denies('cmf_page', [$pageId])) {
            return null;
        }
        if (!$cmfConfig) {
            $cmfConfig = \PeskyCMF\Config\CmfConfig::getPrimary();
        }
        return route($cmfConfig::getRouteName('cmf_page'), array_merge(['page' => $pageId], $queryArgs), $absolute);
    }
}

if (!function_exists('redirectToCmfPage')) {
    /**
     * @param string $pageId
     * @param array $queryArgs
     * @param bool $absolute
     * @param null|\PeskyCMF\Config\CmfConfig $cmfConfig
     * @return string
     */
    function redirectToCmfPage($pageId, array $queryArgs = [], $absolute = false, $cmfConfig = null) {
        $url = routeToCmfPage($pageId, $queryArgs, $absolute, $cmfConfig);
        if (!$url) {
            abort(\PeskyCMF\HttpCode::FORBIDDEN);
        }
        return \Redirect::to($url);
    }
}

if (!function_exists('routeToCmfItemsTable')) {
    /**
     * @param string $tableName
     * @param array $filters - key-value array where key is column name to add to filter and value is column's value.
     * Note: Operator is 'equals' (col1 = val1). Multiple filters joined by 'AND' (col1 = val1 AND col2 = val2)
     * @param bool $absolute
     * @param null|\PeskyCMF\Config\CmfConfig $cmfConfig
     * @return string
     */
    function routeToCmfItemsTable($tableName, array $filters = [], $absolute = false, $cmfConfig = null) {
        if (Gate::denies('resource.view', [$tableName])) {
            return null;
        }
        $params = ['table_name' => $tableName];
        if (!empty($filters)) {
            $params['filter'] = json_encode($filters, JSON_UNESCAPED_UNICODE);
        }
        if (!$cmfConfig) {
            $cmfConfig = \PeskyCMF\Config\CmfConfig::getPrimary();
        }
        return route($cmfConfig::getRouteName('cmf_items_table'), $params, $absolute);
    }
}

if (!function_exists('routeToCmfTableCustomData')) {
    /**
     * @param string $tableName
     * @param string $dataId - identifier of data to be returned. For example: 'special_options'
     * @param bool $absolute
     * @param null|\PeskyCMF\Config\CmfConfig $cmfConfig
     * @return string
     */
    function routeToCmfTableCustomData($tableName, $dataId, $absolute = false, $cmfConfig = null) {
        if (Gate::denies('resource.view', [$tableName])) {
            return null;
        }
        if (!$cmfConfig) {
            $cmfConfig = \PeskyCMF\Config\CmfConfig::getPrimary();
        }
        return route(
            $cmfConfig::getRouteName('cmf_api_get_custom_data'),
            array_merge(['table_name' => $tableName, 'data_id' => $dataId]),
            $absolute
        );
    }
}

if (!function_exists('routeToCmfItemAddForm')) {
    /**
     * @param string $tableName
     * @param bool $absolute
     * @param null|\PeskyCMF\Config\CmfConfig $cmfConfig
     * @return string
     */
    function routeToCmfItemAddForm($tableName, $absolute = false, $cmfConfig = null) {
        if (Gate::denies('resource.create', [$tableName])) {
            return null;
        }
        if (!$cmfConfig) {
            $cmfConfig = \PeskyCMF\Config\CmfConfig::getPrimary();
        }
        return route($cmfConfig::getRouteName('cmf_item_add_form'), ['table_name' => $tableName], $absolute);
    }
}

if (!function_exists('routeToCmfItemEditForm')) {
    /**
     * @param string $tableName
     * @param int|string $itemId
     * @param bool $absolute
     * @param null|\PeskyCMF\Config\CmfConfig $cmfConfig
     * @return string
     */
    function routeToCmfItemEditForm($tableName, $itemId, $absolute = false, $cmfConfig = null) {
        if (Gate::denies('resource.update', [$tableName, $itemId])) {
            return null;
        }
        if (!$cmfConfig) {
            $cmfConfig = \PeskyCMF\Config\CmfConfig::getPrimary();
        }
        return route($cmfConfig::getRouteName('cmf_item_edit_form'), ['table_name' => $tableName, 'id' => $itemId], $absolute);
    }
}

if (!function_exists('routeToCmfItemDetails')) {
    /**
     * @param string $tableName
     * @param int|string $itemId
     * @param bool $absolute
     * @param null|\PeskyCMF\Config\CmfConfig $cmfConfig
     * @return string
     */
    function routeToCmfItemDetails($tableName, $itemId, $absolute = false, $cmfConfig = null) {
        if (Gate::denies('resource.details', [$tableName, $itemId])) {
            return null;
        }
        if (!$cmfConfig) {
            $cmfConfig = \PeskyCMF\Config\CmfConfig::getPrimary();
        }
        return route($cmfConfig::getRouteName('cmf_item_details'), ['table_name' => $tableName, 'id' => $itemId], $absolute);
    }
}

if (!function_exists('routeToCmfItemCustomPage')) {
    /**
     * @param string $tableName
     * @param int|string $itemId
     * @param string $pageId
     * @param array $queryArgs
     * @param bool $absolute
     * @param null|\PeskyCMF\Config\CmfConfig $cmfConfig
     * @return string
     */
    function routeToCmfItemCustomPage($tableName, $itemId, $pageId, array $queryArgs = [], $absolute = false, $cmfConfig = null) {
        if (!$cmfConfig) {
            $cmfConfig = \PeskyCMF\Config\CmfConfig::getPrimary();
        }
        return route(
            $cmfConfig::getRouteName('cmf_item_custom_page'),
            array_merge(
                ['table_name' => $tableName, 'id' => $itemId, 'page' => $pageId],
                $queryArgs
            ),
            $absolute
        );
    }
}

if (!function_exists('transChoiceRu')) {
    /**
     * @param string|array $idOrTranslations - array: translations for items count 1,4,5
     * @param int $itemsCount
     * @param array $parameters
     * @param string|null $locale
     * @return string
     */
    function transChoiceRu($idOrTranslations, $itemsCount, array $parameters = [], $locale = 'ru') {
        return transChoiceAlt($idOrTranslations, $itemsCount, $parameters, $locale);
    }
}

if (!function_exists('transChoiceAlt')) {
    /**
     * @param string|array $idOrTranslations - array: translations for items count 1,4,5
     * @param int $itemsCount
     * @param array $parameters
     * @param string|null $locale
     * @return string
     */
    function transChoiceAlt($idOrTranslations, $itemsCount, array $parameters = [], $locale = null) {
        $trans = \Swayok\Utils\StringUtils::pluralizeRu(
            $itemsCount,
            is_array($idOrTranslations) ? $idOrTranslations : trans($idOrTranslations, [], $locale)
        );
        if (!empty($parameters)) {
            $trans = \Swayok\Utils\StringUtils::insert($trans, $parameters, ['before' => ':']);
        }
        return $trans;
    }
}

if (!function_exists('cmfTransGeneral')) {

    /**
     * @param string $path - without dictionary name. Example: 'admins.test' will be converted to '{dictionary}.admins.test'
     * @param array $parameters
     * @param null|string $locale
     * @return string|array
     */
    function cmfTransGeneral($path, array $parameters = [], $locale = null) {
        return \PeskyCMF\Config\CmfConfig::transGeneral($path, $parameters, $locale);
    }
}

if (!function_exists('cmfTransCustom')) {

    /**
     * @param string $path - without dictionary name. Example: 'admins.test' will be converted to '{dictionary}.admins.test'
     * @param array $parameters
     * @param null|string $locale
     * @return string|array
     */
    function cmfTransCustom($path, array $parameters = [], $locale = null) {
        return \PeskyCMF\Config\CmfConfig::transCustom($path, $parameters, $locale);
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
            $fallbackUrl = \PeskyCMF\Config\CmfConfig::getPrimary()->home_page_url();
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

if (!function_exists('formatDate')) {
    /**
     * @param string $date
     * @param bool $addTime
     * @return string
     */
    function formatDate($date, $addTime = false) {
        if (!is_numeric($date)) {
            $date = strtotime($date);
        }
        if ($date <= 0) {
            return cmfTransGeneral('.error.invalid_date_received');
        }
        $month = cmfTransGeneral('.month.when.' . date('m', $date));
        return date('j ', $date) . $month . date(' Y', $date) . ($addTime ? date(' H:i', $date) : '');
    }
}

if (!function_exists('formatMoney')) {
    /**
     * @param float $number
     * @param string $thousandsSeparator
     * @return string
     */
    function formatMoney($number, $thousandsSeparator = ' ') {
        return number_format($number, 2, '.', $thousandsSeparator);
    }
}

if (!function_exists('formatSeconds')) {
    /**
     * @param int $seconds
     * @param bool $displaySeconds - true: display "days hours minutes seconds"; false: display "days hours minutes"
     * @param bool $shortLabels - true: use shortened labels (min, sec, hr, d) | false: user full lables (days, hours, minutes, seconds)
     * @return bool|string
     */
    function formatSeconds($seconds, $displaySeconds = true, $shortLabels = true) {
        $ret = '';
        if ($seconds >= 86400) {
            $days = floor($seconds / 86400);
            $seconds -= 86400 * $days;
            $ret .= $shortLabels
                ? cmfTransGeneral('.format_seconds.days_short', ['days' => $days])
                : transChoiceAlt(cmfTransGeneral('.format_seconds.days'), $days, ['days' => $days]);
        }
        if ($seconds >= 3600 || !empty($days)) {
            $hours = floor($seconds / 3600);
            $seconds -= 3600 * $hours;
            $ret .= $shortLabels
                ? cmfTransGeneral('.format_seconds.hours_short', ['hours' => $hours])
                : transChoiceAlt(cmfTransGeneral('.format_seconds.hours'), $hours, ['hours' => $hours]);
        }
        if ($seconds >= 60 || !empty($days) || !empty($hours)) {
            $minutes = floor($seconds / 60);
            $seconds -= 60 * $minutes;
            $ret .= $shortLabels
                ? cmfTransGeneral('.format_seconds.minutes_short', ['minutes' => $minutes])
                : transChoiceAlt(cmfTransGeneral('.format_seconds.minutes'), $minutes, ['minutes' => $minutes]);
        }
        if ($displaySeconds) {
            $ret .= $shortLabels
                ? cmfTransGeneral('.format_seconds.seconds_short', ['seconds' => $seconds])
                : transChoiceAlt(cmfTransGeneral('.format_seconds.seconds'), $seconds, ['seconds' => $seconds]);
        } else if (empty($days) && empty($hours) && empty($minutes)) {
            $ret = cmfTransGeneral('.format_seconds.less_then_a_minute');
        }
        return $ret;
    }
}

if (!function_exists('pickLocalization')) {
    /**
     * Pick correct localization strings from specially formatted array. Useful for localizations stored in DB
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
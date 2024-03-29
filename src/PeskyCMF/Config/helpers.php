<?php

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\RedirectResponse;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Http\CmfJsonResponse;

if (!defined('t')) {
    define('t', true);
}

if (!defined('f')) {
    define('f', false);
}

if (!defined('y')) {
    define('y', true);
}

if (!defined('n')) {
    define('n', false);
}

if (!defined('DOTJS_INSERT_REGEXP_FOR_ROUTES')) {
    define('DOTJS_INSERT_REGEXP_FOR_ROUTES', '(\{\{\s*=.*?\}\}|\{\s*=.*?\})');
}

if (!function_exists('cmfConfig')) {

    /**
     * @return CmfConfig
     */
    function cmfConfig() {
        return CmfConfig::getPrimary();
    }
}

if (!function_exists('cmfRoute')) {
    /**
     * @param string $routeName
     * @param array $parameters
     * @param bool $absolute
     * @param null|CmfConfig|string $cmfConfig
     * @return string
     */
    function cmfRoute(string $routeName, array $parameters = [], bool $absolute = false, ?CmfConfig $cmfConfig = null): string {
        if (!$cmfConfig) {
            $cmfConfig = cmfConfig();
        }
        return $cmfConfig::route($routeName, $parameters, $absolute);
    }
}

if (!function_exists('cmfRouteTpl')) {
    /**
     * @param string $routeName
     * @param array $parameters
     * @param array $tplParams where
     *  - key - parameter or query argument name;
     *  - value is optional an should be set to valid dotjs insert without a wrapper,
     *      for example: ['id' => 'it.some_id'] will generate '{{= it.some_id }}' insert for 'id' parameter
     *      while ['other_id'] will generate '{{= it.other_id }}' insert for 'other_id' parameter
     * @param bool $absolute
     * @param null|CmfConfig $cmfConfig
     * @return string
     */
    function cmfRouteTpl(string $routeName, array $parameters = [], array $tplParams = [], bool $absolute = false, ?CmfConfig $cmfConfig = null): string {
        $replaces = [];
        $i = 1;
        foreach ($tplParams as $name => $tplName) {
            $dotJsVarPrefix = '';
            if (is_numeric($name)) {
                $name = $tplName;
                $dotJsVarPrefix = 'it.';
            }
            $parameters[$name] = '__dotjs_' . (string)$i . '_insert__';
            $i++;
            $replaces[$parameters[$name]] = "{{= {$dotJsVarPrefix}{$tplName} }}";
        }

        $url = cmfRoute($routeName, $parameters, $absolute, $cmfConfig);
        return str_replace(array_keys($replaces), array_values($replaces), $url);
    }
}

if (!function_exists('routeToCmfPage')) {
    /**
     * @param string $pageId
     * @param array $queryArgs
     * @param bool $absolute
     * @param null|CmfConfig $cmfConfig
     * @param bool $ignoreAccessPolicy - true: will not run Gate::denies('resource.*') test
     * @return string|null
     */
    function routeToCmfPage(string $pageId, array $queryArgs = [], bool $absolute = false, ?CmfConfig $cmfConfig = null, bool $ignoreAccessPolicy = false): ?string {
        if (!$ignoreAccessPolicy && Gate::denies('cmf_page', [$pageId])) {
            return null;
        }
        return cmfRoute('cmf_page', array_merge(['page' => $pageId], $queryArgs), $absolute, $cmfConfig);
    }
}

if (!function_exists('redirectToCmfPage')) {
    /**
     * @param string $pageId
     * @param array $queryArgs
     * @param bool $absolute
     * @param null|CmfConfig $cmfConfig
     * @return RedirectResponse
     */
    function redirectToCmfPage(string $pageId, array $queryArgs = [], bool $absolute = false, ?CmfConfig $cmfConfig = null): RedirectResponse {
        $url = routeToCmfPage($pageId, $queryArgs, $absolute, $cmfConfig);
        if (!$url) {
            abort(\PeskyCMF\HttpCode::FORBIDDEN);
        }
        return \Redirect::to($url);
    }
}

if (!function_exists('routeToCmfItemsTable')) {
    /**
     * @param string $resourceName
     * @param array $filters - key-value array where key is column name to add to filter and value is column's value.
     *      Values may contain dotjs inserts in format: {{= it.id }} or {= it.id }
     *      Note: Operator is 'equals' (col1 = val1). Multiple filters joined by 'AND' (col1 = val1 AND col2 = val2)
     * @param bool $absolute
     * @param null|CmfConfig $cmfConfig
     * @param bool $ignoreAccessPolicy - true: will not run Gate::denies('resource.*') test
     * @return string|null
     */
    function routeToCmfItemsTable(string $resourceName, array $filters = [], bool $absolute = false, ?CmfConfig $cmfConfig = null, bool $ignoreAccessPolicy = false): ?string {
        if (!$ignoreAccessPolicy && Gate::denies('resource.view', [$resourceName])) {
            return null;
        }
        $params = [
            'resource' => $resourceName,
        ];
        $replaces = replaceDotJsInstertsInArrayValuesByUrlSafeInserts($filters);
        if (!empty($filters)) {
            $params['filter'] = json_encode($replaces['data']);
        }
        $url = cmfRoute('cmf_items_table', $params, $absolute, $cmfConfig);
        return replaceUrlSafeInsertsInUrlByDotJsInsterts($url, $replaces['replaces']);
    }
}

if (!function_exists('routeToCmfTableCustomData')) {
    /**
     * @param string $resourceName
     * @param string $dataId - identifier of data to be returned. For example: 'special_options'
     * @param bool $absolute
     * @param null|CmfConfig $cmfConfig
     * @param bool $ignoreAccessPolicy - true: will not run Gate::denies('resource.*') test
     * @return string|null
     */
    function routeToCmfTableCustomData(string $resourceName, string $dataId, bool $absolute = false, ?CmfConfig $cmfConfig = null, bool $ignoreAccessPolicy = false): ?string {
        if (!$ignoreAccessPolicy && Gate::denies('resource.view', [$resourceName])) {
            return null;
        }
        return cmfRoute(
            'cmf_api_get_custom_data',
            array_merge(['resource' => $resourceName, 'data_id' => $dataId]),
            $absolute,
            $cmfConfig
        );
    }
}

if (!function_exists('routeToCmfItemAddForm')) {
    /**
     * @param string $resourceName
     * @param array $data - data for form inputs to be used as default values; may contain dotjs inserts
     *       as values in format: '{{= it.id }}' or '{= it.id }'
     * @param bool $absolute
     * @param null|CmfConfig $cmfConfig
     * @param bool $ignoreAccessPolicy - true: will not run Gate::denies('resource.*') test
     * @return string|null
     */
    function routeToCmfItemAddForm(string $resourceName, array $data = [], bool $absolute = false, ?CmfConfig $cmfConfig = null, bool $ignoreAccessPolicy = false): ?string {
        if (!$ignoreAccessPolicy && Gate::denies('resource.create', [$resourceName])) {
            return null;
        }
        $params = ['resource' => $resourceName];
        $replaces = [];
        foreach ($data as $key => $value) {
            if (!is_string($key)) {
                throw new \InvalidArgumentException('$data argument contains non-string key. All keys must be a strings');
            }
            if (!is_scalar($value) && !is_array($value)) {
                throw new \InvalidArgumentException(
                    '$data argument must be a scalar value or array. ' . gettype($value) . ' received.'
                );
            }
            if (!is_array($value) && preg_match('%^\s*' . DOTJS_INSERT_REGEXP_FOR_ROUTES . '\s*$%s', $value, $matches)) {
                $value = '__dotjs_' . (count($replaces) + 1) . '_insert__';
                $replaces[$value] = '{{' . trim($matches[0], '{} ') . '}}';
            }
            $params[$key] = $value;
        }
        $url = cmfRoute('cmf_item_add_form', $params, $absolute, $cmfConfig);
        return str_replace(array_keys($replaces), array_values($replaces), $url);
    }
}

if (!function_exists('cmfRouteWithPossibleItemIdDotJsInsert')) {
    /**
     * @param string $route
     * @param string|int|float $itemId
     * @param array $parameters
     * @param bool $absolute
     * @param CmfConfig|null|string $cmfConfig
     * @return string
     */
    function cmfRouteWithPossibleItemIdDotJsInsert(string $route, string $itemId, array $parameters, bool $absolute = false, ?CmfConfig $cmfConfig = null): string {
        if (preg_match('%^\s*' . DOTJS_INSERT_REGEXP_FOR_ROUTES . '\s*$%s', $itemId)) {
            $parameters['id'] = '__dotjs_item_id_insert__';
            $url = cmfRoute($route, $parameters, $absolute, $cmfConfig);
            $itemId = '{{' . trim($itemId, '{}') . '}}'; //< normalize inserts like '{= it.id }'
            return str_replace('__dotjs_item_id_insert__', $itemId, $url);
        } else {
            $parameters['id'] = $itemId;
            return cmfRoute($route, $parameters, $absolute, $cmfConfig);
        }
    }
}

if (!function_exists('routeToCmfItemEditForm')) {
    /**
     * @param string $resourceName
     * @param int|string $itemId - it may be a dotjs insert in format: '{{= it.id }}' or '{= it.id }'
     * @param bool $absolute
     * @param null|CmfConfig $cmfConfig
     * @param bool $ignoreAccessPolicy - true: will not run Gate::denies('resource.*') test
     * @return string|null
     */
    function routeToCmfItemEditForm(string $resourceName, string $itemId, bool $absolute = false, ?CmfConfig $cmfConfig = null, bool $ignoreAccessPolicy = false): ?string {
        if (!$ignoreAccessPolicy && Gate::denies('resource.update', [$resourceName, $itemId])) {
            return null;
        }
        return cmfRouteWithPossibleItemIdDotJsInsert(
            'cmf_item_edit_form',
            $itemId,
            ['resource' => $resourceName],
            $absolute,
            $cmfConfig
        );
    }
}

if (!function_exists('routeForCmfTempFileUpload')) {
    /**
     * @param string $resourceName
     * @param string $inputName
     * @param bool $absolute
     * @param null|CmfConfig $cmfConfig
     * @param bool $ignoreAccessPolicy - true: will not run Gate::denies('resource.*') test
     * @return string|null
     */
    function routeForCmfTempFileUpload(string $resourceName, string $inputName, bool $absolute = false, ?CmfConfig $cmfConfig = null, bool $ignoreAccessPolicy = false): ?string {
        if (!$ignoreAccessPolicy && Gate::denies('resource.create', [$resourceName])) {
            return null;
        }
        return cmfRoute(
            'cmf_upload_temp_file_for_input',
            ['resource' => $resourceName, 'input' => $inputName],
            $absolute,
            $cmfConfig
        );
    }
}

if (!function_exists('routeForCmfTempFileDelete')) {
    /**
     * @param string $resourceName
     * @param string $inputName
     * @param bool $absolute
     * @param null|CmfConfig $cmfConfig
     * @param bool $ignoreAccessPolicy - true: will not run Gate::denies('resource.*') test
     * @return string|null
     */
    function routeForCmfTempFileDelete(string $resourceName, string $inputName, bool $absolute = false, ?CmfConfig $cmfConfig = null, bool $ignoreAccessPolicy = false): ?string {
        if (!$ignoreAccessPolicy && Gate::denies('resource.create', [$resourceName])) {
            return null;
        }
        return cmfRoute(
            'cmf_delete_temp_file_for_input',
            ['resource' => $resourceName, 'input' => $inputName],
            $absolute,
            $cmfConfig
        );
    }
}

if (!function_exists('routeToCmfItemCloneForm')) {
    /**
     * @param string $resourceName
     * @param int|string $itemId - it may be a dotjs insert in format: '{{= it.id }}' or '{= it.id }'
     * @param bool $absolute
     * @param null|CmfConfig $cmfConfig
     * @param bool $ignoreAccessPolicy - true: will not run Gate::denies('resource.*') test
     * @return string|null
     */
    function routeToCmfItemCloneForm(string $resourceName, string $itemId, bool $absolute = false, ?CmfConfig $cmfConfig = null, bool $ignoreAccessPolicy = false): ?string {
        if (!$ignoreAccessPolicy && Gate::denies('resource.create', [$resourceName, $itemId])) {
            return null;
        }
        return cmfRouteWithPossibleItemIdDotJsInsert(
            'cmf_item_clone_form',
            $itemId,
            ['resource' => $resourceName],
            $absolute,
            $cmfConfig
        );
    }
}

if (!function_exists('routeToCmfItemDetails')) {
    /**
     * @param string $resourceName
     * @param int|string $itemId - it may be a dotjs insert in format: '{{= it.id }}' or '{= it.id }'
     * @param bool $absolute
     * @param null|CmfConfig $cmfConfig
     * @param bool $ignoreAccessPolicy - true: will not run Gate::denies('resource.*') test
     * @return string|null
     */
    function routeToCmfItemDetails(string $resourceName, string $itemId, bool $absolute = false, ?CmfConfig $cmfConfig = null, bool $ignoreAccessPolicy = false): ?string {
        if (!$ignoreAccessPolicy && Gate::denies('resource.details', [$resourceName, $itemId])) {
            return null;
        }
        return cmfRouteWithPossibleItemIdDotJsInsert(
            'cmf_item_details',
            $itemId,
            ['resource' => $resourceName],
            $absolute,
            $cmfConfig
        );
    }
}

if (!function_exists('routeToCmfItemDelete')) {
    /**
     * @param string $resourceName
     * @param int|string $itemId - it may be a dotjs insert in format: '{{= it.id }}' or '{= it.id }'
     * @param bool $absolute
     * @param null|CmfConfig $cmfConfig
     * @param bool $ignoreAccessPolicy - true: will not run Gate::denies('resource.*') test
     * @return string|null
     */
    function routeToCmfItemDelete(string $resourceName, string $itemId, bool $absolute = false, ?CmfConfig $cmfConfig = null, bool $ignoreAccessPolicy = false): ?string {
        if (!$ignoreAccessPolicy && Gate::denies('resource.delete', [$resourceName, $itemId])) {
            return null;
        }
        return cmfRouteWithPossibleItemIdDotJsInsert(
            'cmf_api_delete_item',
            $itemId,
            ['resource' => $resourceName],
            $absolute,
            $cmfConfig
        );
    }
}

if (!function_exists('routeToCmfResourceCustomPage')) {
    /**
     * @param string $resourceName
     * @param string $pageId
     * @param array $queryArgs - may contain dotjs inserts in format: '{{= it.id }}' or '{= it.id }'
     * @param bool $absolute
     * @param null|CmfConfig $cmfConfig
     * @return string
     */
    function routeToCmfResourceCustomPage(string $resourceName, string $pageId, array $queryArgs = [], bool $absolute = false, ?CmfConfig $cmfConfig = null): string {
        $replaces = replaceDotJsInstertsInArrayValuesByUrlSafeInserts($queryArgs);
        $url = cmfRoute(
            'cmf_resource_custom_page',
            array_merge(
                ['resource' => $resourceName, 'page' => $pageId],
                $replaces['data']
            ),
            $absolute,
            $cmfConfig
        );
        return replaceUrlSafeInsertsInUrlByDotJsInsterts($url, $replaces['replaces']);
    }
}

if (!function_exists('routeToCmfItemCustomPage')) {
    /**
     * @param string $resourceName
     * @param int|string $itemId - it may be a dotjs insert in format: '{{= it.id }}' or '{= it.id }'
     * @param string $pageId
     * @param array $queryArgs - may contain dotjs inserts in format: '{{= it.id }}' or '{= it.id }'
     * @param bool $absolute
     * @param null|CmfConfig $cmfConfig
     * @return string
     */
    function routeToCmfItemCustomPage(string $resourceName, string $itemId, string $pageId, array $queryArgs = [], bool $absolute = false, ?CmfConfig $cmfConfig = null): string {
        $itemDotJs = $itemId;
        if (preg_match('%^\s*' . DOTJS_INSERT_REGEXP_FOR_ROUTES . '\s*$%s', $itemId)) {
            $itemDotJs = '__dotjs_item_id_insert__';
            $itemId = '{{' . trim($itemId, '{} ') . '}}';
        }
        $replaces = replaceDotJsInstertsInArrayValuesByUrlSafeInserts($queryArgs);
        $url = cmfRoute(
            'cmf_item_custom_page',
            array_merge(
                ['resource' => $resourceName, 'id' => $itemDotJs, 'page' => $pageId],
                $replaces['data']
            ),
            $absolute,
            $cmfConfig
        );
        $url = str_replace('__dotjs_item_id_insert__', $itemId, $url);
        return replaceUrlSafeInsertsInUrlByDotJsInsterts($url, $replaces['replaces']);
    }
}

if (!function_exists('routeToCmfItemCustomAction')) {
    /**
     * @param string $resourceName
     * @param int|string $itemId - it may be a dotjs insert in format: '{{= it.id }}' or '{= it.id }'
     * @param string $actionId
     * @param array $queryArgs - may contain dotjs inserts in format: '{{= it.id }}' or '{= it.id }'
     * @param bool $absolute
     * @param null|CmfConfig $cmfConfig
     * @return string
     */
    function routeToCmfItemCustomAction(string $resourceName, string $itemId, string $actionId, array $queryArgs = [], bool $absolute = false, ?CmfConfig $cmfConfig = null): string {
        $itemDotJs = $itemId;
        if (preg_match('%^\s*' . DOTJS_INSERT_REGEXP_FOR_ROUTES . '\s*$%s', $itemId)) {
            $itemDotJs = '__dotjs_item_id_insert__';
            $itemId = '{{' . trim($itemId, '{} ') . '}}';
        }
        $replaces = replaceDotJsInstertsInArrayValuesByUrlSafeInserts($queryArgs);
        $url = cmfRoute(
            'cmf_api_item_custom_action',
            array_merge(
                ['resource' => $resourceName, 'id' => $itemDotJs, 'action' => $actionId],
                $replaces['data']
            ),
            $absolute,
            $cmfConfig
        );
        $url = str_replace('__dotjs_item_id_insert__', $itemId, $url);
        return replaceUrlSafeInsertsInUrlByDotJsInsterts($url, $replaces['replaces']);
    }
}

if (!function_exists('routeToCmfResourceCustomAction')) {
    /**
     * @param string $resourceName
     * @param string $actionId
     * @param array $queryArgs - may contain dotjs inserts in format: '{{= it.id }}' or '{= it.id }'
     * @param bool $absolute
     * @param null|CmfConfig $cmfConfig
     * @return string
     */
    function routeToCmfResourceCustomAction(string $resourceName, string $actionId, array $queryArgs = [], bool $absolute = false, ?CmfConfig $cmfConfig = null): string {
        $replaces = replaceDotJsInstertsInArrayValuesByUrlSafeInserts($queryArgs);
        $url = cmfRoute(
            'cmf_api_resource_custom_action',
            array_merge(
                ['resource' => $resourceName, 'action' => $actionId],
                $replaces['data']
            ),
            $absolute,
            $cmfConfig
        );
        return replaceUrlSafeInsertsInUrlByDotJsInsterts($url, $replaces['replaces']);
    }
}

if (!function_exists('replaceDotJsInstertsInArrayValuesByUrlSafeInserts')) {

    /**
     * @param array $data - array with values that contain dotJs insterts
     * @return array - ['replaces' => $replaces, 'data' => $escapedData]
     */
    function replaceDotJsInstertsInArrayValuesByUrlSafeInserts(array $data): array {
        $ret = ['replaces' => [], 'data' => $data];
        if (!empty($data)) {
            $json = json_encode($data, JSON_UNESCAPED_UNICODE);
            if (preg_match_all('%' . DOTJS_INSERT_REGEXP_FOR_ROUTES . '%s', $json, $matches) > 0) {
                // there are dotJs inserts inside filters
                foreach ($matches[1] as $i => $dotJsInsert) {
                    $replace = '__dotjs_' . $i . '_insert__';
                    $ret['replaces'][$replace] = '{{' . trim($matches[0][$i], '{} ') . '}}';
                    $json = str_replace($dotJsInsert, $replace, $json);
                }
                $ret['data'] = json_decode($json, true);
            }
        }
        return $ret;
    }
}

if (!function_exists('replaceUrlSafeInsertsInUrlByDotJsInsterts')) {

    function replaceUrlSafeInsertsInUrlByDotJsInsterts(string $url, array $replaces): string {
        if (!empty($replaces)) {
            $url = str_replace(array_keys($replaces), array_values($replaces), $url);
        }
        return $url;
    }
}

if (!function_exists('transChoiceRu')) {
    /**
     * @param string|array $idOrTranslations - array: translations rray with 3 values:
     *      array(0 => 'variant for 1', 1 => 'variant for 4', 2 => 'variant for 5')
     * @param int $itemsCount
     * @param array $parameters
     * @param string|null $locale
     * @return string
     */
    function transChoiceRu($idOrTranslations, int $itemsCount, array $parameters = [], string $locale = 'ru') {
        return transChoiceAlt($idOrTranslations, $itemsCount, $parameters, $locale);
    }
}

if (!function_exists('transChoiceAlt')) {
    /**
     * @param string|array $idOrTranslations - array: translations array with 3 values:
     *      array(0 => 'variant for 1', 1 => 'variant for 4', 2 => 'variant for 5')
     * @param int $itemsCount
     * @param array $parameters
     * @param string|null $locale
     * @return string
     */
    function transChoiceAlt($idOrTranslations, int $itemsCount, array $parameters = [], ?string $locale = null) {
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
    function cmfTransGeneral(string $path, array $parameters = [], ?string $locale = null) {
        return CmfConfig::transGeneral($path, $parameters, $locale);
    }
}

if (!function_exists('cmfTransCustom')) {

    /**
     * @param string $path - without dictionary name. Example: 'admins.test' will be converted to '{dictionary}.admins.test'
     * @param array $parameters
     * @param null|string $locale
     * @return string|array
     */
    function cmfTransCustom(string $path, array $parameters = [], ?string $locale = null) {
        return CmfConfig::transCustom($path, $parameters, $locale);
    }
}

if (!function_exists('cmfJsonResponse')) {
    /**
     * @param int $httpCode
     * @param array $headers
     * @param int $options
     * @return CmfJsonResponse
     */
    function cmfJsonResponse(int $httpCode = \PeskyCMF\HttpCode::OK, array $headers = [], $options = 0): CmfJsonResponse {
        return new CmfJsonResponse([], $httpCode, $headers, $options);
    }
}

if (!function_exists('cmfJsonResponseForValidationErrors')) {
    /**
     * @param array $errors
     * @param null|string $message
     * @return CmfJsonResponse
     */
    function cmfJsonResponseForValidationErrors(array $errors = [], ?string $message = null): CmfJsonResponse {
        if (empty($message)) {
            $message = (string)cmfTransGeneral('.form.message.validation_errors');
        }
        return cmfJsonResponse(\PeskyCMF\HttpCode::CANNOT_PROCESS)
            ->setErrors($errors, $message);
    }
}

if (!function_exists('cmfJsonResponseForHttp404')) {
    /**
     * @param null|string $fallbackUrl
     * @param null|string $message
     * @return CmfJsonResponse
     */
    function cmfJsonResponseForHttp404(?string $fallbackUrl = null, ?string $message = null): CmfJsonResponse {
        if (empty($message)) {
            $message = (string)cmfTransGeneral('.message.http404');
        }
        if (empty($fallbackUrl)) {
            $fallbackUrl = cmfConfig()->home_page_url();
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
     * @return RedirectResponse|CmfJsonResponse
     */
    function cmfRedirectResponseWithMessage(string $url, string $message, string $type = 'info') {
        if (request()->ajax()) {
            return cmfJsonResponse()
                ->setMessage($message)
                ->setRedirect($url);
        } else {
            return Redirect::to($url)->with(cmfConfig()->session_message_key(), [
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
    function modifyDotJsTemplateToAllowInnerScriptsAndTemplates(string $dotJsTemplate): string {
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
     * @param string|int|CarbonInterface|null $date
     * @param bool $addTime
     * @param string $yearSuffix - 'none', 'full', 'short' or custom value
     * @param bool|string|int $ignoreYear
     *      - false: year will be added
     *      - true: year will not be added;
     *      - 'current': drop year only when it is same as current
     *      - integer: drop year only when it is same as passed integer
     *      - other values: year will be added
     * @return string
     */
    function formatDate(
        string|int|CarbonInterface|null $date,
        bool $addTime = false,
        string $yearSuffix = 'full',
        bool|string|int $ignoreYear = false,
        ?string $default = ''
    ): string | null {
        if (!$date) {
            return $default;
        }
        if (!($date instanceof CarbonInterface)) {
            if (is_numeric($date)) {
                /** @noinspection CallableParameterUseCaseInTypeContextInspection */
                $date = Carbon::createFromTimestamp($date);
            } else {
                /** @noinspection CallableParameterUseCaseInTypeContextInspection */
                $date = Carbon::parse($date);
            }
        }
        if (in_array(app()->getLocale(), ['ru', 'ru_RU'], true)) {
            $month = mb_strtolower(cmfTransGeneral('.month.when.' . $date->format('m')));
            if (
                $ignoreYear === true //< ignore any year
                || ($ignoreYear === 'current' && $date->isCurrentYear()) //< ignore current year
                || (is_numeric($ignoreYear) && (int)$ignoreYear === $date->year) //< ignore certain year ($ignoreYear)
            ) {
                $year = '';
            } else {
                switch ($yearSuffix) {
                    case 'short':
                        $yearSuffix = (string)cmfTransGeneral('.year_suffix.short');
                        break;
                    case 'full':
                        $yearSuffix = (string)cmfTransGeneral('.year_suffix.full');
                        break;
                    case 'none':
                        $yearSuffix = '';
                }
                $year = $date->year . $yearSuffix;
            }
            $dateStr = rtrim("{$date->day} {$month} {$year}");
            $timeStr = ($addTime ? ' ' . ltrim(cmfTransGeneral('.time.at') . $date->format(' H:i')) : '');
            return $dateStr . $timeStr;
        } else {
            return date('H:i d F Y') . (in_array($yearSuffix, ['short', 'full', 'none'], true) ? '' : $yearSuffix);
        }
    }
}

if (!function_exists('formatMoney')) {
    /**
     * @param float  $number
     * @param int    $decimals
     * @param string $thousandsSeparator
     * @return string
     */
    function formatMoney(float $number, int $decimals = 2, string $thousandsSeparator = ' '): string {
        return number_format($number, $decimals, '.', $thousandsSeparator);
    }
}

if (!function_exists('formatSeconds')) {
    /**
     * @param int $seconds
     * @param bool $displaySeconds - true: display "days hours minutes seconds"; false: display "days hours minutes"
     * @param bool $shortLabels - true: use shortened labels (min, sec, hr, d) | false: user full lables (days, hours, minutes, seconds)
     * @return string
     */
    function formatSeconds(int $seconds, bool $displaySeconds = true, bool $shortLabels = true): string {
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
     * @param array $translations
     *      - associative array format ($isAssociativeArray = true): ['lang1_code' => 'translation1', 'lang2_code' => 'translation2', ...]
     *      - indexed array format ($isAssociativeArray = false): [ ['key' => 'lang1_code', 'value' => 'translation1'], ...]
     * @param null|string $default - default value to return when there is no translation for app()->getLocale()
     *      language and for CmfConfig::getPrimary()->default_locale()
     * @param bool $isAssociativeArray
     *      - true: $translations keys = language codes, values = translations;
     *      - false: $translations values = arrays with 2 keys: 'key' and 'value';
     * @return string|null
     */
    function pickLocalization(array $translations, $default = null, bool $isAssociativeArray = true): ?string {
        $langCodes = [app()->getLocale(), cmfConfig()->default_locale()];
        foreach ($langCodes as $langCode) {
            if ($isAssociativeArray) {
                if (
                    array_key_exists($langCode, $translations)
                    && is_string($translations[$langCode])
                    && trim($translations[$langCode]) !== ''
                ) {
                    return $translations[$langCode];
                }
            } else {
                foreach ($translations as $translation) {
                    if (
                        isset($translation['key'])
                        && $translation['key'] === $langCode
                        && !empty($translation['value'])
                        && trim($translation['value']) !== ''
                    ) {
                        return $translation['value'];
                    }
                }
            }
        }
        return $default;
    }

}

if (!function_exists('pickLocalizationFromJson')) {
    /**
     * Pick correct localization strings from specially formatted array. Useful for localizations stored in DB
     * @param string|array $translationsJson - format: '{"lang1_code": "translation1", "lang2_code": "translation2", ...}'
     * @param null|string $default - default value to return when there is no translation for app()->getLocale()
     *      language and for CmfConfig::getPrimary()->default_locale()
     * @param bool $isAssociativeArray
     *      - true: $translations keys = language codes, values = translations;
     *      - false: $translations values = arrays with 2 keys: 'key' and 'value';
     * @see pickLocalization()
     * @return string|null
     */
    function pickLocalizationFromJson($translationsJson, $default = null, bool $isAssociativeArray = true): ?string {
        $translations = is_array($translationsJson) ? $translationsJson : json_decode($translationsJson, true);
        return is_array($translations) ? $default : pickLocalization($translations, $default, $isAssociativeArray);
    }

}

if (!function_exists('setting')) {

    /**
     * Get value for CmfSetting called $name (CmfSetting->key === $name)
     * @param string $name - setting name
     * @param mixed $default - default value
     * @return mixed|\PeskyCMF\PeskyCmfAppSettings|\App\AppSettings
     */
    function setting(?string $name = null, $default = null) {
        $appSettings = cmfConfig()->getAppSettings();
        if ($name === null) {
            return $appSettings;
        } else {
            return $appSettings::$name($default);
        }
    }
}

if (!function_exists('hidePasswords')) {

    /**
     * @param array $data
     * @return array
     */
    function hidePasswords(array $data): array {
        foreach ($data as $key => &$value) {
            if (!empty($value) && preg_match('(pass(word|phrase|wd)?|pwd)', $key)) {
                $value = '******';
            }
        }
        return $data;
    }
}

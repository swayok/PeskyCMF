<?php

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
     * @return \PeskyCMF\Config\CmfConfig
     */
    function cmfConfig() {
        return \PeskyCMF\Config\CmfConfig::getPrimary();
    }
}

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
            $cmfConfig = cmfConfig();
        }
        return route($cmfConfig::getRouteName($routeName), $parameters, $absolute);
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
     * @param null|\PeskyCMF\Config\CmfConfig $cmfConfig
     * @return string
     */
    function cmfRouteTpl($routeName, array $parameters = [], array $tplParams = [], $absolute = false, $cmfConfig = null) {
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
        if (!$cmfConfig) {
            $cmfConfig = cmfConfig();
        }
        $url = route($cmfConfig::getRouteName($routeName), $parameters, $absolute);
        return str_replace(array_keys($replaces), array_values($replaces), $url);
    }
}

if (!function_exists('routeToCmfPage')) {
    /**
     * @param string $pageId
     * @param array $queryArgs
     * @param bool $absolute
     * @param null|\PeskyCMF\Config\CmfConfig $cmfConfig
     * @param bool $ignoreAccessPolicy - true: will not run Gate::denies('resource.*') test
     * @return string
     */
    function routeToCmfPage($pageId, array $queryArgs = [], $absolute = false, $cmfConfig = null, $ignoreAccessPolicy = false) {
        if (!$ignoreAccessPolicy && Gate::denies('cmf_page', [$pageId])) {
            return null;
        }
        if (!$cmfConfig) {
            $cmfConfig = cmfConfig();
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
     *      Values may contain dotjs inserts in format: {{= it.id }} or {= it.id }
     *      Note: Operator is 'equals' (col1 = val1). Multiple filters joined by 'AND' (col1 = val1 AND col2 = val2)
     * @param bool $absolute
     * @param null|\PeskyCMF\Config\CmfConfig $cmfConfig
     * @param bool $ignoreAccessPolicy - true: will not run Gate::denies('resource.*') test
     * @return string
     */
    function routeToCmfItemsTable($tableName, array $filters = [], $absolute = false, $cmfConfig = null, $ignoreAccessPolicy = false) {
        if (!$ignoreAccessPolicy && Gate::denies('resource.view', [$tableName])) {
            return null;
        }
        $params = ['table_name' => $tableName];
        $replaces = [];
        if (!empty($filters)) {
            $params['filter'] = json_encode($filters, JSON_UNESCAPED_UNICODE);
            if (preg_match_all('%' . DOTJS_INSERT_REGEXP_FOR_ROUTES . '%s', $params['filter'], $matches) > 0) {
                // there are dotJs inserts inside filters
                foreach ($matches[1] as $i => $dotJsInsert) {
                    $replace = '__dotjs_' . (string)$i . '_insert__';
                    $replaces[$replace] = '{{' . trim($matches[$i][0], '{} ') . '}}';
                    $params['filter'] = str_replace($matches[$i][0], $replace, $params['filter']);
                }
            }
        }
        if (!$cmfConfig) {
            $cmfConfig = cmfConfig();
        }
        $url = route($cmfConfig::getRouteName('cmf_items_table'), $params, $absolute);
        return str_replace(array_keys($replaces), array_values($replaces), $url);
    }
}

if (!function_exists('routeToCmfTableCustomData')) {
    /**
     * @param string $tableName
     * @param string $dataId - identifier of data to be returned. For example: 'special_options'
     * @param bool $absolute
     * @param null|\PeskyCMF\Config\CmfConfig $cmfConfig
     * @param bool $ignoreAccessPolicy - true: will not run Gate::denies('resource.*') test
     * @return string
     */
    function routeToCmfTableCustomData($tableName, $dataId, $absolute = false, $cmfConfig = null, $ignoreAccessPolicy = false) {
        if (!$ignoreAccessPolicy && Gate::denies('resource.view', [$tableName])) {
            return null;
        }
        if (!$cmfConfig) {
            $cmfConfig = cmfConfig();
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
     * @param array $data - data for form inputs to be used as default values; may contain dotjs inserts
     *       as values in format: '{{= it.id }}' or '{= it.id }'
     * @param bool $absolute
     * @param null|\PeskyCMF\Config\CmfConfig $cmfConfig
     * @param bool $ignoreAccessPolicy - true: will not run Gate::denies('resource.*') test
     * @return string
     */
    function routeToCmfItemAddForm($tableName, array $data = [], $absolute = false, $cmfConfig = null, $ignoreAccessPolicy = false) {
        if (!$ignoreAccessPolicy && Gate::denies('resource.create', [$tableName])) {
            return null;
        }
        if (!$cmfConfig) {
            $cmfConfig = cmfConfig();
        }
        $params = ['table_name' => $tableName];
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
                $value = '__dotjs_' . (string)(count($replaces) + 1) . '_insert__';
                $replaces[$value] = '{{' . trim($matches[0], '{} ') . '}}';
            }
            $params[$key] = $value;
        }
        $url = route($cmfConfig::getRouteName('cmf_item_add_form'), $params, $absolute);
        return str_replace(array_keys($replaces), array_values($replaces), $url);
    }
}

if (!function_exists('routeToCmfItemEditForm')) {
    /**
     * @param string $tableName
     * @param int|string $itemId - it may be a dotjs insert in format: '{{= it.id }}' or '{= it.id }'
     * @param bool $absolute
     * @param null|\PeskyCMF\Config\CmfConfig $cmfConfig
     * @param bool $ignoreAccessPolicy - true: will not run Gate::denies('resource.*') test
     * @return string
     */
    function routeToCmfItemEditForm($tableName, $itemId, $absolute = false, $cmfConfig = null, $ignoreAccessPolicy = false) {
        if (!$ignoreAccessPolicy && Gate::denies('resource.update', [$tableName, $itemId])) {
            return null;
        }
        if (!$cmfConfig) {
            $cmfConfig = cmfConfig();
        }
        $itemDotJs = preg_match('%^\s*' . DOTJS_INSERT_REGEXP_FOR_ROUTES . '\s*$%s', $itemId) ? '__dotjs_item_id_insert__' : $itemId;
        $url = route($cmfConfig::getRouteName('cmf_item_edit_form'), ['table_name' => $tableName, 'id' => $itemDotJs], $absolute);
        return str_replace('__dotjs_item_id_insert__', $itemId, $url);
    }
}

if (!function_exists('routeToCmfItemCloneForm')) {
    /**
     * @param string $tableName
     * @param int|string $itemId - it may be a dotjs insert in format: '{{= it.id }}' or '{= it.id }'
     * @param bool $absolute
     * @param null|\PeskyCMF\Config\CmfConfig $cmfConfig
     * @param bool $ignoreAccessPolicy - true: will not run Gate::denies('resource.*') test
     * @return string
     */
    function routeToCmfItemCloneForm($tableName, $itemId, $absolute = false, $cmfConfig = null, $ignoreAccessPolicy = false) {
        if (!$ignoreAccessPolicy && Gate::denies('resource.create', [$tableName, $itemId])) {
            return null;
        }
        if (!$cmfConfig) {
            $cmfConfig = cmfConfig();
        }
        $itemDotJs = preg_match('%^\s*' . DOTJS_INSERT_REGEXP_FOR_ROUTES . '\s*$%s', $itemId) ? '__dotjs_item_id_insert__' : $itemId;
        $url = route($cmfConfig::getRouteName('cmf_item_clone_form'), ['table_name' => $tableName, 'id' => $itemDotJs], $absolute);
        return str_replace('__dotjs_item_id_insert__', $itemId, $url);
    }
}

if (!function_exists('routeToCmfItemDetails')) {
    /**
     * @param string $tableName
     * @param int|string $itemId - it may be a dotjs insert in format: '{{= it.id }}' or '{= it.id }'
     * @param bool $absolute
     * @param null|\PeskyCMF\Config\CmfConfig $cmfConfig
     * @param bool $ignoreAccessPolicy - true: will not run Gate::denies('resource.*') test
     * @return string
     */
    function routeToCmfItemDetails($tableName, $itemId, $absolute = false, $cmfConfig = null, $ignoreAccessPolicy = false) {
        if (!$ignoreAccessPolicy && Gate::denies('resource.details', [$tableName, $itemId])) {
            return null;
        }
        if (!$cmfConfig) {
            $cmfConfig = cmfConfig();
        }
        $itemDotJs = preg_match('%^\s*' . DOTJS_INSERT_REGEXP_FOR_ROUTES . '\s*$%s', $itemId) ? '__dotjs_item_id_insert__' : $itemId;
        $url = route($cmfConfig::getRouteName('cmf_item_details'), ['table_name' => $tableName, 'id' => $itemDotJs], $absolute);
        return str_replace('__dotjs_item_id_insert__', $itemId, $url);
    }
}

if (!function_exists('routeToCmfItemDelete')) {
    /**
     * @param string $tableName
     * @param int|string $itemId - it may be a dotjs insert in format: '{{= it.id }}' or '{= it.id }'
     * @param bool $absolute
     * @param null|\PeskyCMF\Config\CmfConfig $cmfConfig
     * @param bool $ignoreAccessPolicy - true: will not run Gate::denies('resource.*') test
     * @return string
     */
    function routeToCmfItemDelete($tableName, $itemId, $absolute = false, $cmfConfig = null, $ignoreAccessPolicy = false) {
        if (!$ignoreAccessPolicy && Gate::denies('resource.delete', [$tableName, $itemId])) {
            return null;
        }
        if (!$cmfConfig) {
            $cmfConfig = cmfConfig();
        }
        $itemDotJs = preg_match('%^\s*' . DOTJS_INSERT_REGEXP_FOR_ROUTES . '\s*$%s', $itemId) ? '__dotjs_item_id_insert__' : $itemId;
        $url = route($cmfConfig::getRouteName('cmf_api_delete_item'), ['table_name' => $tableName, 'id' => $itemDotJs], $absolute);
        return str_replace('__dotjs_item_id_insert__', $itemId, $url);
    }
}

if (!function_exists('routeToCmfResourceCustomPage')) {
    /**
     * @param string $tableName
     * @param int|string $itemId - it may be a dotjs insert in format: '{{= it.id }}' or '{= it.id }'
     * @param string $pageId
     * @param array $queryArgs - may contain dotjs inserts in format: '{{= it.id }}' or '{= it.id }'
     * @param bool $absolute
     * @param null|\PeskyCMF\Config\CmfConfig $cmfConfig
     * @return string
     */
    function routeToCmfResourceCustomPage($tableName, $pageId, array $queryArgs = [], $absolute = false, $cmfConfig = null) {
        if (!$cmfConfig) {
            $cmfConfig = cmfConfig();
        }
        $queryArgsEscaped = [];
        $replaces = [];
        if (!empty($queryArgs)) {
            $json = json_encode($queryArgs, JSON_UNESCAPED_UNICODE);
            if (preg_match_all('%' . DOTJS_INSERT_REGEXP_FOR_ROUTES . '%s', $json, $matches) > 0) {
                // there are dotJs inserts inside filters
                foreach ($matches[1] as $i => $dotJsInsert) {
                    $replace = '__dotjs_' . (string)$i . '_insert__';
                    $replaces[$replace] = '{{' . trim($matches[$i][0], '{} ') . '}}';
                    $json = str_replace($replaces[$replace], $replace, $json);
                }
                $queryArgsEscaped = json_decode($json, true);
            }
        }
        $url = route(
            $cmfConfig::getRouteName('cmf_resource_custom_page'),
            array_merge(
                ['table_name' => $tableName, 'page' => $pageId],
                $queryArgsEscaped
            ),
            $absolute
        );
        if (!empty($replaces)) {
            $url = str_replace(array_keys($replaces), array_values($replaces), $url);
        }
        return $url;
    }
}

if (!function_exists('routeToCmfItemCustomPage')) {
    /**
     * @param string $tableName
     * @param int|string $itemId - it may be a dotjs insert in format: '{{= it.id }}' or '{= it.id }'
     * @param string $pageId
     * @param array $queryArgs - may contain dotjs inserts in format: '{{= it.id }}' or '{= it.id }'
     * @param bool $absolute
     * @param null|\PeskyCMF\Config\CmfConfig $cmfConfig
     * @return string
     */
    function routeToCmfItemCustomPage($tableName, $itemId, $pageId, array $queryArgs = [], $absolute = false, $cmfConfig = null) {
        if (!$cmfConfig) {
            $cmfConfig = cmfConfig();
        }
        $itemDotJs = preg_match('%^\s*' . DOTJS_INSERT_REGEXP_FOR_ROUTES . '\s*$%s', $itemId) ? '__dotjs_item_id_insert__' : $itemId;
        $queryArgsEscaped = [];
        $replaces = [];
        if (!empty($queryArgs)) {
            $json = json_encode($queryArgs, JSON_UNESCAPED_UNICODE);
            if (preg_match_all('%' . DOTJS_INSERT_REGEXP_FOR_ROUTES . '%s', $json, $matches) > 0) {
                // there are dotJs inserts inside filters
                foreach ($matches[1] as $i => $dotJsInsert) {
                    $replace = '__dotjs_' . (string)$i . '_insert__';
                    $replaces[$replace] = '{{' . trim($matches[$i][0], '{} ') . '}}';
                    $json = str_replace($replaces[$replace], $replace, $json);
                }
                $queryArgsEscaped = json_decode($json, true);
            }
        }
        $url = route(
            $cmfConfig::getRouteName('cmf_item_custom_page'),
            array_merge(
                ['table_name' => $tableName, 'id' => $itemDotJs, 'page' => $pageId],
                $queryArgsEscaped
            ),
            $absolute
        );
        $url = str_replace('__dotjs_item_id_insert__', $itemId, $url);
        if (!empty($replaces)) {
            $url = str_replace(array_keys($replaces), array_values($replaces), $url);
        }
        return $url;
    }
}

if (!function_exists('routeToCmfItemCustomAction')) {
    /**
     * @param string $tableName
     * @param int|string $itemId - it may be a dotjs insert in format: '{{= it.id }}' or '{= it.id }'
     * @param string $actionId
     * @param array $queryArgs - may contain dotjs inserts in format: '{{= it.id }}' or '{= it.id }'
     * @param bool $absolute
     * @param null|\PeskyCMF\Config\CmfConfig $cmfConfig
     * @return string
     */
    function routeToCmfItemCustomAction($tableName, $itemId, $actionId, array $queryArgs = [], $absolute = false, $cmfConfig = null) {
        if (!$cmfConfig) {
            $cmfConfig = cmfConfig();
        }
        $itemDotJs = preg_match('%^\s*' . DOTJS_INSERT_REGEXP_FOR_ROUTES . '\s*$%s', $itemId) ? '__dotjs_item_id_insert__' : $itemId;
        $queryArgsEscaped = [];
        $replaces = [];
        if (!empty($queryArgs)) {
            $json = json_encode($queryArgs, JSON_UNESCAPED_UNICODE);
            if (preg_match_all('%' . DOTJS_INSERT_REGEXP_FOR_ROUTES . '%s', $json, $matches) > 0) {
                // there are dotJs inserts inside filters
                foreach ($matches[1] as $i => $dotJsInsert) {
                    $replace = '__dotjs_' . (string)$i . '_insert__';
                    $replaces[$replace] = '{{' . trim($matches[$i][0], '{} ') . '}}';
                    $json = str_replace($replaces[$replace], $replace, $json);
                }
                $queryArgsEscaped = json_decode($json, true);
            }
        }
        $url = route(
            $cmfConfig::getRouteName('cmf_api_item_custom_action'),
            array_merge(
                ['table_name' => $tableName, 'id' => $itemDotJs, 'action' => $actionId],
                $queryArgsEscaped
            ),
            $absolute
        );
        $url = str_replace('__dotjs_item_id_insert__', $itemId, $url);
        if (!empty($replaces)) {
            $url = str_replace(array_keys($replaces), array_values($replaces), $url);
        }
        return $url;
    }
}

if (!function_exists('routeToCmfResourceCustomAction')) {
    /**
     * @param string $tableName
     * @param int|string $itemId - it may be a dotjs insert in format: '{{= it.id }}' or '{= it.id }'
     * @param string $actionId
     * @param array $queryArgs - may contain dotjs inserts in format: '{{= it.id }}' or '{= it.id }'
     * @param bool $absolute
     * @param null|\PeskyCMF\Config\CmfConfig $cmfConfig
     * @return string
     */
    function routeToCmfResourceCustomAction($tableName, $actionId, array $queryArgs = [], $absolute = false, $cmfConfig = null) {
        if (!$cmfConfig) {
            $cmfConfig = cmfConfig();
        }
        $queryArgsEscaped = [];
        $replaces = [];
        if (!empty($queryArgs)) {
            $json = json_encode($queryArgs, JSON_UNESCAPED_UNICODE);
            if (preg_match_all('%' . DOTJS_INSERT_REGEXP_FOR_ROUTES . '%s', $json, $matches) > 0) {
                // there are dotJs inserts inside filters
                foreach ($matches[1] as $i => $dotJsInsert) {
                    $replace = '__dotjs_' . (string)$i . '_insert__';
                    $replaces[$replace] = '{{' . trim($matches[$i][0], '{} ') . '}}';
                    $json = str_replace($replaces[$replace], $replace, $json);
                }
                $queryArgsEscaped = json_decode($json, true);
            }
        }
        $url = route(
            $cmfConfig::getRouteName('cmf_api_resource_custom_action'),
            array_merge(
                ['table_name' => $tableName, 'action' => $actionId],
                $queryArgsEscaped
            ),
            $absolute
        );
        if (!empty($replaces)) {
            $url = str_replace(array_keys($replaces), array_values($replaces), $url);
        }
        return $url;
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
            $message = cmfTransGeneral('.form.message.validation_errors');
        }
        return cmfJsonResponse(\PeskyCMF\HttpCode::CANNOT_PROCESS)
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
            $message = cmfTransGeneral('.message.http404');
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
     * @return \Illuminate\Http\RedirectResponse|\PeskyCMF\Http\CmfJsonResponse
     */
    function cmfRedirectResponseWithMessage($url, $message, $type = 'info') {
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
            return cmfTransGeneral('.message.invalid_date_received');
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
    function pickLocalization(array $translations, $default = null, $isAssociativeArray = true) {
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
     * @param string $translationsJson - format: '{"lang1_code": "translation1", "lang2_code": "translation2", ...}'
     * @param null|string $default - default value to return when there is no translation for app()->getLocale()
     *      language and for CmfConfig::getPrimary()->default_locale()
     * @param bool $isAssociativeArray
     *      - true: $translations keys = language codes, values = translations;
     *      - false: $translations values = arrays with 2 keys: 'key' and 'value';
     * @see pickLocalization()
     * @return string|null
     */
    function pickLocalizationFromJson($translationsJson, $default = null, $isAssociativeArray = true) {
        $translations = json_decode($translationsJson, true);
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
    function setting($name = null, $default = null) {
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
    function hidePasswords(array $data) {
        foreach ($data as $key => &$value) {
            if (!empty($value) && preg_match('(pass(word|phrase|wd)?|pwd)', $key)) {
                $value = '******';
            }
        }
        return $data;
    }
}
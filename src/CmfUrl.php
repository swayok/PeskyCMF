<?php

declare(strict_types=1);

namespace PeskyCMF;

use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Http\RedirectResponse;
use PeskyCMF\Config\CmfConfig;

abstract class CmfUrl
{
    
    protected static function getCmfConfig(?CmfConfig $cmfConfig = null): CmfConfig
    {
        return $cmfConfig ?: CmfConfig::getPrimary();
    }
    
    protected static function getAuthGate(?CmfConfig $cmfConfig): GateContract
    {
        return static::getCmfConfig($cmfConfig)->getLaravelApp()->make(GateContract::class);
    }
    
    /**
     * @param string $routeName
     * @param array $parameters
     * @param bool $absolute
     * @param null|CmfConfig|string $cmfConfig
     * @return string
     */
    public static function route(string $routeName, array $parameters = [], bool $absolute = false, ?CmfConfig $cmfConfig = null): string
    {
        return static::getCmfConfig($cmfConfig)->route($routeName, $parameters, $absolute);
    }
    
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
    public static function routeTpl(
        string $routeName,
        array $parameters = [],
        array $tplParams = [],
        bool $absolute = false,
        ?CmfConfig $cmfConfig = null
    ): string {
        $replaces = [];
        $i = 1;
        foreach ($tplParams as $name => $tplName) {
            $dotJsVarPrefix = '';
            if (is_numeric($name)) {
                $name = $tplName;
                $dotJsVarPrefix = 'it.';
            }
            $parameters[$name] = '__dotjs_' . $i . '_insert__';
            $i++;
            $replaces[$parameters[$name]] = "{{= {$dotJsVarPrefix}{$tplName} }}";
        }
        
        $url = static::route($routeName, $parameters, $absolute, $cmfConfig);
        return str_replace(array_keys($replaces), array_values($replaces), $url);
    }
    
    /**
     * @param string $pageId
     * @param array $queryArgs
     * @param bool $absolute
     * @param null|CmfConfig $cmfConfig
     * @param bool $ignoreAccessPolicy - true: will not run static::getAuthGate($cmfConfig)->denies('resource.*') test
     * @return string|null
     */
    public static function toPage(
        string $pageId,
        array $queryArgs = [],
        bool $absolute = false,
        ?CmfConfig $cmfConfig = null,
        bool $ignoreAccessPolicy = false
    ): ?string {
        if (!$ignoreAccessPolicy && static::getAuthGate($cmfConfig)->denies('cmf_page', [$pageId])) {
            return null;
        }
        return static::route('cmf_page', array_merge(['page' => $pageId], $queryArgs), $absolute, $cmfConfig);
    }
    
    /**
     * @param string $pageId
     * @param array $queryArgs
     * @param bool $absolute
     * @param null|CmfConfig $cmfConfig
     * @return RedirectResponse
     */
    public static function redirectToPage(string $pageId, array $queryArgs = [], bool $absolute = false, ?CmfConfig $cmfConfig = null): RedirectResponse
    {
        $url = routeToCmfPage($pageId, $queryArgs, $absolute, $cmfConfig);
        if (!$url) {
            abort(HttpCode::FORBIDDEN);
        }
        return new RedirectResponse($url);
    }
    
    /**
     * @param string $resourceName
     * @param array $filters - key-value array where key is column name to add to filter and value is column's value.
     *      Values may contain dotjs inserts in format: {{= it.id }} or {= it.id }
     *      Note: Operator is 'equals' (col1 = val1). Multiple filters joined by 'AND' (col1 = val1 AND col2 = val2)
     * @param bool $absolute
     * @param null|CmfConfig $cmfConfig
     * @param bool $ignoreAccessPolicy - true: will not run static::getAuthGate($cmfConfig)->denies('resource.*') test
     * @return string|null
     */
    public static function toItemsTable(
        string $resourceName,
        array $filters = [],
        bool $absolute = false,
        ?CmfConfig $cmfConfig = null,
        bool $ignoreAccessPolicy = false
    ): ?string {
        if (!$ignoreAccessPolicy && static::getAuthGate($cmfConfig)->denies('resource.view', [$resourceName])) {
            return null;
        }
        $params = [
            'resource' => $resourceName,
        ];
        $replaces = static::replaceDotJsInstertsInArrayValuesByUrlSafeInserts($filters);
        if (!empty($filters)) {
            $params['filter'] = json_encode($replaces['data']);
        }
        $url = static::route('cmf_items_table', $params, $absolute, $cmfConfig);
        return static::replaceUrlSafeInsertsInUrlByDotJsInsterts($url, $replaces['replaces']);
    }
    
    /**
     * @param string $resourceName
     * @param string $dataId - identifier of data to be returned. For example: 'special_options'
     * @param bool $absolute
     * @param null|CmfConfig $cmfConfig
     * @param bool $ignoreAccessPolicy - true: will not run static::getAuthGate($cmfConfig)->denies('resource.*') test
     * @return string|null
     */
    public static function toTableCustomData(
        string $resourceName,
        string $dataId,
        bool $absolute = false,
        ?CmfConfig $cmfConfig = null,
        bool $ignoreAccessPolicy = false
    ): ?string {
        if (!$ignoreAccessPolicy && static::getAuthGate($cmfConfig)->denies('resource.view', [$resourceName])) {
            return null;
        }
        return static::route(
            'cmf_api_get_custom_data',
            array_merge(['resource' => $resourceName, 'data_id' => $dataId]),
            $absolute,
            $cmfConfig
        );
    }
    
    /**
     * @param string $resourceName
     * @param array $data - data for form inputs to be used as default values; may contain dotjs inserts
     *       as values in format: '{{= it.id }}' or '{= it.id }'
     * @param bool $absolute
     * @param null|CmfConfig $cmfConfig
     * @param bool $ignoreAccessPolicy - true: will not run static::getAuthGate($cmfConfig)->denies('resource.*') test
     * @return string|null
     */
    public static function toItemAddForm(
        string $resourceName,
        array $data = [],
        bool $absolute = false,
        ?CmfConfig $cmfConfig = null,
        bool $ignoreAccessPolicy = false
    ): ?string {
        if (!$ignoreAccessPolicy && static::getAuthGate($cmfConfig)->denies('resource.create', [$resourceName])) {
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
        $url = static::route('cmf_item_add_form', $params, $absolute, $cmfConfig);
        return str_replace(array_keys($replaces), array_values($replaces), $url);
    }
    
    /**
     * @param string $resourceName
     * @param int|string $itemId - it may be a dotjs insert in format: '{{= it.id }}' or '{= it.id }'
     * @param bool $absolute
     * @param null|CmfConfig $cmfConfig
     * @param bool $ignoreAccessPolicy - true: will not run static::getAuthGate($cmfConfig)->denies('resource.*') test
     * @return string|null
     */
    public static function toItemEditForm(
        string $resourceName,
        string $itemId,
        bool $absolute = false,
        ?CmfConfig $cmfConfig = null,
        bool $ignoreAccessPolicy = false
    ): ?string {
        if (!$ignoreAccessPolicy && static::getAuthGate($cmfConfig)->denies('resource.update', [$resourceName, $itemId])) {
            return null;
        }
        return static::routeWithPossibleItemIdDotJsInsert(
            'cmf_item_edit_form',
            $itemId,
            ['resource' => $resourceName],
            $absolute,
            $cmfConfig
        );
    }
    
    /**
     * @param string $resourceName
     * @param string $inputName
     * @param bool $absolute
     * @param null|CmfConfig $cmfConfig
     * @param bool $ignoreAccessPolicy - true: will not run static::getAuthGate($cmfConfig)->denies('resource.*') test
     * @return string|null
     */
    public static function toTempFileUpload(
        string $resourceName,
        string $inputName,
        bool $absolute = false,
        ?CmfConfig $cmfConfig = null,
        bool $ignoreAccessPolicy = false
    ): ?string {
        if (!$ignoreAccessPolicy && static::getAuthGate($cmfConfig)->denies('resource.create', [$resourceName])) {
            return null;
        }
        return static::route(
            'cmf_upload_temp_file_for_input',
            ['resource' => $resourceName, 'input' => $inputName],
            $absolute,
            $cmfConfig
        );
    }
    
    /**
     * @param string $resourceName
     * @param string $inputName
     * @param bool $absolute
     * @param null|CmfConfig $cmfConfig
     * @param bool $ignoreAccessPolicy - true: will not run static::getAuthGate($cmfConfig)->denies('resource.*') test
     * @return string|null
     */
    public static function toTempFileDelete(
        string $resourceName,
        string $inputName,
        bool $absolute = false,
        ?CmfConfig $cmfConfig = null,
        bool $ignoreAccessPolicy = false
    ): ?string {
        if (!$ignoreAccessPolicy && static::getAuthGate($cmfConfig)->denies('resource.create', [$resourceName])) {
            return null;
        }
        return static::route(
            'cmf_delete_temp_file_for_input',
            ['resource' => $resourceName, 'input' => $inputName],
            $absolute,
            $cmfConfig
        );
    }
    
    /**
     * @param string $resourceName
     * @param int|string $itemId - it may be a dotjs insert in format: '{{= it.id }}' or '{= it.id }'
     * @param bool $absolute
     * @param null|CmfConfig $cmfConfig
     * @param bool $ignoreAccessPolicy - true: will not run static::getAuthGate($cmfConfig)->denies('resource.*') test
     * @return string|null
     */
    public static function toItemCloneForm(
        string $resourceName,
        string $itemId,
        bool $absolute = false,
        ?CmfConfig $cmfConfig = null,
        bool $ignoreAccessPolicy = false
    ): ?string {
        if (!$ignoreAccessPolicy && static::getAuthGate($cmfConfig)->denies('resource.create', [$resourceName, $itemId])) {
            return null;
        }
        return static::routeWithPossibleItemIdDotJsInsert(
            'cmf_item_clone_form',
            $itemId,
            ['resource' => $resourceName],
            $absolute,
            $cmfConfig
        );
    }
    
    /**
     * @param string $resourceName
     * @param int|string $itemId - it may be a dotjs insert in format: '{{= it.id }}' or '{= it.id }'
     * @param bool $absolute
     * @param null|CmfConfig $cmfConfig
     * @param bool $ignoreAccessPolicy - true: will not run static::getAuthGate($cmfConfig)->denies('resource.*') test
     * @return string|null
     */
    public static function toItemDetails(
        string $resourceName,
        string $itemId,
        bool $absolute = false,
        ?CmfConfig $cmfConfig = null,
        bool $ignoreAccessPolicy = false
    ): ?string {
        if (!$ignoreAccessPolicy && static::getAuthGate($cmfConfig)->denies('resource.details', [$resourceName, $itemId])) {
            return null;
        }
        return static::routeWithPossibleItemIdDotJsInsert(
            'cmf_item_details',
            $itemId,
            ['resource' => $resourceName],
            $absolute,
            $cmfConfig
        );
    }
    
    
    /**
     * @param string $resourceName
     * @param int|string $itemId - it may be a dotjs insert in format: '{{= it.id }}' or '{= it.id }'
     * @param bool $absolute
     * @param null|CmfConfig $cmfConfig
     * @param bool $ignoreAccessPolicy - true: will not run static::getAuthGate($cmfConfig)->denies('resource.*') test
     * @return string|null
     */
    public static function toItemDelete(
        string $resourceName,
        string $itemId,
        bool $absolute = false,
        ?CmfConfig $cmfConfig = null,
        bool $ignoreAccessPolicy = false
    ): ?string {
        if (!$ignoreAccessPolicy && static::getAuthGate($cmfConfig)->denies('resource.delete', [$resourceName, $itemId])) {
            return null;
        }
        return static::routeWithPossibleItemIdDotJsInsert(
            'cmf_api_delete_item',
            $itemId,
            ['resource' => $resourceName],
            $absolute,
            $cmfConfig
        );
    }
    
    /**
     * @param string $resourceName
     * @param string $pageId
     * @param array $queryArgs - may contain dotjs inserts in format: '{{= it.id }}' or '{= it.id }'
     * @param bool $absolute
     * @param null|CmfConfig $cmfConfig
     * @return string
     */
    public static function toResourceCustomPage(
        string $resourceName,
        string $pageId,
        array $queryArgs = [],
        bool $absolute = false,
        ?CmfConfig $cmfConfig = null
    ): string {
        $replaces = static::replaceDotJsInstertsInArrayValuesByUrlSafeInserts($queryArgs);
        $url = static::route(
            'cmf_resource_custom_page',
            array_merge(
                ['resource' => $resourceName, 'page' => $pageId],
                $replaces['data']
            ),
            $absolute,
            $cmfConfig
        );
        return static::replaceUrlSafeInsertsInUrlByDotJsInsterts($url, $replaces['replaces']);
    }
    
    /**
     * @param string $resourceName
     * @param int|string $itemId - it may be a dotjs insert in format: '{{= it.id }}' or '{= it.id }'
     * @param string $pageId
     * @param array $queryArgs - may contain dotjs inserts in format: '{{= it.id }}' or '{= it.id }'
     * @param bool $absolute
     * @param null|CmfConfig $cmfConfig
     * @return string
     */
    public static function toItemCustomPage(
        string $resourceName,
        string $itemId,
        string $pageId,
        array $queryArgs = [],
        bool $absolute = false,
        ?CmfConfig $cmfConfig = null
    ): string {
        $itemDotJs = $itemId;
        if (preg_match('%^\s*' . DOTJS_INSERT_REGEXP_FOR_ROUTES . '\s*$%s', $itemId)) {
            $itemDotJs = '__dotjs_item_id_insert__';
            $itemId = '{{' . trim($itemId, '{} ') . '}}';
        }
        $replaces = static::replaceDotJsInstertsInArrayValuesByUrlSafeInserts($queryArgs);
        $url = static::route(
            'cmf_item_custom_page',
            array_merge(
                ['resource' => $resourceName, 'id' => $itemDotJs, 'page' => $pageId],
                $replaces['data']
            ),
            $absolute,
            $cmfConfig
        );
        $url = str_replace('__dotjs_item_id_insert__', $itemId, $url);
        return static::replaceUrlSafeInsertsInUrlByDotJsInsterts($url, $replaces['replaces']);
    }
    
    /**
     * @param string $resourceName
     * @param int|string $itemId - it may be a dotjs insert in format: '{{= it.id }}' or '{= it.id }'
     * @param string $actionId
     * @param array $queryArgs - may contain dotjs inserts in format: '{{= it.id }}' or '{= it.id }'
     * @param bool $absolute
     * @param null|CmfConfig $cmfConfig
     * @return string
     */
    public static function toItemCustomAction(
        string $resourceName,
        string $itemId,
        string $actionId,
        array $queryArgs = [],
        bool $absolute = false,
        ?CmfConfig $cmfConfig = null
    ): string {
        $itemDotJs = $itemId;
        if (preg_match('%^\s*' . DOTJS_INSERT_REGEXP_FOR_ROUTES . '\s*$%s', $itemId)) {
            $itemDotJs = '__dotjs_item_id_insert__';
            $itemId = '{{' . trim($itemId, '{} ') . '}}';
        }
        $replaces = static::replaceDotJsInstertsInArrayValuesByUrlSafeInserts($queryArgs);
        $url = static::route(
            'cmf_api_item_custom_action',
            array_merge(
                ['resource' => $resourceName, 'id' => $itemDotJs, 'action' => $actionId],
                $replaces['data']
            ),
            $absolute,
            $cmfConfig
        );
        $url = str_replace('__dotjs_item_id_insert__', $itemId, $url);
        return static::replaceUrlSafeInsertsInUrlByDotJsInsterts($url, $replaces['replaces']);
    }
    
    /**
     * @param string $resourceName
     * @param string $actionId
     * @param array $queryArgs - may contain dotjs inserts in format: '{{= it.id }}' or '{= it.id }'
     * @param bool $absolute
     * @param null|CmfConfig $cmfConfig
     * @return string
     */
    public static function toResourceCustomAction(
        string $resourceName,
        string $actionId,
        array $queryArgs = [],
        bool $absolute = false,
        ?CmfConfig $cmfConfig = null
    ): string {
        $replaces = static::replaceDotJsInstertsInArrayValuesByUrlSafeInserts($queryArgs);
        $url = static::route(
            'cmf_api_resource_custom_action',
            array_merge(
                ['resource' => $resourceName, 'action' => $actionId],
                $replaces['data']
            ),
            $absolute,
            $cmfConfig
        );
        return static::replaceUrlSafeInsertsInUrlByDotJsInsterts($url, $replaces['replaces']);
    }
    
    /**
     * @param string $route
     * @param string|int|float $itemId
     * @param array $parameters
     * @param bool $absolute
     * @param CmfConfig|null|string $cmfConfig
     * @return string
     */
    protected static function routeWithPossibleItemIdDotJsInsert(
        string $route,
        string $itemId,
        array $parameters,
        bool $absolute = false,
        ?CmfConfig $cmfConfig = null
    ): string {
        if (preg_match('%^\s*' . DOTJS_INSERT_REGEXP_FOR_ROUTES . '\s*$%s', $itemId)) {
            $parameters['id'] = '__dotjs_item_id_insert__';
            $url = static::route($route, $parameters, $absolute, $cmfConfig);
            $itemId = '{{' . trim($itemId, '{}') . '}}'; //< normalize inserts like '{= it.id }'
            return str_replace('__dotjs_item_id_insert__', $itemId, $url);
        } else {
            $parameters['id'] = $itemId;
            return static::route($route, $parameters, $absolute, $cmfConfig);
        }
    }
    
    /**
     * @param array $data - array with values that contain dotJs insterts
     * @return array - ['replaces' => $replaces, 'data' => $escapedData]
     */
    protected static function replaceDotJsInstertsInArrayValuesByUrlSafeInserts(array $data): array
    {
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
    
    protected static function replaceUrlSafeInsertsInUrlByDotJsInsterts(string $url, array $replaces): string
    {
        if (!empty($replaces)) {
            $url = str_replace(array_keys($replaces), array_values($replaces), $url);
        }
        return $url;
    }
}
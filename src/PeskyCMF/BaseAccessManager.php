<?php

namespace PeskyCMF;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;

abstract class BaseAccessManager implements AccessManagerInterface {

    const REQUIRES_ACCESS_TO = 'requires_access_to';
    /**
     * REQUIRES_ACCESS_TO_ROUTES expects:
     * a) array('route_name' => 'http_method', ...)
     * b) array('route_name' => ['http_method1', 'http_method2'], ...) - access to route with ONE OF http methods
     * c) array('route_name1', 'route_name2') - 'http_method' will be set to 'get'
     * d) 'route_name' - 'http_method' will be set to 'get'
     * a,b,c - can be mixed with each other
     */
    const REQUIRES_ACCESS_TO_ROUTES = 'requires_access_to_route';
    const HTTP_METHOD_OVERRIDE_KEY = 'access_right_http_method_override';

    const OTHERS = 'others';

    /**
     * @var array
     * format:
     *  'role' => [
     *      'access_right_name' => $allowedHttpMethods,
     *      self::OTHERS => true|false  //< true: allow others | false - forbid others (default)
     *  ]
     * or
     *  'role' => true
     * to allow access to everythng
     *
     * Where $allowedHttpMethods can be:
     *      true: any http method
     *      false: forbidden
     *      array: only listed methods. Accepted HTTP methods: 'get', 'post', 'put', 'delete' (lowercased only!!!)
     *          ['get'],
     *          ['get', 'post'],
     *          ['get', 'put'],
     *          ['get', 'delete'],
     *          ['get', 'post', 'put', 'delete'],
     */
    static protected $rolesToRights = [

    ];
    /**
     * @var array
     */
    private static $cachedPermissions = [];

    /**
     * @param Request $request
     * @return bool
     * @throws \InvalidArgumentException
     */
    static public function isAuthorised(Request $request) {
        /** @var Route $route */
        $route = $request->route();
        $routeInfo = $route->getAction();
        if (!empty($routeInfo[self::HTTP_METHOD_OVERRIDE_KEY])) {
            $httpMethod = $routeInfo[self::HTTP_METHOD_OVERRIDE_KEY];
        } else {
            $httpMethod = $request->getMethod();
        }
        return static::isRoleHasAccessToRoute(static::getUserRole(), $route, $httpMethod);
    }

    /**
     * @param string $routeName
     * @param string $httpMethod
     * @return bool
     * @throws \InvalidArgumentException
     */
    static public function hasAccessToRoute($routeName, $httpMethod) {
        $route = static::findRoute($routeName);
        return empty($route) ? false : static::isRoleHasAccessToRoute(static::getUserRole(), $route, $httpMethod);
    }

    /**
     * @param string $role
     * @param Route $route
     * @param string $httpMethod
     * @return bool
     * @throws \InvalidArgumentException
     */
    static public function isRoleHasAccessToRoute($role, Route $route, $httpMethod) {
        $httpMethod = static::validateAndNormalizeHttpMethod($httpMethod);
        if (!in_array($httpMethod, static::getAllowedHttpMethods(), true)) {
            return false;
        }
        static::validateRole($role);
        if (static::roleHasUnlimitedAccess($role)) {
            return true;
        }
        $permissions = static::getCachedPermissions($role);
        return !empty($permissions[static::getPermissionKey($route, $httpMethod)]);
    }

    /**
     * @return array
     */
    static public function getRolesList() {
        return array_keys(static::getAccessRightsForAllRoles());
    }

    /**
     * @param string $role
     * @return bool
     */
    static public function roleHasUnlimitedAccess($role) {
        return static::getAccessRightsFor($role) === true;
    }

    /**
     * @return array
     */
    static public function getAllowedHttpMethods() {
        return ['get', 'post', 'put', 'delete'];
    }

    /**
     * If provided - any route that has prefix other then provided prefixes will be ignored
     * @return null|string|array - null: no restriction | string: single route prefix | array: list of route prefixes
     */
    static protected function getAllowedRoutePrefixes() {
        return null;
    }

    /**
     * @param $routeName
     * @return Route|null
     */
    static protected function findRoute($routeName) {
        return \Route::getRoutes()->getByName($routeName);
    }

    /**
     * @param string $role
     * @return bool|array
     */
    static protected function getAccessRightsFor($role) {
        $rights = static::getAccessRightsForAllRoles();
        return empty($rights[$role]) ? false : $rights[$role];
    }

    /**
     * @return array
     */
    static protected function getAccessRightsForAllRoles() {
        return static::$rolesToRights;
    }

    /**
     * @return string
     */
    static public function getCacheKey() {
        return get_called_class() . '_access_rights';
    }

    /**
     * @param string $httpMethod
     * @return string
     * @throws \InvalidArgumentException
     */
    static protected function validateAndNormalizeHttpMethod($httpMethod) {
        $httpMethod = strtolower($httpMethod);
        if ($httpMethod === 'head') {
            return 'get';
        }
        if (!in_array($httpMethod, ['get', 'post', 'put', 'delete'], true)) {
            throw new \InvalidArgumentException("Invalid http method passed: $httpMethod");
        }
        return $httpMethod;
    }

    /**
     * @param string $role
     * @throws \InvalidArgumentException
     */
    static protected function validateRole($role) {
        if (empty($role)) {
            throw new \InvalidArgumentException('Empty role name passed');
        }
    }

    /**
     * @param null|string $role
     * @return mixed
     * @throws \InvalidArgumentException
     */
    static protected function getCachedPermissions($role = null) {
        $cacheKey = static::getCacheKey();
        if (!array_key_exists($cacheKey, self::$cachedPermissions)) {
            self::$cachedPermissions[$cacheKey] = \Cache::get($cacheKey, function () use ($cacheKey) {
                return static::collectAndCachePermissionsForAllRoutesAndRoles($cacheKey);
            });
        }
        if ($role) {
            static::validateRole($role);
            return empty(self::$cachedPermissions[$cacheKey][$role]) ? [] : self::$cachedPermissions[$cacheKey][$role];
        } else {
            return self::$cachedPermissions[$cacheKey];
        }
    }

    /**
     * @param Route $route
     * @param string $httpMethod
     * @return string
     */
    static protected function getPermissionKey(Route $route, $httpMethod) {
//        return str_pad($httpMethod, 6, ' ') . ' -> /' . ltrim($route->getPath(), '/');
        return $httpMethod . '->' . $route->getPath();
    }

    static protected function collectAndCachePermissionsForAllRoutesAndRoles($cacheKey) {
        $roles = static::getRolesList();
        $accessRightsPerRouteAndMethod = [];
        foreach ($roles as $role) {
            $accessRightsPerRouteAndMethod[$role] = [];
        }
        $routePrefixesRestriction = static::getAllowedRoutePrefixes();
        if (is_string($routePrefixesRestriction)) {
            $routePrefixesRestriction = [$routePrefixesRestriction];
        } else if (!is_array($routePrefixesRestriction)) {
            $routePrefixesRestriction = false;
        }
        /** @var Route $route */
        foreach (\Route::getRoutes()->getRoutes() as $route) {
            if ($routePrefixesRestriction !== false && !in_array($route->getPrefix(), $routePrefixesRestriction, true)) {
                continue;
            }
            $routeHttpMethodOverride = array_get($route->getAction(), self::HTTP_METHOD_OVERRIDE_KEY, false);
            if ($routeHttpMethodOverride) {
                $routeHttpMethodOverride = static::validateAndNormalizeHttpMethod($routeHttpMethodOverride);
                $routeHttpMethods = [$routeHttpMethodOverride];
            } else {
                $routeHttpMethods = $route->getMethods();
            }
            foreach ($routeHttpMethods as $httpMethod) {
                // note: 'head' and 'patch' http methods are ignored
                if (in_array(strtolower($httpMethod), ['get', 'post', 'put', 'delete'], true)) {
                    foreach ($roles as $role) {
                        static::testRoleAccessToRoute(
                            $role,
                            $route,
                            $httpMethod,
                            $accessRightsPerRouteAndMethod[$role]
                        );
                    }
                }
            }
        }
        \Cache::put($cacheKey, $accessRightsPerRouteAndMethod, 180);
        // dpr($accessRightsPerRouteAndMethod); exit;
        return $accessRightsPerRouteAndMethod;
    }

    /**
     * @param string $role
     * @param Route $route
     * @param string $httpMethod
     * @param array $collectedPermissions
     * @return bool
     * @throws \InvalidArgumentException
     */
    static protected function testRoleAccessToRoute(
        $role,
        Route $route,
        $httpMethod,
        array &$collectedPermissions
    ) {
        $httpMethod = static::validateAndNormalizeHttpMethod($httpMethod);
        static::validateRole($role);
        $permissionKey = self::getPermissionKey($route, $httpMethod);
        if (!array_key_exists($permissionKey, $collectedPermissions)) {
            $accessRights = static::getAccessRightsFor($role);
            // is all forbidden?
            if (empty($accessRights)) {
                return ($collectedPermissions[$permissionKey] = false);
            }
            // is there any access rights required?
            $routeInfo = $route->getAction();
            // normalize access rules
            $routeRequiresAccessToSections = static::normalizeAccessRequirements(
                $routeInfo,
                self::REQUIRES_ACCESS_TO,
                $httpMethod
            );
            $routeRequiresAccessToOtherRoutes = static::normalizeAccessRequirements(
                $routeInfo,
                self::REQUIRES_ACCESS_TO_ROUTES,
                'get'
            );
            // test if there are any access requirements in route
            if (empty($routeRequiresAccessToOtherRoutes) && empty($routeRequiresAccessToSections)) {
                // there is no requirements for this route so we need to analyze $accessRights in order to
                // find preferred strategy via self::OTHERS key or by $accessRights if it is not an array
                if (is_array($accessRights)) {
                    /** @var array $accessRights */
                    $collectedPermissions[$permissionKey] = array_key_exists(self::OTHERS, $accessRights)
                        ? (bool) $accessRights[self::OTHERS]
                        : true;
                } else {
                    $collectedPermissions[$permissionKey] = (bool) $accessRights;
                }
                return $collectedPermissions[$permissionKey];
            }
            // test access to other rules
            foreach ($routeRequiresAccessToOtherRoutes as $otherRouteAlias => $otherRouteHttpMethods) {
                if (is_numeric($otherRouteAlias)) {
                    $otherRouteAlias = $otherRouteHttpMethods;
                    $otherRouteHttpMethods = ['get'];
                } else if (!is_array($otherRouteHttpMethods)) {
                    $otherRouteHttpMethods = [$otherRouteHttpMethods];
                }
                $hasAccessToRequiredMethod = false;
                $otherRoute = self::findRoute($otherRouteAlias);
                if (!empty($otherRoute)) {
                    foreach ($otherRouteHttpMethods as $routeHttpMethod) {
                        if (static::testRoleAccessToRoute($role, $otherRoute, $routeHttpMethod, $collectedPermissions)) {
                            $hasAccessToRequiredMethod = true;
                            break;
                        }
                    }
                }
                if (!$hasAccessToRequiredMethod) {
                    return ($collectedPermissions[$permissionKey] = false);
                }
            }
            // is superadmin?
            // placed here to firstly perform access validation to other routes where there are
            // possibly some specific item-related rights are tested
            if ($accessRights === true) {
                return ($collectedPermissions[$permissionKey] = true);
            }
            /** @var array $accessRights */
            unset($otherRoute, $otherRouteAlias, $otherRouteHttpMethods);
            // is access forbidden to a right?
            $hasAccess = false;
            foreach ($routeRequiresAccessToSections as $requiredRight => $requiredMethods) {
                if (empty($accessRights[$requiredRight])) {
                    if (array_key_exists($requiredRight, $accessRights) || empty($accessRights[self::OTHERS])) {
                        return ($collectedPermissions[$permissionKey] = false);
                    } else {
                        $hasAccess = true;
                        continue; //< allow others rule
                    }
                } else if ($accessRights[$requiredRight] === true) {
                    $hasAccess = true;
                    continue;
                } else {
                    foreach ($requiredMethods as $requiredMethod) {
                        if (in_array($requiredMethod, $accessRights[$requiredRight], true)) {
                            $hasAccess = true;
                        } else {
                            return ($collectedPermissions[$permissionKey] = false);
                        }
                    }
                }
            }
            $collectedPermissions[$permissionKey] = $hasAccess;
        }
        return $collectedPermissions[$permissionKey];
    }

    /**
     * @param array $routeInfo
     * @param string $requirementsKey - key in $routeInfo that stores requirements
     * @param string|array $defaultHttpMethods - HTTP method(s) to use as default when requirements has no its own methods
     * @return array
     * @throws \InvalidArgumentException
     */
    static protected function normalizeAccessRequirements(array $routeInfo, $requirementsKey, $defaultHttpMethods) {
        if (empty($routeInfo[$requirementsKey])) {
            return [];
        }
        $requirements = $routeInfo[$requirementsKey];
        if (is_string($defaultHttpMethods)) {
            $normalizedDefaultHttpMethods = [strtolower($defaultHttpMethods)];
        } else {
            $normalizedDefaultHttpMethods = [];
            foreach ($defaultHttpMethods as $httpMethod) {
                $normalizedDefaultHttpMethods[] = static::validateAndNormalizeHttpMethod($httpMethod);
            }
        }
        if (is_string($requirements)) {
            return [$requirements => $normalizedDefaultHttpMethods];
        } else if (is_array($requirements)) {
            $normalizedRequirements = [];
            foreach ($requirements as $subject => $httpMethods) {
                if (is_numeric($subject)) {
                    $subject = $httpMethods;
                    $httpMethods = $normalizedDefaultHttpMethods;
                } elseif (!is_array($httpMethods)) {
                    $httpMethods = [$httpMethods];
                }
                $normalizedHttpMethods = [];
                foreach ($httpMethods as $httpMethod) {
                    $normalizedHttpMethods[] = static::validateAndNormalizeHttpMethod($httpMethod);
                }
                $normalizedRequirements[$subject] = $normalizedHttpMethods;
            }
            return $normalizedRequirements;
        } else {
            throw new \InvalidArgumentException("Invalid access rights for route [{$routeInfo['as']} -> {$routeInfo['uses']}]");
        }
    }

}
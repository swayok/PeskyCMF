<?php

namespace PeskyCMF;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;

abstract class BaseAccessManager {

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
    protected $rolesToRights = [

    ];

    /** @var null|$this */
    static public $instance = null;

    static public function getInstance() {
        $class = get_called_class();
        if (empty($class::$instance)) {
            $class::$instance = new $class();
        }
        return $class::$instance;
    }

    /**
     * @param Request $request
     * @return bool
     */
    static public function isAuthorised(Request $request) {
        /** @var Route $route */
        $route = $request->route();
        /** @var BaseAccessManager $instance */
        $instance = call_user_func([get_called_class(), 'getInstance']);
        $routeInfo = $route->getAction();
        if (!empty($routeInfo[self::HTTP_METHOD_OVERRIDE_KEY])) {
            $httpMethod = $routeInfo[self::HTTP_METHOD_OVERRIDE_KEY];
        } else {
            $httpMethod = $request->getMethod();
        }
        return $instance->_hasAccessToRoute($route, $httpMethod);
    }

    /**
     * @param string $routeAlias
     * @param string $httpMethod
     * @return bool
     */
    static public function hasAccessToRoute($routeAlias, $httpMethod) {
        $route = self::findRoute($routeAlias);
        /** @var BaseAccessManager $instance */
        $instance = call_user_func([get_called_class(), 'getInstance']);
        return empty($route) ? false : $instance->_hasAccessToRoute($route, $httpMethod);
    }

    /**
     * @param $routeAlias
     * @return Route|null
     */
    static protected function findRoute($routeAlias) {
        return \Route::getRoutes()->getByName($routeAlias);
    }

    /**
     * @return array
     */
    static public function getRolesList() {
        /** @var BaseAccessManager $instance */
        $instance = call_user_func([get_called_class(), 'getInstance']);
        return $instance->_getRolesList();
    }

    /**
     * @return string|null
     */
    abstract protected function getUserRole();

    /**
     * @param $role
     * @return bool|array
     */
    protected function getAccessRightsFor($role) {
        return empty($this->getAccessRights()[$role]) ? false : self::getAccessRights()[$role];
    }

    protected function getAccessRights() {
        return $this->rolesToRights;
    }

    public function _getRolesList() {
        return array_keys($this->getAccessRights());
    }

    public function _hasAccessToRoute(Route $route, $httpMethod) {
        $role = $this->getUserRole();
        if (empty($role)) {
            return false;
        }
        // is there any access rights required?
        $routeInfo = $route->getAction();
        /** @var array|string $routeRequiresAccessToOtherRoutes */
        $routeRequiresAccessToOtherRoutes = empty($routeInfo[self::REQUIRES_ACCESS_TO_ROUTES]) ? [] : $routeInfo[self::REQUIRES_ACCESS_TO_ROUTES];
        $routeRequiresRightsTo = empty($routeInfo[self::REQUIRES_ACCESS_TO]) ? false : $routeInfo[self::REQUIRES_ACCESS_TO];
        if (empty($routeRequiresAccessToOtherRoutes) && !$routeRequiresRightsTo) {
            return true;
        }
        // is superuser?
        $accessRights = $this->getAccessRightsFor($role);
        if (empty($accessRights)) {
            return false;
        } else if ($accessRights === true) {
            return true;
        }
        // normalize access rules
        if (!is_array($routeRequiresRightsTo)) {
            $routeRequiresRightsTo = [$routeRequiresRightsTo];
        }
        if (!is_array($routeRequiresAccessToOtherRoutes)) {
            $routeRequiresAccessToOtherRoutes = [$routeRequiresAccessToOtherRoutes => 'get'];
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
            foreach ($otherRouteHttpMethods as $routeHttpMethod) {
                $otherRoute = self::findRoute($otherRouteAlias);
                if (!empty($otherRoute) && $this->_hasAccessToRoute($otherRoute, $routeHttpMethod)) {
                    $hasAccessToRequiredMethod = true;
                    break;
                }
            }
            if (!$hasAccessToRequiredMethod) {
                return false;
            }
        }
        unset($otherRoute, $otherRouteAlias, $otherRouteHttpMethods);
        // is access forbidden to a right?
        $hasAccess = false;
        foreach ($routeRequiresRightsTo as $requiredRight) {
            if (empty($accessRights[$requiredRight])) {
                if (array_key_exists($requiredRight, $accessRights) || empty($accessRights[self::OTHERS])) {
                    return false;
                } else {
                    $hasAccess = true;
                    continue; //< allow others rule
                }
            } else if ($accessRights[$requiredRight] === true) {
                $hasAccess = true;
                continue;
            } else if (in_array(strtolower($httpMethod), $accessRights[$requiredRight])){
                $hasAccess = true;
                continue;
            } else {
                return false;
            }
        }
        return $hasAccess;
    }

}
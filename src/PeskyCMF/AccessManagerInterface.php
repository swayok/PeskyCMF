<?php

namespace PeskyCMF;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;

interface AccessManagerInterface {

    /**
     * Check if role provided by static::getUserRole() has access to current route
     * @param Request $request
     * @return bool
     */
    static public function isAuthorised(Request $request);

    /**
     * Get current user's role
     * @return string
     */
    static public function getUserRole();

    /**
     * Check if role provided by static::getUserRole() has access to a route represented as alias (route name)
     * @param string $routeAlias - route unique name provided via 'as' key in route config
     * @param string $httpMethod - one of: 'get', 'post', 'put', 'delete'
     * @return bool
     */
    static public function hasAccessToRoute($routeAlias, $httpMethod);

    /**
     * @param string $role
     * @param Route $route
     * @param string $httpMethod
     * @return bool
     * @throws \InvalidArgumentException
     */
    static public function isRoleHasAccessToRoute($role, Route $route, $httpMethod);

    /**
     * Get available roles
     * @return array
     */
    static public function getRolesList();

    /**
     * Any combination of 'get', 'post', 'put', 'delete' values
     * @return array
     */
    static public function getAllowedHttpMethods();
}
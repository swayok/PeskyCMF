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
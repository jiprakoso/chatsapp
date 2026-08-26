<?php

if (!function_exists('is_active_route')) {
    function is_active_route($route)
    {
        $currentRoute = service('uri')->getSegment(1);
        return $currentRoute === $route ? 'active' : '';
    }
}

if (!function_exists('is_active_subroute')) {
    function is_active_subroute($route, $subroute)
    {
        $uri = service('uri');
        $currentRoute = $uri->getSegment(1);
        $currentSubroute = $uri->getTotalSegments() >= 2 ? $uri->getSegment(2) : null;
        return $currentRoute === $route && $currentSubroute === $subroute ? 'active' : '';
    }
}

if (!function_exists('is_active_fullpath')) {
    function is_active_fullpath($expectedPath)
    {
        $uri = service('uri');
        $currentPath = trim($uri->getPath(), '/');
        return $currentPath === $expectedPath ? 'active' : '';
    }
}
<?php

class RequestUtilities
{

    public static function app_version_smaller_than($version)
    {
        $requestHeaders = apache_request_headers();
        $app_version =  isset($requestHeaders['app_version']) ? $requestHeaders['app_version'] : false;

        if ($app_version && version_compare($app_version, $version, '<')) {
            return true;
        }

        return false;
    }
}

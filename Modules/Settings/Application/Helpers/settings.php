<?php

if (!function_exists('get_setting')) {
    function get_setting(string $key, $default = null)
    {
        try {
            return app(\Modules\Settings\Domain\Interfaces\SettingRepositoryInterface::class)->getByKey($key, $default);
        }
        catch (\Exception $e) {
            return $default;
        }
    }
}
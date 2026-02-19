<?php

if (!function_exists('direction')) {
    /**
     * Get the current language direction.
     *
     * @return string
     */
    function direction()
    {
        return app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
    }
}

if (!function_exists('get_supported_locales')) {
    /**
     * Get the supported locales.
     *
     * @return array
     */
    function get_supported_locales()
    {
        return ['en', 'ar'];
    }
}
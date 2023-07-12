<?php

namespace App\Utils;

use Illuminate\Support\Facades\App;

class Texts {
    const TYPE_FACETS = 'facets';
    const TYPE_WEB = 'web';
    const TYPES = [self::TYPE_FACETS];
    const DEFAULT = 'DEFAULT';

    private static function getText(string $key, ?string $lang, $original_key = '', array $replace = [], $default = null) {
        if ($lang == null) $lang = env('DEFAULT_LANGUAGE');

        if (null !== $lang && self::isAvailableLanguage($lang)) {
            App::setLocale($lang);
        }

        $text = __($key, $replace, $lang);

        if (null == $text) $text = $original_key;

        if ($text == $key) return self::getDefaultText($text, $default, $lang);

        return $text;
    }

    public static function web(string $key, string $lang = null, array $replace = [], $default = null)
    {
        $client = env('APP_CLIENT') ?? self::DEFAULT;
        return self::getText(self::getKey($key, self::TYPE_WEB, $client), $lang, $key, $replace, $default);
    }

    public static function facets(string $key, string $lang = null, array $replace = [], $default = null)
    {
        $client = env('APP_CLIENT') ?? self::DEFAULT;
        return self::getText(self::getKey($key, self::TYPE_FACETS, $client), $lang, $key, $replace, $default);
    }

    private static function getKey($key, $type, $client = self::DEFAULT)
    {
        $key_array = explode(".", $key);
        if (count($key_array) == 1 || (count($key_array) > 1 && !in_array($key_array[1], self::TYPES)) ) {
            $key = "$type.$client.$key";
        }
        return $key;
    }

    private static function getDefaultText($text, $default, $lang)
    {
        if (is_string($default)) return $default;
        if (is_array($default) && isset($default[$lang])) return $default[$lang];
        return "##$text##";
    }

    public static function isAvailableLanguage($lang)
    {
        $client = env('APP_CLIENT') ?? self::DEFAULT;
        $lang_available = config('constants.' . $client . '.languages');
        return in_array($lang, $lang_available);
    }
}

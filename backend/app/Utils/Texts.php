<?php

namespace App\Utils;

use Illuminate\Support\Facades\App;

class Texts {

    const TYPE_BOOK = 'book';
    const TYPE_WEB = 'web';
    const TYPES = [self::TYPE_BOOK, self::TYPE_WEB];

    private static function getText(string $key, ?string $lang, array $replace = [], $default = null) {
        if (null !== $lang && self::isAvailableLanguage($lang)) {
            App::setLocale($lang);
        }

        $text = __($key, $replace, $lang);

        if ($text == $key) return self::getDefaultText($text, $default, $lang);

        return $text;
    }

    public static function book(string $key, string $lang = null, array $replace = [], $default = null)
    {
        return self::getText(self::getKey($key, self::TYPE_BOOK), $lang, $replace, $default);
    }

    public static function web(string $key, string $lang = null, array $replace = [], $default = null)
    {
        return self::getText(self::getKey($key, self::TYPE_WEB), $lang, $replace, $default);
    }

    private static function getKey($key, $type)
    {
        $key_array = explode(".", $key);
        if (count($key_array) == 1 || (count($key_array) > 1 && !in_array($key_array[1], self::TYPES)) ) {
            $key = "$type.$key";
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
        $lang_available = config('constants.languages');
        return in_array($lang, $lang_available);
    }
}

<?php

namespace Violet88\Shortcodable;

use SilverStripe\View\ViewableData;
use SilverStripe\View\Parsers\ShortcodeParser;
use SilverStripe\Core\Config\Config;

/**
 * The Shortcodable class is responsible for registering shortcodable classes, parsing shortcodes and managing configuration.
 *
 * @package shortcodable
 * @property array $shortcodable_classes
 * @method array get_shortcodable_classes()
 * @method void register_classes(array $classes)
 * @method void register_class(string $class)
 * @author PixNyb <contact@roelc.me>
 **/
class Shortcodable extends ViewableData
{
    private static $shortcodable_classes = array();

    public static function register_classes($classes)
    {
        if (is_array($classes) && count($classes))
            foreach ($classes as $class)
                self::register_class($class);
    }

    public static function register_class($class)
    {
        if (class_exists($class)) {
            if (!singleton($class)->hasMethod('parse_shortcode'))
                user_error("Failed to register \"$class\" with shortcodable. $class must have the method parse_shortcode(). See /shortcodable/README.md", E_USER_ERROR);

            ShortcodeParser::get('default')->register($class, array($class, 'parse_shortcode'));
        }
    }

    public static function get_shortcodable_classes()
    {
        return Config::inst()->get(Shortcodable::class, 'shortcodable_classes');
    }
}

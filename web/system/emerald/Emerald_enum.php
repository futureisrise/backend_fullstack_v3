<?php
/*
 * ------------------------------------------------------
 *  Makes easy to work with Enums :)
 * ------------------------------------------------------
 */
namespace System\Emerald;

use ReflectionClass;

class Emerald_enum {
    public static function get_list(): array
    {
        return (new ReflectionClass(static::class))->getConstants();
    }

    public static function has_value($value): bool
    {
        return in_array($value, static::get_list());
    }
}

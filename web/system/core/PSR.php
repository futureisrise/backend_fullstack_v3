<?php
/**
 * PSR-like autoloading feature
 */
defined('BASEPATH') or exit('No direct script access allowed');

$psr_config = [
    'Model' => APPPATH . 'models',
    'Library' => APPPATH . 'libraries',
    'Core' => APPPATH . 'core',
    'System' => rtrim(BASEPATH, DIRECTORY_SEPARATOR)
];

spl_autoload_register(function ($class) use ($psr_config) {
    $segments = explode('\\', $class);
    $classname = array_pop($segments);
    $root = count($segments) ? array_shift($segments) : $class;
    if ( ! array_key_exists($root, $psr_config))
    {
        return;
    }
    $app_path = strtolower(implode(DIRECTORY_SEPARATOR, $segments));
    $path = $psr_config[$root] . DIRECTORY_SEPARATOR . $app_path;
    $class_file = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $classname . '.php';

    if (file_exists($class_file))
    {
        include_once $class_file;
    }
});

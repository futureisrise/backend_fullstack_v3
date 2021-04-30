<?php

namespace System\Emerald;

use ReflectionFunction;
use ReflectionMethod;
use Throwable;

class Emerald_model_exception_handler {


    public function __construct()
    {
        //set_exception_handler([$this, 'exception_handler']);
    }

    public static function exception_handler(Throwable $ex)
    {
        $info = $ex->getTrace();
        if (empty($info))
        {
            $last_call['function'] = '';
            $last_call['type'] = '';
            $last_call['class'] = '';
            $last_call['file'] = $ex->getFile();
            $last_call['line'] = $ex->getLine();
        } else
        {
            $last_call = $info[0];
        }

        ['function' => $method_name, 'type' => $type, 'class' => $class, 'file' => $file, 'line' => $line] = $last_call;
        $error_msg = sprintf("Call of %s%s%s(", $class, $type, $method_name);

        if (empty($class) && ! function_exists($method_name))
        {
            $error_msg .= sprintf(') throws uncaught %s: %s in %s:%s%sStack trace:%s%s', get_class($ex), $ex->getMessage(), $last_call['file'], $last_call['line'], PHP_EOL, PHP_EOL, $ex->getTraceAsString());
            echo sprintf('<b>Fatal error:</b> %s', str_replace(PHP_EOL, '</br>', $error_msg));
            error_log($error_msg);
            return;
        }

        $args = [];
        $reflection = ! empty($class) ? new ReflectionMethod($class, $method_name) : new ReflectionFunction($method_name);
        $call_parameters = $reflection->getParameters();
        foreach ($last_call['args'] as $idx => $arg)
        {
            if ($arg instanceof Emerald_model)
            {
                $args[$call_parameters[$idx]->getName()] = '(' . get_class($arg) . '(' . ($arg->get_id() ?? 'NULL') . '))';
                continue;
            }
            if (gettype($arg) === 'object')
            {
                $args[$call_parameters[$idx]->getName()] = '(object) stdClass';
                continue;
            }
            if (gettype($arg) === 'string')
            {
                $args[$call_parameters[$idx]->getName()] = sprintf('(string) \'%s\'', str_replace('\'', '\\\'', $arg));
                continue;
            }


            $args[$call_parameters[$idx]->getName()] = '(' . gettype($arg) . ') ' . $arg;
        }
        $error_msg .= implode(', ', array_map(function ($arg, $value) {
            return '$' . $arg . ' = ' . $value;
        }, array_keys($args), $args));
        $error_msg .= sprintf(') throws uncaught %s: %s in %s:%s%sStack trace:%s%s', get_class($ex), $ex->getMessage(), $last_call['file'], $last_call['line'], PHP_EOL, PHP_EOL, $ex->getTraceAsString());
        echo '<b>Fatal error:</b> ' . str_replace(PHP_EOL, '</br>', $error_msg);
        error_log($error_msg);
    }
}

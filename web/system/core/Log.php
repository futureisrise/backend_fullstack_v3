<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2016, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright	Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 1.0.0
 * @filesource
 */
namespace System\Core;

use DateTime;
use ReflectionClass;
use System\Core\Enum\Log_level;
use Throwable;

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Logging Class
 *
 * @package        CodeIgniter
 * @subpackage    Libraries
 * @category    Logging
 * @author        EllisLab Dev Team
 * @link		https://codeigniter.com/user_guide/general/errors.html
 */
class Log {

    /**
     * Path to save log files
     *
     * @var string
     */
    protected $_log_path;

    /**
     * Path to save log files
     *
     * @var string
     */
    const LOG_CI_INTERNTAL_FUNCTION_NAME = 'log_message';

    /**
     * File permissions
     *
     * @var    int
     */
    protected $_file_permissions = 0644;

    /**
     * Level of logging
     *
     * @var int
     */
    protected $_threshold = 1;

    /**
     * Array of threshold levels to log
     *
     * @var array
     */
    protected $_threshold_array = array();

    /**
     * Format of timestamp for log files
     *
     * @var string
     */
    protected $_date_fmt = 'Y-m-d H:i:s';

    /**
     * Filename extension
     *
     * @var	string
     */
    protected $_file_ext;

    /**
     * Whether or not the logger can write to the log files
     *
     * @var bool
     */
    protected $_enabled = TRUE;

    /**
     * Predefined logging levels
     *
     * @var array
     */
    protected $_levels = [
        Log_level::EMERGENCY => 1,
        Log_level::ALERT => 2,
        Log_level::CRITICAL => 3,
        Log_level::ERROR => 4,
        Log_level::WARNING => 5,
        Log_level::NOTICE => 6,
        Log_level::INFO => 7,
        Log_level::DEBUG => 8,
    ];

    /**
     * mbstring.func_overload flag
     *
     * @var    bool
     */
    protected static $func_overload;


    // --------------------------------------------------------------------

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function emergency($message, array $context = []): void
    {
        $this->log(Log_level::EMERGENCY, $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function alert($message, array $context = []): void
    {
        $this->log(Log_level::ALERT, $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function critical($message, array $context = []): void
    {
        $this->log(Log_level::CRITICAL, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function error($message, array $context = []): void
    {
        $this->log(Log_level::ERROR, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function warning($message, array $context = []): void
    {
        $this->log(Log_level::WARNING, $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function notice($message, array $context = []): void
    {
        $this->log(Log_level::NOTICE, $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function info($message, array $context = []): void
    {
        $this->log(Log_level::INFO, $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function debug($message, array $context = []): void
    {
        $this->log(Log_level::DEBUG, $message, $context);
    }

    /**
     * Class constructor
     *
     * @return    void
     */
    public function __construct()
    {
        $config =& get_config();

        isset(self::$func_overload) or self::$func_overload = (extension_loaded('mbstring') && ini_get('mbstring.func_overload'));

        $this->_log_path = ($config['log_path'] !== '') ? $config['log_path'] : APPPATH . 'logs/';
        $this->_file_ext = (isset($config['log_file_extension']) && $config['log_file_extension'] !== '')
            ? ltrim($config['log_file_extension'], '.') : 'php';

        file_exists($this->_log_path) or mkdir($this->_log_path, 0755, TRUE);

        if ( ! is_dir($this->_log_path) or ! is_really_writable($this->_log_path))
        {
            $this->_enabled = FALSE;
        }

        if (is_string($config['log_threshold']))
        {
            $this->_threshold = $this->_levels[$config['log_threshold']];
        } elseif (is_array($config['log_threshold']))
        {
            $this->_threshold = 0;
            $this->_threshold_array = (array_intersect_key($this->_levels,
                array_flip($config['log_threshold'])));
        }

        if ( ! empty($config['log_date_format']))
        {
            $this->_date_fmt = $config['log_date_format'];
        }

        if ( ! empty($config['log_file_permissions']) && is_int($config['log_file_permissions']))
        {
            $this->_file_permissions = $config['log_file_permissions'];
        }
    }

    // --------------------------------------------------------------------

    /**
     * Write Log File
     *
     * Generally this function will be called using the global log_message() function
     *
     * @param string $level The error level: 'error', 'debug' or 'info'
     * @param string|mixed $msg The error message
     * @return    bool
     */
    public function log(string $level, $msg, array $context = [])
    {
        if ($this->_enabled === FALSE)
        {
            return FALSE;
        }

        if (( ! in_array($level, (new ReflectionClass('System\Core\Enum\Log_level'))->getConstants()) or ($this->_levels[$level] > $this->_threshold))
            && ! isset($this->_threshold_array[$level]))
        {
            return FALSE;
        }

        $filepath = $this->_log_path . 'log-' . date('Y-m-d') . '.' . $this->_file_ext;
        $message = '';

        if ( ! file_exists($filepath))
        {
            $newfile = TRUE;
            // Only add protection to php files
            if ($this->_file_ext === 'php')
            {
                $message .= "<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>\n\n";
            }
        }

        if ( ! $fp = @fopen($filepath, 'ab'))
        {
            return FALSE;
        }

        flock($fp, LOCK_EX);

        // Instantiating DateTime with microseconds appended to initial date is needed for proper support of this format
        if (strpos($this->_date_fmt, 'u') !== FALSE)
        {
            $microtime_full = microtime(TRUE);
            $microtime_short = sprintf("%06d", ($microtime_full - floor($microtime_full)) * 1000000);
            $date = new DateTime(date('Y-m-d H:i:s.' . $microtime_short, $microtime_full));
            $date = $date->format($this->_date_fmt);
        } else
        {
            $date = date($this->_date_fmt);
        }

        $msg = $this->interpolate($msg, $context);
        if ( ! is_string($msg))
        {
            $msg = json_encode($msg);
        }

        $message .= $this->_format_line($level, $date, $msg);

        for ($written = 0, $length = self::strlen($message); $written < $length; $written += $result)
        {
            if (($result = fwrite($fp, self::substr($message, $written))) === FALSE)
            {
                break;
            }
        }

        flock($fp, LOCK_UN);
        fclose($fp);

        if (isset($newfile) && $newfile === TRUE)
        {
            chmod($filepath, $this->_file_permissions);
        }

        return is_int($result);
    }

    // --------------------------------------------------------------------

    /**
     * Replaces any placeholders in the message with variables
     * from the context, as well as a few special items like:
     *
     * {session_vars}
     * {post_vars}
     * {get_vars}
     * {env}
     * {env:foo}
     * {file}
     * {line}
     *
     * @param mixed $message
     * @param array $context
     *
     * @return mixed
     */
    protected function interpolate($message, array $context = [])
    {
        if ( ! is_string($message))
        {
            return $message;
        }

        // build a replacement array with braces around the context keys
        $replace = [];

        foreach ($context as $key => $val)
        {
            // Verify that the 'exception' key is actually an exception
            // or error, both of which implement the 'Throwable' interface.
            if ($key === 'exception' && $val instanceof Throwable)
            {
                $val = $val->getMessage() . ' ' . $this->clean_file_names($val->getFile()) . ':' . $val->getLine();
            }

            // todo - sanitize input before writing to file?
            $replace['{' . $key . '}'] = $val;
        }

        // Add special placeholders
        $replace['{post_vars}'] = '$_POST: ' . print_r($_POST, TRUE);
        $replace['{get_vars}'] = '$_GET: ' . print_r($_GET, TRUE);
        $replace['{env}'] = ENVIRONMENT;

        // Allow us to log the file/line that we are logging from
        if (strpos($message, '{file}') !== FALSE)
        {
            [$file, $line] = $this->determine_file();

            $replace['{file}'] = $file;
            $replace['{line}'] = $line;
        }

        // Match up environment variables in {env:foo} tags.
        if (strpos($message, 'env:') !== FALSE)
        {
            preg_match('/env:[^}]+/', $message, $matches);

            if ($matches)
            {
                foreach ($matches as $str)
                {
                    $key = str_replace('env:', '', $str);
                    $replace["{{$str}}"] = $_ENV[$key] ?? 'n/a';
                }
            }
        }

        if (isset($_SESSION))
        {
            $replace['{session_vars}'] = '$_SESSION: ' . json_encode($_SESSION);
        }

        // interpolate replacement values into the message and return
        return strtr($message, $replace);
    }


    /**
     * Determines the file and line that the logging call
     * was made from by analyzing the backtrace.
     * Find the earliest stack frame that is part of our logging system.
     *
     * @return array
     */
    public function determine_file(): array
    {
        $logFunctions = [
            'log_message',
            'log',
            'error',
            'debug',
            'info',
            'warning',
            'critical',
            'emergency',
            'alert',
            'notice',
        ];

        // Generate Backtrace info
        $trace = \debug_backtrace(0);

        // So we search from the bottom (earliest) of the stack frames
        $stackFrames = \array_reverse($trace);

        // Find the first reference to a Logger class method
        foreach ($stackFrames as $frame)
        {
            if (\in_array($frame['function'], $logFunctions, TRUE))
            {
                $file = isset($frame['file']) ? $this->clean_file_names($frame['file']) : 'unknown';
                $line = $frame['line'] ?? 'unknown';
                return [
                    $file,
                    $line,
                ];
            }
        }

        return [
            'unknown',
            'unknown',
        ];
    }

    // --------------------------------------------------------------------

    protected static function get_call_from($path)
    {

        if (preg_match('/application\/(.+)\//', $path, $matches))
        {
            return strtolower($matches[1]);
//                return strtolower(rtrim($matches[1], 's'));
//            }
        }
        return '';
    }

    // --------------------------------------------------------------------

    /** Generating caller string like: [BASEPATH/core/Loader.php][_ci_load_stock_library]
     * @return string
     */
    protected function get_instance_info(): string
    {
        $stack = debug_backtrace();

        // [0] last class and method, that means is as same Log class
        foreach ($stack as $inst_key => $inst)
        {
            //will return the first not Core\Log class (that will be a class/method that call the log ).
            if ($inst['class'] !== $stack[0]['class'] || $inst['function'] == self::LOG_CI_INTERNTAL_FUNCTION_NAME)
            {
                $inst_next = $stack[$inst_key + 1];
                $class_or_file = ! empty($inst_next['class']) ? $inst_next['class'] : $this->clean_file_names($inst_next['file']);
                return $instance_info = '[' . $class_or_file . '][' . $inst_next['function'] . '] ';
            }
        }
        return '';
    }


    //--------------------------------------------------------------------

    /**
     * Cleans the paths of filenames by replacing APPPATH, BASEPATH, FCPATH
     * with the actual var. i.e.
     *
     *  /var/www/site/app/Controllers/Home.php
     *      becomes:
     *  APPPATH/Controllers/Home.php
     *
     * @param string $file
     *
     * @return string
     */
    protected function clean_file_names(string $file): string
    {
        $file = str_replace(APPPATH, 'APPPATH/', $file);
        $file = str_replace(BASEPATH, 'BASEPATH/', $file);

        return str_replace(FCPATH, 'FCPATH/', $file);
    }

    // --------------------------------------------------------------------

    /**
     * Format the log line.
     *
     * This is for extensibility of log formatting
     * If you want to change the log format, extend the CI_Log class and override this method
     *
     * @param string $level The error level
     * @param string $date Formatted date string
     * @param string $message The log message
     * @return    string    Formatted log line with a new line character '\n' at the end
     */
    protected function _format_line(string $level, string $date, string $message)
    {
        return '[' . $date . '][' . strtolower($level) . '] ' . self::get_instance_info() . $message . "\n";
    }


    // --------------------------------------------------------------------

    /**
     * Byte-safe strlen()
     *
     * @param string $str
     * @return    int
     */
    protected static function strlen($str)
    {
        return (self::$func_overload)
            ? mb_strlen($str, '8bit')
            : strlen($str);
    }

    // --------------------------------------------------------------------

    /**
     * Byte-safe substr()
     *
     * @param string $str
     * @param int $start
     * @param int $length
     * @return    string
     */
    protected static function substr($str, $start, $length = NULL)
    {
        if (self::$func_overload)
        {
            // mb_substr($str, $start, null, '8bit') returns an empty
            // string on PHP 5.3
            isset($length) or $length = ($start >= 0 ? self::strlen($str) - $start : -$start);
            return mb_substr($str, $start, $length, '8bit');
        }

        return isset($length)
            ? substr($str, $start, $length)
            : substr($str, $start);
    }
}

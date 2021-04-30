<?php

namespace System\Core\Enum;

/**
 * Describes log levels.
 */
class Log_level {
    /**
     * Urgent alert.
     */
    const EMERGENCY = 'emergency';
    /**
     * Action must be taken immediately
     *
     * Example: Entire website down, database unavailable, etc.
     * This should trigger the SMS alerts and wake you up.
     */
    const ALERT = 'alert';
    /**
     * Critical conditions
     *
     * Example: Application component unavailable, unexpected exception.
     */
    const CRITICAL = 'critical';
    /**
     * Runtime errors
     */
    const ERROR = 'error';
    /**
     * Exceptional occurrences that are not errors
     *
     * Examples: Use of deprecated APIs, poor use of an API,
     * undesirable things that are not necessarily wrong.
     */
    const WARNING = 'warning';
    /**
     * Uncommon events
     */
    const NOTICE = 'notice';
    /**
     * Interesting events
     *
     * Examples: User logs in, SQL logs.
     */
    const INFO = 'info';
    /**
     * Detailed debug information
     */
    const DEBUG = 'debug';
}
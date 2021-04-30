<?php

namespace System\Libraries;

use App;
use CriticalException;

class Mysql {

    private static $db_config;

    private static $db_table_schema;

    public function __construct()
    {

    }

    public static function analyze_table(string $table): bool
    {
        self::load_db_config();


        $console = new ConsoleTable();

        self::$db_table_schema = App::get_s()->sql(sprintf('
		SELECT *
		FROM INFORMATION_SCHEMA.COLUMNS
		WHERE TABLE_SCHEMA = "%s"
		AND TABLE_NAME = "%s"
		ORDER BY `ORDINAL_POSITION` ASC
		', self::get_db_config('database'), $table))->many();


        if (empty(self::$db_table_schema))
        {
            echo sprintf('Wrong table name or database name! Table: %s Database: %s%s', $table, self::get_db_config('database'), PHP_EOL);
            return FALSE;
        }

        $prepared = 'SELECT MIN(`%s`) as `min_value`,MAX(`%s`) as `max_value`, MIN(length(`%s`)) as `min_length`, MAX(length(`%s`))  as `max_length` FROM `%s` WHERE 1';

        foreach (self::$db_table_schema as &$column)
        {
            $sql = sprintf($prepared, $column['COLUMN_NAME'], $column['COLUMN_NAME'], $column['COLUMN_NAME'], $column['COLUMN_NAME'], $table);
            $req = App::get_s()->sql($sql)->one();
            $column['ACTUAL_DATA'] = $req;
        }

        // VERY IMPORTANT!!!!!! https://habr.com/ru/post/136835/
        unset($column);

        echo 'TABLE: ' . self::$db_table_schema[0]['TABLE_NAME'] . PHP_EOL;

        $headers = [
            'COLUMN_NAME',
            'COLUMN_DEFAULT',
            'IS_NULLABLE',
            'COLUMN_TYPE',
            'min_value',
            'max_value',
            'min_length',
            'max_length'
        ];

        $console->setHeaders($headers);
        $console->setPadding(1);

        foreach (self::$db_table_schema as $column)
        {
            $console->addRow([
                $column['COLUMN_NAME'],
                (is_null($column['COLUMN_DEFAULT']) ? 'NULL' : $column['COLUMN_DEFAULT']),
                $column['IS_NULLABLE'],
                $column['COLUMN_TYPE'],
                $column['ACTUAL_DATA']['min_value'],
                $column['ACTUAL_DATA']['max_value'],
                $column['ACTUAL_DATA']['min_length'],
                $column['ACTUAL_DATA']['max_length'],
            ]);
        }

        $console->display();

        return TRUE;
    }


    protected static function get_db_config(string $key)
    {
        if (empty(self::$db_config['default']))
        {
            throw new CriticalException('Cant get db config! Please load it firts!');
        }
        return self::$db_config['default'][$key];
    }


    private static function load_db_config(): bool
    {
        self::$db_config = [];

        $file = 'database';
        foreach (find_config_files_path($file) as $location)
        {
            $file_path = APPPATH . 'config/' . $location . '.php';

//                log_message('error', '['.PROJECT.']Config try search: '.$file_path);

            if ( ! file_exists($file_path))
            {
                continue;
            }

            include($file_path);

            if ( ! isset($db) or ! is_array($db))
            {
                show_error('Your ' . $file_path . ' file does not appear to contain a valid configuration array.');
            }


            $loaded = TRUE;
            log_message('debug', 'Config file loaded: ' . $file_path);
        }

        if ( ! $loaded)
        {
            show_error('Cant find ' . $file . '.php config file!');
        }

        self::$db_config = $db;

        return TRUE;
    }


}
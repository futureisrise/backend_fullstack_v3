<?php

/*
 *  Class for igniter - like artisan igite you!
 */


use System\Libraries\ConsoleColors;

class System extends CI_Controller {

    public function __construct()
    {
        parent::__construct();

        if ( ! defined('CLI_IGNITER'))
        {
            show_404();
            die();
        }
    }

    public function index()
    {
        $this->help();
    }

    public function help()
    {
        echo (new ConsoleColors())->getColoredString('Hello my lovely friend! Welcome to next generation framework!', 'cyan', 'black') . PHP_EOL;
        echo PHP_EOL;
        echo (new ConsoleColors())->getColoredString('Available commands:', 'white', 'black') . PHP_EOL;

        echo (new ConsoleColors())->getColoredString('php igniter system generate_emerald_model [table_name] [\class\that\extends] - generates Emerald model in application/cachce folder', 'green', 'black') . PHP_EOL;
        echo (new ConsoleColors())->getColoredString('php igniter system mysql_analyze [table_name] - Mysql analyze tables min, max values and their length', 'green', 'black') . PHP_EOL;
        echo PHP_EOL;
        echo (new ConsoleColors())->getColoredString('For more information please visit our docs!', 'cyan', 'black') . PHP_EOL;
    }


    public function generate_emerald_model(string $table, string $extends = 'System\Emerald\Emerald_model')
    {
        App::get_ci()->load->helper('file');

        if (empty($table))
        {
            echo 'Wrong table name!' . PHP_EOL;
            return;
        }
        if (empty($extends) || ! is_string($extends))
        {
            echo 'Wrong extend class name!' . PHP_EOL;
            return;
        }

        System\Libraries\Emerald_generator::generate_model($table, $extends);
    }

    public function mysql_analyze(string $table)
    {
        if (empty($table))
        {
            echo 'Wrong table name!' . PHP_EOL;
            return;
        }

        $res = \System\Libraries\Mysql::analyze_table($table);

        if ($res)
        {
            echo 'Well done!' . PHP_EOL;
        } else
        {
            echo 'Something was fucked up...' . PHP_EOL;
        }
    }

}
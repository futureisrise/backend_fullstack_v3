<?php


use System\Libraries\ConsoleColors;

class Phinx extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!defined('CLI_IGNITER')) {
            show_404();
            die();
        }

        if (!class_exists('Phinx\Console\PhinxApplication', TRUE)) {
            throw new Exception('It requires Phinx to be installed');
        }

        chdir(APPPATH);
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

        echo (new ConsoleColors())->getColoredString('php igniter phinx status - get migrations status', 'green', 'black') . PHP_EOL;
        echo (new ConsoleColors())->getColoredString('php igniter phinx create [migration_name] - creates migration with defined name', 'green', 'black') . PHP_EOL;
        echo (new ConsoleColors())->getColoredString('php igniter phinx migrate [target] - migrate all migrations (or to specific by target)', 'green', 'black') . PHP_EOL;
        echo (new ConsoleColors())->getColoredString('php igniter phinx rollback - rollback last migration', 'green', 'black') . PHP_EOL;
        echo PHP_EOL;
        echo (new ConsoleColors())->getColoredString('For more information please visit our docs!', 'cyan', 'black') . PHP_EOL;
        echo (new ConsoleColors())->getColoredString('For Phinx docs https://book.cakephp.org/phinx/0/en/index.html', 'cyan', 'black') . PHP_EOL;
    }

    public function status()
    {
        $phinx = new \Phinx\Console\PhinxApplication();
        $command = $phinx->find('status');
        $arguments = [
            'command' => 'status',
        ];

        $input = new \Symfony\Component\Console\Input\ArrayInput($arguments);
        $output = new \Symfony\Component\Console\Output\ConsoleOutput();
        $returnCode = $command->run($input, $output);
        if ($returnCode > 0) {
            echo 'Having some problems cause status command returns code #' . $returnCode;
        }
    }

    public function create(string $migration_name)
    {
        $phinx = new \Phinx\Console\PhinxApplication();
        $command = $phinx->find('create');
        $arguments = [
            'command' => 'create',
            'name' => $migration_name
        ];

        $input = new \Symfony\Component\Console\Input\ArrayInput($arguments);
        $output = new \Symfony\Component\Console\Output\ConsoleOutput();
        $returnCode = $command->run($input, $output);
        if ($returnCode > 0)
        {
            echo 'Having some problems cause create command returns code #' . $returnCode;
        }
    }

    public function migrate(string $target = NULL)
    {
        $phinx = new \Phinx\Console\PhinxApplication();
        $command = $phinx->find('migrate');
        $arguments = [
            'command' => 'migrate',
        ];
        if ( ! is_null($target) && is_numeric($target))
        {
            $arguments['--target'] = $target;
        }

        $input = new \Symfony\Component\Console\Input\ArrayInput($arguments);
        $output = new \Symfony\Component\Console\Output\ConsoleOutput();
        $returnCode = $command->run($input, $output);
        if ($returnCode > 0) {
            throw new RuntimeException('Error #' . $returnCode . ' on Phinx migrate');
        }
    }

    public function rollback()
    {
        $phinx = new \Phinx\Console\PhinxApplication();
        $command = $phinx->find('rollback');
        $arguments = [
            'command' => 'rollback',
        ];

        $input = new \Symfony\Component\Console\Input\ArrayInput($arguments);
        $output = new \Symfony\Component\Console\Output\ConsoleOutput();
        $returnCode = $command->run($input, $output);
        if ($returnCode > 0) {
            throw new RuntimeException('Error #' . $returnCode . ' on Phinx rollback');
        }
    }
}

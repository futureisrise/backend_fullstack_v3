<?php

class CI_Sparrow_starter {

    function __construct()
    {
        $this->set_db();
    }


    function set_db()
    {
        include_once BASEPATH . 'libraries/Sparrow.php';

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

            if ( ! isset($db) OR ! is_array($db))
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


        $CI = &get_instance();

        $CI->s = new Sparrow();

        $CI->s->setDb([
            'type' => $db['default']['dbdriver'],
            'hostname' => $db['default']['pconnect'] === TRUE ? ('p:' . $db['default']['hostname']) : ($db['default']['hostname']),
            'port' => $db['default']['port'],
            'database' => $db['default']['database'],
            'username' => $db['default']['username'],// $db['default']['username'],
            'password' => $db['default']['password']
        ]);

        $CI->s->sql('SET NAMES utf8')->execute();
    }
}

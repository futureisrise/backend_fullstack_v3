<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function __destruct()
    {

    }

    public function go_back()
    {
        $url = App::get_ci()->agent->referer ?? "/";

        header("Location: {$url}");
        exit();
    }
}
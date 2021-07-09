<?php

namespace Model;

use App;
use Exception;
use System\Core\CI_Model;

class Login_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();

    }

    public static function logout()
    {
        App::get_ci()->session->unset_userdata('id');
    }

    /**
     * @return array
     * @throws Exception
     */
    public static function login(): array
    {
		App::get_ci()->load->library('form_validation');
		App::get_ci()->load->helper('form');

		App::get_ci()->form_validation->set_rules('login', 'Email', 'required');
		App::get_ci()->form_validation->set_rules('password', 'Password', 'required');

		if(App::get_ci()->form_validation->run()){

			$user = User_model::find_user_by_email(set_value('login'));

			if($user){
				if(set_value('password') == $user['password']){
					unset($user['password']);
					$data['user'] = $user;
				}
			}
		}

        self::start_session($user['id']);
		return $data;
	}

    public static function start_session(int $user_id)
    {
        // если не передан пользователь
        if (empty($user_id))
        {
            throw new Exception('No id provided!');
        }

        App::get_ci()->session->set_userdata('id', $user_id);
    }
}

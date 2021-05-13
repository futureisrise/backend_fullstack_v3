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
     * @return User_model
     * @throws Exception
     */
    public static function login($user_name, $user_password): User_model
    {
        $user_model = User_model::find_user_by_email($user_name);

        if (!$user_model)
        {
            throw new Exception("User not found");
        }

        if (password_verify($user_password, $user_model->get_password()) !== TRUE)
        {
            throw new Exception("Incorrect password");
        }

        self::start_session($user_model->get_id());

        return $user_model;
    }

    public static function start_session(int $user_id)
    {
        // если перенедан пользователь
        if (empty($user_id))
        {
            throw new Exception('No id provided!');
        }

        App::get_ci()->session->set_userdata('id', $user_id);
    }
}

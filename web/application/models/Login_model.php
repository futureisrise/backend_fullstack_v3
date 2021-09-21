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
    public static function login(string $email, string $password): User_model
    {
        //TODO

        $user = User_model::find_user_by_email($email);
        if (!$user) {
            return null;
        }
        if ($user->get_password() !== $password) {
            return null;
        }

        self::start_session($user->get_id());
        return $user;
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

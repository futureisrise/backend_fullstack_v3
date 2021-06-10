<?php

use Model\Analytics_model;
use Model\Boosterpack_model;
use Model\Comment_model;
use Model\Login_model;
use Model\Post_model;
use Model\User_model;
use Model\Transaction_type;
use Model\Transaction_info;

/**
 * Created by PhpStorm.
 * User: mr.incognito
 * Date: 10.11.2018
 * Time: 21:36
 */
class Main_page extends MY_Controller
{

    public function __construct()
    {

        parent::__construct();

        if (is_prod())
        {
            die('In production it will be hard to debug! Run as development environment!');
        }
    }

    public function index()
    {
        $user = User_model::get_user();

        App::get_ci()->load->view('main_page', ['user' => User_model::preparation($user, 'default')]);
    }

    public function get_all_posts()
    {
        $posts =  Post_model::preparation_many(Post_model::get_all(), 'default');
        return $this->response_success(['posts' => $posts]);
    }

    public function get_boosterpacks()
    {
        $posts =  Boosterpack_model::preparation_many(Boosterpack_model::get_all(), 'default');
        return $this->response_success(['boosterpacks' => $posts]);
    }

    public function get_post(int $post_id){

        //TODO получения поста по id
        $post = Post_model::preparation(Post_model::get_one($post_id), 'full_info');
        return $this->response_success(['post' => $post]);
    }


    public function comment(){

        if ( ! User_model::is_logged())
        {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $post_id = App::get_ci()->input->post('postId');
        $comment_text = App::get_ci()->input->post('commentText');

        if (!$post_id or !$comment_text) {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        //TODO логика комментирования поста
        $comment = Comment_model::preparation(Comment_model::create([
            'user_id' => User_model::get_user()->get_id(),
            'assign_id' => $post_id,
            'text' => $comment_text,
            'likes' => 0,
        ]), 'default');

        return $this->response_success(['comment' => $comment]);
    }


    public function login()
    {
        //TODO
        if (!App::get_ci()->input->post('login') or !App::get_ci()->input->post('password')) {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        try {
            $user = Login_model::login();
        } catch (Exception $e) {
            return $this->response_error($e->getMessage());
        }

        return $this->response_success(['user' => User_model::preparation($user, 'default')]);
    }


    public function logout()
    {
        //TODO
        Login_model::logout();
    }

    public function add_money(){
        if ( ! User_model::is_logged())
        {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $sum = (float)App::get_ci()->input->post('sum');

        //TODO логика добавления денег
        if (!$sum) {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        App::get_s()->set_transaction_repeatable_read()->execute();
        App::get_s()->start_trans()->execute();

        $user = User_model::get_user();

        try {
            $user->is_loaded(TRUE);
        } catch (Exception $e) {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NO_DATA);
        }

        $response = $user->add_money($sum);

        if (!$response) {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_INTERNAL_ERROR);
        }

        Analytics_model::create([
            'user_id' => $user->get_id(),
            'object' => Transaction_info::WALLET,
            'action' => Transaction_type::MONEY_IN,
            'amount' => $sum
        ]);

        if (!$response){
            App::get_s()->rollback()->execute();
        }

        App::get_s()->commit()->execute();

        return $this->response_success(['user' => User_model::preparation($user, 'default')]);
    }

    public function buy_boosterpack()
    {
        // Check user is authorize
        if ( ! User_model::is_logged())
        {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        //TODO логика покупки и открытия бустерпака по алгоритмку профитбанк, как описано в ТЗ
        App::get_s()->set_transaction_repeatable_read()->execute();
        App::get_s()->start_trans()->execute();

        $user = User_model::get_user();
        $boosterpack_id = App::get_ci()->input->post('id');

        if (!is_numeric($boosterpack_id)) {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        $boosterpack = Boosterpack_model::get_one($boosterpack_id);

        try {
            $user->is_loaded(TRUE);
            $boosterpack->is_loaded(TRUE);
        } catch (Exception $e) {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NO_DATA);
        }

        if ($user->get_wallet_balance() < $boosterpack->get_price()) {
            return $this->response_error(MY_Core::RESPONSE_GENERIC_NOT_ENOUGH_MONEY);
        }

        try {
            $likes_count = $boosterpack->open();
        } catch (Exception $e) {
            App::get_s()->rollback()->execute();
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_INTERNAL_ERROR);
        }

        if (!is_numeric($likes_count)){
            App::get_s()->rollback()->execute();
        }

        Analytics_model::create([
            'user_id' => $user->get_id(),
            'object' => Transaction_info::BOOSTERPACK,
            'object_id' => $boosterpack_id,
            'action' => Transaction_type::MONEY_OUT,
            'amount' => $likes_count,
        ]);

        App::get_s()->commit()->execute();

        return $this->response_success(['amount' => $likes_count, 'user' => User_model::preparation($user, 'default')]);
    }


    /**
     *
     * @return object|string|void
     */
    public function like_comment(int $comment_id)
    {
        // Check user is authorize
        if ( ! User_model::is_logged())
        {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        //TODO логика like comment(remove like у юзерa, добавить лай к комменту)
        if (!is_numeric($comment_id)) {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }
        $user = User_model::get_user();
        $comment = Comment_model::get_one($comment_id);

        try {
            $comment->is_loaded(TRUE);
        } catch (Exception $e) {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NO_DATA);
        }

        if ($comment->increment_likes($user)) {
            $comment->reload();
            $user->decrement_likes();
            return $this->response_success(['likes' => $comment->get_likes()]);
        }

        return $this->response_error(MY_Core::RESPONSE_GENERIC_NOT_ENOUGH_LIKES);
    }

    /**
     * @param int $post_id
     *
     * @return object|string|void
     */
    public function like_post(int $post_id)
    {
        // Check user is authorize
        if ( ! User_model::is_logged())
        {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        //TODO логика like post(remove like у юзерa, добавить лай к посту)
        $user = User_model::get_user();
        $post = Post_model::get_one($post_id);

        try {
            $post->is_loaded(TRUE);
        } catch (Exception $e) {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NO_DATA);
        }

        if ($post->increment_likes($user)) {
            $post->reload();
            $user->decrement_likes();
            return $this->response_success(['likes' => $post->get_likes()]);
        }

        return $this->response_error(MY_Core::RESPONSE_GENERIC_NOT_ENOUGH_LIKES);
    }


    /**
     * @return object|string|void
     */
    public function get_boosterpack_info(int $bootserpack_info)
    {
        // Check user is authorize
        if ( ! User_model::is_logged())
        {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }


        //TODO получить содержимое бустерпак
    }
}

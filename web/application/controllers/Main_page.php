<?php

use Model\Boosterpack_model;
use Model\Post_model;
use Model\User_model;
use Model\Login_model;
use Model\Comment_model;
use Model\Analytics_model;
use Model\Enum\Transaction_type_model;
use Model\Enum\Transaction_info_model;
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

    public function get_post(int $post_id)
    {
        //TODO получения поста по id
        try {
            $post = Post_model::preparation(Post_model::get_post($post_id), 'full_info');
        } catch (Exception $e) {
            return $this->response_error($e->getMessage());
        }
        return $this->response_success(['post' => $post]);        
    }

    public function comment()
    {
        if ( ! User_model::is_logged())
        {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }
        
        //TODO логика комментирования поста
        if ( ! App::get_ci()->input->post('postId') or ! App::get_ci()->input->post('commentText')) {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }
        $postId = App::get_ci()->input->post('postId');
        $commentText = App::get_ci()->input->post('commentText');        
        $data = [
            'user_id' => User_model::get_user()->get_id(),
            'assign_id' => $postId,
            'text' => $commentText,
            'likes' => 0,
        ];
        if ( App::get_ci()->input->post('replyId')>0 ) {
            $data['reply_id'] = App::get_ci()->input->post('replyId');
        }
        $comment = Comment_model::preparation(Comment_model::create($data), 'default');
        return $this->response_success(['comment' => $comment]);
    }


    public function login()
    {
                
        //TODO
        if ( ! App::get_ci()->input->post('login') or ! App::get_ci()->input->post('password')) {
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
        $user = Login_model::logout();
        redirect('/', 'refresh');
    }

    public function add_money()
    {
        if ( ! User_model::is_logged())
        {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $sum = (float)App::get_ci()->input->post('sum');

        //TODO логика добавления денег
        App::get_s()->set_transaction_repeatable_read()->execute();
        App::get_s()->start_trans()->execute();
        
        $user = User_model::get_user();
        $addMoney = $user->add_money($sum);
        
        $data = [
            'user_id' => $user->get_id(),
            'object' => Transaction_info_model::BALANCE,
            'object_id' => Transaction_type_model::TOP_UP,
            'action' => Transaction_info_model::BALANCE_TOP_UP,
            'amount' => $sum
        ];
        Analytics_model::create($data);
        
        if (!$addMoney){
            App::get_s()->rollback()->execute();
        } else {
            App::get_s()->commit()->execute();
        }
        
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
        
        $user = User_model::get_user();
        $comment = Comment_model::get_comment($comment_id);
        
        if ($user->get_likes_balance() > 0) {
            
            App::get_s()->set_transaction_repeatable_read()->execute();
            App::get_s()->start_trans()->execute();
            
            $incrementLikes = $comment->increment_likes($user);
            $decrementLikes = $user->decrement_likes();
            
            if (!$incrementLikes && !$decrementLikes){
                App::get_s()->rollback()->execute();
            } else {
                App::get_s()->commit()->execute();
                $comment->reload();
                $user->reload();
                return $this->response_success(['likes' => $comment->get_likes(), 'likes_balance' => $user->get_likes_balance()]);
            }
            
        }
        
        return $this->response_success(['user' => User_model::preparation($user, 'default')]);
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
        $post = Post_model::get_post($post_id);
        
        if ($user->get_likes_balance() > 0) {
            
            App::get_s()->set_transaction_repeatable_read()->execute();
            App::get_s()->start_trans()->execute();
            
            $incrementLikes = $post->increment_likes($user);
            $decrementLikes = $user->decrement_likes();
            
            if (!$incrementLikes && !$decrementLikes){
                App::get_s()->rollback()->execute();
            } else {
                App::get_s()->commit()->execute();
                $post->reload();
                $user->reload();
                return $this->response_success(['likes' => $post->get_likes(), 'likes_balance' => $user->get_likes_balance()]);
            }
            
        }
        
        return $this->response_success(['user' => User_model::preparation($user, 'default')]);
        
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

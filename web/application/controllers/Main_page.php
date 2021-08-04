<?php

use Model\Boosterpack_model;
use Model\Post_model;
use Model\User_model;
use Model\Login_model;
use Model\Comment_model;
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
        $post_model = new Post_model($post_id);
        if (!$post_model) {
            throw new Exception("Post not found");
        }

        $post = Post_model::preparation($post_model, 'full_info');        
        return $this->response_success(['post' => $post]);
    }


    public function comment(){

        if ( ! User_model::is_logged())
        {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        //TODO логика комментирования поста
        $post_id = App::get_ci()->input->post("postId");
        $post_model = new Post_model($post_id);
        if (!$post_id || !$post_model->get_id()) {
            return $this->response_error("Invalid post id");
        }

        $data = [
            'assign_id' => $post_id,
            'user_id' => User_model::get_session_id(),
            'reply_id' => NULL,
            'text' => htmlspecialchars(stripslashes(App::get_ci()->input->post('commentText'))),
            'likes' => 0            
        ];

        $res = Comment_model::create($data);

        if (!$res) {
            return $this->response_error("Can't create comment");
        }


        return $this->response_success([
            'comments' => Comment_model::preparation_many(Comment_model::get_all_by_assign_id($post_id),'default')
        ]);

        
    }


    public function login()
    {
        //TODO
        if ( User_model::is_logged())
        {
            return $this->response_success();
        }
        $email = App::get_ci()->input->post("login");
        $password = App::get_ci()->input->post('password');

        if (empty($email) || empty($password)) {
            return $this->response_error("Login or Password can't be empty");
        }

        try {
            $user = Login_model::login($email, $password);
        } catch (Exception $exception) {
            return $this->response_error($exception->getMessage());
        }

        return $this->response_success(['user' => $user->get_id()]);
    }


    public function logout()
    {
        //TODO
        if (!User_model::is_logged())
        {
            return $this->response_success();
        }

        Login_model::logout();
        App::get_ci()->load->helper('url');
        redirect(site_url());
    }

    public function add_money(){
        if ( ! User_model::is_logged())
        {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $sum = (float)App::get_ci()->input->post('sum');

        //TODO логика добавления денег
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
        if (!$user || $user->get_likes_balance() < 1) {
            return $this->response_error("User haven't like points in balance");
        }

        //maybe in future have better way
        $comment = Comment_model::get_comment($comment_id);
        if (!$comment) {
            return $this->response_error("Wrong comment ID");
        }

        if($user->decrement_likes()){
            if($comment->increment_likes($comment_id)){
                //beter will get all post again 
                return $this->get_post($comment->get_assing_id());
            }
        }
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
        if (!$user || $user->get_likes_balance() < 1) {
            return $this->response_error("User haven't like points in balance");
        }

        //maybe in future have better way
        $post = new Post_model($post_id);
        if (!$post) {
            // here this  or can be ->response_error
            throw new Exception("Wrong post ID");
        }

        if($user->decrement_likes()){
            if($post->increment_likes($post_id)){
                //beter will get all post again 
                return $this->get_post($post_id);
            }
        }
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

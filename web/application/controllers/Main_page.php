<?php

use Model\Boosterpack_model;
use Model\Post_model;
use Model\User_model;
use Model\Login_model;
use Model\Comment_model;
use Model\Analytics_model;

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
        $post = Post_model::get_post($post_id);
        return $this->response_success(['post' =>  Post_model::preparation($post, 'full_info')]);
    }

    public function comment(){

        if ( ! User_model::is_logged())
        {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        if($this->input->post('commentText') !== ''){
            $replyId = $this->input->post('replyId');
            $data = array(
                'user_id' => User_model::get_session_id(),
                'assign_id' =>  $this->input->post('postId'),
                'reply_id' =>  (!$replyId == '' and $replyId > 0) ? $replyId : null,
                'text' =>  $this->input->post('commentText'),
                'likes' =>  0,
            );

            $comment_id = Comment_model::create($data);

            $comment = Comment_model::get_comment_by_id($comment_id);

            return $this->response_success(['comment' =>  Comment_model::preparation($comment)] );
        }else{
            return $this->response_error('Comment text empty');
        }

    }


    public function login()
    {
        if(User_model::is_logged()){
            $this->load->helper('url');
            redirect('/');
        }

        $data = Login_model::login();
        return $this->response_success($data);
    }


    public function logout()
    {
        Login_model::logout();

        redirect('/');
    }

    public function add_money(){
        if ( ! User_model::is_logged())
        {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $sum = (float)App::get_ci()->input->post('sum');

        $add = new User_model(User_model::get_session_id());

        $add->add_money($sum);
    }

    public function buy_boosterpack()
    {
        // Check user is authorize
        if ( ! User_model::is_logged())
        {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }
        $this->load->helper('array');

        $pack_id = (float)App::get_ci()->input->post('id');

        return $this->response_success(['amount' => boosterpack_model::buy_boosterpack($pack_id)]);

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
        $user = User_model::get_user();
        $comment= new Comment_model($comment_id);

        return $this->response_success(['msg' => $comment->increment_likes($user)]);
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
        $user = User_model::get_user();
        $post = new Post_model($post_id);

        return $this->response_success(['msg' => $post->increment_likes($user)]);
    }

    /**
     * @return object|string|void
     */
    public function get_history()
    {
        // Check user is authorize
        if ( ! User_model::is_logged())
        {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $analytics = Analytics_model::get_analytics_for_user(User_model::get_session_id());
        $user_balance = User_model::preparation(User_model::get_user(),'balance_show');

        return $this->response_success(['history' => Analytics_model::preparation_many($analytics), 'user_balance' => $user_balance]);
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



    }
}

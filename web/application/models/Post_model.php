<?php
namespace Model;
use App;
use Exception;
use stdClass;
use System\Emerald\Emerald_model;

/**
 * Created by PhpStorm.
 * User: mr.incognito
 * Date: 27.01.2020
 * Time: 10:10
 */
class Post_model extends Emerald_Model
{
    const CLASS_TABLE = 'post';


    /** @var int */
    protected $user_id;
    /** @var string */
    protected $text;
    /** @var string */
    protected $img;

    /** @var string */
    protected $time_created;
    /** @var string */
    protected $time_updated;

    // generated
    protected $comments;
    protected $likes;
    protected $user;


    /**
     * @return int
     */
    public function get_user_id(): int
    {
        return $this->user_id;
    }

    /**
     * @param int $user_id
     *
     * @return bool
     */
    public function set_user_id(int $user_id):bool
    {
        $this->user_id = $user_id;
        return $this->save('user_id', $user_id);
    }

    /**
     * @return string
     */
    public function get_text(): string
    {
        return $this->text;
    }

    /**
     * @param string $text
     *
     * @return bool
     */
    public function set_text(string $text):bool
    {
        $this->text = $text;
        return $this->save('text', $text);
    }

    /**
     * @return string
     */
    public function get_img(): string
    {
        return $this->img;
    }

    /**
     * @param string $img
     *
     * @return bool
     */
    public function set_img(string $img):bool
    {
        $this->img = $img;
        return $this->save('img', $img);
    }


    /**
     * @return string
     */
    public function get_time_created(): string
    {
        return $this->time_created;
    }

    /**
     * @param string $time_created
     *
     * @return bool
     */
    public function set_time_created(string $time_created):bool
    {
        $this->time_created = $time_created;
        return $this->save('time_created', $time_created);
    }

    /**
     * @return string
     */
    public function get_time_updated(): string
    {
        return $this->time_updated;
    }

    /**
     * @param string $time_updated
     *
     * @return bool
     */
    public function set_time_updated(int $time_updated):bool
    {
        $this->time_updated = $time_updated;
        return $this->save('time_updated', $time_updated);
    }

    /**
     * @return int|null
     */
    public function get_likes(): ?int
    {
        return $this->likes;
    }

    public function set_likes(int $likes):bool
    {
        $this->likes = $likes;
        return $this->save('likes', $likes);
    }

    // generated

    /**
     * @return Comment_model[]
     */
    public function get_comments():array
    {
        return Comment_model::get_all_by_assign_id($this->get_id());
       //TODO
    }

    /**
     * @return User_model
     */
    public function get_user():User_model
    {
        $this->is_loaded(TRUE);

        if (empty($this->user))
        {
            try {
                $this->user = new User_model($this->get_user_id());
            } catch (Exception $exception)
            {
                $this->user = new User_model();
            }
        }
        return $this->user;
    }

    function __construct($id = NULL)
    {
        parent::__construct();
        $this->set_id($id);
    }

    public function reload()
    {
        parent::reload();
        return $this;
    }

    public static function create(array $data)
    {
        App::get_s()->from(self::CLASS_TABLE)->insert($data)->execute();
        return new static(App::get_s()->get_insert_id());
    }

    public function delete()
    {
        $this->is_loaded(TRUE);
        App::get_s()->from(self::CLASS_TABLE)->where(['id' => $this->get_id()])->delete()->execute();
        return App::get_s()->is_affected();
    }

    /**
     * @return static[]
     * @throws Exception
     */
    public static function get_all():array
    {
        return static::transform_many(App::get_s()->from(self::CLASS_TABLE)->many());
    }

    /**
     * 
     *
     * @return bool
     * @throws Exception
     */
    public function increment_likes(): bool
    {
        App::get_s()->from(self::get_table())
            ->where(['id' => $this->get_id()])
            ->update(sprintf('likes = likes +  %s', App::get_s()->quote(1)))
            ->execute();

        if ( ! App::get_s()->is_affected())
        {
            return FALSE;
        }
        return TRUE;
        //TODO
    }


    /**
     * @param Post_model $data
     * @param string $preparation
     * @return stdClass
     * @throws Exception
     */
    public static function preparation(Post_model $data, $preparation = 'default'):stdClass
    {
        switch ($preparation)
        {
            case 'default':
                return self::_preparation_default($data);
            case 'full_info':
                return self::_preparation_full_info($data);
            default:
                throw new Exception('undefined preparation type');
        }
    }

    /**
     * @param Post_model $data
     * @return stdClass
     */
    private static function _preparation_default(Post_model  $data):stdClass
    {
        $o = new stdClass();

        $o->id = $data->get_id();
        $o->img = $data->get_img();

        $o->text = $data->get_text();

        $o->user = User_model::preparation($data->get_user(), 'main_page');

        $o->time_created = $data->get_time_created();
        $o->time_updated = $data->get_time_updated();

        return $o;
    }


    /**
     * @param Post_model $data
     * @return stdClass
     */
    private static function _preparation_full_info(Post_model $data):stdClass
    {
        $o = new stdClass();


        $o->id = $data->get_id();
        $o->img = $data->get_img();

        $o->user = User_model::preparation($data->get_user(),'main_page');
        $o->coments = Comment_model::preparation_many($data->get_comments(),'nested');

        $o->likes = $data->get_likes();


        $o->time_created = $data->get_time_created();
        $o->time_updated = $data->get_time_updated();

        return $o;
    }
}

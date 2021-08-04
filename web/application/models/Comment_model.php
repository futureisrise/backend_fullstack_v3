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
class Comment_model extends Emerald_Model {
    const CLASS_TABLE = 'comment';


    /** @var int */
    protected $user_id;
    /** @var int */
    protected $assing_id;
    /** @var string */
    protected $text;
    /** @var int */
    protected $reply_id;

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
    public function set_user_id(int $user_id)
    {
        $this->user_id = $user_id;
        return $this->save('user_id', $user_id);
    }

    /**
     * @return int
     */
    public function get_assing_id(): ?int
    {
        return $this->assign_id;
    }

    /**
     * @param int $assing_id
     *
     * @return bool
     */
    public function set_assing_id(int $assing_id)
    {
        $this->assing_id = $assing_id;
        return $this->save('assing_id', $assing_id);
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
    public function set_text(string $text)
    {
        $this->text = $text;
        return $this->save('text', $text);
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
    public function set_time_created(string $time_created)
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
    public function set_time_updated(int $time_updated)
    {
        $this->time_updated = $time_updated;
        return $this->save('time_updated', $time_updated);
    }

    /**
     * @return Int|null
     */
    public function get_likes()
    {
        return $this->likes;
    }

    /**
     * @param int $likes
     * @return bool
     */
    public function set_likes(int $likes)
    {
        $this->likes = $likes;
        return $this->save('likes', $likes);
    }

    /**
     * @return Int
     */
    public function get_reply_id(): ?int
    {
        return $this->reply_id;
    }

    /**
     * @param int $reply_id
     * @return bool
     */
    public function set_reply_id(int $reply_id)
    {
        $this->reply_id = $reply_id;
        return $this->save('reply_id', $reply_id);
    }

    /**
     * @return mixed
     */
    public function get_comments()
    {
        return $this->comments;
    }

    /////////// GENERATED

    /**
     * @return User_model
     */
    public function get_user(): User_model
    {
        $this->is_loaded(TRUE);

        if (empty($this->user))
        {
            try
            {
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

    public static function get_comment(int $id) : Comment_model
    {
        $result = App::get_s()->from(self::CLASS_TABLE)
            ->where(['id' => $id])
            ->select()
            ->one();

        return ($result)? self::transform_one($result):NULL;
    }

    public static function create(array $data)
    {
        App::get_s()->from(self::CLASS_TABLE)->insert($data)->execute();
        return new static(App::get_s()->get_insert_id());
    }

    public function delete(): bool
    {
        $this->is_loaded(TRUE);
        App::get_s()->from(self::CLASS_TABLE)->where(['id' => $this->get_id()])->delete()->execute();
        return App::get_s()->is_affected();
    }

    /**
     * @param int $assign_id
     * @return self[]
     * @throws Exception
     */
    public static function get_all_by_assign_id(int $assign_id): array
    {
        return static::transform_many(App::get_s()->from(self::CLASS_TABLE)->where(['assign_id' => $assign_id])->orderBy('time_created', 'ASC')->many());
    }

    /**
     * @param int $user
     *
     * @return bool
     * @throws Exception
     */
    public function increment_likes(int $comment_id): bool
    {
        //TODO
        App::get_s()->from(self::CLASS_TABLE)
            ->where(['id' => $comment_id])
            ->update(sprintf('likes = likes + %s', App::get_s()->quote(1)))
            ->execute();

        return ( App::get_s()->is_affected())? TRUE:FALSE;
    }

    public static function get_all_by_replay_id(int $reply_id) : array
    {
        //TODO
        return static::transform_many(App::get_s()->from(self::CLASS_TABLE)->where(['reply_id' => $reply_id])->orderBy('time_created', 'ASC')->many());
    }

    /**
     * @param self $data
     * @param string $preparation
     * @return stdClass
     * @throws Exception
     */
    public static function preparation(Comment_model $data, string $preparation = 'default')
    {
        switch ($preparation)
        {
            case 'default':
                return self::_preparation_default($data);
            default:
                throw new Exception('undefined preparation type');
        }
    }


    /**
     * @param self $data
     * @return stdClass
     */
    private static function _preparation_default(Comment_model $data): stdClass
    {
        $o = new stdClass();

        $o->id = $data->get_id();
        $o->text = $data->get_text();

        $o->user = User_model::preparation($data->get_user(), 'main_page');

        $o->likes = $data->get_likes();

        $o->time_created = $data->get_time_created();
        $o->time_updated = $data->get_time_updated();

        return $o;
    }

}

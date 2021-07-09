<?php
namespace Model;

use App;
use Exception;
use System\Emerald\Emerald_model;
use stdClass;
use ShadowIgniterException;

/**
 * Created by PhpStorm.
 * User: mr.incognito
 * Date: 27.01.2020
 * Time: 10:10
 */
class Boosterpack_model extends Emerald_model
{
    const CLASS_TABLE = 'boosterpack';

    /** @var float Цена бустерпака */
    protected $price;
    /** @var float Банк, который наполняется  */
    protected $bank;
    /** @var float Наша комиссия */
    protected $us;

    protected $boosterpack_info;


    /** @var string */
    protected $time_created;
    /** @var string */
    protected $time_updated;

    /**
     * @return float
     */
    public function get_price(): float
    {
        return $this->price;
    }

    /**
     * @param float $price
     *
     * @return bool
     */
    public function set_price(int $price):bool
    {
        $this->price = $price;
        return $this->save('price', $price);
    }

    /**
     * @return float
     */
    public function get_bank(): float
    {
        return $this->bank;
    }

    /**
     * @param float $bank
     *
     * @return bool
     */
    public function set_bank(float $bank):bool
    {
        $this->bank = $bank;
        return $this->save('bank', $bank);
    }

    /**
     * @return float
     */
    public function get_us(): float
    {
        return $this->us;
    }

    /**
     * @param float $us
     *
     * @return bool
     */
    public function set_us(float $us):bool
    {
        $this->us = $us;
        return $this->save('us', $us);
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
    public function set_time_updated(string $time_updated):bool
    {
        $this->time_updated = $time_updated;
        return $this->save('time_updated', $time_updated);
    }

    //////GENERATE

    /**
     * @return Boosterpack_info_model[]
     */
    public function get_boosterpack_info(): array
    {
        //TODO
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

	public function update_bank(float $bank)
	{
		if($bank < 0) $bank = 0;

		App::get_s()->from(self::CLASS_TABLE)->where(['id' => $this->get_id()])->update(sprintf('bank =  %s', $bank))->execute();

		if ( ! App::get_s()->is_affected())
		{
			return FALSE;
		}
		return TRUE;
	}

    public function delete():bool
    {
        $this->is_loaded(TRUE);
        App::get_s()->from(self::CLASS_TABLE)->where(['id' => $this->get_id()])->delete()->execute();
        return App::get_s()->is_affected();
    }

    public static function get_all()
    {
        return static::transform_many(App::get_s()->from(self::CLASS_TABLE)->many());
    }

	/**
	 * @return
	 * @throws Exception
	 */
	public static function get_boosterpack(int $post_id)
	{
		return App::get_s()->from(self::CLASS_TABLE)->where('id',  $post_id)->one();
	}
	/**
	 * @return
	 * @throws Exception
	 */
	public static function buy_boosterpack(int $pack_id)
	{

		$boosterpack = Boosterpack_model::get_boosterpack($pack_id);

		$max_available_likes = $boosterpack['bank'] + ($boosterpack['price'] - $boosterpack['us']);
		$items = Boosterpack_model::get_contains($max_available_likes);

		$result = random_element($items);

		$profit_bank = $boosterpack['bank'] + $boosterpack['price'] - $boosterpack['us'] - $result['price'];
		$boosterpack_info_data = array(
			'boosterpack_id' => $boosterpack['id'],
			'item_id' => $result['id']
		);


		$user = new User_model(User_model::get_session_id());

		App::get_s()->set_transaction_repeatable_read()->execute();
		App::get_s()->start_trans()->execute();

		$remove = $user->remove_money($boosterpack['price']);
		$increment_like = $user->increment_likes($result['price']);
		$update_bank = (new self($pack_id))->update_bank($profit_bank);

		$object_id = Boosterpack_info_model::create($boosterpack_info_data);
		$analytics = array(
			'user_id'   => User_model::get_session_id(),
			'object'    => 'boosterpack',
			'action'    => 'buy',
			'object_id' => $object_id,
			'amount'    => $boosterpack['price']
		);

		Analytics_model::create($analytics);

		if($remove && $increment_like && $update_bank){

			App::get_s()->commit()->execute();

			return $result['price'];
		}
		else{
			App::get_s()->rollback()->execute();
			return false;
		}

	}

    /**
     * @return int
     */
    public function open(): int
    {

    }

    /**
     * @param int $max_available_likes
     *
     * @return Item_model[]
     */
    public static function get_contains(int $max_available_likes): array
    {
        return Item_model::get_by_max_available_likes($max_available_likes);
    }



    /**
     * @param Boosterpack_model $data
     * @param string            $preparation
     *
     * @return stdClass|stdClass[]
     */
    public static function preparation(Boosterpack_model $data, string $preparation = 'default')
    {
        switch ($preparation)
        {
            case 'default':
                return self::_preparation_default($data);
            case 'contains':
                return self::_preparation_contains($data);
            default:
                throw new Exception('undefined preparation type');
        }
    }

    /**
     * @param Boosterpack_model $data
     *
     * @return stdClass
     */
    private static function _preparation_default(Boosterpack_model $data): stdClass
    {
        $o = new stdClass();

        $o->id = $data->get_id();
        $o->price = $data->get_price();

        return $o;
    }


    /**
     * @param Boosterpack_model $data
     *
     * @return stdClass
     */
    private static function _preparation_contains(Boosterpack_model $data): stdClass
    {
        //TODO
    }
}

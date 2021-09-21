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
class Transaction_info_model extends Emerald_model
{
    const CLASS_TABLE = 'transaction_info';

    /** @var int User id */
    protected $user_ud;
    /** @var float amount */
    protected $amount;
    /** @var string transacton type */
    protected $type;
    /** @var string transacton type */
    protected $info;

    /** @var string external transaction id */
    protected $external_id;
    /** @var int boosterpack id */
    protected $boosterpack_id;
    /** @var float boosterpack price */
    protected $boosterpack_price;
    /** @var int item id */
    protected $item_id;
    /** @var float item price */
    protected $item_price;


    /** @var string */
    protected $time_created;
    /** @var string */
    protected $time_updated;


    /**
     * @return string
     */
    public function get_type(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public function set_type(string $type): bool
    {
        $this->type = $type;
        return $this->save('type', $type);
    }

    /**
     * @return int
     */
    public function get_transaction_id(): int
    {
        return $this->transaction_id;
    }

    /**
     * @param int $transaction_id
     *
     * @return bool
     */
    public function set_transaction_id(int $transaction_id): bool
    {
        $this->transaction_id = $transaction_id;
        return $this->save('transaction_id', $transaction_id);
    }


    /**
     * @return string
     */
    public function get_info(): string
    {
        return $this->info;
    }

    /**
     * @param string $info
     *
     * @return bool
     */
    public function set_info(string $info): bool
    {
        $this->info = $info;
        return $this->save('info', $info);
    }

    /**
     * @return string
     */
    public function get_external_id(): ?string
    {
        return $this->external_id;
    }

    /**
     * @param string $external_id
     *
     * @return bool
     */
    public function set_external_id(string $external_id): bool
    {
        $this->external_id = $external_id;
        return $this->save('external_id', $external_id);
    }


    /**
     * @return int
     */
    public function get_boosterpack_id(): ?int
    {
        return $this->boosterpack_id;
    }

    /**
     * @param int $boosterpack_id
     *
     * @return bool
     */
    public function set_boosterpack_id(string $boosterpack_id): bool
    {
        $this->boosterpack_id = $boosterpack_id;
        return $this->save('boosterpack_id', $boosterpack_id);
    }

    /**
     * @return float
     */
    public function get_boosterpack_price(): ?float
    {
        return $this->boosterpack_price;
    }

    /**
     * @param string $boosterpack_price
     *
     * @return bool
     */
    public function set_boosterpack_price(string $boosterpack_price): bool
    {
        $this->boosterpack_price = $boosterpack_price;
        return $this->save('boosterpack_price', $boosterpack_price);
    }


    /**
     * @return int
     */
    public function get_item_id(): ?int
    {
        return $this->item_id;
    }


    /**
     * @param int $item_id
     *
     * @return bool
     */
    public function set_item_id(string $item_id): bool
    {
        $this->item_id = $item_id;
        return $this->save('item_id', $item_id);
    }

    /**
     * @return float
     */
    public function get_item_price(): ?float
    {
        return $this->item_price;
    }

    /**
     * @param string $item_price
     *
     * @return bool
     */
    public function set_item_price(string $item_price): bool
    {
        $this->item_price = $item_price;
        return $this->save('item_price', $item_price);
    }







    public static function get_all_by_transaction_id($transaction_id)
    {
        return static::transform_many(App::get_s()->from(self::CLASS_TABLE)->where(['transaction_id' => $transaction_id])->orderBy('time_created', 'ASC')->many());

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
    public function set_time_created(string $time_created): bool
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
    public function set_time_updated(string $time_updated): bool
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
        return [];
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


    public static function get_all()
    {
        return static::transform_many(App::get_s()->from(self::CLASS_TABLE)->many());
    }


    /**
     * @param Boosterpack_model $data
     * @param string $preparation
     *
     * @return stdClass|stdClass[]
     */
    public static function preparation(Boosterpack_model $data, string $preparation = 'default')
    {
        switch ($preparation) {
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
        return new stdClass;
        //TODO
    }
}

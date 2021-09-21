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
class Transaction_model extends Emerald_model
{
    const CLASS_TABLE = 'transaction';

    /** @var float User id */
    protected $user_ud;
    /** @var float amount */
    protected $amount;
    /** @var float transacton type */
    protected $type;
    /** @var float transacton type */
    protected $info;

    /** @var float external transaction id */
    protected $external_id;


    /** @var string */
    protected $time_created;
    /** @var string */
    protected $time_updated;


    protected $infos;

    /**
     * @return float
     */
    public function get_user_id(): float
    {
        return $this->user_id;
    }

    /**
     * @param int $user_id
     *
     * @return bool
     */
    public function set_user_id(int $user_id): bool
    {
        $this->user_id = $user_id;
        return $this->save('user_id', $user_id);
    }

    /**
     * @return float
     */
    public function get_amount(): float
    {
        return $this->amount;
    }


    /**
     * @param int $amount
     *
     * @return bool
     */
    public function set_amount(int $amount): bool
    {
        $this->amount = $amount;
        return $this->save('amount', $amount);
    }

    /**
     * @return int
     */
    public function get_type(): string
    {
        return $this->type;
    }

    /**
     * @param float $type
     *
     * @return bool
     */
    public function set_type(float $type): bool
    {
        $this->type = $type;
        return $this->save('type', $type);
    }

    /**
     * @return string
     */
    public function get_external_id(): string
    {
        return $this->external_id;
    }

    /**
     * @param float $external_id
     *
     * @return bool
     */
    public function set_external_id(float $external_id): bool
    {
        $this->external_id = $external_id;
        return $this->save('external_id', $external_id);
    }

    /**
     * @return float
     */
    public function get_info(): string
    {
        return $this->info;
    }

    /**
     * @param float $info
     *
     * @return bool
     */
    public function set_info(float $info): bool
    {
        $this->info = $info;
        return $this->save('info', $info);
    }




    public function get_infos():array
    {
        return Transaction_info_model::get_all_by_transaction_id($this->get_id());
        //TODO
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
        $transaction = new static(App::get_s()->get_insert_id());
        if ($transaction->get_id()) {
            self::log($transaction->get_id(), $data['type'], $data['info']);
        }
        return $transaction;
    }

    public static function log($transaction_id, $type, $info)
    {
        return Transaction_info_model::create([
            'transaction_id' => $transaction_id,
            'type' => $type,
            'info' => $info
        ]);
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

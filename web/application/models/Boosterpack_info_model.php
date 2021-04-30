<?php
namespace Model;

use App;
use Cake\Database\Exception;
use System\Emerald\Emerald_model;

/**
 * Created by PhpStorm.
 * User: mr.incognito
 * Date: 27.01.2020
 * Time: 10:10
 */
class Boosterpack_info_model extends Emerald_model {
    const CLASS_TABLE = 'boosterpack_info';

    /** @var float Цена бустерпака */
    protected $boosterpack_id;
    /** @var float Банк, который наполняется */
    protected $item_id;

    protected $item;

    /**
     * @return float
     */
    public function get_boosterpack_id(): int
    {
        return $this->boosterpack_id;
    }

    /**
     * @param float $boosterpack_id
     *
     * @return bool
     */
    public function set_boosterpack_id(int $boosterpack_id): bool
    {
        $this->boosterpack_id = $boosterpack_id;

        return $this->save('boosterpack_id', $boosterpack_id);
    }

    /**
     * @return float
     */
    public function get_item_id(): int
    {
        return $this->item_id;
    }

    /**
     * @param float $item_id
     *
     * @return bool
     */
    public function set_item_id(int $item_id): bool
    {
        $this->item_id = $item_id;

        return $this->save('item_id', $item_id);
    }


    //GENERATED

    /**
     * @return Item_model
     */
    public function get_item(): Item_model
    {
        if ( ! empty($this->item))
        {
            return $this->item;
        }

        try
        {
            $this->item = new Item_model($this->get_item_id());
        } catch (Exception $e)
        {
            $this->item = new Item_model();
        }

        return $this->item;
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

    public function delete(): bool
    {
        $this->is_loaded(TRUE);
        App::get_s()->from(self::CLASS_TABLE)->where(['id' => $this->get_id()])->delete()->execute();

        return App::get_s()->is_affected();
    }

    /**
     * @param int $boosterpack_id
     *
     * @return self[]
     */
    public static function get_by_boosterpack_id(int $boosterpack_id): array
    {
        $data = App::get_s()->from(self::CLASS_TABLE)
            ->where(['boosterpack_id' => $boosterpack_id])
            ->many();

        return self::transform_many($data);
    }
}

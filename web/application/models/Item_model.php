<?php

namespace Model;

use App;
use Cake\Database\Exception;
use System\Emerald\Emerald_model;
use stdClass;

/**
 * Created by PhpStorm.
 * User: mr.incognito
 * Date: 27.01.2020
 * Time: 10:10
 */
class Item_model extends Emerald_model {
    const CLASS_TABLE = 'items';

    protected $price;
    protected $name;

    /**
     * @return int
     */
    public function get_price(): int
    {
        return $this->price;
    }

    /**
     * @param int $price
     *
     * @return bool
     * @throws \ShadowIgniterException
     */
    public function set_price(int $price): bool
    {
        $this->price = $price;

        return $this->save('price', $price);
    }

    /**
     * @return string
     */
    public function get_name(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function set_name(string $name): bool
    {
        $this->name = $name;

        return $this->save('name', $name);
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
}

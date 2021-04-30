<?php

namespace System\Emerald;

use App;
use Exception;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionProperty;
use ShadowIgniterException;
use stdClass;
use System\Emerald\Exception\EmeraldModelLoadException;
use System\Emerald\Exception\EmeraldModelNoDataException;
use System\Emerald\Exception\EmeraldModelSaveException;
use System\Libraries\Core;
use Throwable;

class Emerald_model {

    protected $id = NULL;
    protected $data = [];

    protected $_can_save = TRUE;

    const CLASS_TABLE = NULL;


    public function __construct()
    {

    }

    public function get_table(): ?string
    {
        return static::CLASS_TABLE;
    }

    /**
     * @return bool
     */
    public function get_can_save(): bool
    {
        return $this->_can_save;
    }

    /**
     * @param bool $can_save
     *
     * @return bool
     */
    public function set_can_save(bool $can_save): bool
    {
        $this->_can_save = $can_save;
        return TRUE;
    }

    /**
     * @param null $id
     * @return $this
     * @throws ShadowIgniterException
     */
    public function set_id($id = NULL)
    {
        $this->is_sparrow_loaded();
        if ((int)$id > 0)
        {

            $this->data = App::get_s()->from($this->get_table())->where(['id' => $id])->one();

            if ( ! empty($this->data))
            {
                $this->map_sql_to_class();
            } else
            {
                throw new EmeraldModelNoDataException('No data with this id!:' . $id);
            }
        } else
        {
            if ( ! is_null($id))
            {
                throw new EmeraldModelNoDataException('Id error!');
            }
        }
        return $this;
    }

    /**
     * @return null|int
     */
    public function get_id(): ?int
    {
        return $this->id;
    }

    /**
     * @return $this
     * @throws ShadowIgniterException
     */
    public function reload()
    {
        return $this->set_id($this->id);
    }

    /**
     *
     */
    protected function map_sql_to_class()
    {
        foreach ($this->data as $k => $v)
        {
            $this->{$k} = $v;
        }
    }

    /**
     * @param null $key
     * @param null $value
     * @return bool
     * @throws ShadowIgniterException
     * @throws Exception
     */
    protected function save($key = NULL, $value = NULL): bool
    {
        $this->is_sparrow_loaded();
        if (is_null($key))
        {
            return FALSE;
        }

        if ( ! $this->_can_save)
        {
            throw new EmeraldModelSaveException('Cant save!');
        }

        if ($this->is_loaded(TRUE) && $this->get_id() != NULL)
        {
            if (is_bool($value))
            {
                $value = intval($value);
            }
            $affect = App::get_s()->from($this->get_table())->where(['id' => $this->id])->update([$key => $value])->execute();
            return ($affect && (App::get_s()->get_affected_rows() == 1));
        } else
        {
            return FALSE;
        }
    }


    /**
     * @param bool $hard
     * @return bool
     * @throws Exception
     */
    public function is_loaded(bool $hard = FALSE): bool
    {
        if ($hard)
        {
            if (empty($this->data) || $this->id == NULL)
            {
                throw new EmeraldModelLoadException('Object not loaded!');
            }
        }
        return ( ! empty($this->data));
    }


    public function __destruct()
    {
    }

    /**
     * @param array $fields
     * @param int $nesting_level
     * @return stdClass
     * @throws Exception
     */
    public function object_beautify(array $fields = [], int $nesting_level = 0): stdClass
    {
        $nesting_level++;
        $_info = new stdClass();

        if ( ! $this->is_loaded() || $nesting_level > Core::OBJECT_BEAUTIFY_NESTING_LEVEL)
        {
            $_info->is_loaded = FALSE;
            return $_info;
        } else
        {
            $_info->is_loaded = TRUE;
        }

        if (empty($fields))
        {
            $props = (new ReflectionClass($this))->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED);
            foreach ($props as $prop)
            {
                $fields[] = $prop->getName();
            }
        }

        foreach ($fields as $field)
        {

            $_get_method = 'get_' . $field;
            if (method_exists($this, $_get_method))
            {
                $prop = $this->$_get_method();
                if (is_object($prop))
                {
                    if ($prop instanceof Emerald_model)
                    {
                        $_info->$field = $prop->object_beautify([], $nesting_level);
                    } else
                    {
                        $_info->$field = get_class($prop);
                    }
                    continue;
                }
                if (is_array($prop))
                {
                    $_info->$field = [];
                    foreach ($prop as $key_pro => $p)
                    {
                        if ($prop instanceof Emerald_model)
                        {
                            $_info->$field[$key_pro] = $p->object_beautify([], $nesting_level);
                        } else
                        {
                            $_info->$field[$key_pro] = $p;
                        }
                    }
                    continue;
                }

                $_info->$field = $prop;
            }
        }

        return $_info;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function set(array $data)
    {

        $this->data = $data;
        if ( ! empty($this->data))
        {
            $this->map_sql_to_class();
        } else
        {
            throw new EmeraldModelNoDataException('wrong data');
        }

        return $this;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function __debugInfo(): array
    {
        return (array)$this->object_beautify();
    }

    /**
     * @throws ShadowIgniterException
     */
    private function is_sparrow_loaded()
    {
        if ( ! class_exists('Sparrow'))
        {
            throw new ShadowIgniterException('Sparrow should be loaded before model accessing!');
        }
    }

    /**
     * @param array $data
     * @param string $preparation
     * @param string|null $key
     * @return array
     */
    public static function preparation_many(array $data, string $preparation = 'default', string $key = NULL): array
    {
        $ret = [];
        if (is_null($key))
        {
            foreach ($data as $d)
            {
                $ret[] = static::preparation($d, $preparation);
            }
        } else
        {
            foreach ($data as $d)
            {
                $ret[$d->{$key}()] = static::preparation($d, $preparation);
            }
        }

        return $ret;
    }

    /**
     * @param array $data
     * @return static[]
     */
    protected static function transform_many(array $data): array
    {
        $objs = [];

        foreach ($data as $d)
        {
            $objs[] = (new static())->set($d);
        }
        return $objs;
    }


    /**
     * @param array $data
     * @return static
     */
    protected static function transform_one(array $data)
    {
        if (empty($data))
        {
            return (new static());
        }
        return (new static())->set($data);
    }

}

<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class EmeraldModelNoDataException extends RuntimeException {
}

class EmeraldModelLoadException extends RuntimeException {
}

class EmeraldModelSaveException extends RuntimeException {
}

class CI_Emerald_Model {

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
    public function get_can_save()
    {
        return $this->_can_save;
    }

    /**
     * @param bool $can_save
     *
     * @return bool
     */
    public function set_can_save(bool $can_save)
    {
        $this->_can_save = $can_save;
        return TRUE;
    }

    /**
     * @param $id
     * @param bool $for_update
     * @return $this
     * @throws Exception
     */
    public function set_id($id = NULL, bool $for_update = FALSE)
    {
        $this->is_sparrow_loaded();
        if ((int)$id > 0)
        {
            if ($for_update)
            {
                $this->data = App::get_ci()->s->from($this->get_table())->where(['id' => $id])->for_update()->one();
            } else
            {
                $this->data = App::get_ci()->s->from($this->get_table())->where(['id' => $id])->one();
            }
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
    public function get_id()
    {
        return $this->id;
    }

    /**
     * @param bool $for_update
     * @return $this
     * @throws Exception
     */
    public function reload(bool $for_update = FALSE)
    {
        return $this->set_id($this->id, $for_update);
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
     */
    protected function save($key = NULL, $value = NULL)
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
            $affect = App::get_ci()->s->from($this->get_table())->where(['id' => $this->id])->update([$key => $value])->execute();
            return ($affect && (App::get_ci()->s->get_affected_rows() == 1)
            );
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
    public function is_loaded(bool $hard = FALSE)
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
     * @return stdClass
     */
    public function object_beautify(array $fields = [])
    {
        $_info = new stdClass();

        if ( ! $this->is_loaded())
        {
            $_info->is_loaded = FALSE;
            return $_info;
        } else
        {
            $_info->is_loaded = TRUE;
        }


        //if empty fields - return all public|protected fields
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
            //if getter exist
            $_get_method = 'get_' . $field;
            if (method_exists($this, $_get_method))
            {
                $prop = $this->$_get_method();
                if (is_object($prop))
                {
                    if ($prop instanceof CI_Emerald_Model)
                    {
                        $_info->$field = $prop->object_beautify();
                    }
                    $_info->$field = 'OBJECT';
                    continue;
                }
                if (is_array($prop))
                {
                    $_info->$field = [];
                    foreach ($prop as $p)
                    {
                        if ($prop instanceof CI_Emerald_Model)
                        {
                            $_info->$field[] = $p->object_beautify();
                        } else
                        {
                            $_info->$field[] = $p;
                        }
                    }
                    continue;
                }

                $_info->$field = $prop;
            }
        }

        return $_info;
    }

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

    public function __debugInfo()
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
}

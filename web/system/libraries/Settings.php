<?php

namespace System\Libraries;

use App;
use Exception;

/*

CREATE TABLE `settings` (
  `id` int UNSIGNED NOT NULL,
  `key` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `type` enum('string','int','bool','float','json') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'string',
  `info` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`);

ALTER TABLE `settings`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

 */

class Settings {
    const TABLE = 'settings';
    const CACHE_TTL = 60;
    const SETTINGS_SELECT = '`key`, `value`, `info`, `type`';

    const TYPE_STRING = 'string';
    const TYPE_INT = 'int';
    const TYPE_BOOL = 'bool';
    const TYPE_FLOAT = 'float';
    const TYPE_JSON = 'json';

    /** @var array */
    protected $data;
    /** @var \CI_Cache */
    protected $cache;
    /** @var static */
    protected static $settings;

    public function __construct()
    {
        // Workaround before we make own cache classes
        $this->cache = App::get_ci()->cache;
        if ($this->load_from_cache())
        {
            return;
        }

        $this->get_from_db();
        $this->save_to_cache();
    }

    /**
     * @param string $key
     * @return mixed
     * @throws Exception
     */
    public function __get(string $key)
    {
        if ( ! isset($this->data[$key]))
        {
            throw new Exception(sprintf('Error with get \'%s\' setting data.', $key));
        }

        return $this->data[$key];
    }

    // переопределение параметра

    /**
     * @param string $key
     * @param $value
     * @return bool
     */
    public function __set(string $key, $value): bool
    {
        if (isset($this->data[$key]))
        {
            $this->data[$key] = $value;
            return TRUE;
        }

        return FALSE;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function __isset(string $key): bool
    {
        return isset($this->data[$key]);
    }

    /**
     * @return bool
     */
    protected function load_from_cache(): bool
    {
        if (is_null($this->cache))
        {
            return FALSE;
        }

        $data = $this->cache->get(Core::CACHE_KEY_SETTINGS);

        if ($data && is_array($data))
        {
            $this->data = $data;
            return TRUE;
        }
        return FALSE;
    }

    /**
     * @return bool
     */
    protected function get_from_db(): bool
    {
        $q = App::get_s()->from(static::TABLE)->select(static::SETTINGS_SELECT)->many();

        foreach ($q as $row_value)
        {
            $this->data[$row_value['key']] = $row_value['value'];
        }

        return TRUE;
    }

    /**
     * @return bool
     */
    protected function save_to_cache(): bool
    {
        if (is_null($this->cache))
        {
            return FALSE;
        }
        $this->cache->save(Core::CACHE_KEY_SETTINGS, $this->data, self::CACHE_TTL);
        return TRUE;
    }

    /**
     * @return array
     */
    public static function get_all(): array
    {
        return App::get_s()->from(static::TABLE)->sortAsc('`key`')->select(static::SETTINGS_SELECT)->many();
    }

    /**
     * @return bool
     */
    public function update(): bool
    {
        $this->get_from_db();
        $this->save_to_cache();
        return TRUE;
    }

    /** Quotes are nesessary here, because key and value are reserved in MySQL
     * @param string $key
     * @param string $value
     * @return bool
     * @throws Exception
     */
    public static function change(string $key, string $value): bool
    {
        $db_value = $sql = App::get_s()
            ->from(static::TABLE)
            ->where(['`key`' => $key])
            ->select('`type`')
            ->one();

        if (empty($db_value))
        {
            throw new Exception(sprintf('Wrong key %s - no such setting in database', $key));
        }

        if ( ! self::validate_param($key, $db_value['type'], $value))
        {
            throw new Exception(sprintf('Wrong value %s[%s] for key %s', json_encode($value), $db_value['type'], $key));
        }

        $sql = App::get_s()
            ->from(static::TABLE)
            ->where(['`key`' => $key])
            ->update([
                '`value`' => $value,
            ]);

        $sql->execute();

        return App::get_s()->is_affected();
    }

    /**
     * @param string $key
     * @param string $type
     * @param string $value
     * @return bool
     */
    public static function validate_param(string $key, string $type, string $value): bool
    {
        if (empty($key) || empty($type))
        {
            return FALSE;
        }

        switch ($type)
        {
            case App::get_settings()::TYPE_INT:
                return (bool)preg_match('#^[0-9]+$#', $value);
            case App::get_settings()::TYPE_BOOL:
                return (bool)in_array($value, [0, 1]);
            case App::get_settings()::TYPE_FLOAT:
                return (bool)preg_match('#^([0-9]+\.?[0-9]*)$#', $value);
            case App::get_settings()::TYPE_JSON:
                return isJSON($value);
            case App::get_settings()::TYPE_STRING:
                return is_string($value);
            default:
                return FALSE;
        }
    }
}

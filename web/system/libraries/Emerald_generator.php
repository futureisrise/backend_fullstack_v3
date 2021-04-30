<?php
namespace System\Libraries;

use App;
use CriticalException;

defined('TAB1') or define('TAB1', "\t");

class Emerald_generator {


    const PROTECTED_ID = 'id';

    const DATA_TYPE_ENUM = 'enum';
    const DATA_TYPE_STRING = 'string';
    const DATA_TYPE = [
        'int' => [
            'tinyint',
            'smallint',
            'mediumint',
            'int',
            'bigint'
        ],
        'float' => [
            'decimal',
            'float',
            'double',
            'real',
        ],
//		'string' => [
//			'enum',
//			'set',
//			'varchar',
//			'char',
//			'datetime',
//		],
    ];

    private const GETTER = '
    ' . TAB1 . '/**
    ' . TAB1 . '* @return %s
    ' . TAB1 . '*/
    ' . TAB1 . 'public function get_%s(): %s
    ' . TAB1 . '{
    ' . TAB1 . '    return $this->%2$s;
    ' . TAB1 . '}';

    private const SETTER = '
    ' . TAB1 . '/**
    ' . TAB1 . '* @param %1$s $%2$s
    ' . TAB1 . '* 
    ' . TAB1 . '* @return bool
    ' . TAB1 . '*/
    ' . TAB1 . 'public function set_%2$s(%3$s $%2$s%4$s): bool
    ' . TAB1 . '{
    ' . TAB1 . '    $this->%2$s = $%2$s;
    ' . TAB1 . '    return $this->save(\'%2$s\', $%2$s);
    ' . TAB1 . '}';

    private const SQL_STRUCTURE = '
		SELECT *
		FROM INFORMATION_SCHEMA.COLUMNS
		WHERE TABLE_SCHEMA = "%s"
		AND TABLE_NAME = "%s"
		ORDER BY `ORDINAL_POSITION` ASC		
	';

    private static $db_config;

    private static $db_table_schema;
    private static $emerald_model;
    private static $emerald_model_name;
    private static $emerald_model_enums;

    protected static $file_content;

    public function __construct()
    {
        App::get_ci()->load->helper('file');
    }

    public static function generate_model(string $class_name, string $extends = 'System\Emerald\Emerald_model'): bool
    {
        self::$file_content = NULL;
        self::load_db_config();

        self::$db_table_schema = App::get_s()->sql(sprintf(self::SQL_STRUCTURE, self::get_db_config('database'), $class_name))->many();

        if (empty(self::$db_table_schema))
        {
            echo sprintf('Wrong table name or database name! Table: %s Database: %s%s', $class_name, self::get_db_config('database'), PHP_EOL);
            return FALSE;
        }

        self::proccess_database($class_name);

        self::file_model_generate_header($extends);

        self::file_model_generate_content();

        self::file_model_generate_footer();

        $result_path = self::create_file(self::$emerald_model_name, self::$file_content);

        echo 'Model generated! Check path: ' . $result_path . PHP_EOL;

        if (self::is_enums())
        {
            echo 'Generating enums! We have ' . count(self::$emerald_model_enums) . ' ... ' . PHP_EOL;
            foreach (self::$emerald_model_enums as $enum)
            {

                $enum_conetent = self::generate_enum($enum);

                $result_path = self::create_file($class_name . '_' . $enum['name'], $enum_conetent);
                echo 'Enum generated for field ' . $enum['name'] . '! Check path: ' . $result_path . PHP_EOL;
            }
        }
        return TRUE;
    }

    protected static function detect_data_type(string $data_type): string
    {
        foreach (self::DATA_TYPE as $key => $types)
        {
            if (in_array($data_type, $types))
            {
                return $key;
            }
        }
        return self::DATA_TYPE_STRING;
    }

    protected static function proccess_database(string $table_name)
    {
        // define field data types
        self::$emerald_model_name = $table_name;
        self::$emerald_model = [];
        foreach (self::$db_table_schema as $idx => $col)
        {
            $field = [];
            if ($col['COLUMN_NAME'] == self::PROTECTED_ID)
            {
                continue;
            }

            $field['name'] = $col['COLUMN_NAME'];
            $field['type'] = self::detect_data_type($col['DATA_TYPE']);
            $field['is_nullable'] = $col['IS_NULLABLE'] == 'YES';

            self::$emerald_model[] = $field;
        }

        // find enums
        self::$emerald_model_enums = [];
        foreach (self::$db_table_schema as $idx => $col)
        {
            if ($col['COLUMN_NAME'] == self::PROTECTED_ID)
            {
                continue;
            }
            if ($col['DATA_TYPE'] == self::DATA_TYPE_ENUM)
            {
                $enum = [];
                $enum['name'] = $col['COLUMN_NAME'];
                $enum['type'] = self::detect_data_type($col['DATA_TYPE']);

                $output_array = [];
                if (preg_match('/\((.+)\)/', $col['COLUMN_TYPE'], $output_array))
                {
                    $enum['values'] = explode(',', $output_array[1]);
                    foreach ($enum['values'] as &$enum_val)
                    {
                        $enum_val = trim($enum_val, '\'');
                    }
                }
                self::$emerald_model_enums[] = $enum;
            }
        }
        return TRUE;
    }

    protected static function is_enums(): bool
    {
        return ! empty(self::$emerald_model_enums);
    }


    protected static function file_model_generate_header(string $extends = 'My_Model')
    {
        self::$file_content = '<?php ' . PHP_EOL;
        self::$file_content .= 'class ' . ucfirst(self::$emerald_model_name);
        if ( ! empty($extends))
        {
            self::$file_content .= ' extends ' . $extends . PHP_EOL;
        } else
        {
            self::$file_content .= PHP_EOL;
        }
        self::$file_content .= '{' . PHP_EOL;
        self::$file_content .= PHP_EOL;

        self::$file_content .= TAB1 . sprintf('const CLASS_TABLE = \'%s\';', self::$emerald_model_name) . PHP_EOL;
        self::$file_content .= PHP_EOL;
    }

    protected static function file_model_generate_content()
    {
        // Property

        foreach (self::$emerald_model as $idx => $col)
        {
            $phpdoc = '/** @var ';
            $phpdoc .= ' ' . $col['type'];
            if ($col['is_nullable'])
            {
                $phpdoc .= '|null ';
            }
            $phpdoc .= '*/';

            self::$file_content .= TAB1 . $phpdoc . PHP_EOL;
            self::$file_content .= TAB1 . 'protected $' . $col['name'] . ';' . PHP_EOL;
        }


        // Accessors

        foreach (self::$emerald_model as $idx => $col)
        {
            $is_nullable = $col['is_nullable'] == 'YES';

            $is_nullable_phpdoc = $col['type'] . ($is_nullable ? '|null' : '');
            $return_type = ($is_nullable ? '?' : '') . $col['type'];
            $nullable_param = ($is_nullable ? ' = NULL' : '');

            $getter = sprintf(self::GETTER, $is_nullable_phpdoc, $col['name'], $return_type);

            self::$file_content .= TAB1 . $getter . PHP_EOL;

            $setter = sprintf(self::SETTER, $is_nullable_phpdoc, $col['name'], $col['type'], $nullable_param);

            self::$file_content .= TAB1 . $setter . PHP_EOL;
        }
    }

    protected static function file_model_generate_footer()
    {

        self::$file_content .= PHP_EOL;
        self::$file_content .= TAB1 . '/**' . PHP_EOL;
        self::$file_content .= TAB1 . ' * ' . ucfirst(self::$emerald_model_name) . ' constructor.' . PHP_EOL;
        self::$file_content .= TAB1 . ' * @param int|null $id' . PHP_EOL;
        self::$file_content .= TAB1 . ' * @throws Exception' . PHP_EOL;
        self::$file_content .= TAB1 . ' */' . PHP_EOL;
        self::$file_content .= TAB1 . 'function __construct($id = NULL)' . PHP_EOL;
        self::$file_content .= TAB1 . '{' . PHP_EOL;
        self::$file_content .= TAB1 . TAB1 . 'parent::__construct();' . PHP_EOL;
        self::$file_content .= TAB1 . TAB1 . '$this->set_id($id);' . PHP_EOL;
        self::$file_content .= TAB1 . '}' . PHP_EOL;
        self::$file_content .= PHP_EOL;


        self::$file_content .= PHP_EOL;
        self::$file_content .= TAB1 . '/**' . PHP_EOL;
        self::$file_content .= TAB1 . ' * @param bool $for_update' . PHP_EOL;
        self::$file_content .= TAB1 . ' * @return ' . ucfirst(self::$emerald_model_name) . PHP_EOL;
        self::$file_content .= TAB1 . ' * @throws Exception' . PHP_EOL;
        self::$file_content .= TAB1 . ' */' . PHP_EOL;
        self::$file_content .= TAB1 . 'public function reload(): ' . ucfirst(self::$emerald_model_name) . PHP_EOL;
        self::$file_content .= TAB1 . '{' . PHP_EOL;
        self::$file_content .= TAB1 . TAB1 . 'parent::reload();' . PHP_EOL;
        self::$file_content .= TAB1 . TAB1 . 'return $this;' . PHP_EOL;
        self::$file_content .= TAB1 . '}' . PHP_EOL;
        self::$file_content .= PHP_EOL;


        self::$file_content .= PHP_EOL;

        self::$file_content .= TAB1 . '/**' . PHP_EOL;
        self::$file_content .= TAB1 . ' * @param array $data' . PHP_EOL;
        self::$file_content .= TAB1 . ' * @return static' . PHP_EOL;
        self::$file_content .= TAB1 . ' * @throws Exception' . PHP_EOL;
        self::$file_content .= TAB1 . ' */' . PHP_EOL;
        self::$file_content .= TAB1 . 'public static function create(array $data): ' . ucfirst(self::$emerald_model_name) . PHP_EOL;
        self::$file_content .= TAB1 . '{' . PHP_EOL;
        self::$file_content .= TAB1 . '	App::get_s()->from(self::CLASS_TABLE)->insert($data)->execute();' . PHP_EOL;
        self::$file_content .= TAB1 . '	return new static(App::get_s()->get_insert_id());' . PHP_EOL;
        self::$file_content .= TAB1 . '}' . PHP_EOL;

        self::$file_content .= TAB1 . '/**' . PHP_EOL;
        self::$file_content .= TAB1 . ' * @return bool' . PHP_EOL;
        self::$file_content .= TAB1 . ' * @throws Exception' . PHP_EOL;
        self::$file_content .= TAB1 . ' */' . PHP_EOL;
        self::$file_content .= TAB1 . 'public function delete(): bool' . PHP_EOL;
        self::$file_content .= TAB1 . '{' . PHP_EOL;
        self::$file_content .= TAB1 . '	$this->is_loaded(TRUE);' . PHP_EOL;
        self::$file_content .= TAB1 . '	App::get_s()->from(self::get_table())->where([\'id\' => $this->get_id()])->delete()->execute();' . PHP_EOL;
        self::$file_content .= TAB1 . '	return App::get_s()->is_affected();' . PHP_EOL;
        self::$file_content .= TAB1 . '}' . PHP_EOL;
        self::$file_content .= PHP_EOL;

        self::$file_content .= '}' . PHP_EOL;
    }

    protected static function generate_enum(array $enum): string
    {
        $content = '';

        $content = '<?php ' . PHP_EOL;
        $content .= PHP_EOL;
        $content .= 'use System\Emerald\Emerald_enum;' . PHP_EOL;
        $content .= PHP_EOL;
        $content .= 'class ' . ucfirst(self::$emerald_model_name) . '_' . $enum['name'] . ' extends Emerald_enum' . PHP_EOL;
        $content .= '{' . PHP_EOL;
        $content .= PHP_EOL;

        foreach ($enum['values'] as $value)
        {
            $content .= TAB1 . sprintf('const %s = \'%s\';', strtoupper($value), $value) . PHP_EOL;
        }
        $content .= '}' . PHP_EOL;
        $content .= PHP_EOL;

        return $content;
    }


    protected static function create_file(string $file_name, string $content): string
    {
        $path = self::get_ci_cache_path() . ucfirst($file_name) . '.php';
        write_file($path, $content);
        return $path;
    }

    private static function get_ci_cache_path(): string
    {
        $path = APPPATH . 'cache/';
        return $path;
    }


    protected static function get_db_config(string $key)
    {
        if (empty(self::$db_config['default']))
        {
            throw new CriticalException('Cant get db config! Please load it firts!');
        }
        return self::$db_config['default'][$key];
    }


    private static function load_db_config(): bool
    {
        self::$db_config = [];

        $file = 'database';
        foreach (find_config_files_path($file) as $location)
        {
            $file_path = APPPATH . 'config/' . $location . '.php';

//                log_message('error', '['.PROJECT.']Config try search: '.$file_path);

            if ( ! file_exists($file_path))
            {
                continue;
            }

            include($file_path);

            if ( ! isset($db) or ! is_array($db))
            {
                show_error('Your ' . $file_path . ' file does not appear to contain a valid configuration array.');
            }


            $loaded = TRUE;
            log_message('debug', 'Config file loaded: ' . $file_path);
        }

        if ( ! $loaded)
        {
            show_error('Cant find ' . $file . '.php config file!');
        }

        self::$db_config = $db;

        return TRUE;
    }


}
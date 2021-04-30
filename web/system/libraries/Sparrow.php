<?php

/**
 * Sparrow: A simple database toolkit.
 *
 * @copyright Copyright (c) 2011, Mike Cao <mike@mikecao.com>
 * @license   MIT, http://www.opensource.org/licenses/mit-license.php
 */
class Sparrow {
    protected $table;
    protected $where;
    protected $joins;
    protected $order;
    protected $groups;
    protected $having;
    protected $distinct;
    protected $limit;
    protected $offset;
    protected $for_update;
    protected $sql;
    protected $unionSql;

    protected $db;
    protected $db_type;
    protected $cache;
    protected $cache_type;
    protected $stats;
    protected $query_time;
    protected $class;

    protected $transLevel = 0;
    protected static $savepointTransactions = ['pgsql', 'mysqli'];

    protected static $db_types = [
        'pdo', 'mysqli', 'pgsql', 'sqlite', 'sqlite3'
    ];
    protected static $cache_types = [
        'memcached', 'memcache', 'xcache'
    ];

    private $last_query;
    private $num_rows;
    private $insert_id;
    private $affected_rows;
    private $affected;
    public $is_cached = FALSE;
    public $stats_enabled = FALSE;
    public $show_sql = FALSE;
    public $key_prefix = '';

    /**
     * Class constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return string
     */
    public function get_last_query(): string
    {
        return $this->last_query;
    }

    /**
     * @return int
     */
    public function get_num_rows(): int
    {
        return $this->num_rows;
    }

    /**
     * @return int
     */
    public function get_insert_id(): int
    {
        return $this->insert_id;
    }

    /**
     * @return int
     */
    public function get_affected_rows(): int
    {
        return $this->affected_rows;
    }

    /**
     * @return bool
     */
    public function is_affected(): bool
    {
        return $this->affected;
    }


    /*** Core Methods ***/

    /**
     * Joins string tokens into a SQL statement.
     *
     * @param string $sql SQL statement
     * @param string $input Input string to append
     * @return string New SQL statement
     */
    public function build($sql, $input)
    {
        return (strlen($input) > 0) ? ($sql . ' ' . $input) : $sql;
    }

    /**
     * Parses a connection string into an object.
     *
     * @param string $connection Connection string
     * @return array Connection information
     * @throws Exception For invalid connection string
     */
    public function parseConnection($connection)
    {
        $url = parse_url($connection);

        if (empty($url))
        {
            throw new Exception('Invalid connection string.');
        }

        $cfg = [];

        $cfg['type'] = isset($url['scheme']) ? $url['scheme'] : $url['path'];
        $cfg['hostname'] = isset($url['host']) ? $url['host'] : NULL;
        $cfg['database'] = isset($url['path']) ? substr($url['path'], 1) : NULL;
        $cfg['username'] = isset($url['user']) ? $url['user'] : NULL;
        $cfg['password'] = isset($url['pass']) ? $url['pass'] : NULL;
        $cfg['port'] = isset($url['port']) ? $url['port'] : NULL;

        return $cfg;
    }

    /**
     * Gets the query statistics.
     */
    public function getStats()
    {
        $this->stats['total_time'] = 0;
        $this->stats['num_queries'] = 0;
        $this->stats['num_rows'] = 0;
        $this->stats['num_changes'] = 0;

        if (isset($this->stats['queries']))
        {
            foreach ($this->stats['queries'] as $query)
            {
                $this->stats['total_time'] += $query['time'];
                $this->stats['num_queries'] += 1;
                $this->stats['num_rows'] += $query['rows'];
                $this->stats['num_changes'] += $query['changes'];
            }
        }

        $this->stats['avg_query_time'] =
            $this->stats['total_time'] /
            (float)(($this->stats['num_queries'] > 0) ? $this->stats['num_queries'] : 1);

        return $this->stats;
    }

    /**
     * Checks whether the table property has been set.
     */
    public function checkTable()
    {
        if ( ! $this->table)
        {
            throw new Exception('Table is not defined.');
        }
    }

    /**
     * Checks whether the class property has been set.
     */
    public function checkClass()
    {
        if ( ! $this->class)
        {
            throw new Exception('Class is not defined.');
        }
    }

    /**
     * Resets class properties.
     */
    public function reset()
    {
        $this->where = '';
        $this->joins = '';
        $this->order = '';
        $this->groups = '';
        $this->having = '';
        $this->distinct = '';
        $this->limit = '';
        $this->offset = '';
        $this->for_update = '';
        $this->sql = '';
    }

    /*** SQL Builder Methods ***/


    /**
     * Parses a condition statement.
     *
     * @param string $field Database field
     * @param string $value Condition value
     * @param string $join Joining word
     * @param boolean $escape Escape values setting
     * @return string Condition as a string
     * @throws Exception For invalid where condition
     */
    protected function parseCondition($field, $value = NULL, $join = '', $escape = TRUE)
    {
        if (is_string($field))
        {
            if ($value === FALSE)
            {
                return $join . ' ' . trim($field);
            }
            $operator = '';
            if (strpos($field, ' ') !== FALSE)
            {
                [$field, $operator] = explode(' ', $field);
            }
            if ( ! empty($operator))
            {
                switch ($operator)
                {
                    case '%':
                        $condition = ' LIKE ';
                        break;
                    case '!%':
                        $condition = ' NOT LIKE ';
                        break;
                    case '@':
                        $condition = ' IN ';
                        break;
                    case '!@':
                        $condition = ' NOT IN ';
                        break;
                    case '!=':
                        $condition = ($value === NULL) ? ' IS NOT ' : $operator;
                        break;
                    default:
                        $condition = $operator;
                }
            } else
            {
                if ($value === NULL) $condition = ' IS '; else $condition = '=';
            }
            if (empty($join))
            {
                $join = ($field[0] == '|') ? ' OR' : ' AND';
            }
            if (is_array($value))
            {
                if (strpos($operator, '@') === FALSE) $condition = ' IN ';
                if ( ! empty($value))
                {
                    $value = '(' . implode(',', array_map([$this, 'quote'], $value)) . ')';
                } else
                {
                    return $join . ' ' . 'FALSE';
                }
            } else
            {
                $value = ($escape && ! self::is_numeric_not_exp($value)) ? $this->quote($value) : $value;
            }
            if ($value === NULL) $value = 'NULL';
            return $join . ' ' . str_replace('|', '', $field) . $condition . $value;
        } else if (is_array($field))
        {
            $str = '';
            foreach ($field as $key => $value)
            {
                $str .= $this->parseCondition($key, $value, $join, $escape);
                $join = '';
            }
            return $str;
        } else
        {
            throw new Exception('Invalid where condition.');
        }
    }

    public static function is_numeric_not_exp($a): bool
    {
        return is_numeric($a)
            && (
                ! is_string($a)
                || (
                    (
                        strpos($a, 'e') === FALSE
                        && strpos($a, 'E') === FALSE
                    )
                    || strpos($a, 'E-') !== FALSE
                    || strpos($a, 'E+') !== FALSE
                )
            );
    }

    /**
     * Sets the table.
     *
     * @param string $table Table name
     * @param boolean $reset Reset class properties
     * @return self reference
     */
    public function from($table, $reset = TRUE)
    {
        $this->table = $table;
        if ($reset)
        {
            $this->reset();
        }

        return $this;
    }

    /**
     * Adds a table join.
     *
     * @param string $table Table to join to
     * @param array $fields Fields to join on
     * @param string $type Type of join
     * @return self reference
     * @throws Exception For invalid join type
     */
    public function join($table, array $fields, $type = 'INNER')
    {
        static $joins = [
            'INNER',
            'LEFT OUTER',
            'RIGHT OUTER',
            'FULL OUTER'
        ];

        if ( ! in_array($type, $joins))
        {
            throw new Exception('Invalid join type.');
        }

        $this->joins .= ' ' . $type . ' JOIN ' . $table .
            $this->parseCondition($fields, NULL, ' ON', FALSE);

        return $this;
    }

    /**
     * Adds a left table join.
     *
     * @param string $table Table to join to
     * @param array $fields Fields to join on
     * @return self reference
     */
    public function leftJoin($table, array $fields)
    {
        return $this->join($table, $fields, 'LEFT OUTER');
    }

    /**
     * Adds a right table join.
     *
     * @param string $table Table to join to
     * @param array $fields Fields to join on
     * @return self reference
     */
    public function rightJoin($table, array $fields)
    {
        return $this->join($table, $fields, 'RIGHT OUTER');
    }

    /**
     * Adds a full table join.
     *
     * @param string $table Table to join to
     * @param array $fields Fields to join on
     * @return self reference
     */
    public function fullJoin($table, array $fields)
    {
        return $this->join($table, $fields, 'FULL OUTER');
    }

    /**
     * Adds where conditions.
     *
     * @param string|array $field A field name or an array of fields and values.
     * @param string $value A field value to compare to
     * @return self reference
     */
    public function where($field, $value = FALSE)
    {
        $join = (empty($this->where)) ? 'WHERE' : '';
        $this->where .= $this->parseCondition($field, $value, $join);

        return $this;
    }


    /**
     * Adds an ascending sort for a field.
     *
     * @param string $field Field name
     * @return self reference
     */
    public function sortAsc($field)
    {
        return $this->orderBy($field, 'ASC');
    }

    /**
     * Adds an descending sort for a field.
     *
     * @param string $field Field name
     * @return self reference
     */
    public function sortDesc($field)
    {
        return $this->orderBy($field, 'DESC');
    }

    /**
     * Adds fields to order by.
     *
     * @param string|array $field Field name
     * @param string $direction Sort direction
     * @return self reference
     */
    public function orderBy($field, $direction = 'ASC')
    {
        $join = (empty($this->order)) ? 'ORDER BY' : ',';

        if (is_array($field))
        {
            foreach ($field as $key => $value)
            {
                $field[$key] = $value . ' ' . $direction;
            }
        } else
        {
            $field .= ' ' . $direction;
        }

        $fields = (is_array($field)) ? implode(', ', $field) : $field;

        $this->order .= $join . ' ' . $fields;

        return $this;
    }

    /**
     * @param string|array $fields
     * @param string|null $direct
     * @return $this
     */
    public function order($fields, string $direct = NULL)
    {
        $join = (empty($this->order)) ? 'ORDER BY' : ',';

        if ( ! is_array($fields))
        {
            $fields = $direct === NULL ? [$fields] : [$fields => $direct];
        }
        $conditions = [];
        foreach ($fields as $key => $field)
        {
            $conditions[] = is_numeric($key) ? $field : $key . ' ' . $field;
        }

        $this->order .= $join . ' ' . implode(', ', $conditions);

        return $this;
    }

    /**
     * Adds fields to group by.
     *
     * @param string|array $field Field name or array of field names
     * @return self reference
     */
    public function groupBy($field)
    {
        $join = (empty($this->groups)) ? 'GROUP BY' : ',';
        $fields = (is_array($field)) ? implode(',', $field) : $field;

        $this->groups .= $join . ' ' . $fields;

        return $this;
    }

    /**
     * Adds having conditions.
     *
     * @param string|array $field A field name or an array of fields and values.
     * @param string $value A field value to compare to
     * @return self reference
     */
    public function having($field, $value = NULL)
    {
        $join = (empty($this->having)) ? 'HAVING' : '';
        $this->having .= $this->parseCondition($field, $value, $join);

        return $this;
    }

    /**
     * Adds a limit to the query.
     *
     * @param int $limit Number of rows to limit
     * @param int $offset Number of rows to offset
     * @return self reference
     */
    public function limit($limit, $offset = NULL)
    {
        if ($limit !== NULL)
        {
            $this->limit = 'LIMIT ' . $limit;
        }
        if ($offset !== NULL)
        {
            $this->offset($offset);
        }

        return $this;
    }

    public function for_update()
    {
        $this->for_update = 'for update';

        return $this;
    }

    /**
     * Adds an offset to the query.
     *
     * @param int $offset Number of rows to offset
     * @param int $limit Number of rows to limit
     * @return self reference
     */
    public function offset($offset, $limit = NULL)
    {
        if ($offset !== NULL)
        {
            $this->offset = 'OFFSET ' . $offset;
        }
        if ($limit !== NULL)
        {
            $this->limit($limit);
        }

        return $this;
    }

    /**
     * Sets the distinct keyword for a query.
     */
    public function distinct($value = TRUE)
    {
        $this->distinct = ($value) ? 'DISTINCT' : '';

        return $this;
    }

    /**
     * Sets a between where clause.
     *
     * @param string $field Database field
     * @param string $value1 First value
     * @param string $value2 Second value
     */
    public function between($field, $value1, $value2)
    {
        $this->where(sprintf(
            '%s BETWEEN %s AND %s',
            $field,
            $this->quote($value1),
            $this->quote($value2)
        ));
    }

    /**
     * Builds a select query.
     *
     * @param array|string $fields Array of field names to select
     * @param int $limit Limit condition
     * @param int $offset Offset condition
     * @return self reference
     */
    public function select($fields = '*', $limit = NULL, $offset = NULL)
    {
        $this->checkTable();

        $fields = (is_array($fields)) ? implode(',', $fields) : $fields;
        $this->limit($limit, $offset);

        $this->sql([
            'SELECT',
            $this->distinct,
            $fields,
            'FROM',
            $this->table,
            $this->joins,
            $this->where,
            $this->groups,
            $this->having,
            $this->order,
            $this->limit,
            $this->offset,
            $this->for_update
        ]);

        return $this;
    }


    /**
     * Builds a union query
     *
     * @param bool $all
     * @return $this
     */
    public function union(bool $all = FALSE): Sparrow
    {
        $union = ' ';
        if (!empty($this->unionSql)) {
            $union = ' UNION ';
        }

        if ($all) {
            $union .= ' ALL ';
        }

        $this->unionSql .= $union . $this->getSql();

        return $this;
    }

    /**
     * Builds an insert query.
     *
     * @param array $data Array of key and values to insert
     * @param bool $ignore
     * @return self reference
     */
    public function insert(array $data, $ignore = FALSE)
    {
        $this->checkTable();

        if (empty($data)) return $this;

        $keys = implode(',', array_keys($data));
        $values = implode(',', array_values(
            array_map(
                [$this, 'quote'],
                $data
            )
        ));

        $this->sql([
            $ignore ? 'INSERT IGNORE INTO' : 'INSERT INTO',
            $this->table,
            '(' . $keys . ')',
            'VALUES',
            '(' . $values . ')'
        ]);

        return $this;
    }

    /**
     * @param array $data
     * @return self reference
     */
    public function insert_ignore(array $data)
    {
        return $this->insert($data, TRUE);
    }

    /**
     * @param array|string $data
     * @return self reference
     * @throws Exception
     */
    public function on_duplicate_key_update($data)
    {
        $this->checkTable();

        if (empty($data))
        {
            return $this;
        }
        if (empty($this->sql))
        {
            throw new Exception('No "insert" part in "on duplicate key update" query.');
        }

        $values = [];

        if (is_array($data))
        {
            foreach ($data as $key => $value)
            {
                $values[] = (is_numeric($key)) ? $value : $key . '=' . $this->quote($value);
            }
        } else
        {
            $values[] = (string)$data;
        }

        $this->sql([
            $this->sql,
            'ON DUPLICATE KEY UPDATE',
            implode(',', $values),
        ]);

        return $this;
    }

    /**
     * Lock table.
     * @link http://dev.mysql.com/doc/refman/5.7/en/lock-tables.html
     * @param strong $lock_type Array of key and values to insert
     * @return self reference
     */
    public function lock($lock_type = "READ")
    {
        $this->checkTable();
        if ($lock_type == '' || ! in_array($lock_type, ["READ", "WRITE"]))
        {
            $lock_type = "READ";
        }
        $this->sql([
            'LOCK TABLES',
            $this->table,
            $lock_type
        ]);
        return $this->execute();
    }

    /**
     * Unlock table.
     * @link http://dev.mysql.com/doc/refman/5.7/en/lock-tables.html
     * @return self reference
     */
    public function unlock()
    {
        $this->checkTable();
        $this->sql([
            'UNLOCK TABLES'
        ]);
        return $this->execute();
    }

    protected function nestable()
    {
        return in_array($this->db_type,
            self::$savepointTransactions);
    }

    /**
     * Transactions.
     * @link http://dev.mysql.com/doc/refman/5.7/en/commit.html
     * @return self Self reference
     */
    public function start_trans()
    {

        if ($this->transLevel == 0 || ! $this->nestable())
        {
            $this->sql(['START TRANSACTION']);
        } else
        {
            $this->sql(["SAVEPOINT LEVEL{$this->transLevel}"]);
        }

        $this->transLevel++;
        return $this;
    }

    /**
     * @return $this
     */
    public function commit()
    {

        $this->transLevel--;

        if ($this->transLevel == 0 || ! $this->nestable())
        {
            $this->sql(['COMMIT']);
        } else
        {
            $this->sql(["RELEASE SAVEPOINT LEVEL{$this->transLevel}"]);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function rollback()
    {

        $this->transLevel--;

        if ($this->transLevel == 0 || ! $this->nestable())
        {
            $this->sql(['ROLLBACK']);
        } else
        {
            $this->sql(["ROLLBACK TO SAVEPOINT LEVEL{$this->transLevel}"]);
        }

        return $this;
    }


    /**
     * Builds an update query.
     *
     * @param string|array $data Array of keys and values, or string literal
     * @return self reference
     */
    public function update($data)
    {
        $this->checkTable();

        if (empty($data)) return $this;

        $values = [];

        if (is_array($data))
        {
            foreach ($data as $key => $value)
            {
                $values[] = (is_numeric($key)) ? $value : $key . '=' . $this->quote($value);
            }
        } else
        {
            $values[] = (string)$data;
        }

        $this->sql([
            'UPDATE',
            $this->table,
            'SET',
            implode(',', $values),
            $this->where
        ]);

        return $this;
    }

    /**
     * Builds a delete query.
     *
     * @param array $where Where conditions
     * @return self reference
     */
    public function delete($where = NULL)
    {
        $this->checkTable();

        if ($where !== NULL)
        {
            $this->where($where);
        }

        $this->sql([
            'DELETE FROM',
            $this->table,
            $this->where
        ]);

        return $this;
    }

    /**
     * Gets or sets the SQL statement.
     *
     * @param string|array SQL statement
     * @return self | string SQL statement
     */
    public function sql($sql = NULL)
    {
        if ($sql !== NULL)
        {
            $this->sql = trim(
                (is_array($sql)) ?
                    array_reduce($sql, [$this, 'build']) :
                    $sql
            );

            return $this;
        }

        return $this->sql;
    }

    /**
     * @param string $transaction_characteristic
     *
     * @return self
     */
    public function set_transaction_repeatable_read($transaction_characteristic = 'SESSION')
    {
        return $this->_set_transaction_level($transaction_characteristic, 'REPEATABLE READ');
    }

    /**
     * @param string $transaction_characteristic
     *
     * @return self
     */
    public function set_transaction_read_committed($transaction_characteristic = 'SESSION')
    {
        return $this->_set_transaction_level($transaction_characteristic, 'READ COMMITTED');
    }

    /**
     * @param string $transaction_characteristic
     *
     * @return self
     */
    public function set_transaction_read_uncommitted($transaction_characteristic = 'SESSION')
    {
        return $this->_set_transaction_level($transaction_characteristic, 'READ UNCOMMITTED');
    }

    /**
     * @param string $transaction_characteristic
     * @return self
     */
    public function set_transaction_serializable($transaction_characteristic = 'SESSION')
    {
        return $this->_set_transaction_level($transaction_characteristic, 'SERIALIZABLE');
    }

    /**
     * @param $transaction_characteristic
     * @param $level
     *
     * @return self
     */
    private function _set_transaction_level($transaction_characteristic, $level)
    {
        return $this->sql('SET ' . $transaction_characteristic . ' TRANSACTION ISOLATION LEVEL ' . $level);
    }

    /*** Database Access Methods ***/

    /**
     * Sets the database connection.
     *
     * @param string|array|object $db Database connection string, array or object
     * @throws Exception For connection error
     */
    public function setDb($db)
    {
        $this->db = NULL;

        // Connection string
        if (is_string($db))
        {
            $this->setDb($this->parseConnection($db));
        } // Connection information
        else if (is_array($db))
        {
            switch ($db['type'])
            {
                case 'mysqli':
                    $this->db = new mysqli(
                        $db['hostname'],
                        $db['username'],
                        $db['password'],
                        $db['database'],
                        $db['port'],
                    );

                    if ($this->db->connect_error)
                    {
                        throw new Exception('Connection error: ' . $this->db->connect_error);
                    }

                    break;

                case 'pgsql':
                    $str = sprintf(
                        'host=%s dbname=%s user=%s password=%s',
                        $db['hostname'],
                        $db['database'],
                        $db['username'],
                        $db['password']
                    );

                    $this->db = pg_connect($str);

                    break;

                case 'sqlite':
                    $this->db = sqlite_open($db['database'], 0666, $error);

                    if ( ! $this->db)
                    {
                        throw new Exception('Connection error: ' . $error);
                    }

                    break;

                case 'sqlite3':
                    $this->db = new SQLite3($db['database']);

                    break;

                case 'pdomysql':
                    $dsn = sprintf(
                        'mysql:host=%s;port=%d;dbname=%s',
                        $db['hostname'],
                        isset($db['port']) ? $db['port'] : 3306,
                        $db['database']
                    );

                    $this->db = new PDO($dsn, $db['username'], $db['password']);
                    $db['type'] = 'pdo';

                    break;

                case 'pdopgsql':
                    $dsn = sprintf(
                        'pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s',
                        $db['hostname'],
                        isset($db['port']) ? $db['port'] : 5432,
                        $db['database'],
                        $db['username'],
                        $db['password']
                    );

                    $this->db = new PDO($dsn);
                    $db['type'] = 'pdo';

                    break;

                case 'pdosqlite':
                    $this->db = new PDO('sqlite:/' . $db['database']);
                    $db['type'] = 'pdo';

                    break;
            }

            if ($this->db == NULL)
            {
                throw new Exception('Undefined database.');
            }

            $this->db_type = $db['type'];
        } // Connection object or resource
        else
        {
            $type = $this->getDbType($db);

            if ( ! in_array($type, self::$db_types))
            {
                throw new Exception('Invalid database type.');
            }

            $this->db = $db;
            $this->db_type = $type;
        }
    }

    /**
     * Gets the database connection.
     *
     * @return mysqli|object Database connection
     */
    public function getDb()
    {
        return $this->db;
    }

    public function ping()
    {
        switch ($this->db_type)
        {
            case 'mysqli':
                return @$this->db->ping();
//            case 'pdo':
//            case 'pgsql':
//            case 'sqlite':
//            case 'sqlite3':
            default:
                try
                {
                    $this->sql('SELECT 1')->many();
                } catch (PDOException $e)
                {
                    return FALSE;
                }
                return TRUE;
        }
    }

    /**
     * Gets the sql query.
     *
     * @return string sql query
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * Gets the database type.
     *
     * @param object|resource $db Database object or resource
     * @return string Database type
     */
    public function getDbType($db)
    {
        if (is_object($db))
        {
            return strtolower(get_class($db));
        } else if (is_resource($db))
        {
            switch (get_resource_type($db))
            {
                case 'mysql link':
                    return 'mysql';

                case 'sqlite database':
                    return 'sqlite';

                case 'pgsql link':
                    return 'pgsql';
            }
        }

        return NULL;
    }

    /**
     * Executes a sql statement.
     *
     * @param string $key Cache key
     * @param int $expire Expiration time in seconds
     * @return object Query results object
     * @throws Exception When database is not defined
     */
    public function execute($key = NULL, $expire = 0)
    {
        if ( ! $this->db)
        {
            throw new Exception('Database is not defined.');
        }

        if ($key !== NULL)
        {
            $result = $this->fetch($key);

            if ($this->is_cached)
            {
                return $result;
            }
        }

        if ( ! empty($this->unionSql))
        {
            $this->sql = $this->unionSql;
            $this->unionSql = '';
        }

        $result = NULL;

        $this->is_cached = FALSE;
        $this->num_rows = 0;
        $this->affected_rows = 0;
        $this->affected = FALSE;
        $this->insert_id = -1;
        $this->last_query = $this->sql;


        if ($this->stats_enabled)
        {
            if (empty($this->stats))
            {
                $this->stats = [
                    'queries' => []
                ];
            }

            $this->query_time = microtime(TRUE);
        }

        if ( ! empty($this->sql))
        {
            $error = NULL;

            switch ($this->db_type)
            {
                case 'pdo':
                    try
                    {
                        $result = $this->db->prepare($this->sql);

                        if ( ! $result)
                        {
                            $error = $this->db->errorInfo();
                        } else
                        {
                            $result->execute();

                            $this->num_rows = $result->rowCount();
                            $this->affected_rows = $result->rowCount();
                            $this->affected = $this->affected_rows > 0;
                            $this->insert_id = $this->db->lastInsertId();
                        }
                    } catch (PDOException $ex)
                    {
                        $error = $ex->getMessage();
                    }

                    break;

                case 'mysqli':
                    $result = $this->db->query($this->sql);

                    if ( ! $result)
                    {
                        $error = $this->db->error;
                    } else
                    {
                        if (is_object($result))
                        {
                            $this->num_rows = $result->num_rows;
                        } else
                        {
                            $this->affected_rows = $this->db->affected_rows;
                            $this->affected = $this->affected_rows > 0;
                        }
                        $this->insert_id = $this->db->insert_id;
                    }

                    break;

                case 'pgsql':
                    $result = pg_query($this->db, $this->sql);

                    if ( ! $result)
                    {
                        $error = pg_last_error($this->db);
                    } else
                    {
                        $this->num_rows = pg_num_rows($result);
                        $this->affected_rows = pg_affected_rows($result);
                        $this->affected = $this->affected_rows > 0;
                        $this->insert_id = pg_last_oid($result);
                    }

                    break;

                case 'sqlite':
                    $result = sqlite_query($this->db, $this->sql, SQLITE_ASSOC, $error);

                    if ($result !== FALSE)
                    {
                        $this->num_rows = sqlite_num_rows($result);
                        $this->affected_rows = sqlite_changes($this->db);
                        $this->affected = $this->affected_rows > 0;
                        $this->insert_id = sqlite_last_insert_rowid($this->db);
                    }

                    break;

                case 'sqlite3':
                    $result = $this->db->query($this->sql);

                    if ($result === FALSE)
                    {
                        $error = $this->db->lastErrorMsg();
                    } else
                    {
                        $this->num_rows = 0;
                        $this->affected_rows = ($result) ? $this->db->changes() : 0;
                        $this->affected = $this->affected_rows > 0;
                        $this->insert_id = $this->db->lastInsertRowId();
                    }

                    break;
            }

            if ($error !== NULL)
            {
                if ($this->db->errno)
                {
                    $error .= ' errno: ' . $this->db->errno . ' ';
                }

                if ($this->show_sql)
                {
                    $error .= "\nSQL: " . $this->sql;
                }

                throw new Exception('Database error: ' . $error);
            }
        }

        if ($this->stats_enabled)
        {
            $time = microtime(TRUE) - $this->query_time;
            $this->stats['queries'][] = [
                'query' => $this->sql,
                'time' => $time,
                'rows' => (int)$this->num_rows,
                'changes' => (int)$this->affected_rows
            ];
        }

        return $result;
    }

    public function key_pair($index = NULL, $value = NULL)
    {
        $result = $this->many();
        if (empty($result))
        {
            return [];
        }
        if ($index === NULL && $value === NULL)
        {
            $first = current($result);
            $index = key($first);
            next($first);
            $value = key($first);

            if ($value === NULL)
            {
                return [];
            }
        }

        $result = array_column($result, $value, $index);

        return $result;
    }

    public function column($index = NULL)
    {
        $result = $this->many();
        if (empty($result))
        {
            return [];
        }
        if ($index === NULL)
        {
            $index = key(current($result));
        }
        $result = array_column($result, $index);
        return $result;
    }

    public function unique_key($index = NULL)
    {
        $result = $this->many();
        if (empty($result))
        {
            return [];
        }
        if ($index === NULL)
        {
            $first = current($result);
            $index = key($first);
        }

        $result = array_column($result, NULL, $index);

        return $result;
    }

    /**
     * Fetch multiple rows from a select query.
     *
     * @param string $key Cache key
     * @param int $expire Expiration time in seconds
     * @return array Rows
     */
    public function many($key = NULL, $expire = 0)
    {
        if (empty($this->sql))
        {
            $this->select();
        }

        $data = [];

        $result = $this->execute($key, $expire);

        if ($this->is_cached)
        {
            $data = $result;

            if ($this->stats_enabled)
            {
                $this->stats['cached'][$this->key_prefix . $key] = $this->sql;
            }
        } else
        {
            switch ($this->db_type)
            {
                case 'pdo':
                    $data = $result->fetchAll(PDO::FETCH_ASSOC);
                    $this->num_rows = sizeof($data);

                    break;

                case 'mysqli':
                    if (function_exists('mysqli_fetch_all'))
                    {
                        $data = $result->fetch_all(MYSQLI_ASSOC);
                    } else
                    {
                        while ($row = $result->fetch_assoc())
                        {
                            $data[] = $row;
                        }
                    }
                    $result->close();
                    break;

                case 'pgsql':
                    $data = pg_fetch_all($result);
                    pg_free_result($result);
                    break;

                case 'sqlite':
                    $data = sqlite_fetch_all($result, SQLITE_ASSOC);
                    break;

                case 'sqlite3':
                    if ($result)
                    {
                        while ($row = $result->fetchArray(SQLITE3_ASSOC))
                        {
                            $data[] = $row;
                        }
                        $result->finalize();
                        $this->num_rows = sizeof($data);
                    }
                    break;
            }
        }

        if ( ! $this->is_cached && $key !== NULL)
        {
            $this->store($key, $data, $expire);
        }

        return $data;
    }

    /**
     * Fetch a single row from a select query.
     *
     * @param string $key Cache key
     * @param int $expire Expiration time in seconds
     * @return array Row
     */
    public function one($key = NULL, $expire = 0)
    {
        if (empty($this->sql))
        {
            $this->limit(1)->select();
        }

        $data = $this->many($key, $expire);

        $row = ( ! empty($data)) ? $data[0] : [];

        return $row;
    }

    /**
     * Fetch a value from a field.
     *
     * @param string $name Database field name
     * @param string $key Cache key
     * @param int $expire Expiration time in seconds
     * @return mixed Row value
     */
    public function value($name, $key = NULL, $expire = 0)
    {
        $row = $this->one($key, $expire);

        $value = ( ! empty($row)) ? $row[$name] : NULL;

        return $value;
    }

    /**
     * Gets the min value for a specified field.
     *
     * @param string $field Field name
     * @param int $expire Expiration time in seconds
     * @param string $key Cache key
     * @return self reference
     */
    public function min($field, $key = NULL, $expire = 0)
    {
        $this->select('MIN(' . $field . ') min_value');

        return $this->value(
            'min_value',
            $key,
            $expire
        );
    }

    /**
     * Gets the max value for a specified field.
     *
     * @param string $field Field name
     * @param int $expire Expiration time in seconds
     * @param string $key Cache key
     * @return self reference
     */
    public function max($field, $key = NULL, $expire = 0)
    {
        $this->select('MAX(' . $field . ') max_value');

        return $this->value(
            'max_value',
            $key,
            $expire
        );
    }

    /**
     * Gets the sum value for a specified field.
     *
     * @param string $field Field name
     * @param int $expire Expiration time in seconds
     * @param string $key Cache key
     * @return mixed Row value
     */
    public function sum($field, $key = NULL, $expire = 0)
    {
        $this->select('SUM(' . $field . ') sum_value');

        return $this->value(
            'sum_value',
            $key,
            $expire
        );
    }

    /**
     * Gets the average value for a specified field.
     *
     * @param string $field Field name
     * @param int $expire Expiration time in seconds
     * @param string $key Cache key
     * @return self reference
     */
    public function avg($field, $key = NULL, $expire = 0)
    {
        $this->select('AVG(' . $field . ') avg_value');

        return $this->value(
            'avg_value',
            $key,
            $expire
        );
    }

    /**
     * Gets a count of records for a table.
     *
     * @param string $field Field name
     * @param string $key Cache key
     * @param int $expire Expiration time in seconds
     * @return int
     */
    public function count($field = '*', $key = NULL, $expire = 0)
    {
        $this->select('COUNT(' . $field . ') num_rows');

        return $this->value(
            'num_rows',
            $key,
            $expire
        );
    }

    /**
     * Wraps quotes around a string and escapes the content for a string parameter.
     *
     * @param mixed $value mixed value
     * @return mixed Quoted value
     */
    public function quote($value)
    {
        if ($value === NULL) return 'NULL';

        if (is_string($value))
        {
            if ($this->db !== NULL)
            {
                switch ($this->db_type)
                {
                    case 'pdo':
                        return $this->db->quote($value);

                    case 'mysqli':
                        return "'" . $this->db->real_escape_string($value) . "'";

                    case 'pgsql':
                        return "'" . pg_escape_string($this->db, $value) . "'";

                    case 'sqlite':
                        return "'" . sqlite_escape_string($value) . "'";

                    case 'sqlite3':
                        return "'" . $this->db->escapeString($value) . "'";
                }
            }

            $value = str_replace(
                ['\\', "\0", "\n", "\r", "'", '"', "\x1a"],
                ['\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'],
                $value
            );

            return "'$value'";
        }

        return $value;
    }

    /**
     * Onle escapes the content for a string parameter (NO WRAP).
     *
     * @param mixed $value mixed value
     * @return mixed Quoted value
     * @deprecated Если по какой-то причине нужно использовать эту функцию, то лучше переделать.
     */
    public function escape($value)
    {
        if ($value === NULL) return 'NULL';

        if (is_string($value))
        {
            if ($this->db !== NULL)
            {
                switch ($this->db_type)
                {
                    // в pdo нужного метода, который не добавлял бы кавычки - не нашёл :'(
//                    case 'pdo':
//                        return $this->db->quote($value);
                    case 'mysqli':
                        return $this->db->real_escape_string($value);

                    case 'pgsql':
                        return pg_escape_string($this->db, $value);

                    case 'sqlite':
                        return sqlite_escape_string($value);

                    case 'sqlite3':
                        return $this->db->escapeString($value);
                }
            }

            $value = str_replace(
                ['\\', "\0", "\n", "\r", "'", '"', "\x1a"],
                ['\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'],
                $value
            );

            return "'$value'";
        }

        return $value;
    }

    /*** Cache Methods ***/

    /**
     * Sets the cache connection.
     *
     * @param string|object $cache Cache connection string or object
     * @throws Exception For invalid cache type
     */
    public function setCache($cache)
    {
        $this->cache = NULL;

        // Connection string
        if (is_string($cache))
        {
            if ($cache[0] == '.' || $cache[0] == '/')
            {
                $this->cache = $cache;
                $this->cache_type = 'file';
            } else
            {
                $this->setCache($this->parseConnection($cache));
            }
        } // Connection information
        else if (is_array($cache))
        {
            switch ($cache['type'])
            {
                case 'memcache':
                    $this->cache = new Memcache;
                    $this->cache->connect(
                        $cache['hostname'],
                        $cache['port']
                    );
                    break;

                case 'memcached':
                    $this->cache = new Memcached;
                    $this->cache->addServer(
                        $cache['hostname'],
                        $cache['port']
                    );
                    break;

                default:
                    $this->cache = $cache['type'];
            }

            $this->cache_type = $cache['type'];
        } // Cache object
        else if (is_object($cache))
        {
            $type = strtolower(get_class($cache));

            if ( ! in_array($type, self::$cache_types))
            {
                throw new Exception('Invalid cache type.');
            }

            $this->cache = $cache;
            $this->cache_type = $type;
        }
    }

    /**
     * Gets the cache instance.
     *
     * @return object Cache instance
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Stores a value in the cache.
     *
     * @param string $key Cache key
     * @param mixed $value Value to store
     * @param int $expire Expiration time in seconds
     */
    public function store($key, $value, $expire = 0)
    {
        $key = $this->key_prefix . $key;

        switch ($this->cache_type)
        {
            case 'memcached':
                $this->cache->set($key, $value, $expire);
                break;

            case 'memcache':
                $this->cache->set($key, $value, 0, $expire);
                break;

            case 'apc':
                apc_store($key, $value, $expire);
                break;

            case 'xcache':
                xcache_set($key, $value, $expire);
                break;

            case 'file':
                $file = $this->cache . '/' . md5($key);
                $data = [
                    'value' => $value,
                    'expire' => ($expire > 0) ? (time() + $expire) : 0
                ];
                file_put_contents($file, serialize($data));
                break;

            default:
                $this->cache[$key] = $value;
        }
    }

    /**
     * Fetches a value from the cache.
     *
     * @param string $key Cache key
     * @return mixed Cached value
     */
    public function fetch($key)
    {
        $key = $this->key_prefix . $key;

        switch ($this->cache_type)
        {
            case 'memcached':
                $value = $this->cache->get($key);
                $this->is_cached = ($this->cache->getResultCode() == Memcached::RES_SUCCESS);
                return $value;

            case 'memcache':
                $value = $this->cache->get($key);
                $this->is_cached = ($value !== FALSE);
                return $value;

            case 'apc':
                return apc_fetch($key, $this->is_cached);

            case 'xcache':
                $this->is_cached = xcache_isset($key);
                return xcache_get($key);

            case 'file':
                $file = $this->cache . '/' . md5($key);

                if ($this->is_cached = file_exists($file))
                {
                    $data = unserialize(file_get_contents($file));
                    if ($data['expire'] == 0 || time() < $data['expire'])
                    {
                        return $data['value'];
                    } else
                    {
                        $this->is_cached = FALSE;
                    }
                }
                break;

            default:
                return $this->cache[$key];
        }
        return NULL;
    }

    /**
     * Clear a value from the cache.
     *
     * @param string $key Cache key
     * @return self reference
     */
    public function clear($key)
    {
        $key = $this->key_prefix . $key;

        switch ($this->cache_type)
        {
            case 'memcached':
                return $this->cache->delete($key);

            case 'memcache':
                return $this->cache->delete($key);

            case 'apc':
                return apc_delete($key);

            case 'xcache':
                return xcache_unset($key);

            case 'file':
                $file = $this->cache . '/' . md5($key);
                if (file_exists($file))
                {
                    return unlink($file);
                }
                return FALSE;

            default:
                if (isset($this->cache[$key]))
                {
                    unset($this->cache[$key]);
                    return TRUE;
                }
                return FALSE;
        }
    }

    /**
     * Flushes out the cache.
     */
    public function flush()
    {
        switch ($this->cache_type)
        {
            case 'memcached':
                $this->cache->flush();
                break;

            case 'memcache':
                $this->cache->flush();
                break;

            case 'apc':
                apc_clear_cache();
                break;

            case 'xcache':
                xcache_clear_cache();
                break;

            case 'file':
                if ($handle = opendir($this->cache))
                {
                    while (FALSE !== ($file = readdir($handle)))
                    {
                        if ($file != '.' && $file != '..')
                        {
                            unlink($this->cache . '/' . $file);
                        }
                    }
                    closedir($handle);
                }
                break;

            default:
                $this->cache = [];
                break;
        }
    }

    /*** Object Methods ***/

    /**
     * Sets the class.
     *
     * @param string|object $class Class name or instance
     * @return self reference
     */
    public function using($class)
    {
        if (is_string($class))
        {
            $this->class = $class;
        } else if (is_object($class))
        {
            $this->class = get_class($class);
        }

        $this->reset();

        return $this;
    }

    /**
     * Loads properties for an object.
     *
     * @param object $object Class instance
     * @param array $data Property data
     * @return object Populated object
     */
    public function load($object, array $data)
    {
        foreach ($data as $key => $value)
        {
            if (property_exists($object, $key))
            {
                $object->$key = $value;
            }
        }

        return $object;
    }

    /**
     * Finds and populates an object.
     *
     * @param int|string|array Search value
     * @param string $key Cache key
     * @return object Populated object
     */
    public function find($value = NULL, $key = NULL)
    {
        $this->checkClass();

        $properties = $this->getProperties();

        $this->from($properties->table, FALSE);

        if ($value !== NULL)
        {
            if (is_int($value) && property_exists($properties, 'id_field'))
            {
                $this->where($properties->id_field, $value);
            } else if (is_string($value) && property_exists($properties, 'name_field'))
            {
                $this->where($properties->name_field, $value);
            } else if (is_array($value))
            {
                $this->where($value);
            }
        }

        if (empty($this->sql))
        {
            $this->select();
        }

        $data = $this->many($key);
        $objects = [];

        foreach ($data as $row)
        {
            $objects[] = $this->load(new $this->class, $row);
        }

        return (sizeof($objects) == 1) ? $objects[0] : $objects;
    }

    /**
     * Saves an object to the database.
     *
     * @param object $object Class instance
     * @param array $fields Select database fields to save
     */
    public function save($object, array $fields = NULL)
    {
        $this->using($object);

        $properties = $this->getProperties();

        $this->from($properties->table);

        $data = get_object_vars($object);
        $id = $object->{$properties->id_field};

        unset($data[$properties->id_field]);

        if ($id === NULL)
        {
            $this->insert($data)
                ->execute();

            $object->{$properties->id_field} = $this->insert_id;
        } else
        {
            if ($fields !== NULL)
            {
                $keys = array_flip($fields);
                $data = array_intersect_key($data, $keys);
            }

            $this->where($properties->id_field, $id)
                ->update($data)
                ->execute();
        }

        return $this->class;
    }

    /**
     * Removes an object from the database.
     *
     * @param object $object Class instance
     */
    public function remove($object)
    {
        $this->using($object);

        $properties = $this->getProperties();

        $this->from($properties->table);

        $id = $object->{$properties->id_field};

        if ($id !== NULL)
        {
            $this->where($properties->id_field, $id)
                ->delete()
                ->execute();
        }
    }

    /**
     * Gets class properties.
     *
     * @return object Class properties
     */
    public function getProperties()
    {
        static $properties = [];

        if ( ! $this->class) return [];

        if ( ! isset($properties[$this->class]))
        {
            static $defaults = [
                'table' => NULL,
                'id_field' => NULL,
                'name_field' => NULL
            ];

            $reflection = new ReflectionClass($this->class);
            $config = $reflection->getStaticProperties();

            $properties[$this->class] = (object)array_merge($defaults, $config);
        }

        return $properties[$this->class];
    }
}

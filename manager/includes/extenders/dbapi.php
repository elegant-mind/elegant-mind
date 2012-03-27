<?php
/**
 * Extend Zend Db class to use it in MODX. It also emulates the methods of the
 * original DBAPI class in dbapi.mysql.class.inc.php.
 *
 * @author  Stefanie Janine Stoelting, mail@stefanie-stoelting.de
 * @since   2012/03/26
 * @version 1.1.alpha.1
 */
class DBAPI {

    /**
     * Database type MySQL MyISAM
     */
    const DB_MYSQL_MYISAM = 'MySQL:MyISAM';

    /**
     * Database type MySQL InnoDB
     */
    const DB_MYSQL_INNODB = 'MySQL:InnoDB';

    /**
     * Database type PostgreSQL
     */
    const DB_POSTGRESQL = 'PostgreSQL';
    
    /**
     * Default PostgreSQL TCP/IP port is 5432 
     */
    const POSTGRESQL_DEFAULT_PORT = '5432';

    /**
     * The name of the current database engine.
     * @var string Default: emptystr
     */
    private $currentDBEngine = '';

    /**
     * Whether a connection is established, or not.
     * @var boolean Default: false
     */
    private $connected = false;
    
    /**
     * The database object.
     * @var object
     */
    public $conn = null;
    
    /**
     *The configuration array.
     * @var array
     */
    private $config;

    /**
     * Database constructor
     *
     * @param array $config The array with all database configuration parameters.
     * @return boolean
     */
    public function __construct($config)
    {
        $result = false;
        
        $this->config = $config;
        
        include $this->config['basePath'] . 'manager/lib/ezSQL/shared/ez_sql_core.php';

        switch ($this->config['db_type']) {
            case self::DB_MYSQL_MYISAM:
                $this->currentDBEngine = self::DB_MYSQL_MYISAM;
                include $basePath . 'manager/lib/ezSQL/mysql/ez_sql_mysql.php';
                
                $result = true;

                break;

            case self::DB_MYSQL_INNODB:
                $this->currentDBEngine = self::DB_MYSQL_INNODB;
                include $basePath . 'manager/lib/ezSQL/mysql/ez_sql_mysql.php';
                $result = true;

                break;

            case self::DB_POSTGRESQL:
                $this->currentDBEngine = self::DB_POSTGRESQL;
                include $basePath . 'manager/lib/ezSQL/postgresql/ez_sql_postgresql.php';
                $result = true;

                break;

            default:
                $this->currentDBEngine = '';

                throw new ErrorException($db_type . ' is not a valid database connection name.');

                break;
        }

        return $result;
    } // __construct

    /**
     * Getter for the current database.
     *
     * @return string The name of the current database engine.
     */
    public function getCurrentDatabaseEngine()
    {
        $result = '';

        if (empty($this->currentDBEngine)) {
            throw new ErrorException('No database connection initialized.');
        } else {
            $result = $this->currentDBEngine;
        }

        return $result;
    } // getCurrentDatabaseEngine

    /**
     * Whether a database connection is established, or not.
     *
     * @return boolean
     */
    public function getConnected()
    {
        return $this->connected;
    }

    /**
     * Establish a connection to of the database type.
     *
     * @param type $host The host address of the database server.
     * @param type $dbase The name of the database.
     * @param type $uid The database user name.
     * @param type $pwd The database password.
     * @param type $persist Only for compatibility.
     * @param type $charset MySQL connection charset.
     * @param type $port PostgreSQL TCP/IP port, if empty and the current engine
     *                   is PostgreSQL, then the default port is used.
     * @throws ErrorException 
     */
    public function connect($host = '', $dbase = '', $uid = '', $pwd = '', $persist = 0, $charset='', $port = '')
    {
        
        if (empty($this->currentDBEngine)) {
            throw new ErrorException('No database connection initialized.');
        } else {
            // Set configuration
            $this->config['user'] = $uid;
            $this->config['pass'] = $pwd;
            $this->config['host'] = $host;
            $this->config['dbase'] = $dbase;
            $this->config['charset'] = $charset;
            $this->config['port'] = $port;

            switch ($this->currentDBEngine) {
                case self::DB_MYSQL_MYISAM:
                case self::DB_MYSQL_INNODB:
                    $this->conn = new ezSQL_mysql($uid, $pwd, $dbase, $host, $charset);
                    
                    break;

                case self::DB_POSTGRESQL:
                    if (empty($port)) {
                        $this->config['port'] = self::POSTGRESQL_DEFAULT_PORT;
                    }
                    $this->conn = new ezSQL_postgresql($uid, $pwd, $dbase, $host, $this->config['port']);

                    break;

                default:
                    throw new ErrorException('No valid database engine configured.');
                    
                    break;
            }

            $this->connected = true;
        }
    } // connect

    /**
     * Disconnect the current database connection
     */
    public function disconnect()
    {
        $this->conn->disconnect();
    } // disconnect

    /**
     * Escapes a string and uses the current database engines escape code.
     *
     * @param string $instr The string that has to be escaped
     * @return string The escaped string
     */
    public function escape($instr)
    {
        return $this->conn->escape($instr);
    } // escape

    /**
     * Executes an SQL statement.
     * 
     * @param string $sql The SQL statement to execute.
     * @return object The result of the executed SQL statement.
     */
    public function query($sql)
    {
        global $modx;

        $tstart = $modx->getMicroTime();

        if (!$this->connected) {
            $this->connect();
        }
        // Run the query and set the result
        $result = $this->conn->get_results($sql);

        $tend = $modx->getMicroTime();
        $totaltime = $tend - $tstart;
        $modx->queryTime = $modx->queryTime + $totaltime;

        if ($modx->dumpSQL) {
            $modx->queryCode .= "<fieldset style='text-align:left'><legend>Query " . ($this->executedQueries + 1) . " - " . sprintf("%2.4f s", $totaltime) . "</legend>" . $sql . "</fieldset><br />";
        }

        $modx->executedQueries = $modx->executedQueries + 1;

        return $result;
    } // query
    
    /**
     * Returns the configuration array.
     * 
     * @return array The configuration array.
     */
    public function getConfiguration()
    {
        return $this->config;
    } // getConfiguration
    
    /**
     * Execute a DELETE statement.
     * 
     * @param string $from The name of the table.
     * @param string $where The WHERE cl
     * @param type $fields
     * @return type 
     */
    public function delete($from, $where='', $fields='')
    {
        if (!$from)
            $result = false;
        else {
            $where = ($where != '') ? "WHERE $where" : '';
            
            switch ($this->currentDBEngine) {
                case self::DB_MYSQL_MYISAM:
                case self::DB_MYSQL_MYISAM:
                    $table = $from;
                    $result = $this->query("DELETE $fields FROM $table $where");

                    break;
                
                case self::DB_POSTGRESQL:
                    $result = $this->query("DELETE FROM $table $where");

                default:
                    break;
            }
        }

        return $result;
    } // delete

    function getInsertId($conn=NULL)
    {
        $result = 0;
        
        if (!is_resource($conn)) {
            $conn =& $this->conn;
        }
        
        switch ($this->currentDBEngine) {
            case self::DB_MYSQL_MYISAM:
            case self::DB_MYSQL_INNODB:
                $result = mysql_insert_id($conn);

                break;
            
            case self::DB_POSTGRESQL:
                // Currently there is no function for getting the last inserted id
                $result = 0;
                
                break;

            default:
                break;
        }
        
        return $result;
    }
    
    /**
     * Get the affected number of rows
     * 
     * @param object $conn The connection, to check, by default the current
     *                     connection is used.
     * @return integer The number of affected rows.
     */ 
    public function getAffectedRows($conn=NULL)
    {
        $result = 0;
        
        if (!is_resource($conn)) {
            $conn =& $this->conn;
        }
        
        switch ($this->currentDBEngine) {
            case self::DB_MYSQL_MYISAM:
            case self::DB_MYSQL_INNODB:
                $result = mysql_affected_rows($conn);

                break;
            
            case self::DB_POSTGRESQL:
                $result = pg_affected_rows($conn);
                
                break;

            default:
                break;
        }
        
        return $result;
    } // getAffectedRows

    /**
     * Get the last database error.
     * 
     * @param object $conn The connection, to check, by default the current
     *                     connection is used.
     * @return string The database error message.
     */
    public function getLastError($conn=NULL) 
    {
        $result = '';
        
        if (!is_resource($conn)) {
            $conn =& $this->conn;
        }
        
        switch ($this->currentDBEngine) {
            case self::DB_MYSQL_MYISAM:
            case self::DB_MYSQL_INNODB:
                $result = mysql_error($conn);

                break;
            
            case self::DB_POSTGRESQL:
                $result = pg_errormessage($conn);
                
                break;

            default:
                break;
        }
        
        return $result;
    } // getLastError

    /**
     * Get information about the current version of the used database.
     * 
     * @return string The version information.
     */
    public function getVersion() {
        $result = '';
        switch ($this->currentDBEngine) {
            case self::DB_MYSQL_MYISAM:
            case self::DB_MYSQL_INNODB:
                $result = mysql_get_server_info();

                break;
            
            case self::DB_POSTGRESQL:
                $result = pg_version($this->conn);
                $result = $result['server'];
                
                break;

            default:
                break;
        }
        
        return $result;
    } // getVersion

} // DBAPI
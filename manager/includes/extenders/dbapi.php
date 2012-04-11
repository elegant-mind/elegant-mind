<?php
/**
 * Implements ezSQL to use it in MODX. It also emulates the methods of the
 * original DBAPI class in dbapi.mysql.class.inc.php.
 *
 * @author  Stefanie Janine Stoelting (mail@stefanie-stoelting.de)
 * @name    DBAPI
 * @uses    ezSQL
 * @link    http://www.modx.com
 * @package MODX
 */
class DBAPI
{

    /**
     * Database type MySQL MyISAM.
     */
    const DB_MYSQL_MYISAM = 'MySQL:MyISAM';

    /**
     * Database type MySQL InnoDB.
     */
    const DB_MYSQL_INNODB = 'MySQL:InnoDB';

    /**
     * Database type PostgreSQL.
     */
    const DB_POSTGRESQL = 'PostgreSQL';

    /**
     * Database type SQLite.
     */
    const DB_SQLITE = 'SQLite';

    /**
     * Default PostgreSQL TCP/IP port is 5432.
     */
    const POSTGRESQL_DEFAULT_PORT = '5432';

    /**
     * Default path to the ezSQL class files.
     */
    const EZSQL_PATH = 'manager/includes/lib/ezSQL/';

    /**
     * The instance of the DBAPI class
     * @var object Default: null
     */
    private static $instance = null;

    /**
     * The name of the current database engine.
     * @var string Default: emptystr
     */
    private $_currentDBEngine = '';

    /**
     * Whether a connection is established, or not.
     * @var boolean Default: false
     */
    private $_connected = false;

    /**
     * The number of the current record, is set in queries
     * @var int
     */
    private $_curRecord = 0;

    /**
     * The database object.
     * @var object
     */
    public $conn = null;

    /**
     *The configuration array.
     * @var array
     */
    public $config = array();

    /**
     * Empty and private constructor for the singleton
     */
    private function __construct() {}

    /**
     * Disallow clone from outsite the DBAPI class
     */
    private function __clone() {}

    /**
     * No deserializing with singltons
     */
    private function __wakeup() {}

    /**
     * Database constructor for singleton
     *
     * @param array $config The array with all database configuration parameters.
     * @return DBAPI
     * @todo Variable database engine from configuration
     */
    public static function getInstance($config) {
        if (NULL === self::$instance) {
            self::$instance = new self;

            self::$instance->config = $config;

            include self::$instance->config['basePath'] . self::EZSQL_PATH . '/shared/ez_sql_core.php';

            switch (self::$instance->config['db_type']) {
                case self::DB_MYSQL_MYISAM:
                    self::$instance->_currentDBEngine = self::DB_MYSQL_MYISAM;
                    include self::$instance->config['basePath'] . self::EZSQL_PATH . 'mysql/ez_sql_mysql.php';

                    break;

                case self::DB_MYSQL_INNODB:
                    self::$instance->_currentDBEngine = self::DB_MYSQL_INNODB;
                    include self::$instance->config['basePath'] . self::EZSQL_PATH . 'mysql/ez_sql_mysql.php';

                    break;

                case self::DB_POSTGRESQL:
                    self::$instance->_currentDBEngine = self::DB_POSTGRESQL;
                    include self::$instance->config['basePath'] . self::EZSQL_PATH . 'postgresql/ez_sql_postgresql.php';
                    if (array_key_exists('port', self::$instance->config)) {
                        if (empty(self::$instance->config['port'])) {
                            self::$instance->config['port'] = self::POSTGRESQL_DEFAULT_PORT;
                        }
                    } else {
                        self::$instance->config['port'] = self::POSTGRESQL_DEFAULT_PORT;
                    }

                    break;

                case self::DB_SQLITE:
                    self::$instance->_currentDBEngine = self::DB_SQLITE;
                    include self::$instance->config['basePath'] . self::EZSQL_PATH . 'pdo/ez_sql_pdo.php';

                    break;

                default:
                    self::$instance->_currentDBEngine = '';

                    throw new ErrorException(self::$instance->config['db_type'] . ' is not a valid database connection name.');

                    break;
            }

        }

        return self::$instance;
    } // getInstance

    /**
     * Getter for the current database.
     *
     * @return string The name of the current database engine.
     */
    public function getCurrentDatabaseEngine() {
        $result = '';

        if (empty($this->_currentDBEngine)) {
            throw new ErrorException('No database connection initialized.');
        } else {
            $result = $this->_currentDBEngine;
        }

        return $result;
    } // getCurrentDatabaseEngine

    /**
     * Whether a database connection is established, or not.
     *
     * @return boolean
     */
    public function getConnected() {
        return $this->_connected;
    } // getConnected

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
    public function connect($host = '', $dbase = '', $uid = '', $pwd = '', $persist = 0, $charset='', $port = '') {

        if (empty($this->_currentDBEngine)) {
            throw new ErrorException('No database connection initialized.');
        } else {
            // Set configuration

            $this->config['user'] = !empty($uid) ? $uid : $this->config['user'];
            $this->config['pass'] = !empty($pwd) ? $pwd : $this->config['pass'];
            $this->config['dbase'] = !empty($dbase) ? $dbase : $this->config['dbase'];
            $this->config['host'] = !empty($host) ? $host : $this->config['host'];

            // Remove backticks from the database name
            $this->config['dbase'] = str_replace('`', '', $this->config['dbase']);

            switch ($this->_currentDBEngine) {
                case self::DB_MYSQL_MYISAM:
                case self::DB_MYSQL_INNODB:
                    // Connection charset is for MySQL only
                    $this->config['charset'] = !empty($charset) ? $charset : $this->config['charset'];
                    $this->conn = new ezSQL_mysql(
                            $this->config['user'],
                            $this->config['pass'],
                            $this->config['dbase'],
                            $this->config['host'],
                            $this->config['charset']
                    );

                    break;

                case self::DB_POSTGRESQL:
                    if (empty($port)) {
                        $this->config['port'] = self::POSTGRESQL_DEFAULT_PORT;
                    }
                    $this->conn = new ezSQL_postgresql(
                            $this->config['user'],
                            $this->config['pass'],
                            $this->config['dbase'],
                            $this->config['host'],
                            $this->config['port']
                    );

                    break;

                default:
                    throw new ErrorException('No valid database engine configured.');

                    break;
            }

            $this->_connected = true;
        }
    } // connect

    /**
     * Disconnect the current database connection
     *
     * @return boolean
     */
    public function disconnect() {
        $this->conn->disconnect();

        return true;
    } // disconnect

    /**
     * Escapes a string and uses the current database engines escape code.
     *
     * @param string $instr The string that has to be escaped
     * @return string The escaped string
     */
    public function escape($instr) {
        return $this->conn->escape($instr);
    } // escape

    /**
     * Executes an SQL statement.
     *
     * @param string $sql The SQL statement to execute.
     * @return object The result of the executed SQL statement.
     */
    public function query($sql) {
        global $modx;

        $tstart = $modx->getMicroTime();

        if (!$this->_connected) {
            $this->connect();
        }
        // Run the query and set the result
        $result = $this->conn->get_results($sql);

        $tend = $modx->getMicroTime();
        $totaltime = $tend - $tstart;
        $modx->queryTime($totaltime);

        if ($modx->getDumpSQL()) {
            $modx->queryCode .= "<fieldset style='text-align:left'><legend>Query " . ($this->executedQueries + 1) . " - " . sprintf("%2.4f s", $totaltime) . "</legend>" . $sql . "</fieldset><br />";
        }

        $modx->setExecutedQueries();

        // Reset record position
        $this->_curRecord = 0;

        return $result;
    } // query

    /**
     * Returns the configuration array.
     *
     * @return array The configuration array.
     */
    public function getConfiguration() {
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
    public function delete($from, $where='', $fields='') {
        if (!$from)
            $result = false;
        else {
            $where = ($where != '') ? "WHERE $where" : '';

            switch ($this->_currentDBEngine) {
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

    function getInsertId($conn=NULL) {
        $result = 0;

        if (!is_resource($conn)) {
            $conn =& $this->conn;
        }

        switch ($this->_currentDBEngine) {
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
     * Get the last database error.
     *
     * @param object $conn The connection, to check, by default the current
     *                     connection is used.
     * @return string The database error message.
     */
    public function getLastError($conn=NULL)  {
        $result = '';

        if (!is_resource($conn)) {
            $conn =& $this->conn;
        }

        switch ($this->_currentDBEngine) {
            case self::DB_MYSQL_MYISAM:
            case self::DB_MYSQL_INNODB:
                $result = mysql_error();

                break;

            case self::DB_POSTGRESQL:
                $result = pg_errormessage();

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
        switch ($this->_currentDBEngine) {
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

    /**
     * Returns the count of records
     *
     * @param arrays $ds
     * @return int
     */
    public function getRecordCount($ds) {
        return count($ds);
    } // getRecordCount

    /**
     * Returns the current record and increases the current record position
     *
     * @param array $ds
     * @param string $mode only for compatibility, not used at all
     * @return type
     */
    public function getRow($ds, $mode='assoc') {
        $result = $ds[$this->_curRecord];

        $this->_curRecord++;

        return $result;
    } // getRow

    /**
     * Returns the affected rows of the last query
     *
     * @param object $conn Only for compatiblity, not used anymore
     * @return int
     */
    public function getAffectedRows($conn=NULL) {
        return $this->conn->affectedRows();
    } // getAffectedRows

} // DBAPI
<?php
/**
 * Implements PDO to use it in MODX. It also emulates the methods of the
 * original DBAPI class in dbapi.mysql.class.inc.php.
 *
 * @author  MODX communiy
 * @uses    PDO
 * @name    DBAPI
 * @package MODX
 */
class DBAPI {
    
    /**
     * Constant for assoc row mode. 
     */
    const MODE_ASSOC = 'assoc';
    
    /**
     * Constant for assoc row num. 
     */
    const MODE_NUM = 'num';
    
    /**
     * Constant for assoc row both. 
     */
    const MODE_BOTH = 'both';

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
     * The database object.
     * @var type
     */
    public $conn = null;

    /**
     * The configuration array.
     * @var array
     */
    public $config = array();

    /**
     * The connection method for MySQL connections
     * @var string
     */
    private $_dbconnectionmethod;

    /**
     * Possible row modes
     * @var array
     */
    protected $_rowModes = array(
        'assoc',
        'num',
        'object'
    );

    /**
     * Possible database types
     * @var string
     */
    protected $_dbTypes = array();

    var $isConnected=false;
    var $dbtype='mysql';
    var $pdo=null;

    /**
     * DBAPI constructor, initialises and validates the properties.
     *
     * @global string $database_server
     * @global string $dbase
     * @global string $database_user
     * @global string $database_password
     * @global string $table_prefix
     * @global string $database_connection_charset
     * @global string $database_connection_method
     * @param string $host Default: Empty string
     * @param string $dbase Default: Empty string
     * @param string $uid Default: Empty string
     * @param string $pwd Default: Empty string
     * @param string $prefix Default: NULL
     * @param string $charset Default: Empty string
     * @param string $connection_method Default: SET CHARACTER SET
     * @param string $db_type Default: MySQL MyISAM
     * @param string $dbPort PostgreSQL needs a database port as parameter
     *                       Default: 5432 (PostgreSQL default port)
     * @throws Exception When the database type is not valid.
     */
    public function __construct($host='',$dbase='', $uid='',$pwd='',$prefix=NULL,
            $charset='', $connection_method='SET CHARACTER SET',
            $db_type=self::DB_MYSQL_MYISAM, $dbPort=self::POSTGRESQL_DEFAULT_PORT) {
        global $database_server, $dbase, $database_user, $database_password, $table_prefix, $database_connection_charset, $database_connection_method;

        $this->_dbTypes[] = self::DB_MYSQL_MYISAM;
        $this->_dbTypes[] = self::DB_MYSQL_INNODB;
        $this->_dbTypes[] = self::DB_POSTGRESQL;
        $this->_dbTypes[] = self::DB_SQLITE;

        $this->config['host']    = $host    ? $host    : $database_server;
        $this->config['dbase']   = $dbase   ? $dbase   : $dbase;
        $this->config['user']    = $uid     ? $uid     : $database_user;
        $this->config['pass']    = $pwd     ? $pwd     : $database_password;
        $this->config['charset'] = $charset ? $charset : $database_connection_charset;
        $this->config['connection_method'] = (isset($database_connection_method) ? $database_connection_method : $connection_method);
        $this->config['table_prefix'] = ($prefix !== NULL) ? $prefix : $table_prefix;
        $this->config['port'] = $dbPort;

        $this->_dbconnectionmethod = &$this->config['connection_method'];
        if (in_array($db_type, $this->_dbTypes)) {
            $this->_currentDBEngine = $db_type;
        } else {
            throw new Exception($db_type . ' is not a valid database engine!');
        }
        $this->initDataTypes();
    } // __construct

    /**
     * Getter for the current database.
     *
     * @return string The name of the current database engine.
     * @throws Exception When the current database engine is empty
     */
    public function getCurrentDatabaseEngine() {
        $result = '';

        if (empty($this->_currentDBEngine)) {
            throw new Exception('No database connection initialized.');
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
     * Called in the constructor to set up arrays containing the types of
     * database fields that can be used with specific PHP types
     */
    private function initDataTypes() {
        $this->dataTypes['numeric'] = array (
            'INT',
            'INTEGER',
            'TINYINT',
            'BOOLEAN',
            'DECIMAL',
            'DEC',
            'NUMERIC',
            'FLOAT',
            'DOUBLE PRECISION',
            'REAL',
            'SMALLINT',
            'MEDIUMINT',
            'BIGINT',
            'BIT'
        );
        $this->dataTypes['string'] = array (
            'CHAR',
            'VARCHAR',
            'BINARY',
            'VARBINARY',
            'TINYBLOB',
            'BLOB',
            'MEDIUMBLOB',
            'LONGBLOB',
            'TINYTEXT',
            'TEXT',
            'MEDIUMTEXT',
            'LONGTEXT',
            'ENUM',
            'SET'
        );
        $this->dataTypes['date'] = array (
            'DATE',
            'DATETIME',
            'TIMESTAMP',
            'TIME',
            'YEAR'
        );
    } // initDataTypes

    /**
     * Set up Database type(default mysql) For the moment, only MySQL can be set
     * up.
     *
     * @throws Exception When the database type is not valid.
     * @param string $dbtype Default; MySQL MyISAM
     */
    public function setDatabaseType($dbtype=self::DB_MYSQL_MYISAM) {
        if (in_array($dbtype, $this->_dbTypes)) {
            $this->_currentDBEngine = $db_type;
        } else {
            throw new Exception($db_type . ' is not a valid database engine!');
        }
    } // setDatabaseType

    /**
     * Establish a connection to of the database type.
     *
     * @param string $host The host address of the database server.
     *                    Default: Empty string
     * @param string $dbase The name of the database.
     *                    Default: Empty string
     * @param string $uid The database user name.
     *                    Default: Empty string
     * @param string $pwd The database password.
     *                    Default: Empty string
     * @param int $persist Only for compatibility.
     *                     Default: 0
     * @param string $charset MySQL connection charset.
     *                        Default: Empty string
     * @param string $dbType The database type.
     *                       Default: MySQL MyIsam
     * @param int $port PostgreSQL TCP/IP port, if empty and the current engine
     *                  is PostgreSQL, then the default port is used.
     * @throws ErrorException When the database type is not valid
     */
    public function connect($host='', $dbase='', $uid='', $pwd='', $persist=0, 
            $charset='', $dbType=self::DB_MYSQL_MYISAM, $port=self::POSTGRESQL_DEFAULT_PORT) {
        global $modx;

        if (!class_exists('PDO')) {
            throw new Exception('<b>Fatal Error: The system requires PDO Lib to be compiled and or linked in to the PHP engine');
        }
        if (!in_array($this->_currentDBEngine, $this->_dbTypes)) {
            throw new Exception('No valid database connection initialized.');
        } else {
            if (!$uid) {
                $uid   = $this->config['user'];
            }
            if (!$pwd) {
                $pwd   = $this->config['pass'];
            }
            if (!$host) {
                $host  = $this->config['host'];
            }
            if (!$dbase) {
                $dbase = $this->config['dbase'];
            }
            if ($dbType == self::DB_POSTGRESQL && !is_numeric($port)) {
                $port = self::POSTGRESQL_DEFAULT_PORT;
            }
            $dbase = str_replace('`', '', $dbase); // remove the `` chars
            $tstart = $modx->getMicroTime();
            $safe_count = 0;
            $errmsg='';
            
            $options = array(
                
            );

            while (!$this->pdo && $safe_count < 3) {
                try {
                    switch ($this->_currentDBEngine) {
                        case self::DB_MYSQL_MYISAM:
                        case self::DB_MYSQL_INNODB:
                            $this->pdo = new PDO(
                                'mysql:' . $host, 
                                $uid, 
                                $pwd, 
                                $options
                            );

                            break;
                        
                        case self::DB_POSTGRESQL:
                            $this->pdo = new PDO(
                                'pgsql:' . $host, 
                                $uid, 
                                $pwd, 
                                $options
                            );
                            
                            break;
                        
                        case self::DB_SQLITE:
                            $this->pdo = new PDO(
                                'sqlite:' . $host, 
                                $uid, 
                                $pwd, 
                                $options
                            );
                            
                            break;

                        default:
                            break;
                    }

                } catch(PDOException $e) {
                    sleep(3);
                    $safe_count++;
                    $errmsg.=($e->getMessage());
                    $this->pdo = null;
                }
            }

            if ( $this->pdo == null) {
                $modx->messageQuit('Failed to create the database connection!');
                exit;
            }

            if ($errmsg != '') {
                $request_uri = $_SERVER['REQUEST_URI'];
                $request_uri = htmlspecialchars($request_uri, ENT_QUOTES);
                $ua          = htmlspecialchars($_SERVER['HTTP_USER_AGENT'], ENT_QUOTES);
                $referer     = htmlspecialchars($_SERVER['HTTP_REFERER'], ENT_QUOTES);
                $logtitle = 'Became an error several times at DB connection. ';
                $msg = "{$logtitle}<br />{$request_uri}<br />{$ua}<br />{$referer}<br />[Error Message]{$errmsg}";
                $modx->logEvent(0, 2,$msg,$logtitle);
            } else {
                $tend = $modx->getMicroTime();
                $totaltime = $tend - $tstart;
                if ($modx->dumpSQL) {
                    $msg = sprintf("Database connection was created in %2.4f s", $totaltime);
                    $modx->queryCode .= '<fieldset style="text-align:left;"><legend>Database connection</legend>' . "{$msg}</fieldset>";
                }
                $this->isConnected = true;
                // FIXME (Fixed by line below):
                // this->queryTime = this->queryTime + $totaltime;
                $modx->queryTime += $totaltime;
            }
        }
    } // connect

    /**
    * @name:  disconnect
    *
    */
    /**
     * Disconnect the current database connection. 
     */
    public function disconnect()
    {
        $this->pdo = null;
        $this->_connected = false;
    } // disconnect

    /**
     * Escapes a string and uses the current database engines escape code.
     *
     * @param string $instr The string that has to be escaped
     * @return string The escaped string
     */
    public function escape($s) {
        if (empty($s)) {
            $result = '';
        } else {
            if ( $this->pdo == null ) {
                $this->connect();
            }
            $result = substr($this->pdo->quote($s), 1, -1);
        }

        return $result;
    } // escape

    /**
     * Executes an SQL statement.
     *
     * @param string $sql The SQL statement to execute.
     * @return ezSQL_recordset The result of the executed SQL statement.
     */
    public function query($sql) {
        global $modx;
        if ($this->pdo == null) {
            $this->connect();
        }
        $tstart = $modx->getMicroTime();
        if (!$result = $this->pdo->query($sql)) {
            $modx->messageQuit('Execution of a query to the database failed - ' . $this->getLastError(), $sql);
        } else {
            $tend = $modx->getMicroTime();
            $totaltime = $tend - $tstart;
            $modx->queryTime = $modx->queryTime + $totaltime;
            if ($modx->dumpSQL) {
                $backtraces = debug_backtrace();
                $modx->queryCode .= '<fieldset style="text-align:left"><legend>Query ' . ++$this->executedQueries . " - " . sprintf("%2.4f s", $totaltime) . '</legend>' . $sql . '<br />src : ' . $backtraces[0]['file'] . '<br />line : ' . $backtraces[0]['line'] . '</fieldset>';
            }
            $modx->executedQueries = $modx->executedQueries + 1;
            return $result;
        }
    } // query

    /**
     * Execute a DELETE statement on the current database connection.
     *
     * @param string $from The FROM clause can contain JOINS between tables,
     *                     be careful, because JOINS are not supported by all
     *                     RDBMS.
     *                     Default: Empty string
     * @param string $where The WHERE clause for the statement
     *                      Default: Empty string
     * @param string $limit The LIMIT clause for the statemen, will be traited
     *                      different for different RDBMS
     *                      Default: Empty string
     * @return boolean 
     */
    public function delete($from, $where='', $limit='') {
        if (!$from) {
            $result = false;
        } else {
            $table = $from;
            if ($where != '') {
                $where = "WHERE {$where}";
            }
            if ($limit != '') {
                switch ($this->_currentDBEngine) {
                    case self::DB_MYSQL_MYISAM:
                    case self::DB_MYSQL_INNODB:
                    case self::DB_SQLITE:
                        $limit = "LIMIT {$limit}";

                        break;
                    
                    case self::DB_POSTGRESQL:
                        // PostgreSQL has no limit in DELETE statements
                        $limit = '';
                        
                        break;

                   default:
                        break;
                }
                
            }
            $result = $this->query("DELETE FROM {$table} {$where} {$limit}");
        }

        return $result;
    } // delete

    /**
    * @name:  select
    *
    */
    /**
     * A select statement to get a result from the current database connection.
     *
     * @param string $fields Database fields, Default: *
     * @param string $from The FROM clause can contain JOINS between tables
     *                     Default: Empty string
     * @param string $where The WHERE clause for the statement
     *                      Default: Empty string
     * @param string $orderby The ORDER BY clause for the statement
     *                        Default: Empty string
     * @param string $limit The LIMIT clause for the statemen, will be traited
     *                      different for different RDBMS
     *                      Default: Empty string
     * @return boolean|?
     */
    public function select($fields="*", $from='', $where='', 
            $orderby = '', $limit = '') {
        if (!$from) {
            $result = false;
        } else {
            if ($where !== '') {
                $where = "WHERE {$where}";
            }
            if ($orderby !== '') {
                $orderby = "ORDER BY {$orderby}";
            }
            if ($limit !== '') {
                // All current database supported do use the same LIMIT clause
                $limit   = "LIMIT {$limit}";
            }
            $result = $this->query("SELECT {$fields} FROM {$from} {$where} {$orderby} {$limit}");
        }

        return $result;
    } // select

    /**
    * @name:  update
    *
    */
    /**
     *
     * @param string|array $fields Fields shout be given in a format as 
     *                             t1.f1 = 'something', you can also use an 
     *                             array with fieldname as key and value as 
     *                             value.
     * @param string $table The name of the table. Be aware, that the UPDATE
     *                      statement is defined by SQL standard as one table
     *                      and different RDBMS have different implementations.
     * @param string $where The WHERE clause for the statement
     *                      Default: Empty string
     * @return boolean|?
     */
    public function update($fields, $table, $where='') {
        if (!$table) {
            $result = false;
        } else {
            if (!is_array($fields)) {
                $pairs = $fields;
            } else {
                foreach ($fields as $key => $value) {
                    $pair[] = "{$key}='{$value}'";
                }
                $pairs = join(',',$pair);
            }
            if ($where != '') {
                $where = "WHERE {$where}";
            }
            $result = $this->query("UPDATE {$table} SET {$pairs} {$where}");
        }

        return $result;
    } // update

    /**
     * Insert statement to insert data as values or as select result.
     *
     * @param string|array< $fields The fields for the INSERT statement
     * @param string $intotable The table, where the data will be inserted
     * @param string $fromfields Default: *
     * @param string $fromtable Default: Empty string
     * @param string $where The WHERE clause for the statement
     *                      Default: Empty string
     * @param string $limit The LIMIT clause for the statemen, will be traited
     *                      different for different RDBMS
     *                      Default: Empty string
     * @return int|? Returns either last id inserted or the result from the query.
     */
    public function insert($fields, $intotable, $fromfields="*", $fromtable='', 
            $where ='', $limit='') {
        if (!$intotable) {
            $result = false;
        } else {
            if (!is_array($fields)) {
                $pairs = $fields;
            } else {
                $keys = array_keys($fields);
                $keys = implode(',', $keys) ;
                $values = array_values($fields);
                $values = "'" . implode("','", $values) . "'";
                $pairs = "({$keys}) ";
                if (!$fromtable && $values) {
                    $pairs .= "VALUES({$values})";
                }
                if ($fromtable) {
                    if ($where !== '') {
                        $where = "WHERE {$where}";
                    }
                    if ($limit !== '') {
                        $limit = "LIMIT {$limit}";
                    }
                    $sql = "SELECT {$fromfields} FROM {$fromtable} {$where} {$limit}";
                }
            }
            $rt = $this->query("INSERT INTO {$intotable} {$pairs} {$sql}");
            $lid = $this->getInsertId();
            $result = $lid ? $lid : $rt;
        }

        return $result;
    } // insert

    /**
     * Returns the last inserted id. This does only work with MySQL connections. 
     * Other databases do not support this method. In this cases, the result is
     * zero (0).
     *
     * @param string $name The sequenze name for the last id, this is not 
     *                     supported by most of the RDBMS.
     *                     Default: null
     * @return type 
     */
    public function getInsertId($name=null) {
        if (empty($name)) {
            $result = $this->pdo->lastInsertId();
        } else {
            $result = $this->pdo->lastInsertId($name);
        }

        return $result;
    } // getInsertId

    /**
     * Returns the affected rows of the last query, is the same as 
     * getRecordCount().
     *
     * @param object $ds Result of a query
     * @return int
     */
    public function getAffectedRows($ds) {
        return $this->getRecordCount($ds);
    } // getAffectedRows

    /**
     * Get the last database error, supported are is code, in this case, the 
     * function pdo->errorCode() is called, otherwise pdo->errorInfo() is
     * called.
     *
     * @param string $method Default: code
     * @return string The database error cdoe or message.
     */
    public function getLastError($method='code') {
        if ($method == 'code') {
            $result = $this->pdo->errorCode();
        } else {
            $result = $this->pdo->errorInfo();
        }

        return $result;
    } // getLastError

    /**
     * Returns the recordcount of the a result.
     *
     * @param object $ds
     * @return int
     */
     public function getRecordCount($ds) {
        return (is_object($ds)) ? $ds->rowCount() : 0;
    } // getRecordCount

    /**
     * Returns the current record and increases the current record position.
     *
     * @param stdClass $ds
     * @param string $mode Three modes are available, that are supported by
     *                     PDO class: fetch_assoc, fetch_row, fetch_both
     *                     Default: fetch_assoc
     * @return stdClass
     */
    public function getRow($ds, $mode=self::MODE_ASSOC) {
        if ($ds) {
            switch($mode) {
                case self::MODE_ASSOC:
                    $result = $ds->fetch(PDO::FETCH_ASSOC); 
                    break;
                
                case self::MODE_NUM:
                    $result = $ds->fetch(PDO::FETCH_NUM);
                    break;
                
                case self::MODE_BOTH:
                    $result = $ds->fetch(PDO::FETCH_BOTH);
                    break;

                default:
                    global $modx;
                    $modx->messageQuit("Unknown get type ({$mode}) specified for fetchRow - must be empty, 'assoc', 'num' or 'both'.");
            }
        }
    } // getRow

    /**
    * @name:  getColumn
    * @desc:  returns an array of the values found on colun $name
    * @param: $dsq - dataset or query string
    */
    function getColumn($name, $dsq)
    {
        if (!is_object($dsq)) $dsq = $this->query($dsq);
        if ($dsq)
        {
            $col = array ();
            while ($row = $this->getRow($dsq))
            {
                $col[] = $row[$name];
            }
            return $col;
        }
    }

    /**
    * @name:  getColumnNames
    * @desc:  returns an array containing the column $name
    * @param: $dsq - dataset or query string
    */
    function getColumnNames($dsq)
    {
        if (!is_object($dsq)) $dsq = $this->query($dsq);
        if ($dsq)
        {
            $names = array ();
            $limit = $dsq->columnCount();
            for ($i = 0; $i < $limit; $i++)
            {
                $md=$dsq->getColumnMeta($i);
                $names[] = $md['name'];
            }
            return $names;
        }

    }

    /**
    * @name:  getValue
    * @desc:  returns the value from the first column in the set
    * @param: $dsq - dataset or query string
    */
    function getValue($dsq)
    {
        if (!is_object($dsq)) $dsq = $this->query($dsq);
        if ($dsq)
        {
            $r = $this->getRow($dsq, 'num');
            return $r[0];
        }
    }

    /**
    * @name:  getXML
    * @desc:  returns an XML formay of the dataset $ds
    */
    function getXML($dsq)
    {
        if (!is_object($dsq)) $dsq = $this->query($dsq);
        $xmldata = "<xml>\r\n<recordset>\r\n";
        while ($row = $this->getRow($dsq, 'both'))
        {
            $xmldata .= "<item>\r\n";
            for ($j = 0; $line = each($row); $j++)
            {
                if ($j % 2)
                {
                    $xmldata .= "<{$line[0]}>{$line[1]}</{$line[0]}>\r\n";
                }
            }
            $xmldata .= "</item>\r\n";
        }
        $xmldata .= "</recordset>\r\n</xml>";
        return $xmldata;
    }

    /**
    * @name:  getTableMetaData
    * @desc:  returns an array of MySQL structure detail for each column of a
    *         table
    * @param: $table: the full name of the database table
    */
    function getTableMetaData($table)
    {
        $metadata = false;
        if (!empty ($table))
        {
            $sql = "SHOW FIELDS FROM {$table}";
            if ($ds = $this->query($sql))
            {
                while ($row = $this->getRow($ds))
                {
                    $fieldName = $row['Field'];
                    $metadata[$fieldName] = $row;
                }
            }
        }
        return $metadata;
    }

    /**
    * @name:  prepareDate
    * @desc:  prepares a date in the proper format for specific database types
    *         given a UNIX timestamp
    * @param: $timestamp: a UNIX timestamp
    * @param: $fieldType: the type of field to format the date for
    *         (in MySQL, you have DATE, TIME, YEAR, and DATETIME)
    */
    function prepareDate($timestamp, $fieldType = 'DATETIME')
    {
        $date = '';
        if (!$timestamp !== false && $timestamp > 0)
        {
            switch ($fieldType)
            {
                case 'DATE' :
                $date = date('Y-m-d', $timestamp);
                break;
                case 'TIME' :
                $date = date('H:i:s', $timestamp);
                break;
                case 'YEAR' :
                $date = date('Y', $timestamp);
                break;
                case 'DATETIME' :
                default :
                $date = date('Y-m-d H:i:s', $timestamp);
                break;
            }
        }
        return $date;
    }

    /**
    * @name:  getHTMLGrid
    * @param: $params: Data grid parameters
    *         columnHeaderClass
    *         tableClass
    *         itemClass
    *         altItemClass
    *         columnHeaderStyle
    *         tableStyle
    *         itemStyle
    *         altItemStyle
    *         columns
    *         fields
    *         colWidths
    *         colAligns
    *         colColors
    *         colTypes
    *         cellPadding
    *         cellSpacing
    *         header
    *         footer
    *         pageSize
    *         pagerLocation
    *         pagerClass
    *         pagerStyle
    *
    */
    function getHTMLGrid($dsq, $params)
    {
        //The PDO version is not write.
        return false;

        /*
        global $base_path;
        if (!is_resource($dsq)) $dsq = $this->query($dsq);
        if ($dsq)
        {
            include_once (MODX_MANAGER_PATH . 'includes/controls/datagrid.class.php');
            $grd = new DataGrid('', $dsq);

            $grd->noRecordMsg = $params['noRecordMsg'];

            $grd->columnHeaderClass = $params['columnHeaderClass'];
            $grd->cssClass = $params['cssClass'];
            $grd->itemClass = $params['itemClass'];
            $grd->altItemClass = $params['altItemClass'];

            $grd->columnHeaderStyle = $params['columnHeaderStyle'];
            $grd->cssStyle = $params['cssStyle'];
            $grd->itemStyle = $params['itemStyle'];
            $grd->altItemStyle = $params['altItemStyle'];

            $grd->columns = $params['columns'];
            $grd->fields = $params['fields'];
            $grd->colWidths = $params['colWidths'];
            $grd->colAligns = $params['colAligns'];
            $grd->colColors = $params['colColors'];
            $grd->colTypes = $params['colTypes'];
            $grd->colWraps = $params['colWraps'];

            $grd->cellPadding = $params['cellPadding'];
            $grd->cellSpacing = $params['cellSpacing'];
            $grd->header = $params['header'];
            $grd->footer = $params['footer'];
            $grd->pageSize = $params['pageSize'];
            $grd->pagerLocation = $params['pagerLocation'];
            $grd->pagerClass = $params['pagerClass'];
            $grd->pagerStyle = $params['pagerStyle'];

            return $grd->render();
        }
        */
    }

    /**
    * @name:  makeArray
    * @desc:  turns a recordset into a multidimensional array
    * @return: an array of row arrays from recordset, or empty array
    *          if the recordset was empty, returns false if no recordset
    *          was passed
    * @param: $rs Recordset to be packaged into an array
    */
    function makeArray($rs='')
    {
        if(!$rs) return false;
        $rsArray = array();
        $qty = $this->getRecordCount($rs);
        for ($i = 0; $i < $qty; $i++)
        {
            $rsArray[] = $this->getRow($rs);
        }
        return $rsArray;
    }

    /**
    * @name    getVersion
    * @desc    returns a string containing the database server version
    *
    * @return string
    */
    function getVersion()
    {
        return $this->pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
    }
}

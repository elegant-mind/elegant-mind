<?php
/**
 * Works on an ini style configuration file, that includes all configuration
 * properties of the current MODX instance.
 *
 * @author  Stefanie Janine Stoelting (mail@stefanie-stoelting.de)
 * @name    Configuration
 * @package MODX
 * @license LGPL
 */
class Configuration
{
    /**
     * Constant string configuration file
     */
    const CONFIG_INI_FILE = 'config.ini.inc.php';

    /**
     * Constant string
     */
    const PROPERTY_MAIN_AREA = 'MODX_base_configuration';
    /**
     * Constant string
     */
    const PROPERTY_DATABASE_TYPE = 'database_type';

    /**
     * Constant string
     */
    const PROPERTY_DATABASE_SERVER = 'database_server';

    /**
     * Constant string
     */
    const PROPERTY_DATABASE_USER = 'database_user';

    /**
     * Constant string
     */
    const PROPERTY_DATABASE_PASSWORD = 'database_password';

    /**
     * Constant string
     */
    const PROPERTY_DATABASE_CONNECTION_CHARSET = 'database_connection_charset';

    /**
     * Constant string
     */
    const PROPERTY_DATABASE_CONNECTION_METHOD = 'database_connection_method';

    /**
     * Constant string
     */
    const PROPERTY_DBASE = 'dbase';

    /**
     * Constant string
     */
    const PROPERTY_TABLE_PREFIX = 'table_prefix';

    /**
     * Constant string
     */
    const PROPERTY_ERROR_REPORTING = 'error_reporting';

    /**
     * Constant string
     */
    const PROPERTY_LAST_INSTALL_TIME = 'lastInstallTime';

    /**
     * Constant string
     */
    const PROPERTY_SITE_SESSIONNAME = 'site_sessionname';

    /**
     * Constant string
     */
    const PROPERTY_HTTPS_PORT = 'https_port';

    /**
     * The instance of the Configuration class
     * @var object Default: null
     */
    private static $instance = null;

    /**
     * Contains the ini properties
     * @var array Default: Empty array
     */
    private $_properties = array();

    /**
     * The path and file name of the configuration ini file
     * @var string
     */
    private $iniFile = '';

    /**
     * Empty and private constructor for the singleton.
     */
    private function __construct() {}

    /**
     * Disallow clone from outsite the DBAPI class.
     */
    private function __clone() {}

    /**
     * No deserializing with singltons.
     */
    private function __wakeup() {}

    /**
     * Document constructor for the singleton.
     *
     * @param string $iniFile The variable should contain the full path and name
     *                        of the ini file. An empty ini file is used as
     *                        default and in this case we take the current
     *                        directory and the default ini file name as defined
     *                        in CONFIG_INI_FILE
     *                        Default: empty string
     * @return Configuration
     * @throws Exeption Configuration file not found
     */
    public static function getInstance($iniFile='') {
        if (NULL === self::$instance) {
            self::$instance = new self;

            // With an empty ini file, we take the default one
            if (empty($iniFile)) {
                self::$instance->iniFile = __DIR__ . '/' . self::CONFIG_INI_FILE;
            } else {
                self::$instance->iniFile = $iniFile;
            }

            if (file_exists(self::$instance->iniFile)) {
                self::$instance->_properties = parse_ini_file(self::$instance->iniFile, true);
            } else {
                throw new Exception('Configuration file ' . self::$instance->iniFile . ' not found!');
            }
        }

        return self::$instance;
    } // getInstance

    /**
     * Returns a property from the ini style configuration. If the property does
     * not exist, an exeption is thrown.
     *
     * @param string $property
     * @param string $area Default: self::PROPERTY_MAIN_AREA
     * @return string
     * @throws Exception Property does not exist
     */
    public function getProperty($property, $area=self::PROPERTY_MAIN_AREA) {
        $result = '';

        if (array_key_exists($property, $this->_properties[self::PROPERTY_MAIN_AREA])) {
            $result = $this->_properties[$area][$property];
        } else {
            throw new Exception('Property ' . $property . ' in ' . $area . ' does not exist');
        }

        return $result;
    } // getProperty

    /**
     * Returns whether the configation file is writable, or not.
     *
     * @return boolean
     */
    public function isWritable() {
        return is_writable($this->iniFile);
    } // isWritable

    /**
     * Writes the ini file with all configurations.
     *
     * @return boolean
     */
    private function _writeIni() {
        $result = false;

        $data = array(
              '<?php'
            , ';die(); // For further security'
            , ';/*'

        );

        foreach ($this->_properties as $areaName => $areaProperties) {
            $data[] = '[' . $areaName . ']';

            foreach ($areaProperties as $key => $value) {
                $data[] = $key . ' = ' . $value;
            }
        }

        $data[] = ';*/';
        $data[] = ';?>';

        if ($fp = fopen($this->iniFile, 'w')) {
            $dataToSave = implode("\r\n", $data);

            $startTime = microtime();

            do {
                $canWrite = flock($fp, LOCK_EX);
                // If lock not obtained sleep for 0 - 100 milliseconds, to avoid collision and CPU load
                if (!$canWrite) {
                    usleep(round(rand(0, 100)*1000));
                }
            } while ((!$canWrite)and((microtime()-$startTime) < 1000));

            // File was locked so now we can store information
            if ($canWrite) {
                fwrite($fp, $dataToSave);
                flock($fp, LOCK_UN);
            }
            fclose($fp);

            $result = true;
        }

        return $result;
    } // writeIni

    /**
     * Changes a property, only existing properties are available, if a property
     * does not exist, an exception is thrown.
     *
     * @param string $property
     * @param string $value
     * @param string $area
     * @return boolean
     * @throws Exception Property does not exist
     */
    public function writeProperty($property, $value, $area=self::PROPERTY_MAIN_AREA) {
        $result = false;

        if ($this->isWritable()) {
            if (array_key_exists($property, $this->_properties[$area])) {
                $this->_properties[$area][$property] = $value;
                $result = true;
                $result = $this->_writeIni();
            } else {
                throw new Exception('Property ' . $property . ' in ' . $area . ' does not exist');
            }
        }

        return $result;
    } // writeProperty
    
    /**
     * Free the instance of this singleton
     * 
     * @return boolean 
     */
    public function freeInstance() {
        self::$instance = null;

        return true;
    } // freeInstance

    /**
     * Returns the current ini file
     *
     * @return stringtype
     */
    public function getIniFile() {
        return $this->iniFile;
    } // getIniFile

} // Configuration
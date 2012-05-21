<?php
/**
 * MODX Document Parser
 * Function: This class contains the main document parsing functions
 * At the moment, all variables are public, because there where no get/set
 * methods. Step by step the get/set methods will be implented.
 * The advantage is more control over properties and easier development.
 *
 * @author  The MODX community
 * @name    DocumentParser
 * @package MODX
 */
class DocumentParser {
    /**
     * Constant string for redirect refresh
     */
    const REDIRECT_REFRESH = 'REDIRECT_REFRESH';

    /**
     * Constant string for redirect meta
     */
    const REDIRECT_META = 'REDIRECT_META';

    /**
     * Constant string for redirect header
     */
    const REDIRECT_HEADER = 'REDIRECT_HEADER';

    /**
     * Constant string for getting the page by alias
     */
    const PAGE_BY_ALIAS = 'alias';

    /**
     * Constant string for getting the page by ID
     */
    const PAGE_BY_ID = 'id';

    /**
     * Database object
     * @var DBAPI
     */
    public $db; // db object
    var $event, $Event; // event object
    var $pluginEvent = array();
    var $config= null;
    var $rs;
    var $result;
    var $sql;
    var $table_prefix;
    var $debug;
    var $documentIdentifier;
    var $documentMethod;
    var $documentGenerated;
    var $documentContent;
    var $tstart;
    var $mstart;
    var $minParserPasses;
    var $maxParserPasses;
    var $documentObject;
    var $templateObject;
    var $snippetObjects;
    var $stopOnNotice;
    var $executedQueries;
    var $queryTime;
    var $currentSnippet;
    var $aliases;
    var $entrypage;
    var $documentListing;
    var $dumpSnippets;
    var $snipCode;
    var $chunkCache;
    var $snippetCache;
    var $contentTypes;
    var $dumpSQL;
    var $queryCode;
    var $virtualDir;
    var $placeholders;
    var $sjscripts = array();
    var $jscripts = array();
    var $loadedjscripts = array();
    var $documentMap;
    var $forwards= 3;
    var $referenceListing;
    var $documentMap_cache;
    var $safeMode;
    var $qs_hash;
    var $cacheRefreshTime;

    /**
     * Document constructor
     *
     * @return DocumentParser
     */
    public function __construct() {
        if (!isset($_REQUEST['id'])) {
            $_REQUEST['q'] = substr($_SERVER['REQUEST_URI'],strlen(MODX_BASE_URL));
            if(strpos($_REQUEST['q'],'?')) {
                $_REQUEST['q'] = substr($_REQUEST['q'],0,strpos($_REQUEST['q'],'?'));
            }
        }
        if ($_REQUEST['q']=='index.php') {
            $_REQUEST['q'] = '';
        }
        
        $this->loadExtension('DBAPI') or die('Could not load DBAPI class.'); // load DBAPI class
        // events
        $this->event= new SystemEvent();
        $this->Event= & $this->event; //alias for backward compatibility
        $this->minParserPasses = 1; // min number of parser recursive loops or passes
        $this->maxParserPasses = 10; // max number of parser recursive loops or passes
        $this->dumpSQL = false;
        $this->dumpSnippets = false; // feed the parser the execution start time
        $this->stopOnNotice = false;
        $this->safeMode     = false;
        // set track_errors ini variable
        @ ini_set('track_errors', '1'); // enable error tracking in $php_errormsg
        // Don't show PHP errors to the public
        if($this->checkSession()===false) {
            @ini_set('display_errors','0');
        }
    } // __construct

    /**
     * Loads an extension from the extenders folder. By now it does load the
     * database classes and the manager API class.
     *
     * @global string $database_type
     * @param string $extnamegetAllChildren
     * @return boolean
     */
    public function loadExtension($extname) {
        global $database_type;

        $result = false;

        switch ($extname) {
            // Database API
            case 'DBAPI' :
                if (!include_once MODX_BASE_PATH . 'manager/includes/extenders/dbapi.' . $database_type . '.class.inc.php')
                    return false;
                $this->db= new DBAPI;
                $result = true;
                break;

                // Manager API
            case 'ManagerAPI' :
                if (!include_once MODX_BASE_PATH . 'manager/includes/extenders/manager.api.class.inc.php') {
                    $result = false;
                } else {
                    $this->manager= new ManagerAPI;
                    $result = true;
                }

                break;

            default :
                $result = false;
        }

        return $result;
    } // loadExtension
    
    /**
     * Returns the current micro time
     *
     * @return float
     */
    public function getMicroTime() {
        list ($usec, $sec)= explode(' ', microtime());
        return ((float) $usec + (float) $sec);
    } // getMicroTime

    /**
     * Execute redirect
     *
     * @global string $base_url
     * @global string $site_url
     * @param string $url
     * @param int $count_attempts
     * @param type $type
     * @param type $responseCode
     * @return boolean
     */
    public function sendRedirect($url, $count_attempts=0, $type='', $responseCode='') {
        if (empty ($url)) {
            return false;
        } else {
            if ($count_attempts == 1) {
                // append the redirect count string to the url
                $currentNumberOfRedirects= isset ($_REQUEST['err']) ? $_REQUEST['err'] : 0;
                if ($currentNumberOfRedirects > 3) {
                    $this->messageQuit('Redirection attempt failed - please ensure the document you\'re trying to redirect to exists. <p>Redirection URL: <i>' . $url . '</i></p>');
                } else {
                    $currentNumberOfRedirects += 1;
                    if (strpos($url, "?") > 0) {
                        $url .= "&err=$currentNumberOfRedirects";
                    } else {
                        $url .= "?err=$currentNumberOfRedirects";
                    }
                }
            }
            if ($type == self::REDIRECT_REFRESH) {
                $header= 'Refresh: 0;URL=' . $url;
            } elseif ($type == self::REDIRECT_META) {
                $header= '<META HTTP-EQUIV="Refresh" CONTENT="0; URL=' . $url . '" />';
                echo $header;
                exit;
            } elseif ($type == self::REDIRECT_HEADER || empty ($type)) {
                // check if url has /$base_url
                global $base_url, $site_url;
                if (substr($url, 0, strlen($base_url)) == $base_url) {
                    // append $site_url to make it work with Location:
                    $url= $site_url . substr($url, strlen($base_url));
                }
                if (strpos($url, "\n") === false) {
                    $header= 'Location: ' . $url;
                } else {
                    $this->messageQuit('No newline allowed in redirect url.');
                }
            }
            if ($responseCode && (strpos($responseCode, '30') !== false)) {
                header($responseCode);
            }
            header($header);
            exit();
        }
    } // sendRedirect

    /**
     * Forward to an other page
     *
     * @param int $id
     * @param string $responseCode
     */
    private function sendForward($id, $responseCode='') {
        if ($this->forwards > 0) {
            $this->forwards= $this->forwards - 1;
            $this->documentIdentifier= $id;
            $this->documentMethod= self::PAGE_BY_ID;
            $this->documentObject= $this->getDocumentObject(self::PAGE_BY_ID, $id);
            if ($responseCode) {
                header($responseCode);
            }
            $result = $this->prepareResponse();
            echo $result;
        } else {
            header('HTTP/1.0 500 Internal Server Error');
            die('<h1>ERROR: Too many forward attempts!</h1><p>The request could not be completed due to too many unsuccessful forward attempts.</p>');
        }
        exit();
    } // sendForward

    /**
     * Redirect to the error page, by calling sendForward. This is called for
     * example when the page was not found.
     */
    private function sendErrorPage() {
        // invoke OnPageNotFound event
        $this->invokeEvent('OnPageNotFound');
        
        if($this->config['error_page']) {
            $dist = $this->config['error_page'];
        } else {
            $dist = $this->config['site_start'];
        }
        
        $this->sendForward($dist, 'HTTP/1.0 404 Not Found');
    } // sendErrorPage
    
    /**
     * Redirect to the unauthorized page, for example on calling a page, without
     * having the right to see this page.
     */
    private function sendUnauthorizedPage() {
        // invoke OnPageUnauthorized event
        $_REQUEST['refurl'] = $this->documentIdentifier;
        $this->invokeEvent('OnPageUnauthorized');
        
        if ($this->config['unauthorized_page']) {
            $dist = $this->config['unauthorized_page'];
        } elseif ($this->config['error_page']) {
            $dist = $this->config['error_page'];
        } else {
            $dist = $this->config['site_start'];
        }
        
        $this->sendForward($dist , 'HTTP/1.1 401 Unauthorized');
    } // sendUnauthorizedPage


    /**
     * Function to connect to the database
     *
     * @deprecated use $modx->db->connect()
     */
    public function dbConnect() {
        $this->db->connect();
        $this->rs= $this->db->conn; // for compatibility
    } // dbConnect

    /**
     * Function to query the database
     *
     * @deprecated use $modx->db->query()
     * @param string $sql The SQL statement to execute
     * @return array Query result
     */
    public function dbQuery($sql) {
        return $this->db->query($sql);
    } // dbQuery

    /**
     * Function to count the number of rows in a record set
     *
     * @deprecated use $modx->db->getRecordCount($rs)
     * @param array $rs
     * @return int
     */
    public function recordCount($rs) {
        return $this->db->getRecordCount($rs);
    } // recordCount

    /**
     * @deprecated use $modx->db->getRow()
     * @param array $rs
     * @param string $mode
     * @return array
     */
    public function fetchRow($rs, $mode='assoc') {
        return $this->db->getRow($rs, $mode);
    } // fetchRow

    /**
     * @deprecated use $modx->db->getAffectedRows()
     * @param array $rs
     * @return int
     */
    public function affectedRows($rs) {
        return $this->db->getAffectedRows($rs);
    } // affectedRows

    /**
     * @deprecated use $modx->db->getInsertId()
     * @param array $rs
     * @return int
     */
    public function insertId($rs) {
        return $this->db->getInsertId($rs);
    } // insertId

    /**
     * Function to close a database connection
     *
     * @deprecated use $modx->db->disconnect()
     */
    public function dbClose() {
        $this->db->disconnect();
    } // dbClose

    /**
     * Setup MODX settings
     */
    public function getSettings() {
        if (!isset($this->config) || !is_array($this->config) || empty ($this->config)) {
            if (file_exists(MODX_BASE_PATH . 'assets/cache/siteCache.idx.php')) {
                $included= include_once (MODX_BASE_PATH . 'assets/cache/siteCache.idx.php');
            }
            if (!isset($included) || !is_array($this->config) || empty ($this->config)) {
                include_once MODX_MANAGER_PATH . 'processors/cache_sync.class.processor.php';
                $cache = new synccache();
                $cache->setCachepath(MODX_BASE_PATH . 'assets/cache/');
                $cache->setReport(false);
                $rebuilt = $cache->buildCache($this);
                $included = false;
                if ($rebuilt && file_exists(MODX_BASE_PATH . 'assets/cache/siteCache.idx.php')) {
                    $included= include_once(MODX_BASE_PATH . 'assets/cache/siteCache.idx.php');
                }
                if (!$included) {
                    $result= $this->db->select('setting_name, setting_value', $this->getFullTableName('system_settings'));
                    while ($row= $this->db->getRow($result, 'both')) {
                        $this->config[$row[0]]= $row[1];
                    }
                }
            }
            // added for backwards compatibility - garry FS#104
            $this->config['etomite_charset'] = & $this->config['modx_charset'];
            
            // store base_url and base_path inside config array
            $this->config['base_url']= MODX_BASE_URL;
            $this->config['base_path']= MODX_BASE_PATH;
            if (!isset($this->config['site_url']) || empty($this->config['site_url'])) {
                $this->config['site_url']= MODX_SITE_URL;
            }
        }
        // load user setting if user is logged in
        $tbl_user_settings = $this->getFullTableName('user_settings');
        $usrSettings= array();
        if ($id= $this->getLoginUserID()) {
            $usrType= $this->getLoginUserType();
            if (isset ($usrType) && $usrType == 'manager') {
                $usrType= 'mgr';
            }
            
            if ($usrType == 'mgr' && $this->isBackend()) {
                // invoke the OnBeforeManagerPageInit event, only if in backend
                $this->invokeEvent('OnBeforeManagerPageInit');
            }
            if (isset ($_SESSION["{$usrType}UsrConfigSet"]) && 0 < count($_SESSION["{$usrType}UsrConfigSet"])) {
                $usrSettings= & $_SESSION["{$usrType}UsrConfigSet"];
            } else {
                if ($usrType == 'web') {
                    $from  = $this->getFullTableName('web_user_settings');
                    $where ="webuser='{$id}'";
                } else {
                    $from  = $tbl_user_settings;
                    $where = "user='{$id}'";
                }
                $result= $this->db->select('setting_name, setting_value', $from, $where);
                while ($row= $this->db->getRow($result, 'both')) {
                    $usrSettings[$row[0]]= $row[1];
                }
                if (isset ($usrType)) {
                    $_SESSION[$usrType . 'UsrConfigSet']= $usrSettings; // store user settings in session
                }
            }
        }
        if ($this->isFrontend() && $mgrid= $this->getLoginUserID('mgr')) {
            $musrSettings= array ();
            if (isset ($_SESSION['mgrUsrConfigSet'])) {
                $musrSettings= & $_SESSION['mgrUsrConfigSet'];
            } else {
                if ($result= $this->db->select('setting_name, setting_value', $tbl_user_settings, "user='{$mgrid}'")) {
                    while ($row= $this->db->getRow($result, 'both')) {
                        $usrSettings[$row[0]]= $row[1];
                    }
                    $_SESSION['mgrUsrConfigSet']= $musrSettings; // store user settings in session
                }
            }
            if (!empty ($musrSettings)) {
                $usrSettings= array_merge($musrSettings, $usrSettings);
            }
        }
        $this->config= array_merge($this->config, $usrSettings);
    } // getSettings
    
    /**
     * Returns the requested document method, whether it was by alias or by id
     *
     * @return string
     */
    private function getDocumentMethod() {
        // function to test the query and find the retrieval method
        if (isset ($_REQUEST['q'])) {
            return self::PAGE_BY_ALIAS;
        } elseif (isset ($_REQUEST[self::PAGE_BY_ID])) {
            return self::PAGE_BY_ID;
        } else {
            $result = "none";
        }

        return $result;
    } // getDocumentMethod

    /**
     * Returns the document identifier of the current request
     *
     * @param string $method id and alias are allowed
     * @return int
     */
    private function getDocumentIdentifier($method) {
        // function to test the query and find the retrieval method
        $docIdentifier= $this->config['site_start'];
        switch ($method) {
            case self::PAGE_BY_ALIAS :
                $docIdentifier= $this->db->escape($_REQUEST['q']);
                break;
            case self::PAGE_BY_ID :
                if (!is_numeric($_REQUEST[self::PAGE_BY_ID])) {
                    $this->sendErrorPage();
                } else {
                    $docIdentifier= intval($_REQUEST[self::PAGE_BY_ID]);
                }
                break;
        }
        return $docIdentifier;
    } // getDocumentIdentifier

    /**
     * Check for manager login session
     *
     * @return boolean
     */
    public function checkSession() {
        if (isset ($_SESSION['mgrValidated'])) {
            $result = true;
        } else {
            $result = false;
        }

        return $result;
    } // checkSession

    /**
     * Checks, if a the result is a preview
     *
     * @return boolean
     */
    private function checkPreview() {
        if ($this->checkSession() == true) {
            if (isset ($_REQUEST['z']) && $_REQUEST['z'] == 'manprev') {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    } // checkPreview

    /**
     * check if site is offline
     *
     * @return boolean
     */
    private function checkSiteStatus() {
        $siteStatus= $this->config['site_status'];
        if ($siteStatus == 1) {
            // site online
            return true;
        } elseif ($siteStatus == 0 && $this->checkSession()) {
            // site offline but launched via the manager
            return true;
        } else {
            // site is offline
            return false;
        }
    } // checkSiteStatus

    /**
     * Create the document identifier with path informations, friendly URL
     * postfix and prefix.
     *
     * @param string $qOrig
     * @return string
     */
    private function cleanDocumentIdentifier($qOrig) {
        (!empty($qOrig)) or $qOrig = $this->config['site_start'];
        $q= $qOrig;
        /* First remove any / before or after */
        if ($q[strlen($q) - 1] == '/')
            $q= substr($q, 0, -1);
        if ($q[0] == '/')
            $q= substr($q, 1);
        /* Save path if any */
        /* FS#476 and FS#308: only return virtualDir if friendly paths are enabled */
        if ($this->config['use_alias_path'] == 1) {
            $this->virtualDir = dirname($q);
            $this->virtualDir = ($this->virtualDir == '.' ? '' : $this->virtualDir);
            $q= basename($q);
        } else {
            $this->virtualDir = '';
        }
        $q= str_replace($this->config['friendly_url_prefix'], "", $q);
        $q= str_replace($this->config['friendly_url_suffix'], "", $q);
        if (is_numeric($q) && !$this->documentListing[$q]) { /* we got an ID returned, check to make sure it's not an alias */
            /* FS#476 and FS#308: check that id is valid in terms of virtualDir structure */
            if ($this->config['use_alias_path'] == 1) {
                if ((($this->virtualDir != '' && !$this->documentListing[$this->virtualDir . '/' . $q]) || ($this->virtualDir == '' && !$this->documentListing[$q])) && (($this->virtualDir != '' && in_array($q, $this->getChildIds($this->documentListing[$this->virtualDir], 1))) || ($this->virtualDir == '' && in_array($q, $this->getChildIds(0, 1))))) {
                    $this->documentMethod = self::PAGE_BY_ID;
                    return $q;
                } else { /* not a valid id in terms of virtualDir, treat as alias */
                    $this->documentMethod = self::PAGE_BY_ALIAS;
                    return $q;
                }
            } else {
                $this->documentMethod = self::PAGE_BY_ID;
                return $q;
            }
        } else { /* we didn't get an ID back, so instead we assume it's an alias */
            if ($this->config['friendly_alias_urls'] != 1) {
                $q= $qOrig;
            }
            $this->documentMethod= self::PAGE_BY_ALIAS;
            return $q;
        }
    } // cleanDocumentIdentifier

    /**
     * Check the cache for a given document identifier
     *
     * @param int $id
     * @return string
     */
    private function checkCache($id) {
        if (isset($this->config['cache_type']) && $this->config['cache_type'] == 0) {
            return '';
        }
        $cacheFile = "{$this->config['base_path']}assets/cache/docid_{$id}{$this->qs_hash}.pageCache.php";
        
        if (isset($_SESSION['mgrValidated']) || 0 < count($_POST)) {
            $this->config['cache_type'] = '1';
        }
        
        if ($this->config['cache_type'] == 2) {
            $flContent = '';
        } elseif (file_exists($cacheFile)) {
            $flContent = file_get_contents($cacheFile, false);
        }
        if (!file_exists($cacheFile) || empty($flContent)) {
            $this->documentGenerated = 1;
            return '';
        }
        
        $this->documentGenerated = 0;
        
        $flContent = substr($flContent, 37); // remove php header
        $a = explode('<!--__MODxCacheSpliter__-->', $flContent, 2);
        if(count($a) == 1) {
            return $a[0]; // return only document content
        }
        
        $docObj = unserialize(trim($a[0])); // rebuild document object
        // add so - check page security(admin(mgrRole=1) is pass)
        if (!(isset($_SESSION['mgrRole']) && $_SESSION['mgrRole'] == 1) 
            && $docObj['privateweb'] && isset ($docObj['__MODxDocGroups__'])) {
            $pass = false;
            $usrGrps = $this->getUserDocGroups();
            $docGrps = explode(',', $docObj['__MODxDocGroups__']);
            // check is user has access to doc groups
            if (is_array($usrGrps)) {
                foreach ($usrGrps as $k => $v) {
                    if (in_array($v, $docGrps)) {
                        $pass = true;
                        break;
                    }
                }
            }
            // diplay error pages if user has no access to cached doc
            if (!$pass) {
                if($this->config['unauthorized_page']) {
                    // check if file is not public
                    $tbl_document_groups = $this->getFullTableName('document_groups');
                    $secrs = $this->db->select('id', $tbl_document_groups, "document='{$id}'",'',1);
                    if ($secrs) {
                        $seclimit = $this->db->getRecordCount($secrs);
                    }
                }
                if ($seclimit > 0) {
                    // match found but not publicly accessible, send the visitor to the unauthorized_page
                    $this->sendUnauthorizedPage();
                } else {
                    // no match found, send the visitor to the error_page
                    $this->sendErrorPage();
                }
            }
            // Grab the Scripts
            if (isset($docObj['__MODxSJScripts__'])) {
                $this->sjscripts = $docObj['__MODxSJScripts__'];
            }
            if (isset($docObj['__MODxJScripts__'])) {
                $this->jscripts  = $docObj['__MODxJScripts__'];
            }
            
            // Remove intermediate variables
            unset($docObj['__MODxDocGroups__'], $docObj['__MODxSJScripts__'], $docObj['__MODxJScripts__']);
        }
        $this->documentObject = $docObj;
        return $a[1]; // return document content
    } // checkCache
    
    /**
     * Returns the content with echo function
     *
     * @param boolean $noEvent Default: false
     */
    private function outputContent($noEvent=false) {
        
        $this->documentOutput= $this->documentContent;
        
        if ($this->documentGenerated == 1 && $this->documentObject['cacheable'] == 1
            && $this->documentObject['type'] == 'document' && $this->documentObject['published'] == 1) {
            if (!empty($this->sjscripts)) {
                $this->documentObject['__MODxSJScripts__'] = $this->sjscripts;
            }
            if (!empty($this->jscripts)) {
                $this->documentObject['__MODxJScripts__'] = $this->jscripts;
            }
        }
        
        // check for non-cached snippet output
        if (strpos($this->documentOutput, '[!') !== false) {
            // Parse document source
            $passes = $this->minParserPasses;
            
            for ($i= 0; $i < $passes; $i++) {
                if ($i == ($passes -1)) {
                    $st= md5($this->documentOutput);
                }
                $this->documentOutput = str_replace(array('[!','!]'), array('[[',']]'), $this->documentOutput);
                $this->documentOutput = $this->parseDocumentSource($this->documentOutput);
                
                if ($i == ($passes -1) && $i < ($this->maxParserPasses - 1)) {
                    $et = md5($this->documentOutput);
                    if ($st != $et) {
                        $passes++;
                    }
                }
            }
        }
        
        // Moved from prepareResponse() by sirlancelot
        if ($js= $this->getRegisteredClientStartupScripts()) {
            $this->documentOutput= preg_replace("/(<\/head>)/i", $js . "\n\\1", $this->documentOutput);
        }
        
        // Insert jscripts & html block into template - template must have a </body> tag
        if ($js= $this->getRegisteredClientScripts()) {
            $this->documentOutput= preg_replace("/(<\/body>)/i", $js . "\n\\1", $this->documentOutput);
        }
        // End fix by sirlancelot
        
        // remove all unused placeholders
        if (strpos($this->documentOutput, '[+') > -1) {
            $matches= array ();
            preg_match_all('~\[\+(.*?)\+\]~', $this->documentOutput, $matches);
            if ($matches[0]) {
                $this->documentOutput= str_replace($matches[0], '', $this->documentOutput);
            }
        }
        
        if (strpos($this->documentOutput,'[~') !== false) {
            $this->documentOutput = $this->rewriteUrls($this->documentOutput);
        }
        
        // send out content-type and content-disposition headers
        if (IN_PARSER_MODE == 'true') {
            $type = $this->documentObject['contentType'];
            if(empty($type)) $type = 'text/html';
            
            header("Content-Type: {$type}; charset={$this->config['modx_charset']}");
            //            if (($this->documentIdentifier == $this->config['error_page']) || $redirect_error)
            //                header('HTTP/1.0 404 Not Found');
            if ($this->documentObject['content_dispo'] == 1) {
                if ($this->documentObject['alias']) {
                    $name= urldecode($this->documentObject['alias']);
                } else {
                    // strip title of special characters
                    $name= $this->documentObject['pagetitle'];
                    $name= strip_tags($name);
                    $name= preg_replace('/&.+?;/', '', $name); // kill entities
                    $name= preg_replace('/\s+/', '-', $name);
                    $name= preg_replace('|-+|', '-', $name);
                    $name= trim($name, '-');
                }
                $header= 'Content-Disposition: attachment; filename=' . $name;
                header($header);
            }
        }
        if ($this->config['cache_type'] !=2) {
            $this->documentOutput = $this->mergeBenchmarkContent($this->documentOutput);
        }
        
        if ($this->dumpSQL) {
            $this->documentOutput = preg_replace("/(<\/body>)/i", $this->queryCode . "\n\\1", $this->documentOutput);
        }
        if ($this->dumpSnippets) {
            $this->documentOutput = preg_replace("/(<\/body>)/i", $this->snipCode . "\n\\1", $this->documentOutput);
        }
        
        // invoke OnWebPagePrerender event
        if (!$noEvent) {
            $this->invokeEvent('OnWebPagePrerender');
        }
        if(strpos($this->documentOutput,'[^')) {
            echo $this->mergeBenchmarkContent($this->documentOutput);
        } else { 
            echo $this->documentOutput;
        }
        $result = ob_get_clean();
        return $result;
    } // outputContent
    
    /**
     * Checks the publish state of page
     */
    public function checkPublishStatus() {
        $tbl_site_content = $this->getFullTableName('site_content');
        $tbl_site_htmlsnippets = $this->getFullTableName('site_htmlsnippets');
        $cache_path= "{$this->config['base_path']}assets/cache/sitePublishing.idx.php";
        if($this->cacheRefreshTime=='') {
            if(file_exists($cache_path)) {
                include_once($cache_path);
                $this->cacheRefreshTime = $cacheRefreshTime;
            } else {
                $this->cacheRefreshTime = 0;
            }
        }
        $timeNow= time() + $this->config['server_offset_time'];
        
        if ($timeNow < $this->cacheRefreshTime || $this->cacheRefreshTime == 0) {
            return;
        }
        
        // now, check for documents that need publishing
        $fields = "published='1', publishedon=pub_date";
        $where = "pub_date <= {$timeNow} AND pub_date!=0 AND published=0";
        $rs = $this->db->update($fields,$tbl_site_content, $where);
        
        // now, check for documents that need un-publishing
        $fields = "published='0', publishedon='0'";
        $where = "unpub_date <= {$timeNow} AND unpub_date!=0 AND published=1";
        $rs = $this->db->update($fields,$tbl_site_content, $where);
    
        // now, check for chunks that need publishing
        $fields = "published='1'";
        $where = "pub_date <= {$timeNow} AND pub_date!=0 AND published=0";
        $rs = $this->db->update($fields,$tbl_site_htmlsnippets, $where);
        
        // now, check for chunks that need un-publishing
        $fields = "published='0'";
        $where = "unpub_date <= {$timeNow} AND unpub_date!=0 AND published=1";
        $rs = $this->db->update($fields,$tbl_site_htmlsnippets, $where);
    
        unset($this->chunkCache);
    
        // clear the cache
        $this->clearCache();
        
        // update publish time file
        $timesArr= array ();
        $rs = $this->db->select('MIN(pub_date) AS minpub', $tbl_site_content, "{$timeNow} < pub_date");
        $minpub = $this->db->getValue($rs);
        if ($minpub != NULL) {
            $timesArr[]= $minpub;
        }
        
        $rs = $this->db->select('MIN(unpub_date) AS minunpub', $tbl_site_content, "{$timeNow} < unpub_date");
        $minunpub = $this->db->getValue($rs);
        if ($minunpub != NULL) {
            $timesArr[]= $minunpub;
        }
        
        $rs = $this->db->select('MIN(pub_date) AS minpub', $tbl_site_htmlsnippets, "{$timeNow} < pub_date");
        $minpub = $this->db->getValue($rs);
        if ($minpub != NULL) {
            $timesArr[]= $minpub;
        }
        
        $rs = $this->db->select('MIN(unpub_date) AS minunpub', $tbl_site_htmlsnippets, "{$timeNow} < unpub_date");
        $minunpub = $this->db->getValue($rs);
        if ($minunpub != NULL) {
            $timesArr[]= $minunpub;
        }
        
        if (count($timesArr) > 0) {
            $nextevent = min($timesArr);
        } else { 
            $nextevent = 0;
        }
        
        $content = '<?php $cacheRefreshTime=' . $nextevent . ';';
        file_put_contents($cache_path, $content);
    } // checkPublishStatus

    /**
     * Running post processes
     */
    public function postProcess() {
        // if the current document was generated, cache it!
        if ($this->documentGenerated == 1 && $this->documentObject['cacheable'] == 1
            && $this->documentObject['type'] == 'document' && $this->documentObject['published'] == 1) {

            $tbl_document_groups = $this->getFullTableName('document_groups');
            $docid = $this->documentIdentifier;
            
            // invoke OnBeforeSaveWebPageCache event
            $this->invokeEvent('OnBeforeSaveWebPageCache');
            // get and store document groups inside document object. Document groups will be used to check security on cache pages
            $dsq = $this->db->select('document_group', $tbl_document_groups, "document='{$docid}'");
            $docGroups= $this->db->getColumn('document_group', $dsq);
            
            // Attach Document Groups and Scripts
            if (is_array($docGroups)) {
                $this->documentObject['__MODxDocGroups__'] = implode(',', $docGroups);
            }
            
            $base_path = $this->config['base_path'];
            
            switch($this->config['cache_type'])
            {
                case '1':
                    $cacheContent  = "<?php die('Unauthorized access.'); ?>\n"
                                   . serialize($this->documentObject)
                                   . "<!--__MODxCacheSpliter__-->{$this->documentContent}";
                    $filename = "docid_{$docid}{$this->qs_hash}";
                    
                    break;
                
                case '2':
                    $cacheContent  = $this->documentOutput;
                    $filename = md5($_SERVER['REQUEST_URI']);
                    
                    break;
            }
            $page_cache_path = "{$base_path}assets/cache/{$filename}.pageCache.php";
            file_put_contents($page_cache_path, $cacheContent);
        }
        
        // invoke OnLogPageView event
        if ($this->config['track_visitors'] == 1) {
            $this->invokeEvent('OnLogPageHit');
        }
        
        // Useful for example to external page counters/stats packages
        $this->invokeEvent('OnWebPageComplete');
    } // postProcess
    
    /**
     * Add meta tags to the document
     *
     * @param string $template
     * @return string
     */
    private function mergeDocumentMETATags($template) {
        $metas = '';
        if ($this->documentObject['haskeywords'] == 1) {
            // insert keywords
            $keywords = $this->getKeywords();
            if (is_array($keywords) && count($keywords) > 0) {
                $keywords = implode(", ", $keywords);
                $metas= "\t<meta name=\"keywords\" content=\"$keywords\" />\n";
            }

            // Don't process when cached
            $this->documentObject['haskeywords'] = '0';
        }
        if ($this->documentObject['hasmetatags'] == 1) {
            // insert meta tags
            $tags= $this->getMETATags();
            foreach ($tags as $n => $col) {
                $tag= strtolower($col['tag']);
                $tagvalue= $col['tagvalue'];
                $tagstyle= $col['http_equiv'] ? 'http-equiv' : 'name';
                $metas .= "\t<meta $tagstyle=\"$tag\" content=\"$tagvalue\" />\n";
            }

            // Don't process when cached
            $this->documentObject['hasmetatags'] = '0';
        }
        if (isset($metas) && $metas) {
            $template = preg_replace("/(<head>)/i", "\\1\n\t" . trim($metas), $template);
        }
        return $template;
    } // mergeDocumentMETATags

    /**
     * mod by Raymond
     *
     * @param string $template
     * @return string
     */
    public function mergeDocumentContent($template) {
        $replace= array ();
        preg_match_all('~\[\*(.*?)\*\]~', $template, $matches);
        $variableCount= count($matches[1]);
        $basepath= $this->config["base_path"] . "manager/includes";
        for ($i= 0; $i < $variableCount; $i++) {
            $key= $matches[1][$i];
            $key= substr($key, 0, 1) == '#' ? substr($key, 1) : $key; // remove # for QuickEdit format
            $value= $this->documentObject[$key];
            if (is_array($value)) {
                include_once $basepath . "/tmplvars.format.inc.php";
                include_once $basepath . "/tmplvars.commands.inc.php";
                $w= "100%";
                $h= "300";
                $value= getTVDisplayFormat($value[0], $value[1], $value[2], $value[3], $value[4]);
            }
            $replace[$i]= $value;
        }
        $template= str_replace($matches[0], $replace, $template);

        return $template;
    } // mergeDocumentContent

    /**
     *
     * @param string $template
     * @return string
     */
    public function mergeSettingsContent($template) {
        $replace= array ();
        $matches= array ();
        if (preg_match_all('~\[\(([a-z\_]*?)\)\]~', $template, $matches)) {
            $settingsCount= count($matches[1]);
            for ($i= 0; $i < $settingsCount; $i++) {
                if (array_key_exists($matches[1][$i], $this->config))
                    $replace[$i]= $this->config[$matches[1][$i]];
            }

            $template= str_replace($matches[0], $replace, $template);
        }
        return $template;
    } // mergeSettingsContent

    /**
     * Insert chunk content
     *
     * @param string $content
     * @return string
     */
    public function mergeChunkContent($content) {
        $replace= array ();
        $matches= array ();
        if (preg_match_all('~{{(.*?)}}~', $content, $matches)) {
            $total= count($matches[1]);
            for ($i= 0; $i < $total; $i++) {
                $name = $matches[1][$i];
                if (isset ($this->chunkCache[$name])) {
                    $replace[$i]= $this->chunkCache[$name];
                } else {
                    $escaped_name = $this->db->escape($name);
                    $where = "`name`='{$escaped_name}' AND `published`='1'";
                    $result= $this->db->select('snippet',$this->getFullTableName('site_htmlsnippets'),$where);
                    $limit= $this->db->getRecordCount($result);
                    if ($limit < 1) {
                        $this->chunkCache[$name]= '';
                        $replace[$i]= '';
                    } else {
                        $row= $this->db->getRow($result);
                        $this->chunkCache[$name]= $row['snippet'];
                        $replace[$i]= $row['snippet'];
                    }
                }
            }
            $content= str_replace($matches[0], $replace, $content);
        }
        return $content;
    } // mergeChunkContent
    
    /**
     * Added by Raymond
     *
     * @param string $content
     * @return string
     */
    public function mergePlaceholderContent($content) {
        $replace= array ();
        $matches= array ();
        if (preg_match_all('~\[\+(.*?)\+\]~', $content, $matches)) {
            $cnt= count($matches[1]);
            for ($i= 0; $i < $cnt; $i++) {
                $v= '';
                $key= $matches[1][$i];
                if (is_array($this->placeholders) && array_key_exists($key, $this->placeholders))
                    $v= $this->placeholders[$key];
                if ($v === '')
                    unset ($matches[0][$i]); // here we'll leave empty placeholders for last.
                else
                    $replace[$i]= $v;
            }
            $content= str_replace($matches[0], $replace, $content);
        }
        return $content;
    } // mergePlaceholderContent

    /**
     *
     * @param string $pluginCode
     * @param array $params
     */
    private function evalPlugin($pluginCode, $params) {
        $etomite= $modx= & $this;
        $modx->event->params= & $params; // store params inside event object
        if (is_array($params)) {
            extract($params, EXTR_SKIP);
        }
        ob_start();
        eval ($pluginCode);
        $msg= ob_get_contents();
        ob_end_clean();
        if ($msg && isset ($php_errormsg)) {
            if (!strpos($php_errormsg, 'Deprecated')) { // ignore php5 strict errors
                // log error
                $this->logEvent(1, 3, "<b>$php_errormsg</b><br /><br /> $msg", $this->Event->activePlugin . " - Plugin");
                if ($this->isBackend())
                    $this->Event->alert("An error occurred while loading. Please see the event log for more information.<p />$msg");
            }
        } else {
            echo $msg;
        }
        unset ($modx->event->params);
    } // evalPlugin

    /**
     *
     * @param string $snippet
     * @param array $params
     * @return string
     */
    private function evalSnippet($snippet, $params) {
        $etomite= $modx= & $this;
        if (isset($params) && is_array($params)) {
            foreach ($params as $k=>$v) {
                if ($v === 'false') {
                    $params[$k] = false;
                } elseif ($v === 'true') { 
                    $params[$k] = true;
                }
            }
        }
        $modx->event->params = $params; // store params inside event object
        if (is_array($params)) {
            extract($params, EXTR_SKIP);
        }
        ob_start();
        $result= eval($snippet);
        $msg= ob_get_contents();
        ob_end_clean();
        if ($msg && isset ($php_errormsg)) {
            if (strpos(strtolower($php_errormsg), 'deprecated') === false) {
                // ignore php5 strict errors
                // log error
                $request_uri = $_SERVER['REQUEST_URI'];
                $request_uri = 'REQUEST_URI = ' . htmlspecialchars($request_uri, ENT_QUOTES) . '<br />';
                $docid = "ID = {$this->documentIdentifier}<br />";
//                $bt = $this->get_backtrace(debug_backtrace()) . '<br />';
                $log = "<b>{$php_errormsg}</b><br />{$msg}<br />{$request_uri}{$docid}";
                $snip = $this->currentSnippet . ' - Snippet<br />';
                $this->logEvent(1, 3, $log,$snip);
                if ($this->isBackend()) {
                    $this->event->alert("An error occurred while loading. Please see the event log for more information<p>{$msg}</p>");
                }
            }
        }
        unset ($modx->event->params);
        return $msg . $result;
    } // evalSnippet
    
    /**
     *
     * @param string $documentSource
     * @return string
     */
    public function evalSnippets($documentSource) {
        $etomite= & $this;
        
        $stack = $documentSource;
        unset($documentSource);
        
        $passes = $this->minParserPasses;
        
        for ($i= 0; $i < $passes; $i++) {
            if ($i == ($passes -1)) {
                $bt = md5($stack);
            }
            $pieces = array();
            $pieces = explode('[[', $stack);
            $stack = '';
            $loop_count = 0;
            foreach ($pieces as $piece) {
                if ($loop_count < 1) {
                    $result = $piece;
                } elseif (strpos($piece,']]') === false) {
                    $result = '[[' . $piece;
                } else {
                    $result = $this->_get_snip_result($piece);
                }
                
                $stack .= $result;
                $loop_count++; // End of foreach loop
            }
            if ($i == ($passes -1) && $i < ($this->maxParserPasses - 1)) {
                if($bt != md5($stack)) { 
                    $passes++;
                }
            }
        }

        return $stack;
    } // evalSnippets
    
    /**
     * Create a friendly URL
     *
     * @param string $pre
     * @param string $suff
     * @param string $alias
     * @return string
     */
    public function makeFriendlyURL($pre, $suff, $path) {
        $elements = explode('/', $path);
        $alias = array_pop($elements);
        $dir = implode('/', $elements);
        unset($elements);
        if ((strpos($alias, '.') !== false)) {
            if(isset($this->config['suffix_mode']) && $this->config['suffix_mode']==1) {
                $suff = '';
            }
        }
        //container_suffix
        if (substr($alias, 0, 1) === '[' && substr($alias, -1) === ']') {
            $result = '[~' . $alias . '~]';
        } else {
            $result = ($dir !== '' ? $dir . '/' : '') . $pre . $alias . $suff;
        }
        
        return $result;
    } // makeFriendlyURL

    /**
     * Rewrite URL
     *
     * @param string $documentSource
     * @return string
     */
    public function rewriteUrls($documentSource) {
        // rewrite the urls
        $pieces = preg_split('/(\[~|~\])/', $documentSource);
        $maxidx = sizeof($pieces);
        $documentSource = '';
        if (empty($this->referenceListing)) {
            $this->referenceListing = array();
            $res = $this->db->select('id,content', $this->getFullTableName('site_content'), "type='reference'");
            $rows = $this->db->makeArray($res);
            foreach ($rows as $row) {
                extract($row);
                $this->referenceListing[$id] = $content;
            }
        }
        
        if ($this->config['friendly_urls'] == 1) {
            if (!isset($this->aliases) || empty($this->aliases)) {
                $aliases = $this->set_aliases();
            } else {
                $aliases = $this->aliases;
            }
            
            $use_alias = $this->config['friendly_alias_urls'];
            $prefix    = $this->config['friendly_url_prefix'];
            $suffix    = $this->config['friendly_url_suffix'];
            
            for ($idx = 0; $idx < $maxidx; $idx++) {
                $documentSource .= $pieces[$idx];
                $idx++;
                if ($idx < $maxidx) {
                    $target = trim($pieces[$idx]);
                    if (preg_match("/^[0-9]+$/",$this->referenceListing[$target])) {
                        $target = $this->referenceListing[$target];
                    } elseif (preg_match("/^[0-9]+$/",$target)) {
                        $target = $aliases[$target];
                    } else { 
                        $target = $this->parseDocumentSource($target);
                    }
                    
                    if (preg_match('@^https?://@', $this->referenceListing[$target])) {
                        $path = $this->referenceListing[$target];
                    }  elseif ($aliases[$target] && $use_alias) { 
                        $path = $this->makeFriendlyURL($prefix, $suffix, $aliases[$target]);
                    } else { 
                        $path = $this->makeFriendlyURL($prefix, $suffix, $target);
                    }
                    $documentSource .= $path;
                }
            }
            unset($aliases);
        } else {
            for ($idx = 0; $idx < $maxidx; $idx++) {
                $documentSource .= $pieces[$idx];
                $idx++;
                if ($idx < $maxidx) {
                    $target = trim($pieces[$idx]);
                    if (isset($this->referenceListing[$target]) 
                        && preg_match("/^[0-9]+$/",$this->referenceListing[$target])) {
                        $target = $this->referenceListing[$target];
                    }
                    
                    if ($target === $this->config['site_start']) {
                        $path = 'index.php';
                    } elseif(isset($this->referenceListing[$target]) 
                        && preg_match('@^https?://@', $this->referenceListing[$target])) {
                        $path = $this->referenceListing[$target];
                    } else {
                        $path = 'index.php?id=' . $target;
                    }
                    $documentSource .= $path;
                }
            }
        }
        return $documentSource;
    } // rewriteUrls

    /**
     * name: getDocumentObject  - used by parser
     * desc: returns a document object - $method: alias, id
     *
     * @param type $method
     * @param type $identifier
     * @return array
     */
    public function getDocumentObject($method, $identifier) {
        $tblsc= $this->getFullTableName("site_content");
        $tbldg= $this->getFullTableName("document_groups");

        // allow alias to be full path
        if($method == self::PAGE_BY_ALIAS) {
            $identifier = $this->cleanDocumentIdentifier($identifier);
            $method = $this->documentMethod;
        }
        if($method == self::PAGE_BY_ALIAS && $this->config['use_alias_path'] && array_key_exists($identifier, $this->documentListing)) {
            $method = self::PAGE_BY_ID;
            $identifier = $this->documentListing[$identifier];
        }
        // get document groups for current user
        if ($docgrp= $this->getUserDocGroups())
            $docgrp= implode(",", $docgrp);
        // get document
        $access= ($this->isFrontend() ? "sc.privateweb=0" : "1='" . $_SESSION['mgrRole'] . "' OR sc.privatemgr=0") .
         (!$docgrp ? "" : " OR dg.document_group IN ($docgrp)");
        $sql= "SELECT sc.*
              FROM $tblsc sc
              LEFT JOIN $tbldg dg ON dg.document = sc.id
              WHERE sc." . $method . " = '" . $identifier . "'
              AND ($access) LIMIT 1;";
        $result= $this->db->query($sql);
        $rowCount= $this->db->getRecordCount($result);
        if ($rowCount < 1) {
            if ($this->config['unauthorized_page']) {
                // method may still be alias, while identifier is not full path alias, e.g. id not found above
                if ($method === self::PAGE_BY_ALIAS) {
                    $q = "SELECT dg.id FROM $tbldg dg, $tblsc sc WHERE dg.document = sc.id AND sc.alias = '{$identifier}' LIMIT 1;";
                } else {
                    $q = "SELECT id FROM $tbldg WHERE document = '{$identifier}' LIMIT 1;";
                }
                // check if file is not public
                $secrs= $this->db->query($q);
                if ($secrs)
                    $seclimit= mysql_num_rows($secrs);
            }
            if ($seclimit > 0) {
                // match found but not publicly accessible, send the visitor to the unauthorized_page
                $this->sendUnauthorizedPage();
                exit; // stop here
            } else {
                $this->sendErrorPage();
                exit;
            }
        }

        # this is now the document :) #
        $documentObject= $this->db->getRow($result);

        // load TVs and merge with document - Orig by Apodigm - Docvars
        $sql= "SELECT tv.*, IF(tvc.value!='',tvc.value,tv.default_text) as value ";
        $sql .= "FROM " . $this->getFullTableName("site_tmplvars") . " tv ";
        $sql .= "INNER JOIN " . $this->getFullTableName("site_tmplvar_templates")." tvtpl ON tvtpl.tmplvarid = tv.id ";
        $sql .= "LEFT JOIN " . $this->getFullTableName("site_tmplvar_contentvalues")." tvc ON tvc.tmplvarid=tv.id AND tvc.contentid = '" . $documentObject[self::PAGE_BY_ID] . "' ";
        $sql .= "WHERE tvtpl.templateid = '" . $documentObject['template'] . "'";
        $rs= $this->db->query($sql);
        $rowCount= $this->db->getRecordCount($rs);
        if ($rowCount > 0) {
            for ($i= 0; $i < $rowCount; $i++) {
                $row= $this->db->getRow($rs);
                $tmplvars[$row['name']]= array (
                    $row['name'],
                    $row['value'],
                    $row['display'],
                    $row['display_params'],
                    $row['type']
                );
            }
            $documentObject= array_merge($documentObject, $tmplvars);
        }
        return $documentObject;
    } // getDocumentObject

    /**
     * name: parseDocumentSource - used by parser
     * desc: return document source aftering parsing tvs, snippets, chunks, etc.
     *
     * @param string $source
     * @return string
     */
    public function parseDocumentSource($source) {
        $passes= $this->minParserPasses;
        for ($i= 0; $i < $passes; $i++) {
            // get source length if this is the final pass
            if ($i == ($passes -1)) { 
                $bt= md5($source);
            }
            if ($this->dumpSnippets == 1) {
                $this->snipCode .= "<fieldset><legend><b style='color: #821517;'>PARSE PASS " . ($i +1) . "</b></legend>The following snippets (if any) were parsed during this pass.<div style='width:100%' align='center'>";
            }
            
            // invoke OnParseDocument event
            $this->documentOutput= $source; // store source code so plugins can
            $this->invokeEvent('OnParseDocument'); // work on it via $modx->documentOutput
            $source= $this->documentOutput;
            
            if (strpos($source, '<!-- #modx') !== false) {
                $source= $this->mergeCommentedTagsContent($source);
            }
            // combine template and document variables
            if (strpos($source, '[*') !== false) {
                $source= $this->mergeDocumentContent($source);
            }
            // replace settings referenced in document
            if (strpos($source, '[(') !== false) {
                $source= $this->mergeSettingsContent($source);
            }
            // replace HTMLSnippets in document
            if (strpos($source, '{{') !== false) {
                $source= $this->mergeChunkContent($source);
            }
            // insert META tags & keywords
            $source= $this->mergeDocumentMETATags($source);
            // find and merge snippets
            if (strpos($source, '[[') !== false) {
                $source= $this->evalSnippets($source);
            }
            // find and replace Placeholders (must be parsed last) - Added by Raymond
            if (strpos($source, '[+') !== false) {
                $source= $this->mergePlaceholderContent($source);
            }
            if ($this->dumpSnippets == 1) {
                $this->snipCode .= '</div></fieldset>';
            }
            if ($i == ($passes -1) && $i < ($this->maxParserPasses - 1)) {
                // check if source length was changed
                if ($bt != md5($source)) {
                    $passes++; // if content change then increase passes because
                }
            } // we have not yet reached maxParserPasses
            if (strpos($source, '[~') !== false) {
                $source = $this->rewriteUrls($source);
            }
        }
        return $source;
    } // parseDocumentSource
    
    /**
     * Checks the PHP version and starts the output of the document
     */
    function executeParser() {
        ob_start();
        //error_reporting(0);
        if (version_compare(phpversion(), '5.0.0', '>=')) {
            set_error_handler(array(& $this,'phpError'), E_ALL);
        } else {
            set_error_handler(array(& $this,'phpError'));
        }
        
        if (!empty($_SERVER['QUERY_STRING'])) {
            $qs = $_GET;
            if ($qs['id']) {
                unset($qs['id']);
            }
            if (0 < count($qs)) {
                $this->qs_hash = '_' . md5(join('&',$qs));
            } else {
                $this->qs_hash = '';
            }
        }
        
        // get the settings
        $this->db->connect();
        $this->getSettings();
        
        if (0 < count($_POST)) {
            $this->config['cache_type'] = 0;
        }
        
        $this->documentOutput = $this->get_static_pages();
        if (!empty($this->documentOutput)) {
            $this->documentOutput = $this->parseDocumentSource($this->documentOutput);
            $this->invokeEvent('OnWebPagePrerender');
            echo $this->documentOutput;
            $this->invokeEvent('OnWebPageComplete');
            exit;
        }
        
        // IIS friendly url fix
        if ($this->config['friendly_urls'] == 1 
            && strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') !== false) {
            $url= $_SERVER['QUERY_STRING'];
            $err= substr($url, 0, 3);
            if ($err == '404' || $err == '405') {
                $k= array_keys($_GET);
                unset ($_GET[$k[0]]);
                unset ($_REQUEST[$k[0]]); // remove 404,405 entry
                $_SERVER['QUERY_STRING']= $qp['query'];
                $qp= parse_url(str_replace($this->config['site_url'], '', substr($url, 4)));
                if (!empty ($qp['query'])) {
                    parse_str($qp['query'], $qv);
                    foreach ($qv as $n => $v) {
                        $_REQUEST[$n]= $_GET[$n]= $v;
                    }
                }
                $_SERVER['PHP_SELF']= $this->config['base_url'] . $qp['path'];
                $_REQUEST['q']= $_GET['q']= $qp['path'];
            }
        }
        
        // check site settings
        if (!$this->checkSiteStatus()) {
            header('HTTP/1.0 503 Service Unavailable');
            if (!$this->config['site_unavailable_page']) {
                // display offline message
                $this->documentContent= $this->config['site_unavailable_message'];
                $result = $this->outputContent();
                echo $result;
                exit; // stop processing here, as the site's offline
            } else {
                // setup offline page document settings
                $this->documentMethod= self::PAGE_BY_ID;
                $this->documentIdentifier= $this->config['site_unavailable_page'];
            }
        } else {
            // make sure the cache doesn't need updating
            $this->checkPublishStatus();
            
            // find out which document we need to display
            $this->documentMethod = $this->getDocumentMethod();
            $this->documentIdentifier = $this->getDocumentIdentifier($this->documentMethod);
        }
        
        if ($this->documentMethod == 'none' || $_SERVER['REQUEST_URI']===$this->config['base_url']){
            $this->documentMethod = self::PAGE_BY_ID; // now we know the site_start, change the none method to id
            $this->documentIdentifier = $this->config['site_start'];
        } elseif ($this->documentMethod == self::PAGE_BY_ALIAS) {
            $this->documentIdentifier = $this->cleanDocumentIdentifier($this->documentIdentifier);
        }
        
        if ($this->documentMethod == 'alias') {
            // Check use_alias_path and check if $this->virtualDir is set to anything, then parse the path
            if ($this->config['use_alias_path'] == 1) {
                $alias= (strlen($this->virtualDir) > 0 ? $this->virtualDir . '/' : '') . $this->documentIdentifier;
                if (isset($this->documentListing[$alias])) {
                    $this->documentIdentifier= $this->documentListing[$alias];
                } else {
                    $this->sendErrorPage();
                }
            } else {
                $this->documentIdentifier= $this->documentListing[$this->documentIdentifier];
            }
            $this->documentMethod= 'id';
        }
        
        // invoke OnWebPageInit event
        $this->invokeEvent('OnWebPageInit');
        
        $result = $this->prepareResponse();
        return $result;
    } // executeParser
    
    /**
     * Prepare the document response
     */
    private function prepareResponse() {
        // we now know the method and identifier, let's check the cache
        $this->documentContent= $this->checkCache($this->documentIdentifier);
        if ($this->documentContent != "") {
            // invoke OnLoadWebPageCache  event
            $this->invokeEvent("OnLoadWebPageCache");
        } else {
            // get document object
            $this->documentObject= $this->getDocumentObject($this->documentMethod, $this->documentIdentifier);
            
            // validation routines
            if ($this->documentObject['deleted'] == 1) {
                $this->sendErrorPage();
            }
            //  && !$this->checkPreview()
            if ($this->documentObject['published'] == 0) {
            // Can't view unpublished pages
                if (!$this->hasPermission('view_unpublished')) {
                    $this->sendErrorPage();
                } else {
                    // Inculde the necessary files to check document permissions
                    include_once ($this->config['base_path'] . 'manager/processors/user_documents_permissions.class.php');
                    $udperms= new udperms();
                    $udperms->user= $this->getLoginUserID();
                    $udperms->document= $this->documentIdentifier;
                    $udperms->role= $_SESSION['mgrRole'];
                    // Doesn't have access to this document
                    if (!$udperms->checkPermissions()) {
                        $this->sendErrorPage();
                    }
                }
            }
            // check whether it's a reference
            if ($this->documentObject['type'] == 'reference') {
                if (is_numeric($this->documentObject['content'])) {
                    // if it's a bare document id
                    $this->documentObject['content']= $this->makeUrl($this->documentObject['content']);
                } elseif (strpos($this->documentObject['content'], '[~') !== false) {
                    // if it's an internal docid tag, process it
                    $this->documentObject['content']= $this->rewriteUrls($this->documentObject['content']);
                }
                $this->sendRedirect($this->documentObject['content'], 0, '', 'HTTP/1.0 301 Moved Permanently');
            }
            // check if we should not hit this document
            if ($this->documentObject['donthit'] == 1) {
                $this->config['track_visitors']= 0;
            }
            // get the template and start parsing!
            if (!$this->documentObject['template']) {
                $this->documentContent= '[*content*]'; // use blank template
            } else {
                $tbl_site_templates = $this->getFullTableName('site_templates');
                $result= $this->db->select('content', $tbl_site_templates, "id = '{$this->documentObject['template']}'");
                $rowCount= $this->db->getRecordCount($result);
                if ($rowCount > 1) {
                    $this->messageQuit('Incorrect number of templates returned from database', $sql);
                } elseif ($rowCount == 1) {
                    $row= $this->db->getRow($result);
                    $this->documentContent= $row['content'];
                }
            }
            // invoke OnLoadWebDocument event
            $this->invokeEvent('OnLoadWebDocument');
            
            // Parse document source
            $this->documentContent= $this->parseDocumentSource($this->documentContent);
        }
        register_shutdown_function(array (
            & $this,
            'postProcess'
        )); // tell PHP to call postProcess when it shuts down
        $result = $this->outputContent();
        return $result;
    } // evalSnippets
    
    /**
     * Returns false when the file does not exist, otherwise it writes the file
     * content into the output buffer
     *
     * @return boolean 
     */
    private function get_static_pages() {
        $filepath = $_SERVER['REQUEST_URI'];
        if (strpos($filepath,'?')!==false) {
            $filepath = substr($filepath, 0, strpos($filepath, '?'));
        }
        $filepath = substr($filepath, strlen($this->config['base_url']));
        if (substr($filepath, -1) === '/' || empty($filepath)) {
            $filepath .= 'index.html';
        }
        $filepath = $this->config['base_path'] . 'assets/public_html/' . $filepath;
        if (file_exists($filepath) !== false) {
            $ext = strtolower(substr($filepath,strrpos($filepath,'.')));
            switch($ext) {
                case '.html':
                case '.htm':
                    $mime_type = 'text/html';
                    
                    break;
                case '.xml':
                case '.rdf':
                    $mime_type ='text/xml'; 
                    
                    break;
                case '.css':
                    $mime_type = 'text/css'; 
                    
                    break;
                case '.js':
                    $mime_type = 'text/javascript'; 
                    
                    break;
                case '.txt':
                    $mime_type = 'text/plain'; 
                    
                    break;
                case '.ico':
                case '.jpg':
                case '.jpeg':
                case '.png':
                case '.gif':
                    if ($ext==='.ico') {
                        $mime_type = 'image/x-icon';
                    } else {
                        $info = getImageSize($filepath);
                        $mime_type = $info['mime'];
                    }
                    header("Content-type: {$mime_type}");
                    readfile($filepath);
                    exit;

                default:
                    exit;
            }
            header("Content-type: {$mime_type}");
            $src = file_get_contents($filepath);
        } else {
            $src = false;
        }
        
        return $src;
    } // get_static_pages
    
    /**
     *
     * @param array $content
     * @return string 
     */
    private function mergeCommentedTagsContent($content) {
        $pieces = explode('<!-- #modx', $content);
        $stack = '';
        $total = count($pieces);
        for ($i = 0; $i < $total; $i++) {
            $_ = $pieces[$i];
            if ($i !== 0) {
                list($modxelm, $txt) = explode('-->',$_, 2);
                $modxelm = trim($modxelm);
                $txt = substr($txt, strpos($txt, '<!-- /#modx'));
                $txt = substr($txt, strpos($txt, '-->')+3);
                $_ = $modxelm . $txt;
            }
            $stack .= $_;
        }
        return $stack;
    } // mergeCommentedTagsContent
    
    /**
     *
     * @param string $content
     * @return string
     */
    private function mergeBenchmarkContent($content) {
        $totalTime= ($this->getMicroTime() - $this->tstart);
        $queryTime= $this->queryTime;
        $phpTime= $totalTime - $queryTime;
        
        $queryTime= sprintf("%2.4f s", $queryTime);
        $totalTime= sprintf("%2.4f s", $totalTime);
        $phpTime= sprintf("%2.4f s", $phpTime);
        $source= ($this->documentGenerated == 1 || $this->config['cache_type'] ==0) ? 'database' : 'full_cache';
        $queries= isset ($this->executedQueries) ? $this->executedQueries : 0;
        $mem = (function_exists('memory_get_peak_usage')) ? memory_get_peak_usage()  : memory_get_usage();
        $total_mem = $this->nicesize($mem - $this->mstart);
        
        $content= str_replace('[^q^]', $queries, $content);
        $content= str_replace('[^qt^]', $queryTime, $content);
        $content= str_replace('[^p^]', $phpTime, $content);
        $content= str_replace('[^t^]', $totalTime, $content);
        $content= str_replace('[^s^]', $source, $content);
        $content= str_replace('[^m^]', $total_mem, $content);
        
        return $content;
    } // mergeBenchmarkContent
    
    /**
     *
     * @param type $backtrace
     * @return string 
     */
    private function get_backtrace($backtrace) {
        $result  = '<table>'
              . '<tr align="center"><td>#</td><td>call</td><td>path</td></tr>';
        foreach ($backtrace as $key => $val) {
            $key++;
            $path = str_replace('\\', '/', $val['file']);
            if (strpos($path, MODX_BASE_PATH) === 0) {
                $path = substr($path, strlen(MODX_BASE_PATH));
            }
            $result .= "<tr><td>{$key}</td>"
                    . "<td>{$val['function']}()</td>"
                    . "<td>{$path} on line {$val['line']}</td></tr>";
        }
        $result .= '</table>';

        return $result;
    } // get_backtrace

    /**
     *
     * @param string $piece
     * @return string
     */
    private function _get_snip_result($piece) {
        $snip_call = $this->_split_snip_call($piece);
        $snip_name = $snip_call['name'];
        $except_snip_call = $snip_call['except_snip_call'];
        
        $snippetObject = $this->_get_snip_properties($snip_call);
        
        $params   = array ();
        $this->currentSnippet = $snippetObject['name'];
        
        if (isset($snippetObject['properties'])) {
            $params = $this->parseProperties($snippetObject['properties']);
        } else {
            $params = '';
        }
        // current params
        if (!empty($snip_call['params'])) {
            $snip_call['params'] = ltrim($snip_call['params'], '?');
            
            $i = 0;
            $limit = 50;
            $params_stack = $snip_call['params'];
            while (!empty($params_stack) && $i < $limit) {
                list($pname, $params_stack) = explode('=', $params_stack, 2);
                $params_stack = trim($params_stack);
                $delim = substr($params_stack, 0, 1);
                $temp_params = array();
                switch($delim) {
                    case '`':
                    case '"':
                    case "'":
                        $params_stack = substr($params_stack,1);
                        list($pvalue, $params_stack) = explode($delim, $params_stack, 2);
                        $params_stack = trim($params_stack);
                        if (substr($params_stack, 0, 2) === '//') {
                            $params_stack = strstr($params_stack, "\n");
                        }

                        break;

                    default:
                        if (strpos($params_stack, '&') !== false) {
                            list($pvalue, $params_stack) = explode('&', $params_stack, 2);
                        } else {
                            $pvalue = $params_stack;
                        }
                        $pvalue = trim($pvalue);
                }
                if ($delim !== "'") {
                    $pvalue = (strpos($pvalue, '[*') !== false) ? $this->mergeDocumentContent($pvalue) : $pvalue;
                }
                
                $pname  = str_replace('&amp;', '', $pname);
                $pname  = trim($pname);
                $pname  = trim($pname, '&');
                $params[$pname] = $pvalue;
                $params_stack = trim($params_stack);
                if($params_stack!=='') {
                    $params_stack = '&' . ltrim($params_stack, '&');
                }
                $i++;
            }
            unset($temp_params);
        }
        $executedSnippets = $this->evalSnippet($snippetObject['content'], $params);
        if ($this->dumpSnippets == 1) {
            $this->snipCode .= '<fieldset><legend><b>' . $snippetObject['name'] . '</b></legend><textarea style="width:60%;height:200px">' . htmlentities($executedSnippets,ENT_NOQUOTES,$this->config['modx_charset']) . '</textarea></fieldset>';
        }
        return $executedSnippets . $except_snip_call;
    } // _get_snip_result
    
    /**
     *
     * @param string $src
     * @return string 
     */
    private function _split_snip_call($src) {
        list($call, $snip['except_snip_call']) = explode(']]', $src, 2);
        if (strpos($call, '?') !== false && strpos($call, "\n") !== false 
                && strpos($call, '?') < strpos($call, "\n")) {
            list($name, $params) = explode('?', $call, 2);
        } elseif (strpos($call, '?') !== false && strpos($call, "\n") !== false 
                && strpos($call, "\n") < strpos($call, '?')) {
            list($name, $params) = explode("\n", $call, 2);
        } elseif(strpos($call, '?') !== false) {
            list($name, $params) = explode('?', $call, 2);
        } elseif((strpos($call, '&') !== false) && (strpos($call, '=') !== false) 
                && (strpos($call, '?') === false)) {
            list($name, $params) = explode('&', $call, 2);
            $params = "&{$params}";
        } elseif(strpos($call, "\n") !== false) {
            list($name, $params) = explode("\n", $call, 2);
        } else {
            $name = $call;
            $params = '';
        }
        $snip['name'] = trim($name);
        $snip['params'] = $params;

        return $snip;
    } // _split_snip_call
    
    /**
     *
     * @param array $snip_call
     * @return string 
     */
    private function _get_snip_properties($snip_call) {
        $snip_name  = $snip_call['name'];
        $snippetObject = array();
        
        if (isset($this->snippetCache[$snip_name])) {
            $snippetObject['name'] = $snip_name;
            $snippetObject['content'] = $this->snippetCache[$snip_name];
            if (isset($this->snippetCache[$snip_name . 'Props'])) {
                $snippetObject['properties'] = $this->snippetCache[$snip_name . 'Props'];
            }
        } else {
            $tbl_snippets  = $this->getFullTableName('site_snippets');
            $esc_snip_name = $this->db->escape($snip_name);
            // get from db and store a copy inside cache
            $result= $this->db->select('name,snippet,properties', $tbl_snippets, "name='{$esc_snip_name}'");
            $added = false;
            if ($this->db->getRecordCount($result) == 1) {
                $row = $this->db->getRow($result);
                if ($row['name'] == $snip_name) {
                    $snippetObject['name']       = $row['name'];
                    $snippetObject['content']    = $this->snippetCache[$snip_name]           = $row['snippet'];
                    $snippetObject['properties'] = $this->snippetCache[$snip_name . 'Props'] = $row['properties'];
                    $added = true;
                }
            }
            if ($added === false) {
                $snippetObject['name']       = $snip_name;
                $snippetObject['content']    = $this->snippetCache[$snip_name] = 'return false;';
                $snippetObject['properties'] = '';
            }
        }

        return $snippetObject;
    } // _get_snip_properties
    
    /**
     * Sets the aliases array
     *
     * @return array
     */
    private function set_aliases() {
        $path_aliases = MODX_BASE_PATH . 'assets/cache/aliases.pageCache.php';
        if (file_exists($path_aliases)) {
            $src = file_get_contents($path_aliases);
            $this->aliases = unserialize($src);
        } else {
            $aliases = array ();
            foreach ($this->aliasListing as $doc) {
                $aliases[$doc['id']]= (strlen($doc['path']) > 0 ? $doc['path'] . '/' : '') . $doc['alias'];
            }
            file_put_contents($path_aliases, serialize($aliases));
            $this->aliases = $aliases;
        }
        return $this->aliases;
    } // set_aliases

    /***************************************************************************************/
    /* API functions                                                                /
    /***************************************************************************************/

    /**
     * Returns an array of all parent record IDs for the id passed.
     *
     * @category API-Function
     * @param int $id Resource ID to get parents for
     * @param int $height Argument defines the maximum number of levels to go up.
     *                    Default: 10
     * @return array
     * @example $parents = $modx->getParentIds(10);
     */
    public function getParentIds($id, $height=10) {
        $parents= array ();
        while ( $id && $height-- ) {
            $thisid = $id;
            $id = $this->aliasListing[$id]['parent'];
            if (!$id) 
                break;
            $pkey = strlen($this->aliasListing[$thisid]['path']) ? $this->aliasListing[$thisid]['path'] : $this->aliasListing[$id]['alias'];
            if (!strlen($pkey)) {
                $pkey = "{$id}";
            }
            $parents[$pkey] = $id;
        }
        return $parents;
    } // getParentIds

    /**
     * Loads the content of the documentmap.pageCache.php into the class 
     * variable documentMap_cache
     *
     * @category API-Function
     * @return string
     * @example $modx->set_documentMap_cache();
     */
    public function set_documentMap_cache() {
        $path_documentmapcache = MODX_BASE_PATH . 'assets/cache/documentmap.pageCache.php';
        if (file_exists($path_documentmapcache)) {
            $src = file_get_contents($path_documentmapcache);
            $this->documentMap_cache = unserialize($src);
        } else {
            $documentMap_cache= array ();
            foreach ($this->documentMap as $document) {
                foreach ($document as $p => $c) {
                    $documentMap_cache[$p][] = $c;
                }
            }
            file_put_contents($path_documentmapcache, serialize($documentMap_cache));
            $this->documentMap_cache = $documentMap_cache;
        }

        return $this->documentMap_cache;
    } // set_documentMap_cache

    /**
     * @category API-Function
     * @staticvar array $documentMap_cache
     * @param int $id The parent page to start from
     * @param int $depth How many levels deep to search for children
     *                   Default: 10
     * @param array $children If you already have an array of child ids, give it
     *                        to the method, and new values will be added
     *                        Default: Empty array
     * @return array Contains the document Listing (tree) like the sitemap
     */
    public function getChildIds($id, $depth=10, $children=array()) {
        // Initialise a static array to index parents->children
        if (!count($this->documentMap_cache)) {
            $documentMap_cache = $this->set_documentMap_cache();
        } else {
            $documentMap_cache = $this->documentMap_cache;
        }
        
        // Get all the children for this parent node
        if (isset($documentMap_cache[$id])) {
            $depth--;
            
            foreach ($documentMap_cache[$id] as $childId) {
                $pkey = $this->aliasListing[$childId]['alias'];
                if (strlen($this->aliasListing[$childId]['path'])) {
                    $pkey = "{$this->aliasListing[$childId]['path']}/{$pkey}";
                }
                
                if (!strlen($pkey)) {
                    $pkey = $childId;
                }
                $children[$pkey] = $childId;
                
                if ($depth) {
                    $children += $this->getChildIds($childId, $depth);
                }
            }
        }

        return $children;
    } // getChildIds

    /**
     * Displays a javascript alert message in the web browser
     *
     * @category API-Function
     * @param string $msg Message to show
     * @param string $url URL that is linked to
     *                    Default: Empty string
     * @example $modx->webAlert("hello world", "http://www.google.com"]);
     */
    public function webAlert($msg, $url='') {
        $msg= addslashes($this->db->escape($msg));
        if (substr(strtolower($url), 0, 11) == "javascript:") {
            $act= "__WebAlert();";
            $fnc= "function __WebAlert(){" . substr($url, 11) . "};";
        } else {
            $act= ($url ? "window.location.href='" . addslashes($url) . "';" : "");
        }
        $html= "<script>$fnc window.setTimeout(\"alert('$msg');$act\",100);</script>";
        if ($this->isFrontend())
            $this->regClientScript($html);
        else {
            echo $html;
        }
    } // webAlert

    /**
     * Returns true if user has the currect permission
     *
     * @category API-Function
     * @param string $pm Name of the permission
     * @return int
     * @example $fileAccess = $modx->hasPermission('file_manager');
     */
    public function hasPermission($pm) {
        $state= false;
        $pms= $_SESSION['mgrPermissions'];
        if ($pms)
            $state= ($pms[$pm] == 1);
        return $state;
    } // hasPermission

    /**
     * Add an a alert message to the system event log
     *
     * @category API-Function
     * @param int $evtid Event ID
     * @param int $type Types: 1 = information, 2 = warning, 3 = error
     * @param string $msg Message to be logged
     * @param string $source source of the event (module, snippet name, etc.)
     *                       Default: Parser
     * @example $modx->logEvent($my_msg_cnt, 3, 'There was an error!', 'MySnippet');
     */
    public function logEvent($evtid, $type, $msg, $source='Parser') {
        $evtid= intval($evtid);
        if ($type < 1) {
            // Types: 1 = information, 2 = warning, 3 = error
            $type= 1; 
        }
        if (3 < $type) {
            $type= 3;
        }
        $msg= $this->db->escape($msg);
        $source= $this->db->escape($source);
        if (function_exists('mb_substr')) {
            $source = mb_substr($source, 0, 50 , $this->config['modx_charset']);
        } else {
            $source = substr($source, 0, 50);
        }
        $LoginUserID = $this->getLoginUserID();
        if ($LoginUserID == '' || $LoginUserID===false) {
            $LoginUserID = '-';
        }
        
        $fields['eventid']     = $evtid;
        $fields['type']        = $type;
        $fields['createdon']   = time();
        $fields['source']      = $source;
        $fields['description'] = $msg;
        $fields['user']        = $LoginUserID;
        $insert_id = $this->db->insert($fields,$this->getFullTableName('event_log'));
        if(!$this->db->conn) $source = 'DB connect error';
        if (isset($this->config['send_errormail']) && $this->config['send_errormail'] !== '0') {
            if ($this->config['send_errormail'] <= $type) {
                $subject = 'Notice of error from ' . $this->config['site_name'];
                $this->sendmail($subject,$source);
            }
        }
        if (!$insert_id) {
            echo 'Error while inserting event log into database.';
            exit();
        } else {
            $trim  = (isset($this->config['event_log_trim']))  ? intval($this->config['event_log_trim']) : 100;
            if (($insert_id % $trim) == 0) {
                $limit = (isset($this->config['event_log_limit'])) ? intval($this->config['event_log_limit']) : 2000;
                $this->rotate_log('event_log', $limit, $trim);
            }
        }
    } // logEvent
    
    /**
     * Determines if the page being parsed is from the Web (frontend) or manager
     * (backend) view. Returns true if accessed from the manager interface.
     * false otherwise.
     *
     * @category API-Function
     * @return boolean
     * @example $isBackend = $modx->isBackend();
     */
    public function isBackend() {
        return $this->insideManager() ? true : false;
    } // isBackend

    /**
     * Determines if the page being parsed is from the Web (frontend) or manager
     * (backend) view. Returns true if accessed from the web interface. false
     * otherwise.
     *
     * @category API-Function
     * @return boolean
     */
    public function isFrontend() {
        return !$this->insideManager() ? true : false;
    } // isFrontend

    /**
     * Gets all child documents of the specified document, including those which
     * are unpublished or deleted.
     *
     * @category API-Function
     * @param int $id The Document identifier to start with
     * @param string $sort Sort field
     *                     Default: menuindex
     * @param string $dir Sort direction, ASC and DESC is possible
     *                    Default: ASC
     * @param string $fields Default: id, pagetitle, description, parent, alias, menutitle
     * @return array
     * @example $allChildren = $modx->getAllChildren(10);
     * @todo Change the row handling
     */
    public function getAllChildren($id=0, $sort='menuindex', $dir='ASC', $fields='id, pagetitle, description, parent, alias, menutitle') {
        $tbl_site_content= $this->getFullTableName('site_content');
        $tbl_document_groups= $this->getFullTableName('document_groups');
        // modify field names to use sc. table reference
        $fields= 'sc.' . implode(',sc.', preg_replace("/^\s/i", '', explode(',', $fields)));
        $sort= 'sc.' . implode(',sc.', preg_replace("/^\s/i", '', explode(',', $sort)));
        // get document groups for current user
        if ($docgrp= $this->getUserDocGroups()) {
            $docgrp= implode(',', $docgrp);
        }
        // build query
        $context = ($this->isFrontend() ? 'web' : 'mgr');
        $cond    = ($docgrp) ? "OR dg.document_group IN ({$docgrp}) OR 1='{$_SESSION['mgrRole']}'" : '';
        $from = "{$tbl_site_content} sc LEFT JOIN {$tbl_document_groups} dg on dg.document = sc.id";
        $where = "sc.parent = '{$id}' AND (sc.private{$context}=0 {$cond}) GROUP BY sc.id";
        $orderby = "{$sort} {$dir}";
        $result= $this->db->select("DISTINCT {$fields}", $from, $where, $orderby);
        $resourceArray= array ();
        for ($i= 0; $i < $this->db->getRecordCount($result); $i++) {
            $resourceArray[] = $this->db->getRow($result);
        }
        return $resourceArray;
    } // getAllChildren
    
    /**
     * Gets all active child documents of the specified document, those which
     * are unpublished or deleted are not included.
     *
     * @category API-Function
     * @param int $id The Document identifier to start with
     * @param string $sort Sort field
     *                     Default: menuindex
     * @param string $dir Sort direction, ASC and DESC is possible
     *                    Default: ASC
     * @param string $fields Default: id, pagetitle, description, parent, alias, menutitle
     * @return array
     * @example $allChildren = $modx->getActiveChildren(10);
     * @todo Change the row handling
     */
    public function getActiveChildren($id=0, $sort='menuindex', $dir='ASC', $fields='id, pagetitle, description, parent, alias, menutitle') {
        $tbl_site_content    = $this->getFullTableName('site_content');
        $tbl_document_groups = $this->getFullTableName('document_groups');
        
        // modify field names to use sc. table reference
        $fields= 'sc.' . implode(',sc.', preg_replace("/^\s/i", '', explode(',', $fields)));
        $sort= 'sc.' . implode(',sc.', preg_replace("/^\s/i", '', explode(',', $sort)));
        // get document groups for current user
        if ($docgrp= $this->getUserDocGroups()) {
            $docgrp= implode(",", $docgrp);
        }
        // build query
        if ($this->isFrontend()) {
            $context = 'sc.privateweb=0';
        } else {
            $context = "1='{$_SESSION['mgrRole']}' OR sc.privatemgr=0";
        }
        $cond = ($docgrp) ? " OR dg.document_group IN ({$docgrp})" : '';
        
        $fields = "DISTINCT {$fields}";
        $from = "{$tbl_site_content} sc LEFT JOIN {$tbl_document_groups} dg on dg.document = sc.id";
        $where = "sc.parent = '{$id}' AND sc.published=1 AND sc.deleted=0 AND ({$context} {$cond}) GROUP BY sc.id";
        $orderby = "{$sort} {$dir}";
        $result= $this->db->select($fields, $from, $where, $orderby);
        $resourceArray= array ();
        for ($i = 0; $i < $this->db->getRecordCount($result); $i++) {
            $resourceArray[] = $this->db->getRow($result);
        }
        return $resourceArray;
    } // getActiveChildren
    
    /**
     * Returns the children of the selected document/folder.
     *
     * @category API-Function
     * @param int $parentid The parent document identifier
     *                      Default: 0
     * @param int $published Whether only published documents are in the result,
     *                       or all documents, 1 = yes, 0 = no
     *                       Default: 1
     * @param int $deleted Whether deleted documents are in the result, or not,
     *                     1 = yes, 0 = no
     *                     Default: 0
     * @param string $fields List of fields
     *                       Default: * (= all fields)
     * @param string $where Where condition in SQL style
     *                      Default: Empty string
     * @param type $sort Should be a comma-separated list of field names on
     *                   which to sort
     *                   Default: menuindex
     * @param string $dir Sort direction, ASC and DESC is possible
     *                    Default: ASC
     * @param string|int $limit Should be a valid argument to the SQL LIMIT
     *                          clause. Empty string is without limit.
     *                          Default: Empty string
     * @return array
     * @example $allDocChilds = $modx->getDocumentChildren(10);
     * @todo Change the row handling
     */
    public function getDocumentChildren($parentid=0, $published= 1, $deleted=0, $fields= "*", $where='', $sort="menuindex", $dir="ASC", $limit='') {
        $tbl_site_content= $this->getFullTableName('site_content');
        $tbl_document_groups= $this->getFullTableName('document_groups');
        // modify field names to use sc. table reference
        $fields = 'sc.' . implode(',sc.', preg_replace("/^\s/i", '', explode(',', $fields)));
        if ($where != '') {
            $where = "AND {$where}";
        }
        // get document groups for current user
        if ($docgrp= $this->getUserDocGroups()) {
            $docgrp= implode(',', $docgrp);
        }
        // build query
        $access  = $this->isFrontend() ? 'sc.privateweb=0' : "1='{$_SESSION['mgrRole']}' OR sc.privatemgr=0";
        $access .= !$docgrp ? '' : " OR dg.document_group IN ({$docgrp})";
        $from = "{$tbl_site_content} sc LEFT JOIN {$tbl_document_groups} dg on dg.document = sc.id";
        $where = "sc.parent = '{$parentid}' AND sc.published={$published} AND sc.deleted={$deleted} {$where} AND ({$access}) GROUP BY sc.id";
        $sort = ($sort != '') ? 'sc.' . implode(',sc.', preg_replace("/^\s/i", '', explode(',', $sort))) : '';
        $orderby = $sort ? "{$sort} {$dir}" : '';
        $result= $this->db->select("DISTINCT {$fields}", $from, $where, $orderby, $limit);
        $resourceArray= array ();
        for ($i = 0; $i < $this->db->getRecordCount($result); $i++) {
            $resourceArray[] = $this->db->getRow($result);
        }
        return $resourceArray;
    } // getDocumentChildren
    
    /**
     * Returns the documents, that are declared in the ids parameter
     *
     * @category API-Function
     * @param array $ids The parent document identifiers in an array
     *                   Default: Empty array
     * @param int $published Whether only published documents are in the result,
     *                       or all documents, 1 = yes, 0 = no
     *                       Default: 1
     * @param int $deleted Whether deleted documents are in the result, or not,
     *                     1 = yes, 0 = no
     *                     Default: 0
     * @param string $fields List of fields
     *                       Default: * (= all fields)
     * @param string $where Where condition in SQL style
     *                      Default: Empty string
     * @param type $sort Should be a comma-separated list of field names on
     *                   which to sort
     *                   Default: menuindex
     * @param string $dir Sort direction, ASC and DESC is possible
     *                    Default: ASC
     * @param int $limit Should be a valid argument to the SQL LIMIT clause.
     * @return array|boolean Result array with documents, or false
     * @example $docs = $modx->getDocuments(array(1, 4, 5));
     * @todo Change the row handling
     */
    public function getDocuments($ids=array (), $published=1, $deleted=0, $fields="*", $where='', $sort="menuindex", $dir="ASC", $limit='') {
        if (count($ids) == 0) {
            return false;
        } else {
            $tbl_site_content= $this->getFullTableName('site_content');
            $tbl_document_groups= $this->getFullTableName('document_groups');
            
            // modify field names to use sc. table reference
            $fields= 'sc.' . implode(',sc.', preg_replace("/^\s/i", '', explode(',', $fields)));
            if ($sort !== '') {
                $sort = 'sc.' . implode(',sc.', preg_replace("/^\s/i", '', explode(',', $sort)));
            }
            if ($where != '') {
                $where= "AND {$where}";
            }
            // get document groups for current user
            if ($docgrp= $this->getUserDocGroups()) {
                $docgrp= implode(',', $docgrp);
            }
            $context = ($this->isFrontend()) ? 'web' : 'mgr';
            $cond = $docgrp ? "OR dg.document_group IN ({$docgrp})" : '';
            
            $fields = "DISTINCT {$fields}";
            $from = "{$tbl_site_content} sc LEFT JOIN {$tbl_document_groups} dg on dg.document = sc.id";
            $ids_str = implode(',', $ids);
            $where = "(sc.id IN ({$ids_str}) AND sc.published={$published} AND sc.deleted={$deleted} {$where}) AND (sc.private{$context}=0 {$cond} OR 1='{$_SESSION['mgrRole']}') GROUP BY sc.id";
            $orderby = ($sort) ? "{$sort} {$dir}" : '';
            $result= $this->db->select($fields,$from,$where,$orderby,$limit);
            $resourceArray= array ();
            for ($i = 0; $i < $this->db->getRecordCount($result); $i++) {
                $resourceArray[] = $this->db->getRow($result);
            }
            return $resourceArray;
        }
    } // getDocuments

    /**
     * Returns the parsed content of the given document identifier.
     *
     * @category API-Function
     * @param int $id The Document identifier to start with
     *                Default: 0
     * @param string $fields List of fields
     *                       Default: * (= all fields)
     * @param int $published Whether only published documents are in the result,
     *                       or all documents, 1 = yes, 0 = no
     *                       Default: 1
     * @param int $deleted Whether deleted documents are in the result, or not,
     *                     1 = yes, 0 = no
     *                     Default: 0
     * @return boolean|string
     * @example $doc = $modx->getDocument(10);
     */
    public function getDocument($id= 0, $fields= '*', $published= 1, $deleted= 0) {
        if ($id == 0) {
            $result = false;
        } else {
            $tmpArr[]= $id;
            $docs= $this->getDocuments($tmpArr, $published, $deleted, $fields, '', '', '', 1);
            
            if ($docs != false) {
                $result = $docs[0];
            } else {
                $result = false;
            }
        }

        return $result;
    } // getDocument

    /**
     * Returns the page information as database row, the type of result is
     * defined with the parameter $rowMode
     *
     * @category API-Function
     * @param int $pageid The parent document identifier
     *                    Default: -1
     * @param int $active Whether the document is active, or not,
     *                     1 = yes, 0 = no
     *                     Default: 1
     * @param string $fields List of fields
     *                       Default: id, pagetitle, description, alias
     * @return boolean|array|stdClass
     */
    public function getPageInfo($docid= 0, $active= 1, $fields= 'id, pagetitle, description, alias') {
        if ($docid == 0) {
            $result = false;
        } else {
            $tbl_site_content    = $this->getFullTableName('site_content');
            $tbl_document_groups = $this->getFullTableName('document_groups');
            
            // modify field names to use sc. table reference
            $fields = preg_replace("/\s/i", '',$fields);
            $fields = 'sc.' . implode(',sc.', explode(',', $fields));
            
            $published = ($active == 1) ? 'AND sc.published=1 AND sc.deleted=0' : '';
            
            // get document groups for current user
            if ($docgrp= $this->getUserDocGroups()) {
                $docgrp= implode(',', $docgrp);
            }
            if ($this->isFrontend()) {
                $context = 'sc.privateweb=0';
            } else {
                $context = "1='{$_SESSION['mgrRole']}' OR sc.privatemgr=0";
            }
            $cond   =  ($docgrp) ? "OR dg.document_group IN ({$docgrp})" : '';
            
            $from = "{$tbl_site_content} sc LEFT JOIN {$tbl_document_groups} dg on dg.document = sc.id";
            $where = "(sc.id={$docid} {$published}) AND ({$context} {$cond})";
            $result = $this->db->select($fields,$from,$where,'',1);
            $pageInfo = $this->db->getRow($result);
            $result = $pageInfo;
        }

        return $result;
    } // getPageInfo

    /**
     * Returns the parent document of the given identifier.
     *
     * @category API-Function
     * @param int $pid The parent document identifier
     *                 Default: -1
     * @param int $active Whether the document is active, or not,
     *                     1 = yes, 0 = no
     *                     Default: 1
     * @param string $fields List of fields
     *                       Default: id, pagetitle, description, alias
     * @return boolean|array|stdClass
     * @example $parentDoc = $modx->getParent(10);
     */
    public function getParent($pid=-1, $active=1, $fields='id, pagetitle, description, alias, parent') {
        if ($pid == -1) {
            $pid= $this->documentObject['parent'];
            $result = ($pid == 0) ? false : $this->getPageInfo($pid, $active, $fields, $rowMode);
        } else {
            if ($pid == 0) {
                $result = false;
            } else {
                // first get the child document
                $child= $this->getPageInfo($pid, $active, "parent");
                // now return the child's parent
                $pid= ($child['parent']) ? $child['parent'] : 0;
                $result = ($pid == 0) ? false : $this->getPageInfo($pid, $active, $fields);
            }
        }

        return $result;
    } // getParent

    /**
     * Returns the identifier of the current snippet.
     *
     * @category API-Function
     * @return int
     * @example $snippetIdentifier = $modx->getSnippetId();
     */
    public function getSnippetId() {
        $result = 0;
        if ($this->currentSnippet) {
            $tbl= $this->getFullTableName("site_snippets");
            $rs= $this->db->query("SELECT id FROM $tbl WHERE name='" . $this->db->escape($this->currentSnippet) . "' LIMIT 1");
            $row= @ $this->db->getRow($rs);
            if ($row['id'])
                $result = $row['id'];
        }

        return $result;
    } // getSnippetId

    /**
     * Returns the name of the current snippet.
     *
     * @category API-Function
     * @return string
     * @example $snippetName = $modx->getSnippetName();
     */
    public function getSnippetName() {
        return $this->currentSnippet;
    } // getSnippetName

    /**
     * Clear the cache of MODX.
     *
     * @category API-Function
     * @param array $params Default: Empty array
     * @return boolean 
     * @example $cacheCleared = $modx->clearCache();
     */
    public function clearCache($params=array()) {
        if (opendir(MODX_BASE_PATH . 'assets/cache') !== false) {
            $showReport = ($params['showReport']) ? $params['showReport'] : false;
            $target = ($params['target']) ? $params['target'] : 'pagecache,sitecache';
            
            include_once MODX_MANAGER_PATH . 'processors/cache_sync.class.processor.php';
            $sync = new synccache();
            $sync->setCachepath(MODX_BASE_PATH . 'assets/cache/');
            $sync->setReport($showReport);
            $sync->setTarget($target);
            $sync->emptyCache(); // first empty the cache
            $result = true;
        } else {
            $result = false;
        }

        return $result;
    } // clearCache

    /**
     * Create an URL for the given document identifier. The url prefix and
     * postfix are used, when friendly_url is active.
     *
     * @category API-Function
     * @param int $id The document identifier
     * @param string $alias The alias name for the document
     *                      Default: Empty string
     * @param string $args The paramaters to add to the URL
     *                     Default: Empty string
     * @param string $scheme With full as valus, the site url configuration is
     *                       used
     *                       Default: Empty string
     * @return string
     * @example $url = $modx->makeUrl(10, 'test-page');
     */
    public function makeUrl($id, $alias='', $args='', $scheme='') {
        $url= '';
        $virtualDir= '';
        $f_url_prefix = $this->config['friendly_url_prefix'];
        $f_url_suffix = $this->config['friendly_url_suffix'];
        if (!is_numeric($id)) {
            $this->messageQuit('`' . $id . '` is not numeric and may not be passed to makeUrl()');
        }
        if ($args != '' && $this->config['friendly_urls'] == 1) {
            // add ? to $args if missing
            $c= substr($args, 0, 1);
            if (strpos($f_url_prefix, '?') === false) {
                if ($c == '&')
                    $args= '?' . substr($args, 1);
                elseif ($c != '?') $args= '?' . $args;
            } else {
                if ($c == '?')
                    $args= '&' . substr($args, 1);
                elseif ($c != '&') $args= '&' . $args;
            }
        } elseif ($args != '') {
            // add & to $args if missing
            $c= substr($args, 0, 1);
            if ($c == '?') {
                $args= '&' . substr($args, 1);
            } elseif ($c != '&') {
                $args= '&' . $args;
            }
        }
        if ($this->config['friendly_urls'] == 1 && $alias != '') {
            $url= $f_url_prefix . $alias . $f_url_suffix . $args;
        } elseif ($this->config['friendly_urls'] == 1 && $alias == '') {
            $alias= $id;
            if ($this->config['friendly_alias_urls'] == 1) {
                $al= $this->aliasListing[$id];
                $alPath= !empty ($al['path']) ? $al['path'] . '/' : '';
                if ($al && $al['alias'])
                    $alias= $al['alias'];
            }
            $alias= $alPath . $f_url_prefix . $alias . $f_url_suffix;
            $url= $alias . $args;
        } else {
            $url= 'index.php?id=' . $id . $args;
        }

        $host= $this->config['base_url'];
        // check if scheme argument has been set
        if ($scheme != '') {
            // for backward compatibility - check if the desired scheme is different than the current scheme
            if (is_numeric($scheme) && $scheme != $_SERVER['HTTPS']) {
                $scheme= ($_SERVER['HTTPS'] ? 'http' : 'https');
            }

            // to-do: check to make sure that $site_url incudes the url :port (e.g. :8080)
            $host= $scheme == 'full' ? $this->config['site_url'] : $scheme . '://' . $_SERVER['HTTP_HOST'] . $host;
        }

        if ($this->config['xhtml_urls']) {
            $result = preg_replace("/&(?!amp;)/","&amp;", $host . $virtualDir . $url);
        } else {
            $result = $host . $virtualDir . $url;
        }

        return $result;
    } // makeUrl

    /**
     * Returns the current value of a configuration given by name.
     *
     * @category API-Function
     * @param string $name The configuration name
     * @return boolean|string
     * @example $dbUser = $modx->getConfig('user'); // Returns the current database user name
     */
    public function getConfig($name='') {
        if (!empty ($this->config[$name])) {
            $result = $this->config[$name];
        } else {
            $result = false;
        }

        return $result;
    } // getConfig

    /**
     * Returns the MODX version information as version, branch, release date,
     * and full application name.
     *
     * @category API-Function
     * @return array
     */
    public function getVersionData() {
        include $this->config["base_path"] . "manager/includes/version.inc.php";
        $v= array ();
        $v['version']= $modx_version;
        $v['branch']= $modx_branch;
        $v['release_date']= $modx_release_date;
        $v['full_appname']= $modx_full_appname;
        return $v;
    } // getVersionData

    /**
     * Returns an ordered or unordered HTML list.
     *
     * @category API-Function
     * @param array $array
     * @param string $ulroot Default: root
     * @param string $ulprefix Default: sub_
     * @param string $type Default: Empty string
     * @param boolean $ordered Default: false
     * @param int $tablevel Default: 0
     * @return string
     */
    public function makeList($array, $ulroot='root', $ulprefix='sub_', $type='', $ordered=false, $tablevel=0) {
        // first find out whether the value passed is an array
        if (!is_array($array)) {
            $result = "<ul><li>Bad list</li></ul>";
        }
        if (!empty ($type)) {
            $typestr= " style='list-style-type: $type'";
        } else {
            $typestr= '';
        }
        $tabs= '';
        for ($i= 0; $i < $tablevel; $i++) {
            $tabs .= "\t";
        }
        $result= $ordered == true ? $tabs . "<ol class='$ulroot'$typestr>\n" : $tabs . "<ul class='$ulroot'$typestr>\n";
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result .= $tabs . "\t<li>" . $key . "\n" . $this->makeList($value, $ulprefix . $ulroot, $ulprefix, $type, $ordered, $tablevel +2) . $tabs . "\t</li>\n";
            } else {
                $result .= $tabs . "\t<li>" . $value . "</li>\n";
            }
        }
        $result .= $ordered == true ? $tabs . "</ol>\n" : $tabs . "</ul>\n";

        return $result;
    } // makeList

    /**
     * Returns user loginn information, as logged in, internal key, user name,
     * and user type. User type is web or manager.
     *
     * @category API-Function
     * @return boolean|array
     */
    public function userLoggedIn() {
        $result = array ();
        if ($this->isFrontend() && isset ($_SESSION['webValidated'])) {
            // web user
            $result['loggedIn']= true;
            $result['id']= $_SESSION['webInternalKey'];
            $result['username']= $_SESSION['webShortname'];
            $result['usertype']= 'web'; // added by Raymond
        } else {
            if ($this->isBackend() && isset ($_SESSION['mgrValidated'])) {
                // manager user
                $result['loggedIn']= true;
                $result['id']= $_SESSION['mgrInternalKey'];
                $result['username']= $_SESSION['mgrShortname'];
                $result['usertype']= 'manager'; // added by Raymond
            } else {
                $result = false;
            }
        } // userLoggedIn

        return $result;
    } // userLoggedIn

    /**
     * Returns an array with keywords for the current document, or a document
     * with a given identifier.
     *
     * @category API-Function
     * @param int $id The document identifier, 0 means the current document
     *                Default: 0
     * @return array
     */
    public function getKeywords($id= 0) {
        if ($id == 0) {
            $id= $this->documentObject['id'];
        }
        $tblKeywords= $this->getFullTableName('site_keywords');
        $tblKeywordXref= $this->getFullTableName('keyword_xref');
        $sql= "SELECT keywords.keyword FROM " . $tblKeywords . " AS keywords INNER JOIN " . $tblKeywordXref . " AS xref ON keywords.id=xref.keyword_id WHERE xref.content_id = '$id'";
        $result= $this->db->query($sql);
        $limit= $this->db->getRecordCount($result);
        $keywords= array ();
        if ($limit > 0) {
            for ($i= 0; $i < $limit; $i++) {
                $row= $this->db->getRow($result);
                $keywords[]= $row['keyword'];
            }
        }
        return $keywords;
    } // getKeywords

    /**
     * Returns an array with meta tags for the current document, or a document
     * with a given identifier.
     *
     * @category API-Function
     * @param int $id The document identifier, 0 means the current document
     *                Default: 0
     * @return array
     */
    public function getMETATags($id= 0) {
        if ($id == 0) {
            $id= $this->documentObject['id'];
        }
        $sql= "SELECT smt.* " .
        "FROM " . $this->getFullTableName("site_metatags") . " smt " .
        "INNER JOIN " . $this->getFullTableName("site_content_metatags") . " cmt ON cmt.metatag_id=smt.id " .
        "WHERE cmt.content_id = '$id'";
        $ds= $this->db->query($sql);
        $limit= $this->db->getRecordCount($ds);
        $metatags= array ();
        if ($limit > 0) {
            for ($i= 0; $i < $limit; $i++) {
                $row= $this->db->getRow($ds);
                $metatags[$row['name']]= array (
                    "tag" => $row['tag'],
                    "tagvalue" => $row['tagvalue'],
                    "http_equiv" => $row['http_equiv']
                );
            }
        }
        return $metatags;
    } // getMETATags

    /**
     * Executes a snippet, if the snippet is chached, the result comes from the
     * cache.
     *
     * @category API-Function
     * @param string $snippetName
     * @param array $params Default: Empty array
     * @return string
     */
    public function runSnippet($snippetName, $params=array ()) {
        if (isset ($this->snippetCache[$snippetName])) {
            $snippet= $this->snippetCache[$snippetName];
            $properties= $this->snippetCache[$snippetName . "Props"];
        } else { // not in cache so let's check the db
            $sql= 'SELECT name, snippet, properties FROM '
                 .$this->getFullTableName('site_snippets') . ' WHERE '
                 .$this->getFullTableName('site_snippets') . '.name=\''
                 .$this->db->escape($snippetName) . '\';';
            $result= $this->db->query($sql);
            if ($this->db->getRecordCount($result) == 1) {
                $row= $this->db->getRow($result);
                $snippet= $this->snippetCache[$row['name']]= $row['snippet'];
                $properties= $this->snippetCache[$row['name'] . "Props"]= $row['properties'];
            } else {
                $snippet= $this->snippetCache[$snippetName]= "return false;";
                $properties= '';
            }
        }
        // load default params/properties
        $parameters= $this->parseProperties($properties);
        $parameters= array_merge($parameters, $params);
        // run snippet
        return $this->evalSnippet($snippet, $parameters);
    } // runSnippet

    /**
     * Returns the chunk content for the given chunk name
     * 
     * @category API-Function
     * @param string $chunkName
     * @return boolean|string
     */
    public function getChunk($chunkName) {
        if (isset($this->chunkCache[$chunkName])) {
            $result = $this->chunkCache[$chunkName];
        } else {
            $result = false;
        }

        return $result;
    } // getChunk
    
    /**
     * Old function that does call getChunk.
     * 
     * @category API-Function
     * @deprecated Use getChunk
     * @param string $chunkName
     * @return boolean|string
     */
    public function putChunk($chunkName) {
        return $this->getChunk($chunkName);
    } // putChunk

    /**
     * Returns the generated user data array
     *
     * @todo Replace code injection with a class
     * @category API-Function
     * @return array
     */
    public function getUserData() {
        include $this->config["base_path"] . "manager/includes/extenders/getUserData.extender.php";
        return $tmpArray;
    } // getUserData

    /**
     * Returns the timestamp in the configured format.
     *
     * @category API-Function
     * @param int $timestamp Default: 0
     * @param string $mode Default: Empty string
     * @return string
     */
    public function toDateFormat($timestamp=0, $mode='') {
        $timestamp = trim($timestamp);
        $timestamp = intval($timestamp);

        switch($this->config['datetime_format']) {
            case 'YYYY/mm/dd':
                $dateFormat = '%Y/%m/%d';
                break;
            case 'dd-mm-YYYY':
                $dateFormat = '%d-%m-%Y';
                break;
            case 'mm/dd/YYYY':
                $dateFormat = '%m/%d/%Y';
                break;
            /*
            case 'dd-mmm-YYYY':
                $dateFormat = '%e-%b-%Y';
                break;
            */
        }

        if (empty($mode)) {
            $strTime = strftime($dateFormat . " %H:%M:%S", $timestamp);
        } elseif ($mode == 'dateOnly') {
            $strTime = strftime($dateFormat, $timestamp);
        } elseif ($mode == 'formatOnly') {
            $strTime = $dateFormat;
        }
        return $strTime;
    } // toDateFormat

    /**
     * Returns a timestamp in the configured format.
     *
     * @category API-Function
     * @param string $str
     * @return string
     */
    public function toTimeStamp($str) {
        $result = '';

        $str = trim($str);
        if (!empty($str)) {
            switch($this->config['datetime_format']) {
                case 'YYYY/mm/dd':
                    if (!preg_match('/^[0-9]{4}\/[0-9]{2}\/[0-9]{2}[0-9 :]*$/', $str)) {
                        $str = '';
                    } else {
                        list ($Y, $m, $d, $H, $M, $S) = sscanf($str, '%4d/%2d/%2d %2d:%2d:%2d');
                    }
                    break;

                case 'dd-mm-YYYY':
                    if (!preg_match('/^[0-9]{2}-[0-9]{2}-[0-9]{4}[0-9 :]*$/', $str)) {
                        $str = '';
                    } else {
                        list ($d, $m, $Y, $H, $M, $S) = sscanf($str, '%2d-%2d-%4d %2d:%2d:%2d');
                    }
                    break;

                case 'mm/dd/YYYY':
                    if (!preg_match('/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}[0-9 :]*$/', $str)) {
                        $str = '';
                    } else {
                        list ($m, $d, $Y, $H, $M, $S) = sscanf($str, '%2d/%2d/%4d %2d:%2d:%2d');
                    }
                    break;
            }
            if (!empty($str)) {
                if (!$H && !$M && !$S) {
                    $H = 0;
                    $M = 0;
                    $S = 0;
                }
                $timeStamp = mktime($H, $M, $S, $m, $d, $Y);
                $timeStamp = intval($timeStamp);
                $result = $timeStamp;
            }
        }
        return $result;
    } // toTimeStamp

    /**
     * Returns the template varialble as assosiative array.
     *
     * @category API-Function
     * @param int $parentid The parent document identifier
     *                 Default: 0
     * @param array $tvidnames Default: Empty array
     * @param int $published Whether the document is published, or not,
     *                       1 = yes, 0 = no
     *                       Default: 1
     * @param string $docsort The result of the document children is sorted by
     *                        the given field
     *                        Default: menuindex
     * @param ASC $docsortdir SQL sort direction of the document children
     *                        Default: ASC
     * @param string $tvfields Template variables, * means all TVs
     *                         Default: *
     * @param string $tvsort The result of the template variables is sorted by
     *                        the given field
     *                        Default: rank
     * @param string  $tvsortdir SQL sort direction of the template variables
     *                           Default: ASC
     * @return boolean|array
     */
    public function getDocumentChildrenTVars($parentid=0, $tvidnames=array(), $published=1, $docsort="menuindex", $docsortdir="ASC", $tvfields="*", $tvsort="rank", $tvsortdir="ASC") {
        $docs= $this->getDocumentChildren($parentid, $published, 0, '*', '', $docsort, $docsortdir);
        if (!$docs) {
            $result = false;
        } else {
            $result= array ();
            // get user defined template variables
            $fields= ($tvfields == '') ? 'tv.*' : 'tv.' . implode(',tv.', preg_replace("/^\s/i", '', explode(',', $tvfields)));
            $tvsort= ($tvsort == '') ? '' : 'tv.' . implode(',tv.', preg_replace("/^\s/i", '', explode(',', $tvsort)));
            if ($tvidnames == '*') {
                $query= 'tv.id<>0';
            } else {
                $join_tvidnames = implode("','", $tvidnames);
                $query  = is_numeric($tvidnames[0]) ? 'tv.id' : 'tv.name';
                $query .= " IN ('{$join_tvidnames}')";
            }
            if ($docgrp= $this->getUserDocGroups()) {
                $docgrp= implode(',', $docgrp);
            }
            $tbl_site_tmplvars = $this->getFullTableName('site_tmplvars');
            $tbl_site_tmplvar_templates = $this->getFullTableName('site_tmplvar_templates');
            $tbl_site_tmplvar_contentvalues = $this->getFullTableName('site_tmplvar_contentvalues');
            $docCount= count($docs);
            for ($i= 0; $i < $docCount; $i++) {
                $tvs= array ();
                $docRow= $docs[$i];
                $docid= $docRow['id'];
                
                $fields  = "{$fields}, IF(tvc.value!='',tvc.value,tv.default_text) as value";
                $from    = "{$tbl_site_tmplvars} tv INNER JOIN {$tbl_site_tmplvar_templates} tvtpl ON tvtpl.tmplvarid = tv.id";
                $from   .= " LEFT JOIN {$tbl_site_tmplvar_contentvalues} tvc ON tvc.tmplvarid=tv.id AND tvc.contentid = '{$docid}'";
                $where   = "{$query} AND tvtpl.templateid = {$docRow['template']}";
                $orderby = ($tvsort) ? "{$tvsort} {$tvsortdir}" : '';
                $rs= $this->db->select($fields,$from,$where,$orderby);
                $total= $this->db->getRecordCount($rs);
                for ($x= 0; $x < $total; $x++) {
                    $tvs[] = @ $this->db->getRow($rs);
                }
                
                // get default/built-in template variables
                ksort($docRow);
                foreach ($docRow as $key => $value) {
                    if ($tvidnames == '*' || in_array($key, $tvidnames)) {
                        $tvs[] = array ('name'=>$key, 'value'=>$value);
                    }
                }
                if (count($tvs)) {
                    $result[] = $tvs;
                }
            }
        }

        return $result;
    } // getDocumentChildrenTVars
        
    /**
     * Returns the output of the template variables for the document children.
     *
     * @category API-Function
     * @param int $parentid The parent document identifier
     *                 Default: 0
     * @param array $tvidnames Default: Empty array
     * @param int $published Whether the document is published, or not,
     *                       1 = yes, 0 = no
     *                       Default: 1
     * @param string $docsort The result of the document children is sorted by
     *                        the given field
     *                        Default: menuindex
     * @param ASC $docsortdir SQL sort direction of the document children
     *                        Default: ASC
     * @return boolean|array
     */
    public function getDocumentChildrenTVarOutput($parentid=0, $tvidnames=array (), $published=1, $docsort="menuindex", $docsortdir="ASC") {
        $docs= $this->getDocumentChildren($parentid, $published, 0, '*', '', $docsort, $docsortdir);
        if (!$docs) {
            $result = false;
        } else {
            $result = array ();
            for ($i= 0; $i < count($docs); $i++) {
                $tvs= $this->getTemplateVarOutput($tvidnames, $docs[$i]["id"], $published);
                if ($tvs) {
                    $result[$docs[$i]['id']]= $tvs; // Use docid as key - netnoise 2006/08/14
                }
            }
        }

        return $result;
    } // getDocumentChildrenTVarOutput
    
    /**
     * Modified by Raymond for TV - Orig Modified by Apodigm - DocVars
     * Returns a single TV record. $idnames - can be an id or name that belongs
     * the template that the current document is using
     *
     * @category API-Function
     * @param string $idname Can be an id or name
     *                       Default: Empty string
     * @param string $fields Default: *
     * @param type $docid
     * @param type $published
     * @return boolean
     */
    public function getTemplateVar($idname='', $fields="*", $docid='', $published=1) {
        if ($idname == '') {
            $result = false;
        } else {
            $result= $this->getTemplateVars(array ($idname), $fields, $docid, $published, '', ''); //remove sorting for speed
            $result = ($result != false) ? $result[0] : false;
        }

        return $result;
    } // getTemplateVar

    /**
     * Returns an array of TV records. $idnames - can be an id or name that
     * belongs the template that the current document is using
     *
     * @category API-Function
     * @param array $idnames Default: empty array
     * @param string $fields Default: *
     * @param string $docid Default: Empty string
     * @param int $published Default: 1
     * @param string $sort Default: rank
     * @param string $dir Default: ASC
     * @return boolean|array
     */
    function getTemplateVars($idnames= array (), $fields='*', $docid='', $published=1, $sort='rank', $dir='ASC') {
        if (($idnames != '*' && !is_array($idnames)) || count($idnames) == 0) {
            $result = false;
        } else {
            $result= array ();

            // get document record
            if ($docid == '') {
                $docid= $this->documentIdentifier;
                $docRow= $this->documentObject;
            } else {
                $docRow= $this->getDocument($docid, '*', $published);
                if (!$docRow)
                    return false;
            }
            if (!$docRow) {
                $result = false;
            } else {

                // get user defined template variables
                $fields= ($fields == '') ? "tv.*" : 'tv.' . implode(',tv.', preg_replace("/^\s/i", "", explode(',', $fields)));
                $sort= ($sort == '') ? '' : 'tv.' . implode(',tv.', preg_replace("/^\s/i", "", explode(',', $sort)));
                if ($idnames == '*') {
                    $query= 'tv.id<>0';
                } else {
                    $query= (is_numeric($idnames[0]) ? 'tv.id' : 'tv.name') . " IN ('" . implode("','", $idnames) . "')";
                }
                if ($docgrp= $this->getUserDocGroups()) {
                    $docgrp= implode(",", $docgrp);
                }
                $sql= "SELECT $fields, IF(tvc.value!='',tvc.value,tv.default_text) as value ";
                $sql .= 'FROM ' . $this->getFullTableName('site_tmplvars')." tv ";
                $sql .= 'INNER JOIN ' . $this->getFullTableName('site_tmplvar_templates')." tvtpl ON tvtpl.tmplvarid = tv.id ";
                $sql .= 'LEFT JOIN ' . $this->getFullTableName('site_tmplvar_contentvalues')." tvc ON tvc.tmplvarid=tv.id AND tvc.contentid = '" . $docid . "' ";
                $sql .= 'WHERE ' . $query . ' AND tvtpl.templateid = ' . $docRow['template'];
                if ($sort) {
                    $sql .= " ORDER BY $sort $dir ";
                }
                $rs= $this->db->query($sql);
                for ($i= 0; $i < @ $this->db->getRecordCount($rs); $i++) {
                    array_push($result, @ $this->db->getRow($rs));
                }

                // get default/built-in template variables
                ksort($docRow);
                foreach ($docRow as $key => $value) {
                    if ($idnames == '*' || in_array($key, $idnames)) {
                        array_push($result, array (
                            'name' => $key,
                            'value' => $value
                        ));
                    }
                }
            }
        }

        return $result;
    } // getTemplateVars

    /**
     * Returns an associative array containing TV rendered output values.
     *
     * @category API-Function
     * @param type $idnames Can be an id or name that belongs the template that
     *                      the current document is using
     *                      Default: Empty array
     * @param string $docid Default: Empty string
     * @param int $published Default: 1
     * @param string $sep Default: Empty string
     * @return boolean|array
     */
    public function getTemplateVarOutput($idnames=array(), $docid='', $published=1, $sep='') {
        if (count($idnames) == 0) {
            $output = false;
        } else {
            $output= array ();
            $vars= ($idnames == '*' || is_array($idnames)) ? $idnames : array ($idnames);
            $docid= intval($docid) ? intval($docid) : $this->documentIdentifier;
            $result= $this->getTemplateVars($vars, '*', $docid, $published, '', '', $sep); // remove sort for speed
            if ($result == false) {
                $output = false;
            } else {
                $baspath= $this->config['base_path'] . 'manager/includes';
                include_once $baspath . '/tmplvars.format.inc.php';
                include_once $baspath . '/tmplvars.commands.inc.php';
                for ($i= 0; $i < count($result); $i++) {
                    $row= $result[$i];
                    if (!$row['id']) {
                        $output[$row['name']]= $row['value'];
                    } else {
                        $output[$row['name']]= getTVDisplayFormat($row['name'], $row['value'], $row['display'], $row['display_params'], $row['type'], $docid, $sep);
                    }
                }
            }
        }

        return $output;
    } // getTemplateVarOutput

    /**
     * Returns the full table name based on db settings
     *
     * @category API-Function
     * @param string $tbl Table name
     * @return string Table name with prefix
     */
    public function getFullTableName($tbl) {
        return $this->db->config['dbase'] . '.' . $this->db->config['table_prefix'] . $tbl;
    } // getFullTableName

    /**
     * Returns the placeholder value
     *
     * @param string $name Placeholder name
     * @return string Placeholder value
     */
    public function getPlaceholder($name) {
        return $this->placeholders[$name];
    } // getPlaceholder

    /**
     * Sets a value for a placeholder
     *
     * @category API-Function
     * @param string $name The name of the placeholder
     * @param string $value The value of the placeholder
     */
    public function setPlaceholder($name, $value) {
        $this->placeholders[$name]= $value;
    } // setPlaceholder

    /**
     * Set arrays or object vars as placeholders
     *
     * @category API-Function
     * @param object|array $subject
     * @param string $prefix
     */
    public function toPlaceholders($subject, $prefix='') {
        if (is_object($subject)) {
            $subject= get_object_vars($subject);
        }
        if (is_array($subject)) {
            foreach ($subject as $key => $value) {
                $this->toPlaceholder($key, $value, $prefix);
            }
        }
    } // toPlaceholders

    /**
     * Sets an array or object var as placeholder
     *
     * @category API-Function
     * @param string $key
     * @param object|array $value
     * @param string $prefix
     */
    public function toPlaceholder($key, $value, $prefix='') {
        if (is_array($value) || is_object($value)) {
            $this->toPlaceholders($value, "{$prefix}{$key}.");
        } else {
            $this->setPlaceholder("{$prefix}{$key}", $value);
        }
    } // toPlaceholder

    /**
     * Returns the virtual relative path to the manager folder
     *
     * @category API-Function
     * @global string $base_url
     * @return string The complete URL to the manager folder
     */
    public function getManagerPath() {
        global $base_url;
        return $base_url . 'manager/';
    } // getManagerPath

    /**
     * Returns the virtual relative path to the cache folder
     *
     * @category API-Function
     * @global string $base_url
     * @return string The complete URL to the cache folder
     */
    public function getCachePath() {
        global $base_url;
        return $base_url . 'assets/cache/';
    } // getCachePath

    /**
     * Sends a message to a user's message box.
     *
     * @category API-Function
     * @param string $type Type of the message
     * @param string $to The recipient of the message
     * @param string $from The sender of the message
     * @param string $subject The subject of the message
     * @param string $msg The message body
     * @param int $private Whether it is a private message, or not
     *                     Default : 0
     */
    public function sendAlert($type, $to, $from, $subject, $msg, $private= 0) {
        $tbl_manager_users = $this->getFullTableName('manager_users');
        $private= ($private) ? 1 : 0;
        if (!is_numeric($to)) {
            // Query for the To ID
            $rs= $this->db->select('id', $tbl_manager_users, "username='{$to}'");
            if ($this->db->getRecordCount($rs)) {
                $rs= $this->db->getRow($rs);
                $to= $rs['id'];
            }
        }
        if (!is_numeric($from)) {
            // Query for the From ID
            $rs= $this->db->select('id', $tbl_manager_users, "username='{$from}'");
            if ($this->db->getRecordCount($rs)) {
                $rs= $this->db->getRow($rs);
                $from= $rs['id'];
            }
        }
        // insert a new message into user_messages
        $f['id'] = '';
        $f['type'] = $type;
        $f['subject'] = $subject;
        $f['message'] = $msg;
        $f['sender'] = $from;
        $f['recipient'] = $to;
        $f['private'] = $private;
        $f['postdate'] = time();
        $f['messageread'] = 0;
        $rs= $this->db->insert($f, $this->getFullTableName('user_messages'));
    } // sendAlert
    
    /**
     * Returns true, install or interact when inside manager.
     *
     * @deprecated
     * @category API-Function
     * @return string
     */
    public function insideManager() {
        $m= false;
        if (defined('IN_MANAGER_MODE') && IN_MANAGER_MODE == 'true') {
            $m= true;
            if (defined('SNIPPET_INTERACTIVE_MODE') && SNIPPET_INTERACTIVE_MODE == 'true')
                $m= "interact";
            else
                if (defined('SNIPPET_INSTALL_MODE') && SNIPPET_INSTALL_MODE == 'true')
                    $m= "install";
        }
        return $m;
    } // insideManager

    /**
     * Returns current user id.
     *
     * @category API-Function
     * @param string $context
     * @return string
     */
    public function getLoginUserID($context= '') {
        $result = '';

        if ($context && isset ($_SESSION[$context . 'Validated'])) {
            $result = $_SESSION[$context . 'InternalKey'];
        } elseif ($this->isFrontend() && isset ($_SESSION['webValidated'])) {
            $result = $_SESSION['webInternalKey'];
        } elseif ($this->isBackend() && isset ($_SESSION['mgrValidated'])) {
            $result = $_SESSION['mgrInternalKey'];
        }

        return $result;
    } // getLoginUserID

    /**
     * Returns current user name
     *
     * @category API-Function
     * @param string $context
     * @return string
     */
    public function getLoginUserName($context= '') {
        $result = '';

        if (!empty($context) && isset ($_SESSION[$context . 'Validated'])) {
            $result = $_SESSION[$context . 'Shortname'];
        } elseif ($this->isFrontend() && isset ($_SESSION['webValidated'])) {
            $result = $_SESSION['webShortname'];
        } elseif ($this->isBackend() && isset ($_SESSION['mgrValidated'])) {
            $result = $_SESSION['mgrShortname'];
        }

        return $result;
    } // getLoginUserName

    /**
     * Returns current login user type - web or manager
     *
     * @category API-Function
     * @return string
     */
    public function getLoginUserType() {
        $result = '';

        if ($this->isFrontend() && isset ($_SESSION['webValidated'])) {
            $result = 'web';
        } elseif ($this->isBackend() && isset ($_SESSION['mgrValidated'])) {
            $result = 'manager';
        }

        return $result;
    } // getLoginUserType

    /**
     * Returns a user info record for the given manager user
     *
     * @category API-Function
     * @param int $uid
     * @return boolean|string
     */
    public function getUserInfo($uid) {
        $result = false;
        
        $tbl_manager_users = $this->getFullTableName('manager_users');
        $tbl_user_attributes = $this->getFullTableName('user_attributes');
        $field = 'mu.username, mu.password, mua.*';
        $from  = "{$tbl_manager_users} mu INNER JOIN {$tbl_user_attributes} mua ON mua.internalkey=mu.id";
        $rs= $this->db->select($field, $from, "mu.id = '$uid'");
        $limit= $this->db->getRecordCount($rs);
        if ($limit == 1) {
            $result= $this->db->getRow($rs);
            if (!$result['usertype']) {
                $result['usertype'] = 'manager';
            }
        }

        return $result;
    } // getUserInfo
    
    /**
     * Returns a record for the web user
     *
     * @category API-Function
     * @param int $uid
     * @return boolean|string
     */
    public function getWebUserInfo($uid) {
        $result = false;
        
        $tbl_web_users = $this->getFullTableName('web_users');
        $tbl_web_user_attributes = $this->getFullTableName('web_user_attributes');
        $field = 'wu.username, wu.password, wua.*';
        $from = "{$tbl_web_users} wu INNER JOIN {$tbl_web_user_attributes} wua ON wua.internalkey=wu.id";
        $rs= $this->db->select($field, $from, "wu.id='$uid'");
        $limit= $this->db->getRecordCount($rs);
        if ($limit == 1) {
            $result = $this->db->getRow($rs);
            if (!$result['usertype']) {
                $result['usertype'] = 'web';
            }
        }

        return $result;
    } // getWebUserInfo

    /**
     * Returns an array of document groups that current user is assigned to.
     * This function will first return the web user doc groups when running from
     * frontend otherwise it will return manager user's docgroup.
     *
     * @category API-Function
     * @param boolean $resolveIds Set to true to return the document group names
     *                            Default: false
     * @return string|array
     */
    public function getUserDocGroups($resolveIds=false) {
        $result = '';

        $dg  = array(); // add so
        $dgn = array();
        if ($this->isFrontend() && isset($_SESSION['webDocgroups']) 
                && !empty($_SESSION['webDocgroups']) && isset($_SESSION['webValidated'])) {
            $dg = $_SESSION['webDocgroups'];
            if(isset($_SESSION['webDocgrpNames'])) {
                $dgn = $_SESSION['webDocgrpNames']; //add so
            }
        }
        if (isset($_SESSION['mgrDocgroups']) && !empty($_SESSION['mgrDocgroups']) 
                && isset($_SESSION['mgrValidated'])) {
            if ($this->config['allow_mgr2web']==='1' || $this->isBackend()) {
                $dg = array_merge($dg, $_SESSION['mgrDocgroups']);
                if (isset($_SESSION['mgrDocgrpNames'])) {
                    $dgn = array_merge($dgn, $_SESSION['mgrDocgrpNames']);
                }
            }
        }
        if (!$resolveIds) {
            $result = $dg;
        } elseif(!empty($dgn) || empty($dg)) {
            $result = $dgn; // add so
        } elseif(is_array($dg)) {
            // resolve ids to names
            $dgn = array ();
            $tbl_dgn = $this->getFullTableName('documentgroup_names');
            $imploded_dg = implode(',', $dg);
            $ds = $this->db->select('name', $tbl_dgn, "id IN ({$imploded_dg})");
            while ($row = $this->db->getRow($ds)) {
                $dgn[count($dgn)] = $row['name'];
            }
            // cache docgroup names to session
            if ($this->isFrontend()) {
                $_SESSION['webDocgrpNames'] = $dgn;
            } else {
                $_SESSION['mgrDocgrpNames'] = $dgn;
            }
            $result = $dgn;
        }

        return $result;
    } // getUserDocGroups
    
    /**
     * Returns an array of document groups that current user is assigned to.
     * This function will first return the web user doc groups when running from
     * frontend otherwise it will return manager user's docgroup.
     *
     * @deprecated
     * @category API-Function
     * @return string|array
     */
    public function getDocGroups() {
        return $this->getUserDocGroups();
    } // getDocGroups

    /**
     * Change current web user's password
     *
     * @category API-Function
     * @todo Make password length configurable, allow rules for passwords and translation of messages
     * @param string $oldPwd
     * @param string $newPwd
     * @return string|boolean Returns true if successful, oterhwise return error
     *                        message
     */
    public function changeWebUserPassword($oldPwd, $newPwd) {
        $result = false;

        $rt= false;
        if ($_SESSION["webValidated"] == 1) {
            $tbl_web_users= $this->getFullTableName("web_users");
            $uid = $this->getLoginUserID();
            $ds= $this->db->select('id,username,password', $tbl_web_users, "`id`='{$uid}'");
            $limit= $this->db->getRecordCount($ds);
            if ($limit == 1) {
                $row = $this->db->getRow($ds);
                if ($row["password"] == md5($oldPwd)) {
                    if (strlen($newPwd) < 6) {
                        $result = 'Password is too short!';
                    } elseif ($newPwd == '') {
                        $result = "You didn't specify a password for this user!";
                    } else {
                        $newPwd = $this->db->escape($newPwd);
                        $this->db->update("password = md5('{$newPwd}')", $tbl_web_users, "id='{$uid}'");
                        // invoke OnWebChangePassword event
                        $this->invokeEvent("OnWebChangePassword",
                        array
                        (
                            "userid" => $row["id"],
                            "username" => $row["username"],
                            "userpassword" => $newPwd
                        ));
                        $result = true;
                    }
                } else {
                    $result = 'Incorrect password.';
                }
            }
        }

        return $result;
    } // changeWebUserPassword
    
    /**
     * Change current web user's password
     *
     * @category API-Function
     * @deprecated
     * @param string $oldPwd
     * @param string $newPwd
     * @return string|boolean Returns true if successful, oterhwise return error
     *                        message
     */
    public function changePassword($o, $n) {
        return changeWebUserPassword($o, $n);
    } // changePassword

    /**
     * Returns true if the current web user is a member the specified groups
     *
     * @category API-Function
     * @param array $groupNames
     * @return boolean
     */
    public function isMemberOfWebGroup($groupNames=array ()) {
        if (!is_array($groupNames)) {
            $result = false;
        } else {
            // check cache
            $grpNames= isset ($_SESSION['webUserGroupNames']) ? $_SESSION['webUserGroupNames'] : false;
            if (!is_array($grpNames)) {
                $tbl= $this->getFullTableName("webgroup_names");
                $tbl2= $this->getFullTableName("web_groups");
                $sql= "SELECT wgn.name
                        FROM $tbl wgn
                        INNER JOIN $tbl2 wg ON wg.webgroup=wgn.id AND wg.webuser='" . $this->getLoginUserID() . "'";
                $grpNames= $this->db->getColumn("name", $sql);
                // save to cache
                $_SESSION['webUserGroupNames']= $grpNames;
            }
            foreach ($groupNames as $k => $v)
                if (in_array(trim($v), $grpNames))
                    return true;
            $result = false;
        }

        return $result;
    } // isMemberOfWebGroup

    /**
     * Registers Client-side CSS scripts - these scripts are loaded at inside
     * the <head> tag
     *
     * @category API-Function
     * @param string $src
     * @param string $media Default: Empty string
     */
    public function regClientCSS($src, $media='') {
        if (!empty($src) || !isset($this->loadedjscripts[$src])) {
            $nextpos= max(array_merge(array(0),array_keys($this->sjscripts)))+1;
            $this->loadedjscripts[$src]['startup']= true;
            $this->loadedjscripts[$src]['version']= '0';
            $this->loadedjscripts[$src]['pos']= $nextpos;
            if (strpos(strtolower($src), "<style") !== false || strpos(strtolower($src), "<link") !== false) {
                $this->sjscripts[$nextpos]= $src;
            } else {
                $this->sjscripts[$nextpos]= "\t" . '<link rel="stylesheet" type="text/css" href="'.$src.'" '.($media ? 'media="'.$media.'" ' : '').'/>';
            }
        }
    } // regClientCSS

    /**
     * Registers Startup Client-side JavaScript - these scripts are loaded at
     * inside the <head> tag
     *
     * @category API-Function
     * @param string $src
     * @param array $options Default: 'name'=>'', 'version'=>'0', 'plaintext'=>false
     */
    public function regClientStartupScript($src, $options= array('name'=>'', 'version'=>'0', 'plaintext'=>false)) {
        $this->regClientScript($src, $options, true);
    } // regClientStartupScript

    /**
     * Registers Client-side JavaScript
     * these scripts are loaded at the end of the page unless $startup is true
     *
     * @category API-Function
     * @param string $src
     * @param array $options Default: 'name'=>'', 'version'=>'0', 'plaintext'=>false
     * @param boolean $startup Default: false
     * @return string
     */
    public function regClientScript($src, $options=array('name'=>'', 'version'=>'0', 'plaintext'=>false), $startup=false) {
        $registered = false;

        // Empty = nothing to register
        if (!empty($src)) {
            if (!is_array($options)) {
                if (is_bool($options))  // backward compatibility with old plaintext parameter
                    $options=array('plaintext'=>$options);
                elseif (is_string($options)) // Also allow script name as 2nd param
                    $options=array('name'=>$options);
                else
                    $options=array();
            }
            $name= isset($options['name']) ? strtolower($options['name']) : '';
            $version= isset($options['version']) ? $options['version'] : '0';
            $plaintext= isset($options['plaintext']) ? $options['plaintext'] : false;
            $key= !empty($name) ? $name : $src;
            unset($overwritepos); // probably unnecessary--just making sure

            $useThisVer= true;
            if (isset($this->loadedjscripts[$key])) { // a matching script was found
                // if existing script is a startup script, make sure the candidate is also a startup script
                if ($this->loadedjscripts[$key]['startup'])
                    $startup= true;

                if (empty($name)) {
                    $useThisVer= false; // if the match was based on identical source code, no need to replace the old one
                } else {
                    $useThisVer = version_compare($this->loadedjscripts[$key]['version'], $version, '<');
                }

                if ($useThisVer) {
                    if ($startup==true && $this->loadedjscripts[$key]['startup']==false) {
                        // remove old script from the bottom of the page (new one will be at the top)
                        unset($this->jscripts[$this->loadedjscripts[$key]['pos']]);
                    } else {
                        // overwrite the old script (the position may be important for dependent scripts)
                        $overwritepos= $this->loadedjscripts[$key]['pos'];
                    }
                } else { // Use the original version
                    if ($startup==true && $this->loadedjscripts[$key]['startup']==false) {
                        // need to move the exisiting script to the head
                        $version= $this->loadedjscripts[$key][$version];
                        $src= $this->jscripts[$this->loadedjscripts[$key]['pos']];
                        unset($this->jscripts[$this->loadedjscripts[$key]['pos']]);
                    } else {
                        $registered = true; // the script is already in the right place
                    }
                }
            }

            if (!$registered) {
                if ($useThisVer && $plaintext!=true && (strpos(strtolower($src), "<script") === false)) {
                    $src= "\t" . '<script type="text/javascript" src="' . $src . '"></script>';
                }
                if ($startup) {
                    $pos= isset($overwritepos) ? $overwritepos : max(array_merge(array(0),array_keys($this->sjscripts)))+1;
                    $this->sjscripts[$pos]= $src;
                } else {
                    $pos= isset($overwritepos) ? $overwritepos : max(array_merge(array(0),array_keys($this->jscripts)))+1;
                    $this->jscripts[$pos]= $src;
                }
                $this->loadedjscripts[$key]['version']= $version;
                $this->loadedjscripts[$key]['startup']= $startup;
                $this->loadedjscripts[$key]['pos']= $pos;
            }
        }
    } // regClientScript

    /**
     * Registers Client-side Startup HTML block
     *
     * @category API-Function
     * @param string $html
     */
    public function regClientStartupHTMLBlock($html) {
        $this->regClientScript($html, true, true);
    } // regClientStartupHTMLBlock

    /**
     * Registers Client-side HTML block
     *
     * @param string $html
     */
    public function regClientHTMLBlock($html) {
        $this->regClientScript($html, true);
    } // regClientHTMLBlock

    /**
     * Remove unwanted html tags and snippet, settings and tags
     *
     * @category API-Function
     * @param string $html
     * @param string $allowed Default: Empty string
     * @return string
     */
    public function stripTags($html, $allowed='') {
        $result = strip_tags($html, $allowed);
        $result = preg_replace('~\[\*(.*?)\*\]~', '', $result); //tv
        $result = preg_replace('~\[\[(.*?)\]\]~', '', $result); //snippet
        $result = preg_replace('~\[\!(.*?)\!\]~', '', $result); //snippet
        $result = preg_replace('~\[\((.*?)\)\]~', '', $result); //settings
        $result = preg_replace('~\[\+(.*?)\+\]~', '', $result); //placeholders
        $result = preg_replace('~{{(.*?)}}~', '', $result); //chunks

        return $result;
    } // stripTags

    /**
     * Add an event listner to a plugin - only for use within the current
     * execution cycle
     *
     * @category API-Function
     * @param string $evtName
     * @param string $pluginName
     * @return boolean|int
     */
    public function addEventListener($evtName, $pluginName) {
        if (!$evtName || !$pluginName) {
            $result = false;
        } else {
            if (!array_key_exists($evtName,$this->pluginEvent)) {
                $this->pluginEvent[$evtName] = array();
            }
            $result = array_push($this->pluginEvent[$evtName], $pluginName); // return array count
        }

        return $result;
    } // addEventListener

    /**
     * Remove event listner - only for use within the current execution cycle
     *
     * @category API-Function
     * @param string $evtName
     * @return boolean
     */
    public function removeEventListener($evtName) {
        if (!$evtName) {
            $result = false;
        } else {
            unset ($this->pluginEvent[$evtName]);
            $result = true;
        }

        return $result;
    } // removeEventListener

    /**
     * Remove all event listners - only for use within the current execution
     * cycle
     * @category API-Function
     */
    public function removeAllEventListener() {
        unset ($this->pluginEvent);
        $this->pluginEvent= array ();
    } // removeAllEventListener

    /**
     * Invoke an event. $extParams - hash array: name=>value
     *
     * @param string $evtName
     * @param array $extParams
     * @return boolean|array
     */
    public function invokeEvent($evtName, $extParams=array()) {
        if ($this->safeMode == true || !$evtName || !isset($this->pluginEvent[$evtName])) {
            $result = false;
        } else {
            $el= $this->pluginEvent[$evtName];
            $result= array ();
            $numEvents= count($el);
            if ($numEvents > 0) {
                for ($i= 0; $i < $numEvents; $i++) { // start for loop
                    $pluginName= $el[$i];
                    $pluginName = stripslashes($pluginName);
                    // reset event object
                    $e= & $this->event;
                    $e->_resetEventObject();
                    $e->name= $evtName;
                    $e->activePlugin= $pluginName;

                    // get plugin code
                    if (isset ($this->pluginCache[$pluginName])) {
                        $pluginCode= $this->pluginCache[$pluginName];
                        $pluginProperties= isset($this->pluginCache["{$pluginName}Props"]) ? $this->pluginCache["{$pluginName}Props"] : '';
                    } else {
                        $fields = '`name`, plugincode, properties';
                        $tbl_site_plugins = $this->getFullTableName('site_plugins');
                        $where = "`name`='{$pluginName}' AND disabled=0";
                        $result= $this->db->select($fields,$tbl_site_plugins,$where);
                        if ($this->db->getRecordCount($result) == 1) {
                            $row= $this->db->getRow($result);

                            $pluginCode = $row['plugincode'];
                            $this->pluginCache[$row['name']] = $row['plugincode']; 
                            $pluginProperties= $this->pluginCache["{$row['name']}Props"]= $row['properties'];
                        } else {
                            $pluginCode = 'return false;';
                            $this->pluginCache[$pluginName] = 'return false;';
                            $pluginProperties= '';
                        }
                    }

                    // load default params/properties
                    $parameter= $this->parseProperties($pluginProperties);
                    if (!empty($extParams)) {
                        $parameter= array_merge($parameter, $extParams);
                    }

                    // eval plugin
                    $this->evalPlugin($pluginCode, $parameter);
                    $e->setAllGlobalVariables();
                    if ($e->_output != '') {
                        $result[]= $e->_output;
                    }
                    if ($e->_propagate != true) {
                        break;
                    }
                }
            }
            $e->activePlugin= '';
        }
        return $result;
    } // invokeEvent

    /**
     * Parses a resource property string and returns the result as an array
     *
     * @category API-Function
     * @param string $propertyString
     * @return type
     */
    public function parseProperties($propertyString) {
        $result = array ();

        if (!empty($propertyString)) {
            $tmpParams= explode('&', $propertyString);
            for ($x= 0; $x < count($tmpParams); $x++) {
                if (strpos($tmpParams[$x], '=', 0)) {
                    $pTmp= explode('=', $tmpParams[$x]);
                    $pvTmp= explode(';', trim($pTmp[1]));
                    if ($pvTmp[1] == 'list' && $pvTmp[3] != '') {
                        $result[trim($pTmp[0])]= $pvTmp[3]; //list default
                    } else {
                        if ($pvTmp[1] != 'list' && $pvTmp[2] != '') {
                            $result[trim($pTmp[0])]= $pvTmp[2];
                        }
                    }
                }
            }
        }

        return $result;
    } // parseProperties

    /**
     * Sends a mail to the recipients. The mail fields are managed in the
     * params array. The minium parameters are sendto and subject.
     *
     * @category API-Function
     * @param type $params The parameters that are used to send the mail.
     *                     Possible parameters in the array are sendto, subject,
     *                     from, and fromname. The configuration parameters 
     *                     emailsender is used, if from is empty, and site_name
     *                     is used, when fromname is empty.
     *                     Default: Empty array
     * @param array $msg The body of the mail
     *                   Default: Empty string
     * @return boolean
     */
    public function sendmail($params=array(), $msg='') {
        if (isset($params) && is_string($params)) {
            if (strpos($params, '=') === false) {
                if (strpos($params,'@') !== false) {
                    $p['sendto']  = $params;
                } else {
                    $p['subject'] = $params;
                }
            } else {
                $params_array = explode(',',$params);
                foreach ($params_array as $k=>$v) {
                    $k = trim($k);
                    $v = trim($v);
                    $p[$k] = $v;
                }
            }
        } else {
            $p = $params;
            unset($params);
        }
        include_once $this->config['base_path'] . 'manager/includes/controls/modxmailer.inc.php';
        $mail = new MODxMailer();
        $mail->IsMail();
        $mail->IsHTML(0);
        $mail->From     = (!isset($p['from']))     ? $this->config['emailsender']  : $p['from'];
        $mail->FromName = (!isset($p['fromname'])) ? $this->config['site_name']    : $p['fromname'];
        $mail->Subject  = (!isset($p['subject']))  ? $this->config['emailsubject'] : $p['subject'];
        $sendto         = (!isset($p['sendto']))   ? $this->config['emailsender']  : $p['sendto'];
        $mail->Body     = $msg;
        $mail->AddAddress($sendto);
        $result = $mail->Send();

        return $result;
    } // sendmail
    
    /**
     * Reduces the records in a given table.
     *
     * @category API-Function
     * @global type $dbase
     * @param string $target Default: event_log
     * @param int $limit Default: 2000
     * @param int $trim Default: 100
     * @todo This method is not database independent, we need to move it to the DBAPI class
     */
    public function rotate_log($target='event_log', $limit=2000, $trim=100) {
        global $dbase;
        
        if ($limit < $trim) {
            $trim = $limit;
        }
        
        $target = $this->getFullTableName($target);
        $count = $this->db->getValue($this->db->select('COUNT(id)', $target));
        $over = $count - $limit;
        if (0 < $over) {
            $trim = ($over + $trim);
            $this->db->delete($target, '', $trim);
        }
        $result = $this->db->query("SHOW TABLE STATUS FROM {$dbase}");
        while ($row = $this->db->getRow($result)) {
            $tbl_name = $row['Name'];
            $this->db->query("OPTIMIZE TABLE {$tbl_name}");
        }
    } // rotate_log
    
    /**
     * Removes inactive users from the activ users table.
     *
     * @category API-Function
     * @param string $action Default: all
     * @param int $limit_time Default: 86400
     */
    public function remove_locks($action='all', $limit_time=86400) {
        $limit_time = time() - $limit_time;
        if ($action === 'all') {
            $action = '';
        } else {
            $action     = intval($action);
            $action = "action={$action} and";
        }
        $tbl_active_users = $this->getFullTableName('active_users');
        $this->db->delete($tbl_active_users,"{$action} lasthit < {$limit_time}");
    } // remove_locks
    
    /**
     * Parse a chunk
     *
     * @category API-Function
     * @param string $chunkName
     * @param array $chunkArr
     * @param string $prefix Default: {
     * @param string $suffix Default: }
     * @return boolean|string
     */
    function parseChunk($chunkName, $chunkArr, $prefix= '{', $suffix= '}',$mode='chunk')
    {
        if (!is_array($chunkArr)) return false;
        
        if($mode==='chunk') {
            $_ = $this->getChunk($chunkName);
        } else {
            $_ = $chunkName;
        }
        
        foreach ($chunkArr as $key => $value) {
            $_ = str_replace("{$prefix}{$key}{$suffix}", $value, $_);
        }
        $result = $_;
        return $result;
    } // parseChunk

    /**
     * Parse placeholders and call parseChunk with the result.
     * 
     * @category API-Function
     * @param string $src Default: Empty string
     * @param array $ph Default: Empty array
     * @param string $left Default: [+
     * @param string $right Default: +]
     * @param string $mode Default: ph
     * @return string
     */
    public function parsePlaceholder($src='', $ph=array(), $left='[+', $right= '+]',$mode='ph') {
        if (!$ph) {
            $result = $src;
        } elseif (is_string($ph) && strpos($ph, '=')) {
            if (strpos($ph,',')) {
                $pairs   = explode(',',$ph);
            } else {
                $pairs[] = $ph;
            }
            
            unset($ph);
            $ph = array();
            foreach ($pairs as $pair) {
                list($k,$v) = explode('=',$pair);
                $ph[$k] = $v;
            }
        }
        $result = $this->parseChunk($src, $ph, $left, $right, $mode);
        return $result;
    } // parsePlaceholder
    
    /**
     * Returns a formatted string with weekday information.
     * 
     * @category API-Function
     * @param string $format 
     * @param string $timestamp
     * @return string
     * @todo Check, if this funktion isn't really needed, and if it is needed,
     *       then it has to be internaionlalized.
     */
    public function mb_strftime($format='%Y/%m/%d', $timestamp='') {
        $a = array(
            'Sun', 
            'Mon', 
            'Tue', 
            'Wed', 
            'Thu', 
            'Fri', 
            'Sat'
        );
        $A = array(
            'Sunday', 
            'Monday', 
            'Tuesday', 
            'Wednesday', 
            'Thursday', 
            'Friday',
            'Saturday'
        );
        $w = strftime('%w', $timestamp);
        $p = array(
            'am'=>'AM', 
            'pm'=>'PM'
        );
        $P = array(
            'am'=>'am', 
            'pm'=>'pm'
        );
        $ampm = (strftime('%H', $timestamp) < 12) ? 'am' : 'pm';
        
        if ($timestamp === '') {
            $result = '';
        } else {
            if (substr(PHP_OS, 0, 3) == 'WIN') {
                $format = str_replace('%-', '%#', $format);
            }
            $pieces = preg_split('@(%[\-#]?[a-zA-Z%])@', $format, null, PREG_SPLIT_DELIM_CAPTURE);

            $result = '';
            foreach ($pieces as $v) {
                if($v == '%a') {
                    $result .= $a[$w];
                } elseif ($v == '%A') { 
                    $result .= $A[$w];
                } elseif ($v == '%p') {
                    $result .= $p[$ampm];
                } elseif ($v == '%P') {
                    $result .= $P[$ampm];
                } elseif (strpos($v,'%') !== false) {
                    $result .= strftime($v, $timestamp);
                } else {
                    $result .= $v;
                }
            }
        }

        return $result;
    } // mb_strftime


    /*############################################
      Etomite_dbFunctions.php
      New database functions for Etomite CMS
    Author: Ralph A. Dahlgren - rad14701@yahoo.com
    Etomite ID: rad14701
    See documentation for usage details
    ############################################*/
    /**
     * @deprecated And set to private
     * @param type $fields
     * @param type $from
     * @param type $where
     * @param type $sort
     * @param type $dir
     * @param type $limit
     * @return boolean|array 
     */
    private function getIntTableRows($fields= "*", $from= "", $where= "", $sort= "", $dir= "ASC", $limit= "") {
        // function to get rows from ANY internal database table
        if ($from == "") {
            return false;
        } else {
            $where= ($where != "") ? "WHERE $where" : "";
            $sort= ($sort != "") ? "ORDER BY $sort $dir" : "";
            $limit= ($limit != "") ? "LIMIT $limit" : "";
            $tbl= $this->getFullTableName($from);
            $sql= "SELECT $fields FROM $tbl $where $sort $limit;";
            $result= $this->db->query($sql);
            $resourceArray= array ();
            for ($i= 0; $i < @ $this->db->getRecordCount($result); $i++) {
                array_push($resourceArray, @ $this->db->getRow($result));
            }
            return $resourceArray;
        }
    } // getIntTableRows

    /**
     * @deprecated And set to private
     * @param type $fields
     * @param type $into
     * @return boolean 
     */
    private function putIntTableRow($fields= "", $into= "") {
        // function to put a row into ANY internal database table
        if (($fields == "") || ($into == "")) {
            return false;
        } else {
            $tbl= $this->getFullTableName($into);
            $sql= "INSERT INTO $tbl SET ";
            foreach ($fields as $key => $value) {
                $sql .= $key . "=";
                if (is_numeric($value))
                    $sql .= $value . ",";
                else
                    $sql .= "'" . $value . "',";
            }
            $sql= rtrim($sql, ",");
            $sql .= ";";
            $result= $this->db->query($sql);
            return $result;
        }
    } // putIntTableRow

    /**
     * @deprecated And set to private
     * @param type $fields
     * @param type $into
     * @param type $where
     * @param type $sort
     * @param type $dir
     * @param type $limit
     * @return boolean 
     */
    private function updIntTableRow($fields= "", $into= "", $where= "", $sort= "", $dir= "ASC", $limit= "") {
        // function to update a row into ANY internal database table
        if (($fields == "") || ($into == "")) {
            return false;
        } else {
            $where= ($where != "") ? "WHERE $where" : "";
            $sort= ($sort != "") ? "ORDER BY $sort $dir" : "";
            $limit= ($limit != "") ? "LIMIT $limit" : "";
            $tbl= $this->getFullTableName($into);
            $sql= "UPDATE $tbl SET ";
            foreach ($fields as $key => $value) {
                $sql .= $key . "=";
                if (is_numeric($value))
                    $sql .= $value . ",";
                else
                    $sql .= "'" . $value . "',";
            }
            $sql= rtrim($sql, ",");
            $sql .= " $where $sort $limit;";
            $result= $this->db->query($sql);
            return $result;
        }
    } // updIntTableRow

    /**
     * @deprecated And set to private
     * @param type $host
     * @param type $user
     * @param type $pass
     * @param type $dbase
     * @param type $fields
     * @param type $from
     * @param type $where
     * @param type $sort
     * @param type $dir
     * @param type $limit
     * @return boolean|array 
     */
    private function getExtTableRows($host= "", $user= "", $pass= "", $dbase= "", $fields= "*", $from= "", $where= "", $sort= "", $dir= "ASC", $limit= "") {
        // function to get table rows from an external MySQL database
        if (($host == "") || ($user == "") || ($pass == "") || ($dbase == "") || ($from == "")) {
            return false;
        } else {
            $where= ($where != "") ? "WHERE  $where" : "";
            $sort= ($sort != "") ? "ORDER BY $sort $dir" : "";
            $limit= ($limit != "") ? "LIMIT $limit" : "";
            $tbl= $dbase . "." . $from;
            $this->dbExtConnect($host, $user, $pass, $dbase);
            $sql= "SELECT $fields FROM $tbl $where $sort $limit;";
            $result= $this->db->query($sql);
            $resourceArray= array ();
            for ($i= 0; $i < @ $this->db->getRecordCount($result); $i++) {
                array_push($resourceArray, @ $this->db->getRow($result));
            }
            return $resourceArray;
        }
    } // getExtTableRows

    /**
     * @deprecated And set to private
     * @param type $host
     * @param type $user
     * @param type $pass
     * @param type $dbase
     * @param type $fields
     * @param type $into
     * @return boolean 
     */
    private function putExtTableRow($host= "", $user= "", $pass= "", $dbase= "", $fields= "", $into= "") {
        // function to put a row into an external database table
        if (($host == "") || ($user == "") || ($pass == "") || ($dbase == "") || ($fields == "") || ($into == "")) {
            return false;
        } else {
            $this->dbExtConnect($host, $user, $pass, $dbase);
            $tbl= $dbase . "." . $into;
            $sql= "INSERT INTO $tbl SET ";
            foreach ($fields as $key => $value) {
                $sql .= $key . "=";
                if (is_numeric($value))
                    $sql .= $value . ",";
                else
                    $sql .= "'" . $value . "',";
            }
            $sql= rtrim($sql, ",");
            $sql .= ";";
            $result= $this->db->query($sql);
            return $result;
        }
    } // putExtTableRow

    /**
     * @deprecated And set to private
     * @param type $host
     * @param type $user
     * @param type $pass
     * @param type $dbase
     * @param type $fields
     * @param type $into
     * @param type $where
     * @param type $sort
     * @param type $dir
     * @param type $limit
     * @return boolean 
     */
    private function updExtTableRow($host= "", $user= "", $pass= "", $dbase= "", $fields= "", $into= "", $where= "", $sort= "", $dir= "ASC", $limit= "") {
        // function to update a row into an external database table
        if (($fields == "") || ($into == "")) {
            return false;
        } else {
            $this->dbExtConnect($host, $user, $pass, $dbase);
            $tbl= $dbase . "." . $into;
            $where= ($where != "") ? "WHERE $where" : "";
            $sort= ($sort != "") ? "ORDER BY $sort $dir" : "";
            $limit= ($limit != "") ? "LIMIT $limit" : "";
            $sql= "UPDATE $tbl SET ";
            foreach ($fields as $key => $value) {
                $sql .= $key . "=";
                if (is_numeric($value))
                    $sql .= $value . ",";
                else
                    $sql .= "'" . $value . "',";
            }
            $sql= rtrim($sql, ",");
            $sql .= " $where $sort $limit;";
            $result= $this->db->query($sql);
            return $result;
        }
    } // updExtTableRow

    /**
     * @deprecated And set to private
     * @param type $host
     * @param type $user
     * @param type $pass
     * @param type $dbase 
     */
    private function dbExtConnect($host, $user, $pass, $dbase) {
        // function to connect to external database
        $tstart= $this->getMicroTime();
        if (@ !$this->rs= mysql_connect($host, $user, $pass)) {
            $this->messageQuit("Failed to create connection to the $dbase database!");
        } else {
            mysql_select_db($dbase);
            $tend= $this->getMicroTime();
            $totaltime= $tend - $tstart;
            if ($this->dumpSQL) {
                $this->queryCode .= "<fieldset style='text-align:left'><legend>Database connection</legend>" . sprintf("Database connection to %s was created in %2.4f s", $dbase, $totaltime) . "</fieldset><br />";
            }
            $this->queryTime= $this->queryTime + $totaltime;
        }
    } // dbExtConnect

    /**
     * @deprecated And set to private
     * @param type $method
     * @param type $prefix
     * @param type $trim
     * @param type $REQUEST_METHOD
     * @return boolean 
     */
    private function getFormVars($method= "", $prefix= "", $trim= "", $REQUEST_METHOD) {
        //  function to retrieve form results into an associative array
        $results= array ();
        $method= strtoupper($method);
        if ($method == "")
            $method= $REQUEST_METHOD;
        if ($method == "POST")
            $method= & $_POST;
        elseif ($method == "GET") $method= & $_GET;
        else
            return false;
        reset($method);
        foreach ($method as $key => $value) {
            if (($prefix != "") && (substr($key, 0, strlen($prefix)) == $prefix)) {
                if ($trim) {
                    $pieces= explode($prefix, $key, 2);
                    $key= $pieces[1];
                    $results[$key]= $value;
                } else
                    $results[$key]= $value;
            }
            elseif ($prefix == "") $results[$key]= $value;
        }
        return $results;
    } // getFormVars

    ########################################
    // END New database functions - rad14701
    ########################################

    /***************************************************************************************/
    /* End of API functions                                       */
    /***************************************************************************************/

    /**
     * Checks the PHP error and calls messageQuit. messageQuit is not called
     * when error_reporting is 0, $nr is 0, or $nr is 8 and stopOnNotice is
     * false
     *
     * @param int $nr
     * @param string $text
     * @param string $file
     * @param string $line
     * @return boolean
     */
    public function phpError($nr, $text, $file, $line) {
        if (error_reporting() == 0 || $nr == 0 || ($nr == 8 && $this->stopOnNotice == false)) {
            return true;
        }
        if (is_readable($file)) {
            $source= file($file);
            $source= htmlspecialchars($source[$line -1]);
        } else {
            $source= '';
        } //Error $nr in $file at $line: <div><code>$source</code></div>
        $this->messageQuit('PHP Parse Error', '', true, $nr, $file, $source, $text, $line);
    } // phpError

    /**
     * Returns an error page with detailed informations about the error.
     *
     * @param string $msg Default: unspecified error
     * @param string $query Default: Empty string
     * @param boolean $is_error Default: true
     * @param string $nr Default: Empty string
     * @param string $file Default: Empty string
     * @param string $source Default: Empty string
     * @param string $text Default: Empty string
     * @param string $line Default: Empty string
     */
    public function messageQuit($msg='unspecified error', $query='', $is_error=true, $nr='', $file='', $source='', $text='', $line='') {

        if(!empty($file)) $file = str_replace('\\', '/', $file);
        $version= isset ($GLOBALS['version']) ? $GLOBALS['version'] : '';
        $release_date= isset ($GLOBALS['release_date']) ? $GLOBALS['release_date'] : '';
        $request_uri = $_SERVER['REQUEST_URI'];
        $request_uri = htmlspecialchars($request_uri, ENT_QUOTES);
        $ua          = htmlspecialchars($_SERVER['HTTP_USER_AGENT'], ENT_QUOTES);
        $referer     = htmlspecialchars($_SERVER['HTTP_REFERER'], ENT_QUOTES);
        $str = "
              <html><head><title>MODX Content Manager $version &raquo; $release_date</title>
              <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
              <style>td, body { font-size: 12px; font-family:Verdana; }</style>
              </head><body>
              ";
        if ($is_error) {
            $str .= "<h3 style='color:red'>&laquo; MODX Parse Error &raquo;</h3>
                    <table border='0' cellpadding='1' cellspacing='0'>
                    <tr><td colspan='3'>MODX encountered the following error while attempting to parse the requested resource:</td></tr>
                    <tr><td colspan='3'><b style='color:red;'>&laquo; $msg &raquo;</b></td></tr>";
        } else {
            $str .= "<h3 style='color:#003399'>&laquo; MODX Debug/ stop message &raquo;</h3>
                    <table border='0' cellpadding='1' cellspacing='0'>
                    <tr><td colspan='3'>The MODX parser recieved the following debug/ stop message:</td></tr>
                    <tr><td colspan='3'><b style='color:#003399;'>&laquo; $msg &raquo;</b></td></tr>";
        }

        if (!empty ($query)) {
            $str .= "<tr><td colspan='3'><b style='color:#999;font-size: 12px;'>SQL:<span id='sqlHolder'>$query</span></b>
                    </td></tr>";
        }

        if ($text != '') {

            $errortype= array (
                E_ERROR => "Error",
                E_WARNING => "Warning",
                E_PARSE => "Parsing Error",
                E_NOTICE => "Notice",
                E_CORE_ERROR => "Core Error",
                E_CORE_WARNING => "Core Warning",
                E_COMPILE_ERROR => "Compile Error",
                E_COMPILE_WARNING => "Compile Warning",
                E_USER_ERROR => "User Error",
                E_USER_WARNING => "User Warning",
                E_USER_NOTICE => "User Notice",
                E_STRICT => "E_STRICT",
                E_RECOVERABLE_ERROR => "E_RECOVERABLE_ERROR",
                E_DEPRECATED => "E_DEPRECATED",
                E_USER_DEPRECATED => "E_USER_DEPRECATED"
            );

            $str .= "<tr><td colspan='3'></td></tr><tr><td colspan='3'><b>PHP error debug</b></td></tr>";

            $str .= "<tr><td valign='top'>Error: </td>";
            $str .= "<td colspan='2'>$text</td><td></td>";
            $str .= "</tr>";

            $str .= "<tr><td valign='top'>Error type/ Nr.: </td>";
            $str .= "<td colspan='2'>" . $errortype[$nr] . " - $nr</td><td></td>";
            $str .= "</tr>";

            $str .= "<tr><td>File: </td>";
            $str .= "<td colspan='2'>$file</td><td></td>";
            $str .= "</tr>";

            $str .= "<tr><td>Line: </td>";
            $str .= "<td colspan='2'>$line</td><td></td>";
            $str .= "</tr>";
            if ($source != '') {
                $str .= "<tr><td valign='top'>Line $line source: </td>";
                $str .= "<td colspan='2'>$source</td><td></td>";
                $str .= "</tr>";
            }
        }

        $str .= "<tr><td colspan='3'></td></tr><tr><td colspan='3'><b>Basic info</b></td></tr>";

        $str .= "<tr><td valign='top'>REQUEST_URI: </td>";
        $str .= "<td colspan='2'>$request_uri</td>";
        $str .= "</tr>";

        $str .= "<tr><td valign='top'>ID: </td>";
        $str .= "<td colspan='2'>" . $this->documentIdentifier . "</td>";
        $str .= "</tr>";

        if(!empty($this->currentSnippet))
        {
            $str .= "<tr><td>Current Snippet: </td>";
            $str .= '<td colspan="2">' . $this->currentSnippet . '</td>';
            $str .= "</tr>";
        }

        if(!empty($this->event->activePlugin))
        {
            $str .= "<tr><td>Current Plugin: </td>";
            $str .= '<td colspan="2">' . $this->event->activePlugin . '(' . $this->event->name . ')' . '</td>';
            $str .= "</tr>";
        }

        $str .= "<tr><td>Referer: </td>";
        $str .= '<td colspan="2">' . $referer . '</td>';
        $str .= "</tr>";

        $str .= "<tr><td>User Agent: </td>";
        $str .= '<td colspan="2">' . $ua . '</td>';
        $str .= "</tr>";

        $str .= '<tr><td colspan="2"></td></tr>';
        $str .= '<tr><td colspan="2"><b>Parser timing</b></td></tr>';

        $str .= "<tr><td>MySQL: </td>";
        $str .= '<td colspan="2"><i>[^qt^] ([^q^] Requests</i>)</td>';
        $str .= "</tr>";

        $str .= "<tr><td>PHP: </td>";
        $str .= '<td colspan="2"><i>[^p^]</i></td>';
        $str .= "</tr>";

        $str .= "<tr><td>Total: </td>";
        $str .= '<td colspan="2"><i>[^t^]</i></td>';
        $str .= "</tr>";

        $str .= "</table>";
        $str .= "</body></html>";

        $totalTime= ($this->getMicroTime() - $this->tstart);

        $mem = (function_exists('memory_get_peak_usage')) ? memory_get_peak_usage()  : memory_get_usage();
        $total_mem = $this->nicesize($mem - $this->mstart);
        
        $queryTime= $this->queryTime;
        $phpTime= $totalTime - $queryTime;
        $queries= isset ($this->executedQueries) ? $this->executedQueries : 0;
        $queryTime= sprintf("%2.4f s", $queryTime);
        $totalTime= sprintf("%2.4f s", $totalTime);
        $phpTime= sprintf("%2.4f s", $phpTime);

        $str= str_replace("[^q^]", $queries, $str);
        $str= str_replace("[^qt^]", $queryTime, $str);
        $str= str_replace("[^p^]", $phpTime, $str);
        $str= str_replace("[^t^]", $totalTime, $str);
        $str= str_replace("[^m^]", $total_mem, $str);

        if (isset($php_errormsg) && !empty($php_errormsg)) {
            $str = "<b>{$php_errormsg}</b><br />\n{$str}";
        }
        $str .= '<br />' . $this->get_backtrace(debug_backtrace());

        // Log error
        if ($source!=='') {
            $source = 'Parser - ' . $source;
        } else { 
            $source = 'Parser';
        }
        $this->logEvent(0, 3, $str, $source);
        if ($nr == E_DEPRECATED) {
            return true;
        }

        // Set 500 response header
        header('HTTP/1.1 500 Internal Server Error');

        // Display error
        if (isset($_SESSION['mgrValidated'])) {
            echo $str;
        } else {
            echo 'Error';
        }
        ob_end_flush();

        // Make sure and die!
        exit();
    } // messageQuit

    /**
     * Returns all registered JavaScripts
     *
     * @return string
     */
    public function getRegisteredClientScripts() {
        return implode("\n", $this->jscripts);
    } // getRegisteredClientScripts

    /**
     * Returns all registered startup scripts
     *
     * @return string
     */
    public function getRegisteredClientStartupScripts() {
        return implode("\n", $this->sjscripts);
    } // getRegisteredClientStartupScripts
    
    /**
     * Format alias to be URL-safe. Strip invalid characters.
     *
     * @param string Alias to be formatted
     * @return string Safe alias
     */
    function stripAlias($alias) {
        // let add-ons overwrite the default behavior
        $results = $this->invokeEvent('OnStripAlias', array ('alias'=>$alias));
        if (!empty($results)) {
            // if multiple plugins are registered, only the last one is used
            $result = end($results);
        } else {
            // default behavior: strip invalid characters and replace spaces with dashes.
            $alias = strip_tags($alias); // strip HTML
//          $alias = preg_replace('/[^\.A-Za-z0-9 _-]/', '', $alias); // strip non-alphanumeric characters
//          $alias = preg_replace('/\s+/', '-', $alias); // convert white-space to dash
//          $alias = preg_replace('/-+/', '-', $alias);  // convert multiple dashes to one
//          $alias = trim($alias, '-'); // trim excess
            $alias = urlencode($alias);
            $result = $alias;
        }

        return $result;
    } // stripAlias
    
    /**
     * Returns a nice readable size in B, KB, MB, GB, TB, or PB.
     *
     * @param int $size
     * @return float
     */
    public function nicesize($size) {
        $a = array(
            'B', 
            'KB', 
            'MB', 
            'GB', 
            'TB', 
            'PB'
        );
        $pos = 0;
        while ($size >= 1024) {
               $size /= 1024;
               $pos++;
        }

        return round($size,2)." ".$a[$pos];
    } // nicesize
    // End of class.
}

/**
 * SystemEvent Class
 * Function: This class does handle MODX system events
 *
 * @author  The MODX community
 * @todo    Set all class variables to private or protected
 * @name    SystemEvent
 * @package MODX
 */
class SystemEvent {
    /**
     * Name of the event
     * @var string
     */
    public $name;
    
    /**
     * Whether to propagate events, or not
     * @var boolean
     */
    public $_propagate;
    
    /**
     * Message output
     * @var string
     */
    public $_output;
    
    /**
     * Replaces $GLOBALS
     * @var array
     */
    public $_globalVariables;
    
    /**
     * Whether the event is active, or not
     * @var false
     */
    public $activated;
    
    /**
     * The name of the active plugin
     * @var string
     */
    public $activePlugin;

    /**
     * Constructor of SystemEvents, initializes the object
     *
     * @param string $name Name of the event
     */
    public function __construct($name= "") {
        $this->_resetEventObject();
        $this->name = $name;
    } // __construct

    /**
     * Used for displaying a message to the user
     *
     * @global array $SystemAlertMsgQueque
     * @param string $msg The message
     */
    public function alert($msg) {
        global $SystemAlertMsgQueque;

        if ($msg != '') {
            if (is_array($SystemAlertMsgQueque)) {
                if ($this->name && $this->activePlugin)
                    $title= "<div><b>" . $this->activePlugin . "</b> - <span style='color:maroon;'>" . $this->name . "</span></div>";
                $SystemAlertMsgQueque[]= "$title<div style='margin-left:10px;margin-top:3px;'>$msg</div>";
            }
        }
    } // alert

    /**
     * Used for rendering an out on the screen
     *
     * @param string $msg 
     */
    public function output($msg) {
        $this->_output .= $msg;
    } // output
    
    /**
     * Set $GLOBALS
     *
     * @param string $key
     * @param string $val
     * @param string $now
     */
    public function setGlobalVariable($key,$val,$now=0) {
        if (! isset( $GLOBALS[$key] ) ) {
            return false;
        }
        if ( $now === 1 || $now === 'now' ) {
            $GLOBALS[$key] = $val;
        } else {
            $this->_globalVariables[$key]=$val;
        }
        return true;
    }
    
    /**
     * Set all $GLOBALS
     */
    public function setAllGlobalVariables() {
        if (empty($this->_globalVariables)) {
            return false;
        }
        foreach ( $this->_globalVariables as $key => $val ) {
            $GLOBALS[$key] = $val;
        }
        return true;
    }

    /**
     * Sets _propagate to false
     */
    public function stopPropagation() {
        $this->_propagate = false;
    } // stopPropagation

    /**
     * Sets all class variables back to defaults 
     */
    public function _resetEventObject() {
        unset ($this->returnedValues);
        $this->name = '';
        $this->_output = '';
        $this->_globalVariables=array();
        $this->_propagate = true;
        $this->activated = false;
    } // _resetEventObject
} // SystemEvent
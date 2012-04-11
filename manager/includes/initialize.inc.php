<?php

// check PHP version. MODX Evolution is compatible with php 4 (4.4.2+)
if(version_compare(phpversion(), '4.4.2') < 0)
{
	echo sprintf($_lang['php_version_check'], phpversion());
	exit;
}

// automatically assign base_path and base_url
if(empty($base_path)) $base_path = assign_base_path();
if(empty($base_url))  $base_url  = assign_base_url();
if(empty($site_url))  $site_url  = assign_site_url($base_url);

if (!defined('MODX_BASE_PATH'))    define('MODX_BASE_PATH', $base_path);
if (!defined('MODX_BASE_URL'))     define('MODX_BASE_URL', $base_url);
if (!defined('MODX_SITE_URL'))     define('MODX_SITE_URL', $site_url);
if (!defined('MODX_MANAGER_PATH')) define('MODX_MANAGER_PATH', $base_path.'manager/');
if (!defined('MODX_MANAGER_URL'))  define('MODX_MANAGER_URL', $site_url.'manager/');

// start cms session
if(!function_exists('startCMSSession'))
{
	function startCMSSession()
	{
		global $site_sessionname;
		session_name($site_sessionname);
		session_start();
		$cookieExpiration= 0;
		if (isset ($_SESSION['mgrValidated']) || isset ($_SESSION['webValidated']))
		{
			$contextKey= isset ($_SESSION['mgrValidated']) ? 'mgr' : 'web';
			if (isset ($_SESSION['modx.' . $contextKey . '.session.cookie.lifetime']) && is_numeric($_SESSION['modx.' . $contextKey . '.session.cookie.lifetime']))
			{
				$cookieLifetime= intval($_SESSION['modx.' . $contextKey . '.session.cookie.lifetime']);
			}
			if ($cookieLifetime)
			{
				$cookieExpiration= time() + $cookieLifetime;
			}
			if (!isset($_SESSION['modx.session.created.time']))
			{
				$_SESSION['modx.session.created.time'] = time();
			}
		}
		setcookie(session_name(), session_id(), $cookieExpiration, MODX_BASE_URL);
	}
}

function assign_base_url()
{
	$conf_dir = str_replace("\\", '/', dirname(__FILE__));
	$docroot = get_DOCUMENT_ROOT();
	$conf_dir = substr($conf_dir,strlen($docroot));
	$mgr_pos = strlen($conf_dir) - strlen('manager/includes');
	return substr($conf_dir,0,$mgr_pos);
}

function assign_base_path()
{
	$conf_dir = str_replace("\\", '/', dirname(__FILE__));
	$mgr_pos = strlen($conf_dir) - strlen('manager/includes');
	return substr($conf_dir,0,$mgr_pos);
}

// assign site_url
function assign_site_url($base_url)
{
	if(is_https()) $scheme = 'https://';
	else                      $scheme = 'http://';
	
	$host = $_SERVER['HTTP_HOST'];
	
	$pos = strpos($host,':');
	if($pos!==false && ($_SERVER['SERVER_PORT'] == 80 || is_https()))
	{
		$host= substr($host,0,$pos);
	}
	return $scheme . $host . $base_url;
}

function is_https()
{
	global $https_port;
	if((isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') || $_SERVER['SERVER_PORT'] == $https_port)
	{
		return true;
	}
	else return false;
}

function get_DOCUMENT_ROOT()
{
	if(!isset($_SERVER['SCRIPT_NAME']) || !isset($_SERVER['SCRIPT_FILENAME']))
	{
		return false;
	}
	$pos = strlen($_SERVER['SCRIPT_FILENAME']) - strlen($_SERVER['SCRIPT_NAME']);
	$docroot = substr($_SERVER['SCRIPT_FILENAME'], 0, $pos);
	return str_replace("\\", '/',$docroot);
}

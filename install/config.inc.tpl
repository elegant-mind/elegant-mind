<?php
/**
 *	MODx Configuration file
 */
$database_type               = '[+database_type+]';
$database_server             = '[+database_server+]';
$database_user               = '[+database_user+]';
$database_password           = '[+database_password+]';
$database_connection_charset = '[+database_connection_charset+]';
$database_connection_method  = '[+database_connection_method+]';
$dbase                       = '`[+dbase+]`';
$table_prefix                = '[+table_prefix+]';

$lastInstallTime             = [+lastInstallTime+];
$site_sessionname            = '[+site_sessionname+]';
$https_port                  = '[+https_port+]';

error_reporting(E_ALL & ~E_NOTICE);
setlocale (LC_TIME, 'ja_JP.UTF-8');
if(function_exists('date_default_timezone_set')) date_default_timezone_set('Asia/Tokyo');

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
	$sapi= 'undefined';
	if (!strstr($_SERVER['PHP_SELF'], $_SERVER['SCRIPT_NAME']) && ($sapi= @ php_sapi_name()) == 'cgi')
	{
		$script_name= $_SERVER['PHP_SELF'];
	}
	else
	{
		$script_name= $_SERVER['SCRIPT_NAME'];
	}
	$a= explode('/manager', str_replace("\\", '/', dirname($script_name)));
	if (count($a) > 1)
	{
		array_pop($a);
	}
	$url= implode('manager', $a);
	return rtrim($url, '/') . '/';
}

function assign_base_path()
{
	$a= explode('manager', str_replace("\\", '/', dirname(__FILE__)));
	if (count($a) > 1)
	{
		array_pop($a);
	}
	$pth= implode('manager', $a);
	return rtrim($pth, '/') . '/';
}

// assign site_url
function assign_site_url($base_url)
{
	if((isset ($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') || $_SERVER['SERVER_PORT'] == $https_port)
	{
		$site_url = 'https://';
	}
	else
	{
		$site_url = 'http://';
	}
	$site_url .= $_SERVER['HTTP_HOST'];
	if ($_SERVER['SERVER_PORT'] != 80)
	{
		$site_url= str_replace(':' . $_SERVER['SERVER_PORT'], '', $site_url); // remove port from HTTP_HOST
	}
	if($_SERVER['SERVER_PORT'] == 80 || (isset ($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') || $_SERVER['SERVER_PORT'] == $https_port)
	{
		$site_url .= '';
	}
	else
	{
		$site_url .= $_SERVER['SERVER_PORT'];
	}
	return $site_url . $base_url;
}

<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

if (isset($_SESSION['mgrValidated']) && $_SESSION['usertype']!='manager')
{
	@session_destroy();
}

// andrazk 20070416 - if installer is running, destroy active sessions
$pth = dirname(__FILE__);
if (file_exists($pth.'/../../assets/cache/installProc.inc.php'))
{
	include_once($pth.'/../../assets/cache/installProc.inc.php');
	if (isset($installStartTime))
	{
		if ((time() - $installStartTime) > 5 * 60)
		{ // if install flag older than 5 minutes, discard
			unset($installStartTime);
			@ chmod($pth.'/../../assets/cache/installProc.inc.php', 0755);
			unlink($pth.'/../../assets/cache/installProc.inc.php');
		} 
		else
		{
			if ($_SERVER['REQUEST_METHOD'] != 'POST')
			{
				if (isset($_COOKIE[session_name()]))
				{
					session_unset();
					@session_destroy();
				}
				$installGoingOn = 1;
			}
		}
	}
}

// andrazk 20070416 - if session started before install and was not destroyed yet
if (isset($lastInstallTime) && isset($_SESSION['mgrValidated']))
{
	if (isset($_SESSION['modx.session.created.time']) && ($_SESSION['modx.session.created.time'] < $lastInstallTime))
	{
		if ($_SERVER['REQUEST_METHOD'] != 'POST')
		{
			if (isset($_COOKIE[session_name()]))
			{
				session_unset();
				@session_destroy();
			}
			header('HTTP/1.0 307 Redirect');
			header('Location: '.MODX_MANAGER_URL.'index.php?installGoingOn=2');
		}
	}
}

if(!isset($_SESSION['mgrValidated'])){
	if(isset($manager_language))
	{
		include_once "lang/english.inc.php";
		// include localized overrides
		include_once "lang/".$manager_language.".inc.php";
	}
	else
	{
		include_once "lang/english.inc.php";
	}


	$modx->setPlaceholder('modx_charset',$modx_manager_charset);
	$modx->setPlaceholder('theme',$manager_theme);

	global $tpl;
	// invoke OnManagerLoginFormPrerender event
	$evtOut = $modx->invokeEvent('OnManagerLoginFormPrerender');
	if(!isset($tpl) || empty($tpl))
	{
		// load template file
		$tplFile = MODX_BASE_PATH . 'assets/templates/manager/login.html';
		if(file_exists($tplFile)==false)
		{
			$tplFile = MODX_BASE_PATH . 'manager/media/style/' . $modx->config['manager_theme'] . '/manager/login.html';
		}
		$tpl = file_get_contents($tplFile);
	}
	
	$html = is_array($evtOut) ? implode('',$evtOut) : '';
	$modx->setPlaceholder('OnManagerLoginFormPrerender',$html);

	$modx->setPlaceholder('site_name',$site_name);
	$modx->setPlaceholder('logo_slogan',$_lang["logo_slogan"]);
	$modx->setPlaceholder('login_message',$_lang["login_message"]);

	// andrazk 20070416 - notify user of install/update
	if (isset($_GET['installGoingOn'])) {
		$installGoingOn = $_GET['installGoingOn'];
	}
	if (isset($installGoingOn)) {			
		switch ($installGoingOn) {
		 case 1 : $modx->setPlaceholder('login_message',"<p><span class=\"fail\">".$_lang["login_cancelled_install_in_progress"]."</p><p>".$_lang["login_message"]."</p>"); break;
		 case 2 : $modx->setPlaceholder('login_message',"<p><span class=\"fail\">".$_lang["login_cancelled_site_was_updated"]."</p><p>".$_lang["login_message"]."</p>"); break;
		}
	}

	if($use_captcha==1)  {
		$modx->setPlaceholder('login_captcha_message',$_lang["login_captcha_message"]);
		$modx->setPlaceholder('captcha_image','<a href="'.MODX_MANAGER_URL.'" class="loginCaptcha"><img id="captcha_image" src="../action.php?include=manager/media/captcha/veriword.php&rand='.rand().'" alt="'.$_lang["login_captcha_message"].'" /></a>');
		$modx->setPlaceholder('captcha_input','<label>'.$_lang["captcha_code"].'<input type="text" class="text" name="captcha_code" tabindex="3" value="" /></label>');
	}

	// login info
	$uid =  isset($_COOKIE['modx_remember_manager']) ? preg_replace('/[^a-zA-Z0-9\-_@\.]*/', '',  $_COOKIE['modx_remember_manager']) :''; 
	$modx->setPlaceholder('uid',$uid);
	$modx->setPlaceholder('username',$_lang["username"]);
	$modx->setPlaceholder('password',$_lang["password"]);

	// remember me
	$html =  isset($_COOKIE['modx_remember_manager']) ? 'checked="checked"' :'';
	$modx->setPlaceholder('remember_me',$html);
	$modx->setPlaceholder('remember_username',$_lang["remember_username"]);
	$modx->setPlaceholder('login_button',$_lang["login_button"]);
	
	// invoke OnManagerLoginFormRender event
	$evtOut = $modx->invokeEvent('OnManagerLoginFormRender');
	$html = is_array($evtOut) ? '<div id="onManagerLoginFormRender">'.implode('',$evtOut).'</div>' : '';
	$modx->setPlaceholder('OnManagerLoginFormRender',$html);

    // merge placeholders
    $tpl = $modx->parseDocumentSource($tpl);
    $regx = strpos($tpl,'[[+')!==false ? '~\[\[\+(.*?)\]\]~' : '~\[\+(.*?)\+\]~'; // little tweak for newer parsers
    $tpl = preg_replace($regx, '', $tpl); //cleanup

    echo $tpl;

    exit;

} else {
	// log the user action
	if ($cip = getenv("HTTP_CLIENT_IP"))
		$ip = $cip;
	elseif ($cip = getenv("HTTP_X_FORWARDED_FOR"))
		$ip = $cip;
	elseif ($cip = getenv("REMOTE_ADDR"))
		$ip = $cip;
	else	$ip = "UNKNOWN";
	
	$_SESSION['ip'] = $ip;

    $itemid = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : '';
	$lasthittime = time();
    $action = isset($_REQUEST['a']) ? (int) $_REQUEST['a'] : 1;

    if($action !== 1) {
		if (!intval($itemid)) $itemid= null;
		$sql = sprintf('REPLACE INTO %s (internalKey, username, lasthit, action, id, ip)
			VALUES (%d, \'%s\', \'%d\', \'%s\', %s, \'%s\')',
			$modx->getFullTableName('active_users'), // Table
			$modx->getLoginUserID(),
			$_SESSION['mgrShortname'],
			$lasthittime,
			(string)$action,
			$itemid == null ? var_export(null, true) : $itemid,
			$ip
		);
		if(!$rs = $modx->db->query($sql)) {
			echo "error replacing into active users! SQL: ".$sql."\n".mysql_error();
			exit;
		}
	}
}
?>
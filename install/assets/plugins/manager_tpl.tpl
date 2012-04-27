//<?php
/**
 * Custom Admin
 *
 * login screen,and dashboard
 *
 * @category 	plugin
 * @version 	1.0.1
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	@events OnManagerLoginFormPrerender,OnManagerWelcomePrerender
 * @internal	@modx_category Manager and Admin
 * @internal    @installset base
 */

// See chunk, "custom loginscreen" and "custom dashboard"
// When troubled, delete comment out
// manager/index.php $modx->safeMode = true;

switch($modx->event->name)
{
	case 'OnManagerLoginFormPrerender':
		$src = $modx->getChunk('custom loginscreen');
		break;
	case 'OnManagerWelcomePrerender':
		$src = $modx->getChunk('custom dashboard');
		break;
}
if($src!==false && !empty($src))
{
	global $tpl;
	$tpl = $src;
}

<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
$mxla = $modx_lang_attribute ? $modx_lang_attribute : 'en';

// invoke OnManagerRegClientStartupHTMLBlock event
$evtOut = $modx->invokeEvent('OnManagerMainFrameHeaderHTMLBlock');
$onManagerMainFrameHeaderHTMLBlock = is_array($evtOut) ? '<div id="onManagerMainFrameHeaderHTMLBlock">' . implode('', $evtOut) . '</div>' : '';

// load Placeholders array
$textdir = $modx_textdir ? ' dir="rtl"' : '';
$dirclass = $modx_textdir ? ' class="rtl"':'';
$doRefrsh =  isset($_REQUEST['r']) ? " doRefresh(".$_REQUEST['r'].");" : "";
$subject = array(
    'textdir'=>$textdir,
    'dirclass'=>$dirclass,
    'mxla'=>$mxla,
    'manager_charset'=>$modx_manager_charset,
    'manager_theme'=>$manager_theme,
    'ManagerMainFrameHeaderHTMLBlock'=>$onManagerMainFrameHeaderHTMLBlock,
    'doRefresh'=>$doRefresh,
    'lang_warning_not_saved'=>$_lang['warning_not_saved'],
    'lang_saving'=>$_lang['saving'],
    'style_ajax_loader'=>$_style['ajax_loader']
);
$modx->toPlaceholders($subject);

// load template file
$tplFile = 'media/style/'.$modx->config['manager_theme'].'/templates/header.html';
$handle = fopen($tplFile, "r");
$tpl = fread($handle, filesize($tplFile));
fclose($handle);

// merge placeholders
$tpl = $modx->mergePlaceholderContent($tpl);
$regx = strpos($tpl,'[[+')!==false ? '~\[\[\+(.*?)\]\]~' : '~\[\+(.*?)\+\]~'; // little tweak for newer parsers
$tpl = preg_replace($regx, '', $tpl); //cleanup

echo $tpl;

?>

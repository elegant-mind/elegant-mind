<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
?>
<style type="text/css">
h3 {font-weight:bold;letter-spacing:2px;font-size:1;margin-top:10px;}
h4 {font-weight:bold;letter-spacing:2px;}
pre {border:1px dashed #ccc;background-color:#fcfcfc;padding:15px;}
ul {margin-bottom:15px;}
td {vertical-align:top;padding-bottom:7px;}
</style>

<div class="sectionHeader">Information</div>
<div class="sectionBody" style="padding:10px 20px;">
<h3>Information</h3>
<p>
<a href="http://forum.modx.com/" target="_blank">support forum</a><br />
<a href="index.php?a=114">see event logs</a>
</p>
<?php


$info = array(
              'OS'  => php_uname('s') . ' ' . php_uname('r') . ' ' . php_uname('v') . ' ' . php_uname('m'),
              'PHP version' => PHP_VERSION,
              'Safe mode'  => (ini_get('safe_mode') ==0) ? 'off' : 'on',
              'php_sapi_name'  => php_sapi_name(),
              'MySQL version'=>mysql_get_server_info(),
              'MySQL host info' => mysql_get_host_info(),
//              'mysql_get_client_info' => mysql_get_client_info(),
              'MODX version' => $modx_version,
              'Site URL'  => $modx->config['site_url'],
              'Host name' => gethostbyaddr(getenv('SERVER_ADDR')),
              'MODX_BASE_URL' => MODX_BASE_URL,
              'Size of siteCache' => $modx->nicesize(filesize(MODX_BASE_PATH . 'assets/cache/siteCache.idx.php')),
              'upload_tmp_dir' => ini_get('upload_tmp_dir'),
              'memory_limit' => ini_get('memory_limit'),
              'post_max_size' => ini_get('post_max_size'),
              'upload_max_filesize' => ini_get('upload_max_filesize'),
              'max_execution_time' => ini_get('max_execution_time'),
              'max_input_time' => ini_get('max_input_time') . 'sec',
              'session.save_path' => ini_get('session.save_path'),
              );

echo '<p>'.getenv('SERVER_SOFTWARE') .'</p>'. PHP_EOL . PHP_EOL;

echo '<table style="margin-bottom:20px;">';
foreach($info as $key=>$value)
{
    echo '<tr><td style="padding-right:30px;">' . $key . '</td><td>' . $value . '</td></tr>' . PHP_EOL;
}
echo '</table>' . PHP_EOL;



echo '<h4>mbstring</h4>' . PHP_EOL . PHP_EOL;
echo '<table style="margin-bottom:20px;">';
$mbstring_array = array('mbstring.detect_order',
'mbstring.encoding_translation',
'mbstring.func_overload',
'mbstring.http_input',
'mbstring.http_output',
'mbstring.internal_encoding',
'mbstring.language',
'mbstring.strict_detection',
'mbstring.substitute_character');

foreach($mbstring_array as $v)
{
    $key = $v;
    $value = ini_get($v)!==false ? ini_get($v): 'no value';
    echo '<tr><td style="padding-right:30px;">' . $key . '</td><td>' . $value . '</td></tr>' . PHP_EOL;
}
echo '</table>' . PHP_EOL;

//Mysql char set
echo '<h4>MySQL collation info</h4>' . PHP_EOL . PHP_EOL;
echo '<table style="margin-bottom:20px;">';
$res = $modx->db->query("SHOW VARIABLES LIKE 'collation_database';");
$collation = $modx->db->getRow($res, 'num');
global $database_connection_method;
echo '<tr><td style="padding-right:30px;">conection method</td><td>' . $database_connection_method . '</td></tr>' . PHP_EOL;
echo '<tr><td style="padding-right:30px;">collation</td><td>' . $collation[1] . '</td></tr>' . PHP_EOL;
$rs = $modx->db->query("SHOW VARIABLES LIKE 'char%';");
while ($row = $modx->db->getRow($rs)){
  echo '<tr><td style="padding-right:30px;">' . $row['Variable_name'] . '</td><td>' . $row['Value'] . '</td></tr>' . PHP_EOL;
}
echo '</table>' . PHP_EOL;

?>
<h3>More description</h3>
<p>
<a href="index.php?a=200">phpinfo</a><br />
<a href="index.php?a=200#module_mbstring">mbstring</a><br />
<a href="index.php?a=200#module_gd">GD / FreeType</a>
</p>
</div>

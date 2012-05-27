<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
if(!$modx->hasPermission('view_eventlog')) {	
	$e->setError(3);
	$e->dumpError();	
}

// get id
if(isset($_REQUEST['id'])) {
	$id = intval($_REQUEST['id']);
}
else {
	$id=0;
}

// make sure the id's a number
if(!is_numeric($id)) {
	echo "Passed ID is NaN!";
	exit;
}


$sql = "SELECT el.*, IFNULL(wu.username,mu.username) as 'username' " .
		"FROM ".$modx->getFullTableName("event_log")." el ".
		"LEFT JOIN ".$modx->getFullTableName("manager_users")." mu ON mu.id=el.user AND el.usertype=0 ".
		"LEFT JOIN ".$modx->getFullTableName("web_users")." wu ON wu.id=el.user AND el.usertype=1 ".
		" WHERE el.id=$id";
$ds = $modx->db->query($sql);
if(!$ds) {
	echo "Error while load event log";
	exit;
}
else{
	$content = $modx->db->getRow($ds);	
}

?>

	<h1><?php echo $_lang['eventlog']; ?></h1>

<div id="actions">
	<ul class="actionButtons">
		<li id="Button4"><a href="index.php?a=114"><img src="<?php echo $_style["icons_cancel"] ?>" /> <?php echo $_lang['cancel']; ?></a></li>
<?php if($modx->hasPermission('delete_eventlog')) { ?>
		<li id="Button3"><a href="#" onclick="deletelog();"><img src="<?php echo $_style["icons_delete_document"] ?>" /> <?php echo $_lang['delete']; ?></a></li>
<?php } ?>
	</ul>
</div>

<script language="JavaScript" type="text/javascript">
	function deletelog() {
		if(confirm("<?php echo $_lang['confirm_delete_eventlog']; ?>")==true) {
			document.location.href="index.php?id=" + document.resource.id.value + "&a=116";
		}
	}
</script> 

<form name="resource" method="get">
<input type="hidden" name="id" value="<?php echo $id; ?>" />
<input type="hidden" name="a" value="<?php echo (int) $_REQUEST['a']; ?>" />
<input type="hidden" name="listmode" value="<?php echo $_REQUEST['listmode']; ?>" />
<input type="hidden" name="op" value="" />
<div class="sectionHeader"><?php echo $content['source']." - ".$_lang['eventlog_viewer']; ?></div><div class="sectionBody">
<?php
$date = $modx->toDateFormat($content["createdon"]);
if($content["type"]==1) $msgtype = $_lang["information"];
else if($content["type"]==2) $msgtype = $_lang["warning"];
else if($content["type"]==3) $msgtype = $_lang["error"];
$useTheme = $manager_theme ? "$manager_theme/":"";
if(empty($content["username"])) $content["username"] = '-';
echo <<<HTML
	<div class="warning"><img src="media/style/{$useTheme}images/icons/event{$content["type"]}.png" align="absmiddle" /> {$msgtype}</div>
	<table>
	<tr><td>{$_lang["event_id"]} </td><td>{$content["eventid"]}</td></tr>
	<tr><td>{$_lang["source"]} </td><td>{$content["source"]}</td></tr>
	<tr><td>{$_lang["date"]} </td><td>$date</td></tr>
	<tr><td>{$_lang["user"]} </td><td>{$content["username"]}</td></tr>
	</table>
	<div>{$content["description"]}</div>
HTML;
?>
</div>
</form>

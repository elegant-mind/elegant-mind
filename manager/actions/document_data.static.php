<?php
if (IN_MANAGER_MODE!='true') die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

if (isset($_REQUEST['id']))
        $id = (int)$_REQUEST['id'];
else    $id = 0;

if (isset($_GET['opened'])) $_SESSION['openedArray'] = $_GET['opened'];

// Get table names (alphabetical)
$tbl_document_groups       = $modx->getFullTableName('document_groups');
$tbl_keyword_xref          = $modx->getFullTableName('keyword_xref');
$tbl_manager_users         = $modx->getFullTableName('manager_users');
$tbl_site_content          = $modx->getFullTableName('site_content');
$tbl_site_content_metatags = $modx->getFullTableName('site_content_metatags');
$tbl_site_keywords         = $modx->getFullTableName('site_keywords');
$tbl_site_metatags         = $modx->getFullTableName('site_metatags');
$tbl_site_templates        = $modx->getFullTableName('site_templates');

// Get access permissions
if($_SESSION['mgrDocgroups']) $docgrp = implode(",",$_SESSION['mgrDocgroups']);
$in_docgrp = !$docgrp ? '':" OR dg.document_group IN ({$docgrp})";
$access = "1='{$_SESSION['mgrRole']}' OR sc.privatemgr=0 {$in_docgrp}";


// Get the document content
$sql = 'SELECT DISTINCT sc.* '.
       "FROM {$tbl_site_content} AS sc ".
       "LEFT JOIN {$tbl_document_groups} AS dg ON dg.document = sc.id ".
       "WHERE sc.id ='{$id}' ".
       "AND ({$access})";
$rs = mysql_query($sql);
$limit = mysql_num_rows($rs);
if ($limit > 1) {
	echo "<p>Internal System Error...</p>",
	     "<p>More results returned than expected. </p>",
	     "<p><strong>Aborting...</strong></p>";
	exit;
} elseif ($limit == 0) {
	$e->setError(3);
	$e->dumpError();
}
$content = mysql_fetch_assoc($rs);

/**
 * "General" tab setup
 */
// Get Creator's username
$rs = mysql_query("SELECT username FROM {$tbl_manager_users} WHERE id='{$content['createdby']}'");
if ($row = mysql_fetch_assoc($rs))
	$createdbyname = $row['username'];

// Get Editor's username
$rs = mysql_query("SELECT username FROM {$tbl_manager_users} WHERE id='{$content['editedby']}'");
if ($row = mysql_fetch_assoc($rs))
	$editedbyname = $row['username'];

// Get Template name
$rs = mysql_query("SELECT templatename FROM {$tbl_site_templates} WHERE id='{$content['template']}'");
if ($row = mysql_fetch_assoc($rs))
	$templatename = $row['templatename'];

// Set the item name for logging
$_SESSION['itemname'] = $content['pagetitle'];

$keywords = array();
$metatags_selected = array();
if ($modx->config['show_meta'])
{
	// Get list of current keywords for this document
	$sql = "SELECT k.keyword FROM {$tbl_site_keywords} AS k, {$tbl_keyword_xref} AS x ".
	       "WHERE k.id = x.keyword_id AND x.content_id = '{$id}' ".
	       "ORDER BY k.keyword ASC";
	$rs = mysql_query($sql);
	while($row = mysql_fetch_assoc($rs))
	{
		$keywords[$i] = $row['keyword'];
	}
	
	// Get list of selected site META tags for this document
	$sql = "SELECT meta.id, meta.name, meta.tagvalue ".
	       "FROM {$tbl_site_metatags} AS meta ".
	       "LEFT JOIN {$tbl_site_content_metatags} AS sc ON sc.metatag_id = meta.id ".
	       "WHERE sc.content_id='{$content['id']}'";
	$rs = mysql_query($sql);
	while($row = mysql_fetch_assoc($rs))
	{
		$metatags_selected[] = $row['name'].': <i>'.$row['tagvalue'].'</i>';
	}
}

/**
 * "View Children" tab setup
 */
$maxpageSize = $modx->config['number_of_results'];
define('MAX_DISPLAY_RECORDS_NUM',$maxpageSize);

if (!class_exists('makeTable')) include_once $modx->config['base_path'].'manager/includes/extenders/maketable.class.php';
$childsTable = new makeTable();

// Get child document count
$sql = "SELECT DISTINCT sc.id ".
       "FROM {$tbl_site_content} AS sc ".
       "LEFT JOIN {$tbl_document_groups} AS dg ON dg.document = sc.id ".
       "WHERE sc.parent='{$content['id']}' ".
       "AND ({$access})";
$rs = $modx->db->query($sql);
$numRecords = $modx->db->getRecordCount($rs);

// Get child documents (with paging)
$sql = 'SELECT DISTINCT sc.* '.
       "FROM {$tbl_site_content} AS sc ".
       "LEFT JOIN {$tbl_document_groups} AS dg ON dg.document = sc.id ".
       "WHERE sc.parent='{$content['id']}' ".
       "AND ({$access}) ".
       "ORDER BY sc.isfolder DESC, sc.menuindex DESC".
       $childsTable->handlePaging(); // add limit clause

if ($numRecords > 0) {
	if (!$rs = $modx->db->query($sql)) {
		// sql error
		$e->setError(1);
		$e->dumpError();
		include($modx->config['base_path'].'manager/includes/footer.inc.php');
		exit;
	} else {
		$resource = array();
		while($row = $modx->fetchRow($rs))
		{
			$resource[] = $row;
		}

		// CSS style for table
		$tableClass = 'grid';
		$rowHeaderClass = 'gridHeader';
		$rowRegularClass = 'gridItem';
		$rowAlternateClass = 'gridAltItem';

		$childsTable->setTableClass($tableClass);
		$childsTable->setRowHeaderClass($rowHeaderClass);
		$childsTable->setRowRegularClass($rowRegularClass);
		$childsTable->setRowAlternateClass($rowAlternateClass);

		// Table header
		$listTableHeader = array(
			'checkbox' =>    '<input type="checkbox" name="chkselall" onclick="selectAll()" />',
			'docid' =>    $_lang['id'],
			'title' =>    $_lang['resource_title'],
			'editedon' => $_lang['editedon'],
			'status' =>   $_lang['page_data_status']
		);
		$tbWidth = array('2%','3%', '52%', '15%', '8%', '20%');
		$childsTable->setColumnWidths($tbWidth);

		$limitClause = $childsTable->handlePaging();
		
		// context menu
		include_once MODX_MANAGER_PATH .'includes/controls/contextmenu.php';
		$cm = new ContextMenu("cntxm", 150);
		// $cm->addSeparator();
		$cm->addItem($_lang["edit_resource"],       "js:menuAction(27)",$_style['icons_edit_document'],($modx->hasPermission('edit_document') ? 0:1));
		//$cm->addItem($_lang["create_resource_here"],"js:menuAction(4)",$_style['icons_new_document'],($modx->hasPermission('new_document') ? 0:1));
		$cm->addItem($_lang["move_resource"],       "js:menuAction(51)",$_style['icons_move_document'],($modx->hasPermission('save_document') ? 0:1));
		//$cm->addItem($_lang["resource_duplicate"],  "js:menuAction(94)",$_style['icons_resource_duplicate'],($modx->hasPermission('new_document') ? 0:1));
		//$cm->addSeparator();
		//$cm->addItem($_lang["publish_resource"],   "js:menuAction(61)",$_style['icons_publish_document'],($modx->hasPermission('publish_document') ? 0:1));
		//$cm->addItem($_lang["unpublish_resource"], "js:menuAction(62)",$_style['icons_unpublish_resource'],($modx->hasPermission('publish_document') ? 0:1));
		$cm->addItem($_lang["delete_resource"],    "js:menuAction(6)",$_style['icons_delete'],($modx->hasPermission('delete_document') ? 0:1));
		$cm->addItem($_lang["undelete_resource"],  "js:menuAction(63)",$_style['icons_undelete_resource'],($modx->hasPermission('delete_document') ? 0:1));
		//$cm->addSeparator();
		//$cm->addItem($_lang["create_weblink_here"], "js:menuAction(72)",$_style['icons_weblink'],($modx->hasPermission('new_document') ? 0:1));
		//$cm->addSeparator();
		$cm->addItem($_lang["resource_overview"], "js:menuAction(3)",$_style['icons_resource_overview'],($modx->hasPermission('view_document') ? 0:1));
		//$cm->addItem($_lang["preview_resource"], "js:menuAction(999)",$_style['icons_preview_resource'],0);
		echo $cm->render();
?>
<script type="text/javascript">
	var selectedItem;
	var contextm = <?php echo $cm->getClientScriptObject(); ?>;
	function showContentMenu(id,e){
		selectedItem=id;
		contextm.style.left = (e.pageX || (e.clientX + (document.documentElement.scrollLeft || document.body.scrollLeft)))<?php echo $modx_textdir ? '-190' : '';?>+"px"; //offset menu if RTL is selected
		contextm.style.top = (e.pageY || (e.clientY + (document.documentElement.scrollTop || document.body.scrollTop)))+"px";
		contextm.style.visibility = "visible";
		e.cancelBubble=true;
		return false;
	};

	function menuAction(a) {
		var id = selectedItem;
		switch(a) {
			case 27:		// run module
				dontShowWorker = true; // prevent worker from being displayed
				window.location.href='index.php?a=27&id='+id;
				break;
			case 3:		// edit
				window.location.href='index.php?a=3&id='+id;
				break;
			case 51:		// duplicate
				window.location.href='index.php?a=51&id='+id;
				break;
			case 6:		// delete
				if(confirm("<?php echo $_lang['confirm_delete_resource']; ?>")==true) {
					window.location.href='index.php?a=6&id='+id;
				}
				break;
			case 63:		// undelete
				window.location.href='index.php?a=63&id='+id;
				break;
		}
	}

	document.addEvent('click', function(){
		contextm.style.visibility = "hidden";
	});
</script>
<?php
		$listDocs = array();
		foreach($resource as $k => $children)
		{
			if($children['published'] == 0)
			{
				$status = '<span class="unpublishedDoc">'.$_lang['page_data_unpublished'].'</span>';
			}
			else
			{
				$status = '<span class="publishedDoc">'.$_lang['page_data_published'].'</span>';
			}
			$description = $children['description'];
			$len_title = mb_strlen($children['pagetitle'], 'UTF-8');
			$len_desc  = mb_strlen($description, 'UTF-8');
			$len_total = $len_title + $len_desc;
			if($len_total < 50)
			{
				if(!empty($description)) $description = ' <span style="color:#777;">' . $description . '</span>';
			}
			else
			{
				$description = '<br /><div style="color:#777;">' . $description . '</div>';
			}
			
			$classes = array();
			if($children['deleted']==='1') $classes[] = 'deletedNode';
			if($children['published']==='0') $classes[] = 'unpublishedNode';
			if(0<count($classes)) $class = ' class="' . join(' ',$classes) . '"';
			else $class = '';
			
			$tpl = '<span [+class+] oncontextmenu="document.getElementById(\'icon[+id+]\').onclick(event);return false;">[+pagetitle+]</span>';
			$pagetitle = str_replace(array('[+class+]','[+pagetitle+]','[+id+]'),
			                         array($class,$children['pagetitle'],$children['id']),$tpl);
			
			if($children['isfolder'] == 0)
			{
				$link = 'index.php?a=27&amp;id=' . $children['id'];
				$iconpath = $_style['tree_page'];
			}
			else
			{
				$link = "index.php?a=3&amp;id={$children['id']}&amp;tab=0";
				$iconpath = $_style['icons_folder'];
			}
			
			if( $children['type']==='reference')
			{
				$pagetitle = '<img src="' . $_style['tree_weblink'] . '" /> ' . $pagetitle;
			}
			$tpl = '';
			$tpl = '<img src="[+iconpath+]" id="icon[+id+]" onclick="return showContentMenu([+id+],event);" />';
			$icon = str_replace(array('[+iconpath+]','[+id+]'),array($iconpath,$children['id']),$tpl);
			$tpl = '<div style="float:left;">[+icon+]</div><a href="[+link+]" style="overflow:auto;display:block;color:#333;">[+pagetitle+][+$description+]</a>';
			$title = str_replace(array('[+icon+]','[+link+]','[+pagetitle+]','[+$description+]'),
			                     array($icon,$link,$pagetitle,$description), $tpl);
			
			$listDocs[] = array(
				'checkbox' =>    '<input type="checkbox" name="batch[]" value="' . $children['id'] . '" />',
				'docid'    => $children['id'],
				'title'    => $title,
				'editedon' => '<span style="white-space:nowrap;">' . $modx->toDateFormat($children['editedon']) . '</span>',
				'status'   => $status
			);
		}
		$childsTable->createPagingNavigation($numRecords,'a=3&amp;id='.$content['id'] . '&amp;tab=0');
		$children_output = $childsTable->create($listDocs,$listTableHeader,'index.php?a=3&amp;id='.$content['id'] . '&amp;tab=0');
	}
}
else
{
	// No Child documents
	$children_output = "<p>".$_lang['resources_in_container_no']."</p>";
}

?>
<script type="text/javascript">
function duplicatedocument(){
	if(confirm("<?php echo $_lang['confirm_resource_duplicate']?>")==true) {
		document.location.href="index.php?id=<?php echo $_REQUEST['id']?>&a=94";
	}
}
function deletedocument() {
	if(confirm("<?php echo $_lang['confirm_delete_resource']?>")==true) {
		document.location.href="index.php?id=<?php echo $_REQUEST['id']?>&a=6";
	}
}
function editdocument() {
	document.location.href="index.php?id=<?php echo $_REQUEST['id']?>&a=27";
}
function movedocument() {
	document.location.href="index.php?id=<?php echo $_REQUEST['id']?>&a=51";
}
</script>
<script type="text/javascript" src="media/script/tabpane.js"></script>
<script type="text/javascript" src="media/script/tablesort.js"></script>

	<h1><?php echo $_lang['doc_data_title']?></h1>
	
	<div id="actions">	
	  <ul class="actionButtons">
		  <li id="Button1">
			<a href="#" onclick="editdocument();"><img src="<?php echo $_style["icons_edit_document"] ?>" /> <?php echo $_lang['edit']?></a>
		  </li>
		  <li id="Button2">
			<a href="#" onclick="movedocument();"><img src="<?php echo $_style["icons_move_document"] ?>" /> <?php echo $_lang['move']?></a>
		  </li>
		  <li id="Button4">
		    <a href="#" onclick="duplicatedocument();"><img src="<?php echo $_style["icons_resource_duplicate"] ?>" /> <?php echo $_lang['duplicate']?></a>
		  </li>
		  <li id="Button3">
		    <a href="#" onclick="deletedocument();"><img src="<?php echo $_style["icons_delete_document"] ?>" /> <?php echo $_lang['delete']?></a>
		  </li>
          <li id="Button5"><a href="#" onclick="documentDirty=false;<?php
          	 if(isset($content['parent']) && $content['parent']!=='0')
          	 {
          		echo "document.location.href='index.php?a=3&id={$content['parent']}&tab=0';";
          	 }
          	 elseif($_GET['pid'])
          	 {
          	 	$_GET['pid'] = intval($_GET['pid']);
          		echo "document.location.href='index.php?a=3&id={$_GET['pid']}&tab=0';";
          	 }
          	 else
          	 {
          		echo "document.location.href='index.php?a=2';";
          	 }
          	?>"><img alt="icons_cancel" src="<?php echo $_style["icons_cancel"] ?>" /> <?php echo $_lang['cancel']?></a></li>
		  <li id="Button6">
			<a href="#" onclick="<?php echo ($modx->config['friendly_urls'] == '1') ? "window.open('".$modx->makeUrl($id)."','previeWin')" : "window.open('../index.php?id=$id','previeWin')"; ?>"><img src="<?php echo $_style["icons_preview_resource"]?>" /> <?php echo $_lang['preview']?></a>
		  </li>
	  </ul>
	</div>

<div class="sectionHeader"><?php echo $_lang['page_data_title']?></div>
<div class="sectionBody">

<div class="tab-pane" id="childPane">
	<script type="text/javascript">
	docSettings = new WebFXTabPane( document.getElementById( "childPane" ), <?php echo $modx->config['remember_last_tab'] == 0 ? 'false' : 'true'; ?> );
	</script>

	<!-- View Children -->
	<div class="tab-page" id="tabChildren">
		<h2 class="tab"><?php echo $_lang['view_child_resources_in_container']?></h2>
		<script type="text/javascript">docSettings.addTabPage( document.getElementById( "tabChildren" ) );</script>
<?php if ($modx->hasPermission('new_document')) { ?>
	
			<ul class="actionButtons">
				<li><a href="index.php?a=4&amp;pid=<?php echo $content['id']?>"><img src="<?php echo $_style["icons_new_document"]; ?>" align="absmiddle" /> <?php echo $_lang['create_resource_here']?></a></li>
				<li><a href="index.php?a=72&amp;pid=<?php echo $content['id']?>"><img src="<?php echo $_style["icons_new_weblink"]; ?>" align="absmiddle" /> <?php echo $_lang['create_weblink_here']?></a></li>
			</ul>
<?php }
	if ($numRecords > 0)
		echo '<p><span class="publishedDoc">'.$numRecords.'</span> '.$_lang['resources_in_container'].' (<strong>'.$content['pagetitle'].'</strong>)</p>'."\n";
		echo <<< EOT
<script type="text/javascript">
	function selectAll() {
		var f = document.forms['mutate'];
		var c = f.elements['batch[]'];
		for(i=0;i<c.length;i++){
			c[i].checked=f.chkselall.checked;
		}
	}
</script>
<form name="mutate" id="mutate" class="content" method="post" enctype="multipart/form-data" action="index.php">
<input type="hidden" name="a" value="51" />
{$children_output}
<input type="submit" value="チェックしたリソースを移動" />
</form>
EOT;
?>
	</div><!-- end tab-page -->
<style type="text/css">
h3 {font-size:1em;padding-bottom:0;margin-bottom:0;}
</style>
	<!-- General -->
	<div class="tab-page" id="tabdocInfo">
		<h2 class="tab"><?php echo $_lang['information']?></h2>
		<script type="text/javascript">docSettings.addTabPage( document.getElementById( "tabdocInfo" ) );</script>
		<div class="sectionBody">
		<h3><?php echo $_lang['page_data_general']?></h3>
		<table>
			<tr><td width="200"><?php echo $_lang['resource_title']?>: </td>
				<td><b><?php echo $content['pagetitle']?></b></td></tr>
			<tr><td><?php echo $_lang['long_title']?>: </td>
				<td><small><?php echo $content['longtitle']!='' ? $content['longtitle'] : "(<i>".$_lang['not_set']."</i>)"?></small></td></tr>
			<tr><td><?php echo $_lang['resource_description']?>: </td>
				<td><?php echo $content['description']!='' ? $content['description'] : "(<i>".$_lang['not_set']."</i>)"?></td></tr>
			<tr><td><?php echo $_lang['resource_summary']?>: </td>
				<td><?php echo $content['introtext']!='' ? $content['introtext'] : "(<i>".$_lang['not_set']."</i>)"?></td></tr>
			<tr><td><?php echo $_lang['type']?>: </td>
				<td><?php echo $content['type']=='reference' ? $_lang['weblink'] : $_lang['resource']?></td></tr>
			<tr><td><?php echo $_lang['resource_alias']?>: </td>
				<td><?php echo $content['alias']!='' ? urldecode($content['alias']) : "(<i>".$_lang['not_set']."</i>)"?></td></tr>
			<?php if ($modx->config['show_meta']) {?>
			<tr><td><?php echo $_lang['keywords']?>: </td>
				<td><?php // Keywords
				if(count($keywords) != 0)
					echo join($keywords, ", ");
				else    echo "(<i>".$_lang['not_set']."</i>)";
				?></td></tr>
			<tr><td><?php echo $_lang['metatags']?>: </td>
				<td><?php // META Tags
				if(count($metatags_selected) != 0)
					echo join($metatags_selected, "<br /> ");
				else    echo "(<i>".$_lang['not_set']."</i>)";
				?></td></tr>
			<?php } ?>
			</table>
			<table>
			<tr><td colspan="2"><h3><?php echo $_lang['page_data_changes']?></h3></td></tr>
			<tr><td width="200"><?php echo $_lang['page_data_created']?>: </td>
				<td><?php echo $modx->toDateFormat($content['createdon']+$server_offset_time)?> (<b><?php echo $createdbyname?></b>)</td></tr>
<?php				if ($editedbyname != '') { ?>
			<tr><td><?php echo $_lang['page_data_edited']?>: </td>
				<td><?php echo $modx->toDateFormat($content['editedon']+$server_offset_time)?> (<b><?php echo $editedbyname?></b>)</td></tr>
<?php				} ?>
		</table>
		<table>
			<tr><td colspan="2"><h3><?php echo $_lang['page_data_status']?></h3></td></tr>
			<tr><td width="200"><?php echo $_lang['page_data_status']?>: </td>
				<td><?php echo $content['published']==0 ? '<span class="unpublishedDoc">'.$_lang['page_data_unpublished'].'</span>' : '<span class="publisheddoc">'.$_lang['page_data_published'].'</span>'?></td></tr>
			<tr><td><?php echo $_lang['page_data_publishdate']?>: </td>
				<td><?php echo $content['pub_date']==0 ? "(<i>".$_lang['not_set']."</i>)" : $modx->toDateFormat($content['pub_date'])?></td></tr>
			<tr><td><?php echo $_lang['page_data_unpublishdate']?>: </td>
				<td><?php echo $content['unpub_date']==0 ? "(<i>".$_lang['not_set']."</i>)" : $modx->toDateFormat($content['unpub_date'])?></td></tr>
			<tr><td><?php echo $_lang['page_data_cacheable']?>: </td>
				<td><?php echo $content['cacheable']==0 ? $_lang['no'] : $_lang['yes']?></td></tr>
			<tr><td><?php echo $_lang['page_data_searchable']?>: </td>
				<td><?php echo $content['searchable']==0 ? $_lang['no'] : $_lang['yes']?></td></tr>
			<tr><td><?php echo $_lang['resource_opt_menu_index']?>: </td>
				<td><?php echo $content['menuindex']?></td></tr>
			<tr><td><?php echo $_lang['resource_opt_show_menu']?>: </td>
				<td><?php echo $content['hidemenu']==1 ? $_lang['no'] : $_lang['yes']?></td></tr>
			<tr><td><?php echo $_lang['page_data_web_access']?>: </td>
				<td><?php echo $content['privateweb']==0 ? $_lang['public'] : '<b style="color: #821517">'.$_lang['private'].'</b> <img src="' . $style_path .'icons/secured.gif" align="absmiddle" width="16" height="16" />'?></td></tr>
			<tr><td><?php echo $_lang['page_data_mgr_access']?>: </td>
				<td><?php echo $content['privatemgr']==0 ? $_lang['public'] : '<b style="color: #821517">'.$_lang['private'].'</b> <img src="' . $style_path . 'icons/secured.gif" align="absmiddle" width="16" height="16" />'?></td></tr>
		</table>
		<table>
			<tr><td colspan="2"><h3><?php echo $_lang['page_data_markup']?></h3></td></tr>
			<tr><td width="200"><?php echo $_lang['page_data_template']?>: </td>
				<td><?php echo $templatename ?></td></tr>
			<tr><td><?php echo $_lang['page_data_editor']?>: </td>
				<td><?php echo $content['richtext']==0 ? $_lang['no'] : $_lang['yes']?></td></tr>
			<tr><td><?php echo $_lang['page_data_folder']?>: </td>
				<td><?php echo $content['isfolder']==0 ? $_lang['no'] : $_lang['yes']?></td></tr>
		</table>
		</div><!-- end sectionBody -->
	</div><!-- end tab-page -->

	<!-- Page Source -->
	<div class="tab-page" id="tabSource">
		<h2 class="tab"><?php echo $_lang['page_data_source']?></h2>
		<script type="text/javascript">docSettings.addTabPage( document.getElementById( "tabSource" ) );</script>
		<?php
		$buffer = "";
		$filename = $modx->config['base_path']."assets/cache/docid_".$id.".pageCache.php";
		$handle = @fopen($filename, "r");
		if(!$handle) {
			$buffer = $_lang['page_data_notcached'];
		} else {
			while (!feof($handle)) {
				$buffer .= fgets($handle, 4096);
			}
			fclose($handle);
			$buffer = $_lang['page_data_cached'].'<p><textarea style="width: 100%; height: 400px;">'.htmlspecialchars($buffer)."</textarea>\n";
		}
		echo $buffer;
?>
	</div><!-- end tab-page -->
</div><!-- end documentPane -->
</div><!-- end sectionBody -->

<?php if ($show_preview==1) { ?>
<div class="sectionHeader"><?php echo $_lang['preview']?></div>
<div class="sectionBody" id="lyr2">
	<iframe src="../index.php?id=<?php echo $id?>&z=manprev" frameborder="0" border="0" id="previewIframe"></iframe>
</div>
<?php }

<?php
require_once "framework/database/connect-cloud.php";
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
require_once "framework/security/default.php";

$T		= $_GET['T'];
$D		= $_GET['D'];
$K		= $_GET['K'];	

$query_col 		= "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '".$D."' AND TABLE_NAME = '".$T."'";
$result_col		= mysql_query($query_col);
$query_coll 	= "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '".$D."' AND TABLE_NAME = '".$T."'";
$result_coll	= mysql_query($query_coll);
$coll			= mysql_fetch_array($result_coll);
$query_up		= "SELECT * FROM ".$D.".".$T." WHERE ".$coll[0]."='".$K."'";
$result_up		= mysql_query($query_up);
$row_coll		= mysql_num_fields($result_up);
$row_up			= mysql_fetch_array($result_up);

if(isset($_POST['submit'])){
	
	$arrayData	= $_POST;	
	$query="SELECT * FROM ".$D.".".$T." ";
	$result=mysql_query($query);
		print_r($result);
	for($i=0;$i<mysql_num_fields($result);$i++){
    	$field_info=mysql_fetch_field($result,$i);
 		$fieldNames[]="$field_info->name";    
 		$fieldTypes[]="$field_info->type";
	} 
	$query ="INSERT INTO ".$D.".".$T;
	$query.= " VALUES ( ";

	for($i=0; $i<count($fieldNames); $i++){
		foreach($arrayData as $key => $data) {
			if($key == $fieldNames[$i]) {
				if(($fieldNames=='')&&($fieldTypes[$i]!='string')){
						$query.="NULL,";
					}
					elseif($fieldTypes[$i]=='int'){
			        		$query.=$data.",";
		    	    }elseif($fieldTypes[$i]=='timestamp'){
							$query.=$data.",";
					}else{
							$query.="'".$data."',";
		        	}
			}
		}
	}
	
	$query = rtrim($query, "',");
	$query.="'); ";
	$result = mysql_query($query);
	header("Location: cloud-list-all.php?T=".$T."&&D=".$D);
	}

if(isset($_POST['update'])){
	$arrayData	= $_POST;
	
	$query="SELECT * FROM ".$D.".".$T." ";
	$result=mysql_query($query);
	for($i=0;$i<mysql_num_fields($result);$i++){
    	$field_info=mysql_fetch_field($result,$i);
 		$fieldNames[]="$field_info->name";    
 		$fieldTypes[]="$field_info->type";
	}

	$query_coll 	= "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '".$D."' AND TABLE_NAME = '".$T."'";
	$result_coll	= mysql_query($query_coll);
	$coll			= mysql_fetch_array($result_coll);
	
	$query	= "UPDATE ".$D.".".$T." SET ";
	
	for($i=0; $i<count($fieldNames); $i++){
		foreach($arrayData as $key => $data) {
			if($key == $fieldNames[$i]) {
				$query.=$fieldNames[$i]."=";
				if(($fieldNames=='')&&($fieldTypes[$i]!='string')){
						$query.="NULL,";
					}elseif($fieldTypes[$i]=='int'){
			        		$query.=$data.",";
		    	    }elseif($fieldTypes[$i]=='timestamp'){
							$query.=$data.",";
					}else{
							$query.="'".$data."',";
		        	}
			}
		}
	}
		
	$query = rtrim($query, ",");	
	$query.=" WHERE ".$coll[0]."='".$K." '; ";
	$result = mysql_query($query);
	print_r ($query);
	header("Location: cloud-list-all.php?T=".$T."&&D=".$D);	
	}
	
	if(isset($_GET['delete_id'])){
		$query_coll 	= "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '".$D."' AND TABLE_NAME = '".$T."'";
		$result_coll	= mysql_query($query_coll);
		$coll			= mysql_fetch_array($result_coll);
		
		$sql_query="DELETE FROM $D.$T WHERE $coll[0]='".$_GET['delete_id']."'";
		$result = mysql_query($sql_query);
		header("Location: cloud-list-all.php?T=".$T."&&D=".$D);
	}

?>



<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/pagination/pagination.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/combobox/chosen.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">

	<script type="text/javascript" src="framework/liveSearch/livesearch.js"></script>
	<script type="text/javascript" src="framework/jquery/jquery-latest.min.js"></script>
	<script type="text/javascript" src="framework/pagination/pagination.js"></script>
	<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>
	<script type="text/javascript" src="framework/uri/src/URI.min.js"></script>
	<script type="text/javascript" src="framework/functions/default.js"></script>
	<script type="text/javascript" src='framework/jquery-freezeheader/js/jquery.freezeheader.js'></script>
	<script type="text/javascript" src="framework/combobox/chosen.jquery.js"></script>
	<script type="text/javascript" src="framework/combobox/chosen.default.js"></script>
	
	<script>
	parent.document.getElementById('addressDeleteYes').onclick=
	function () { 
		parent.document.getElementById('content').src='cloud-list-all-edit.php?delete_id=<?php echo $_GET['K']?>&&T=<?php echo $_GET['T']?>&&D=<?php echo $_GET['D']?>';
		parent.document.getElementById('addressDelete').style.display='none';
		parent.document.getElementById('fade').style.display='none'; 
	};
	</script>
</head>

<body>
<?php
	if (!empty($K)){
?>
		<div class="toolbar-only">
		<p class="toolbar-left">
			<a href="javascript:void(0)" onclick = "window.scrollTo(0,0);parent.document.getElementById('addressDelete').style.display='block';parent.document.getElementById('fade').style.display='block'"><span class="fa fa-trash toolbar" style="cursor:pointer" alt="Delete" /></span></a>
		</p>
		</div>
		
<?php
		}
?>
		<form method="post" action="#" >
		<input type="hidden" name="table" value="cloud">
			<?php
				if (!empty($K)){
				$i=0;
				while($col= mysql_fetch_array($result_col)) {
			?>
			<?php
				if($col['COLUMN_TYPE']!='timestamp'){
					if($col['COLUMN_KEY']=='PRI') {
			?>
			
			<label class="side"  style="color:#000;"><?php echo $col['COLUMN_NAME']?></label>
			<input id="<?php echo $col['COLUMN_NAME']; ?>" name="<?php echo $col['COLUMN_NAME']; ?>" value="<?php echo $row_up[$i];?>" type="text" style="width:300px;" readonly="readonly">
			<br/>
			<?php
				}else{
			?>
			
			<label class="side" style="color:#000;"><?php echo $col['COLUMN_NAME']?></label>
			<input id="<?php echo $col['COLUMN_NAME']; ?>" name="<?php echo $col['COLUMN_NAME']; ?>" value="<?php echo $row_up[$i];?>" type="text" style="width:300px;"/>
			<br/>
			<?php
				}}else {
			?>			
			<input id="<?php echo $col['COLUMN_NAME']; ?>" name="<?php echo $col['COLUMN_NAME']; ?>" value="CURRENT_TIMESTAMP" type="hidden" style="width:300px; color:#000;"/>
			<?php
			}
			?>
			<?php
			$i++;
				}
			?>
			<input name="update" class="process" type="submit" value="Update"/>
			<?php
				} else {
			$i=0;
			while($col= mysql_fetch_array($result_col)) {	
			?>
			<?php
			if($col['COLUMN_TYPE']!='timestamp'){
			?>
					<label class="side" style="color:#000"><?php echo $col['COLUMN_NAME']?></label>
					<input id="<?php echo $col['COLUMN_NAME']; ?>" name="<?php echo $col['COLUMN_NAME']; ?>" value="<?php echo $row_up[$i];?>" type="text" style="width:300px;"/>
					</p>
			<?php
				}else {
			?>			
					<input id="<?php echo $col['COLUMN_NAME']; ?>" name="<?php echo $col['COLUMN_NAME']; ?>" value="CURRENT_TIMESTAMP" type="hidden" style="width:300px;"/>
			<?php
				}
			?>
			<?php
			$i++;
				}
			?>
				<input name="submit" class="process" type="submit" value="Save"/>
			<?php
				}
			?>
			<br/>
		</form>			
</body>
</html>			

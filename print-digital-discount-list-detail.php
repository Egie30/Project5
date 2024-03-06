<?php
	include "framework/database/connect-cloud.php";
	include "framework/functions/default.php";
	$PlanTyp=$_GET['PLAN_TYP'];
	$SchedId=$_GET['SCHED_ID'];
	$changed=false;
	$addNew=false;
	//Process changes here
	if($_POST['SCHED_ID']!="")
	{
		$j=downTable("PRN_DIG_VOL_SCHED","SCHED_ID","CMP",$CMP,$local,$cloud);

		$SchedId=$_POST['SCHED_ID'];
		//Take care of nulls
		if($_POST['PLAN_TYP']==""){$PlanTyp="NULL";}else{$PlanTyp=$_POST['PLAN_TYP'];}
		if($_POST['DISC_AMT']==""){$DiscAmt="NULL";}else{$DiscAmt=$_POST['DISC_AMT'];}
		if($_POST['MIN_Q']==""){$MinQ="NULL";}else{$MinQ=$_POST['MIN_Q'];}
		if($_POST['MAX_Q']==""){$MaxQ="NULL";}else{$MaxQ=$_POST['MAX_Q'];}
		
		//Process add new
		if($SchedId==-1)
		{
			$addNew=true;
			$query="SELECT COALESCE(MAX(SCHED_ID),0)+1 AS NEW_NBR FROM $CMP.PRN_DIG_VOL_SCHED";
			$result=mysql_query($query, $cloud);
			$row=mysql_fetch_array($result);
			$SchedId=$row['NEW_NBR'];
	
			$query="INSERT INTO $CMP.PRN_DIG_VOL_SCHED (SCHED_ID) VALUES (".$SchedId.")";
			$result = mysql_query($query, $cloud);
			$query = str_replace($CMP, "CMP", $query);
			$result = mysql_query($query, $local);
		}
		
		$query="UPDATE $CMP.PRN_DIG_VOL_SCHED
	   			SET PLAN_TYP='".$PlanTyp."',
					DISC_AMT=".$DiscAmt.",
					MIN_Q=".$_POST['MIN_Q'].",
					MAX_Q=".$_POST['MAX_Q']."
					WHERE SCHED_ID=".$SchedId;
		//echo $query;
	   	$result = mysql_query($query, $cloud);
		$query = str_replace($CMP, "CMP", $query);
		$result = mysql_query($query, $local);
		
	   	$changed=true;
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">


<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" type="text/css" media="screen" href="css/popup.css" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
<script type="text/javascript" src="framework/functions/default.js"></script>

<script type="text/javascript">
	function getInt(objectID)
	{
		if(document.getElementById(objectID).value=="")
		{
			return 0;
		}else{
			return parseInt(document.getElementById(objectID).value);
		}
	}
	function getFloat(objectID)
	{
		if(document.getElementById(objectID).value=="")
		{
			return 0;
		}else{
			return parseFloat(document.getElementById(objectID).value);
		}
	}

</script>

</head>

<body>

<?php
	if($changed){
		echo "<script>";
		echo "parent.document.getElementById('content').contentDocument.getElementById('refresh-list').click();";
		echo "parent.document.getElementById('content').contentDocument.getElementById('refresh-tot').click();";
		echo "</script>";
	}
	if($addNew){$SchedId=0;}
?>
<div style="height:480px; overflow:auto">
<span class='fa fa-times toolbar' style='margin-left:10px' onclick="pushFormOut();"></span>
<?php
	$query="SELECT SCHED_ID,
				   PLAN_TYP,
				   DISC_AMT,
				   MIN_Q,
				   MAX_Q
			FROM CMP.PRN_DIG_VOL_SCHED
			WHERE SCHED_ID=".$SchedId;
	//echo $query;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
?>

<script>
	parent.document.getElementById('content').contentDocument.getElementById('refresh-list').click();
</script>

<form enctype="multipart/form-data" action="#" method="post" style="width:450px;" onSubmit="return checkform();">
<table>
		<tr>
			<td>Plan Type</td>
			<td><input readonly name="PLAN_TYP" id="PLAN_TYP"  value="<?php echo $PlanTyp; ?>" type="text" style="width:300px" /></td>
		</tr>
		<tr>
			<td><input name="SCHED_ID" id="SCHED_ID" value="<?php echo $row['SCHED_ID'];if($row['SCHED_ID']==""){echo "-1"; $addNew=true; } ?>"  type="hidden" style="width:300px" /></td>
		</tr>
		
		<tr>
			<td>Discount (Rp)</td>
			<td><input id="DISC_AMT" name="DISC_AMT" value="<?php echo $row['DISC_AMT']; ?>" type="text" style="width:300px;" /></td>
		</tr>
		<tr>
			<td>Minimal Quantity</td>
			<td><input id="MIN_Q" name="MIN_Q" value="<?php echo $row['MIN_Q']; ?>" type="text" style="width:100px;"/></td>
		</tr>
		<tr>
			<td>Maximal Quantity</td>
			<td><input id="MAX_Q" name="MAX_Q" value="<?php echo $row['MAX_Q']; ?>" type="text" style="width:100px;"/></td>
		</tr>
		
	</table>
	<br />
	<input class="process" type="submit" value="<?php if($addNew){echo 'Tambah';}else{echo 'Simpan';} ?>"/>
</form>
	
</div>
</body>
</html>


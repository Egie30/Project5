<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/functions/dotmatrix.php";
	
	if($_GET['DEL_A']!="")
	{
		$query="UPDATE RTL.INV_AUD SET DEL_NBR=".$_SESSION['personNBR']." WHERE AUD_TS='".$_GET['DEL_A']." ".$_GET['DEL_B']."'"; //echo $query;
		$result=mysql_query($query);
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />

<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />
<script src="framework/database/jquery.min.js"></script>

<script type="text/javascript" src="framework/tablesorter/jquery-latest.js"></script>
<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>	
</head>
<body>
<?php
if($_GET['COM']=='EDT'){
$CrtTs=$_GET['CRT_TS_DT'].' '.$_GET['CRT_TS'];
$InvBcd=$_GET['INV_BCD'];

if($CrtTs==''){$CrtTs=$_POST['CRT_TS_DT'].' '.$_POST['CRT_TS'];};
if($InvBcd==""){$InvBcd=$_POST['INV_BCD'];}
if(strlen($InvBcd)<8){$InvBcd=LeadZero($InvBcd,8);}

if($_GET['NEW']==1){ 
	if($_POST['CRT_TS']=='-1'){	
		$query="INSERT INTO RTL.INV_AUD (PRSN_NBR,AUD_Q) VALUES (".$_SESSION['personNBR'].",1);";
		mysql_query($query);//echo $query;
		$query="SELECT MAX(AUD_TS) AUD_TS FROM RTL.INV_AUD;";
		$result=mysql_query($query);//echo $query;
		$row=mysql_fetch_array($result);//echo $query;
		$CrtTs=$row['AUD_TS'];	
		$Qty=1;		
	}
	if($_POST['CRT_TS']!='-1'){$CrtTs=$_POST['CRT_TS_DT'].' '.$_POST['CRT_TS'];$Qty=mysql_real_escape_string($_POST['AUD_Q']);}
	$query="UPDATE RTL.INV_AUD SET 
			INV_BCD='".$InvBcd."', 
			AUD_Q='".$Qty."' 
			WHERE AUD_TS='".$CrtTs."' "; //echo $query;
			mysql_query($query);
}
	$query="SELECT AUD.INV_BCD,AUD_Q,INV_NBR,INV.NAME NAME,DATE(AUD.AUD_TS) CRT_TS_DATE,TIME(AUD.AUD_TS) CRT_TS FROM RTL.INV_AUD AUD 
			LEFT OUTER JOIN RTL.INVENTORY INV ON INV.INV_BCD= AUD.INV_BCD 
			WHERE AUD.AUD_TS='".$CrtTs."' AND AUD.INV_BCD='".$InvBcd."'"; //echo $query;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
?>
<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
<script>
	parent.document.getElementById('invAudDeleteYes').onclick=
	function () { 
		parent.document.getElementById('content').src='inventory-audit.php?DEL_A=<?php echo $row['CRT_TS_DATE']."&DEL_B=".$row['CRT_TS']; ?>';
		parent.document.getElementById('invAudDelete').style.display='none';
		parent.document.getElementById('fade').style.display='none'; 
	};
</script>
<div class="toolbar-only">
<p class="toolbar-left"><a href="javascript:void(0)" onclick="window.scrollTo(0,0);parent.document.getElementById('invAudDelete').style.display='block';parent.document.getElementById('fade').style.display='block'"><span class='fa fa-trash toolbar' style="cursor:pointer"></span></a></p>
</div>
<form enctype="multipart/form-data" action="inventory-audit.php?COM=EDT&NEW=1" method="post" style="width:600px" onSubmit="return checkform();" id="signup">
	<p>
		<h2>
			Tanggal <?php if($CrtTs=="0 0"){echo "Baru";}else{echo $row['CRT_TS_DATE'];} ?>
		</h2>

		<input name="CRT_TS_DT" id="CRT_TS_DT" value="<?php echo $row['CRT_TS_DATE'];if($row['CRT_TS_DATE']==""){echo "-1";} ?>" type="hidden" />
		<input name="CRT_TS" id="CRT_TS" value="<?php echo $row['CRT_TS'];if($row['CRT_TS']==""){echo "-1";} ?>" type="hidden" />
		<label>Barcode</label><br />
		<input name="INV_BCD" id="INV_BCD" value="<?php echo $row['INV_BCD'];?>" type="text" />
		<input name="INV_NBR" id="INV_NBR" value="<?php echo $row['INV_NBR'];?>" type="hidden" />
		<br/>
		<label>Deskripsi</label><br />
		<input name="INV_NM" id="INV_NM" value="<?php echo $row['NAME'];?>" type="text" readonly /><br />
		<label>Jumlah</label><br />
		<input name="AUD_Q" value="<?php echo $row['AUD_Q']; ?>" type="text" size="20" /><br />
		<input class="process" type="submit" value="Simpan"/>
		</p>
	</form>
<?php
}else{	
?>
<div class="toolbar">
	<p class="toolbar-left"><a href="inventory-audit.php?COM=EDT&CRT_TS_DT=0&CRT_TS=0"><span class='fa fa-plus toolbar' style="cursor:pointer" onclick="location.href="></span></a></p>
</div>

<div id="mainResult">
	<table id="mainTable" class="tablesorter searchTable">
		<thead>
			<tr>
				<th>Tanggal</th>
				<th>Jumlah</th>
				<th>Jenis</th>
				<th>SPG</th>
				<th>Mulai</th>
				<th>Selesai</th>
			</tr>
		</thead>
		<tbody>
		<?php
		$query=mysql_query("SELECT DATE(AUD_TS) AS AUD_DT,SUM(AUD_Q) AS VOL,COUNT(DISTINCT INV_BCD) NBR_TYP,PPL.NAME AS NBR_PRSN,AUD.DEL_NBR,
				MIN(TIME(AUD_TS)) AS BEG_TM,MAX(TIME(AUD_TS)) AS END_TM FROM RTL.INV_AUD AUD 
				LEFT OUTER JOIN CMP.PEOPLE PPL ON PPL.PRSN_NBR=AUD.PRSN_NBR WHERE AUD.DEL_NBR=0 GROUP BY DATE(AUD_TS) ORDER BY 1 DESC");
		$alt="";
		while($row=mysql_fetch_array($query)){
			echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='inventory-audit-result.php?AUD_DT=".$row['AUD_DT']."';".chr(34).">";
			echo "<td style='text-align:center'>".$row['AUD_DT']."</td>";
			echo "<td style='text-align:center'>".number_format($row['VOL'],0,".",",")."</td>";
			echo "<td style='text-align:center'>".number_format($row['NBR_TYP'],0,".",",")."</td>";
			echo "<td style='text-align:center'>".$row['NBR_PRSN']."</td>";
			echo "<td style='text-align:center'>".$row['BEG_TM']."</td>";
			echo "<td style='text-align:center'>".$row['END_TM']."</td>";
			echo "</tr>";
		}
			
		?>
		</tbody>
		</table>
	</div>

<script>
	$(document).ready(function()
		{
			$("#mainTable").tablesorter({ widgets:["zebra"]});  
		}
	);
</script>
<?php } ?>
</body>
</html>			

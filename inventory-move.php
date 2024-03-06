<?php
include "framework/database/connect.php";
include "framework/functions/default.php";
include "framework/security/default.php";

$Security=getSecurity($_SESSION['userID'],"DigitalPrint");
$UpperSec=getSecurity($_SESSION['userID'],"Executive");
$CashSec=getSecurity($_SESSION['userID'],"Finance");

if($_GET['DEL_A']!=""){
	$query="UPDATE RTL.INV_MOV SET DEL_NBR=".$_SESSION['personNBR']." WHERE CRT_TS='".$_GET['DEL_A']." ".$_GET['DEL_B']."'";
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
if($_GET['COM']	== 'EDT'){
$CrtTs			= $_GET['CRT_TS_DT'].' '.$_GET['CRT_TS'];
$DetNbr			= $_GET['DET_NBR'];
$CurrDate 		= new DateTime("now");

if($CrtTs==''){
	$CrtTs		= $_POST['CRT_TS_DT'].' '.$_POST['CRT_TS'];
};
if($DetNbr==''){
	$DetNbr		= $_POST['ORD_DET_NBR'];
};

if($_GET['NEW']==1){
	if($_POST['CRT_TS']=='-1'){	
		$query="INSERT INTO RTL.INV_MOV (CRT_NBR,MOV_Q) VALUES (".$_SESSION['personNBR'].",1);";
		$result	= mysql_query($query);//echo $query;
		
		$query	= "SELECT MAX(CRT_TS) CRT_TS FROM RTL.INV_MOV;";
		$result	= mysql_query($query);//echo $query;
		$row	= mysql_fetch_array($result);//echo $query;
		$CrtTs	= $row['CRT_TS'];		
	}

	if($_POST['CRT_TS']!='-1'){$CrtTs=$_POST['CRT_TS_DT'].' '.$_POST['CRT_TS'];}

	$query_det	= "SELECT 
		(CASE 
			WHEN DET.INV_PRC != 0 THEN DET.INV_PRC * COALESCE(ORD_X,1) * COALESCE(ORD_Y,1) * COALESCE(ORD_Z,1)
			ELSE DET.FEE_MISC * COALESCE(ORD_X,1) * COALESCE(ORD_Y,1) * COALESCE(ORD_Z,1)	
		END) DET_INV_PRC 
	FROM RTL.RTL_STK_DET DET 
	WHERE DET.ORD_DET_NBR = '".$DetNbr."' ";
			
	$result_det	= mysql_query($query_det);	
	$row_det	= mysql_fetch_array($result_det);
	$DetInvPrice= $row_det['DET_INV_PRC'];
	
	$query="UPDATE RTL.INV_MOV SET 
		ORD_DET_NBR='".$DetNbr."', 
		DET_INV_PRC='".$DetInvPrice."', 
		MOV_Q='".mysql_real_escape_string($_POST['MOV_Q'])."' 
	WHERE CRT_TS='".$CrtTs."' "; //echo $query;
	mysql_query($query);
}

$query="SELECT
	INV.INV_NBR,
	INV.NAME NAME, 
	DATE(MOV.CRT_TS) CRT_TS_DATE,
	TIME(MOV.CRT_TS) CRT_TS,
	MOV_Q, PPL.NAME STAF,
	MOV.ORD_DET_NBR ORD_DET_NBR 
FROM RTL.INV_MOV MOV 
	LEFT OUTER JOIN RTL.RTL_STK_DET DET ON MOV.ORD_DET_NBR=DET.ORD_DET_NBR 
	LEFT OUTER JOIN RTL.INVENTORY INV ON INV.INV_NBR=DET.INV_NBR
	LEFT OUTER JOIN CMP.PEOPLE PPL ON PPL.PRSN_NBR=MOV.CRT_NBR 
WHERE MOV.CRT_TS='".$CrtTs."' AND MOV.ORD_DET_NBR='".$DetNbr."'";
$result=mysql_query($query);
$row=mysql_fetch_array($result);
?>
<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
<script>
	parent.document.getElementById('invMoveDeleteYes').onclick=
	function () { 
		parent.document.getElementById('content').src='inventory-move.php?DEL_A=<?php echo $row['CRT_TS_DATE']."&DEL_B=".$row['CRT_TS']; ?>';
		parent.document.getElementById('invMoveDelete').style.display='none';
		parent.document.getElementById('fade').style.display='none'; 
	};
</script>
<script type="text/javascript">
	function checkform(){
		if((document.getElementById('MOV_Q').value=="")||(document.getElementById('MOV_Q').value <=0)){
			window.scrollTo(0,0);
			parent.document.getElementById('jumBlank').style.display='block';parent.document.getElementById('fade').style.display='block';
			return false;
		}
		return true;
	}
</script>
<script>
$(document).ready(function(){
	$("#ORD_DET_NBR").keyup(function(){
		var ORD_DET_NBR=$(this).val();
		if(ORD_DET_NBR != ''){
			$.ajax({
				type:"POST",
				url:"inventory-move-detail-ls.php",
				data:"ORD_DET_NBR="+ORD_DET_NBR,
				datatype:"json",
				success:function(data){
					var rep = /"/gi;
					var a = data.replace(rep,'');
					$("#INV_NM").val(a);
				}
			});
		}else{
			$("#INV_NM").val("");
		}
	});
});
</script>
<div class="toolbar-only">
<?php if($Security==0) { ?>
<p class="toolbar-left">
	<a href="javascript:void(0)" onclick="window.scrollTo(0,0);parent.document.getElementById('invMoveDelete').style.display='block';parent.document.getElementById('fade').style.display='block'"><span class='fa fa-trash toolbar' style="cursor:pointer"></span></a>
</p>
<?php } ?>
</div>
<form enctype="multipart/form-data" action="inventory-move.php?COM=EDT&NEW=1" method="post" style="width:600px" onSubmit="return checkform();" id="signup">
	<p>
		<h2>
			Tanggal <?php if($CrtTs=="0 0"){echo "Baru";}else{echo $row['CRT_TS_DATE'];} ?>
		</h2>

		<input name="CRT_TS_DT" id="CRT_TS_DT" value="<?php echo $row['CRT_TS_DATE'];if($row['CRT_TS']==""){echo "-1";} ?>" type="hidden" />
		<input name="CRT_TS" id="CRT_TS" value="<?php echo $row['CRT_TS'];if($row['CRT_TS']==""){echo "-1";} ?>" type="hidden" />
		<label>Nomor</label><br />
		<input name="ORD_DET_NBR"  id="ORD_DET_NBR" type="text" style="width: 200px;" value="<?php echo $row['ORD_DET_NBR'];?>" /></input>
		<br/>
		<label>Deskripsi</label><br />
		<input name="INV_NM" id="INV_NM" style="width: 500px;" value="<?php echo $row['NAME'];?>" type="text" readonly />
		<input name="INV_NBR" id="INV_NBR" value="<?php echo $row['INV_NBR'];?>" type="hidden" />
		<br/>
		<label>Jumlah</label><br />
		<input id="MOV_Q" name="MOV_Q" style="width: 100px;" value="<?php if (isset($row['MOV_Q'])){echo $row['MOV_Q'];}else{ echo "1";} ?>" type="text" size="20" /><br />
		<?php
		if($CrtTs != 0 && $row['CRT_TS_DATE'] != date('Y-m-d') && $Security > 0){ $style="style='display: none;'"; }?>
		<input class="process" type="submit" value="Simpan" <?php echo $style; ?>/>
		</p>
	</form>
<?php
}else if($_GET['COM']=="DET"){	
?>
<div class="toolbar">
	<p class="toolbar-left"><span class='fa fa-arrow-left toolbar' style="cursor:pointer" onclick="window.history.back()"></span></p>
</div>
<div id="mainResult">
	<table id="mainTable" class="tablesorter searchTable">
		<thead>
			<tr>
				<th>Check Out</th>
				<th>SPG</th>
				<th>Waktu</th>
			</tr>
		</thead>
		<tbody>
		<?php
		$query="SELECT 
					INV.INV_NBR,
					INV.NAME NAME,
					DATE(MOV.CRT_TS) CRT_TS_DATE,
					TIME(MOV.CRT_TS) CRT_TS, 
					MOV_Q, 
					PPL.NAME STAF,
					MOV.ORD_DET_NBR ORD_DET_NBR 
				FROM RTL.INV_MOV MOV 
					LEFT OUTER JOIN RTL.RTL_STK_DET DET ON MOV.ORD_DET_NBR=DET.ORD_DET_NBR 
					LEFT OUTER JOIN RTL.INVENTORY INV ON INV.INV_NBR=DET.INV_NBR
					LEFT OUTER JOIN CMP.PEOPLE PPL ON PPL.PRSN_NBR=MOV.CRT_NBR 
				WHERE DATE(MOV.CRT_TS)='".$_GET['CRT_TS']."' AND INV.INV_NBR='".$_GET['INV_NBR']."' AND INV.INV_NBR='".$_GET['INV_NBR']."' AND MOV.DEL_NBR=0";
		$result=mysql_query($query);
		$alt="";
		while($row=mysql_fetch_array($result)){
			echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='inventory-move.php?COM=EDT&CRT_TS_DT=".$row['CRT_TS_DATE']."&CRT_TS=".$row['CRT_TS']."&DET_NBR=".$row['ORD_DET_NBR']."';".chr(34).">";
			echo "<td style='text-align:center'>".number_format($row['MOV_Q'],0,".",",")."</td>";
			echo "<td>".$row['STAF']."</td>";
			echo "<td style='text-align:center'>".$row['CRT_TS']."</td>";
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
<?php }else  if($_GET['COM']=="RES"){	
?>
<div class="toolbar">
	<p class="toolbar-left"><span class='fa fa-arrow-left toolbar' style="cursor:pointer" onclick="window.history.back()"></span></p>
</div>

<div id="mainResult">
	<table id="mainTable" class="tablesorter searchTable">
		<thead>
			<tr>
				<th>No</th>
				<th>Kategory</th>
				<th>Sub Kategory</th>
				<th>Nama</th>
				<th>Supplier</th>
				<th>Barcode</th>
				<th>Jumlah</th>
			</tr>
		</thead>
		<tbody>
		<?php
		$query="SELECT 
					INV.INV_NBR,
					CAT_DESC,
					CAT_SUB_DESC,
					INV.NAME NAME,
					COM.NAME SUP,
					INV_BCD,
					DATE(MOV.CRT_TS) CRT_TS, 
					SUM(MOV_Q) MOV_Q, 
					MOV.CRT_NBR,
					PPL.NAME STAF,
					MOV.ORD_DET_NBR ORD_DET_NBR 
				FROM RTL.INV_MOV MOV 
					LEFT OUTER JOIN RTL.RTL_STK_DET DET ON MOV.ORD_DET_NBR=DET.ORD_DET_NBR 
					LEFT OUTER JOIN RTL.INVENTORY INV ON INV.INV_NBR=DET.INV_NBR
					LEFT OUTER JOIN RTL.CAT CAT ON CAT.CAT_NBR=INV.CAT_NBR
					LEFT OUTER JOIN RTL.CAT_SUB SUB ON SUB.CAT_SUB_NBR=INV.CAT_SUB_NBR
					LEFT OUTER JOIN CMP.COMPANY COM ON COM.CO_NBR=INV.CO_NBR
					LEFT OUTER JOIN CMP.PEOPLE PPL ON PPL.PRSN_NBR=MOV.CRT_NBR 
				WHERE MOV.DEL_NBR=0 AND DATE(MOV.CRT_TS) = '". $_GET['AUD_DT'] ."' AND MOV.CRT_NBR = '". $_GET['CRT_NBR'] ."'
				GROUP BY DATE(MOV.CRT_TS),INV.INV_NBR";
		$result=mysql_query($query);
		$alt="";
		while($row=mysql_fetch_array($result)){
			echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='inventory-move.php?COM=DET&CRT_TS=".$row['CRT_TS']."&INV_NBR=".$row['INV_NBR']."&CRT_NBR=".$row['CRT_NBR']."';".chr(34).">";
			echo "<td style='text-align:center'>".$row['INV_NBR']."</td>";
			echo "<td>".$row['CAT_DESC']."</td>";
			echo "<td>".$row['CAT_SUB_DESC']."</td>";
			echo "<td>".$row['NAME']."</td>";
			echo "<td style='text-align:center'>".$row['SUP']."</td>";
			echo "<td style='text-align:center'>".$row['INV_BCD']."</td>";
			echo "<td style='text-align:center'>".number_format($row['MOV_Q'],0,".",",")."</td>";
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
<?php
}else{	
?>
<div class="toolbar">
	<p class="toolbar-left">
		<a href="inventory-move.php?COM=EDT&CRT_TS_DT=0&CRT_TS=0">
			<span class='fa fa-plus toolbar' style="cursor:pointer" onclick="location.href="></span>
		</a>
	</p>
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
		$alt="";
		if($_GET['POS_ID'] != ""){
			$where = "AND YEAR(MOV.CRT_TS) >= 2023";
		}
		$query="SELECT 
			DATE(MOV.CRT_TS) AS AUD_DT,
			SUM(MOV_Q) AS VOL,
			COUNT(DISTINCT DET.INV_NBR) AS INV_NBR_TYP,
			MOV.CRT_NBR,
			PPL.NAME AS NBR_PRSN,
			MIN(TIME(MOV.CRT_TS)) AS BEG_TM,
			MAX(TIME(MOV.CRT_TS)) AS END_TM 
		FROM RTL.INV_MOV MOV 
			LEFT OUTER JOIN RTL.RTL_STK_DET DET ON DET.ORD_DET_NBR=MOV.ORD_DET_NBR 
			LEFT OUTER JOIN CMP.PEOPLE PPL ON PPL.PRSN_NBR=MOV.CRT_NBR 
		WHERE MOV.DEL_NBR=0 ". $where ."
		GROUP BY DATE(MOV.CRT_TS),MOV.CRT_NBR 
		ORDER BY MOV.CRT_TS DESC";
		//echo $query;
		$result=mysql_query($query);
		while($row=mysql_fetch_array($result)){
			echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='inventory-move.php?COM=RES&AUD_DT=".$row['AUD_DT']."&CRT_NBR=".$row['CRT_NBR']."';".chr(34).">";
			echo "<td style='text-align:center'>".$row['AUD_DT']."</td>";
			echo "<td style='text-align:center'>".number_format($row['VOL'],0,".",",")."</td>";
			echo "<td style='text-align:center'>".number_format($row['INV_NBR_TYP'],0,".",",")."</td>";
			echo "<td>".$row['NBR_PRSN']."</td>";
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

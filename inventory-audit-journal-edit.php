<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<script>parent.Pace.restart();</script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<script type="text/javascript" src='framework/tablesort/tablesort.js'></script>
	
</head>
<body>
<?php
if($_GET['COM']=='EDT'){
$AudTS=$_GET['AUD_TS'];
$InvBcd=$_GET['INV_NBR'];
$CurrDate = new DateTime("now");
if($_POST['AUD_NBR']==""){$AudNbr=$_POST['AUD_NBR'];}{$AudNbr=$_GET['AUD_NBR'];}

if($_GET['NEW']==1){
	if($_POST['AUD_NBR']=='-1'){	
		$query="SELECT IFNULL(MAX(AUD_NBR),0)+1 AUD_NBR FROM RTL.INV_AUD;";
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		$AudNbr=$row['AUD_NBR'];
		$query="INSERT INTO RTL.INV_AUD (AUD_NBR) VALUES ($AudNbr);";
		mysql_query($query);//echo $query;
	}
	if($_POST['INV_NBR']!="" && $_POST['AUD_Q']!=""){
		$query="SELECT INV.INV_NBR INV_NBR,  
				(IFNULL(SUM(ORD_Q),0)-IFNULL(SUM(RTL_Q),0)) STOK 
				FROM RTL.INVENTORY INV 
				LEFT OUTER JOIN (SELECT SUM(ORD_Q) ORD_Q,INV_NBR FROM RTL.RTL_STK_DET GROUP BY INV_NBR) DET ON DET.INV_NBR=INV.INV_NBR  
				LEFT OUTER JOIN (SELECT SUM(RTL_Q) RTL_Q,INV_NBR FROM RTL.CSH_REG GROUP BY INV_NBR) REG ON REG.INV_NBR=INV.INV_NBR  
				WHERE INV.DEL_NBR=0 AND INV.INV_NBR='".$_POST['INV_NBR']."'  GROUP BY INV.INV_NBR"; //echo $query;
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		$InvDiff=$_POST['AUD_Q']-$row['STOK'];
	}
	
	$query="UPDATE RTL.INV_AUD SET 
			INV_NBR='".mysql_real_escape_string($_POST['INV_NBR'])."',
			INV_BCD='".mysql_real_escape_string($_POST['INV_BCD'])."',
			AUD_Q='".mysql_real_escape_string($_POST['AUD_Q'])."',
			INV_DIFF='".mysql_real_escape_string($InvDiff)."',
			PRSN_NBR='".mysql_real_escape_string($_SESSION['personNBR'])."'
			WHERE AUD_NBR=".$AudNbr;// echo $query;
			mysql_query($query);
}
	$query="SELECT * FROM RTL.INV_AUD  
			WHERE AUD_NBR='".$AudNbr."'";
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
?>
<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
<div class="toolbar-only">
<p class="toolbar-left"><a href="javascript:void(0)" onclick="window.scrollTo(0,0);parent.document.getElementById('inventoryListDelete').style.display='block';parent.document.getElementById('fade').style.display='block'"><img style="cursor:pointer" class="toolbar-left" src="img/delete.png"></a></p>
</div>
<form enctype="multipart/form-data" action="inventory-audit.php?COM=EDT&NEW=1&AUD_NBR=<?php echo $AudNbr;?>" method="post" style="width:600px" onSubmit="return checkform();" id="signup">
	<p>
		<h2>
			No <?php echo $row['AUD_NBR'];if($row['AUD_NBR']==""){echo "Baru";} ?>
		</h2>

		<h3>
			Tanggal : <?php if($row['AUD_TS']==""){echo date_format($CurrDate, 'd-m-Y');}else{ echo date_format(new DateTime($row['AUD_TS']), 'd-m-Y');} ?>
		</h3>
		<input name="AUD_NBR" id="AUD_NBR" value="<?php echo $row['AUD_NBR'];if($row['AUD_NBR']==""){echo "-1";} ?>" type="hidden" />
		<label>Cari Barang</label><br />
				<input type="text" id="livesearch1" onkeyup="cariSatu()"/></input>
				<div style="margin-top:5px;background:#ffffff" id="liveRequestResults"></div>
				<div id="mainResult" ></div>
		<br />
		<script>
		function cariSatu(){
		liveReqInit('livesearch1','liveRequestResults','retail-stock-edit-list-detail-ls.php?IVC_TYP=INV_TBL','');
		<?php if($row['INV_NBR']!="") { ?>
		getContent('liveRequestResults',"retail-stock-edit-list-detail-ls.php?INV_NBR=<?php echo $row['INV_NBR']."&IVC_TYP=INV_TBL" ?>");
		document.getElementById('liveRequestResults').style.display="";	
		document.getElementById('livesearch1').value="<?php echo $row['INV_BCD']; ?>";
		<?php } ?>
		}
		</script>
		<label>Barcode</label><br />
		<input name="INV_BCD" id="INV_BCD" value="<?php echo $row['INV_BCD'];?>" type="text" readonly />
		<input name="INV_NBR" id="INV_NBR" value="<?php echo $row['INV_NBR'];?>" type="hidden" />
		<br/>
		<label>Jumlah</label><br />
		<input name="AUD_Q" value="<?php echo $row['AUD_Q']; ?>" type="text" size="20" /><br />
		<input class="process" type="submit" value="Simpan"/>
		</p>
	</form>
<?php
}else{	
?>
<div class="toolbar">
	<p class="toolbar-left"><a href="inventory-audit.php?COM=EDT&NEW=0"><img class="toolbar-left" src="img/add.png" onclick="location.href="></a></p>
	<p class="toolbar-right"><img class="toolbar-right" src="img/search.png"><input type="text" id="livesearch" class="livesearch" /></p>
</div>

<div id="mainResult">
	<table id="mainTable" class="rowstyle-alt colstyle-alt no-arrow searchTable">
		<thead>
			<tr>
				<th class="sortable">No</th>
				<th class="sortable">Barang</th>
				<th class="sortable">Barcode</th>
				<th class="sortable">Tanggal</th>
				<th class="sortable">Jumlah</th>
				<th class="sortable">SPG</th><!--
				<th class="sortable">Jenis</th>
				<th class="sortable">Mulai</th>
				<th class="sortable">Selesai</th>-->
			</tr>
		</thead>
		<tbody>
		<?php
		//$query="SELECT DATE(AUD_TS) AS AUD_DT,SUM(AUD_Q) AS VOL,COUNT(DISTINCT INV_BCD) NBR_TYP,COUNT(DISTINCT PRSN_NBR) AS NBR_PRSN,MIN(TIME(AUD_TS)) AS BEG_TM,MAX(TIME(AUD_TS)) AS END_TM FROM RTL.INV_AUD AUD GROUP BY DATE(AUD_TS) ORDER BY 1 DESC";
		$query="SELECT AUD_NBR,AUD_TS,AUD.INV_BCD INV_BCD, AUD_Q, PPL.NAME NAME, INV.NAME INVNM  
				FROM RTL.INV_AUD AUD 
				LEFT OUTER JOIN RTL.INVENTORY INV ON INV.INV_NBR=AUD.INV_NBR 
				LEFT OUTER JOIN CMP.PEOPLE PPL ON PPL.PRSN_NBR=AUD.PRSN_NBR 
				ORDER BY AUD_NBR";
		$res=mysql_query($query);
		$alt="";
		while($row=mysql_fetch_array($res)){
			//echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='inventory-audit-result.php?AUD_DT=".$row['AUD_DT']."';".chr(34).">";
			echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='inventory-audit.php?COM=EDT&AUD_NBR=".$row['AUD_NBR']."';".chr(34).">";
			echo "<td style='text-align:center'>".$row['AUD_NBR']."</td>";
			echo "<td style='text-align:center'>".$row['INVNM']."</td>";
			echo "<td style='text-align:center'>".$row['INV_BCD']."</td>";
			echo "<td style='text-align:center'>".date_format(new DateTime($row['AUD_TS']), 'd-m-Y | h:m')."</td>";
			echo "<td style='text-align:center'>".number_format($row['AUD_Q'],0,".",",")."</td>";
			//echo "<td style='text-align:center'>".number_format($row['NBR_TYP'],0,".",",")."</td>";
			echo "<td style='text-align:center'>".$row['NAME']."</td>";
			//echo "<td style='text-align:center'>".$row['BEG_TM']."</td>";
			//echo "<td style='text-align:center'>".$row['END_TM']."</td>";
			echo "</tr>";
			if($alt==""){$alt="class='alt'";}else{$alt="";}
		}
			
		?>
		</tbody>
		</table>
	</div>

<script>fdTableSort.init();</script>
<?php 
}
?>
</body>
</html>			

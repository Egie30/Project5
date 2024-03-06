<?php
	@header("Connection: close\r\n"); 
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/functions/print-digital.php";
	$TrnspNbr=$_GET['TRNSP_NBR'];
	$TrnspDetNbr=$_GET['TRNSP_DET_NBR'];
	$changed=false;
	$addNew=false;
	
	//Process changes here
	if($_POST['TRNSP_DET_NBR']!="")
	{
		$TrnspDetNbr=$_POST['TRNSP_DET_NBR'];
		//Take care of nulls
		if($_POST['TRNSP_Q']==""){$TrnspQ="NULL";}else{$TrnspQ=$_POST['TRNSP_Q'];}
		
		//Process add new
		if($TrnspDetNbr==-1)
		{
			$addNew=true;
			$query="SELECT COALESCE(MAX(TRNSP_DET_NBR),0)+1 AS NEW_NBR FROM CMP.TRNSP_DET";
			$result=mysql_query($query);
			$row=mysql_fetch_array($result);
			$TrnspDetNbr=$row['NEW_NBR'];
			$query="INSERT INTO CMP.TRNSP_DET (TRNSP_DET_NBR,TRNSP_NBR) VALUES (".$TrnspDetNbr.",".$TrnspNbr.")";
			$result=mysql_query($query);
			$create="CRT_TS=CURRENT_TIMESTAMP,CRT_NBR=".$_SESSION['personNBR'].",";

		}
		
		$query="UPDATE CMP.TRNSP_DET
	   			SET TRNSP_Q=".$TrnspQ.",
					DET_TTL='".mysql_real_escape_string($_POST['DET_TTL'])."',
					UPD_TS=CURRENT_TIMESTAMP,
					UPD_NBR=".$_SESSION['personNBR']."
					WHERE TRNSP_DET_NBR=".$TrnspDetNbr;
		//echo $query;
	   	$result=mysql_query($query);
	   	$changed=true;
    }
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" type="text/css" media="screen" href="css/popup.css" />

<script type="text/javascript" src="framework/liveSearch/livepop.js"></script>
<script type="text/javascript" src="framework/functions/default.js"></script>
    
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">

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

<link rel="stylesheet" href="framework/combobox/chosen.css">

</head>

<body>

<?php
	if($changed){
		echo "<script>";
		echo "parent.document.getElementById('content').contentDocument.getElementById('rightpane').contentDocument.getElementById('refresh-list').click();";
		echo "parent.document.getElementById('content').contentDocument.getElementById('rightpane').contentDocument.getElementById('refresh-tot').click();";
		echo "</script>";
	}
	if($addNew){$TrnspDetNbr=0;}
	
?>

<span class='fa fa-times toolbar' style='margin-left:10px' onclick="slideFormOut();"></span></a>

<?php
	$query="SELECT TRNSP_DET_NBR,
				TRNSP_NBR,
                TDT.ORD_DET_NBR,
				TRNSP_Q,
				TDT.DET_TTL AS TRNSP_TTL,
                ODT.DET_TTL AS ORD_TTL,
                PRN_DIG_DESC,
                ORD_Q,
                PRN_LEN,
                PRN_WID,
                ORD_NBR
			FROM CMP.TRNSP_DET TDT LEFT OUTER JOIN
            CMP.PRN_DIG_ORD_DET ODT ON TDT.ORD_DET_NBR=ODT.ORD_DET_NBR LEFT OUTER JOIN
            CMP.PRN_DIG_TYP TYP ON ODT.PRN_DIG_TYP=TYP.PRN_DIG_TYP LEFT OUTER JOIN
            CMP.PRN_DIG_EQP EQP ON TYP.PRN_DIG_EQP=EQP.PRN_DIG_EQP
			WHERE TRNSP_DET_NBR=".$TrnspDetNbr;
	$query 	= "SELECT 
        			TDET.TRNSP_DET_NBR,
        			TDET.TRNSP_Q,
        			DET.ORD_Q,
        			DET.ORD_NBR,
        			CONCAT(COALESCE(INV.NAME,''),' ',DET.INV_DESC) AS INV_NAME,
        			TDET.ORD_DET_NBR,
				TDET.DET_TTL AS TRNSP_TTL
        		FROM CMP.TRNSP_DET TDET 
        		LEFT JOIN RTL.RTL_STK_DET DET ON TDET.ORD_DET_NBR=DET.ORD_DET_NBR 
        		LEFT JOIN RTL.INVENTORY INV ON DET.INV_NBR=INV.INV_NBR 
        		WHERE TRNSP_DET_NBR=".$TrnspDetNbr;
	//echo $query;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
?>

<script>
	parent.parent.document.getElementById('content').contentDocument.getElementById('refresh-list').click();
</script>

<form enctype="multipart/form-data" action="#" method="post" style="width:400px" onSubmit="return checkform();">
	<table>
		<tr>
			<td>No. Pembelian</td>
			<td style='padding-left:7px'>
                <?php echo $row['ORD_NBR']; ?>
			</td>
		</tr>
		<tr>
			<td>PID</td>
			<td style='padding-left:7px'>
                <?php echo $row['ORD_DET_NBR']; ?>
			</td>
		</tr>
		<tr>
			<td>Deskripsi</td>
			<td style='padding-left:7px'>
                <?php
                    echo $row['ORD_TTL']." ".$row['INV_NAME'].$prnDim;
                ?>
			</td>
        </tr>
		<tr>
			<td>Jumlah di Nota</td>
			<td style='padding-left:7px'>
                <?php echo $row['ORD_Q']; ?>
			</td>
		</tr>
		<tr>
			<td style='padding-top:30px'>Jumlah Diterima</td>
			<td style='padding-top:30px'>
				<input name="TRNSP_DET_NBR" id="TRNSP_DET_NBR" value="<?php echo $row['TRNSP_DET_NBR'];if($row['TRNSP_DET_NBR']==""){echo "-1";$addNew=true;} ?>" type="hidden" />
				<input id="TRNSP_Q" name="TRNSP_Q" value="<?php echo $row['TRNSP_Q']; ?>" type="text" style="width:100px" />
			</td>
		</tr>
		<tr>
			<td>Keterangan</td>
			<td><input id="DET_TTL" name="DET_TTL" value="<?php echo $row['TRNSP_TTL']; ?>" type="text" style="width:280px;" /></td>
		</tr>
	</table>
	<?php 
		
			if(@$_GET['readonly']!=1){
	?>
	<input class="process" type="submit" value="<?php if($addNew){echo 'Tambah';}else{echo 'Simpan';} ?>"/>
	<?php }?>
	<script>
		setPrice(document.getElementById('PRN_DIG_TYP').value);
	</script>
	
	<script src="framework/database/jquery.min.js" type="text/javascript"></script>
	<script src="framework/combobox/chosen.jquery.js" type="text/javascript"></script>
	<script type="text/javascript">
		var config = {
			'.chosen-select'           : {},
			'.chosen-select-deselect'  : {allow_single_deselect:true},
			'.chosen-select-no-single' : {disable_search_threshold:10},
			'.chosen-select-no-results': {no_results_text:'Data tidak ketemu'},
			'.chosen-select-width'     : {width:"95%"}
	   	}
		for (var selector in config) {
			$(selector).chosen(config[selector]);
		}
	</script>

</body>
</html>



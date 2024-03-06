<?php
include "framework/database/connect-cloud.php";
include "framework/functions/default.php";

$ProdNbr	= $_GET['PROD_NBR'];
$ProdDetNbr	= $_GET['PROD_DET_NBR'];
$changed	= false;
$addNew		= false;

//Process changes here
if($_POST['PROD_DET_NBR']!=""){
	$ProdDetNbr=$_POST['PROD_DET_NBR'];
	if($_POST['PRN_DIG_TYP']==""){$PlanTyp="NULL";}else{$PlanTyp=$_POST['PRN_DIG_TYP'];}
	if($_POST['PROD_DET_X']==""){$X="NULL";}else{$X=$_POST['PROD_DET_X'];}
	if($_POST['PROD_DET_Y']==""){$Y="NULL";}else{$Y=$_POST['PROD_DET_Y'];}
		
	//Process add new
	if($ProdDetNbr==-1){
		$addNew     = true;
		$query      = "SELECT COALESCE(MAX(PROD_DET_NBR),0)+1 AS NEW_NBR FROM $CMP.PROD_LST_DET";
		$result     = mysql_query($query, $cloud);
		$row        = mysql_fetch_array($result);
		$ProdDetNbr = $row['NEW_NBR'];

		$query      = "INSERT INTO $CMP.PROD_LST_DET(PROD_DET_NBR,CRT_TS,CRT_NBR) 
					  VALUES ('$ProdDetNbr',CURRENT_TIMESTAMP,'".$_SESSION['personNBR']."')";
		$result     = mysql_query($query,$cloud);
		$query      = str_replace($CMP,"CMP",$query);
		$result     = mysql_query($query,$local);
	}
	
	if ($_POST['FEE_MISC']==''){$feeMisc = "NULL";}else {$feeMisc = $_POST['FEE_MISC'];}
	if ($_POST['TOT_SUB'] ==''){$totSub  = "NULL";}else {$totSub   = $_POST['TOT_SUB'];}

	$query = "UPDATE $CMP.PROD_LST_DET SET 
				PROD_NBR=".$ProdNbr.",
				PRN_DIG_TYP='".$PlanTyp."',
				PROD_DET_DESC='".$_POST['PROD_DET_DESC']."',
				PROD_DET_X=".$X.",
				PROD_DET_Y=".$Y.",
				PROD_DET_PRC=".$_POST['PROD_DET_PRC'].",
				FIN_BDR_TYP='".$_POST['FIN_BDR_TYP']."',
				FEE_MISC = ".$feeMisc.",
				TOT_SUB = ".$totSub.",
				UPD_TS=CURRENT_TIMESTAMP,
				UPD_NBR='".$_SESSION['personNBR']."'
				WHERE PROD_DET_NBR=".$ProdDetNbr;
	$result = mysql_query($query,$cloud);
	$query  = str_replace($CMP,"CMP",$query);
	$result = mysql_query($query,$local);
	
	$changed= true;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" type="text/css" media="screen" href="css/popup.css" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<link rel="stylesheet" href="framework/combobox/chosen.css">

<script type="text/javascript" src="framework/liveSearch/livepop.js"></script>
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

	function calcPay(){
		var prnLength = 1;
		var prnWidth  = 1;

		document.getElementById("PROD_DET_PRC").value=window.printDigitalPrice;
		if(document.getElementById('PROD_DET_X').value!=""){prnLength=document.getElementById('PROD_DET_X').value;}
		if(document.getElementById('PROD_DET_Y').value!=""){prnWidth=document.getElementById('PROD_DET_Y').value;}
		document.getElementById('TOT_SUB').value=((getInt('PROD_DET_PRC')+getInt('FEE_MISC'))*prnLength*prnWidth);
		// alert(window.printDigitalPrice);

	}
	
	//Assign price to combo box changes
	function setPrice(printDigitalType){

		switch (printDigitalType) {
			<?php
				$query       = "SELECT PRN_DIG_TYP,
									PRN_DIG_DESC,
									PRN_DIG_EQP_DESC,
									PRN_DIG_PRC,
									PLAN_TYP
							    FROM CMP.PRN_DIG_TYP TYP 
							    INNER JOIN CMP.PRN_DIG_EQP EQP ON TYP.PRN_DIG_EQP=EQP.PRN_DIG_EQP 
							    ORDER BY 2";
				$result       = mysql_query($query, $local);
				$defaultPrice = 0;
				while($row = mysql_fetch_array($result)){
					if($defaultPrice==0){$defaultPrice=$row['PRN_DIG_PRC'];}
					echo " case '".$row['PRN_DIG_TYP']."':window.printDigitalPrice = '".$row['PRN_DIG_PRC']."' ;break;\n";
				}
			?>
		}
	}
	var printDigitalPrice=0;
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
if($addNew){$ProdDetNbr=0;}
?>
<div style="height:480px; overflow:auto">
<span class='fa fa-times toolbar' style='margin-left:10px' onclick="pushFormOut();"></span>
<?php
	$query  = "SELECT PROD_DET_NBR,
				   PROD_NBR,
				   PRN_DIG_TYP,
				   PROD_DET_DESC,
				   PROD_DET_X,
				   PROD_DET_Y,
				   FIN_BDR_TYP,
				   PROD_DET_PRC,
				   FEE_MISC,
				   TOT_SUB
			   FROM CMP.PROD_LST_DET
			   WHERE PROD_DET_NBR=".$ProdDetNbr;
	$result = mysql_query($query, $local);
	$row    = mysql_fetch_array($result);
?>

<script>
	parent.document.getElementById('content').contentDocument.getElementById('refresh-list').click();
</script>

<form enctype="multipart/form-data" action="#" method="post" style="width:400px;" onSubmit="return checkform();">
<table>
		<tr>
			<input type="hidden" name="PROD_DET_NBR" id="PROD_DET_NBR" value="<?php echo $row['PROD_DET_NBR'];if($row['PROD_DET_NBR']==""){echo "-1";$addNew=true;} ?>" />
			<td>Jenis Print</td>
			<td>
				<select style="margin:0px;padding:0px;width:290px;" id="PRN_DIG_TYP" name="PRN_DIG_TYP" onchange="setPrice(this.value);calcPay();" class="chosen-select">
				<?php
					if(($OrdDetNbrPar!="")&&($rowp['PRN_DIG_TYP']!='PROD')){$where="WHERE TYP.DEL_NBR = 0 AND EQP.PRN_DIG_EQP NOT IN ('FLJ320P','KMC6501','EPST13','KMC6000','AJ1800F','CIR6000','DIC8000')";}else{$where="WHERE TYP.DEL_NBR = 0";}
					$query="SELECT PRN_DIG_TYP,
								   PRN_DIG_DESC,
								   PRN_DIG_EQP_DESC,
								   PRN_DIG_PRC
							FROM CMP.PRN_DIG_TYP TYP 
							INNER JOIN CMP.PRN_DIG_EQP EQP ON TYP.PRN_DIG_EQP=EQP.PRN_DIG_EQP 
							WHERE TYP.DEL_NBR = 0 AND (PRN_DIG_PRC != 0 OR PRN_DIG_TYP='CUSTOM') ORDER BY 2";
					genCombo($query,"PRN_DIG_TYP","PRN_DIG_DESC",$row['PRN_DIG_TYP'],"Select Print Type");
				?>
				</select>
			</td>
		</tr>
		<tr>
			<td>Deskripsi</td>
			<td><input name="PROD_DET_DESC" id="PROD_DET_DESC"  value="<?php echo $row['PROD_DET_DESC']; ?>" type="text" style="width:200px" /></td>
		</tr>
		<tr>
			<td>Ukuran (m)</td>
			<td>
                Panjang <input id="PROD_DET_X" name="PROD_DET_X" onkeyup="calcPay();" onchange="calcPay();" value="<?php echo $row['PROD_DET_X']; ?>" type="text" style="width:30px;" /> x 
                Lebar <input id="PROD_DET_Y" name="PROD_DET_Y" onkeyup="calcPay();" onchange="calcPay();" value="<?php echo $row['PROD_DET_Y']; ?>" type="text" style="width:30px;" />
            </td>
		</tr>
		<tr>
			<td>Finishing</td>
			<?php if($row['FIN_BDR_TYP']==""){$finBdrTyp="Simpres";}else{$finBdrTyp=$row['FIN_BDR_TYP'];} ?>
			<td><select style="margin:0px;padding:0px;width:170px;" name="FIN_BDR_TYP" class="chosen-select">
				<?php
					$query="SELECT FIN_BDR_TYP,FIN_BDR_DESC,SORT
							FROM CMP.PRN_DIG_FIN_BDR_TYP ORDER BY 3";
					genCombo($query,"FIN_BDR_TYP","FIN_BDR_DESC",$finBdrTyp,"");
				?>
				</select>
			</td>
		</tr>
		<tr>
			<td>Harga (Rp)</td>
			<td><input id="PROD_DET_PRC" name="PROD_DET_PRC" value="<?php echo $row['PROD_DET_PRC']; ?>" type="text" style="width:100px;" /></td>
		</tr>
		<tr >
			<td style='white-space:nowrap;'>Spot</td>
			<td><input id="FEE_MISC" name="FEE_MISC" onkeyup="calcPay();" onchange="calcPay();" value="<?php echo $row['FEE_MISC']; ?>" type="text" style="width:100px;" /></td>
		</tr>
		<tr>
			<td>Sub total</td>
			<td><input id="TOT_SUB" name="TOT_SUB" value="<?php echo $row['TOT_SUB']; ?>" type="text" style="width:100px" readonly /></td>
		</tr>
	</table>
	<br />
	<input class="process" type="submit" value="<?php if($addNew){echo 'Tambah';}else{echo 'Simpan';} ?>"/>
	<script>
		setPrice(document.getElementById('PRN_DIG_TYP').value);
	</script>
</form>
	
</div>
</body>
<script src="framework/database/jquery.min.js" type="text/javascript"></script>
<script src="framework/combobox/chosen.jquery.js" type="text/javascript"></script>
<script type="text/javascript">
	jQuery.noConflict();
	var config = {
		'.chosen-select'           : {},
		'.chosen-select-deselect'  : {allow_single_deselect:true},
		'.chosen-select-no-single' : {disable_search_threshold:10},
		'.chosen-select-no-results': {no_results_text:'Data tidak ketemu'},
		'.chosen-select-width'     : {width:"95%"}
	};
	for (var selector in config) {
		jQuery(selector).chosen(config[selector]);
	}
</script>
</html>



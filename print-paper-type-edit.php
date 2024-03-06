<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";

	$PrnDigTyp	= $_GET['PRN_PPR_TYP'];
	$Security	= getSecurity($_SESSION['userID'],"DigitalPrint");
	
	//Process changes here
	if($_POST['PRN_PPR_TYP']!=""){
		$PrnDigTyp=$_POST['PRN_PPR_TYP'];

		$query="SELECT PRN_PPR_TYP FROM CMP.PRN_PPR_TYP WHERE PRN_PPR_TYP='$PrnDigTyp'";
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);

		//Process add new
		if($row['PRN_PPR_TYP']==""){
			//echo "Here";
			$query="INSERT INTO CMP.PRN_PPR_TYP (PRN_PPR_TYP) VALUES ('".$PrnDigTyp."')";
			$result=mysql_query($query);
		}
		
		//Take care of nulls
		if($_POST['INV_NBR']==""){$InvNbr="NULL";}else{$InvNbr=$_POST['INV_NBR'];}
		if($_POST['PRN_PPR_PRC']==""){$DigPrnPrc="NULL";}else{$DigPrnPrc=$_POST['PRN_PPR_PRC'];}
		if($_POST['PRN_PPR_PRC_PRSN']==""){$DigPrnPrcPerson="NULL";}else{$DigPrnPrcPerson=$_POST['PRN_PPR_PRC_PRSN'];}
		if($_POST['PRN_PPR_PRC_MBR']==""){$DigPrnPrcMember="NULL";}else{$DigPrnPrcMember=$_POST['PRN_PPR_PRC_MBR'];}
		if($_POST['PRN_PPR_TYP_PAR']==""){$PrnDigTypPar=$PrnDigTyp;} else {$PrnDigTypPar=$_POST['PRN_PPR_TYP_PAR'];}
		if($_POST['ACT_F']=="on"){$ActF=1;}else{$ActF=0;}

		$query="UPDATE CMP.PRN_PPR_TYP SET 
			PRN_PPR_TYP_PAR='".$PrnDigTypPar."',
			PRN_PPR_CD='".$_POST['PRN_PPR_CD']."',
			PRN_PPR_DESC='".$_POST['PRN_PPR_DESC']."',
			PRN_PPR_EQP='".$_POST['PRN_PPR_EQP']."',
			INV_NBR=".$InvNbr.",
			PRN_PPR_PRC=".$DigPrnPrc.",
			PRN_PPR_PRC_PRSN=".$DigPrnPrcPerson.",
			PRN_PPR_PRC_MBR=".$DigPrnPrcMember.",
			PLAN_TYP='".$_POST['PLAN_TYP']."',
			ACT_F='".$ActF."',
			UPD_TS=CURRENT_TIMESTAMP,
			UPD_NBR=".$_SESSION['personNBR']."
		WHERE PRN_PPR_TYP='".$PrnDigTyp."'";
		//echo $query;
		$result=mysql_query($query);
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="framework/combobox/chosen.css">
	<script>parent.Pace.restart();</script>
	<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
	<script type="text/javascript" src="framework/functions/default.js"></script>
	<script>
		parent.document.getElementById('printDigitalTypeDeleteYes').onclick=
		function () {
			parent.document.getElementById('content').src='print-digital-type.php?DEL_L=<?php echo $PrnDigTyp ?>';
			parent.document.getElementById('printDigitalTypeDelete').style.display='none';
			parent.document.getElementById('fade').style.display='none';
		};
	</script>
</head>
<body>
<?php
$query="SELECT 
	PRN_PPR_TYP,
	PRN_PPR_TYP_PAR,
	PRN_PPR_CD,
	PRN_PPR_DESC,
	PRN_PPR_EQP,
	INV_NBR,
	PRN_PPR_PRC, 
	PRN_PPR_PRC_PRSN, 
	PRN_PPR_PRC_MBR,
	PLAN_TYP,
	ACT_F
FROM CMP.PRN_PPR_TYP
WHERE PRN_PPR_TYP='".$PrnDigTyp."'";
//echo $query;
$result=mysql_query($query);
$row=mysql_fetch_array($result);
?>

<?php if(($Security==0)&&($PrnDigTyp!="")) { ?>
	<div class="toolbar-only">
		<p class="toolbar-left">
			<a href="javascript:void(0)" onclick="window.scrollTo(0,0);parent.document.getElementById('printDigitalTypeDelete').style.display='block';parent.document.getElementById('fade').style.display='block'"><span class="fa fa-trash toolbar" style="cursor:pointer"></span></a>
		</p>
	</div>
<?php } ?>
<form enctype="multipart/form-data" action="#" method="post" style="width:600px" onSubmit="return checkform();">
	<p>
		<h2> <?php echo $row['PRN_PPR_DESC'];if($row['PRN_PPR_DESC']==""){echo "Nama Baru";} ?> </h2>
		<h3> <?php echo $row['PRN_PPR_TYP'];if($row['PRN_PPR_TYP']==""){echo "Baru";} ?></h3>

		<input name="INV_NBR" value="<?php echo $row['INV_NBR'];if($row['INV_NBR']==""){echo "-1";} ?>" type="hidden" />

		<label>Kode Harga</label><br />
		<input name="PRN_PPR_TYP" value="<?php echo $row['PRN_PPR_TYP']; ?>" type="text" size="15" /><br />
		
		<label>Alias</label><br />
		<input name="PRN_PPR_CD" value="<?php echo $row['PRN_PPR_CD']; ?>" type="text" size="15" /><br />

		<label>Deskripsi</label><br />
		<input name="PRN_PPR_DESC" value="<?php echo $row['PRN_PPR_DESC']; ?>" type="text" size="80" /><br />

		<label>Mesin</label><br /><div class='labelbox'></div>
		<select name="PRN_PPR_EQP" class="chosen-select">
			<?php
				$query="SELECT PRN_PPR_EQP,PRN_PPR_EQP_DESC
						FROM CMP.PRN_PPR_EQP ORDER BY 2";
				genCombo($query,"PRN_PPR_EQP","PRN_PPR_EQP_DESC",$row['PRN_PPR_EQP'],"",$local);
			?>
		</select><br /><div class="combobox"></div>

		<?php
			if($Security<=1){
				$query="SELECT ORD_Q,TOT_SUB/ORD_Q AS INV_PRC_NET,DATE(DET.CRT_TS) AS CRT_DT,COM.NAME,ORD_X,ORD_Y,ORD_Z 
						FROM RTL.RTL_STK_DET DET 
							INNER JOIN RTL.INVENTORY INV ON DET.INV_NBR=INV.INV_NBR 
							INNER JOIN CMP.PRN_PPR_TYP TYP ON INV.PRD_PRC_TYP=TYP.PRN_PPR_TYP 
							INNER JOIN RTL.RTL_STK_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR 
							INNER JOIN CMP.COMPANY COM ON COM.CO_NBR=HED.SHP_CO_NBR 
						WHERE IVC_TYP='RC' AND PRN_PPR_TYP='$PrnDigTyp' 
						ORDER BY DET.CRT_TS DESC LIMIT 10";
				$resultp=mysql_query($query);
				$rows=mysql_num_rows($resultp);
				if($rows>0){
					echo "<h3>History</h3><table>";
					echo "<tr>";
					echo "<th class='listable'>Volume</th>";
					echo "<th class='listable'>Content</th>";
					echo "<th class='listable'>Unit Price</th>";
					echo "<th class='listable'>Last Order</th>";
					echo "<th class='listable'>Source</th>";
					echo "</tr>";
					$alt="";
					while($rowp=mysql_fetch_array($resultp)){
						echo "<tr $alt ";
						if($salesSec<=8){
							echo "onclick=".chr(34)."document.getElementById('Curve').src='print-digital-report-customer-price-curve-chart.php?NBR=C".$CoNbr."&PRN_PPR_TYP=".$rowp['PRN_PPR_TYP']."';".chr(34);
						}
						echo ">";
						echo "<td style='text-align:right;cursor:pointer'>".$rowp['ORD_Q']."</td>";
						echo "<td style='text-align:right;cursor:pointer'>";
						if($rowp['ORD_X']!=''){echo " Uk ".$rowp['ORD_X'];}
						if($rowp['ORD_Y']!=''){echo "x".$rowp['ORD_Y'];}
						if($rowp['ORD_Z']!=''){echo "x".$rowp['ORD_Z'];}
						echo "</td>";
						echo "<td style='text-align:right;cursor:pointer'>".number_format($rowp['INV_PRC_NET'],0,",",".")."</td>";
						echo "<td style='text-align:center;cursor:pointer'>".$rowp['CRT_DT']."</td>";
						echo "<td style='text-align:center;cursor:pointer'>".$rowp['NAME']."</td>";
						echo "</tr>";
						if($alt==""){$alt="class='alt'";}else{$alt="";}
					}
					echo "</table><br/>";
				}
			}
		?>
		<div style="float:left;width:140px;">
			<label>Harga</label><br>
			<input name="PRN_PPR_PRC" value="<?php echo $row['PRN_PPR_PRC']; ?>" type="text" size="15" />
		</div>
		
		<div style="float:left;width:140px;">
			<label>Harga Tunai</label><br>
			<input name="PRN_PPR_PRC_PRSN" value="<?php echo $row['PRN_PPR_PRC_PRSN']; ?>" type="text" size="15" />
		</div>
		
		<div style="float:left;width:210px;">
			<label>Harga Member</label><br>
			<input name="PRN_PPR_PRC_MBR" value="<?php echo $row['PRN_PPR_PRC_MBR']; ?>" type="text" size="15" />
		</div>
		
		<div style="clear:both"></div>
		
		<label>Status</label><br /><div class="labelbox"></div>
		<input name='ACT_F' id='ACT_F' type='checkbox' class='regular-checkbox'
		<?php if ($row['ACT_F']=='1'){ echo "checked"; } ?> />
		<label for="ACT_F"></label><div class="labelbox" style="height:10px;"></div>

		<input class='process' type='submit' value='Simpan'/>
		
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

	</p>
</form>

<script>liveReqInit('livesearch','liveRequestResults','print-paper-type-edit-ls.php','','mainResult');</script>

<script>
	<?php if($row['INV_NBR']!="") { ?>
	getContent('liveRequestResults',"print-paper-type-edit-disp.php?INV_NBR=<?php echo $row['INV_NBR']; ?>");
	<?php } ?>
	document.getElementById('liveRequestResults').style.display="";	
	document.getElementById('livesearch').value="<?php echo $row['INV_NBR']; ?>";
</script>

</body>
</html>

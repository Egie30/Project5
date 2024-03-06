<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	include "framework/functions/dotmatrix.php";

	$VchrNbr=$_GET['VCHR_NBR'];
	$Security=getSecurity($_SESSION['userID'],"Finance");
	//Process changes here
	if($_POST['VCHR_NBR']!="")
	{
		$VchrNbr=$_POST['VCHR_NBR'];

		//Process add new
		if($VchrNbr==-1)
		{
			$query="SELECT COALESCE(MAX(VCHR_NBR),0)+1 AS NEW_NBR FROM CMP.CAP_VCHR";
			$result=mysql_query($query);
			$row=mysql_fetch_array($result);
			//echo $query;
			$VchrNbr=$row['NEW_NBR'];
			
			//Create a new serial number
			//YYYYMMXXXL
			//1234567890
			$query="SELECT MAX(COALESCE(SUBSTR(VCHR_SER_NBR,7,3),0)) AS SER_NBR FROM CMP.CAP_VCHR WHERE MONTH(CRT_TS)=MONTH(CURRENT_DATE) AND YEAR(CRT_TS)=YEAR(CURRENT_DATE)";
			$result=mysql_query($query);
			$row=mysql_fetch_array($result);
			$SerNbr=$row['SER_NBR']+1;
			$VchrSerNbr=Luhn(date("Y").date("m").leadZero($SerNbr,3));
			
			$query="INSERT INTO CMP.CAP_VCHR (VCHR_NBR,VCHR_SER_NBR,AMT,CRT_CO_NBR,CRT_NBR) VALUES (".$VchrNbr.",".$VchrSerNbr.",".$_POST['AMT'].",".$CoNbrDef.",".$_SESSION['personNBR'].")";
			//echo $query;
			$result=mysql_query($query);
		}
		
		//Selective update
		$query="UPDATE CMP.CAP_VCHR ";
		if($_POST['UPD_FLD']=="Cetak"){$query.="SET PRN_TS=CURRENT_TIMESTAMP ";}
		if($_POST['UPD_FLD']=="Validasi"){$query.="SET VLD_TS=CURRENT_TIMESTAMP ";}
		if($_POST['UPD_FLD']=="Issue"){$query.="SET ISU_TS=CURRENT_TIMESTAMP,EXP_DT=DATE_ADD(CURRENT_DATE(),INTERVAL 3 MONTH) ";}
		if($_POST['UPD_FLD']=="Guna"){$query.="SET USE_TS=CURRENT_TIMESTAMP ";}
		//Remaining update
		if($_POST['RCV_PRSN_NBR']!=""){$query.=",RCV_PRSN_NBR=".$_POST['RCV_PRSN_NBR']." ";}
		if($_POST['RCV_CO_NBR']!=""){$query.=",RCV_CO_NBR=".$_POST['RCV_CO_NBR']." ";}
		$query.="WHERE VCHR_NBR=".$VchrNbr;
		//echo $query;
	   	$result=mysql_query($query);
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<script>parent.Pace.restart();</script>

<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>

<link rel="stylesheet" href="framework/combobox/chosen.css">

<body>

<script>
	parent.document.getElementById('inventoryListDeleteYes').onclick=
	function () {
		parent.document.getElementById('content').src='inventory-list.php?DEL_L=<?php echo $InvNbr ?>';
		parent.document.getElementById('inventoryListDelete').style.display='none';
		parent.document.getElementById('fade').style.display='none';
	};
</script>


<?php
	$query="SELECT VCHR_NBR,VCHR_SER_NBR,AMT,PRN_TS,VLD_TS,RCV_CO_NBR,RCV_PRSN_NBR,ISU_TS,EXP_DT,USE_TS,CRT_CO_NBR,CRT_NBR,CRT_TS
					  FROM CMP.CAP_VCHR
					 WHERE VCHR_NBR=".$VchrNbr;
	//echo $query;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
?>

<?php if(($Security==0)&&($InvNbr!=0)) { ?>
	<div class="toolbar-only">
		<p class="toolbar-left"><a href="javascript:void(0)" onclick="window.scrollTo(0,0);parent.document.getElementById('inventoryListDelete').style.display='block';parent.document.getElementById('fade').style.display='block'"><span class='fa fa-trash toolbar' style="cursor:pointer"></span></a></p>
	</div>
<?php } ?>

<form enctype="multipart/form-data" action="#" method="post" style="width:600px" onSubmit="return checkform();">
	<p>
		<h3>
			Nomor Seri: <?php echo $row['VCHR_SER_NBR'];if($row['VCHR_SER_NBR']==""){echo "Baru";} ?>
		</h3>

		<input name="VCHR_NBR" value="<?php echo $row['VCHR_NBR'];if($row['VCHR_NBR']==""){echo "-1";} ?>" type="hidden" />

		<label>Nominal</label><br /><div class='labelbox'></div>
		<select name="AMT" class="chosen-select">
			<?php
				$query="SELECT AMT
						FROM CMP.CAP_VCHR_AMT ORDER BY 1";
				genCombo($query,"AMT","AMT",$row['AMT']);
			?>
		</select><br /><div class="combobox"></div>

		<label>Waktu Cetak</label><br />
		<input name=PRN_TS value="<?php echo $row['PRN_TS']; ?>" type="text" size="20" readonly /><br />
	
		<label>Waktu Validasi</label><br />
		<input name="VLD_TS" value="<?php echo $row['VLD_TS']; ?>" type="text" size="20" readonly /><br />
	
		<label>Perorangan</label><br /><div class='labelbox'></div>
		<select name="RCV_PRSN_NBR" class="chosen-select" style='width:500px' <?php if(!(($row['VLD_TS']!="")&&($row['ISU_TS']==""))){echo "disabled='disabled'";} ?>>
		<?php
			$query="SELECT PRSN_NBR,CONCAT(NAME,' ',ADDRESS,' ',CITY_NM) AS PRSN_DESC
					FROM CMP.PEOPLE PPL INNER JOIN
					CMP.CITY CIT ON PPL.CITY_ID=CIT.CITY_ID ORDER BY 2";
			genCombo($query,"PRSN_NBR","PRSN_DESC",$row['RCV_PRSN_NBR'],"Kosong");
		?>
		</select><br /><div class="combobox"></div>
	
		<label>Perusahaan</label><br /><div class='labelbox'></div>
		<select name="RCV_CO_NBR" class="chosen-select" style='width:500px'<?php if(!(($row['VLD_TS']!="")&&($row['ISU_TS']==""))){echo "disabled='disabled'";} ?>>
		<?php
			$query="SELECT CO_NBR,CONCAT(NAME,' ',ADDRESS,' ',CITY_NM) AS CO_DESC
					FROM CMP.COMPANY COM INNER JOIN
					CMP.CITY CIT ON COM.CITY_ID=CIT.CITY_ID ORDER BY 2";
			genCombo($query,"CO_NBR","CO_DESC",$row['RCV_CO_NBR'],"Kosong");
		?>
		</select><br /><div class="combobox"></div>

		<label>Waktu Issue</label><br />
		<input name="ISU_TS" value="<?php echo $row['ISU_TS']; ?>" size="20" type="text" readonly /><br />
	
		<label>Tanggal Kadaluwarsa</label><br />
		<input name="EXP_DT" value="<?php echo $row['EXP_DT']; ?>" type="text" size="20" readonly /><br />
	
		<label>Waktu Digunakan</label><br />
		<input name="USE_TS" value="<?php echo $row['USE_TS']; ?>" type="text" size="20" /><br />
		
		<?php
			if($row['CRT_TS']==""){$button="Buat";}
			elseif($row['PRN_TS']==""){$button="Cetak";}
			elseif($row['VLD_TS']==""){$button="Validasi";}
			elseif($row['ISU_TS']==""){$button="Issue";}
			elseif($row['USE_TS']==""){$button="Guna";}
		?>
		
		<input name="UPD_FLD" value="<?php echo $button; ?>" type="hidden" />
		<?php
			if(($button!="")&&($Security<=2)){
				if(($button=="Issue")||($button=="Guna")){
					echo "<input class='process' type='submit' value='$button'/>";
				}elseif($Security<=1){
					echo "<input class='process' type='submit' value='$button'/>";
				}
			}
		?>

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
</body>
</html>

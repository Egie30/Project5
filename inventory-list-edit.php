<?php
	include "framework/database/connect-cloud.php";
	include_once"framework/functions/default.php";
	include "framework/security/default.php";
	include "framework/alert/alert.php";

	$InvNbr=$_GET['INV_NBR'];
	$Security=getSecurity($_SESSION['userID'],"Inventory");
	//Process changes here
	if($_POST['INV_NBR']!="")
	{
		$InvNbr=$_POST['INV_NBR'];

		//Process add new
		if($InvNbr==-1)
		{
			$query="SELECT COALESCE(MAX(INV_NBR),0)+1 AS NEW_NBR FROM CMP.INVENTORY";
			$result=mysql_query($query);
			$row=mysql_fetch_array($result);
			//echo $query;
			$InvNbr=$row['NEW_NBR'];
			$query="INSERT INTO CMP.INVENTORY (INV_NBR) VALUES (".$InvNbr.")";
			$result=mysql_query($query);
		}
		//Take care of nulls
		if($_POST['CTN_NBR']==""){$CtnNbr="NULL";}else{$CtnNbr=$_POST['CTN_NBR'];}
		if($_POST['PRC']==""){$Prc="NULL";}else{$Prc=$_POST['PRC'];}

		$query="UPDATE CMP.INVENTORY
	   			SET INV_TYP='".$_POST['INV_TYP']."',
	   				CO_NBR=".$_POST['CO_NBR'].",
	   				NAME='".$_POST['NAME']."',
	   				COLR_NBR=".$_POST['COLR_NBR'].",
	   				THIC='".$_POST['THIC']."',
	   				SIZE='".$_POST['SIZE']."',
	   				WEIGHT='".$_POST['WEIGHT']."',
	   				UNIT='".$_POST['UNIT']."',
	   				CTN_NBR='".$CtnNbr."',
	   				PRC='".$Prc."',
	   				SPL_NTE='".$_POST['SPL_NTE']."',
					UPD_DTE=CURRENT_DATE,
					UPD_NBR=".$_SESSION['personNBR']."
					WHERE INV_NBR=".$InvNbr;
		//echo $query;
	   	$result=mysql_query($query);
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />

<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />

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
	$query="SELECT INV.INV_TYP,CO_NBR,NAME,INV.COLR_NBR,THIC,SIZE,WEIGHT,UNIT,CTN_NBR,SPL_NTE,CONCAT(NAME,' ',COLR_DESC,' ',THIC,' ',SIZE,' ',WEIGHT) AS NAME_DESC,PRC
			FROM CMP.INVENTORY INV INNER JOIN
			CMP.INV_TYP TYP ON INV.INV_TYP=TYP.INV_TYP INNER JOIN
			CMP.INV_COLR CLR ON INV.COLR_NBR=CLR.COLR_NBR
			WHERE INV_NBR=".$InvNbr;
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
		<h2>
			<?php echo $row['NAME'];if($row['NAME']==""){echo "Nama Baru";} ?>
		</h2>

		<h3>
			Nomor: <?php echo $row['INV_NBR'];if($row['INV_NBR']==""){echo "Baru";} ?>
		</h3>

		<input name="INV_NBR" value="<?php echo $row['INV_NBR'];if($row['INV_NBR']==""){echo "-1";} ?>" type="hidden" />

		<label>Jenis</label><br /><div class='labelbox'></div>
		<select name="INV_TYP" class="chosen-select">
			<?php
				$query="SELECT INV_TYP,INV_TYP_DESC
						FROM CMP.INV_TYP ORDER BY 2";
				genCombo($query,"INV_TYP","INV_TYP_DESC",$row['INV_TYP']);
			?>
		</select><br /><div class="combobox"></div>

		<label>Perusahaan</label><br /><div class='labelbox'></div>
		<select name="CO_NBR" style='width:550px' class="chosen-select">
			<?php
				$query="SELECT CO_NBR,CONCAT(NAME,' ',ADDRESS,' ',CITY_NM) AS CO_DESC
						FROM CMP.COMPANY COM INNER JOIN
						CMP.CITY CIT ON COM.CITY_ID=CIT.CITY_ID ORDER BY 2";
				genCombo($query,"CO_NBR","CO_DESC",$row['CO_NBR']);
			?>
		</select><br /><div class="combobox"></div>
	
		<label>Nama</label><br /><div class='labelbox'></div>
		<input name="NAME" value="<?php echo $row['NAME']; ?>" type="text" size="30" /><br />
	
		<label>Warna</label><br />
		<select name="COLR_NBR" class="chosen-select">
		<?php
			$query="SELECT COLR_NBR,COLR_DESC
					FROM CMP.INV_COLR ORDER BY 2";
			genCombo($query,"COLR_NBR","COLR_DESC",$row['COLR_NBR']);
		?>
		</select><br /><div class="combobox"></div>
	
		<label>Bahan</label><br />
		<input name="THIC" value="<?php echo $row['THIC']; ?>" size="15" /><br />
	
		<label>Ukuran</label><br />
		<input name="SIZE" value="<?php echo $row['SIZE']; ?>" type="text" size="15" /><br />
	
		<label>Tipe</label><br />
		<input name="WEIGHT" value="<?php echo $row['WEIGHT']; ?>" type="text" size="15" /><br />
	
		<label>Unit</label><br />
		<input name="UNIT" value="<?php echo $row['UNIT']; ?>" type="text" size="15" /><br />
	
		<label>Jumlah Isi</label><br />
		<input name="CTN_NBR" value="<?php echo $row['CTN_NBR']; ?>" type="text" size="15" /><br />
	
		<label>Harga</label><br />
		<input name="PRC" value="<?php echo $row['PRC']; ?>" type="text" size="15" /><br />
	
		<label>Catatan</label><br />
		<textarea name="SPL_NTE" style="width:400px;height:40px;"><?php echo $row['SPL_NTE']; ?></textarea><br />
	
		<input class="process" type="submit" value="Simpan"/>
	
		</p>
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
	</form>
</body>
</html>

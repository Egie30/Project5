<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";

	$StaNbr=$_GET['STA_NBR'];
	$Security=getSecurity($_SESSION['userID'],"Stationery");
	//Process changes here
	if($_POST['STA_NBR']!="")
	{
		$StaNbr=$_POST['STA_NBR'];

		//Process add new
		if($StaNbr==-1)
		{
			$query="SELECT COALESCE(MAX(STA_NBR),0)+1 AS NEW_NBR FROM CMP.STATIONERY";
			$result=mysql_query($query);
			$row=mysql_fetch_array($result);
			//echo $query;
			$StaNbr=$row['NEW_NBR'];
			$query="INSERT INTO CMP.STATIONERY (STA_NBR) VALUES (".$StaNbr.")";
			$result=mysql_query($query);
		}
		//Take care of nulls
		if($_POST['CTN_NBR']==""){$CtnNbr="NULL";}else{$CtnNbr=$_POST['CTN_NBR'];}
		if($_POST['PRC']==""){$Prc="NULL";}else{$Prc=$_POST['PRC'];}

		$query="UPDATE CMP.STATIONERY
	   			SET STA_TYP='".$_POST['STA_TYP']."',
	   				CO_NBR=".$_POST['CO_NBR'].",
	   				NAME='".$_POST['NAME']."',
	   				COLR_NBR=".$_POST['COLR_NBR'].",
	   				MATR='".$_POST['MATR']."',
	   				SIZE='".$_POST['SIZE']."',
	   				TYPE='".$_POST['TYPE']."',
	   				UNIT='".$_POST['UNIT']."',
	   				CTN_NBR='".$CtnNbr."',
	   				PRC='".$Prc."',
	   				SPL_NTE='".$_POST['SPL_NTE']."',
					UPD_DTE=CURRENT_DATE,
					UPD_NBR=".$_SESSION['personNBR']."
					WHERE STA_NBR=".$StaNbr;
		//echo $query;
	   	$result=mysql_query($query);
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />

<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>

<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />

<body>

<script>
	parent.document.getElementById('inventoryListDeleteYes').onclick=
	function () {
		parent.document.getElementById('content').src='stationery-list.php?DEL_L=<?php echo $StaNbr ?>';
		parent.document.getElementById('inventoryListDelete').style.display='none';
		parent.document.getElementById('fade').style.display='none';
	};
</script>


<?php
	$query="SELECT STA_NBR,STA.STA_TYP,CO_NBR,NAME,STA.COLR_NBR,MATR,SIZE,TYPE,UNIT,CTN_NBR,SPL_NTE,CONCAT(NAME,' ',COLR_DESC,' ',MATR,' ',SIZE,' ',TYPE) AS NAME_DESC,PRC
			FROM CMP.STATIONERY STA INNER JOIN
				 CMP.STA_TYP TYP ON STA.STA_TYP=TYP.STA_TYP INNER JOIN
				 CMP.STA_COLR CLR ON STA.COLR_NBR=CLR.COLR_NBR
			WHERE STA_NBR=".$StaNbr;
			//echo $query;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
?>

<?php if(($Security==0)&&($StaNbr!=0)) { ?>
	<div class="toolbar-only">
		<p class="toolbar-left"><a href="javascript:void(0)" onclick="window.scrollTo(0,0);parent.document.getElementById('inventoryListDelete').style.display='block';parent.document.getElementById('fade').style.display='block'"><img style="cursor:pointer" class="toolbar-left" src="img/delete.png"></a></p>
	</div>
<?php } ?>

<form enctype="multipart/form-data" action="#" method="post" style="width:600px" onSubmit="return checkform();">
	<p>
		<h2>
			<?php echo $row['NAME'];if($row['NAME']==""){echo "Nama Baru";} ?>
		</h2>

		<h3>
			Nomor: <?php echo $row['STA_NBR'];if($row['STA_NBR']==""){echo "Baru";} ?>
		</h3>

		<input name="STA_NBR" value="<?php echo $row['STA_NBR'];if($row['STA_NBR']==""){echo "-1";} ?>" type="hidden" />

		<label>Jenis</label><br />
		<select name="STA_TYP">
			<?php
				$query="SELECT STA_TYP,STA_TYP_DESC
						FROM CMP.STA_TYP ORDER BY 2";
				genCombo($query,"STA_TYP","STA_TYP_DESC",$row['STA_TYP']);
			?>
		</select><br />

		<label>Perusahaan</label><br />
		<select name="CO_NBR" style='width:550px'>
			<?php
				$query="SELECT CO_NBR,CONCAT(NAME,' ',ADDRESS,' ',CITY_NM) AS CO_DESC
						FROM CMP.COMPANY COM INNER JOIN
						CMP.CITY CIT ON COM.CITY_ID=CIT.CITY_ID ORDER BY 2";
				genCombo($query,"CO_NBR","CO_DESC",$row['CO_NBR']);
			?>
		</select><br />
	
		<label>Nama</label><br />
		<input name="NAME" value="<?php echo $row['NAME']; ?>" type="text" size="30" /><br />
	
		<label>Warna</label><br />
		<select name="COLR_NBR">
		<?php
			$query="SELECT COLR_NBR,COLR_DESC
					FROM CMP.STA_COLR ORDER BY 2";
			genCombo($query,"COLR_NBR","COLR_DESC",$row['COLR_NBR']);
		?>
		</select><br />
	
		<label>Bahan</label><br />
		<input name="MATR" value="<?php echo $row['MATR']; ?>" size="15" /><br />
	
		<label>Ukuran</label><br />
		<input name="SIZE" value="<?php echo $row['SIZE']; ?>" type="text" size="15" /><br />
	
		<label>Tipe</label><br />
		<input name="TYPE" value="<?php echo $row['TYPE']; ?>" type="text" size="15" /><br />
	
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
	</form>
</body>
</html>

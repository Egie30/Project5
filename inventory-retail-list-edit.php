<?php
	include "framework/database/connect-cloud.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	include "framework/functions/dotmatrix.php";

	$InvNbr=$_GET['INV_NBR'];
	$Security		= getSecurity($_SESSION['userID'],"Inventory,Payroll");
	
	//get information schema for journal
	$query_info	= "SELECT TABLE_NAME, COLUMN_NAME, COLUMN_KEY FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'RTL' AND TABLE_NAME ='INVENTORY'";
	$result_info= mysql_query($query_info);
	$array_info	= array();
	while ($row_info = mysql_fetch_array($result_info)){
		if ($row_info['COLUMN_KEY']=="PRI") { $PK = $row_info['COLUMN_NAME']; }
		array_push($array_info,$row_info['COLUMN_NAME']);
	}
	
	//get data awal
	$query_awal	= "SELECT * FROM RTL.INVENTORY WHERE INV_NBR='$InvNbr'";
	$result_awal= mysql_query($query_awal);
	$row_awal	= mysql_fetch_assoc($result_awal);
	
	//Process changes here
	if(($_POST['INV_NBR']!="")&&($cloud!=false))
	{
		$j=syncTable("INVENTORY","INV_NBR","RTL",$RTL,$local,$cloud);
	
		$InvNbr=$_POST['INV_NBR'];

		//Process add new
		if($InvNbr==-1)
		{
			$query="SELECT COALESCE(MAX(INV_NBR),0)+1 AS NEW_NBR FROM $RTL.INVENTORY";
			$result=mysql_query($query,$cloud);
			$row=mysql_fetch_array($result);
			//echo $query;
			$InvNbr=$row['NEW_NBR'];
			$query="INSERT INTO $RTL.INVENTORY (INV_NBR) VALUES (".$InvNbr.")";
			$result=mysql_query($query,$cloud);
			$query=str_replace($RTL,"RTL",$query);
			$result=mysql_query($query,$local);
		}
		//Take care of nulls
		if($_POST['CAT_SUB_NBR']==""){
			$CatNbr="NULL";
			$CatSubNbr="NULL";
		}else{
			$Cats=explode("-",$_POST['CAT_SUB_NBR']);
			$CatNbr=$Cats[0];
			$CatSubNbr=$Cats[1];
		}
		if($_POST['INV_PRC']==""){$InvPrc="NULL";}else{$InvPrc=$_POST['INV_PRC'];}
		if($_POST['CAT_DISC_NBR']==""){$CatDiscNbr="NULL";}else{$CatDiscNbr=$_POST['CAT_DISC_NBR'];}
		if($_POST['CAT_SHLF_NBR']==""){$CatShlfNbr="NULL";}else{$CatShlfNbr=$_POST['CAT_SHLF_NBR'];}
		if($_POST['CAT_PRC_NBR']==""){$CatPrcNbr="NULL";}else{$CatPrcNbr=$_POST['CAT_PRC_NBR'];}
		if($_POST['INV_NBR_CHD']==""){$InvNbrChd="NULL";}else{$InvNbrChd=$_POST['INV_NBR_CHD'];}
		if($_POST['CONV_CHD']==""){$ConvChd="NULL";}else{$ConvChd=$_POST['CONV_CHD'];}
		//COUNT X
		if($_POST['CNT_X']==""){$CntX="NULL";}else{$CntX=$_POST['CNT_X'];}
		if($_POST['PRC']==""){$Prc="NULL";}else{$Prc=$_POST['PRC'];}
		if($_POST['CNT_X_TYP']==""){$CntXTyp="NULL";}else{$CntXTyp=$_POST['CNT_X_TYP'];}
		//COUNT Y
		if($_POST['CNT_Y']==""){$CntY="NULL";}else{$CntY=$_POST['CNT_Y'];}
		if($_POST['PRC_Y']==""){$PrcY="NULL";}else{$PrcY=$_POST['PRC_Y'];}
		if($_POST['CNT_Y_TYP']==""){$CntYTyp="NULL";}else{$CntYTyp=$_POST['CNT_Y_TYP'];}
		//COUNT Z
		if($_POST['CNT_Z']==""){$CntZ="NULL";}else{$CntZ=$_POST['CNT_Z'];}
		if($_POST['PRC_Z']==""){$PrcZ="NULL";}else{$PrcZ=$_POST['PRC_Z'];}
		if($_POST['CNT_Z_TYP']==""){$CntZTyp="NULL";}else{$CntZTyp=$_POST['CNT_Z_TYP'];}

		if($_POST['CONV_CHD_Y'] == "on"){
			//$ConvChd="Y";
			$query 	= "SELECT CNT_Y,CNT_X,PRC_Y,PRC,CNT_Y_TYP,CNT_X_TYP FROM RTL.INVENTORY WHERE INV_NBR='$_POST[INV_NBR_CHD]'";
			$result = mysql_query($query);
			$row 	= mysql_fetch_array($result);
			$CntY 	= $row['CNT_Y'];
			$PrcY 	= $row['PRC_Y'];
			$CntYTyp= $row['CNT_Y_TYP'];			
			$CntX 	= $row['CNT_X'];
			$Prc 	= $row['PRC'];
			$CntXTyp= $row['CNT_X_TYP'];
		}

		if($_POST['CONV_CHD_Z'] == "on"){
			//$ConvChd="Z";
			$query 	= "SELECT CNT_Z,CNT_X,PRC_Z,PRC,CNT_Z_TYP,CNT_X_TYP FROM RTL.INVENTORY WHERE INV_NBR='$_POST[INV_NBR_CHD]'";
			$result = mysql_query($query);
			$row 	= mysql_fetch_array($result);
			$CntZ 	= $row['CNT_Z'];
			$PrcZ 	= $row['PRC_Z'];
			$CntZTyp= $row['CNT_Z_TYP'];
			$CntX 	= $row['CNT_X'];
			$Prc 	= $row['PRC'];
			$CntXTyp= $row['CNT_X_TYP'];			
		}
		
		if($_POST['INV_BCD']==""){
			$InvBcd=LeadZero(Luhn($InvNbr),8);
		}else{
			$InvBcd=$_POST['INV_BCD'];
		}

		$query1="UPDATE $RTL.INVENTORY
	   				SET CO_NBR=".$_POST['CO_NBR'].",
	   				NAME='".mysql_real_escape_string($_POST['NAME'])."',
	   				CAT_NBR=".$CatNbr.",
	   				CAT_SUB_NBR=".$CatSubNbr.",
	   				INV_BCD='".$InvBcd."',
	   				INV_PRC=".$InvPrc.",";
		$query2=   "CAT_DISC_NBR=".$CatDiscNbr.",
	   				CAT_SHLF_NBR=".$CatShlfNbr.",";
		$query3=   "CAT_PRC_NBR=".$CatPrcNbr.",
                    UNIT_TYP='".$_POST['UNIT_TYP']."',
                    INV_NBR_CHD=".$InvNbrChd.",
                    CONV_CHD=".$ConvChd.",
					CNT_X=".$CntX.",
	   				PRC=".$Prc.",
	   				CNT_X_TYP='".$CntXTyp."',
					CNT_Y=".$CntY.",
	   				PRC_Y=".$PrcY.",
	   				CNT_Y_TYP='".$CntYTyp."',
					CNT_Z=".$CntZ.",
	   				PRC_Z=".$PrcZ.",
	   				CNT_Z_TYP='".$CntZTyp."',
					COLR_NBR='".$_POST['COLR_NBR']."',
					THIC='".$_POST['THIC']."',
	   				SIZE='".$_POST['SIZE']."',
	   				WEIGHT='".$_POST['WEIGHT']."',
	   				PRD_PRC_TYP='".$_POST['PRD_PRC_TYP']."',
	   				SPL_NTE='".$_POST['SPL_NTE']."',
					UPD_TS=CURRENT_TIMESTAMP,
					UPD_NBR=".$_SESSION['personNBR']."
					WHERE INV_NBR=".$InvNbr;
		$query=$query1.$query2.$query3;
		
		//echo $query;
	   	$result=mysql_query($query,$cloud);
		$query=str_replace($RTL,"RTL",$query);
		$result=mysql_query($query,$local);
		
		//get_data_akhir
		$query_akhir	= "SELECT * FROM RTL.INVENTORY WHERE INV_NBR='$InvNbr'";
		$result_akhir	= mysql_query($query_akhir);
		$row_akhir		= mysql_fetch_assoc($result_akhir);
		
		for ($i=0;$i<count($array_info);$i++){
			if ($row_awal[$array_info[$i]]!=$row_akhir[$array_info[$i]]) {
				$query_jrn	= "INSERT INTO $CMP.JRN_LIST (JRN_LIST_NBR, DB_NM, TBL_NM, COL_NM, PK, PK_DTA, REC_BEG, REC_END, CRT_TS, CRT_NBR) VALUES 
								('','".$RTL."','INVENTORY','".$array_info[$i]."','$PK','$InvNbr','".$row_awal[$array_info[$i]]."','".$row_akhir[$array_info[$i]]."','".date('Y-m-d H:i:s')."','".$_SESSION['personNBR']."')";
				mysql_query($query_jrn,$cloud);
				$query_jrn=str_replace($CMP,"CMP",$query_jrn);
				mysql_query($query_jrn,$local);
				//echo $query_jrn."<br />";
			}
		}
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
<script type="text/javascript" src="framework/validation/mootools-1.2.3.js"></script>

<script src="framework/database/jquery.min.js" type="text/javascript"></script>
<script src="framework/combobox/chosen.jquery.js" type="text/javascript"></script>


<link rel="stylesheet" href="framework/combobox/chosen.css">

<script>
	parent.document.getElementById('inventoryListDeleteYes').onclick=
	function () {
		parent.document.getElementById('content').src='inventory-retail-list.php?DEL_L=<?php echo $InvNbr ?>';
		parent.document.getElementById('inventoryListDelete').style.display='none';
		parent.document.getElementById('fade').style.display='none';
	};
	function applyVal(sourceObj,destinationID)
	{
		document.getElementById(destinationID).value=sourceObj.value;
	}	
	function activateSubCat(obj){
		var container=obj.parentNode;
		var cbos=container.getElementsByTagName('select');
		for(var count=0;count<cbos.length;count++){
			var curCbo=cbos[count];
			if(curCbo.name=="CAT_SUB_NBR"){
				curCbo.disabled=true;
				curCbo.style.display='none';
			}
		}
		document.getElementById('CAT_SUB_'+obj.value).disabled=false;
		document.getElementById('CAT_SUB_'+obj.value).style.display='';
		
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
	}
	function getInt(objectID)
	{
		if(document.getElementById(objectID).value=="")
		{
			return 0;
		}else{
			return parseInt(document.getElementById(objectID).value);
		}
	}	
	function calcPay(){
		<?php
			$query="SELECT CAT.CAT_PRC_NBR,CAT_PRC_AMT,CAT_PRC_RND,CAT_PRC_PCT,CAT_PRC_LES
					FROM RTL.CAT_PRC CAT";
				
			$result=mysql_query($query);
			while($row=mysql_fetch_array($result)){
				echo "if(document.getElementById('CAT_PRC_NBR').value == ".$row['CAT_PRC_NBR']."){ \n";
					if($row['CAT_PRC_PCT']!=""){
						echo "document.getElementById('PRC').value=Math.ceil(document.getElementById('INV_PRC').value*(1+".$row['CAT_PRC_PCT']."/100)/".$row['CAT_PRC_RND'].")*".$row['CAT_PRC_RND']."-".$row['CAT_PRC_LES']."; \n";
					}else{
						echo "document.getElementById('PRC').value=Math.ceil(document.getElementById('INV_PRC').value*(1+".$row['CAT_PRC_AMT'].")/".$row['CAT_PRC_RND'].")*".$row['CAT_PRC_RND']."-".$row['CAT_PRC_LES']."; \n";
					}
				echo "} \n";
			}
		?>
	}
</script>
</head>
<body>

<div class="toolbar-only">
	<?php 
	if(($cloud!=false)&&(paramCloud()==1)){ 
	if(($Security==0)&&($InvNbr!=0)) { ?>
		<p class="toolbar-left"><a href="javascript:void(0)" onclick="window.scrollTo(0,0);parent.document.getElementById('inventoryListDelete').style.display='block';parent.document.getElementById('fade').style.display='block'"><span class="fa fa-trash toolbar" style="cursor:pointer"></span></a></p>
	<?php }} ?>
	<p class="toolbar-right"><a href="inventory-retail-list-edit-print.php?INV_NBR=<?php echo $InvNbr; ?>"><span class='fa fa-barcode toolbar' style="cursor:pointer"></span></a>
	</p>
</div>

<?php
	$query="SELECT PAY.INV_NBR,CO_NBR,NAME,CAT_NBR,CAT_SUB_NBR,PAY.CAT_PRC_NBR,INV_BCD,INV_PRC,CAT_DISC_NBR,CAT_SHLF_NBR,PPL.CAT_PRC_DESC,PPL.CAT_PRC_PCT,PPL.CAT_PRC_AMT,UNIT_TYP,INV_NBR_CHD,CONV_CHD,PAY.CNT_X,PRC,PAY.CNT_X_TYP,PAY.CNT_Y,PAY.PRC_Y,PAY.CNT_Y_TYP,PAY.CNT_Z,PAY.PRC_Z,PAY.CNT_Z_TYP,PRD_PRC_TYP,SPL_NTE,PAY.UPD_TS,PAY.UPD_NBR,THIC,SIZE,WEIGHT,PAY.COLR_NBR 			
			FROM RTL.INVENTORY PAY LEFT JOIN 
				 RTL.CAT_PRC PPL ON PAY.CAT_PRC_NBR=PPL.CAT_PRC_NBR
			WHERE PAY.INV_NBR=".$InvNbr;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	//echo $query;
?>

<script>
	window.addEvent('domready',function(){
		$('INV_BCD').addEvent('keyup',function(){
			var input_value=this.value;
			if(input_value.length>1){
				new Request.JSON({
					url:"framework/validation/validation.php?form=brc&bcd=<?php echo $row['INV_BCD'];?>",
					onSuccess:function(response){
						if(response.action=='success'){
							$('INV_BCD').removeClass('error');
							$('INV_BCD').addClass('success');
							$('response').set('html','');
							$('submit_button').disabled=false;
							$('submit_button').removeClass('disabled');
							$('submit_button').addClass('blue');						
						}else{
							$('INV_BCD').removeClass('success');
							$('INV_BCD').addClass('error');
							$('response').set('html','<img class="flat" style="vertical-align:text-bottom;" src="img/error.png"> Barcode <b>'+response.INV_BCD+'</b> sudah digunakan');
							
							$('submit_button').disabled=true;
							$('submit_button').removeClass('blue');
							$('submit_button').addClass('disabled');						
						}
					}
				}).get($('signup'));
			}
		
			$('INV_BCD').addEvent('blur',function(e){		
				if(this.value==''){			
					$('INV_BCD').removeClass('success');
					$('INV_BCD').removeClass('error');
					$('response').set('html','');

					$('submit_button').disabled=true;
			    	$('submit_button').removeClass('blue');
			    	$('submit_button').addClass('disabled');				
				}
			});		
		});
	});
</script>
			
<form enctype="multipart/form-data" action="#" method="post" style="width:600px" onSubmit="return checkform();" id="signup">
	<p>
		<h2>
			<?php echo $row['NAME'];if($row['NAME']==""){echo "Nama Baru";} ?>
		</h2>

		<h3>
			Nomor: <?php echo $row['INV_NBR'];if($row['INV_NBR']==""){echo "Baru";} ?>
		</h3>

		<input name="INV_NBR" value="<?php echo $row['INV_NBR'];if($row['INV_NBR']==""){echo "-1";} ?>" type="hidden" />

		<label>Kategori</label><br /><div class='labelbox'></div>
		
		<select name='CAT_SUB_NBR' id='CAT_SUB_NBR' class='chosen-select'>
		<?php
			$query="SELECT CAT_NBR,CAT_DESC FROM RTL.CAT ORDER BY 2";
			$resultc=mysql_query($query);
			while($rowc=mysql_fetch_array($resultc))
			{
				echo "<optgroup label='".$rowc['CAT_DESC']."'>";
				$query="SELECT CAT_SUB_NBR,CAT_SUB_DESC
						FROM RTL.CAT_SUB WHERE CAT_NBR=".$rowc['CAT_NBR']." ORDER BY 2";
				$resultd=mysql_query($query);
				while($rowd=mysql_fetch_array($resultd)){
					echo "<option value='".$rowc['CAT_NBR']."-".$rowd['CAT_SUB_NBR']."'";
					if($rowd['CAT_SUB_NBR']==$row['CAT_SUB_NBR']){echo " selected";}
				echo ">";
				echo removeExtraSpaces($rowd['CAT_SUB_DESC']);
				echo "</option>";
				}
				echo "</optgroup>";
			}
		?>
		</select><br/><div class="combobox"></div>


		<label>Perusahaan</label><br /><div class='labelbox'></div>
		<select name="CO_NBR" style='width:550px' class="chosen-select">
			<?php
				$query="SELECT CO_NBR,CONCAT(NAME,' ',ADDRESS,' ',CITY_NM) AS CO_DESC
						FROM COMPANY COM INNER JOIN
						CITY CIT ON COM.CITY_ID=CIT.CITY_ID WHERE COM.DEL_NBR = 0 ORDER BY 2";
				genCombo($query,"CO_NBR","CO_DESC",$row['CO_NBR'],"",$local);
			?>
		</select><br /><div class="combobox"></div>
	
		<label>Nama</label><br />
		<input name="NAME" value="<?php echo $row['NAME']; ?>" type="text" size="30" /><br />
		
		<label>Warna</label><br />
		<select name="COLR_NBR" class="chosen-select" style='width:200px'>
		<?php
			$query="SELECT COLR_NBR,COLR_DESC
					FROM CMP.INV_COLR ORDER BY 2";
			genCombo($query,"COLR_NBR","COLR_DESC",$row['COLR_NBR'],"Pilih Warna");
		?>
		</select><br /><div class="combobox"></div>
		
		<label>Bahan</label><br />
		<input name="THIC" value="<?php echo $row['THIC']; ?>" size="15" /><br />
	
		<label>Ukuran</label><br />
		<input name="SIZE" value="<?php echo $row['SIZE']; ?>" type="text" size="15" /><br />
	
		<label>Tipe</label><br />
		<input name="WEIGHT" value="<?php echo $row['WEIGHT']; ?>" type="text" size="15" /><br />
	
		<label>Barcode</label><br />
		<input name="INV_BCD" id="INV_BCD" value="<?php echo $row['INV_BCD']; ?>" type="text" size="20" />&nbsp;<span id="response"></span><br />

		<label>Golongan Diskon</label><br /><div class='labelbox'></div>
		<select name="CAT_DISC_NBR" class="chosen-select">
			<?php
				$query="SELECT CAT_DISC_NBR,CAT_DISC_DESC
						FROM RTL.CAT_DISC ORDER BY 2";
				genCombo($query,"CAT_DISC_NBR","CAT_DISC_DESC",$row['CAT_DISC_NBR']);
			?>
		</select><br /><div class="combobox"></div>
	
		<label>Golongan Harga</label><br /><div class='labelbox'></div>
		<select name="CAT_PRC_NBR" id="CAT_PRC_NBR" class="chosen-select" onchange="calcPay(this);">
			<?php
				$query="SELECT CAT_PRC_NBR,CAT_PRC_DESC
						FROM RTL.CAT_PRC ORDER BY 2";
				genCombo($query,"CAT_PRC_NBR","CAT_PRC_DESC",$row['CAT_PRC_NBR']);
			?>
		</select><br /><div class="combobox"></div>
	
		<label>Lokasi Rak</label><br /><div class='labelbox'></div>
		<select name="CAT_SHLF_NBR" class="chosen-select">
			<?php
				$query="SELECT CAT_SHLF_NBR,CAT_SHLF_DESC
						FROM RTL.CAT_SHLF ORDER BY 2";
				genCombo($query,"CAT_SHLF_NBR","CAT_SHLF_DESC",$row['CAT_SHLF_NBR']);
			?>
		</select><br /><div class="combobox"></div>
		
		<label>Harga Faktur</label><br />
		<input name='INV_PRC' id='INV_PRC' size="15" onkeyup="calcPay();" value="<?php echo $row['INV_PRC']; ?>"><br/>
		
		<?php
			if($Security<=1){
				$query="SELECT HED.ORD_NBR,ORD_Q, INV_PRC, TOT_SUB/ORD_Q AS INV_PRC_NET, DATE(DET.CRT_TS) AS CRT_DTE, COM.NAME, ORD_X, ORD_Y, ORD_Z 
						FROM RTL.RTL_STK_DET DET 
							INNER JOIN RTL.RTL_STK_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR 
							INNER JOIN CMP.COMPANY COM ON HED.SHP_CO_NBR=COM.CO_NBR 
						WHERE INV_NBR='$InvNbr' AND HED.DEL_F=0 
						ORDER BY DET.CRT_TS DESC LIMIT 10 
						";
				//echo $query;
				$resultp=mysql_query($query);
				$rows=mysql_num_rows($resultp);
				if($rows>0){
					echo "<h3>History</h3><table>";
					echo "<tr>";
					echo "<th class='listable'>Jumlah</th>";
					echo "<th class='listable'>Ukuran/Isi</th>";
					echo "<th class='listable'>Harga Faktur</th>";
					echo "<th class='listable'>Tanggal</th>";
					echo "<th class='listable'>Pengirim</th>";
					echo "</tr>";
					$alt="";
					while($rowp=mysql_fetch_array($resultp)){
						echo "<tr $alt ";
						if($salesSec<=8){
							echo "onclick=".chr(34)."document.getElementById('Curve').src='print-digital-report-customer-price-curve-chart.php?NBR=C".$CoNbr."&PRN_DIG_TYP=".$rowp['PRN_DIG_TYP']."';".chr(34);
						}
						echo ">";
						echo "<td style='text-align:right;cursor:pointer'>".$rowp['ORD_Q']."</td>";
						echo "<td style='text-align:right;cursor:pointer'>";
							if($rowp['ORD_X']!=''){echo " Uk ".$rowp['ORD_X'];}
							if($rowp['ORD_Y']!=''){echo "x".$rowp['ORD_Y'];}
							if($rowp['ORD_Z']!=''){echo "x".$rowp['ORD_Z'];}
						echo "</td>";
						echo "<td style='text-align:right;cursor:pointer'>".number_format($rowp['INV_PRC_NET'],0,",",".")."</td>";
						echo "<td style='text-align:center;cursor:pointer'>".$rowp['CRT_DTE']."</td>";
						echo "<td style='text-align:center;cursor:pointer'>".$rowp['NAME']."</td>";
						echo "</tr>";
						if($alt==""){$alt="class='alt'";}else{$alt="";}
					}
					echo "</table><br/>";
				}
			}
		?>
        <label>Nomor Inventory Isi</label><br /><div class='labelbox'></div>
		<select name="INV_NBR_CHD" id="INV_NBR_CHD" class="chosen-select" onChange="showResult(this.value);">
			<option value="">Kosong</option>
			<?php
				$query="SELECT INV_NBR, CONCAT(NAME,' ',INV_BCD) AS INV_DESC 
							FROM RTL.INVENTORY WHERE INV_NBR_CHD IS NULL";
				$result=mysql_query($query);
				while($rowx=mysql_fetch_array($result)){
					echo "<option value='$rowx[INV_NBR]'>$rowx[INV_DESC]</option>";
				}
			?>
		</select><br/><div class="combobox"></div>
		<div id="CONV_CHD_DESC"></div>
		<br/>
		<div id="XYZ_DESC" class="typeresult">
		<label>Jumlah Count</label><br/>
		<input name="CNT_X" id="CNT_X" size="5" tabindex="-1" value="<?php echo $row['CNT_X']; ?>">&nbsp;&nbsp;
		<select name="CNT_X_TYP" class="chosen-select" width="5">
			<?php
				$query="SELECT UNIT_TYP,UNIT_DESC
						FROM RTL.UNIT_TYP ORDER BY 2";
				genCombo($query,"UNIT_TYP","UNIT_DESC",$row['CNT_X_TYP'],Kosong);
			?>
		</select>&nbsp;&nbsp;
		<input name="CNT_Y" id="CNT_Y" size="5" tabindex="-1" value="<?php echo $row['CNT_Y']; ?>">&nbsp;&nbsp;
		<select name="CNT_Y_TYP" class="chosen-select">
			<?php
				$query="SELECT UNIT_TYP,UNIT_DESC
						FROM RTL.UNIT_TYP ORDER BY 2";
				genCombo($query,"UNIT_TYP","UNIT_DESC",$row['CNT_Y_TYP'],Kosong);
			?>
		</select>&nbsp;&nbsp;
		<input name="CNT_Z" id="CNT_Z" size="5" tabindex="-1" value="<?php echo $row['CNT_Z']; ?>">&nbsp;&nbsp;
		<select name="CNT_Z_TYP" class="chosen-select">
			<?php
				$query="SELECT UNIT_TYP,UNIT_DESC
						FROM RTL.UNIT_TYP ORDER BY 2";
				genCombo($query,"UNIT_TYP","UNIT_DESC",$row['CNT_Z_TYP'],Kosong);
			?>
		</select><br/>

		<label>Harga Jual</label><br/>
		<input name="PRC" id="PRC" size="15" tabindex="-1" value="<?php echo $row['PRC']; ?>">&nbsp;&nbsp;
		<input name="PRC_Y" id="PRC_Y" size="15" tabindex="-1" value="<?php echo $row['PRC_Y']; ?>">&nbsp;&nbsp;
		<input name="PRC_Z" id="PRC_Z" size="15" tabindex="-1" value="<?php echo $row['PRC_Z']; ?>"><br/>
		</div>
		<label>Daftar Harga Produksi</label><br /><div class='labelbox'></div>
		<select name="PRD_PRC_TYP" class="chosen-select">
			<?php
				$query="SELECT PRN_DIG_TYP,PRN_DIG_DESC
						FROM CMP.PRN_DIG_TYP WHERE DEL_NBR = 0 ORDER BY 2";
				genCombo($query,"PRN_DIG_TYP","PRN_DIG_DESC",$row['PRD_PRC_TYP'],"Kosong",$local);
			?>
		</select><br /><div class="combobox"></div>

		<label>Catatan</label><br />
		<textarea name="SPL_NTE" style="width:400px;height:40px;"><?php echo $row['SPL_NTE']; ?></textarea><br />
		<?php if(($cloud!=false)&&(paramCloud()==1)){ echo '<input class="process" type="submit" value="Simpan"/>'; }?>
		
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
		<script type="text/javascript">
			jQuery.noConflict();
			jQuery(document).ready(function() {
				jQuery('#INV_NBR_CHD').change(function(){
					if(jQuery('#INV_NBR_CHD').val() != ''){  
						jQuery('.typeresult').fadeOut('fast');
					} else {
						jQuery('.typeresult').fadeIn('fast');
					}
				});
			});
		</script>

		<script>
		function showResult(str) {
			if (str == "") {
				document.getElementById("CONV_CHD_DESC").innerHTML="";
				return;
			} else { 
				if (window.XMLHttpRequest) {
					// code for IE7+, Firefox, Chrome, Opera, Safari
					xmlhttp = new XMLHttpRequest();
				} else {
					// code for IE6, IE5
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						document.getElementById("CONV_CHD_DESC").innerHTML = this.responseText;
					}
				};
				xmlhttp.open("GET","inventory-retail-list-edit-ls.php?q="+str,true);
				xmlhttp.send();
			}
		}
		</script>
	</form>
</body>
</html>

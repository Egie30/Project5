<?php
	$POSID=$_GET['POS_ID'];
	$POSIP=$_GET['POSIP'];
	
	if($POSID != "") {
		include "framework/database/connect-cashier.php";
	}
	else {
		include "framework/database/connect.php";
	}
	include "framework/functions/default.php";
	include "framework/security/default.php";
	include "framework/functions/dotmatrix.php";
			
	$POSID=$_GET['POS_ID'];
	$POSIP=$_GET['POSIP'];
		
	$ExpNbr=$_GET['EXP_NBR'];
	$Security=getSecurity($_SESSION['userID'],"Finance");
	$upSecurity=getSecurity($_SESSION['userID'],"Accounting");
	//Process changes here
	if($_POST['EXP_NBR']!="")
	{
		$ExpNbr=$_POST['EXP_NBR'];

		//Process add new
		if($ExpNbr==-1)
		{
			$query="SELECT COALESCE(MAX(EXP_NBR),0)+1 AS NEW_NBR FROM CMP.EXPENSE";
			$result=mysql_query($query);
			$row=mysql_fetch_array($result);
			//echo $query;
			$ExpNbr=$row['NEW_NBR'];
			$query="INSERT INTO CMP.EXPENSE (EXP_NBR,CRT_TS,CRT_NBR) VALUES (".$ExpNbr.",CURRENT_TIMESTAMP,".$_SESSION['personNBR'].")";
			$result=mysql_query($query);
			//echo $query;
			
		}
		//Take care of nulls
		if($_POST['PRSN_NBR']==""){$PrsnNbr="NULL";}else{$PrsnNbr=$_POST['PRSN_NBR'];}
		if($_POST['CO_NBR']==""){$CoNbr="NULL";}else{$CoNbr=$_POST['CO_NBR'];}
		if($_POST['EXP_Q']==""){$ExpQ="0";}else{$ExpQ=$_POST['EXP_Q'];}
		if($_POST['EXP_AMT']==""){$ExpAmt="0";}else{$ExpAmt=$_POST['EXP_AMT'];}
		if($_POST['EXP_ADD']==""){$ExpAdd="0";}else{$ExpAdd=$_POST['EXP_ADD'];}
		if($_POST['TOT_SUB']==""){$TotSub="0";}else{$TotSub=$_POST['TOT_SUB'];}
		$query="UPDATE CMP.EXPENSE
	   			SET EXP_TYP='".$_POST['EXP_TYP']."',
	   				EXP_CO_NBR=".$CoNbrDef.",
	   				PRSN_NBR=".$PrsnNbr.",
	   				CO_NBR=".$CoNbr.",
	   				REF_NBR_INT='".$_POST['REF_NBR_INT']."',
	   				REF_NBR_EXT='".$_POST['REF_NBR_EXT']."',
					EXP_Q=".$ExpQ.",
	   				EXP_AMT=".$ExpAmt.",
	   				EXP_ADD='".$ExpAdd."',
	   				TOT_SUB='".$TotSub."',
					UPD_TS=CURRENT_TIMESTAMP,
					UPD_NBR=".$_SESSION['personNBR']."
					WHERE EXP_NBR=".$ExpNbr;
	   	$result=mysql_query($query);
	   	if($POSID != "" && $POSIP != "") { echo "Ok";
	   	//Go through the cash register
			$transactionServer 	= 0;
			$transactionLocal 	= 1;
			
			$start = microtime(true);
			$limit = 0.1;  // Seconds

			while($transactionServer != $transactionLocal) {
				if (microtime(true) - $start >= $limit) {
				
				$query_cek			= "SELECT TRSC_NBR FROM RTL.CSH_REG_TOKEN";
				$result_cek			= mysql_query($query_cek, $rtl);
				$row_cek			= mysql_fetch_array($result_cek);
				$transactionServer	= $row_cek['TRSC_NBR'];
				
				$transactionLocal 	= $transactionServer+1;
				
				$query 				= "UPDATE RTL.CSH_REG_TOKEN SET TRSC_NBR = TRSC_NBR+1";
				$result				= mysql_query($query, $rtl);			
				
				$query_cek			= "SELECT TRSC_NBR FROM RTL.CSH_REG_TOKEN";
				$result_cek			= mysql_query($query_cek, $rtl);
				$row_cek			= mysql_fetch_array($result_cek);
				$transactionServer	= $row_cek['TRSC_NBR'];
				
				}
			}
			
			$transactionNumber = $transactionServer;
		
			$query 	= "SELECT Q_NBR, DATE(UPD_LAST) AS UPD_LAST FROM CSH.CSH_REG_TOKEN";
			$result	= mysql_query($query, $csh);
			$row	= mysql_fetch_array($result);
			
			if(date("Y-m-d") != $row['UPD_LAST'])
			{	$query = "UPDATE CSH.CSH_REG_TOKEN SET Q_NBR = 1, UPD_LAST = '".date('Y-m-d h:i:s')."' ";
				$result	= mysql_query($query, $csh);
				
				$query_cek			= "SELECT Q_NBR FROM CSH.CSH_REG_TOKEN";
				$result_cek			= mysql_query($query_cek, $csh);
				$row_cek			= mysql_fetch_array($result_cek);
				$QNumber				= $row_cek['Q_NBR'];
			}
			else {
				$query = "UPDATE CSH.CSH_REG_TOKEN SET Q_NBR = (Q_NBR+1), UPD_LAST = '".date('Y-m-d h:i:s')."' ";
				$result	= mysql_query($query, $csh);
				
			
				$query_cek			= "SELECT Q_NBR FROM CSH.CSH_REG_TOKEN";
				$result_cek			= mysql_query($query_cek, $csh);
				$row_cek			= mysql_fetch_array($result_cek);
				$QNumber			= $row_cek['Q_NBR'];
			}

	   	$query="INSERT INTO RTL.CSH_REG (TRSC_NBR,CRT_NBR,Q_NBR,CO_NBR,CSH_FLO_TYP,PYMT_TYP,TND_AMT,ACT_F,EXP_NBR,POS_ID) 
			VALUES (".$transactionNumber.",".$_SESSION['personNBR'].",".$QNumber.",".$CoNbrDef.",'EX','CSH',".$TotSub.",0,".$ExpNbr.",'".$POSID."')";
		$result=mysql_query($query, $rtl);
		$registerNumber = mysql_insert_id($rtl);
		
		$query="SELECT EXP.PRSN_NBR,PPL.NAME AS PPL_NAME,COM.CO_NBR,COM.NAME AS COM_NAME,EXP.EXP_TYP,EXP_DESC,EXP.EXP_CO_NBR,REF_NBR_INT,REF_NBR_EXT,EXP_Q,EXP.EXP_AMT,EXP_ADD,TOT_SUB,EXP.UPD_NBR
				FROM CMP.EXPENSE EXP LEFT OUTER JOIN
				     CMP.COMPANY COM ON EXP.CO_NBR=COM.CO_NBR LEFT OUTER JOIN
				     CMP.PEOPLE PPL ON EXP.PRSN_NBR=PPL.PRSN_NBR INNER JOIN
				     CMP.EXP_TYP TYP ON EXP.EXP_TYP=TYP.EXP_TYP
				WHERE EXP_NBR=".$ExpNbr;
		$result=mysql_query($query);
		
		$row=mysql_fetch_array($result);

	   	$query="INSERT INTO CSH.CSH_REG (REG_NBR,TRSC_NBR,Q_NBR,CRT_NBR,CO_NBR,CSH_FLO_TYP,PYMT_TYP,TND_AMT,ACT_F) 
			VALUES (".$registerNumber.",".$transactionNumber.",".$QNumber.",".$_SESSION['personNBR'].",".$CoNbrDef.",'EX','CSH',".$TotSub.",0)";
		//echo $query;
		mysql_query($query,$csh);
	    echo "<script>parent.document.getElementById('bottom').src='http://".$POSIP."/cashier-bottom.php?TRSC_NBR=".$transactionNumber."&CSH=".$_SESSION['userID']."&Q_NBR=".$QNumber."&PRSN_NBR=".$_SESSION['personNBR']."&RA=PO&PPL_NAME="
		.$row['PPL_NAME']."&COM_NAME=".$row['COM_NAME']."&EXP_DESC=".$row['EXP_DESC']."&REF_NBR_INT=".$row['REF_NBR_INT']."&REF_NBR_EXT="
		.$row['REF_NBR_EXT']."&EXP_AMT=".$row['EXP_AMT']."&EXP_Q=".$row['EXP_Q']."&EXP_ADD=".$row['EXP_ADD']."&TOT_SUB=".$row['TOT_SUB']."';</script>";
	}
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />

<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>

<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />

<link rel="stylesheet" href="framework/combobox/chosen.css">

<body>

<script>
	parent.document.getElementById('expenseDeleteYes').onclick=
	function () {
		parent.document.getElementById('content').src='expense.php?DEL_A=<?php echo $ExpNbr ?>';
		parent.document.getElementById('expenseDelete').style.display='none';
		parent.document.getElementById('fade').style.display='none';
	};
</script>

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

	function calcAmt(){
		document.getElementById('TOT_SUB').value=getInt('EXP_Q')*getInt('EXP_AMT')+getInt('EXP_ADD');
	}
	
	//This needs to live somewhere in the table or parameter 
	function checkform()
	{
		var limit=<?php if($Security==0){echo "1000000000";}elseif($Security==1){echo "1000000";}elseif($Security==2){echo "250000";}else{echo "0";} ?>;
		if(document.getElementById('TOT_SUB').value>limit)
		{
			window.scrollTo(0,0);
			parent.document.getElementById('expenseOverLimit').style.display='block';parent.document.getElementById('fade').style.display='block';
			return false;
		}

		return true;
	}

</script>


<?php
	$query="SELECT EXP_NBR,EXP_TYP,EXP_CO_NBR,PRSN_NBR,CO_NBR,REF_NBR_INT,REF_NBR_EXT,EXP_Q,EXP_AMT,EXP_ADD,TOT_SUB
			FROM CMP.EXPENSE EXP
			WHERE EXP_NBR=".$ExpNbr;
			//echo $query;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
?>

<!--
<div class="toolbar-only">
	<?php if(($Security==0)&&($ExpNbr!=0)) { ?>
		<p class="toolbar-left"><a href="javascript:void(0)" onclick="window.scrollTo(0,0);parent.document.getElementById('expenseDelete').style.display='block';parent.document.getElementById('fade').style.display='block'"><img style="cursor:pointer" class="toolbar-left" src="img/delete.png"></a></p>
	<?php } ?>
	<p class="toolbar-right">
		<a href="expense-edit-print.php?EXP_NBR=<?php echo $ExpNbr; ?>"><img style="cursor:pointer" class="toolbar-right" src="img/print.png"></a>
	</p>
</div>
-->

<form enctype="multipart/form-data" action="#" method="post" style="width:500px" onSubmit="return checkform();">
	<p>
		<h3>
			Nomor: <?php echo $row['EXP_NBR'];if($row['EXP_NBR']==""){echo "Baru";} ?>
		</h3>

		<input name="EXP_NBR" value="<?php echo $row['EXP_NBR'];if($row['EXP_NBR']==""){echo "-1";} ?>" type="hidden" />

		<label>Jenis</label><br /><div class='labelbox'></div>
		<select name="EXP_TYP" class="chosen-select">
			<?php
				$query="SELECT EXP_TYP,EXP_DESC
						FROM CMP.EXP_TYP ORDER BY 2";
				genCombo($query,"EXP_TYP","EXP_DESC",$row['EXP_TYP']);
			?>
		</select><br /><div class="combobox"></div>

		<label>Nama Petugas</label><br /><div class='labelbox'></div>
		<select name="PRSN_NBR" style='width:450px' class="chosen-select">
			<?php
				$query="SELECT PRSN_NBR,NAME AS PRSN_DESC
						FROM CMP.PEOPLE PPL INNER JOIN
						CMP.CITY CIT ON PPL.CITY_ID=CIT.CITY_ID WHERE CO_NBR=".$CoNbrDef." AND TERM_DTE IS NULL ORDER BY 2";
				genCombo($query,"PRSN_NBR","PRSN_DESC",$row['PRSN_NBR'],"Kosong");
			?>
		</select><br /><div class="combobox"></div>

		<label>Client</label><br /><div class='labelbox'></div>
		<select name="CO_NBR" style='width:450px' class="chosen-select">
			<?php
				$query="SELECT CO_NBR,CONCAT(NAME,' ',ADDRESS,' ',CITY_NM) AS CO_DESC
						FROM CMP.COMPANY COM INNER JOIN
						CMP.CITY CIT ON COM.CITY_ID=CIT.CITY_ID ORDER BY 2";
				genCombo($query,"CO_NBR","CO_DESC",$row['CO_NBR'],"Kosong");
			?>
		</select><br /><div class="combobox"></div>
	
		<label>Nomor referensi internal</label><br />
		<input name="REF_NBR_INT" value="<?php echo $row['REF_NBR_INT']; ?>" type="text" size="50" /><br />
	
		<label>Nomor referensi eksternal</label><br />
		<input name="REF_NBR_EXT" value="<?php echo $row['REF_NBR_EXT']; ?>" type="text" size="50" /><br />
	
		<label>Jumlah</label><br />
		<input name="EXP_Q" id="EXP_Q" value="<?php echo $row['EXP_Q']; ?>" size="15" onkeyup="calcAmt();" onchange="calcAmt();" /><br />
	
		<label>Nominal</label><br />
		<input name="EXP_AMT" id="EXP_AMT" value="<?php echo $row['EXP_AMT']; ?>" type="text" size="15" onkeyup="calcAmt();" /><br />
	
		<label>Tambahan biaya</label><br />
		<input name="EXP_ADD" id="EXP_ADD" value="<?php echo $row['EXP_ADD']; ?>" type="text" size="15" onkeyup="calcAmt();" /><br />
	
		<label>Total</label><br />
		<input name="TOT_SUB" id="TOT_SUB" value="<?php echo $row['TOT_SUB']; ?>" type="text" size="15" onkeyup="calcAmt();" readonly /><br />
		
		<?php
			if($row['EXP_NBR']=="" || $upSecurity == 0){
				echo "<input class='process' type='submit' value='Bayar'/>";
			}
		?>
	
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

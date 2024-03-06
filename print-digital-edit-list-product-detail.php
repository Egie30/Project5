<?php
	@header("Connection: close\r\n"); 
	include "framework/database/connect-cloud.php";
	include "framework/functions/default.php";
	include "framework/functions/print-digital.php";
	include "framework/security/default.php";

	$UpperSec = getSecurity($_SESSION['userID'],"Accounting");
	$OrdNbr			= $_GET['ORD_NBR'];
	$OrdDetNbr		= $_GET['ORD_DET_NBR'];
	$OrdDetNbrPar	= $_GET['ORD_DET_NBR_PAR'];
	$type		= $_GET['TYP'];
	$changed	= false;
	$addNew		= false;
	
	if($type == "EST"){
		$headtable 	= "CMP.PRN_DIG_ORD_HEAD_EST";
		$detailtable= "CMP.PRN_DIG_ORD_DET_EST";
	}else{
		$headtable 	= "CMP.PRN_DIG_ORD_HEAD";
		$detailtable= "CMP.PRN_DIG_ORD_DET";
	}
	
	//Get order head information
	$query="SELECT BUY_PRSN_NBR,BUY_CO_NBR,BRKR_PLAN_TYP,HED.MBR_NBR,COALESCE(MBR_TYP_DISC,0) AS MBR_TYP_DISC
			FROM ". $headtable ." HED LEFT OUTER JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR=PPL.PRSN_NBR
			WHERE ORD_NBR=".$_GET['ORD_NBR'];
	//echo $query;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	$MbrNbr = $row['MBR_NBR'];
	$DiscPPC= $row['MBR_TYP_DISC'];
	if($row['BUY_CO_NBR']!=""){
		$where="AND CO_NBR=".$row['BUY_CO_NBR'];
		$CoNbr=$row['BUY_CO_NBR'];
	}elseif($row['BUY_PRSN_NBR']!=""){
		$where="AND PRSN_NBR=".$row['BUY_PRSN_NBR'];
		$PrsnNbr=$row['BUY_CO_NBR'];
	}
	//echo $where;
	
	//Check to see if brokerage
	if($row['BRKR_PLAN_TYP']!=""){
		$broker=true;
	}

	//Process changes here
	if($_POST['ORD_DET_NBR']!="")
	{
		
		$OrdDetNbr               = $_POST['ORD_DET_NBR'];
		//Take care of nulls
		if($_POST['ORD_Q']       == ""){$OrdQ="NULL";}else{$OrdQ=$_POST['ORD_Q'];}
		if($_POST['FIN_BDR_WID'] == ""){$finBdrWid="NULL";}else{$finBdrWid=$_POST['FIN_BDR_WID'];}
		if($_POST['FIN_LOP_WID'] == ""){$finLopWid="NULL";}else{$finLopWid=$_POST['FIN_LOP_WID'];}
		if($_POST['PRN_LEN']     == ""){$PrnLen="NULL";}else{$PrnLen=$_POST['PRN_LEN'];}
		if($_POST['PRN_WID']     == ""){$PrnWid="NULL";}else{$PrnWid=$_POST['PRN_WID'];}
		if($_POST['PRFO_F']      == ""){$PrfoF="NULL";}else{$PrfoF="1";}
		if($_POST['BK_TO_BK_F']  == ""){$BkToBkF="NULL";}else{$BkToBkF="1";}
		if($_POST['ROLLED_F']    == ""){$RolledF="NULL";}else{$RolledF="1";}
		if($_POST['FEE_MISC']    == ""){$FeeMisc="NULL";}else{$FeeMisc=$_POST['FEE_MISC'];}
		if($_POST['FAIL_CNT']    == ""){$FailCnt="NULL";}else{$FailCnt=$_POST['FAIL_CNT'];}
		if($_POST['DISC_PCT']    == ""){$DiscPct="NULL";}else{$DiscPct=$_POST['DISC_PCT'];}
		if($_POST['DISC_AMT']    == ""){$DiscAmt="NULL";}else{$DiscAmt=$_POST['DISC_AMT'];}
		if($_POST['TOT_SUB']     == ""){$TotSub="NULL";}else{$TotSub=$_POST['TOT_SUB'];}
		if($_POST['LOW_PRC_F']   == "on"){$lowPrice=1;}else{$lowPrice=0;}
		
		//Process add new
		if($OrdDetNbr==-1){
			$addNew    = true;
			$query     = "SELECT COALESCE(MAX(ORD_DET_NBR),0)+1 AS NEW_NBR FROM ". $detailtable ."";
			$result    = mysql_query($query);
			$row       = mysql_fetch_array($result);
			$OrdDetNbr = $row['NEW_NBR'];
			$query     = "INSERT INTO ". $detailtable ." (ORD_DET_NBR) VALUES (".$OrdDetNbr.")";
			$result    = mysql_query($query);
			$create    = "CRT_TS=CURRENT_TIMESTAMP,CRT_NBR=".$_SESSION['personNBR'].",";
		}
		
		//Process job length
		$JobLen=jobLength($_POST['PRN_DIG_TYP'],$OrdQ,$PrnLen,$PrnWid);
		
		//Process child row
		if($OrdDetNbrPar!=""){$childRow="ORD_DET_NBR_PAR=".$OrdDetNbrPar.",";}
		
		$queryP	 = "SELECT PROD_NBR,PROD_DESC FROM CMP.PROD_LST PHEAD WHERE DEL_NBR=0 AND PROD_NBR=".$_POST['PRN_DIG_TYP']."";
		$resultP = mysql_query($queryP);
		$rowP    = mysql_fetch_array($resultP);

		//Digunakan untuk menentukan jika product tersebut pertama kali dibuat bukan hasil update
		$query_awal	= "SELECT PROD_NBR FROM ".$detailtable." WHERE ORD_DET_NBR='".$OrdDetNbr."' AND DEL_NBR=0";
		$result_awal= mysql_query($query_awal);
		$row_awal	= mysql_fetch_array($result_awal);
		$ProdNbrAwal= $row_awal['PROD_NBR'];

			
		$query="UPDATE ". $detailtable ."
	   			SET ORD_NBR=".$OrdNbr.",
	   				PRN_DIG_TYP='PROD',
	   				PROD_NBR = '".$_POST['PRN_DIG_TYP']."',
					ORD_Q=".$OrdQ.",
					DET_TTL='".mysql_real_escape_string($rowP['PROD_DESC'])."',
					FIL_LOC='".mysql_real_escape_string($_POST['FIL_LOC'])."',
					FIL_ATT='".$_FILES['FIL_ATT']['name']."',
					#N_UP='".$_POST['N_UP']."',
					PRN_LEN=".$PrnLen.",
					PRN_WID=".$PrnWid.",
					FIN_BDR_TYP='".$_POST['FIN_BDR_TYP']."',
					FIN_BDR_WID=".$finBdrWid.",
					FIN_LOP_WID=".$finLopWid.",
					GRM_CNT_TOP='".$_POST['GRM_CNT_TOP']."',
					GRM_CNT_BTM='".$_POST['GRM_CNT_BTM']."',
					GRM_CNT_LFT='".$_POST['GRM_CNT_LFT']."',
					GRM_CNT_RGT='".$_POST['GRM_CNT_RGT']."',
					PRFO_F=".$PrfoF.",
					BK_TO_BK_F=".$BkToBkF.",
					ROLLED_F=".$RolledF.",
					PRN_DIG_PRC=0,
					#LOW_PRC_F=".$lowPrice.",
					FEE_MISC=".$FeeMisc.",
					FAIL_CNT=".$FailCnt.",
					DISC_PCT=".$DiscPct.",
					DISC_AMT=".$DiscAmt.",
					TOT_SUB=0,
					HND_OFF_TYP='".$_POST['HND_OFF_TYP']."',".$create."
					JOB_LEN=".$JobLen.",".$childRow."
					UPD_TS=CURRENT_TIMESTAMP,
					UPD_NBR=".$_SESSION['personNBR']."
					WHERE ORD_DET_NBR=".$OrdDetNbr;
	   	$result  = mysql_query($query);
	   	$changed = true;
		
		//Digunakan untuk mengahapus detail product sebelumnnya  
		if (($ProdNbrAwal!="")&&($ProdNbrAwal!=$_POST['PRN_DIG_TYP'])){
			$query_delete	= "DELETE FROM ".$detailtable." WHERE ORD_DET_NBR_PAR='".$OrdDetNbr."'";
			mysql_query($query_delete);
		}

		//Proses update atau insert untuk detail product
		if ($ProdNbrAwal != $_POST['PRN_DIG_TYP']) {
			$query_prod	= "SELECT 
					PHEAD.PROD_NBR,
					PHEAD.PROD_DESC,
					PHEAD.PROD_PRC,
					PDET.PROD_DET_DESC,
					PDET.PROD_DET_X,
					PDET.PROD_DET_Y,
					PDET.FIN_BDR_TYP,
					PDET.PROD_DET_PRC,
					PDET.PRN_DIG_TYP,
					PDET.FEE_MISC,
					PDET.PROD_DET_DESC AS PROD_TTL,
					PDET.TOT_SUB AS DET_TOT_SUB
				FROM CMP.PROD_LST PHEAD 
					LEFT JOIN CMP.PROD_LST_DET PDET ON PHEAD.PROD_NBR=PDET.PROD_NBR 
				WHERE PHEAD.DEL_NBR=0 AND PDET.DEL_NBR=0 AND PDET.PROD_NBR=".$_POST['PRN_DIG_TYP']."";
			$result_prod= mysql_query($query_prod);
			while ($row_prod	= mysql_fetch_array($result_prod)) {
				$queryN		= "SELECT COALESCE(MAX(ORD_DET_NBR),0)+1 AS NEW_NBR FROM ". $detailtable ."";
				$resultN	= mysql_query($queryN);
				$rowN		= mysql_fetch_array($resultN);
				$DetNbrProd	= $rowN['NEW_NBR'];
			
				$printType 	= $row_prod['PRN_DIG_TYP'];
				$ProdNbr    = $row_prod['PROD_NBR'];
				$prodTotSub = $OrdQ * $row_prod['DET_TOT_SUB'];


				if ($row_prod['PROD_TTL']     == ""){$prodDesc   = "''";}else {$prodDesc   = "'".$row_prod['PROD_TTL']."'";}
				if ($row_prod['PROD_DET_X']    == ""){$prodDetX   = "NULL";}else {$prodDetX   = "'".$row_prod['PROD_DET_X']."'";}
				if ($row_prod['PROD_DET_Y']    == ""){$prodDetY   = "NULL";}else {$prodDetY   = "'".$row_prod['PROD_DET_Y']."'";}
				if ($row_prod['FIN_BDR_TYP']  == ""){$prodDetFin = "''";   }else {$prodDetFin = "'".$row_prod['FIN_BDR_TYP']."'";}
				if ($row_prod['PROD_DET_PRC']  == ""){$prodDetPrc = "0";   }else {$prodDetPrc = "'".$row_prod['PROD_DET_PRC']."'";}
				if ($row_prod['FEE_MISC']      == ""){$prodFeeMisc= "NULL";}else {$prodFeeMisc= "'".$row_prod['FEE_MISC']."'";}
										
				$query_ins	= "INSERT INTO ".$detailtable." 
								(ORD_DET_NBR, ORD_NBR, ORD_DET_NBR_PAR, DET_TTL,
								 PRN_DIG_TYP, ORD_Q, PRN_LEN, 
								 PRN_WID, PRN_DIG_PRC, FIN_BDR_TYP, FEE_MISC, 
								 TOT_SUB, HND_OFF_TYP, CRT_TS, 
								 CRT_NBR, UPD_TS, UPD_NBR) 
							  VALUES 
							  	('".$DetNbrProd."', '".$OrdNbr."', '".$OrdDetNbr."',".$prodDesc.",
							  	 '".$printType."', '".$OrdQ."', ".$prodDetX.", 
							  	  ".$prodDetY.", ".$prodDetPrc.", ".$prodDetFin.", ".$prodFeeMisc.", 
							  	 '".$prodTotSub."', '".$_POST['HND_OFF_TYP']."', CURRENT_TIMESTAMP, 
							  	 '".$_SESSION['personNBR']."',  CURRENT_TIMESTAMP, '".$_SESSION['personNBR']."')";
				// echo $query_ins."<br><br>";
				mysql_query($query_ins);
			}
		} else {
			$query_det	= "SELECT ORD_DET_NBR, PRN_DIG_PRC, FEE_MISC, PRN_LEN, PRN_WID, COALESCE(FAIL_CNT,0) AS FAIL_CNT FROM ".$detailtable." WHERE ORD_DET_NBR_PAR='".$OrdDetNbr."'";
			$result_det	= mysql_query($query_det);
			while ($row_det	= mysql_fetch_array($result_det)) {
				if (($row_det['PRN_LEN']==0)||($row_det['PRN_WID']==0)){
					$row_det['PRN_LEN']=1;
					$row_det['PRN_WID']=1;
				} 
				
				$Tot = ($OrdQ-$row_det['FAIL_CNT'])*($row_det['PRN_DIG_PRC']+$row_det['FEE_MISC']) * $row_det['PRN_LEN'] * $row_det['PRN_WID'];
				$query_upd	= "UPDATE ".$detailtable." SET ORD_Q='".$OrdQ."', FEE_MISC='".$row_det['FEE_MISC']."', TOT_SUB='".$Tot."' 
								WHERE ORD_DET_NBR='".$row_det['ORD_DET_NBR']."'";
				mysql_query($query_upd);
			}
		}
	   	
	   	//Update delivery count on the header
	   	$query="SELECT SUM(CASE WHEN HND_OFF_TYP='DL' THEN 1 ELSE 0 END) AS DL_CNT FROM ". $detailtable ." WHERE ORD_NBR=".$OrdNbr;
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		$DLCnt=$row['DL_CNT'];
		if($DLCnt>0){
			$query="UPDATE ". $headtable ." SET DL_CNT=".$DLCnt." WHERE ORD_NBR=".$OrdNbr;
			$result=mysql_query($query);
		}

	   	//Update delivery count on the header
	   	$query="SELECT SUM(CASE WHEN HND_OFF_TYP='DL' THEN 1 ELSE 0 END) AS DL_CNT FROM ". $detailtable ." WHERE ORD_NBR=".$OrdNbr;
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		$DLCnt=$row['DL_CNT'];
		if($DLCnt>0){
			$query="UPDATE ". $headtable ." SET DL_CNT=".$DLCnt." WHERE ORD_NBR=".$OrdNbr;
			$result=mysql_query($query);
		}
		
	   	//Update pick-up count on the header
	   	$query="SELECT SUM(CASE WHEN HND_OFF_TYP='PU' THEN 1 ELSE 0 END) AS PU_CNT FROM ". $detailtable ." WHERE ORD_NBR=".$OrdNbr;
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		$PUCnt=$row['PU_CNT'];
		if($PUCnt>0){
			$query="UPDATE ". $headtable ." SET PU_CNT=".$PUCnt." WHERE ORD_NBR=".$OrdNbr;
			$result=mysql_query($query);
		}

	   	//Update installation count on the header
	   	$query="SELECT SUM(CASE WHEN HND_OFF_TYP='NS' THEN 1 ELSE 0 END) AS NS_CNT FROM ". $detailtable ." WHERE ORD_NBR=".$OrdNbr;
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		$NSCnt=$row['NS_CNT'];
		if($NSCnt>0){
			$query="UPDATE ". $headtable ." SET NS_CNT=".$NSCnt." WHERE ORD_NBR=".$OrdNbr;
			$result=mysql_query($query);
		}

		//Update total job length
	   	$query="SELECT SUM(JOB_LEN) AS JOB_LEN_TOT FROM ". $detailtable ." WHERE ORD_NBR=".$OrdNbr;
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		$JobLenTot=$row['JOB_LEN_TOT'];
		if($JobLenTot>0){
			$query="UPDATE ". $headtable ." SET JOB_LEN_TOT=".$JobLenTot." WHERE ORD_NBR=".$OrdNbr;
			$result=mysql_query($query);
		}

		//Inquire historical discount
		$query="SELECT DISC_AMT
				  FROM CMP.RAT_ENG
				 WHERE PRN_DIG_TYP='".$_POST['PRN_DIG_TYP']."' $where AND DIV_ID='PRN'
				   AND UPD_TS=(SELECT MAX(UPD_TS) FROM CMP.RAT_ENG WHERE PRN_DIG_TYP='".$_POST['PRN_DIG_TYP']."' $where AND DIV_ID='PRN')"; 
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		$lastAmount=$row['DISC_AMT'];
		
		//Check to see if the discount is larger than the currently running promotion
		//Get current promo
		$query="SELECT PROMO_DISC_AMT FROM CMP.PRN_DIG_PROMO WHERE BEG_DT<=CURRENT_DATE AND END_DT>=CURRENT_DATE AND PRN_DIG_TYP='".$_POST['PRN_DIG_TYP']."'";
		//echo $query;
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		if(mysql_num_rows($result)==0){
			$curPromo=0;
		}else{
			$curPromo=$row['PROMO_DISC_AMT'];
		}
		//If promo is larger then don't record
		if($curPromo<$DiscAmt){
		
			//Record the new discount -- still need security logic
			if(($lastAmount!=$DiscAmt)&&(!$broker)){
				if($CoNbr==""){$CoNbr="NULL";}
				if($PrsnNbr==""){$PrsnNbr="NULL";}
				if(!(($CoNbr=="NULL")&&($PrsnNbr=="NULL"))){
					$query="INSERT INTO $CMP.RAT_ENG (DIV_ID,PRN_DIG_TYP,CO_NBR,PRSN_NBR,DISC_AMT,UPD_TS,UPD_NBR,OWN_CO_NBR)
			   				VALUES ('PRN','".$_POST['PRN_DIG_TYP']."',".$CoNbr.",".$PrsnNbr.",".$DiscAmt.",CURRENT_TIMESTAMP,".$_SESSION['personNBR'].",'".$CoNbrDef."')";
					//echo $query;
		   			$result = mysql_query($query,$cloud);
					$query 	= str_replace($CMP,"CMP",$query);
					$result = mysql_query($query,$local);
		   		}
			}
		}

		//Process detail invoice journal
		if (!$addNew) {
			$query="INSERT INTO CMP.JRN_PRN_DIG (ORD_NBR, JRN_TYP, CRT_NBR)
					VALUES (".$OrdNbr.",'DET',".$_SESSION['personNBR'].")";
			//echo $query;
			$resultp=mysql_query($query);
		}
	}
	
	//Process file -- what is the difference between \\ and / for Windows and Unix?
	if(is_uploaded_file($_FILES['FIL_ATT']['tmp_name'])){
		if(file_exists("print-digital\\".$OrdDetNbr)){
			unlink("print-digital\\".$OrdDetNbr);
		}
		$_FILES['FIL_ATT']['tmp_name'];
        move_uploaded_file($_FILES['FIL_ATT']['tmp_name'],"print-digital\\".$OrdDetNbr);
    }
	
	//Take care the where syntax for javascript
	if($where==""){$where="AND CO_NBR=0";}
	$where="' ".$where."'";
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" type="text/css" media="screen" href="css/popup.css" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<link rel="stylesheet" href="framework/combobox/chosen.css">
<link rel="stylesheet" type="text/css" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" media="screen" />

<script type="text/javascript" src="framework/mootools/mootools-latest.min.js"></script>
<script type="text/javascript" src="framework/mootools/mootools-latest-more.js"></script>
<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.min.js"></script>

<script type="text/javascript" src="framework/liveSearch/livepop.js"></script>
<script type="text/javascript" src="framework/functions/default.js"></script>
<script type="text/javascript">
	window.addEvent('domready', function() {
	//Datepicker
	new CalendarEightysix('textbox-id');
	//Calendar
	new CalendarEightysix('block-element-id');
	});
	MooTools.lang.set('id-ID', 'Date', {
		months:    ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
		days:      ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
		dateOrder: ['date', 'month', 'year', '/']
	});
	MooTools.lang.setLanguage('id-ID');
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
		console.log("oke"+ getInt('ORD_Q'));
		//document.getElementById('TOT_SUB').value=(getInt('ORD_Q')-getInt('FAIL_CNT'))*(getInt('PRN_DIG_PRC')+getInt('FEE_MISC')-getInt('DISC_AMT'));
	<?php 
		if($MbrNbr != ''){
			?>
			var totalnota  	= (getInt('ORD_Q')-getInt('FAIL_CNT'))*(getInt('PRN_DIG_PRC')+getInt('FEE_MISC'));
			var discppc 	= '<?php echo $DiscPPC; ?>';
			console.log(totalnota);
			if(totalnota><?php echo $PlafondPPC; ?>){
				console.log('diskon');
				document.getElementById('DISC_PCT').value = discppc;
				document.getElementById('DISC_AMT').value = parseInt(getInt('PRN_DIG_PRC')*getInt('DISC_PCT')/100);
				document.getElementById('TOT_SUB').value=(getInt('ORD_Q')-getInt('FAIL_CNT'))*(getInt('PRN_DIG_PRC')+getInt('FEE_MISC')-getInt('DISC_AMT'));
			} else {
				document.getElementById('DISC_PCT').value = 0;
				document.getElementById('DISC_AMT').value = 0;
				document.getElementById('TOT_SUB').value=(getInt('ORD_Q')-getInt('FAIL_CNT'))*(getInt('PRN_DIG_PRC')+getInt('FEE_MISC'));
			}
			<?php
		} else {
			?>
			document.getElementById('TOT_SUB').value=(getInt('ORD_Q')-getInt('FAIL_CNT'))*(getInt('PRN_DIG_PRC')+getInt('FEE_MISC')-getInt('DISC_AMT'));
			<?php
		}
		?>
	}

	function setPrice(productType){
		switch (productType) {
			<?php
				$query	= "SELECT PROD_NBR,PROD_DESC,PROD_PRC FROM CMP.PROD_LST ORDER BY 1";
				$result	= mysql_query($query);
				while($row=mysql_fetch_array($result)){
					echo " case '".$row['PROD_NBR']."': document.getElementById('PRN_DIG_PRC').value = '".$row['PROD_PRC']."'; break;\n";
				}
			?>
		}
	}
</script>
</head>
<body>
<?php
	if($changed){
		echo "<script>";
		echo "parent.document.getElementById('content').contentDocument.getElementById('rightpane').contentDocument.getElementById('refresh-list').click();";
		echo "parent.document.getElementById('content').contentDocument.getElementById('rightpane').contentDocument.getElementById('refresh-tot').click();";
		echo "</script>";
	}
	if($addNew){$OrdDetNbr=0;}	
?>

<span class='fa fa-times toolbar' style='margin-left:10px' onclick="slideFormOut();"></span>

<?php
	$query="SELECT ORD_DET_NBR,
				ORD_NBR,
				DET.PRN_DIG_TYP,
				ORD_Q,
				DET_TTL,
				FIL_LOC,
				FIL_ATT,
				PRN_LEN,
				PRN_WID,
				FIN_BDR_TYP,
				FIN_BDR_WID,
				FIN_LOP_WID,
				GRM_CNT_TOP,
				GRM_CNT_BTM,
				GRM_CNT_LFT,
				GRM_CNT_RGT,
				PRFO_F,
				BK_TO_BK_F,
				ROLLED_F,
				DET.PRN_DIG_PRC,
				FEE_MISC,
				FAIL_CNT,
				DISC_PCT,
				DISC_AMT,
				PLAN_TYP,
				TOT_SUB,
				HND_OFF_TYP,
				PROD_NBR
			FROM ". $detailtable ." DET INNER JOIN
				CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP
			WHERE ORD_DET_NBR=".$OrdDetNbr;
	// echo $query;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	
	if($OrdDetNbrPar!=""){
		$query="SELECT ORD_DET_NBR,
					ORD_NBR,
					DET.PRN_DIG_TYP,
					ORD_Q,
					DET_TTL,
					FIL_LOC,
					FIL_ATT,
					PRN_LEN,
					PRN_WID,
					FIN_BDR_TYP,
					FIN_BDR_WID,
					FIN_LOP_WID,
					GRM_CNT_TOP,
					GRM_CNT_BTM,
					GRM_CNT_LFT,
					GRM_CNT_RGT,
					PRFO_F,
                    BK_TO_BK_F,
				    ROLLED_F,
					DET.PRN_DIG_PRC,
					FEE_MISC,
					FAIL_CNT,
					DISC_PCT,
					DISC_AMT,
					PLAN_TYP,
					TOT_SUB,
					HND_OFF_TYP,
					PROD_NBR
				FROM ". $detailtable ." DET INNER JOIN
					CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP
				WHERE ORD_DET_NBR=".$OrdDetNbrPar;
		//echo $query;
		$resultp=mysql_query($query);
		$rowp=mysql_fetch_array($resultp);
	}

	if ($OrdDetNbrPar==''){
		$queryDet = "SELECT 
					#SUM ((ORD_Q - FAIL_CNT) * ((PRN_DIG_PRC + FEE_MISC - DISC_AMT) * PRN_LEN * PRN_WID)) AS  TOT_SUB
					SUM(TOT_SUB) AS TOT_SUB
				FROM ".$detailtable." 
				WHERE DEL_NBR=0 AND ORD_DET_NBR_PAR =".$OrdDetNbr;
		$resultDet = mysql_query($queryDet);
		$rows= mysql_fetch_array($resultDet);
		$subTotProd= $rows['TOT_SUB'];
	}
?>

<script>
	parent.parent.document.getElementById('content').contentDocument.getElementById('refresh-list').click();
</script>

<form enctype="multipart/form-data" action="#" method="post" style="width:400px" onSubmit="return checkform();">
	<table>
		<tr>
			<td>Jumlah order</td>
			<td>
				<input name="ORD_DET_NBR" id="ORD_DET_NBR" value="<?php echo $row['ORD_DET_NBR'];if($row['ORD_DET_NBR']==""){echo "-1";$addNew=true;} ?>" type="hidden" />
				<input id="ORD_Q" name="ORD_Q" onkeyup="calcPay();" onchange="calcPay();" value="<?php echo $row['ORD_Q']; ?>" type="text" style="width:100px" />
				<?php if($row['ORD_Q']==0){$prevQuantity=0;}else{$prevQuantity=$row['ORD_Q'];} ?>
				<?php 
					if($OrdDetNbrPar!=""){
						echo "<div class='listable-btn'><span class='fa fa-paperclip listable-btn' style='cursor:pointer' ";
						echo "onclick=".chr(34)."document.getElementById('ORD_Q').value='".$rowp['ORD_Q']."';";
						echo "document.getElementById('PRN_LEN').value='".$rowp['PRN_LEN']."';";
						echo "document.getElementById('PRN_WID').value='".$rowp['PRN_WID']."';".chr(34)."></span></div>";
					}
				?>
			</td>
		</tr>
		</tr>
			<td>Tanggal</td>
			<td>
			<input name="ORD_DTE" id="ORD_DTE" value="<?php echo $OrdDte; ?>" type="text" style="width:110px;" />
			<script>
				new CalendarEightysix('ORD_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });			
			</script>
			</td>
		</tr>
		</tr>
			<td>Keterangan</td>
			<td><input id="DET_TTL" name="DET_TTL" value="<?php echo $row['DET_TTL']; ?>" type="text" style="width:280px;" /></td>
		</tr>
		<tr>
			<td>Jenis Produk</td>
			<td>
				<select style="margin:0px;padding:0px;width:290px;" id="PROD_NBR" name="PRN_DIG_TYP" onchange="setPrice(this.value);calcPay();" class="chosen-select">
				<?php
					$query="SELECT PROD_NBR,PROD_DESC FROM CMP.PROD_LST TYP WHERE DEL_NBR = 0 ORDER BY 1";
					genCombo($query,"PROD_NBR","PROD_DESC",$row['PROD_NBR'],"Pilih Produk");
				?>
				</select>
			</td>
		</tr>
		<tr style="<?php if($UpperSec > 7){echo 'display:none';}?>">
			<td>Harga</td>
			<td>
				<input id="PRN_DIG_PRC" name="PRN_DIG_PRC" value="<?php echo $row['PRN_DIG_PRC']; ?>" type="text" style="width:100px;" readonly />
			</td>
		</tr>
		<tr>
			<td>Lokasi file</div></td>
			<td><input id="FIL_LOC" name="FIL_LOC" value="<?php echo $row['FIL_LOC']; ?>" type="text" style="width:280px;"/></td>
		</tr>
		<tr>
			<td>Lampiran</div></td>
			<td><div class='browse' onclick="document.getElementById('FIL_ATT').click();">Browse ...<input class="browse" id="FIL_ATT" name="FIL_ATT" type="file" style="border:0px;" tabindex=-1 /></div></td>
		</tr>
		<tr>
			<td>Serah Terima</td>
			<?php if($row['HND_OFF_TYP']==""){$hndOffTyp="PU";}else{$hndOffTyp=$row['HND_OFF_TYP'];} ?>
			<td><select style="margin:0px;padding:0px;width:150px;" name="HND_OFF_TYP" class="chosen-select">
				<?php
					$query="SELECT HND_OFF_TYP,HND_OFF_DESC
							FROM CMP.HND_OFF_TYP";
					genCombo($query,"HND_OFF_TYP","HND_OFF_DESC",$hndOffTyp,"");
				?>
				</select>
			</td>
		</tr>
		<tr>
			<td>Rusak</td>
			<td>
				<input id="FAIL_CNT" name="FAIL_CNT" onkeyup="calcPay();" onchange="calcPay();" value="<?php echo $row['FAIL_CNT']; ?>" type="text" style="width:100px;" />
			</td>
		</tr>
		<tr style="<?php if($UpperSec > 7){echo 'display:none';}?>">
			<td>Discount</td>
			<td>
				<input id="DISC_PCT" name="DISC_PCT" onkeyup="document.getElementById('DISC_AMT').value=parseInt(getInt('PRN_DIG_PRC')*getInt('DISC_PCT')/100);calcPay();" onchange="document.getElementById('DISC_AMT').value=parseInt(getInt('PRN_DIG_PRC')*getInt('DISC_PCT')/100);calcPay();" value="<?php echo $row['DISC_PCT']; ?>" type="text" style="width:50px;" <?php if($MbrNbr!=''){ echo 'readonly';} ?> /> % atau
				<input id="DISC_AMT" name="DISC_AMT" onkeyup="document.getElementById('DISC_PCT').value=parseInt(getInt('DISC_AMT')*100/getInt('PRN_DIG_PRC'));calcPay();" onchange="document.getElementById('DISC_PCT').value=parseInt(getInt('DISC_AMT')*100/getInt('PRN_DIG_PRC'));calcPay();" value="<?php echo $row['DISC_AMT']; ?>" type="text" style="width:100px;" <?php if($MbrNbr!=''){ echo 'readonly';} ?> />
			</td>
		</tr>
		<tr id="discount-message" style="display:none;">
			<td></td>
			<td>
				<b class="message"></b>
			</td>
		</tr>
		<tr style="<?php if($UpperSec > 7){echo 'display:none';}?>">
			<td style='white-space:nowrap;'>Spot</td>
			<td><input id="FEE_MISC" name="FEE_MISC" onkeyup="calcPay();" onchange="calcPay();" value="<?php echo $row['FEE_MISC']; ?>" type="text" style="width:100px;" /></td>
		</tr>
		<tr style="<?php if($UpperSec > 7){echo 'display:none';}?>">
			<td>Sub total</td>
			<?php if ($row['PROD_NBR']!=''){$subTotal= $subTotProd;}else{$subTotal = $row['TOT_SUB'];} ?>
			<td><input id="TOT_SUB" name="TOT_SUB" value="<?php echo $subTotal; ?>" type="text" style="width:100px" readonly /></td>
		</tr>
		
	</table>
	<?php 
		
			if(@$_GET['readonly']!=1){
	?>
	<input class="process" type="submit" value="<?php if($addNew){echo 'Tambah';}else{echo 'Simpan';} ?>"/>
	<?php }?>
	<script>
		setPrice(document.getElementById('PROD_NBR').value);
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



<?php
	@header("Connection: close\r\n"); 
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/functions/print-digital.php";
	include "framework/security/default.php";

	$UpperSec 		= getSecurity($_SESSION['userID'],"Accounting");
	$OrdNbr			= $_GET['ORD_NBR'];
	$OrdDetNbr		= $_GET['ORD_DET_NBR'];
	$OrdDetNbrPar	=$_GET['ORD_DET_NBR_PAR'];
	$type			= $_GET['TYP'];
	$origin			= $_GET['ORGN'];
	$changed		= false;
	$addNew			= false;
	
	if($type == "EST"){
		$headtable 	= "CMP.PRN_PPR_ORD_HEAD_EST";
		$detailtable= "CMP.PRN_PPR_ORD_DET_EST";
	}else{
		$headtable 	= "CMP.PRN_PPR_ORD_HEAD";
		$detailtable= "CMP.PRN_PPR_ORD_DET";
	}
	
	//[JOURNAL] get information schema for journal
	$query_info	= "SELECT TABLE_NAME, COLUMN_NAME, COLUMN_KEY FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'CMP' AND TABLE_NAME ='PRN_PPR_ORD_DET'";
	$result_info= mysql_query($query_info);
	$array_info	= array();
	while ($row_info = mysql_fetch_array($result_info)){
		if ($row_info['COLUMN_KEY']=="PRI") { $PK = $row_info['COLUMN_NAME']; }
		array_push($array_info,$row_info['COLUMN_NAME']);
	}
	
	//[JOURNAL] get data before changes
	$query_awal	= "SELECT * FROM CMP.PRN_PPR_ORD_DET WHERE ORD_DET_NBR='$OrdDetNbr'";
	$result_awal= mysql_query($query_awal);
	$row_awal	= mysql_fetch_assoc($result_awal);
	
	//Get order head information
	$query="SELECT 
		BUY_PRSN_NBR,
		BUY_CO_NBR,
		BRKR_PLAN_TYP
	FROM ". $headtable ." HED 
	LEFT OUTER JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR=PPL.PRSN_NBR
	WHERE ORD_NBR=".$_GET['ORD_NBR'];
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	if($row['BUY_CO_NBR']!=""){
		$where	= "AND CO_NBR=".$row['BUY_CO_NBR'];
		$CoNbr	= $row['BUY_CO_NBR'];
	}elseif($row['BUY_PRSN_NBR']!=""){
		$where	= "AND PRSN_NBR=".$row['BUY_PRSN_NBR'];
		$PrsnNbr= $row['BUY_CO_NBR'];
	}
	//echo $where;
	
	//Check to see if brokerage
	if($row['BRKR_PLAN_TYP']!=""){
		$broker=true;
	}

	//Process changes here
	if($_POST['ORD_DET_NBR']!=""){
		$OrdDetNbr=$_POST['ORD_DET_NBR'];
		
		//Take care of nulls
		if($_POST['ORD_Q']==""){$OrdQ="NULL";}else{$OrdQ=$_POST['ORD_Q'];}
		if($_POST['PRN_LEN']==""){$PrnLen="NULL";}else{$PrnLen=$_POST['PRN_LEN'];}
		if($_POST['PRN_WID']==""){$PrnWid="NULL";}else{$PrnWid=$_POST['PRN_WID'];}
		if($_POST['FEE_MISC']==""){$FeeMisc="NULL";}else{$FeeMisc=$_POST['FEE_MISC'];}
		if($_POST['ORD_DET_NBR_REF']==""){$Pid="NULL";}else{$Pid=$_POST['ORD_DET_NBR_REF'];}
		if($_POST['FAIL_CNT']==""){$FailCnt="NULL";}else{$FailCnt=$_POST['FAIL_CNT'];}
		if(($_POST['DISC_PCT']=="")||($_POST['DISC_PCT']=="NaN")){$DiscPct="NULL";}else{$DiscPct=$_POST['DISC_PCT'];}
		if($_POST['DISC_AMT']==""){$DiscAmt="NULL";}else{$DiscAmt=$_POST['DISC_AMT'];}
		if($_POST['TOT_SUB']==""){$TotSub="NULL";}else{$TotSub=$_POST['TOT_SUB'];}
		if($_POST['LOW_PRC_F']=="on"){$lowPrice=1;}else{$lowPrice=0;}
		if($_POST['BK_TO_BK_F']=="on"){$backtoback=1;}else{$backtoback=0;}
		
		//Process add new
		if($OrdDetNbr==-1){
			$addNew=true;
			$query="SELECT COALESCE(MAX(ORD_DET_NBR),0)+1 AS NEW_NBR FROM ". $detailtable ."";
			$result=mysql_query($query);
			$row=mysql_fetch_array($result);
			$OrdDetNbr=$row['NEW_NBR'];
			$query="INSERT INTO ". $detailtable ." (ORD_DET_NBR) VALUES (".$OrdDetNbr.")";
			$result=mysql_query($query);
			$create="CRT_TS=CURRENT_TIMESTAMP,CRT_NBR=".$_SESSION['personNBR'].",";
		}
		
		//Process job length
		$JobLen=jobLength($_POST['PRN_PPR_TYP'],$OrdQ,$PrnLen,$PrnWid);
		
		//Process child row
		if($OrdDetNbrPar!=""){$childRow="ORD_DET_NBR_PAR=".$OrdDetNbrPar.",";}
		
		$query="UPDATE ". $detailtable ." SET 
			ORD_NBR=".$OrdNbr.",
			PRN_PPR_TYP='".$_POST['PRN_PPR_TYP']."',
			ORD_Q=".$OrdQ.",
			DET_TTL='".mysql_real_escape_string($_POST['DET_TTL'])."',
			FIL_LOC='".mysql_real_escape_string($_POST['FIL_LOC'])."',
			FIL_ATT='".$_FILES['FIL_ATT']['name']."',
			N_UP='".$_POST['N_UP']."',
			PRN_LEN=".$PrnLen.",
			PRN_WID=".$PrnWid.",
			PRN_PPR_PRC=".$_POST['PRN_PPR_PRC'].",
			PRC_UP=".$_POST['PRC_UP'].",
			LOW_PRC_F=".$lowPrice.",
			BK_TO_BK_F=".$backtoback.",
			PRN_PPR_EQP_TYP='".$_POST['PRN_PPR_EQP_TYP']."',
			PRN_PPR_EQP_PRC=".$_POST['PRN_PPR_EQP_PRC'].",
			FEE_MISC=".$FeeMisc.",
			ORD_DET_NBR_REF='".$Pid."',
			FAIL_CNT=".$FailCnt.",
			DISC_PCT=".$DiscPct.",
			DISC_AMT=".$DiscAmt.",
			TOT_SUB=".$TotSub.",
			HND_OFF_TYP='".$_POST['HND_OFF_TYP']."',".$create."
			JOB_LEN=".$JobLen.",".$childRow."
			UPD_TS=CURRENT_TIMESTAMP,
			UPD_NBR=".$_SESSION['personNBR']."
		WHERE ORD_DET_NBR=".$OrdDetNbr;
		//echo $query;
	   	$result=mysql_query($query);
	   	$changed=true;
	   	
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
				 WHERE PRN_PPR_TYP='".$_POST['PRN_PPR_TYP']."' $where AND DIV_ID='PRN'
				   AND UPD_TS=(SELECT MAX(UPD_TS) FROM CMP.RAT_ENG WHERE PRN_PPR_TYP='".$_POST['PRN_PPR_TYP']."' $where AND DIV_ID='PRN')"; 
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		$lastAmount=$row['DISC_AMT'];
		
		//Check to see if the discount is larger than the currently running promotion
		//Get current promo
		$query="SELECT PROMO_DISC_AMT FROM CMP.PRN_PPR_PROMO WHERE BEG_DT<=CURRENT_DATE AND END_DT>=CURRENT_DATE AND PRN_PPR_TYP='".$_POST['PRN_PPR_TYP']."'";
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
					$query="INSERT INTO CMP.RAT_ENG (DIV_ID,PRN_PPR_TYP,CO_NBR,PRSN_NBR,DISC_AMT,UPD_TS,UPD_NBR)
			   				VALUES ('PRN','".$_POST['PRN_PPR_TYP']."',".$CoNbr.",".$PrsnNbr.",".$DiscAmt.",CURRENT_TIMESTAMP,".$_SESSION['personNBR'].")";
					//echo $query;
		   			$result=mysql_query($query);
		   		}
			}
		}

		//Process detail invoice journal
		if (!$addNew) {
			$query="INSERT INTO CMP.JRN_PRN_PPR (ORD_NBR, JRN_TYP, CRT_NBR)
					VALUES (".$OrdNbr.",'DET',".$_SESSION['personNBR'].")";
			//echo $query;
			$resultp=mysql_query($query);
		}
		
		//[JOURNAL] get last change
		$query_akhir	= "SELECT * FROM CMP.PRN_PPR_ORD_DET WHERE ORD_DET_NBR='$OrdDetNbr'";
		$result_akhir	= mysql_query($query_akhir);
		$row_akhir		= mysql_fetch_assoc($result_akhir);
		
		//[JOURNAL] Process to journal
		for ($i=0;$i<count($array_info);$i++){
			if ($row_awal[$array_info[$i]]!=$row_akhir[$array_info[$i]]) {
				$query_jrn	= "INSERT INTO CMP.JRN_LIST (JRN_LIST_NBR, DB_NM, TBL_NM, COL_NM, PK, PK_DTA, REC_BEG, REC_END, CRT_TS, CRT_NBR) VALUES 
								('','CMP','PRN_PPR_ORD_DET','".$array_info[$i]."','$PK','$OrdDetNbr','".$row_awal[$array_info[$i]]."','".$row_akhir[$array_info[$i]]."','".date('Y-m-d H:i:s')."','".$_SESSION['personNBR']."')";
				mysql_query($query_jrn);
			}
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
	<link rel="stylesheet" href="framework/combobox/chosen.css">
	<script type="text/javascript" src="framework/liveSearch/livepop.js"></script>
	<script type="text/javascript" src="framework/functions/default.js"></script>
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<script type="text/javascript">
	function getInt(objectID){
		if(document.getElementById(objectID).value==""){
			return 0;
		}else{
			return parseInt(document.getElementById(objectID).value);
		}
	}
	function getFloat(objectID){
		if(document.getElementById(objectID).value==""){
			return 0;
		}else{
			return parseFloat(document.getElementById(objectID).value);
		}
	}
	function roundTo(number,to){
		return Math.round(number/to)*to;
	}
	
	function calcPay(){
		var checkBox	= document.getElementById("LOW_PRC_F");
		var backtoback	= document.getElementById("BK_TO_BK_F");
		var coNbr		= "<?php echo $CoNbr; ?>";
		var cutFolio	= 5;
		var minimunQty	= 1000;
		
		document.getElementById("PRN_PPR_PRC").value=window.printDigitalPrice-setVolDisc(getInt('ORD_Q'));
		document.getElementById("PRC_PPR").value=roundTo((
			((window.printDigitalPrice/cutFolio)/getInt('N_UP')) + parseInt(((window.printDigitalPrice/cutFolio)/getInt('N_UP')) * getInt('PRC_UP')/100)
		),5);
		
		//Get paper Price
		if(backtoback.checked === true){
			document.getElementById('QTY_PPR').value=roundTo(parseInt((getInt('ORD_Q')/getInt('N_UP')) * 2),50);
		}else{
			document.getElementById('QTY_PPR').value=roundTo(parseInt((getInt('ORD_Q')/getInt('N_UP')) * 1),50);
		}
		
		//Get print Price
		document.getElementById("PRN_PPR_EQP_PRC").value=window.printEquipmentPrice;
		
		if(getInt('QTY_PPR') > 1000){
			document.getElementById("PRN_PPR_EQP_PRC_TTL").value=parseInt((getInt('PRN_PPR_EQP_PRC') + ((getInt('QTY_PPR') - minimunQty) * window.printEquipmentPriceOver)) / getInt('ORD_Q'));
		}
		
		document.getElementById('TOT_SUB').value=(getInt('ORD_Q')-getInt('FAIL_CNT'))*((getInt('PRN_PPR_PRC')+getInt('FEE_MISC')-getInt('DISC_AMT')));
		
		
	}
	
	//Part of livePop script
	function postLive(){
        if(getInt('PRN_PPR_PRC')>0){
            document.getElementById('DISC_PCT').value=parseInt(getInt('DISC_AMT')*100/getInt('PRN_PPR_PRC'));
        }else{
             document.getElementById('DISC_PCT').value=0;
        }
        calcPay();
	}

	//Handle volume discount schedule plan type
	var printDigitalPrice	= 0;
	var planTyp				= '';
</script>

<script>	
	//Assign price to combo box changes
	function setPrice(printDigitalType){
		var coNbr    = "<?php echo $CoNbr; ?>";
		var checkBox = document.getElementById("LOW_PRC_F");
		switch (printDigitalType) {
			<?php
			$query="SELECT 
				PRN_PPR_TYP,PRN_PPR_DESC,PRN_PPR_PRC
			FROM CMP.PRN_PPR_TYP TYP 
			WHERE DEL_NBR = 0 AND ACT_F = 1
			ORDER BY 2";
			$result=mysql_query($query);
			$defaultPrice=0;
			while($row=mysql_fetch_array($result)){
				if($defaultPrice==0){$defaultPrice=$row['PRN_PPR_PRC'];}
				echo "case '".$row['PRN_PPR_TYP']."': window.printDigitalPrice = '".$row['PRN_PPR_PRC']."' ;break; \n";
			}
			?>
		}
		document.getElementById("PRN_PPR_PRC").value=window.printDigitalPrice-setVolDisc(getInt('ORD_Q'));
	}
	
	function setPriceEqp(printPaperType){
		switch (printPaperType) {
			<?php
			$query="SELECT PRN_PPR_EQP, PRN_PPR_EQP_DESC, PRN_PPR_EQP_PRC, PRN_PPR_EQP_OVER FROM CMP.PRN_PPR_EQP WHERE DEL_NBR = 0 ORDER BY 2";
			$result=mysql_query($query);
			$defaultPrice	= 0;
			while($row=mysql_fetch_array($result)){
				if($defaultPrice==0){$defaultPrice=$row['PRN_PPR_EQP_PRC'];}
				echo "case '".$row['PRN_PPR_EQP']."': window.printEquipmentPrice = '".$row['PRN_PPR_EQP_PRC']."' ;window.printEquipmentPriceOver = '".$row['PRN_PPR_EQP_OVER']."' ;break; \n";
			}
			?>
		}
		document.getElementById("PRN_PPR_EQP_PRC").value=window.printEquipmentPrice;
		document.getElementById("PRN_PPR_EQP_OVER").value=window.printEquipmentPriceOver;
	}
	
	function getLastPrice(){
		var checkBox = document.getElementById("LOW_PRC_F");
		if(checkBox.checked == true){
			document.getElementById("PRN_PPR_PRC").value=window.printDigitalPrice - window.maxprintDigitalPrice;
		}
	}
	
	//Assign price according to the volume discount schedule
	function setVolDisc(vol){
		var discAmt=0;
		<?php
			$query="SELECT PLAN_TYP,MIN_Q,MAX_Q,DISC_AMT FROM CMP.PRN_PPR_VOL_SCHED ORDER BY 1,2";
			$result=mysql_query($query);
			while($row=mysql_fetch_array($result)){
				echo "discAmt = (window.planTyp=='".$row['PLAN_TYP']."' && vol>=".$row['MIN_Q']." && vol<=".$row['MAX_Q'].") ? ".$row['DISC_AMT']." : discAmt; \n";
			}
		?>
		return discAmt;
	}
	
	function getLastDisc(){
		<?php
			$query="SELECT 
				DET.DISC_PCT,
				DET.DISC_AMT 
			FROM ". $detailtable ." DET 
			LEFT OUTER JOIN ". $headtable ." HED ON DET.ORD_NBR=HED.ORD_NBR 
			WHERE HED.BUY_CO_NBR='".$CoNbr."' 
			ORDER BY DET.CRT_TS DESC LIMIT 1";
			$result=mysql_query($query);
			$row=mysql_fetch_array($result);
			echo "document.getElementById('DISC_PCT').value = '".$row['DISC_PCT']."'; \n";
			echo "document.getElementById('DISC_AMT').value = '".$row['DISC_AMT']."'; \n";
			
			if($row['DISC_PCT']!='0' || $row['DISC_PCT']!=''){
				echo "document.getElementById('DISC_AMT').value=parseInt(getInt('PRN_PPR_PRC')*getInt('DISC_PCT')/100);";
			}
		?>
	}
	getLastDisc();
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
	$query="SELECT 
		ORD_DET_NBR,
		ORD_NBR,
		DET.PRN_PPR_TYP,
		ORD_Q,
		DET_TTL,
		N_UP,
		FIL_LOC,
		FIL_ATT,
		PRN_LEN,
		PRN_WID,
		DET.PRN_PPR_PRC,
		LOW_PRC_F,
		BK_TO_BK_F,
		PRN_PPR_EQP_TYP,
		PRN_PPR_EQP_PRC,
		FEE_MISC,
		FAIL_CNT,
		DISC_PCT,
		DISC_AMT,
		PLAN_TYP,
		TOT_SUB,
		ORD_DET_NBR_REF,
		HND_OFF_TYP
	FROM ". $detailtable ." DET INNER JOIN
		CMP.PRN_PPR_TYP TYP ON DET.PRN_PPR_TYP=TYP.PRN_PPR_TYP
	WHERE ORD_DET_NBR=".$OrdDetNbr;
	//echo $query;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	
	if($OrdDetNbrPar!=""){
		$query="SELECT ORD_DET_NBR,
			ORD_NBR,
			DET.PRN_PPR_TYP,
			ORD_Q,
			DET_TTL,
			N_UP,
			FIL_LOC,
			FIL_ATT,
			PRN_LEN,
			PRN_WID,
			DET.PRN_PPR_PRC,
			LOW_PRC_F,
			BK_TO_BK_F,
			PRN_PPR_EQP_TYP,
			PRN_PPR_EQP_PRC,
			FEE_MISC,
			FAIL_CNT,
			DISC_PCT,
			DISC_AMT,
			PLAN_TYP,
			TOT_SUB,
			ORD_DET_NBR_REF,
			HND_OFF_TYP
		FROM ". $detailtable ." DET INNER JOIN
			CMP.PRN_PPR_TYP TYP ON DET.PRN_PPR_TYP=TYP.PRN_PPR_TYP
		WHERE ORD_DET_NBR=".$OrdDetNbrPar;
		//echo $query;
		$resultp=mysql_query($query);
		$rowp=mysql_fetch_array($resultp);
	}
?>

<script>
	parent.parent.document.getElementById('content').contentDocument.getElementById('refresh-list').click();
</script>

<form enctype="multipart/form-data" action="#" method="post" style="width:400px" onSubmit="return checkform();">
	<table>
		<tr>
			<td>Jenis Cetakan</td>
			<td><select style="margin:0px;padding:0px;width:290px;" id="PRN_TYP" name="PRN_TYP" class="chosen-select">
				<?php
					$query="SELECT PRN_TYP,PRN_DESC
						FROM CMP.PRN_TYP
						ORDER BY 2";
					genCombo($query,"PRN_TYP","PRN_DESC",$row['PRN_TYP'],"");
				?>
				</select>
			</td>
		</tr>
		<tr>
			<td>Jumlah order</td>
			<td>
				<input name="ORD_DET_NBR" id="ORD_DET_NBR" value="<?php echo $row['ORD_DET_NBR'];if($row['ORD_DET_NBR']==""){echo "-1";$addNew=true;} ?>" type="hidden" />
				
				<?php if($row['ORD_Q']==0){$Quantity=1;}else{$Quantity=$row['ORD_Q'];} ?>
				<input id="ORD_Q" name="ORD_Q" onkeyup="calcPay();" onchange="calcPay();" value="<?php echo $Quantity; ?>" type="text" style="width:100px" />
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
			<td>Keterangan</td>
			<td><input id="DET_TTL" name="DET_TTL" value="<?php echo $row['DET_TTL']; ?>" type="text" style="width:280px;" /></td>
		</tr>
		</tr>
			<td>Ukuran (m)</td>
			<td>
				Panjang <input id="PRN_LEN" name="PRN_LEN" onkeyup="calcPay();" onchange="calcPay();" value="<?php echo $row['PRN_LEN']; ?>" type="text" style="width:60px;" />
			    x Lebar <input id="PRN_WID" name="PRN_WID" onkeyup="calcPay();" onchange="calcPay();" value="<?php echo $row['PRN_WID']; ?>" type="text" style="width:60px;" /></td>
			</td>
		</tr>
		<tr>
			<td>Jenis Kertas</td>
			<td><select style="margin:0px;padding:0px;width:290px;" id="PRN_PPR_TYP" name="PRN_PPR_TYP" onchange="setPrice(this.value);if(window.planTyp==''){livePop('PRN_PPR_TYP='+String.fromCharCode(39)+this.value+String.fromCharCode(39)+<?php echo $where; ?>+'@'+document.getElementById('ORD_Q').value+'@<?php echo $prevQuantity; ?>','print-digital-edit-list-detail-lp.php','DISC_AMT')};calcPay();" class="chosen-select">
				<?php
					$query="SELECT PRN_PPR_TYP,PRN_PPR_DESC,PRN_PPR_PRC
						FROM CMP.PRN_PPR_TYP
						WHERE DEL_NBR = 0 AND ACT_F = 1
						ORDER BY 2";
					genCombo($query,"PRN_PPR_TYP","PRN_PPR_DESC",$row['PRN_PPR_TYP'],"");
				?>
				</select>

			</td>
		</tr>
		<tr style="<?php if($UpperSec > 7){echo 'display:none';}?>">
			<td>Harga</td>
			<?php if($row['PRN_PPR_PRC']==""){$prnDigPrc=$defaultPrice;}else{$prnDigPrc=$row['PRN_PPR_PRC'];} ?>
			<td>
				<input id="PRN_PPR_PRC" name="PRN_PPR_PRC" value="<?php echo $prnDigPrc; ?>" type="text" style="width:100px;" readonly />&nbsp;&nbsp;
				<input name='LOW_PRC_F' id='LOW_PRC_F' type='checkbox' class='regular-checkbox' <?php if($row['LOW_PRC_F']=="1"){echo "checked";} ?> onclick="getLastPrice();calcPay();"/>&nbsp;<label for="LOW_PRC_F" style="top: 6px"></label><label class='checkbox' for="LOW_PRC_F" style='cursor:pointer;position: relative;top: 6px;'>Harga Terendah</label></span>&nbsp;&nbsp;
			</td>
		</tr>
		<tr>
			<td>N-Up</td>
			<td>
				<?php if($row['N_UP']==""){$nUP=1;} else{$nUP=$row['N_UP'];} ?>
				<input id="N_UP" name="N_UP" value="<?php echo $nUP; ?>" onkeyup="calcPay();" onchange="calcPay();" type="text" style="width:100px;" />&nbsp;&nbsp;
				<input name='BK_TO_BK_F' id='BK_TO_BK_F' type='checkbox' class='regular-checkbox' <?php if($row['BK_TO_BK_F']=="1"){echo "checked";} ?> onclick="calcPay();"/>&nbsp;<label for="BK_TO_BK_F" style="top: 6px"></label><label class='checkbox' for="BK_TO_BK_F" style='cursor:pointer;position: relative;top: 6px;'>Bolak Balik</label></span>&nbsp;&nbsp;
			</td>
		</tr>
		<tr>
			<td>Insite</td>
			<td>
				<?php if($row['PRC_UP']==""){$priceUP=10;} else{$priceUP=$row['PRC_UP'];} ?>
				<input id="PRC_UP" name="PRC_UP" value="<?php echo $priceUP; ?>" onkeyup="calcPay();" onchange="calcPay();" type="text" style="width:100px;" />
			</td>
		</tr>
		<tr>
			<td>Lembar</td>
			<td>
				<input id="QTY_PPR" name="QTY_PPR" value="" type="text" style="width:100px;"/>
				Harga Kertas<input id="PRC_PPR" name="PRC_PPR" value="" type="text" style="width:100px;"/>
			</td>
		</tr>
		<tr>
			<td>Jenis Mesin</td>
			<td><select style="margin:0px;padding:0px;width:290px;" id="PRN_PPR_EQP_TYP" name="PRN_PPR_EQP_TYP" onchange="setPriceEqp(this.value);calcPay();" class="chosen-select">
				<?php
					$query="SELECT PRN_PPR_EQP,PRN_PPR_EQP_DESC,PRN_PPR_EQP_PRC
						FROM CMP.PRN_PPR_EQP
						WHERE DEL_NBR = 0
						ORDER BY 2";
					genCombo($query,"PRN_PPR_EQP","PRN_PPR_EQP_DESC",$row['PRN_PPR_EQP_TYP'],"");
				?>
				</select>
			</td>
		</tr>
		<tr style="<?php if($UpperSec > 7){echo 'display:none';}?>">
			<td>Harga</td>
			<?php if($row['PRN_PPR_EQP_PRC']==""){$prnDigPrc=$defaultPrice;}else{$prnDigPrc=$row['PRN_PPR_EQP_PRC'];} ?>
			<td>
				<input id="PRN_PPR_EQP_PRC" name="PRN_PPR_EQP_PRC" value="<?php echo $prnDigPrc; ?>" type="text" style="width:100px;" readonly />/1000&nbsp;&nbsp;
				<input id="PRN_PPR_EQP_OVER" name="PRN_PPR_EQP_OVER" value="" type="text" style="width:70px;" readonly />&nbsp;&nbsp;
				<input id="PRN_PPR_EQP_PRC_TTL" name="PRN_PPR_EQP_PRC_TTL" value="" type="text" style="width:70px;" readonly />
			</td>
		</tr>
		<!--
		<tr>
			<td>Finishing</td>
			<?php if($row['FIN_BDR_TYP']==""){$finBdrTyp="Simpres";}else{$finBdrTyp=$row['FIN_BDR_TYP'];} ?>
			<td><select style="margin:0px;padding:0px;width:150px;" name="FIN_BDR_TYP" class="chosen-select">
				<?php
					$query="SELECT FIN_BDR_TYP,FIN_BDR_DESC,SORT
							FROM CMP.PRN_DIG_FIN_BDR_TYP ORDER BY 3";
					genCombo($query,"FIN_BDR_TYP","FIN_BDR_DESC",$finBdrTyp,"");
				?>
				</select>
				<?php if($row['FIN_BDR_WID']==""){$finBdrWid=0;}else{$finBdrWid=$row['FIN_BDR_WID'];} ?>&nbsp;
				P <input id="FIN_BDR_WID" name="FIN_BDR_WID" value="<?php echo $finBdrWid; ?>" type="text" style="width:30px;" />
				<?php if($row['FIN_LOP_WID']==""){$finLopWid=0;}else{$finLopWid=$row['FIN_LOP_WID'];} ?>
				K <input id="FIN_LOP_WID" name="FIN_LOP_WID" value="<?php echo $finLopWid; ?>" type="text" style="width:30px;" />&nbsp;cm
			</td>
		</tr>
		<tr>
			<td>Keling</td>
			<td>
				<div style='float:left;margin-right:5px'>
					A  <input id="GRM_CNT_TOP" name="GRM_CNT_TOP" value="<?php echo $row['GRM_CNT_TOP']; ?>" type="text" style="width:25px;" />
			    	B  <input id="GRM_CNT_BTM" name="GRM_CNT_BTM" value="<?php echo $row['GRM_CNT_BTM']; ?>" type="text" style="width:25px;" />
			    	KA <input id="GRM_CNT_LFT" name="GRM_CNT_LFT" value="<?php echo $row['GRM_CNT_LFT']; ?>" type="text" style="width:25px;" />
			    	KI <input id="GRM_CNT_RGT" name="GRM_CNT_RGT" value="<?php echo $row['GRM_CNT_RGT']; ?>" type="text" style="width:25px;" />
			    </div>
			</td>
		</tr>
		<tr>
			<td>Options</div></td>
			<td>
                <input name='PRFO_F' id='PRFO_F' type='checkbox' class='regular-checkbox' <?php if($row['PRFO_F']=="1"){echo "checked";} ?>/>&nbsp;<label for="PRFO_F"></label><label class='checkbox' for="PRFO_F" style='cursor:pointer'>Perforasi</label></span>&nbsp;&nbsp;
                <input name='BK_TO_BK_F' id='BK_TO_BK_F' type='checkbox' class='regular-checkbox' <?php if($row['BK_TO_BK_F']=="1"){echo "checked";} ?>/>&nbsp;<label for="BK_TO_BK_F"></label><label class='checkbox' for="BK_TO_BK_F" style='cursor:pointer'>Back to back</label></span>&nbsp;&nbsp;
                <input name='ROLLED_F' id='ROLLED_F' type='checkbox' class='regular-checkbox' <?php if($row['ROLLED_F']=="1"){echo "checked";} ?>/>&nbsp;<label for="ROLLED_F"></label><label class='checkbox' for="ROLLED_F" style='cursor:pointer'>Digulung</label></span>&nbsp;&nbsp;
            </td>
		</tr>
		-->
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
		<!--
		<tr>
			<td>PID</td>
			<td>
				<input id="ORD_DET_NBR_REF" name="ORD_DET_NBR_REF" onkeyup="calcPay();" onchange="calcPay();" value="<?php echo $row['ORD_DET_NBR_REF']; ?>" type="text" style="width:100px;" />
			</td>
		</tr>
		-->
		<tr>
			<td>Rusak</td>
			<td>
				<input id="FAIL_CNT" name="FAIL_CNT" onkeyup="calcPay();" onchange="calcPay();" value="<?php echo $row['FAIL_CNT']; ?>" type="text" style="width:100px;" />
			</td>
		</tr>
		<tr style="<?php if($UpperSec > 7){echo 'display:none';}?>">
			<td>Discount</td>
			<td>
				<input id="DISC_PCT" name="DISC_PCT" onkeyup="document.getElementById('DISC_AMT').value=parseInt(getInt('PRN_PPR_PRC')*getInt('DISC_PCT')/100);calcPay();" onchange="document.getElementById('DISC_AMT').value=parseInt(getInt('PRN_PPR_PRC')*getInt('DISC_PCT')/100);calcPay();" value="<?php echo $row['DISC_PCT']; ?>" type="text" style="width:50px;"  /> % atau
				<input id="DISC_AMT" name="DISC_AMT" onkeyup="document.getElementById('DISC_PCT').value=parseInt(getInt('DISC_AMT')*100/getInt('PRN_PPR_PRC'));calcPay();" onchange="document.getElementById('DISC_PCT').value=parseInt(getInt('DISC_AMT')*100/getInt('PRN_PPR_PRC'));calcPay();" value="<?php echo $row['DISC_AMT']; ?>" type="text" style="width:100px;" />
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
			<td><input id="TOT_SUB" name="TOT_SUB" value="<?php echo $row['TOT_SUB']; ?>" type="text" style="width:100px" readonly /></td>
		</tr>
		
	</table>
	<?php 
		
			if(@$_GET['readonly']!=1){
	?>
	<input class="process" type="submit" value="<?php if($addNew){echo 'Tambah';}else{echo 'Simpan';} ?>"/>
	<?php }?>
	<script>
		setPrice(document.getElementById('PRN_PPR_TYP').value);
		setPriceEqp(document.getElementById('PRN_PPR_EQP_TYP').value);
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



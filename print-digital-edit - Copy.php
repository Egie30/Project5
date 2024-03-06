<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/alert/alert.php";
	include "framework/security/default.php";
	include "framework/functions/dotmatrix.php";
	include "framework/slack/slack.php";
	date_default_timezone_set("Asia/Jakarta");
	
	$OrdNbr	= $_GET['ORD_NBR'];
	$type	= $_GET['TYP'];
	$OrdDetNbrStr	= $_GET['ORD_DET_NBR'];
	
	if ($_GET['CONV'] != "") {
		if ($_GET['CONV'] == "NEW") {
			$query_nbr="SELECT COALESCE(MAX(ORD_NBR),0)+1 AS NEW_NBR FROM CMP.PRN_DIG_ORD_HEAD";
			$result_nbr=mysql_query($query_nbr);
			$row_nbr=mysql_fetch_array($result_nbr);
			$OrdNbrNew=$row_nbr['NEW_NBR'];
				
			$query = "INSERT INTO CMP.PRN_DIG_ORD_HEAD (ORD_NBR,ORD_TS,ORD_STT_ID,BUY_PRSN_NBR,BUY_CO_NBR,CNS_CO_NBR,BIL_CO_NBR,REF_NBR,ORD_TTL,DUE_TS,PRN_CO_NBR,SLS_PRSN_NBR,FEE_MISC,TAX_APL_ID,TAX_AMT,TOT_AMT,CMP_TS,PU_TS,PAY_DUE_DT,SPC_NTE,IVC_PRN_CNT,JOB_LEN_TOT,DL_CNT,PU_CNT,NS_CNT,DEL_NBR,CRT_TS,CRT_NBR,UPD_TS,UPD_NBR)
					SELECT 
					'" . $OrdNbrNew . "', CURRENT_TIMESTAMP, 'NE', BUY_PRSN_NBR,BUY_CO_NBR,
					CNS_CO_NBR,BIL_CO_NBR,'".$OrdNbr."',ORD_TTL,DUE_TS, PRN_CO_NBR,SLS_PRSN_NBR,FEE_MISC,TAX_APL_ID,TAX_AMT,TOT_AMT,CMP_TS,PU_TS,PAY_DUE_DT,SPC_NTE, 0, 0, 0, 0, 0, 0,
					CURRENT_TIMESTAMP, " . $_SESSION['personNBR'] . ", CURRENT_TIMESTAMP, " . $_SESSION['personNBR'] . "
					FROM CMP.PRN_DIG_ORD_HEAD_EST WHERE ORD_NBR=" . $OrdNbr;
			$result = mysql_query($query);

			$OrdDetNbrs=explode(',',$OrdDetNbrStr);
			foreach($OrdDetNbrs as $OrdDetNbr){
					
				$query="INSERT INTO CMP.PRN_DIG_ORD_DET (
							SELECT (SELECT COALESCE(MAX(ORD_DET_NBR),0)+1 AS ORD_DET_NBR FROM CMP.PRN_DIG_ORD_DET) AS ORD_DET_NBR," . $OrdNbrNew . ", ORD_DET_NBR_PAR,PRN_DIG_TYP,PRN_DIG_PRC,LOW_PRC_F,ORD_Q,DET_TTL,N_UP,FIL_LOC,FIL_ATT,PRN_LEN,PRN_WID,FIN_BDR_TYP,FIN_BDR_WID,FIN_LOP_WID,GRM_CNT_TOP,GRM_CNT_BTM,GRM_CNT_LFT,GRM_CNT_RGT,PRFO_F,BK_TO_BK_F,ROLLED_F,FEE_MISC,FAIL_CNT,DISC_PCT,DISC_AMT,VAL_ADD_AMT,TOT_SUB,JOB_LEN,PRN_CMP_Q,FIN_CMP_Q,HND_OFF_TYP,HND_OFF_TS,HND_OFF_NBR,SORT_BAY_ID,SORT_BAY_TS,SORT_BAY_NBR,0,CURRENT_TIMESTAMP, " . $_SESSION['personNBR'] . ", CURRENT_TIMESTAMP, " . $_SESSION['personNBR'] . " FROM CMP.PRN_DIG_ORD_DET_EST WHERE ORD_DET_NBR=" . $OrdDetNbr ."
				)";
				mysql_query($query);
			}
				
			$queryDeta ="SELECT 
					DET.ORD_DET_NBR,DET.ORD_DET_NBR_PAR,COUNT(DET.ORD_DET_NBR_PAR) AS COUNT_PAR,MIN(DET.ORD_DET_NBR) AS MIN_PAR,COALESCE(MIN(DET.ORD_DET_NBR),0)-1 AS NEW_NBR_PAR
					FROM CMP.PRN_DIG_ORD_DET DET
					WHERE DET.ORD_NBR=".$OrdNbrNew." AND DET.ORD_DET_NBR_PAR IS NOT NULL AND DET.DEL_NBR=0
					GROUP BY DET.ORD_DET_NBR_PAR";
			$resultsDet = mysql_query($queryDeta);
				
			while($rowsDet=mysql_fetch_array($resultsDet)){
				$minimumPar=$rowsDet['MIN_PAR'];
				$parNumber =$rowsDet['NEW_NBR_PAR'];
				
				$queryPar ="UPDATE CMP.PRN_DIG_ORD_DET SET ORD_DET_NBR_PAR = '".$parNumber."' WHERE ORD_DET_NBR_PAR = '".$rowsDet['ORD_DET_NBR_PAR']."'";
				$result = mysql_query($queryPar);
				}
				
				$OrdNbr = $OrdNbrNew;
				header('Location: print-digital-tripane.php?STT=NE&TYP=&GOTO=TOP');
		}
	}
	
	if($type == "EST"){
		$headtable 	= "CMP.PRN_DIG_ORD_HEAD_EST";
		$detailtable= "CMP.PRN_DIG_ORD_DET_EST";
	}else{
		$headtable 	= "CMP.PRN_DIG_ORD_HEAD";
		$detailtable= "CMP.PRN_DIG_ORD_DET";
	}
	
    if($OrdNbr==''){exit;}
	$Security=getSecurity($_SESSION['userID'],"DigitalPrint");
	$UpperSec=getSecurity($_SESSION['userID'],"Executive");
	$CashSec=getSecurity($_SESSION['userID'],"Finance");
	$Acc=getSecurity($_SESSION['userID'],"Accounting");

	//Process changes here
	if($_POST['ORD_NBR']!="")
	{
		$OrdNbr=$_POST['ORD_NBR'];
		
		//Take care of nulls and timestamps
		if($_POST['BUY_PRSN_NBR']==""){$BuyPrsnNbr="NULL";}else{$BuyPrsnNbr=$_POST['BUY_PRSN_NBR'];}
		if($_POST['BUY_CO_NBR']==""){$BuyCoNbr="NULL";}else{$BuyCoNbr=$_POST['BUY_CO_NBR'];}
		if($_POST['CNS_CO_NBR']==""){$CnsCoNbr="NULL";}else{$CnsCoNbr=$_POST['CNS_CO_NBR'];}
		if($_POST['BIL_CO_NBR']==""){$BilCoNbr="NULL";}else{$BilCoNbr=$_POST['BIL_CO_NBR'];}
		if($_POST['SLS_PRSN_NBR']==""){$SlsPrsnNbr="NULL";}else{$SlsPrsnNbr=$_POST['SLS_PRSN_NBR'];}
		if($_POST['DUE_DTE']==""){$DueTS="NULL";}else{$DueTS="'".$_POST['DUE_DTE']." ".$_POST['DUE_HR'].":".$_POST['DUE_MIN'].":00'";}
		if($_POST['PRN_CO_NBR']==""){$PrnCoNbr="NULL";}else{$PrnCoNbr=$_POST['PRN_CO_NBR'];}
		if($_POST['FEE_MISC']==""){$FeeMisc="NULL";}else{$FeeMisc=$_POST['FEE_MISC'];}
		if($_POST['TAX_AMT']==""){$TaxAmt="NULL";}else{$TaxAmt=$_POST['TAX_AMT'];}
		if($_POST['TOT_AMT']==""){$TotAmt="NULL";}else{$TotAmt=$_POST['TOT_AMT'];}		
		if(($_POST['PYMT_DOWN']=="")||($_POST['PYMT_DOWN']=="0")){$PymtDown="NULL";}else{$PymtDown=$_POST['PYMT_DOWN'];}
		if(($_POST['PYMT_REM']=="")||($_POST['PYMT_REM']=="0")){$PymtRem="NULL";}else{$PymtRem=$_POST['PYMT_REM'];}
		if($_POST['TOT_REM']==""){$TotRem="NULL";}else{$TotRem=$_POST['TOT_REM'];}
		if($_POST['CMP_DTE']==""){$CmpTS="NULL";}else{$CmpTS="'".$_POST['CMP_DTE']." ".$_POST['CMP_TME']."'";}
		if($_POST['PU_DTE']==""){$PUTS="NULL";}else{$PUTS="'".$_POST['PU_DTE']." ".$_POST['PU_TME']."'";}
		if($_POST['CRDT_AMT']==""){$CrdtAmt="NULL";}else{$CrdtAmt=$_POST['CRDT_AMT'];}
		if($_POST['CRDT_MAX']==""){$CrdtMax="NULL";}else{$CrdtMax=$_POST['CRDT_MAX'];}


		//Process add new
		if($OrdNbr==-1)
		{
			$query="SELECT COALESCE(MAX(ORD_NBR),0)+1 AS NEW_NBR FROM ". $headtable ."";
			$result=mysql_query($query);
			$row=mysql_fetch_array($result);
			$OrdNbr=$row['NEW_NBR'];
			$query="INSERT INTO ". $headtable ." (ORD_NBR) VALUES (".$OrdNbr.")";
			$result=mysql_query($query);
			$create="CRT_TS=CURRENT_TIMESTAMP,CRT_NBR=".$_SESSION['personNBR'].",";
			$new=true;

		}

		//Process status journal
	   	if($_POST['ORD_STT_ID']!="")
	   	{
	   		$query="SELECT ORD_STT_ID FROM ". $headtable ." WHERE ORD_NBR=$OrdNbr";
			$result=mysql_query($query);
			$row=mysql_fetch_array($result);
			if($row['ORD_STT_ID']!=$_POST['ORD_STT_ID'])
			{
				if($type != "EST"){
					$query="INSERT INTO CMP.JRN_PRN_DIG (ORD_NBR, JRN_TYP, CRT_NBR)
							VALUES (".$OrdNbr.",'STT','".$_POST['ORD_STT_ID']."',".$_SESSION['personNBR'].")";
					//echo $query;
					$resultp=mysql_query($query);
					$querysl="SELECT ORD_STT_ID, SLACK_F FROM CMP.PRN_DIG_STT WHERE SLACK_F='1'";
					$resultsl=mysql_query($querysl);
					while($rowsl=mysql_fetch_array($resultsl)){
						if ($rowsl['ORD_STT_ID']==$_POST['ORD_STT_ID']){
							$slack=true;
						}
					}
				}
			}
		}

		//Process sales journal
	   	if($_POST['SLS_PRSN_NBR']!="")
	   	{
	   		$query="SELECT SLS_PRSN_NBR FROM ". $headtable ." WHERE ORD_NBR=$OrdNbr";
			$result=mysql_query($query);
			$row=mysql_fetch_array($result);
			if($row['SLS_PRSN_NBR']!=$_POST['SLS_PRSN_NBR'])
			{
				$query="INSERT INTO CMP.JRN_PRN_DIG (ORD_NBR, JRN_TYP, SLS_PRSN_NBR, CRT_NBR)
						VALUES (".$OrdNbr.",'SLS',".$_POST['SLS_PRSN_NBR'].",".$_SESSION['personNBR'].")";
				//echo $query;
				$resultp=mysql_query($query);
			}
		}

		$query="UPDATE ". $headtable ."
				SET ORD_TS='".$_POST['ORD_DTE']." ".$_POST['ORD_TME']."',
	   				ORD_STT_ID='".$_POST['ORD_STT_ID']."',
					BUY_PRSN_NBR=".$BuyPrsnNbr.",
					BUY_CO_NBR=".$BuyCoNbr.",
					CNS_CO_NBR=".$CnsCoNbr.",
					BIL_CO_NBR=".$BilCoNbr.",
					SLS_PRSN_NBR=".$SlsPrsnNbr.",
					REF_NBR='".$_POST['REF_NBR']."',
					ORD_TTL='".mysql_real_escape_string($_POST['ORD_TTL'])."',
					DUE_TS=".$DueTS.",
					PRN_CO_NBR=".$PrnCoNbr.",
					FEE_MISC=".$FeeMisc.",
					TAX_APL_ID='".$_POST['TAX_APL_ID']."',
					TAX_AMT=".$TaxAmt.",
					TOT_AMT=".$TotAmt.",
					PYMT_DOWN=".$PymtDown.",
					PYMT_REM=".$PymtRem.",
					TOT_REM=".$TotRem.",
					CMP_TS=".$CmpTS.",
					PU_TS=".$PUTS.",".$create."
					UPD_TS=CURRENT_TIMESTAMP,
					UPD_NBR=".$_SESSION['personNBR']."
					WHERE ORD_NBR=".$OrdNbr;
		//echo $query;
	   	$result=mysql_query($query);	
	   	$changed=true;

		//Process invoice journal
		if (!$new) {
			$query="INSERT INTO CMP.JRN_PRN_DIG (ORD_NBR, JRN_TYP, CRT_NBR)
					VALUES (".$OrdNbr.",'HED',".$_SESSION['personNBR'].")";
			//echo $query;
			$resultp=mysql_query($query);
		}

		
		//Process Slack Webhook
        if($slack){
            $query="SELECT NAME FROM CMP.COMPANY WHERE CO_NBR=$CoNbrDef";
            $resultp=mysql_query($query);
			$rowp=mysql_fetch_array($resultp);
            $DefCoName=$rowp['NAME'];
            $query="SELECT ORD_TTL,COM.NAME AS COM_NM,PPL.NAME AS PPL_NM,ORD_STT_DESC,UPD.NAME AS UPD_NM,SLACK_CHNNL_NM FROM CMP.PRN_DIG_ORD_HEAD HED
                    INNER JOIN CMP.PRN_DIG_STT STT ON HED.ORD_STT_ID=STT.ORD_STT_ID
                    INNER JOIN CMP.PEOPLE UPD ON HED.UPD_NBR=UPD.PRSN_NBR
                    LEFT OUTER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR=COM.CO_NBR
                    LEFT OUTER JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR=PPL.PRSN_NBR
                    WHERE ORD_NBR=$OrdNbr";
            $resultp=mysql_query($query);
			$rowp=mysql_fetch_array($resultp);
            //echo $query;
            $cust=trim($rowp['COM_NM']." ".$rowp['PPL_NM']);
            if($cust==""){$cust="Tunai";}

            //Check chanel from cabang
	        if ($CoNbrDef=='1002'){
	        	$slackChannelName = 'campus-order-status';
	        }else if ($CoNbrDef == '271' ){
	        	$slackChannelName = 'printing-order-status';
	        }
	
            $message="Nota *$DefCoName* nomor *$OrdNbr* berjudul *".$rowp['ORD_TTL']."* customer *$cust* telah diubah statusnya menjadi *".$rowp['ORD_STT_DESC']."* oleh *".shortName($rowp['UPD_NM'])."*.";
            slack($message,$slackChannelName);
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
<link rel="stylesheet" type="text/css" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" media="screen" />

<script type="text/javascript" src="framework/validation/mootools-1.2.3.js"></script>
<script type="text/javascript" src="framework/mootools/mootools-latest.min.js"></script>
<script type="text/javascript" src="framework/mootools/mootools-latest-more.js"></script>
<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.min.js"></script>

<script type="text/javascript" src="framework/functions/default.js"></script>

<script type="text/javascript">jQuery.noConflict();</script>

<link rel="stylesheet" href="framework/combobox/chosen.css">

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

	function calcAmt(){
		switch (document.getElementById('TAX_APL_ID').value) {
			case "E" : 
				document.getElementById('TOT_AMT').value=getInt('TOT_NET')+getInt('FEE_MISC');
				document.getElementById('TOT_REM').value=getInt('TOT_AMT')-getInt('TOT_PAY')-getInt('TND_AMT');
				document.getElementById('TAX_AMT').value="";
				break;
			case "I" : 
				document.getElementById('TOT_AMT').value=getInt('TOT_NET')+getInt('FEE_MISC');
				document.getElementById('TOT_REM').value=getInt('TOT_AMT')-getInt('TOT_PAY')-getInt('TND_AMT');
				document.getElementById('TAX_AMT').value=Math.round(getInt('TOT_AMT')*parseFloat(getParam("tax","ppn")));
				break;
			case "A" : 
				document.getElementById('TOT_AMT').value=getInt('TOT_NET')+getInt('FEE_MISC');
				document.getElementById('TAX_AMT').value=Math.round(getInt('TOT_AMT')*parseFloat(getParam("tax","ppn")));
				document.getElementById('TOT_AMT').value=getInt('TOT_NET')+getInt('FEE_MISC')+getInt('TAX_AMT');
				document.getElementById('TOT_REM').value=getInt('TOT_AMT')-getInt('TOT_PAY')-getInt('TND_AMT');
				break;
		}
	}
	
	function stampTime(comboBox){
		switch (comboBox.value) {
			case "RD" : document.getElementById('CMP_TME').value=getCurTime();document.getElementById('CMP_DTE').value=getCurDate();break;
			case "CP" : document.getElementById('PU_DTE').value=getCurDate(); document.getElementById('PU_TME').value=getCurTime();break;
		}
	}
	
	//This is to make sure that the value of each combo is submitted
	function enableCombos(button){
		
		var container=button.parentNode;
		var combos=container.getElementsByTagName('select');
		for(var count=0;count<combos.length;count++){
			var curCombo=combos[count];
			curCombo.disabled=false;
		}
	}
		
	function attach_file(p_script_url){
      // create new script element, set its relative URL, and load it
      script=document.createElement('script');
      script.src=p_script_url;
      document.getElementsByTagName('head')[0].appendChild(script);
	}
</script>

<link rel="stylesheet" type="text/css" media="screen" href="framework/alert/alert.css" />

</head>

<body>

<script>
	/*
	parent.parent.document.getElementById('invoiceDeleteYes').onclick=
	function () { 
		parent.parent.document.getElementById('content').contentDocument.getElementById('leftpane').src='print-digital.php?DEL=<?php echo $OrdNbr ?>&STT=<?php echo $_GET['STT']; ?>&TYP=<?php echo $type; ?>';
		parent.parent.document.getElementById('invoiceDelete').style.display='none';
		parent.parent.document.getElementById('fade').style.display='none';
	};
	*/

	parent.parent.document.getElementById('invoiceDeleteYes').onclick=
	function () { 
		parent.parent.document.getElementById('printDigitalReasonContent').src='print-digital-edit-reason.php?ORD_NBR=<?php echo $OrdNbr ?>&STT=<?php echo $_GET['STT']; ?>&TYP=<?php echo $type; ?>';
		parent.parent.document.getElementById('printDigitalReason').style.display='block';
		parent.parent.document.getElementById('invoiceDelete').style.display='none';
		parent.parent.document.getElementById('fade').style.display='block';
	};
    
	parent.parent.document.getElementById('transportCreateYes').onclick=
    function () {
        createDelivery();
		parent.parent.document.getElementById('transportCreate').style.display='none';
		parent.parent.document.getElementById('fade').style.display='none'; 
	};
	
	parent.parent.document.getElementById('proformaCreateYes').onclick=
    function () {
        createConvert();
		parent.parent.document.getElementById('proformaCreate').style.display='none';
		parent.parent.document.getElementById('fade').style.display='none'; 
	};
	
	<?php
		if($new){
			echo "parent.document.getElementById('leftpane').contentDocument.location.reload(true);";	
		}else{
			if($changed){
				echo "parent.parent.document.getElementById('leftmenu').contentDocument.location.reload(true);";
			}
		}
	?>
    
    function checkDelivery(){
        var c=document.getElementsByTagName('input');
        var queryStr='';
            for(var i=0;i<c.length;i++){
            if(c[i].type=='checkbox') {
                if(c[i].name.substr(0,8)=='SEL_IMG_'){
                    if(c[i].checked){;
                        queryStr+=c[i].name.substr(8,c[i].name.length-8)+',';
                    }
                }
            }
        }
        if(queryStr==''){
            window.scrollTo(0,0);parent.parent.document.getElementById('transportBlank').style.display='block';
            parent.parent.document.getElementById('fade').style.display='block';
        }else{
            window.scrollTo(0,0);parent.parent.document.getElementById('transportCreate').style.display='block';
            parent.parent.document.getElementById('fade').style.display='block';
        }
    }
    
    function createDelivery(){
        var c=document.getElementsByTagName('input');
        var queryStr='';
            for(var i=0;i<c.length;i++){
            if(c[i].type=='checkbox') {
                if(c[i].name.substr(0,8)=='SEL_IMG_'){
                    if(c[i].checked){;
                        queryStr+=c[i].name.substr(8,c[i].name.length-8)+',';
                    }
                }
            }
        }
        if(queryStr==''){return;}
        parent.parent.document.getElementById('transport').click();
        parent.parent.document.getElementById('leftmenu').src='transport-lm.php?STT=IN';
        queryStr='transport-tripane.php?ORD_NBR=<?php echo $OrdNbr; ?>&ORD_DET_NBR='+queryStr.substr(0,queryStr.length-1)+'&STT=ADD';
        parent.location.href=queryStr;
        //alert(queryStr);
    }
	<?php
	$query="SELECT ORD_NBR FROM CMP.PRN_DIG_ORD_HEAD WHERE DEL_NBR=0 AND REF_NBR =".$OrdNbr;
	$result=mysql_query($query);
	$row=mysql_num_rows($result);
		
	?>
	function checkConvert(){
        var c=document.getElementsByTagName('input');
        var queryStr='';
            for(var i=0;i<c.length;i++){
            if(c[i].type=='checkbox') {
                if(c[i].name.substr(0,8)=='SEL_IMG_'){
                    if(c[i].checked){;
                        queryStr+=c[i].name.substr(8,c[i].name.length-8)+',';
                    }
                }
            }
        }
        if(queryStr==''){
            window.scrollTo(0,0);parent.parent.document.getElementById('proformaBlank').style.display='block';
            parent.parent.document.getElementById('fade').style.display='block';
        }else if(<?php echo $row; ?>!=''){
            window.scrollTo(0,0);parent.parent.document.getElementById('proformaCheck').style.display='block';
            parent.parent.document.getElementById('fade').style.display='block';
        }else{
            window.scrollTo(0,0);parent.parent.document.getElementById('proformaCreate').style.display='block';
            parent.parent.document.getElementById('fade').style.display='block';
        }
    }
    
    function createConvert(){
        var c=document.getElementsByTagName('input');
        var queryStr='';
            for(var i=0;i<c.length;i++){
            if(c[i].type=='checkbox') {
                if(c[i].name.substr(0,8)=='SEL_IMG_'){
                    if(c[i].checked){;
                        queryStr+=c[i].name.substr(8,c[i].name.length-8)+',';
                    }
                }
            }
        }
        if(queryStr==''){return;}
        parent.parent.document.getElementById('leftmenu').src='print-digital-lm.php?STT=NE';
        queryStr='print-digital-edit.php?ORD_NBR=<?php echo $OrdNbr; ?>&ORD_DET_NBR='+queryStr.substr(0,queryStr.length-1)+'&CONV=NEW';
        parent.location.href=queryStr;
    }
</script>
<?php
	//Make sure there is no error so the page load is halted.
	if($new){exit;}
?>

<div style="display:none;">
	<input id="refresh-pay" type="button" value="Refresh" onclick="syncGetContent('pay','print-digital-payment-list.php?ORD_NBR=<?php echo $OrdNbr; ?>&TYP=<?php echo $type; ?>');" />
	<input id="refresh-list" type="button" value="Refresh" onclick="syncGetContent('edit-list','print-digital-edit-list.php?ORD_NBR=<?php echo $OrdNbr; ?>&TYP=<?php echo $type; ?>');" />
	<input id="refresh-list-del" type="button" value="Refresh" onclick="syncGetContent('edit-list-del','print-digital-edit-list.php?ORD_NBR=<?php echo $OrdNbr; ?>&TYP=<?php echo $type; ?>');" />
	<input id="refresh-tot" type="button" value="Total" onclick="calcAmt();" />
</div>

<?php
	$query="SELECT ORD_NBR,ORD_TS,HED.ORD_STT_ID,ORD_STT_DESC,BUY_PRSN_NBR,NBR,PPL.NAME AS NAME_PPL,COM.NAME AS NAME_CO,BUY_CO_NBR,HED.CNS_CO_NBR,BIL_CO_NBR,REF_NBR,ORD_TTL,DUE_TS,PRN_CO_NBR,SLS_PRSN_NBR,FEE_MISC,TAX_APL_ID,TAX_AMT,TOT_AMT,PYMT_DOWN,PYMT_REM,VAL_PYMT_DOWN,VAL_PYMT_REM,TOT_REM,CMP_TS,PU_TS,DL_CNT,PU_CNT,NS_CNT,IVC_PRN_CNT,SPC_NTE,JOB_LEN_TOT,HED.CRT_TS,HED.CRT_NBR,HED.UPD_TS,HED.UPD_NBR,CRT.NAME AS NAME_CRT,UPD.NAME AS NAME_UPD,COALESCE(COM.CRDT_MAX,0) AS COM_CRDT_MAX, COALESCE(PPL.CRDT_MAX,0) AS PPL_CRDT_MAX, STT.ORD_STT_ORD
			FROM ". $headtable ." HED
			INNER JOIN CMP.PRN_DIG_STT STT ON HED.ORD_STT_ID=STT.ORD_STT_ID
			LEFT OUTER JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR=PPL.PRSN_NBR
			LEFT OUTER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR=COM.CO_NBR
			LEFT OUTER JOIN CMP.PEOPLE CRT ON HED.CRT_NBR=CRT.PRSN_NBR
			LEFT OUTER JOIN CMP.PEOPLE UPD ON HED.UPD_NBR=UPD.PRSN_NBR
			LEFT OUTER JOIN CDW.PRN_DIG_TOP_CUST TOP ON HED.BUY_CO_NBR=TOP.NBR
			WHERE ORD_NBR=".$OrdNbr;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);

	if (($row['BUY_CO_NBR']!=NULL)&&($row['BUY_PRSN_NBR']!=NULL)){ //berdasarkan company
		$Max 			= $row['COM_CRDT_MAX'];
		$query_credit 	= "SELECT COUNT(*) AS CNT_CRDT, COALESCE(SUM(TOT_REM),0) AS TOT_REM FROM ".$headtable." HED
								LEFT JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR=COM.CO_NBR
								LEFT JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR=PPL.PRSN_NBR 
							WHERE HED.BUY_CO_NBR='".$row['BUY_CO_NBR']."'
								AND HED.TOT_REM>0
								AND HED.DEL_NBR=0";
	} else if ($row['BUY_CO_NBR']!=NULL){ //berdasarkan company
		$Max 			= $row['COM_CRDT_MAX'];
		$query_credit 	= "SELECT COUNT(*) AS CNT_CRDT, COALESCE(SUM(TOT_REM),0) AS TOT_REM FROM ".$headtable." HED
								LEFT JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR=COM.CO_NBR
								LEFT JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR=PPL.PRSN_NBR 
							WHERE HED.BUY_CO_NBR='".$row['BUY_CO_NBR']."' 
								AND HED.TOT_REM>0
								AND HED.DEL_NBR=0";
	} else { //berdasarkan perorangan (perusahaan=NULL)
		$Max 			= $row['PPL_CRDT_MAX'];
		$query_credit 	= "SELECT COUNT(*) AS CNT_CRDT, COALESCE(SUM(TOT_REM),0) AS TOT_REM FROM ".$headtable." HED
								LEFT JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR=COM.CO_NBR
								LEFT JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR=PPL.PRSN_NBR 
							WHERE HED.BUY_CO_NBR=NULL 
								AND HED.BUY_PRSN_NBR='".$row['BUY_PRSN_NBR']."'
								AND HED.TOT_REM>0
								AND HED.DEL_NBR=0";
	}
	$result_credit 		= mysql_query($query_credit);
	$row_credit 		= mysql_fetch_array($result_credit);
	
	if ($Max>0){
		if ($row_credit['TOT_REM']>$Max) {
			$query_jrn_crdt 	= "INSERT INTO CMP.JRN_CRDT (JRN_CRDT_NBR, ORD_NBR, BUY_CO_NBR, BUY_PRSN_NBR, CRDT_AMT, CRT_TS, CRT_NBR) VALUES (
									'','".$OrdNbr."',$BuyCoNbr,$BuyPrsnNbr,'$row_credit[TOT_REM]',CURRENT_TIMESTAMP,'".$_SESSION['personNBR']."')";
			$result_jrn_crdt 	= mysql_query($query_jrn_crdt);
		}
	}

	//Process security and process
	$headerRead="";
	$headerEnable="";
	$headerSelect="";
	$footerRead="";
	$statusEnable="";
	if($Security==1){
		if(in_array($row["ORD_STT_ID"],array('RD','DL','CP'))){
			$headerRead="readonly";
			$headerEnable="disabled";
			$headerSelect="WHERE ORD_STT_ID IN ('RD','DL','CP','".$row["ORD_STT_ID"]."')";
		}elseif(in_array($row["ORD_STT_ID"],array('PR','FN'))){
			$headerRead="readonly";
			$headerEnable="disabled";
			$headerSelect="WHERE ORD_STT_ID IN ('".$row["ORD_STT_ID"]."')";
		}elseif(in_array($row["ORD_STT_ID"],array('NE','RC','QU','PF','LT'))){
			$headerSelect="WHERE ORD_STT_ID IN ('NE','RC','QU','PF','LT','".$row["ORD_STT_ID"]."')";
		}
	}
	if($Security==2){
		$headerRead="readonly";
		$headerEnable="disabled";
		$footerRead="readonly";
		$headerSelect="WHERE ORD_STT_ID IN ('QU','PR','FN','RD','".$row["ORD_STT_ID"]."')";
		if(!in_array($row["ORD_STT_ID"],array('QU','PR','FN','RD'))){$stateEnable="disabled";}
	}
	
	if($UpperSec==6){
		$headerSelect=" ";
	}

	if(($Security<2 && $UpperSec<7 && $CashSec<3 && $Acc<8) && ($CashSec<>1 || $UpperSec<1)){
		$statusEnable="";
	} else {
		$headerSelect="WHERE ORD_STT_ORD >= ".$row["ORD_STT_ORD"];
		if ($row["ORD_STT_ORD"]<5){ 
			$headerSelect="WHERE ORD_STT_ORD >= ".$row["ORD_STT_ORD"]." AND ORD_STT_ORD <=5";
		}

		if ($row["ORD_STT_ORD"]==""){
			$headerSelect="WHERE ORD_STT_ORD <=5";
		}

		if(in_array($row["ORD_STT_ID"],array('QU','PR','FN'))){$sttEnable="disabled";}
	}
?>

<?php
	if($changed){
		//Mirror the innerHTML from print-digital.php
		$due=strtotime($row['DUE_TS']);
		$OrdSttId=$row['ORD_STT_ID'];
		if((strtotime("now")>$due)&&(($OrdSttId=="NE")||($OrdSttId=="RC")||($OrdSttId=="QU")||($OrdSttId=="PR")||($OrdSttId=="FN"))){
			$dot="<div class='listable' style='display:inline;float:right'><span class='fa fa-circle listable' style='font-size:8pt;color:#d92115'></span></div>";
		}elseif((strtotime("now + ".$row['JOB_LEN_TOT']." minute")>$due)&&(($OrdSttId=="NE")||($OrdSttId=="RC")||($OrdSttId=="QU")||($OrdSttId=="PR")||($OrdSttId=="FN"))){
			$dot="<div class='listable' style='display:inline;float:right'><span class='fa fa-circle listable' style='font-size:8pt;color:#fbad06'></span></div>";				
		}else{
			$dot="";
		}
		//echo $due." ".strtotime("now")." ".strtotime("now + ".$row['JOB_LEN_TOT']." minute")."<br>";
				
		$newStr="<div style='font-weight:bold;color:#666666;font-size:12pt;display:inline;float:left'>".$row['ORD_NBR']."</div>";
		$newStr.="<div style='display:inline;float:right;'>".parseDateTimeLiteralShort($row['DUE_TS'])."</div>";
		$newStr.="<div style='clear:both'></div>";
		$newStr.="<div style='display:inline;float:left;'>";
		if($row['NBR']!=""){
			$newStr.="<div class='listable'><span class='fa fa-star listable'></span></div>";
		}				
		if($row['SPC_NTE']!=""){
			$newStr.="<div class='listable'><span class='fa fa-comment listable'></span></div>";
		}
		if($row['DL_CNT']>0){
			$newStr.="<div class='listable'><span class='fa fa-truck listable' style='margin-left:-1px'></span></div>";
		}
		if($row['PU_CNT']>0){
			$newStr.="<div class='listable'><span class='fa fa-shopping-cart listable'></span></div>";
		}
		if($row['NS_CNT']>0){
			$newStr.="<div class='listable'><span class='fa fa-flag listable'></span></div>";
		}
		if($row['IVC_PRN_CNT']>0){
			$newStr.="<div class='listable'><span class='fa fa-print listable'></span></div>";
		}
		$newStr.="&nbsp;</div>";
		$newStr.="<div style='clear:both'></div>";
		if(trim($row['NAME_PPL']." ".$row['NAME_CO'])==""){$name="Tunai";}else{$name=trim($row['NAME_PPL']." ".$row['NAME_CO']);}
		$newStr.= $dot;
		$newStr.="<div style='font-weight:700;color:#3464bc'>".$name."</div>";
		$newStr.="<div>".$row['ORD_TTL']."</div>";
		$newStr.="<div>".parseDateShort($row['ORD_TS'])."&nbsp;";
		$newStr.="<span style='font-weight:700'>".$row['ORD_STT_DESC']."</span>";
		$newStr.="<span style='float:right;style='color:#888888'>";
		if($row['TOT_REM']==0){
		$newStr.="<div class='listable' style='display:inline;float:left'><span class='fa fa-circle listable' style='font-size:8pt;color:#3464bc'></span></div>";
        }elseif($row['TOT_AMT']==$row['TOT_REM']){
		$newStr.="<div class='listable' style='display:inline;float:left'><span class='fa fa-circle-o listable' style='font-size:8pt;color:#3464bc'></span></div>";
        }else{
		$newStr.="<div class='listable' style='display:inline;float:left'><span class='fa fa-dot-circle-o listable' style='font-size:8pt;color:#3464bc'></span></div>";
        }
		$newStr.="&nbsp;Rp. ".number_format($row['TOT_AMT'],0,'.',',');
		$newStr.="</span></div>";
		echo "<script>";
		//echo "alert('a');";
		echo "parent.document.getElementById('leftpane').contentWindow.document.getElementById('O".$OrdNbr."').style.opacity=0;";
		echo "parent.document.getElementById('leftpane').contentWindow.document.getElementById('O".$OrdNbr."').style.filter='alpha(opacity=0)';";
		echo "parent.document.getElementById('leftpane').contentWindow.document.getElementById('O".$OrdNbr."').innerHTML=".chr(34).$newStr.chr(34).";";
		echo "fadeIn(parent.document.getElementById('leftpane').contentWindow.document.getElementById('O".$OrdNbr."'));";
		echo "</script>";
	}
?>

<div class="toolbar-only">
	<?php if(($Security==0)&&($OrdNbr!=0)) { ?>
    <p class="toolbar-left"><a href="javascript:void(0)" onclick = "window.scrollTo(0,0);parent.parent.document.getElementById('invoiceDelete').style.display='block';parent.parent.document.getElementById('fade').style.display='block'"><span class='fa fa-trash toolbar' style="cursor:pointer;<?php if ($_GET['STT']=="DEL") echo "display:none;";  ?>"></span></a></p>
	<?php } ?>
	<p class="toolbar-right">
		<?php if($type == "EST"){ ?>
        <a href="javascript:void(0)" onClick="checkConvert()">
			<span class='fa fa-copy toolbar' style='cursor:pointer'></span>
		</a>
		<?php } ?>
		<?php if($type != "EST"){ ?>
			<a href="print-digital-invoice-pdf.php?ORD_NBR=<?php echo $OrdNbr; ?>&TYPE=PRINT&TYP=<?php echo $type; ?>"><span class='fa fa-file-powerpoint-o toolbar' style="cursor:pointer"></span></a>
			<a href="print-digital-invoice-pdf.php?ORD_NBR=<?php echo $OrdNbr; ?>&TYPE=PDF&TYP=<?php echo $type; ?>"><span class='fa fa-file-pdf-o toolbar' style="cursor:pointer"></span></a>
		<?php }elseif($type == "EST" && $CashSec <= 1){ ?>
			<a href="print-digital-invoice-pdf.php?ORD_NBR=<?php echo $OrdNbr; ?>&TYPE=PRINT&TYP=<?php echo $type; ?>"><span class='fa fa-file-powerpoint-o toolbar' style="cursor:pointer"></span></a>
			<a href="print-digital-invoice-pdf.php?ORD_NBR=<?php echo $OrdNbr; ?>&TYPE=PDF&TYP=<?php echo $type; ?>"><span class='fa fa-file-pdf-o toolbar' style="cursor:pointer"></span></a>
		<?php } ?>
		<?php if($type != "EST"){ ?>
        <a href="javascript:void(0)" onClick="checkDelivery()">
			<span class='fa fa-truck fa-flip-horizontal toolbar' style='cursor:pointer'></span>
		</a>
		<?php } ?>
		<?php if($type != "EST"){ ?>
		<a href="print-digital-edit-print.php?ORD_NBR=<?php echo $OrdNbr; ?>&PRN_TYP=Invoice&TYP=<?php echo $type; ?>"><span class='fa fa-print toolbar'></span></a>
		<?php }elseif($type == "EST" && $CashSec <= 1){ ?>
		<a href="print-digital-edit-print.php?ORD_NBR=<?php echo $OrdNbr; ?>&PRN_TYP=Invoice&TYP=<?php echo $type; ?>"><span class='fa fa-print toolbar'></span></a>
		<?php } ?>
	</p>
</div>
			
<form id='mainForm' enctype="multipart/form-data" action="#" method="post" style="width:700px" onSubmit="enableCombos(this);">
	<p>
        <h3>
            Nota Penjualan
        </h3>
		<h2>
			<?php echo $row['ORD_NBR'];if($row['ORD_NBR']==""){echo "Baru";} ?>
		</h2>
        
		<!-- Header -->
		<div style="float:left;width:140px;">
			<input id="ORD_NBR" name="ORD_NBR" type="hidden" value="<?php echo $row['ORD_NBR'];if($row['ORD_NBR']==""){echo "-1";} ?>"/>
			
			<label>Tanggal Nota</label>
			<?php 
				if($row['ORD_TS']==""){$OrdDte="";}else{$OrdDte=parseDate($row['ORD_TS']);}
			?>
			<input name="ORD_DTE" id="ORD_DTE" value="<?php echo $OrdDte; ?>" type="text" style="width:110px;" <?php echo $headerRead; ?> />
			<?php if($headerRead!="readonly"){ ?>
			<script>
				new CalendarEightysix('ORD_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });			
			</script>
			<?php } ?>
		</div>
		<div>
			<label>Judul Pesanan</label><br />
			<input name="ORD_TTL" id="ORD_TTL" value="<?php echo htmlentities($row['ORD_TTL'],ENT_QUOTES); ?>" type="text" style="width:545px;" <?php echo $headerRead; ?> /><br />	
		</div>
		<div style="clear:both"></div>
		
		<div style="float:left;width:140px">
			<label>Waktu Nota</label><br />
			<?php
				if($row[ORD_TS]==""){$OrdTme=date("G:i:s");}else{$OrdTme=parseTime($row['ORD_TS']);}
			?>
			<input name="ORD_TME" id="ORD_TME" value="<?php echo $OrdTme; ?>" type="text" style="width:110px;" <?php echo $headerRead; ?> />
			<?php if($headerRead!="readonly"){ ?>
            <div class='listable-btn'><span class='fa fa-clock-o listable-btn' style='font-size:14px' onclick="document.getElementById('ORD_TME').value=getCurTime();"></span></div>
			<?php } ?>
            <label>Tanggal Dijanjikan</label><br />
			<?php 
				if($row['DUE_TS']==""){$DueDte="";}else{$DueDte=parseDate($row['DUE_TS']);}
			?>
			<input name="DUE_DTE" id="DUE_DTE" value="<?php echo $DueDte; ?>" type="text" style="width:110px;" <?php echo $headerRead; ?> />
			<?php if($headerRead!="readonly"){ ?>
			<script>
				new CalendarEightysix('DUE_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', <?php if($DueDte==""){echo "'defaultDate': 'tomorrow',";} ?> 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });			
			</script>
			<?php } ?>

			<label>Waktu Dijanjikan</label><br /><div class="labelbox"></div>
			<?php
				if($row['DUE_TS']==""){if(strval(date("G"))>12){$DueHr="14";}else{$DueHr="11";}}else{$DueHr=parseHour($row['DUE_TS']);}
			?>
			<select class="chosen-select" style='width:53px' name="DUE_HR" <?php echo $headerEnable; ?> ><br /><div class='labelbox'></div>
				<?php genComboArrayVal(array('00','01','02','03','04','05','06','07','08','09','10','11','12','13','14','15','16','17','18','19','20','21','22','23'),$DueHr); ?>
			</select>
			<?php
				if($row['DUE_TS']==""){$DueMin="00";}else{$DueMin=parseMinute($row['DUE_TS']);}
			?>
			<select class="chosen-select" style='width:53px' name="DUE_MIN" <?php echo $headerEnable; ?> ><br />
				<?php genComboArrayVal(array('00','15','30','45'),$DueMin); ?>
			</select>
		</div>
		<div>
			<label style='width:150px;height:25px'>Nama Pembeli</label>
			<select name="BUY_PRSN_NBR" class="chosen-select" style="width:400px" <?php echo $headerEnable; ?> >
				<?php
					$query="SELECT PRSN_NBR,CONCAT(NAME,' ',ADDRESS,' ',CITY_NM) AS PRSN_DESC
							FROM CMP.PEOPLE PPL INNER JOIN CMP.CITY CIT ON PPL.CITY_ID=CIT.CITY_ID ORDER BY 2";
					genCombo($query,"PRSN_NBR","PRSN_DESC",$row['BUY_PRSN_NBR'],"Kosong");
				?>
			</select><br />
            <label style='width:150px;height:25px'>Perusahaan Pembeli</label>
            <select name="BUY_CO_NBR" class="chosen-select" style="width:400px" <?php echo $headerEnable; ?> >
				<?php
					$query="SELECT CO_NBR,CONCAT(NAME,' ',ADDRESS,' ',CITY_NM) AS CO_DESC
							FROM CMP.COMPANY COM INNER JOIN CMP.CITY CIT ON COM.CITY_ID=CIT.CITY_ID ORDER BY 2";
					genCombo($query,"CO_NBR","CO_DESC",$row['BUY_CO_NBR'],"Tunai");
				?>
			</select>
            <label style='width:150px;height:25px'>Pihak Pertama</label>
            <select name="CNS_CO_NBR" class="chosen-select" style="width:400px" <?php echo $headerEnable; ?> >
				<?php
					$query="SELECT CO_NBR,CONCAT(NAME,' ',ADDRESS,' ',CITY_NM) AS CO_DESC
							FROM CMP.COMPANY COM INNER JOIN CMP.CITY CIT ON COM.CITY_ID=CIT.CITY_ID ORDER BY 2";
					genCombo($query,"CO_NBR","CO_DESC",$row['CNS_CO_NBR'],"Sama dengan diatas");
				?>
			</select>
            <label style='width:150px;height:25px'>Pihak Yang Ditagih</label>
            <select name="BIL_CO_NBR" class="chosen-select" style="width:400px" <?php echo $headerEnable; ?> >
				<?php
					$query="SELECT CO_NBR,CONCAT(NAME,' ',ADDRESS,' ',CITY_NM) AS CO_DESC
							FROM CMP.COMPANY COM INNER JOIN CMP.CITY CIT ON COM.CITY_ID=CIT.CITY_ID ORDER BY 2";
					genCombo($query,"CO_NBR","CO_DESC",$row['BIL_CO_NBR'],"Sama dengan diatas");
				?>
			</select>
            <label style='width:150px;height:25px'>Perusahaan Produser</label>
			<select name="PRN_CO_NBR" class="chosen-select" style="width:400px" <?php echo $headerEnable; ?> >
				<?php
					if($row['PRN_CO_NBR']==""){$PrnCoID=$CoNbrDef;}else{$PrnCoID=$row['PRN_CO_NBR'];}
					$query="SELECT CO_NBR,CONCAT(NAME,' ',ADDRESS,' ',CITY_NM) AS CO_DESC
							FROM CMP.COMPANY COM INNER JOIN CMP.CITY CIT ON COM.CITY_ID=CIT.CITY_ID ORDER BY 2";
					genCombo($query,"CO_NBR","CO_DESC",$PrnCoID);
				?>
			</select>
			<input type="hidden" name="CRDT_MAX" id="CRDT_MAX" value="<?php echo $Max; ?>" />
			<input type="hidden" name="CRDT_AMT" id="CRDT_AMT" value="<?php echo $row_credit['TOT_REM']; ?>" />
            <?php
				//Check for bad debt -- will add debt ceiling, cash transaction, and offender recording soon
				if($row['BUY_CO_NBR']!=''){
					$query="SELECT COUNT(*) AS NBR_ORD,SUM(TOT_REM) AS TOT_REM,LAST_DAY(DATE_ADD(MIN(ORD_TS),INTERVAL COALESCE(PAY_TERM,32) DAY)) AS DATE_MIN,LAST_DAY(DATE_ADD(MAX(ORD_TS),INTERVAL COALESCE(PAY_TERM,32) DAY)) AS DATE_MAX FROM ". $headtable ." HED INNER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR=COM.CO_NBR WHERE BUY_CO_NBR=".$row['BUY_CO_NBR']." AND TOT_REM>0 AND LAST_DAY(DATE_ADD(ORD_TS,INTERVAL COALESCE(PAY_TERM,32) DAY))<=CURRENT_DATE AND HED.DEL_NBR=0";
					//echo $query;
					$resultd=mysql_query($query);
					$rowd=mysql_fetch_array($resultd);
					if($rowd['TOT_REM']>0){
						echo "<div class='print-digital-red' style='padding-left:8px;padding-right:8px;text-align:left;display:inline-block;width:538px;margin-top:2px;margin-bottom:4px'><b>Warning</b> -- ".$rowd['NBR_ORD']." nota dengan total Rp. ".number_format($rowd['TOT_REM'],0,',','.')." telah jatuh tempo dan belum lunas.  Transaksi ini harus dibayar tunai sebelum nota jatuh tempo dilunasi.</div>";
					}
				}

				if ($Max>0){
					if ($row_credit['TOT_REM']>$Max) {
						echo "<div class='print-digital-red' style='padding-left:8px;padding-right:8px;text-align:left;display:inline-block;width:538px;margin-top:2px;margin-bottom:4px'><b>Warning</b> -- ".$row_credit['CNT_CRDT']." nota dengan total Rp. ".number_format($row_credit['TOT_REM'],0,',','.')." telah melebihi batas jumlah plafon dan belum lunas.  Transaksi ini harus dibayar tunai.</div>";
					}
				}
			?>
		</div>
        <div style="clear:both;padding-bottom:5px"></div>
		<div style="float:left;width:140px">
            <label>No. Referensi</label><br />
			<input name="REF_NBR" id="REF_NBR" value="<?php echo $row['REF_NBR']; ?>" type="text" style="width:110px;" <?php echo $headerRead; ?> />
		</div>
		<div style="float:left;width:140px;">
			<label>Status</label><br /><div class='labelbox'></div>
			<select name="ORD_STT_ID" class="chosen-select" style="width:120px" onchange="stampTime(this)" <?php if($stateEnable!=""){echo $stateEnable;}else{echo $sttEnable;} ?> >
				<?php
					$query="SELECT ORD_STT_ID,ORD_STT_DESC,ORD_STT_ORD
							FROM CMP.PRN_DIG_STT $headerSelect ORDER BY 3";
					genCombo($query,"ORD_STT_ID","ORD_STT_DESC",$row["ORD_STT_ID"]);
				?>
			</select><br /><div class="combobox"></div>
		</div>
		<div style="float:left;width:140px;">
			<label>PPN</label><br /><div class='labelbox'></div>
			<select name="TAX_APL_ID" id="TAX_APL_ID" class="chosen-select" onchange="calcAmt()" <?php echo $stateEnable; ?> >
			<?php
				if($row["TAX_APL_ID"]==""){$TaxApl="E";}else{$TaxApl=$row["TAX_APL_ID"];}
				$query="SELECT TAX_APL_ID,TAX_APL_DESC
						FROM CMP.TAX_APL ORDER BY SORT";
				genCombo($query,"TAX_APL_ID","TAX_APL_DESC",$TaxApl);
			?>
			</select><br /><div class="combobox"></div>
		</div>
		<div>
			<label>Ditugaskan Kepada</label><br /><div class='labelbox'></div>
			<select name="SLS_PRSN_NBR" id="SLS_PRSN_NBR" class="chosen-select" style="width:273px" <?php echo $stateEnable; ?> >
			<?php
				if($row["SLS_PRSN_NBR"]==""){$SlsPrsnNbr="";}else{$SlsPrsnNbr=$row["SLS_PRSN_NBR"];}
				$querySls	= "SELECT CO_NBR_CMPST FROM NST.PARAM_PAYROLL WHERE CO_NBR = ".$CoNbrDef." ";
				$resultSls	= mysql_query($querySls);
				$rowSls		= mysql_fetch_array($resultSls);
				$CoNbrSls		= $rowSls['CO_NBR_CMPST'];
				
				$query="SELECT PRSN_NBR,NAME FROM CMP.PEOPLE WHERE CO_NBR IN (". $CoNbrSls .") AND TERM_DTE IS NULL";
				genCombo($query,"PRSN_NBR","NAME",$SlsPrsnNbr,"Corporate");
			?>
			</select><br /><div class="combobox"></div>
		</div>
			
		<div style="clear:both;"></div>
		
		<!-- listing -->
		<div id="edit-list" class="edit-list"></div>
		<script>getContent('edit-list','print-digital-edit-list.php?ORD_NBR=<?php echo $OrdNbr; ?>&TYP=<?php echo $type; ?>');</script>
		
		<!-- Footer -->
		<table style='padding:0px;margin-bottom:10px' id="payment">
			<tr><td style='padding:0px;width:380px'>
				<!-- payment -->
				<div class='total'>
					<table>
						<tr class='total'>
							<td style='padding-left:7px;width:200px'>
								Biaya Tambahan
							</td>
							<td style="text-align:right;width:150px">
								<input name="FEE_MISC" id="FEE_MISC" value="<?php echo $row['FEE_MISC']; ?>" onkeyup="calcAmt();" onchange="calcAmt();" type="text" style="margin:1px;width:100px;border:none;text-align:right" <?php echo $footerRead; ?> />
							</td>
							<td style='width:30px'>
							</td>
						</tr>
						<tr class='total'>
							<td style='padding-left:7px'>
								PPN
							</td>
							<td style="text-align:right">
								<input name="TAX_AMT" id="TAX_AMT" value="<?php echo $row['TAX_AMT']; ?>" type="text" style="margin:1px;width:100px;border:none;text-align:right" readonly />
							</td>
							<td>
							</td>
						</tr>
						<tr class='total'>
							<td style='font-weight:bold;color:#3464bc;padding-left:7px'>
								Total Nota
							</td>
							<td style="text-align:right">
								<input name="TOT_AMT" id="TOT_AMT" value="<?php echo $row['TOT_AMT']; ?>" type="text" style="margin:1px;width:100px;border:none;text-align:right" readonly />
							</td>
							<td>
							</td>
						</tr>
						
						<!-- Payment AJAX-->
						<td id="pay" colspan="3" style='padding:0px'></td>
						<script>getContent('pay','print-digital-payment-list.php?ORD_NBR=<?php echo $OrdNbr; ?>&TYP=<?php echo $type; ?>');</script>
						
						<tr class='total'>
							<td style='font-weight:bold;color:#3464bc;border:0px;padding-left:7px'>
								Sisa
							</td>
							<td style="text-align:right;border:0px">
								<input name="TOT_REM" id="TOT_REM" value="<?php echo $row['TOT_REM']; ?>" type="text" style="width:100px;border:none;text-align:right" readonly />	
							</td>
							<td style="border:0px">
                                <div class='listable-btn' style='margin-left:5px'><span class='fa fa-refresh listable-btn' onclick="calcAmt();" ></span></div>
							</td>
						</tr>
					</table>
				</div>
			</td>
			<td style='padding:0px;vertical-align:bottom'>
				<div style="float:left;width:140px;">
					<label>Tanggal Jadi</label>
					<?php 
						if($row['CMP_TS']==""){$CmpDte="";}else{$CmpDte=parseDate($row['CMP_TS']);}
					?>
					<input name="CMP_DTE" id="CMP_DTE" value="<?php echo $CmpDte; ?>" type="text" style="width:110px;" readonly />
				</div>

				<div style="float:left;width:140px;">
					<label>Waktu Jadi</label>
					<?php
						if($row['CMP_TS']==""){$CmpTme="";}else{$CmpTme=parseTime($row['CMP_TS']);}
					?>
					<input name="CMP_TME" id="CMP_TME" value="<?php echo $CmpTme; ?>" type="text" style="width:110px;" readonly />
				</div>
		
				<div style="clear:both"></div><br>

				<div style="float:left;width:140px;">
					<label>Tanggal Diambil</label>
					<?php 
						if($row['PU_TS']==""){$PUDte="";}else{$PUDte=parseDate($row['PU_TS']);}
					?>
					<input name="PU_DTE" id="PU_DTE" value="<?php echo $PUDte; ?>" type="text" style="width:110px;" readonly />
				</div>

				<div style="float:left;width:140px;">
					<label>Waktu Diambil</label>
					<?php
						if($row['PU_TS']==""){$PUTme="";}else{$PUTme=parseTime($row['PU_TS']);}
					?>
					<input name="PU_TME" id="PU_TME" value="<?php echo $PUTme; ?>" type="text" style="width:110px;" readonly />
				</div>

			</td></tr>
		</table>
						
		<div style="clear:both"></div>

		<!-- This can be removed in the future.
		<div style="float:left;width:690px;">
			<label>Catatan</label><br />
			<textarea name="SPC_NTE" style="width:690px;height:40px;"><?php echo $row['SPC_NTE']; ?></textarea>
		</div>
		-->
		<?php if ($_GET['STT']!="DEL") { ?>		
			<input class="process" type="submit" value="Simpan"/>
		<?php } ?>		

	</p>		
</form>
<table style="margin:0px;<?php if($type == "EST"){echo "display:none;";} ?>">
	<tr>
	<td style="padding:0px;border:0px;vertical-align:top">
		<div class="conv">
			<textarea id="CONV" style="width:270px;height:40px;" <?php if($row['ORD_NBR']==""){echo "disabled='disabled'";} ?> onkeyup="if(event.keyCode==13){document.getElementById('converse').click();this.value='';}"></textarea>
			<?php
				//Old special note backward compatibility effort.
				if($row['SPC_NTE']!=""){
					$alt="-alt";
				}
			?>
            <div class='listable-btn' style='vertical-align:top;margin-left:1px;margin-top:1px'><span class='fa fa-pencil listable-btn' style="<?php if($row['ORD_NBR']==""){echo "display:none";} ?>" onclick="getContent('conversation','print-digital-edit-conversation.php?CONV='+document.getElementById('CONV').value+'&CMPT_NBR=<?php echo $OrdNbr; ?>&ALT=<?php echo $alt; ?>');"></span></div>
			<?php
				if(($row['SPC_NTE']!="")&&($row['SPC_NTE']!="CONV_THRD")){
					echo "<div class='conv-item'>";
					echo $row['SPC_NTE']." &nbsp;<span class='fa fa-user'></span> ".shortName($row['NAME_CRT'])." &nbsp;<span class='fa fa-clock-o'></span> ";
					$time=strtotime($row['UPD_TS']);
					echo humanTiming($time)." yang lalu";
					echo "</div>";
				}
			?>
			<div id="conversation"></div>
			<script>getContent('conversation','print-digital-edit-conversation.php?CMPT_NBR=<?php echo $OrdNbr; ?>&ALT=<?php echo $alt; ?>');</script>
		</div>
	</td>
	<td style="padding:0px;border:0px;vertical-align:top">
		<div class="userLog" style="width:285px;"><?php echo $row['CRT_TS']." ".shortName($row['NAME_CRT'])." membuat<br />\n"; ?>
			<?php echo $row['UPD_TS']." ".shortName($row['NAME_UPD'])." ubah akhir<br />\n"; ?>
			<?php
				$query_jrn="SELECT ORD_STT_DESC,CRT_TS,NAME
						FROM CMP.JRN_PRN_DIG JRN INNER JOIN
						CMP.PRN_DIG_STT STT ON JRN.ORD_STT_ID=STT.ORD_STT_ID INNER JOIN
						CMP.PEOPLE PPL ON PPL.PRSN_NBR=CRT_NBR
						WHERE ORD_NBR=".$OrdNbr." ORDER BY CRT_TS";
				$result_jrn=mysql_query($query_jrn);
				while($row_jrn=mysql_fetch_array($result_jrn)){
					echo " ".$row_jrn['CRT_TS']." ".shortName($row_jrn['NAME'])." ".strtolower($row_jrn['ORD_STT_DESC'])."<br />\n";
				}
			?>
		</div>
		<?php if ($Security==0) { 
			$query_first	= "SELECT JCRDT.CRT_TS, JCRDT.CRT_NBR, PPL.NAME FROM CMP.JRN_CRDT JCRDT 
									LEFT JOIN CMP.PEOPLE PPL ON JCRDT.CRT_NBR=PPL.PRSN_NBR 
								WHERE JRN_CRDT_NBR=(SELECT MIN(JRN_CRDT_NBR) FROM CMP.JRN_CRDT WHERE ORD_NBR=".$OrdNbr.")";
			$result_first 	= mysql_query($query_first);
			$row_first 		= mysql_fetch_array($result_first);

			$query_last 	= "SELECT JCRDT.CRT_TS, JCRDT.CRT_NBR, PPL.NAME FROM CMP.JRN_CRDT JCRDT 
									LEFT JOIN CMP.PEOPLE PPL ON JCRDT.CRT_NBR=PPL.PRSN_NBR 
								WHERE JRN_CRDT_NBR=(SELECT MAX(JRN_CRDT_NBR) FROM CMP.JRN_CRDT WHERE ORD_NBR=".$OrdNbr.")";
			$result_last 	= mysql_query($query_last);
			$row_last 		= mysql_fetch_array($result_last);

			if (($row_first['NAME']!='')&&($row_last['NAME']!='')){
		?>
			<div class="userLog" style="width:285px;">
				<?php 
					echo $row_first['CRT_TS']." ".shortName($row_first['NAME'])." membuat<br />\n"; 
					echo $row_last['CRT_TS']." ".shortName($row_last['NAME'])." ubah akhir<br />\n";
				?>
			</div>
		<?php } } ?>
		<?php
			$query="SELECT PRN_DIG_DESC,SUM((ORD_Q)*COALESCE(PRN_WID,1)*COALESCE(PRN_LEN,1)) AS ORD_Q FROM ". $detailtable ." DET LEFT OUTER JOIN CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP WHERE ORD_NBR=".$OrdNbr."  AND DET.DEL_NBR=0 GROUP BY 1 ORDER BY 1 DESC";
			$result=mysql_query($query);
			while($row=mysql_fetch_array($result)){
				$process.=number_format($row['ORD_Q'],2,".",",")." ";
				if($row['PRN_DIG_DESC']==""){$process.="Lain-lain";}else{$process.=$row['PRN_DIG_DESC'];}
				$process.="<br />\n";
			}
			$query="SELECT PRN_DIG_EQP_DESC,SUM(JOB_LEN) AS JOB_LEN FROM ". $detailtable ." DET LEFT OUTER JOIN CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP LEFT OUTER JOIN CMP.PRN_DIG_EQP EQP ON	TYP.PRN_DIG_EQP=EQP.PRN_DIG_EQP WHERE ORD_NBR=".$OrdNbr." AND DET.DEL_NBR=0 GROUP BY 1 ORDER BY 1 DESC";
			//echo $query;
			$result=mysql_query($query);
			while($row=mysql_fetch_array($result)){
				$process.=secs_to_h($row['JOB_LEN']*60)." ";
				if($row['PRN_DIG_EQP_DESC']==""){$process.="Lain-lain";}else{$process.=$row['PRN_DIG_EQP_DESC'];}
				$process.="<br />\n";
			}
			if($process==""){$process="<br/><br/>";}
		?>
		<div class="processTime" style="width:285px;"><?php echo $process; ?></div>
	</td>
	</tr>
</table>
<p></p>
<?php 
if ($UpperSec<=0) { 
	$queryCount="SELECT COUNT(ORD_DET_NBR) AS CountDel FROM ". $detailtable ." WHERE ORD_NBR=".$OrdNbr." AND DEL_NBR<>0";
	$resultCount=mysql_query($queryCount);
	$rowCount=mysql_fetch_array($resultCount);
	if ($rowCount['CountDel']>0) {
		$style="";
	} else {
		$style="display:none;";
	}
?>
<div style="clear:both;"></div>
<form style="width:700px;<?php echo $style; ?>">
	<!-- listing -->
	<div id="edit-list-del" class="edit-list"></div>
	<script>getContent('edit-list-del','print-digital-edit-list.php?ORD_NBR=<?php echo $OrdNbr; ?>&TYP=<?php echo $type; ?>&SHOW=NO');</script>
</form>
<?php } ?>

<script type="text/javascript"  src="framework/database/jquery.min.js"></script>
<script src="framework/combobox/chosen.jquery.js" type="text/javascript"></script>
<script type="text/javascript">
	jQuery.noConflict();
	var config = {
			'.chosen-select'           : {},
			'.chosen-select-deselect'  : {allow_single_deselect:true},
			'.chosen-select-no-single' : {disable_search_threshold:10},
			'.chosen-select-no-results': {no_results_text:'Data tidak ketemu'},
			'.chosen-select-width'     : {width:"95%"}
   	}
	for (var selector in config) {
		jQuery(selector).chosen(config[selector]);
	}
</script>

<script type="text/javascript">     
jQuery(document).ready(function() {
		jQuery("textarea#CONV").keypress(function (e) {
            var code = (e.keyCode ? e.keyCode : e.which);
            if (code == 13) {
                getContent('conversation','print-digital-edit-conversation.php?CONV='+document.getElementById('CONV').value+'&CMPT_NBR=<?php echo $OrdNbr; ?>&ALT=<?php echo $alt; ?>');
            }
        });
	<?php 
		$query="SELECT TOT_REM
				FROM PRN_DIG_ORD_HEAD
				WHERE ORD_NBR=".$OrdNbr;
		$results=mysql_query($query);
		$rows=mysql_fetch_array($results);
		$totRem= $rows['TOT_REM'];
	?>
		var totRem = <?php echo ($totRem > 0) ? $totRem : 0;?>;
		$("#TND_AMT").keyup(function(){
			$("#amount-message").html();
			var maximumamount = 0;
			maximumamount = totRem;
				
			if ($('#TOT_REM').val() < 0) {
				$('#converse').hide();
                $('#TND_AMT').css("margin-right","4px");
			}else {
				$('#converse').show();
                $('#TND_AMT').css("margin-right","0px");
			}
		});
});
</script>
</body>
</html>
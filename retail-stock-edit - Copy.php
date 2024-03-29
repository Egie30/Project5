<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	include "framework/alert/alert.php";
	
	$Security=getSecurity($_SESSION['userID'],"Inventory");
	$UpperSec=getSecurity($_SESSION['userID'],"Executive");
	$Acc=getSecurity($_SESSION['userID'],"Accounting");
	$OrdNbr=$_GET['ORD_NBR'];
	$IvcTyp=$_GET['IVC_TYP'];
	$OrdDetNbrStr=$_GET['ORD_DET_NBR'];
		
	if ($_GET['CNV'] != "") {
		if ($_GET['CNV'] == "RC") {
			$query_nbr="SELECT COALESCE(MAX(ORD_NBR),0)+1 AS NEW_NBR FROM RTL.RTL_STK_HEAD";
			$result_nbr=mysql_query($query_nbr);
			$row_nbr=mysql_fetch_array($result_nbr);
			$OrdNbrNew=$row_nbr['NEW_NBR'];
			
			$query = "INSERT INTO RTL.RTL_STK_HEAD (ORD_NBR, ORD_DTE, RCV_CO_NBR, REF_NBR,
							SHP_CO_NBR,BIL_CO_NBR, IVC_TYP, FEE_MISC, DISC_PCT, DISC_AMT,
							TOT_AMT, PYMT_DOWN, PYMT_REM, TOT_REM, DL_TS, SPC_NTE, IVC_PRN_CNT, DEL_F,
							CRT_TS, CRT_NBR, UPD_TS, UPD_NBR,
							TAX_APL_ID, TAX_AMT, SLS_PRSN_NBR, SLS_TYP_ID)
							SELECT 
							'" . $OrdNbrNew . "', CURRENT_TIMESTAMP, SHP_CO_NBR, '',
							RCV_CO_NBR,BIL_CO_NBR, '" . $_GET['CNV'] . "', FEE_MISC, DISC_PCT, DISC_AMT,
							TOT_AMT, PYMT_DOWN, PYMT_REM, TOT_REM, CURRENT_TIMESTAMP AS DL_TS, SPC_NTE, 0, 0,
							CURRENT_TIMESTAMP, " . $_SESSION['personNBR'] . ", CURRENT_TIMESTAMP, " . $_SESSION['personNBR'] . ",
							TAX_APL_ID, TAX_AMT, SLS_PRSN_NBR, SLS_TYP_ID
							FROM RTL.RTL_STK_HEAD WHERE ORD_NBR=" . $OrdNbr . "
						";
			$result = mysql_query($query);
			
			$OrdDetNbrs=explode(',',$OrdDetNbrStr);
			foreach($OrdDetNbrs as $OrdDetNbr){
				$query = "INSERT INTO RTL.RTL_STK_DET (
							SELECT (SELECT COALESCE(MAX(ORD_DET_NBR),0)+1 AS ORD_DET_NBR FROM RTL.RTL_STK_DET) ORD_DET_NBR, " . $OrdNbrNew . ", 
							INV_NBR, INV_DESC, ORD_Q, ORD_X, ORD_Y, ORD_Z, INV_PRC, FEE_MISC, DISC_PCT, DISC_AMT, TOT_SUB, ORD_DET_NBR_REF, SER_NBR, NTE,
							CURRENT_TIMESTAMP, " . $_SESSION['personNBR'] . ", CURRENT_TIMESTAMP, " . $_SESSION['personNBR'] . "
							FROM RTL.RTL_STK_DET WHERE ORD_DET_NBR=" . $OrdDetNbr . "
						)";
				mysql_query($query);
			}
			$querytot="UPDATE RTL.RTL_STK_HEAD SET TOT_AMT=(SELECT SUM(TOT_SUB) FROM RTL.RTL_STK_DET DET WHERE ORD_NBR=".$OrdNbrNew."), TOT_REM=(
						SELECT SUM(TOT_SUB) FROM RTL.RTL_STK_DET DET WHERE ORD_NBR=".$OrdNbrNew.") WHERE ORD_NBR=".$OrdNbrNew;
	   		$resulttot=mysql_query($querytot);
			
			$IvcTyp = $_GET['CNV'];
			$OrdNbr = $OrdNbrNew;
			header('Location: retail-stock-edit.php?IVC_TYP='.$IvcTyp.'&ORD_NBR='.$OrdNbr);
		}
	}
	
	// Auto detect invoice type
	if ($IvcTyp == "" && $OrdNbr != "") {
		$query       = "SELECT IVC_TYP FROM RTL.RTL_STK_HEAD WHERE ORD_NBR=" . $OrdNbr;
		$result      = mysql_query($query);
		$row         = mysql_fetch_array($result);
		$IvcTyp = $row['IVC_TYP'];
	}
	
    $query="SELECT IVC_DESC FROM RTL.IVC_TYP WHERE IVC_TYP='$IvcTyp'";
    $result=mysql_query($query);
	$row=mysql_fetch_array($result);
    $IvcDesc=$row['IVC_DESC'];

	//Process changes here
	if($_POST['ORD_NBR']!="")
	{
		$OrdNbr=$_POST['ORD_NBR'];
		
		//Take care of nulls and timestamps
		if($_POST['RCV_CO_NBR']==""){$RcvCoNbr="NULL";}else{$RcvCoNbr=$_POST['RCV_CO_NBR'];}
		if($_POST['SHP_CO_NBR']==""){$ShpCoNbr="NULL";}else{$ShpCoNbr=$_POST['SHP_CO_NBR'];}
		if($_POST['BIL_CO_NBR']==""){$BilCoNbr="NULL";}else{$BilCoNbr=$_POST['BIL_CO_NBR'];}
		if($_POST['FEE_MISC']==""){$FeeMisc="NULL";}else{$FeeMisc=$_POST['FEE_MISC'];}
		if($_POST['TOT_AMT']==""){$TotAmt="NULL";}else{$TotAmt=$_POST['TOT_AMT'];}		
		if($_POST['PYMT_DOWN']==""){$PymtDown="NULL";}else{$PymtDown=$_POST['PYMT_DOWN'];}
		if($_POST['PYMT_REM']==""){$PymtRem="NULL";}else{$PymtRem=$_POST['PYMT_REM'];}
		if($_POST['TOT_REM']==""){$TotRem="NULL";}else{$TotRem=$_POST['TOT_REM'];}
		if($_POST['DL_DTE']==""){$DLTS="NULL";}else{$DLTS="'".$_POST['DL_DTE']." ".$_POST['DL_TME']."'";}
		if($_POST['TAX_AMT']==""){$TaxAmt="NULL";}else{$TaxAmt=$_POST['TAX_AMT'];}
		if($_POST['SLS_PRSN_NBR']==""){$SlsPrsnNbr="NULL";}else{$SlsPrsnNbr=$_POST['SLS_PRSN_NBR'];}
		
		//if($_POST['PYMT_DOWN_DTE']==""){$PymtDownTs="NULL";}else{$PymtDownTs="'".$_POST['PYMT_DOWN_DTE']." ".$_POST['PYMT_DOWN_TME']."'";}
		//if($_POST['PYMT_REM_DTE']==""){$PymtRemTs="NULL";}else{$PymtRemTs="'".$_POST['PYMT_REM_DTE']." ".$_POST['PYMT_REM_TME']."'";}
		
		if($_POST['CAT_SUB_NBR']==""){
			$CatNbr="NULL";
			$CatSubNbr="NULL";
		}else{
			$Cats=explode("-",$_POST['CAT_SUB_NBR']);
			$CatNbr=$Cats[0];
			$CatSubNbr=$Cats[1];
		}
		
		
		//Process add new
		if($OrdNbr==-1)
		{
			$query="SELECT COALESCE(MAX(ORD_NBR),0)+1 AS NEW_NBR FROM RTL.RTL_STK_HEAD";
			$result=mysql_query($query);
			$row=mysql_fetch_array($result);
			$OrdNbr=$row['NEW_NBR'];
			$query="INSERT INTO RTL.RTL_STK_HEAD (ORD_NBR) VALUES (".$OrdNbr.")";
			$result=mysql_query($query);
			$create="CRT_TS=CURRENT_TIMESTAMP,CRT_NBR=".$_SESSION['personNBR'].",";

		}
		
	   	//Process payment journal
	   	if($_POST['PYMT_DOWN']!=""){
	   		$query="SELECT PYMT_DOWN,PYMT_REM, DATE(PYMT_DOWN_TS) AS PYMT_DOWN_DTE FROM RTL.RTL_STK_HEAD WHERE ORD_NBR=$OrdNbr";
			$result=mysql_query($query);
			$row=mysql_fetch_array($result);
			if($row['PYMT_DOWN']!=$_POST['PYMT_DOWN']){
				$query="INSERT INTO RTL.JRN_CSH_FLO (DIV_ID,NM_TBL,ORD_NBR,CSH_FLO_TYP,CSH_AMT,CRT_TS,CRT_NBR)
						VALUES ('PRN','RTL_STK_HEAD',".$OrdNbr.",'DP',".$_POST['PYMT_DOWN'].",CURRENT_TIMESTAMP,".$_SESSION['personNBR'].")";
				$resultp=mysql_query($query);
				
				if (($_POST['PYMT_DOWN'] != "") && ($_POST['PYMT_DOWN'] > 0)) {
					$query_upd ="UPDATE RTL.RTL_STK_HEAD SET PYMT_DOWN_TS = CURRENT_TIMESTAMP WHERE ORD_NBR = ".$OrdNbr." ";
					$result_upd = mysql_query($query_upd);
				}
			}
			if($row['PYMT_DOWN_DTE']!=$_POST['PYMT_DOWN_DTE']){
				$PymtDownTs="'".$_POST['PYMT_DOWN_DTE']." ".$_POST['PYMT_DOWN_TME']."'";
				$query ="UPDATE RTL.RTL_STK_HEAD SET PYMT_DOWN_TS = ". $PymtDownTs ." WHERE ORD_NBR = ".$OrdNbr." ";
				echo "<br><br>".$query_upd;
				$result=mysql_query($query);
			}
		}
		
		
		if (($_POST['PYMT_DOWN'] == "") || ($_POST['PYMT_DOWN'] == 0)) {
			$query_upd ="UPDATE RTL.RTL_STK_HEAD SET PYMT_DOWN_TS = NULL WHERE ORD_NBR = ".$OrdNbr." ";
			$result_upd = mysql_query($query_upd);
		}
		
		
	   	if($_POST['PYMT_REM']!=""){
	   		$query="SELECT PYMT_DOWN,PYMT_REM, DATE(PYMT_REM_TS) AS PYMT_REM_DTE FROM RTL.RTL_STK_HEAD WHERE ORD_NBR=$OrdNbr";
			$result=mysql_query($query);
			$row=mysql_fetch_array($result);
			if($row['PYMT_REM']!=$_POST['PYMT_REM']){
				$query="INSERT INTO RTL.JRN_CSH_FLO (DIV_ID,NM_TBL,ORD_NBR,CSH_FLO_TYP,CSH_AMT,CRT_TS,CRT_NBR)
						VALUES ('PRN','RTL_STK_HEAD',".$OrdNbr.",'FL',".$_POST['PYMT_REM'].",CURRENT_TIMESTAMP,".$_SESSION['personNBR'].")";
				$resultp=mysql_query($query);
				
				if (($_POST['PYMT_REM'] != "") && ($_POST['PYMT_REM'] > 0)) {
					$query_upd ="UPDATE RTL.RTL_STK_HEAD SET PYMT_REM_TS = CURRENT_TIMESTAMP WHERE ORD_NBR = ".$OrdNbr." ";
					$result_upd = mysql_query($query_upd);
				}
			}
			
			if($row['PYMT_REM_DTE']!=$_POST['PYMT_REM_DTE']){
				$PymtRemTs="'".$_POST['PYMT_REM_DTE']." ".$_POST['PYMT_REM_TME']."'";
				$query_upd ="UPDATE RTL.RTL_STK_HEAD SET PYMT_REM_TS = ". $PymtRemTs ." WHERE ORD_NBR = ".$OrdNbr." ";
				echo "<br><br>".$query_upd;
				$result_upd = mysql_query($query_upd);
			}
		}
		
		if (($_POST['PYMT_REM'] == "") || ($_POST['PYMT_REM'] == 0)) {
			$query_upd ="UPDATE RTL.RTL_STK_HEAD SET PYMT_REM_TS = NULL WHERE ORD_NBR = ".$OrdNbr." ";
			$result_upd = mysql_query($query_upd);
		}
		
		
		$query = "UPDATE RTL.RTL_STK_HEAD
				SET ORD_DTE='".$_POST['ORD_DTE']."',
					RCV_CO_NBR=".$RcvCoNbr.",
					BIL_CO_NBR=".$BilCoNbr.",
					REF_NBR='".$_POST['REF_NBR']."',
					IVC_TYP='".$_POST['IVC_TYP']."',
					SHP_CO_NBR=".$_POST['SHP_CO_NBR'].",
					RCV_CO_NBR=".$_POST['RCV_CO_NBR'].",
					FEE_MISC=".$FeeMisc.",
					TOT_AMT=".$TotAmt.",
					PYMT_DOWN=".$PymtDown.",
					PYMT_REM=".$PymtRem.",
					#PYMT_DOWN_TS=".$PymtDownTs.",
					#PYMT_REM_TS=".$PymtRemTs.",
					TOT_REM=".$TotRem.",
					DL_TS=".$DLTS.",
					SPC_NTE='".$_POST['SPC_NTE']."',".$create."
					UPD_TS=CURRENT_TIMESTAMP,
					UPD_NBR=".$_SESSION['personNBR'].",
					TAX_APL_ID='".$_POST['TAX_APL_ID']."',
					TAX_AMT=".$TaxAmt.",
					SLS_PRSN_NBR=".$SlsPrsnNbr.",
					CAT_SUB_NBR='".$CatSubNbr."',
					ACTG_TYP='".$_POST['ACTG_TYP']."'
				WHERE ORD_NBR=".$OrdNbr;
		echo $query;
	   	$result=mysql_query($query);
	   	$IvcTyp=$_POST['IVC_TYP'];
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
<script type="text/javascript" src="framework/datepicker/js/mootools-1.2.4-core.js"></script>
<script type="text/javascript" src="framework/datepicker/js/mootools-1.2.4.4-more.js"></script>
<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.js"></script>

<script type="text/javascript" src="framework/functions/default.js"></script>

<link rel="stylesheet" href="framework/combobox/chosen.css">

<script type="text/javascript">
	//Get parameters
	var salesTax=getParam("tax","ppn");
	
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
				document.getElementById('TOT_SUB').value=getInt('TOT_NET');
				document.getElementById('TOT_REM').value=getInt('TOT_AMT')-getInt('PYMT_DOWN')-getInt('PYMT_REM');
				document.getElementById('TAX_AMT').value="";
				break;
			case "I" : 
				document.getElementById('TOT_AMT').value=getInt('TOT_NET')+getInt('FEE_MISC');
				document.getElementById('TOT_SUB').value=getInt('TOT_NET');
				document.getElementById('TOT_REM').value=getInt('TOT_AMT')-getInt('PYMT_DOWN')-getInt('PYMT_REM');
				document.getElementById('TAX_AMT').value=getInt('TOT_AMT')*parseFloat(getParam("tax","ppn"));
				break;
			case "A" : 
				document.getElementById('TOT_AMT').value=getInt('TOT_NET')+getInt('FEE_MISC');
				document.getElementById('TOT_SUB').value=getInt('TOT_NET');
				document.getElementById('TAX_AMT').value=getInt('TOT_AMT')*parseFloat(getParam("tax","ppn"));
				document.getElementById('TOT_AMT').value=getInt('TOT_NET')+getInt('FEE_MISC')+getInt('TAX_AMT');
				document.getElementById('TOT_REM').value=getInt('TOT_AMT')-getInt('PYMT_DOWN')-getInt('PYMT_REM');
				break;
		}
	}
	</script>

	<script>
	function calcAmtChild(TOT_NET){
		console.log(TOT_NET);
		switch (document.getElementById('TAX_APL_ID').value) {
			case "E" : 
				document.getElementById('TOT_AMT').value=parseInt(TOT_NET)+getInt('FEE_MISC');
				document.getElementById('TOT_REM').value=getInt('TOT_AMT')-getInt('PYMT_DOWN')-getInt('PYMT_REM');
				document.getElementById('TAX_AMT').value="";
				break;
			case "I" : 
				document.getElementById('TOT_AMT').value=parseInt(TOT_NET)+getInt('FEE_MISC');
				document.getElementById('TOT_REM').value=getInt('TOT_AMT')-getInt('PYMT_DOWN')-getInt('PYMT_REM');
				document.getElementById('TAX_AMT').value=getInt('TOT_AMT')*parseFloat(getParam("tax","ppn"));
				break;
			case "A" : 
				document.getElementById('TOT_AMT').value=parseInt(TOT_NET)+getInt('FEE_MISC');
				document.getElementById('TAX_AMT').value=getInt('TOT_AMT')*parseFloat(getParam("tax","ppn"));
				document.getElementById('TOT_AMT').value=parseInt(TOT_NET)+getInt('FEE_MISC')+getInt('TAX_AMT');
				document.getElementById('TOT_REM').value=getInt('TOT_AMT')-getInt('PYMT_DOWN')-getInt('PYMT_REM');
				break;
		}
	}
	</script>	

	<script>
	parent.document.getElementById('invoiceDeleteYes').onclick=
	function () { 
		parent.document.getElementById('content').src='retail-stock.php?IVC_TYP=<?php echo $IvcTyp; ?>&DEL=<?php echo $OrdNbr ?>';
		parent.document.getElementById('invoiceDelete').style.display='none';
		parent.document.getElementById('fade').style.display='none'; 
	};
	
	parent.parent.document.getElementById('convertCreateYes').onclick=
    function () {
        createConvert();
		parent.parent.document.getElementById('convertCreate').style.display='none';
		parent.parent.document.getElementById('fade').style.display='none'; 
	};

	parent.parent.document.getElementById('receiptCreateYes').onclick=
    function () {
        createReceipt();
		parent.parent.document.getElementById('receiptCreate').style.display='none';
		parent.parent.document.getElementById('fade').style.display='none'; 
	};
	</script>
	
	<script>
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
            window.scrollTo(0,0);parent.parent.document.getElementById('convertBlank').style.display='block';
            parent.parent.document.getElementById('fade').style.display='block';
        }else{
            window.scrollTo(0,0);parent.parent.document.getElementById('convertCreate').style.display='block';
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
       var $leftMenu = jQuery(parent.document.getElementById('leftmenu')).contents().find(".sub");
				
		$leftMenu.find("div").removeClass("leftmenusel").addClass("leftmenu");
		$leftMenu.find("#retail-<?php echo strtolower($_GET['CNV']);?>").removeClass("leftmenu").addClass("leftmenusel");
		parent.document.getElementById('content').src='retail-stock-edit.php?ORD_NBR=<?php echo $OrdNbr; ?>&IVC_TYP=<?php echo $IvcTyp;?>&CNV=RC&ORD_DET_NBR='+queryStr.substr(0,queryStr.length-1)+'';
	}

	function checkReceipt(){
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
            window.scrollTo(0,0);parent.parent.document.getElementById('receiptBlank').style.display='block';
            parent.parent.document.getElementById('fade').style.display='block';
        }else{
            window.scrollTo(0,0);parent.parent.document.getElementById('receiptCreate').style.display='block';
            parent.parent.document.getElementById('fade').style.display='block';
        }
    }

    function createReceipt(){
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
        parent.parent.document.getElementById('leftmenu').src='transport-lm.php?STT=RP';
        queryStr='signtake-tripane.php?ORD_NBR=<?php echo $OrdNbr; ?>&ORD_DET_NBR='+queryStr.substr(0,queryStr.length-1)+'&STT=RP&STTRTL=ADD&STT_TYP=RP';
        location.href=queryStr;
        //alert(queryStr);
    }
	</script>

<link rel="stylesheet" type="text/css" media="screen" href="framework/alert/alert.css" />
</head>
<body>

<div style="display:none;">
	<input id="refresh-list" type="button" value="Refresh" onclick="syncGetContent('edit-list','retail-stock-edit-list.php?ORD_NBR=<?php echo $OrdNbr.'&IVC_TYP='.$IvcTyp; ?>');" />
	<input id="refresh-tot" type="button" value="Total" onclick="calcAmt();" />
</div>

<?php
	$query = "SELECT HED.ORD_NBR AS ORD_NBR,
		HED.ORD_DTE AS ORD_DTE,
		HED.IVC_TYP AS IVC_TYP,
		HED.REF_NBR AS REF_NBR,
		HED.SHP_CO_NBR AS SHP_CO_NBR,
		HED.RCV_CO_NBR AS RCV_CO_NBR,
		HED.FEE_MISC AS FEE_MISC,
		HED.TOT_AMT AS TOT_AMT,
		HED.PYMT_DOWN AS PYMT_DOWN,
		HED.PYMT_REM AS PYMT_REM,
		HED.TOT_REM AS TOT_REM,
		DET.TOT_SUB AS TOT_SUB,
		HED.DL_TS AS DL_TS,
		HED.SPC_NTE AS SPC_NTE,
		HED.CRT_TS AS CRT_TS,
		HED.CRT_NBR AS CRT_NBR,
		HED.UPD_TS AS UPD_TS,
		HED.UPD_NBR AS UPD_NBR,
		HED.BIL_CO_NBR AS BIL_CO_NBR,
		HED.TAX_APL_ID AS TAX_APL_ID,
		HED.TAX_AMT AS TAX_AMT,
		HED.SLS_PRSN_NBR AS SLS_PRSN_NBR, 
		HED.CAT_SUB_NBR AS CAT_SUB_NBR, 
		HED.ACTG_TYP AS ACTG_TYP,
		PYMT_DOWN_TS,
		PYMT_REM_TS,
		DATEDIFF(CURRENT_DATE, HED.ORD_DTE) AS DIFF_DATE
	FROM RTL.RTL_STK_HEAD HED
	LEFT OUTER JOIN (
		SELECT SUM(TOT_SUB) AS TOT_SUB, DET.ORD_NBR FROM RTL.RTL_STK_DET DET WHERE DET.ORD_NBR = ".$OrdNbr."
	) DET ON DET.ORD_NBR = HED.ORD_NBR
	WHERE HED.ORD_NBR=".$OrdNbr;
	//echo "<pre>".$query;
	$result = mysql_query($query);
	$row 	= mysql_fetch_array($result);
?>

<?php if((($Security==0) || $UpperSec == 0) &&($OrdNbr!=0)) { ?>
	<div class="toolbar-only">
		<?php if ($UpperSec == 0){ ?>
        <p class="toolbar-left"><a href="javascript:void(0)" onclick="window.scrollTo(0,0);parent.document.getElementById('invoiceDelete').style.display='block';parent.document.getElementById('fade').style.display='block'"><span class='fa fa-trash toolbar' style="cursor:pointer"></span></a></p>
        <?php } ?>
        <?php if ($Security == 0){ ?>
		<p class="toolbar-right">
			<?php if ($IvcTyp == "PO") {?>
				<a href="javascript:void(0)" onClick="checkConvert();calcAmt();"><span class='fa fa-copy toolbar' style="cursor:pointer"></span></a>
			<?php } ?>
	<?php if ($IvcTyp == "PO" || $IvcTyp == "SL" || $IvcTyp == "RC") {?>
            <a href="retail-stock-edit-pdf.new.php?ORD_NBR=<?php echo $OrdNbr; ?>&IVC_TYP=<?php echo $IvcTyp; ?>&TYPE=PRINT"><span class='fa fa-file-powerpoint-o toolbar' style="cursor:pointer"></span></a>
			<a href="retail-stock-edit-pdf.new.php?ORD_NBR=<?php echo $OrdNbr; ?>&IVC_TYP=<?php echo $IvcTyp; ?>&TYPE=PDF"><span class='fa fa-file-pdf-o toolbar' style="cursor:pointer"></span></a>
	<?php } ?>
			<a href="retail-stock-edit-print-orddetnbr.php?ORD_NBR=<?php echo $OrdNbr; ?>"><span style="cursor:pointer" class="fa fa-tag toolbar" src="img/label.png"></span></a>
            <a href="javascript:void(0)" onclick="window.scrollTo(0,0);parent.document.getElementById('retailStockBarcodeWhiteContent').src='retail-stock-edit-print-lead.php?ORD_NBR=<?php echo $OrdNbr; ?>';parent.document.getElementById('retailStockBarcodeWhite').style.display='block';parent.document.getElementById('fade').style.display='block'"><span class='fa fa-barcode toolbar' style="cursor:pointer"></span></a>
            <a href="retail-stock-edit-print.php?ORD_NBR=<?php echo $OrdNbr; ?>&PRN_TYP=<?php echo $IvcTyp; ?>"><span class='fa fa-print toolbar' style="cursor:pointer"></span></a>
			<a href="javascript:void(0)" onClick="checkReceipt()">
				<span class='fa fa-gift toolbar' style='cursor:pointer'></span>
			</a>
		</p>
		<?php } ?>
	</div>
<?php } ?>	

<?php if(($IvcTyp == "RC") && ($Security==2)){ ?>
<div class="toolbar-only">
	<p class="toolbar-left"></p>
	<p class="toolbar-right">
		<a href="retail-stock-edit-pdf.new.php?ORD_NBR=<?php echo $OrdNbr; ?>&IVC_TYP=<?php echo $IvcTyp; ?>&TYPE=PRINT"><span class='fa fa-file-powerpoint-o toolbar' style="cursor:pointer"></span></a>
		<a href="retail-stock-edit-pdf.new.php?ORD_NBR=<?php echo $OrdNbr; ?>&IVC_TYP=<?php echo $IvcTyp; ?>&TYPE=PDF"><span class='fa fa-file-pdf-o toolbar' style="cursor:pointer"></span></a>
	</p>
</div>
<?php } ?>
			
<form enctype="multipart/form-data" action="#" method="post" style="width:700px" onSubmit="return checkform();">
	<p>
        <h3>
            Nota <?php echo $IvcDesc ?>
        </h3>
		<h2>
			<?php echo $row['ORD_NBR'];if($row['ORD_NBR']==""){echo "Baru";} ?>
		</h2>
		
		<!-- Header -->
		<div style="float:left;width:140px;">
			<input id="ORD_NBR" name="ORD_NBR" type="hidden" value="<?php echo $row['ORD_NBR'];if($row['ORD_NBR']==""){echo "-1";} ?>"/>
			<input id="IVC_TYP" name="IVC_TYP" type="hidden" value="<?php echo $row['IVC_TYP'];if($row['IVC_TYP']==""){echo $_GET['IVC_TYP'];} ?>"/>
			
			<label>Tanggal Diterima</label>
			<?php 
				if($row['DL_TS']==""){$DLDte="";}else{$DLDte=parseDate($row['DL_TS']);}
			?>
			<input name="DL_DTE" id="DL_DTE" value="<?php echo $DLDte; ?>" type="text" style="width:110px;" />
			<script>
				new CalendarEightysix('DL_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });			
			</script>
		</div>
		</div>
		<div>
			<label>Pengirim</label><br /><div class='labelbox'></div>
			<select name="SHP_CO_NBR" style="width:550px" class="chosen-select">
				<?php
					if($row['SHP_CO_NBR']==""){if($IvcTyp!="RC"){$ShpCoID=$CoNbrDef;}}else{$ShpCoID=$row['SHP_CO_NBR'];}
					$query="SELECT CO_NBR,CONCAT(NAME,' ',ADDRESS,' ',CITY_NM) AS CO_DESC
							FROM CMP.COMPANY COM INNER JOIN CMP.CITY CIT ON COM.CITY_ID=CIT.CITY_ID WHERE COM.APV_F=1 ORDER BY 2";
					genCombo($query,"CO_NBR","CO_DESC",$ShpCoID,"");
				?>
			</select><br /><div class="combobox"></div>
		</div>
		
		<div style="clear:both"></div>
		
		<div style="float:left;width:140px;">
			<label>Waktu Diterima</label>
			<?php
				if($row['DL_TS']==""){$DLTme=date("G:i:s");}else{$DLTme=parseTime($row['DL_TS']);}
			?>
			<input name="DL_TME" id="DL_TME" value="<?php echo $DLTme; ?>" type="text" style="width:110px;" readonly />
			<div class='listable-btn'><span class='fa fa-clock-o listable-btn' style='font-size:14px' onclick="document.getElementById('DL_TME').value=getCurTime();"></span></div>
		</div>	

		<div>
			<label>Penerima</label><br /><div class='labelbox'></div>
			<select name="RCV_CO_NBR" style="width:550px" class="chosen-select">
				<?php
					if($row['RCV_CO_NBR']==""){if($IvcTyp=="RC"){$RcvCoID=$CoNbrDef;}}else{$RcvCoID=$row['RCV_CO_NBR'];}
					$query="SELECT CO_NBR,CONCAT(NAME,' ',ADDRESS,' ',CITY_NM) AS CO_DESC
							FROM CMP.COMPANY COM INNER JOIN CMP.CITY CIT ON COM.CITY_ID=CIT.CITY_ID WHERE COM.APV_F=1 ORDER BY 2";
					genCombo($query,"CO_NBR","CO_DESC",$RcvCoID,"");
				?>
			</select><br /><div class="combobox"></div>
		</div>

		<div style="clear:both"></div>

		<div style="float:left;width:140px;">
			<input id="ORD_NBR" name="ORD_NBR" type="hidden" value="<?php echo $row['ORD_NBR'];if($row['ORD_NBR']==""){echo "-1";} ?>"/>
			
			<label>Tanggal Nota</label>
			<?php 
				if($row['ORD_DTE']==""){$OrdDte="";}else{$OrdDte=parseDate($row['ORD_DTE']);}			?>
			<input name="ORD_DTE" id="ORD_DTE" value="<?php echo $OrdDte; ?>" type="text" style="width:110px;" />
			<script>
				new CalendarEightysix('ORD_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });			
			</script>
		</div>

		<?php if($IvcTyp == 'PO'){ ?>
		<div>
		<label >Pembayar</label>
            <select name="BIL_CO_NBR" class="chosen-select" style="width:550px" <?php echo $headerEnable; ?> >
				<?php
					$query="SELECT CO_NBR,CONCAT(NAME,' ',ADDRESS,' ',CITY_NM) AS CO_DESC
							FROM CMP.COMPANY COM INNER JOIN CMP.CITY CIT ON COM.CITY_ID=CIT.CITY_ID 
							WHERE COM.DEL_NBR=0 
							ORDER BY 2";
					genCombo($query,"CO_NBR","CO_DESC",$row['BIL_CO_NBR'],"Sama dengan diatas");
				?>
			</select><br /><div class="combobox"></div>
		</div>
		<?php } else {?>

		<?php } ?>
	
		<div style="float:left;width:140px;">
			<label>No. Referensi</label><br />
			<input name="REF_NBR" id="REF_NBR" value="<?php echo $row['REF_NBR']; ?>" type="text" style="width:90px;" />
		</div>
		
		<?php if($IvcTyp == "SL"){ $width = 130; }else{ $width = 140; } ?>
		<div style="float:left;width:<?php echo $width; ?>px;">
		<label>PPN</label><br /><div class='labelbox'></div>
		<select name="TAX_APL_ID" id="TAX_APL_ID"  class="chosen-select" onchange="calcAmt()" <?php echo $stateEnable; ?> >
		<?php
			if($row["TAX_APL_ID"]==""){$TaxApl="E";}else{$TaxApl=$row["TAX_APL_ID"];}
			$query="SELECT TAX_APL_ID,TAX_APL_DESC
					FROM CMP.TAX_APL ORDER BY SORT";
			genCombo($query,"TAX_APL_ID","TAX_APL_DESC",$TaxApl);
		?>
		</select><br /><div class="combobox"></div>
		
		
		</div>
		
		<?php 
		if($IvcTyp == "SL"){ 
			$widthDiv 	= 160; 
			$widthCombo = 150;
		}else{ 
			$widthDiv 	= 210; 
			$widthCombo = 170;
		} 
		?>
		<div style="float:left;width:<?php echo $widthDiv; ?>px;">
		<label>Kategori</label><br /><div class='labelbox'></div>
		<select name='CAT_SUB_NBR' id='CAT_SUB_NBR' class='chosen-select' style="width:<?php echo $widthCombo; ?>px">
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
		</div>
		
		<?php if($IvcTyp=="RC"){ ?>
		<div <?php if (($Acc == 0) && ($locked == 0) && ($_COOKIE["LOCK"] != "LOCK")) { echo "style=''"; } else { echo "style='display:none;'"; } ?> >	
		<div style="float:left;width:50px;">
				<label>Rekening</label><br /><div class='labelbox'></div>
				<select name="ACTG_TYP" id="ACTG_TYP" class="chosen-select" style="width:60px" <?php echo $stateEnable; ?> >
					<option value="">Pilih</option>
					<option value="1" <?php echo ($row['ACTG_TYP'] == '1') ? "selected" : ""; ?> >1</option>
					<option value="2" <?php echo ($row['ACTG_TYP'] == '2') ? "selected" : ""; ?> >2</option>
					<option value="3" <?php echo ($row['ACTG_TYP'] == '3') ? "selected" : ""; ?> >3</option>
				</select><br /><div class="combobox"></div>
		</div>
		</div>
		<?php } ?>

		<?php 
		if($IvcTyp=="SL"){ 
			if($IvcTyp == "SL"){ 
				$width = 150; 
			}else{ 
				$width = 270; 
			}
		?>
			<div>
				<label>Sales</label><br />
				<select name="SLS_PRSN_NBR" id="SLS_PRSN_NBR" class="chosen-select" style="width:<?php echo $width; ?>px" <?php echo $stateEnable; ?> >
				<?php
					if($row["SLS_PRSN_NBR"]==""){$SlsPrsnNbr="";}else{$SlsPrsnNbr=$row["SLS_PRSN_NBR"];}
					$query="SELECT PRSN_NBR,NAME FROM CMP.PEOPLE WHERE CO_NBR=271 AND TERM_DTE IS NULL";
					genCombo($query,"PRSN_NBR","NAME",$SlsPrsnNbr,"Corporate");
				?>
				</select><br />
			</div>	
		<?php } ?>		
		<div style="clear:both"></div>
				
		<!-- listing -->
		<div id="edit-list" class="edit-list"></div>
		<script>getContent('edit-list','retail-stock-edit-list.php?ORD_NBR=<?php echo $OrdNbr.'&IVC_TYP='.$IvcTyp; ?>');</script>
		
			<!-- Footer -->
		<table style='padding:0px;margin-bottom:10px' id="payment" border=0>
			<tr>
			<td style='padding:0px;width:380px'>
				<div class='total'>
				<table>
					<tr class='total'>
							<td style='padding-left:7px;width:200px'>
								Subtotal
							</td>
							<td style="text-align:right">
								<input name="TOT_SUB" id="TOT_SUB" value="<?php echo $row['TOT_SUB']; ?>" type="text" style="margin:1px;width:100px;border:none;text-align:right" readonly />
							</td>
							<td>
							</td>
					</tr>

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

					<tr class='total'>
							<td style='font-weight:bold;color:#3464bc;padding-left:7px'>
								Uang Muka
							</td>
							<td style="text-align:right">
								<input name="PYMT_DOWN" id="PYMT_DOWN" value="<?php echo $row['PYMT_DOWN']; ?>" type="text" style="margin:1px;width:100px;border:none;text-align:right" onkeyup="calcAmt();" onchange="calcAmt();"/>
							</td>
							<td>
							</td>
					</tr>

					<tr class='total'>
							<td style='font-weight:bold;color:#3464bc;padding-left:7px'>
								Pelunasan
							</td>
							<td style="text-align:right">
								<input name="PYMT_REM" id="PYMT_REM" value="<?php echo $row['PYMT_REM']; ?>" type="text" style="margin:1px;width:100px;border:none;text-align:right" onkeyup="calcAmt();" onchange="calcAmt();"/>
							</td>
							<td>
							</td>
					</tr>
							
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
			<td style='padding:0px;vertical-align:bottom;<?php if($Acc != 0){echo "display:none;";} ?>'>
				<div style="float:left;width:140px;">
					<label>Tanggal Uang Muka</label>
					<?php 
						if($row['PYMT_DOWN_TS']==""){$DownDte="";}else{$DownDte=parseDate($row['PYMT_DOWN_TS']);}
					?>
					<input name="PYMT_DOWN_DTE" id="PYMT_DOWN_DTE" value="<?php echo $DownDte; ?>" type="text" style="width:110px;" readonly />
					<script>
						new CalendarEightysix('PYMT_DOWN_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true, 'prefill': false});			
					</script>
				</div>

				<div style="float:left;width:140px;">
					<label>Waktu Uang Muka</label>
					<?php
						if($row['PYMT_DOWN_TS']==""){$DownTme="";}else{$DownTme=parseTime($row['PYMT_DOWN_TS']);}
					?>
					<input name="PYMT_DOWN_TME" id="PYMT_DOWN_TME" value="<?php echo $DownTme; ?>" type="text" style="width:110px;" readonly />
				</div>
		
				<div style="clear:both"></div><br>
				
				<div style="float:left;width:140px;">
					<label>Tanggal Pelunasan </label>
					<?php 
						if($row['PYMT_REM_TS']==""){$RemDte="";}else{$RemDte=parseDate($row['PYMT_REM_TS']);}
					?>
					<input name="PYMT_REM_DTE" id="PYMT_REM_DTE" value="<?php echo $RemDte; ?>" type="text" style="width:110px;" />
					<script>
						new CalendarEightysix('PYMT_REM_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true, 'prefill': false});			
					</script>
				</div>

				<div style="float:left;width:140px;">
					<label>Waktu Pelunasan</label>
					<?php
						if($row['PYMT_REM_TS']==""){$PUTme="";}else{$PUTme=parseTime($row['PYMT_REM_TS']);}
					?>
					<input name="PYMT_REM_TME" id="PYMT_REM_TME" value="<?php echo $PUTme; ?>" type="text" style="width:110px;" readonly />
					<script>
						new CalendarEightysix('PU_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true, 'prefill': false});			
					</script>
				</div>
			</td>
			</tr>
		</table>

	<table style="margin:0px;<?php if($type == "EST"){echo "display:none;";} ?>">
		<tr>
			<td style="padding:0px;border:0px;vertical-align:top;">
				<div class="conv">
					<textarea name="SPC_NTE" style="width:270px;height:40px;"><?php echo $row['SPC_NTE']; ?></textarea>
				</div>
			</td>
		</tr>

		<div style="clear:both"></div>
		<?php //echo $row['DIFF_DATE']."-".$row['TOT_REM']; ?>
		<?php 
			if( $_GET['IVC_TYP'] == 'RC' && $row['TOT_REM'] == 0 && $row['DIFF_DATE'] > 60 && $Acc != 0){ 
				$status = 'disabled style="display:none;"';
			}
		?>
		<input class="process" type="submit" value="Simpan" <?php echo $status; ?>/>
		<!--
		<?php 
		if( $_GET['IVC_TYP'] == 'RC'){
			if($row['TOT_REM'] != 0 || ($row['DIFF_DATE'] <= 60 || $Acc == 0)){
		?>
			<input class="process" type="submit" value="Simpan" />
		<?php }}else{ ?>
			<input class="process" type="submit" value="Simpan" />
		<?php } ?>
		-->
	</table>	
		
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
		   	}
			for (var selector in config) {
				jQuery(selector).chosen(config[selector]);
			}
			<?php if ($_GET['IVC_TYP'] != "") {?>			
				var $leftMenu = jQuery(parent.document.getElementById('leftmenu')).contents().find(".sub");
				
				$leftMenu.find("div").removeClass("leftmenusel").addClass("leftmenu");
				$leftMenu.find("#retail-<?php echo strtolower($_GET['IVC_TYP']);?>").removeClass("leftmenu").addClass("leftmenusel");
			<?php }?>
		</script>
	</p>		
</form>
<div></div>				
</body>
</html>
<?php error_reporting(0);
	include "framework/database/connect-cloud.php";
	include "framework/functions/dotmatrix.php";
	include_once "framework/functions/default.php";
	include "framework/security/default.php";

	date_default_timezone_set('Asia/Jakarta');

	$query      = "SELECT PLAFOND_DEF_PPL,PAY_TERM_DEF FROM NST.PARAM_LOC";
    	$result     = mysql_query($query);
    	$row        = mysql_fetch_array($result);
    	$PlafondDef = $row['PLAFOND_DEF_PPL'];
    	$PayTermDef = $row['PAY_TERM_DEF'];

	$PrsnNbr 		= $_GET['PRSN_NBR'];
	$Security 		= getSecurity($_SESSION['userID'],"AddressBook");
	$upperSecurity 	= getSecurity($_SESSION['userID'],"Executive");
	$SalesSec 		= getSecurity($_SESSION['userID'],"Sales");

	$PrnDigSec 		= getSecurity($_SESSION['userID'],"DigitalPrint");
	$CashSec 		= getSecurity($_SESSION['userID'],"Finance");
	$Acc 			= getSecurity($_SESSION['userID'],"Accounting");

	//get information schema CMP.PEOPLE
	$query_info		= "SELECT TABLE_NAME, COLUMN_NAME, COLUMN_KEY FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'CMP' AND TABLE_NAME ='PEOPLE'";
	$result_info 	= mysql_query($query_info);

	$array_info		= array();
	while ($row_info = mysql_fetch_array($result_info)){
		if ($row_info['COLUMN_KEY']=="PRI") { $PK = $row_info['COLUMN_NAME']; }
		array_push($array_info,$row_info['COLUMN_NAME']);
	}
	 
	//get information schema PAY.PEOPLE
	$query_pay_info	= "SELECT TABLE_NAME, COLUMN_NAME, COLUMN_KEY FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='PAY' AND TABLE_NAME='PEOPLE'";
	$result_pay_info= mysql_query($query_pay_info);
	
	$array_pay_info	= array();
	while ($row_pay_info = mysql_fetch_array($result_pay_info)){
		if ($row_pay_info['COLUMN_KEY']=="PRI") { $PK_PAY = $row_pay_info['COLUMN_NAME']; }
		array_push($array_pay_info,$row_pay_info['COLUMN_NAME']);
	}

	//get data awal CMP.PEOPLE
	$query_awal	= "SELECT * FROM CMP.PEOPLE WHERE PRSN_NBR='$PrsnNbr'";
	$result_awal= mysql_query($query_awal);
	$row_awal	= mysql_fetch_assoc($result_awal);

	//get data awal PAY.PEOPLE
	$query_pay_awal	= "SELECT * FROM PAY.PEOPLE WHERE PRSN_NBR='$PrsnNbr'";
	$result_pay_awal= mysql_query($query_pay_awal);
	$row_pay_awal	= mysql_fetch_assoc($result_pay_awal);

	foreach($xml->{'business-unit'} as $BusUnit){
		$CoNbrAll.=trim($BusUnit->{'company-number'}).",";
	}
	$CoNbrAll=substr($CoNbrAll,0,-1);

	//Process changes here
	if(($_POST['PRSN_NBR']!="")&&($cloud!=false)){
		$j=syncTable("PEOPLE","PRSN_NBR","CMP",$CMP,$local,$cloud);

		$PrsnNbr=$_POST['PRSN_NBR'];
		
		//Take care of nulls
		if($_POST['MBR_EXP_DTE']==""){$MbrExpDte="NULL";}else{$MbrExpDte="'".$_POST['MBR_EXP_DTE']."'";}
		if($_POST['DOB']==""){$DOB="NULL";}else{$DOB="'".$_POST['DOB']."'";}
		if($_POST['HIRE_DTE']==""){$HireDte="NULL";}else{$HireDte="'".$_POST['HIRE_DTE']."'";}
		if($_POST['TERM_DTE']==""){$TermDte="NULL";}else{$TermDte="'".$_POST['TERM_DTE']."'";}		
		if($_POST['WORK_TM']==""){$WorkTm="NULL";}else{$WorkTm="'".$_POST['WORK_TM']."'";}
		if($_POST['PAY_BASE']==""){$PayBase="NULL";}else{$PayBase=$_POST['PAY_BASE'];}
		if($_POST['PAY_ADD']==""){$PayAdd="NULL";}else{$PayAdd=$_POST['PAY_ADD'];}
		if($_POST['PAY_OT']==""){$PayOT="NULL";}else{$PayOT=$_POST['PAY_OT'];}
		if($_POST['PAY_CONTRB']==""){$PayContrb="NULL";}else{$PayContrb=$_POST['PAY_CONTRB'];}
		if($_POST['PAY_MISC']==""){$PayMisc="NULL";}else{$PayMisc=$_POST['PAY_MISC'];}
		if($_POST['HLD_AMT']==""){$HoldAmt="NULL";}else{$HoldAmt=$_POST['HLD_AMT'];}
		if($_POST['DED_DEF']==""){$DedDef="NULL";}else{$DedDef=$_POST['DED_DEF'];}
		if($_POST['BONUS']==""){$Bonus="NULL";}else{$Bonus=$_POST['BONUS'];}
		if($_POST['CO_NBR']==""){$CoNbr="NULL";}else{$CoNbr=$_POST['CO_NBR'];}
		if($_POST['CO_NBR_PAY']==""){$CoNbrPay="NULL";}else{$CoNbrPay=$_POST['CO_NBR_PAY'];}
		if($_POST['BNK_CO_NBR']==""){$BnkCoNbr="NULL";}else{$BnkCoNbr=$_POST['BNK_CO_NBR'];}
		if($_POST['CAP_LIM']==""){$CapLim="NULL";}else{$CapLim=$_POST['CAP_LIM'];}
		if($_POST['CAP_MULT']==""){$CapMult="NULL";}else{$CapMult=$_POST['CAP_MULT'];}
		if($_POST['BON_MULT']==""){$BonMult="NULL";}else{$BonMult=$_POST['BON_MULT'];}
		if($_POST['BON_ERNG']==""){$BonErng="NULL";}else{$BonErng=$_POST['BON_ERNG'];}
        	if($_POST['PWD']==""){$pwd="";}else{$pwd=hash('sha512',$_POST['PWD']);}
		if($_POST['SLACK_USER_NM']==""){$SlackUsername="";}else{$SlackUsername=$_POST['SLACK_USER_NM'];}
		if($_POST['CRDT_MAX']==""){$CrdtMax=$PlafondDef;}else{$CrdtMax=$_POST['CRDT_MAX'];}
		if($_POST['PAY_TERM_PPL']==""){$PayTerm=$PayTermDef;}else{$PayTerm=$_POST['PAY_TERM_PPL'];}
		if($_POST['APV_F']=="on"){$ApvF=1;}else{$ApvF=0;}
		if($_POST['EMPL_CNTRCT']==""){$EmplCntrct="0";}else{$EmplCntrct=$_POST['EMPL_CNTRCT'];}
		
		if($_POST['INS_F']=="on"){$insuranceF=1;}else{$insuranceF=0;}
		if($_POST['INS_VAL']==""){$insuranceVal="0";}else{$insuranceVal=$_POST['INS_VAL'];}
		if($_POST['SS_CRD_F']=="on"){$socialSecF=1;}else{$socialSecF=0;}
		if($_POST['SS_CRD_VAL']==""){$socialSecVal="0";}else{$socialSecVal=$_POST['SS_CRD_VAL'];}
		if($_POST['CNBTN_F']=="on"){$contributionF=1;}else{$contributionF=0;}
		if($_POST['CNBTN_VAL']==""){$contributionVal="NULL";}else{$contributionVal=$_POST['CNBTN_VAL'];}
		
		//Process add new
		if($PrsnNbr==-1)
		{
			$query 	= "SELECT MAX(PRSN_NBR)+1 AS NEW_NBR FROM $CMP.PEOPLE";
			$result = mysql_query($query,$cloud);
			$row 	= mysql_fetch_array($result);
			$PrsnNbr=$row['NEW_NBR'];

			$query 	= "INSERT INTO $CMP.PEOPLE (PRSN_NBR) VALUES (".$PrsnNbr.")";
			$result = mysql_query($query,$cloud);
			$query 	= str_replace($CMP,"CMP",$query);
			$result = mysql_query($query,$local);

			$query_pay 	= "INSERT INTO $PAY.PEOPLE (PRSN_NBR) VALUES (".$PrsnNbr.")";
			$result_pay = mysql_query($query_pay,$cloud);
			$query_pay 	= str_replace($PAY,"PAY",$query_pay);
			$result_pay = mysql_query($query_pay,$local);
		}
		
		$query 	= "SELECT PRSN_NBR, PWD FROM $CMP.PEOPLE WHERE PRSN_NBR = ".$PrsnNbr;
		$result	= mysql_query($query, $local);
		$row	= mysql_fetch_array($result);
		
		//Update CMP.PEOPLE
		$query="UPDATE $CMP.PEOPLE
	   			SET PRSN_ID='".$_POST['PRSN_ID']."',
	   				NAME='".mysql_real_escape_string($_POST['NAME'])."',
	   				ALIAS='".$_POST['ALIAS']."',
					SLACK_USER_NM='".$SlackUsername."',
	   				KEYWORDS='".$_POST['KEYWORDS']."',
	   				TTL='".$_POST['TTL']."',
	   				MBR_NBR='".$_POST['MBR_NBR']."',
					MBR_EXP_DTE=".$MbrExpDte.",
					DOB=".$DOB.",
					ADDRESS='".$_POST['ADDRESS']."',
					CITY_ID='".$_POST['CITY_ID']."',
					ZIP='".$_POST['ZIP']."',
					PHONE='".$_POST['PHONE']."',
					FAX='".$_POST['FAX']."',
					EMAIL='".$_POST['EMAIL']."',
					HIRE_DTE=".$HireDte.",";
		if($_POST['PWD'] != str_repeat('.',10)){ $query.="PWD='".$pwd."',"; }
		if($_POST['POS_TYP']!=""){$query.="POS_TYP='".$_POST['POS_TYP']."',";}
		if($_POST['BRKR_PLAN_TYP']!=""){$query.="BRKR_PLAN_TYP='".$_POST['BRKR_PLAN_TYP']."',";}
		if($_POST['MGR_NBR']!=""){$query.="MGR_NBR=".$_POST['MGR_NBR'].",";}
		$query.="DRV_LIC='".$_POST['DRV_LIC']."',
					RSN_CRD='".$_POST['RSN_CRD']."',
					GNDR='".$_POST['GNDR']."',
					TERM_DTE=".$TermDte.",
					CO_NBR=".$CoNbr.",
					CO_NBR_PAY=".$CoNbrPay.",
	   				BNK_CO_NBR=".$BnkCoNbr.",
	   				BNK_ACCT_NBR='".$_POST['BNK_ACCT_NBR']."',
					TAX_NBR='".$_POST['TAX_NBR']."',
	   				CAP_LIM=".$CapLim.",
	   				CAP_MULT=".$CapMult.",
					CRDT_MAX='".$CrdtMax."',
					PAY_TERM_PPL='".$PayTerm."',
					APV_F='".$ApvF."',
	   				EMPL_CNTRCT=".$EmplCntrct.",
					UPD_TS=CURRENT_TIMESTAMP,
					UPD_NBR=".$_SESSION['personNBR']."
					WHERE PRSN_NBR=".$PrsnNbr;
		//if($_SESSION['personNBR'] == 3){echo $query."<br><br>";}
	   	$result=mysql_query($query,$cloud);
		$query=str_replace($CMP,"CMP",$query);
		$result=mysql_query($query,$local);
		
		//Update PAY.PEOPLE
		$query="UPDATE $PAY.PEOPLE
	   			SET PAY_TYP='".$_POST['PAY_TYP']."',
					PAY_BASE=".$PayBase.",
					PAY_ADD=".$PayAdd.",
					PAY_OT=".$PayOT.",
					PAY_CONTRB=".$PayContrb.",
					PAY_MISC=".$PayMisc.",
					DED_DEF=".$DedDef.",
					BONUS=".$Bonus.",
					BON_MULT=".$BonMult.",
					BON_ERNG=".$BonErng.",
					WORK_TM=".$WorkTm.",
					HLD_AMT=".$HoldAmt.",
					CNBTN_F=".$contributionF.",
					CNBTN_VAL=".$contributionVal.",
					INS_F=".$insuranceF.",
					INS_VAL=".$insuranceVal.",
					SS_CRD_F=".$socialSecF.",
					SS_CRD_VAL=".$socialSecVal.",
					UPD_TS=CURRENT_TIMESTAMP,
					UPD_NBR=".$_SESSION['personNBR']."
					WHERE PRSN_NBR=".$PrsnNbr;
		//if($_SESSION['personNBR'] == 3){echo $query;}
		$result=mysql_query($query,$cloud);
		$query=str_replace($PAY,"PAY",$query);
		$result=mysql_query($query,$local);

		//get data akhir CMP.PEOPLE
		$query_akhir	= "SELECT * FROM CMP.PEOPLE WHERE PRSN_NBR='$PrsnNbr'";
		$result_akhir	= mysql_query($query_akhir);
		$row_akhir		= mysql_fetch_assoc($result_akhir);

		//insert jurnal CMP.PEOPLE
		for ($i=0;$i<count($array_info);$i++){
			if ($row_awal[$array_info[$i]]!=$row_akhir[$array_info[$i]]) {
				$query_jrn	= "INSERT INTO $CMP.JRN_LIST (JRN_LIST_NBR, DB_NM, TBL_NM, COL_NM, PK, PK_DTA, REC_BEG, REC_END, CRT_TS, CRT_NBR) VALUES 
								('','".$CMP."','PEOPLE','".$array_info[$i]."','$PK','$PrsnNbr','".$row_awal[$array_info[$i]]."','".$row_akhir[$array_info[$i]]."','".date('Y-m-d H:i:s')."','".$_SESSION['personNBR']."')";
				mysql_query($query_jrn,$cloud);
				$query_jrn=str_replace($CMP,"CMP",$query_jrn);
				mysql_query($query_jrn,$local);
			}
		}
		
		//get data akhir PAY.PEOPLE
		$query_pay_akhir	= "SELECT * FROM PAY.PEOPLE WHERE PRSN_NBR='$PrsnNbr'";
		$result_pay_akhir	= mysql_query($query_pay_akhir);
		$row_pay_akhir		= mysql_fetch_assoc($result_pay_akhir);

		//insert jurnal PAY.PEOPLE
		for ($ii=0;$ii<count($array_pay_info);$ii++){
			if ($row_pay_awal[$array_pay_info[$ii]]!=$row_pay_akhir[$array_pay_info[$ii]]) {
				$query_pay_jrn	= "INSERT INTO $PAY.JRN_LIST (JRN_LIST_NBR, DB_NM, TBL_NM, COL_NM, PK, PK_DTA, REC_BEG, REC_END, CRT_TS, CRT_NBR) VALUES 
								('','".$PAY."','PEOPLE','".$array_pay_info[$ii]."','$PK_PAY','$PrsnNbr','".$row_pay_awal[$array_pay_info[$ii]]."','".$row_pay_akhir[$array_pay_info[$ii]]."','".date('Y-m-d H:i:s')."','".$_SESSION['personNBR']."')";
				mysql_query($query_pay_jrn,$cloud);
				$query_pay_jrn=str_replace($PAY,"PAY",$query_pay_jrn);
				mysql_query($query_pay_jrn,$local);
			}
		}
	}

	//Process graphics
	if(is_uploaded_file($_FILES['PICTURE']['tmp_name'])){
         //move_uploaded_file($_FILES['PICTURE']['tmp_name'],"address-person/".$PrsnNbr.".jpg");
         move_uploaded_file($_FILES['PICTURE']['tmp_name'],"address-person\\".$PrsnNbr.".jpg");
	}
	//Process delete graphics
	if($_POST['DEL_IMG']=="on"){
		//if(file_exists("address-person/".$PrsnNbr.".jpg")){
		//	unlink("address-person/".$PrsnNbr.".jpg");
		if(file_exists("address-person\\".$PrsnNbr.".jpg")){
			unlink("address-person\\".$PrsnNbr.".jpg");
		}
	}

	$query="SELECT 
				PPL.PRSN_NBR,
				PRSN_ID,
				PPL.NAME,
				ALIAS,
				SLACK_USER_NM,
				KEYWORDS,
				TTL,
				MBR_NBR,
				MBR_EXP_DTE,
				DOB,
				ADDRESS,
				PPL.CITY_ID,
				ZIP,
				PHONE,
				FAX,
				EMAIL,
				HIRE_DTE,
				PPAY.PAY_TYP,
				PPAY.PAY_BASE,
				PPAY.PAY_ADD,
				PPAY.PAY_OT,
				PPAY.PAY_CONTRB,
				PPAY.PAY_MISC,
				PPAY.DED_DEF,
				PPAY.HLD_AMT,
				PPAY.HLD_F,
				PWD,
				POS_TYP,
				MGR_NBR,
				DRV_LIC,
				RSN_CRD,
				GNDR,
				TERM_DTE,
				PPAY.WORK_TM,
				PPAY.BONUS,
				CO_NBR,
				CO_NBR_PAY,
				BRKR_PLAN_TYP,
				BNK_CO_NBR,
				TAX_NBR,
				BNK_ACCT_NBR,
				CAP_LIM,
				CAP_MULT,
				PPAY.BON_MULT,
				PPAY.BON_ERNG,
				APV_F,
				PAY_TERM_PPL,
				CRDT_MAX,
				EMPL_CNTRCT,
				PPAY.CNBTN_F,
				PPAY.CNBTN_VAL,
				PPAY.INS_F,
				PPAY.INS_VAL,
				PPAY.SS_CRD_F,
				PPAY.SS_CRD_VAL,
				PPL.UPD_TS,
				PPL.UPD_NBR,
				UPD.NAME_UPD
			FROM CMP.PEOPLE PPL
			LEFT JOIN PAY.PEOPLE PPAY ON PPL.PRSN_NBR=PPAY.PRSN_NBR
			LEFT OUTER JOIN CMP.CITY CTY ON PPL.CITY_ID=CTY.CITY_ID
			LEFT JOIN (SELECT NAME AS NAME_UPD, PRSN_NBR FROM CMP.PEOPLE WHERE DEL_NBR=0) UPD ON PPL.UPD_NBR=UPD.PRSN_NBR
			WHERE PPL.PRSN_NBR=".$PrsnNbr;
	//echo $query;
	$result=mysql_query($query,$local);
	$row=mysql_fetch_array($result);
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

<script type="text/javascript">jQuery.noConflict()</script>
<link rel="stylesheet" href="framework/combobox/chosen.css">


<script>
	window.addEvent('domready',function(){
		$('PRSN_ID').addEvent('keyup',function(){
			var input_value=this.value;
			if(input_value.length>1){
				new Request.JSON({
					url:"framework/validation/validation.php?form=add&prs=<?php echo $row['PRSN_ID'];?>",
					onSuccess:function(response){

						if(response.action=='success'){
							$('PRSN_ID').removeClass('error');
							$('PRSN_ID').addClass('success');
							$('response').set('html','');
							$('submit_button').disabled=false;
							$('submit_button').removeClass('disabled');
							$('submit_button').addClass('blue');
						}else{
							$('PRSN_ID').removeClass('success');
							$('PRSN_ID').addClass('error');
							$('response').set('html','&nbsp;&nbsp;<span class="fa fa-warning" style="font-size:14px;"></span> Username <b>'+response.PRSN_ID+'</b> sudah digunakan');
							$('submit_button').disabled=true;
							$('submit_button').removeClass('blue');
							$('submit_button').addClass('disabled');
						}
					}
				}).get($('signup'));
			}

			$('PRSN_ID').addEvent('blur',function(e){
				if(this.value==''){			
					$('PRSN_ID').removeClass('success');
					$('PRSN_ID').removeClass('error');
					$('response').set('html','');
					/*
					$('submit_button').disabled=true;
			    	$('submit_button').removeClass('blue');
			    	$('submit_button').addClass('disabled');
				*/
				}
			});		
		});
	});
</script>

<script> 
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
	function checkform()
	{
		if(document.getElementById('NAME').value=="")
		{
			window.scrollTo(0,0);
			parent.document.getElementById('addressBlank').style.display='block';parent.document.getElementById('fade').style.display='block';
			return false;
		}

		return true;
	}
</script>

<script type="text/javascript">
	$(document).ready(function(){
		<?php 
			//if($j>0){echo "parent.msgGrowl('$j record telah di sinkronisasi.');";} //Sample jGrowl
		?>
	});
</script>

</head>

<body>

<script>
	parent.document.getElementById('addressDeleteYes').onclick=
	function () { 
		parent.document.getElementById('content').src='address-person.php?DEL_A=<?php echo $PrsnNbr ?>';
		parent.document.getElementById('addressDelete').style.display='none';
		parent.document.getElementById('fade').style.display='none'; 
	};
</script>


<?php if(($Security==0)&&($PrsnNbr!=0)) { ?>
	<div class="toolbar-only">
		<p class="toolbar-left"><?php if(($cloud!=false)&&(paramCloud()==1)){ ?><a href="javascript:void(0)" onclick="window.scrollTo(0,0);parent.document.getElementById('addressDelete').style.display='block';parent.document.getElementById('fade').style.display='block'"><span class='fa fa-trash toolbar' style="cursor:pointer"></span></a><?php } ?></p>
	</div>
<?php } ?>
			
<form enctype="multipart/form-data" action="#" method="post" style="width:700px" onSubmit="return checkform();" id="signup" autocomplete="off">
	<p>
		<h2>
			<?php
				if((!$cloud)&&($row['PRSN_NBR']=="")){
					echo "<script>window.scrollTo(0,0);parent.document.getElementById('offline').style.display='block';parent.document.getElementById('fade').style.display='block';</script>";
				}
				echo $row['NAME'];if($row['NAME']==""){echo "Nama Baru";}
			?>
		</h2>
		<h3>
			Nomor Induk: <?php echo $row['PRSN_NBR'];if($row['PRSN_NBR']==""){echo "Baru";} ?>
		</h3>
		<img style='width:108px;height:108px;border-radius:50% 50% 50% 50%;margin-bottom:5px'
		<?php
			if(file_exists("address-person\\".$PrsnNbr.".jpg")){
				list($width,$height)=getimagesize("address-person\\".$PrsnNbr.".jpg");
				echo "onClick=".chr(34)."window.open('address-person/".$PrsnNbr.".jpg','Kotak Foto','width=".$width.",height=".$height."')".chr(34);
			}
		?> 
		src="address-person/showimg.php?PRSN_NBR=<?php echo $row['PRSN_NBR']; ?>"><br />
		<?php
			$imgName=$PrsnNbr.".jpg";
			//if(file_exists("address-person/".$imgName)){
			if(file_exists("address-person\\".$imgName)){
				echo "<div class='labelbox'></div><input name='DEL_IMG' id='DEL_IMG' type='checkbox' class='regular-checkbox'/>&nbsp;<label for='DEL_IMG'></label><label class='checkbox' for='DEL_IMG' style='cursor:pointer'>Hapus foto</label>";
			}else{
				echo "<div class='browse' onclick='document.getElementById(".chr(34)."PICTURE".chr(34).").click();'>Browse ...<input class='browse' name='PICTURE' id='PICTURE' type='file'></input></div><br />";
				echo "</span>Format gambar harus berbentuk jpeg ukuran bujur sangkar dan diakhiri dengan ekstensi .jpg</span>";
			}
		?><br /><br />
		<img src='framework/barcode/retail-barcode.php?STRING=<?php echo LeadZero(Luhn($row['PRSN_NBR']),8);?>' style='width:5.2cm;height:1cm'><br /><br />
	
		<input name="PRSN_NBR" value="<?php echo $row['PRSN_NBR'];if($row['PRSN_NBR']==""){echo "-1";} ?>" type="hidden" />
			<?php
			if($SalesSec<=9){
				
				if ( ($row['CO_NBR'] != '') &&  ($row['PRSN_NBR'] != '') ) {
				$query="SELECT PRSN_NBR,NAME,TTL,EMAIL,PHONE FROM CMP.PEOPLE WHERE CO_NBR=".$row['CO_NBR']." AND PRSN_NBR!=".$row['PRSN_NBR']." AND DEL_NBR=0 AND CO_NBR NOT IN (".$CoNbrAll.") ORDER BY UPD_TS DESC";
								
				$resultp=mysql_query($query);
				$rows=mysql_num_rows($resultp);
								
				if($rows>0){
					echo "<h3>Peer</h3><table>";
					$alt="class='alt'";
					while($rowp=mysql_fetch_array($resultp)){
						echo "<tr $alt onclick=".chr(34)."location.href='address-person-edit.php?PRSN_NBR=".$rowp['PRSN_NBR']."';".chr(34).">";
						echo "<td style='text-align:right;cursor:pointer'>".$rowp['PRSN_NBR']."</td>";
						echo "<td style='cursor:pointer'>".$rowp['NAME']."</td>";
						echo "<td style='cursor:pointer'>".$rowp['TTL']."</td>";
						echo "<td style='cursor:pointer'>".$rowp['EMAIL']."</td>";
						echo "<td style='cursor:pointer'>".$rowp['PHONE']."</td>";
						echo "</tr>";
						if($alt==""){$alt="class='alt'";}else{$alt="";}
					}
				echo "</table><br/>";
				}
				
				}
			}
		?>
		
	<h3>Details</h3>
	<label class='side'>Identifikasi</label>
		<input name="PRSN_ID" value="<?php echo $row['PRSN_ID']; ?>" type="text" size="30" id="PRSN_ID" autocomplete="off"/>&nbsp;<span id="response"></span><br />
		<label class='side'>Kata Sandi</label>
		<input name="PWD" value="<?php if($row['PWD']!=""){echo str_repeat('.',10); } ?>" type="password" size="30" autocomplete="off"/><br />
		<label class='side'>Nama</label>
		<input id="NAME" name="NAME" value="<?php echo $row['NAME']; ?>" type="text" size="30" />
		<?php
			if(($_POST['NAME']=="")&&($row['NAME']=="")){echo "&nbsp;&nbsp;<span class='fa fa-warning' style='font-size:14px'></span> Nama tidak boleh kosong";}
		?><br />
		<label class='side'>Alias</label>
		<input id="ALIAS" name="ALIAS" value="<?php echo $row['ALIAS']; ?>" type="text" size="30" /><br />
		<label class='side'>Slack Username</label>
		<input id="SLACK_USER_NM" name="SLACK_USER_NM" value="<?php echo $row['SLACK_USER_NM']; ?>" type="text" size="30" /><br />
		<label class='side'>Hashtag</label>
		<input id="KEYWORDS" name="KEYWORDS" value="<?php echo $row['KEYWORDS']; ?>" type="text" size="70" /><br />
		<label class='side'>Posisi</label>
		<input id="TTL" name="TTL" value="<?php echo $row['TTL']; ?>" type="text" size="40" /><br />
		<label class='side'>Nomor Member</label>
		<input id="MBR_NBR" name="MBR_NBR" value="<?php echo $row['MBR_NBR']; ?>" type="text" size="40" /><br />
		<label class='side'>Tanggal Kadaluarsa Member</label>
		<input id="MBR_EXP_DTE" name="MBR_EXP_DTE" value="<?php echo $row['MBR_EXP_DTE']; ?>" type="text" size="30" /><br />
		<script>
			new CalendarEightysix('MBR_EXP_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });
		</script>
		<label class='side'>Tanggal Lahir</label>
		<input id="DOB" name="DOB" value="<?php echo $row['DOB']; ?>" type="text" size="30" /><br />
		<script>
			new CalendarEightysix('DOB', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });
		</script>
		<label class='side'>Alamat</label>
		<input name="ADDRESS" value="<?php echo $row['ADDRESS']; ?>" type="text" size="70" /><br />
		<label class='side'>Kota</label>
		<div class='side'><select name="CITY_ID" class="chosen-select">
		<?php
			$query="SELECT CITY_ID, CONCAT(CITY_DESC,' ',CITY_NM)AS CITY_NM
					FROM CMP.CITY CTY
					INNER JOIN CMP.CITY_TYP TYP ON CTY.CITY_TYP=TYP.CITY_TYP
					WHERE CITY_ID = 'YOGY'
					UNION ALL
					(SELECT CITY_ID, CONCAT(CITY_DESC,' ',CITY_NM) AS CITY_NM
					FROM CMP.CITY CTY
					INNER JOIN CMP.CITY_TYP TYP ON CTY.CITY_TYP=TYP.CITY_TYP
					WHERE CITY_ID!= 'YOGY' AND PROV_ID='DIY')
					UNION ALL
					(SELECT CITY_ID, CONCAT(CITY_DESC,' ',CITY_NM) AS CITY_NM
					FROM CMP.CITY CTY
					INNER JOIN CMP.CITY_TYP TYP ON CTY.CITY_TYP=TYP.CITY_TYP
					WHERE CITY_ID!= 'YOGY' AND PROV_ID!='DIY')";
			genCombo($query,"CITY_ID","CITY_NM",$row['CITY_ID'],"",$local);
		?>
		</select></div><div class="labelbox"></div>
		<label class='side'>Kode Pos</label>
		<input name="ZIP" value="<?php echo $row['ZIP']; ?>" type="text" size="30" /><br />
		<label class='side'>Nomor Telepon</label>
		<input name="PHONE" value="<?php echo $row['PHONE']; ?>" type="text" size="30" /><br />
		<label class='side'>Nomor Fax</label>
		<input name="FAX" value="<?php echo $row['FAX']; ?>" type="text" size="30" /><br />
		<label class='side'>Alamat E-Mail</label>
		<input name="EMAIL" value="<?php echo $row['EMAIL']; ?>" type="text" size="30" /><br />
		<label class='side'>Nomor SIM</label>
		<input name="DRV_LIC" value="<?php echo $row['DRV_LIC']; ?>" type="text" size="30" /><br />
		<label class='side'>Nomor KTP</label>
		<input name="RSN_CRD" value="<?php echo $row['RSN_CRD']; ?>" type="text" size="30" /><br />
		<label class='side'>Jenis Kelamin</label>
		<div class='side'><select name="GNDR" class="chosen-select">
			<option value="M" <?php if($row['GNDR']=="M"){echo "selected";} ?>>Laki-laki</option>
			<option value="F" <?php if($row['GNDR']=="F"){echo "selected";} ?>>Perempuan</option>
		</select></div><div class="labelbox"></div>
		<label class='side'>Perusahaan Bersangkutan</label>
		<div class='side'><select name="CO_NBR" style='width:500px' class="chosen-select">
		<?php
			$query="SELECT CO_NBR,CONCAT(NAME,' ',ADDRESS,' ',CITY_NM) AS CO_DESC
					FROM CMP.COMPANY COM
				INNER JOIN CMP.CITY CIT ON COM.CITY_ID=CIT.CITY_ID 
				WHERE COM.DEL_NBR = 0
				ORDER BY 2";
			genCombo($query,"CO_NBR","CO_DESC",$row['CO_NBR'],"Kosong",$local);
		?>
		</select></div><div class="labelbox"></div>
		<span<?php if($Security>=2) {echo " style='display:none'";} else { if(($Security==1)&&($row['PAY_TYP']=='MON')) {echo " style='display:none'";} } ?>>
		<label class='side'>Perusahaan Payroll</label>
		<div class='side'><select name="CO_NBR_PAY" style='width:500px' class="chosen-select">
		<?php
			$query="SELECT CO_NBR,CONCAT(NAME,' ',ADDRESS,' ',CITY_NM) AS CO_DESC
					FROM CMP.COMPANY COM
				INNER JOIN CMP.CITY CIT ON COM.CITY_ID=CIT.CITY_ID 
				WHERE COM.DEL_NBR = 0 AND CO_NBR IN (SELECT CO_NBR FROM NST.PARAM_COMPANY WHERE CO_CD_NBR IS NOT NULL)
				ORDER BY 2";
			genCombo($query,"CO_NBR","CO_DESC",$row['CO_NBR_PAY'],"Kosong",$local);
		?>
		</select></div><div class="labelbox"></div>
		</span>
		<label class='side'>Tanggal Masuk</label>
		<input id="HIRE_DTE" name="HIRE_DTE" value="<?php echo $row['HIRE_DTE']; ?>" type="text" size="30" /><br />
		<script>
			new CalendarEightysix('HIRE_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });
		</script>
		<label  class='side'>Tanggal Keluar</label>
		<input id="TERM_DTE" name="TERM_DTE" value="<?php echo $row['TERM_DTE']; ?>" type="text" size="30" /><br />
		<script>
			new CalendarEightysix('TERM_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });			
		</script>

		<label class='side'>Jumlah Jam Kerja</label>
		<input name="WORK_TM" value="<?php echo $row['WORK_TM']; ?>" type="text" size="30" /><br />
		<!-- Hidden if necessary -->
		<span <?php if(($Security>=1)&&(($row['POS_TYP']=='OWN')||($row['POS_TYP']=='SYS')||($row['POS_TYP']=='FIN'))) { echo " style='display:none'"; } ?>>
		<label class='side'>Jabatan</label>
		<div class='side'><select name="POS_TYP" <?php if($Security>=2) {echo " disabled";} ?> class="chosen-select">
		<?php
			if($Security>=1)
			{
				$query="SELECT POS_TYP,POS_DESC FROM CMP.POS_TYP WHERE POS_TYP NOT IN ('OWN','SYS','FIN') ORDER BY 2";
			#filter for Administrative Executive
			}else if ($Security == 0 && $upperSecurity == 4) {
		        $query = "SELECT POS_TYP,POS_DESC FROM CMP.POS_TYP WHERE SUBSTR(SEC_KEY,8,1) >= 6 ORDER BY 2";
            }else{
				$query="SELECT POS_TYP,POS_DESC FROM CMP.POS_TYP ORDER BY 2";
			}
			genCombo($query,"POS_TYP","POS_DESC",$row['POS_TYP'],"Kosong",$local);
		?>
		</select></div><div class="labelbox"></div>
		<span <?php if ($upperSecurity>6){echo " style='display:none'";}?>>
		<label class='side'>Jenis Kontrak</label>
		<div class='side'><select name="EMPL_CNTRCT" class="chosen-select" style="width: 15%;">
		<?php
			$query="SELECT EMPL_CNTRCT_TYP,EMPL_CNTRCT_DESC FROM CMP.EMPL_CNTRCT_TYP";
			genCombo($query,"EMPL_CNTRCT_TYP","EMPL_CNTRCT_DESC",$row['EMPL_CNTRCT'],"Kosong",$local);
		?>
		</select></div><div class="labelbox"></div>
		</span>
		<label class='side'>Atasan</label>
		<input <?php if($upperSecurity>=6) {echo " readonly";} ?> id="MGR_NBR" name="MGR_NBR" value="<?php echo $row['MGR_NBR']; ?>" type="text" size="10" /><br />

		<label  class='side'>Jenis Broker</label>
		<div class='side'><select name="BRKR_PLAN_TYP" class="chosen-select">
		<?php
			$query="SELECT PLAN_TYP, PLAN_DESC FROM CMP.PRN_DIG_BRKR_PLAN_TYP";
			genCombo($query,"PLAN_TYP","PLAN_DESC",$row['BRKR_PLAN_TYP'],"Bukan Broker",$local);
		?>
		</select></div><div class="labelbox"></div>
		
		<span <?php if ($upperSecurity>6){echo " style='display:none'";}?>>
		<label class='side'>BPJS Kesehatan</label>
		<input name='INS_F' id='INS_F' type='checkbox' class='regular-checkbox' <?php if ($row['INS_F']=='1'){ echo "checked"; } ?> />
		<label for="INS_F"></label>
		<input name="INS_VAL" value="<?php echo $row['INS_VAL']; ?>" type="text" size="26" /><br />
		
		<label class='side'>BPJS Ketenagakerjaan</label>
		<input name='SS_CRD_F' id='SS_CRD_F' type='checkbox' class='regular-checkbox' <?php if ($row['SS_CRD_F']=='1'){ echo "checked"; } ?> />
		<label for="SS_CRD_F"></label>
		<input name="SS_CRD_VAL" value="<?php echo $row['SS_CRD_VAL']; ?>" type="text" size="26" /><br />
		
		<label class='side'>Reksadana</label>
		<input name='CNBTN_F' id='CNBTN_F' type='checkbox' class='regular-checkbox' <?php if ($row['CNBTN_F']=='1'){ echo "checked"; } ?> />
		<label for="CNBTN_F"></label>
		<input name="CNBTN_VAL" value="<?php echo $row['CNBTN_VAL']; ?>" type="text" size="26" /><br />
		</span>
		
		</span>
		
		<?php
		/*
		$queryHed   = "SELECT MGR_NBR FROM CMP.PEOPLE WHERE PRSN_NBR = ". $row['MGR_NBR'];
		//echo $queryHed;
		$resultHed  = mysql_query($queryHed);
		$rowHed = mysql_fetch_array($resultHed);
		
		if(($Security < 1) || (($_SESSION['personNBR'] == $row['MGR_NBR'] || $_SESSION['personNBR'] == $rowHed['MGR_NBR']) && $_SESSION['personNBR'] && $row['PRSN_NBR'])){
			$display = "";
		}else{
			$display = " style='display:none'";
		}
		*/
		?>
		
		<?php
		$arrayPrsn 	= array();
		$wherePrsn 	= "";

		function queryPPL($id){
			$resultPPL	= mysql_query("
			SELECT PRSN_NBR FROM CMP.PEOPLE WHERE MGR_NBR = '" . $id ."' AND DEL_NBR=0 AND TERM_DTE IS NULL AND PRSN_NBR > 0
			UNION
			SELECT DET.PRSN_NBR
			FROM CMP.PEOPLE MGR
			LEFT OUTER JOIN(
				SELECT PRSN_NBR, MGR_NBR FROM CMP.PEOPLE WHERE DEL_NBR=0 AND TERM_DTE IS NULL AND MGR_NBR IS NOT NULL GROUP BY PRSN_NBR
			)DET ON MGR.PRSN_NBR = DET.MGR_NBR
			WHERE MGR.MGR_NBR = '" . $id ."' AND DEL_NBR=0 AND TERM_DTE IS NULL  AND DET.PRSN_NBR > 0
			");
			return $resultPPL;
		}

		function show_prsn($id) {
			global $arrayPrsn, $wherePrsn;
			$list_prsn = queryPPL($id);
			if (mysql_num_rows($list_prsn)>0) {
				while($prsn = mysql_fetch_assoc($list_prsn)){
					array_push($arrayPrsn,$prsn['PRSN_NBR']);
					$wherePrsn .= $prsn['PRSN_NBR'].',';
					show_prsn($prsn['PRSN_NBR']);
				}
			}
		}

		show_prsn($_SESSION['personNBR']);
		$wherePrsn .= $_SESSION['personNBR'];
		
		$Person = explode(',',$wherePrsn);
		
		if($Security>=2) {
			$style= " style='display:none'";
			$cek = "A";
		} else {
			if(($Security==1)&&($row['PAY_TYP']=='MON')) {
				$style= " style='display:none'";
				$cek = "B";
			} 
		}
		
		//if ((in_array($PrsnNbr, $Person) && ($Security == 0 || ($Security == 1 && $upperSecurity <= 4))) || ($_SESSION['personNBR'] == $row['PRSN_NBR'])) {
		if (in_array($PrsnNbr, $Person) && ($Security == 0 || ($Security == 1 && $upperSecurity <= 4))) {
			$style= "";
			$cek = "C";
		}
		
		if($_SESSION['personNBR'] == $row['PRSN_NBR']){
			$styleBox = "readonly";
		}
		//echo $Security."==".$cek."==".$upperSecurity."==".$style."==";
		?>
		<!-- Hidden if necessary -->
		<span <?php echo $style; ?>>
		<label class='side'>Jenis Gaji</label>
		<div class='side'><select name="PAY_TYP" class="chosen-select">
		<?php
			$query="SELECT PAY_TYP,PAY_DESC FROM CMP.PAY_TYP";
			genCombo($query,"PAY_TYP","PAY_DESC",$row['PAY_TYP'],"Kosong",$local);
		?>
		</select></div><div class="labelbox"></div>
		<label class='side'>Gaji Pokok</label>
		<input name="PAY_BASE" value="<?php echo $row['PAY_BASE']; ?>" type="text" size="30" <?php echo $styleBox; ?>/><br />
		<label class='side'>Gaji Tambahan</label>
		<input name="PAY_ADD" value="<?php echo $row['PAY_ADD']; ?>" type="text" size="30" <?php echo $styleBox; ?>/><br />
		<label class='side'>Gaji Lembur</label>
		<input name="PAY_OT" value="<?php echo $row['PAY_OT']; ?>" type="text" size="30" <?php echo $styleBox; ?>/><br />
		<label class='side'>Gaji Kontribusi</label>
		<input name="PAY_CONTRB" value="<?php echo $row['PAY_CONTRB']; ?>" type="text" size="30" <?php echo $styleBox; ?>/><br />
		<label class='side'>Gaji Lain</label>
		<input name="PAY_MISC" value="<?php echo $row['PAY_MISC']; ?>" type="text" size="30" <?php echo $styleBox; ?>/><br />
		<label class='side'>Gaji Ditahan</label>
		<input name="HLD_AMT" value="<?php echo $row['HLD_AMT']; ?>" type="text" size="30" <?php echo $styleBox; ?>/><br />
		<label class='side'>Status Gaji Ditahan</label>
		<input name='HLD_F' id='HLD_F' type='checkbox' class='regular-checkbox' disabled
		<?php if ($row['HLD_F']=='1'){ echo "checked"; } ?> />
		<label for="HLD_F"></label><div class="labelbox" style="height:10px;"></div>
		<label class='side'>Potongan Standard</label>
		<input name="DED_DEF" value="<?php echo $row['DED_DEF']; ?>" type="text" size="30" <?php echo $styleBox; ?>/><br />
		<label class='side'>Bonus</label>
		<input name="BONUS" value="<?php echo $row['BONUS']; ?>" type="text" size="30" <?php echo $styleBox; ?>/><br />	
		<label class='side'>Bonus Multiplier</label>
		<input name="BON_MULT" value="<?php echo $row['BON_MULT']; ?>" type="text" size="10" <?php echo $styleBox; ?>/><br />
		<label class='side'>Earning Bonus</label>
		<input name="BON_ERNG" value="<?php echo $row['BON_ERNG']; ?>" type="text" size="10" <?php echo $styleBox; ?>/><br />		
		</span>
		<label class='side'>NPWP</label>
		<input name="TAX_NBR" value="<?php echo $row['TAX_NBR']; ?>" type="text" size="40" /><br />
		<label class='side'>Nomor Rekening</label>
		<input name="BNK_ACCT_NBR" value="<?php echo $row['BNK_ACCT_NBR']; ?>" type="text" size="40" /><br />
		<label class='side'>Nama Bank</label>
		<div class='side'><select name="BNK_CO_NBR" style="width:440px" class="chosen-select">
		<?php
			$query="SELECT CO_NBR,CONCAT(NAME,' ',ADDRESS,' ',CITY_NM) AS CO_DESC
					FROM CMP.COMPANY COM INNER JOIN
					CMP.CITY CIT ON COM.CITY_ID=CIT.CITY_ID WHERE BUS_TYP='BNK' ORDER BY 2";
			genCombo($query,"CO_NBR","CO_DESC",$row['BNK_CO_NBR'],"Kosong",$local);
		?>
		</select></div><div class="labelbox"></div>
		<label class='side'>Limit CAP Per Bulan</label>
		<input name="CAP_LIM" value="<?php echo $row['CAP_LIM']; ?>" type="text" size="10" /><br />
		<label class='side'>Besar CAP Per Unit</label>
		<input name="CAP_MULT" value="<?php echo $row['CAP_MULT']; ?>" type="text" size="10" /><br />
		<div <?php if(($PrnDigSec<2 && $upperSecurity<7 && $CashSec<3 && $Acc<8) && ($CashSec<>1 || $upperSecurity<1)){ echo ""; } else { echo " style='display:none;'"; } ?>>
		<label class='side'>Status Approval</label>
		<input name='APV_F' id='APV_F' type='checkbox' class='regular-checkbox'
		<?php if ($row['APV_F']=='1'){ echo "checked"; } ?> />
		<label for="APV_F"></label><div class="labelbox" style="height:10px;"></div>
		<label class='side'>Plafon Kredit</label>
		<input name="CRDT_MAX" value="<?php echo $row['CRDT_MAX']; ?>" type="text" size="10"/><br />
		<label class='side'>Tempo Pembayaran (Hari)</label>
		<input name="PAY_TERM_PPL" value="<?php echo $row['PAY_TERM_PPL']; ?>" type="text" size="10"/><br />
		</div>
		<?php
			if(($cloud!=false)&&(paramCloud()==1)){
				if((($row['PWD']!="")&&($row['PRSN_ID']==$_SESSION['userID']))||(($Security<3)&&($upperSecurity<6))||($row['PWD']=="")){
					echo "<input  id='submit_button'  class='process submit_button' type='submit' value='Simpan' />";
				}
			}
		?>
		<div class="userLog" style="margin-left: 0px;width: 400px;">
			<?php echo $row['UPD_TS']." ".shortName($row['NAME_UPD'])." ubah akhir<br />\n"; ?>
			<?php
				$query_log	= "SELECT JRN.*, PPL.NAME 
								FROM CMP.JRN_LIST JRN
								LEFT JOIN CMP.PEOPLE PPL ON JRN.CRT_NBR=PPL.PRSN_NBR
								WHERE JRN.DB_NM='CMP' 
									AND JRN.TBL_NM='PEOPLE' 
									AND JRN.PK='PRSN_NBR' 
									AND JRN.COL_NM<>'UPD_NBR'
									AND JRN.COL_NM<>'UPD_TS'
									AND JRN.COL_NM<>'PWD'
									AND JRN.COL_NM<>'PAY_BASE'
									AND JRN.COL_NM<>'PAY_ADD'
									AND JRN.COL_NM<>'PAY_OT'
									AND JRN.COL_NM<>'PAY_MISC'
									AND JRN.COL_NM<>'DED_DEF'
									AND JRN.COL_NM<>'BONUS'
									AND JRN.COL_NM<>'BON_MULT'
									AND JRN.PK_DTA='$PrsnNbr'";
				//echo $query_log;
				$result_log	= mysql_query($query_log, $local);
				while($row_log = mysql_fetch_array($result_log)){
					echo " ".$row_log['CRT_TS']." ".shortName($row_log['NAME'])." ubah ".$row_log['COL_NM']." dari ".$row_log['REC_BEG']." menjadi ".$row_log['REC_END']."<br />\n";
				}
				echo "<br />";
			?>
		</div>
	</p>
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
	</script>
</form>
<div></div>
</body>
</html>
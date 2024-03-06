<?php error_reporting(0);
	include "framework/database/connect-cloud.php";
	include_once"framework/functions/default.php";
	include "framework/security/default.php";
	include "framework/alert/alert.php";

	$CoNbr 			= $_GET['CO_NBR'];
	$Security 		= getSecurity($_SESSION['userID'],"AddressBook");
	$UpperSec 		= getSecurity($_SESSION['userID'],"Finance");
	$SalesSec 		= getSecurity($_SESSION['userID'],"DigitalPrint");
	$upperSecurity 		= getSecurity($_SESSION['userID'],"Executive");
	$Acc 			= getSecurity($_SESSION['userID'],"Accounting");

	$query      = "SELECT PLAFOND_DEF,PAY_TERM_DEF,BUY_TERM_DEF FROM NST.PARAM_LOC";
    	$result     = mysql_query($query);
    	$row        = mysql_fetch_array($result);
    	$PlafondDef = $row['PLAFOND_DEF'];
    	$PayTermDef = $row['PAY_TERM_DEF'];
    	$BuyTermDef = $row['BUY_TERM_DEF'];

	if(($locked==1)||($_COOKIE["LOCK"] == "LOCK")){ $displaylock = "display:none;"; }

	//get information schema
	$query_info	= "SELECT TABLE_NAME, COLUMN_NAME, COLUMN_KEY FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'CMP' AND TABLE_NAME ='COMPANY'";
	$result_info= mysql_query($query_info);
	$array_info	= array();
	while ($row_info = mysql_fetch_array($result_info)){
		if ($row_info['COLUMN_KEY']=="PRI") { $PK = $row_info['COLUMN_NAME']; }
		array_push($array_info,$row_info['COLUMN_NAME']);
	}
	
	//get data awal
	$query_awal	= "SELECT * FROM CMP.COMPANY WHERE CO_NBR='$CoNbr'";
	$result_awal= mysql_query($query_awal);
	$row_awal	= mysql_fetch_assoc($result_awal);
	
	$query="SELECT CO_NBR_DEF,WHSE_NBR_DEF FROM RTL.PARAM";
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	$CoNbrDef=$row['CO_NBR_DEF'];
	
	$xml=simplexml_load_file("data/param.xml");
	foreach($xml->{'business-unit'} as $BusUnit){
		$CoNbrAll.=trim($BusUnit->{'company-number'}).",";
	}
	$CoNbrAll=substr($CoNbrAll,0,-1);
	//echo $CoNbrAll;

	//Process changes here

	if(($_POST['CO_NBR']!="")&&($cloud!=false)){
	
		$j=syncTable("COMPANY","CO_NBR","CMP",$CMP,$local,$cloud);

		$CoNbr=$_POST['CO_NBR'];
		
		//Process add new
		if($CoNbr==-1)
		{
			$query="SELECT COALESCE(MAX(CO_NBR),0)+1 AS NEW_NBR FROM $CMP.COMPANY";
			$result=mysql_query($query,$cloud);
			$row=mysql_fetch_array($result);
			$CoNbr=$row['NEW_NBR'];
			$query="INSERT INTO $CMP.COMPANY (CO_NBR, CRT_TS_COM) VALUES (".$CoNbr.", CURRENT_TIMESTAMP)";
			$result=mysql_query($query,$cloud);
			$query=str_replace($CMP,"CMP",$query);
			$result=mysql_query($query,$local);

			
			
			//Process Slack Webhook
			if($slack){
				
			$query_crt = "SELECT NAME FROM CMP.PEOPLE WHERE PRSN_NBR = ".$_SESSION['personNBR']." ";
			$result_crt	= mysql_query($query_crt,$local);
			$row_crt	= mysql_fetch_array($result_crt);
			
			$PersonName	= $row_crt['NAME'];
			
			$slackChannelName = 'finance';

			$message = "Account baru dengan Nomor ".$CoNbr." telah dibuat oleh ".$PersonName." ";
				
			slack($message,$slackChannelName);
						
			}
		}
		
		if($_POST['CRDT_MAX']==""){$CrdtMax=$PlafondDef;}else{$CrdtMax=$_POST['CRDT_MAX'];}
		if($_POST['PAY_TERM']==""){$PayTerm=$PayTermDef;}else{$PayTerm=$_POST['PAY_TERM'];}
		if($_POST['BUY_TERM']==""){$BuyTerm=$BuyTermDef;}else{$BuyTerm=$_POST['BUY_TERM'];}
		if($_POST['APV_F']=="on"){$ApvF=1;}else{$ApvF=0;}
		if($_POST['MO_DUE_DTE']==""){$MoDueDte="NULL";}else{$MoDueDte=$_POST['MO_DUE_DTE'];}
		if($_POST['LATE_MAX']==""){$LateMax="NULL";}else{$LateMax=$_POST['LATE_MAX'];}
		if($_POST['LATE_INT']==""){$LateInt="NULL";}else{$LateInt=$_POST['LATE_INT'];}
		if($_POST['ACCT_EXEC_NBR']==""){$AcctExecNbr="NULL";}else{$AcctExecNbr=$_POST['ACCT_EXEC_NBR'];}
		if($_POST['BNK_CO_NBR']==""){$BnkCoNbr="NULL";}else{$BnkCoNbr=$_POST['BNK_CO_NBR'];}
		if($_POST['CAP_LIM']==""){$CapLim="NULL";}else{$CapLim=$_POST['CAP_LIM'];}
		if($_POST['CAP_MULT']==""){$CapMult="NULL";}else{$CapMult=$_POST['CAP_MULT'];}
		if($_POST['TAX_F']=="on"){$TaxF=1;}else{$TaxF=0;}
		if($_POST['OUT_CMN_F']=="on"){$outsourceF=1;}else{$outsourceF=0;}
		if($_POST['SLACK_CHNNL_NM']==""){$SlackChannel="";}else{$SlackChannel=$_POST['SLACK_CHNNL_NM'];}
		if($_POST['CO_NBR_PAR']==""){$CoNbrPar="";}else{$CoNbrPar=$_POST['CO_NBR_PAR'];}
		if($_POST['ACTG_TYP']==""){$ActgType=0;}else{$ActgType=$_POST['ACTG_TYP'];}

		$query="UPDATE $CMP.COMPANY
	   			SET CO_NBR=".$CoNbr.",
	   				CO_TYP='".$_POST['CO_TYP']."',
	   				CO_ID='".$_POST['CO_ID']."',
	   				NAME='".mysql_real_escape_string($_POST['NAME'])."',
					SLACK_CHNNL_NM='".$SlackChannel."',
	   				KEYWORDS='".$_POST['KEYWORDS']."',
	   				BUS_TYP='".$_POST['BUS_TYP']."',
					ADDRESS='".$_POST['ADDRESS']."',
					CITY_ID='".$_POST['CITY_ID']."',
					ZIP='".$_POST['ZIP']."',
					PHONE='".$_POST['PHONE']."',
					FAX='".$_POST['FAX']."',
					EMAIL='".$_POST['EMAIL']."',
					WEB='".$_POST['WEB']."',
					APV_F='".$ApvF."',
					CRDT_MAX='".$CrdtMax."',
					PAY_TERM='".$PayTerm."',
					BUY_TERM='".$BuyTerm."',
					MO_DUE_DTE=".$MoDueDte.",
					LATE_MAX=".$LateMax.",
					LATE_INT=".$LateInt.",
					ACCT_EXEC_NBR=".$AcctExecNbr.",
					OUT_CMN_F=".$outsourceF.",
					TAX_F=".$TaxF.",
					TAX_NBR='".$_POST['TAX_NBR']."',
	   				BNK_CO_NBR=".$BnkCoNbr.",
	   				CAP_LIM=".$CapLim.",
	   				CAP_MULT=".$CapMult.",
	   				BNK_ACCT_NM='".mysql_real_escape_string($_POST['BNK_ACCT_NM'])."',
					BNK_ACCT_NBR='".$_POST['BNK_ACCT_NBR']."',
					CO_NBR_PAR='".$CoNbrPar."',
					UPD_TS=CURRENT_TIMESTAMP,
					UPD_NBR=".$_SESSION['personNBR'].",
					ACTG_TYP = ".$ActgType."
					WHERE CO_NBR=".$CoNbr;
		

	   	$result=mysql_query($query,$cloud);
		$query=str_replace($CMP,"CMP",$query);
		$result=mysql_query($query,$local);

		//get_data_akhir
		$query_akhir	= "SELECT * FROM CMP.COMPANY WHERE CO_NBR='$CoNbr'";
		$result_akhir	= mysql_query($query_akhir);
		$row_akhir		= mysql_fetch_assoc($result_akhir);
		
		for ($i=0;$i<count($array_info);$i++){
			if ($row_awal[$array_info[$i]]!=$row_akhir[$array_info[$i]]) {
				$query_jrn	= "INSERT INTO $CMP.JRN_LIST (JRN_LIST_NBR, DB_NM, TBL_NM, COL_NM, PK, PK_DTA, REC_BEG, REC_END, CRT_TS, CRT_NBR) VALUES 
								('','".$CMP."','COMPANY','".$array_info[$i]."','$PK','$CoNbr','".$row_awal[$array_info[$i]]."','".$row_akhir[$array_info[$i]]."','".date('Y-m-d H:i:s')."','".$_SESSION['personNBR']."')";
				mysql_query($query_jrn,$cloud);
				$query_jrn=str_replace($CMP,"CMP",$query_jrn);
				mysql_query($query_jrn,$local);
				//echo $query_jrn."<br />";
			}
		}
	}
	
	if ($CoNbr != ''){

		include "framework/functions/crypt.php";
		$Url 	   = generateUrl(271,$CoNbrDef);
		
		$ArrCb     = json_decode(simple_crypt(file_get_contents('https://'.$Url.'/web_service/get-last-act-dt.php?CO_NBR='.$CoNbr),'d'),true);
		$queryAct  = "SELECT DATE(MAX(ORD_TS)) AS LAST_ACT_DT  FROM CMP.PRN_DIG_ORD_HEAD WHERE DEL_NBR =0  AND BUY_CO_NBR =".$CoNbr;
		$resultAct = mysql_query($queryAct);
		$rowAct    = mysql_fetch_array($resultAct);

		$lastActDt = $rowAct['LAST_ACT_DT'];

		$queryCrt  = "SELECT  DATE(CRT_TS) AS CRT_TS 
						FROM JRN_LIST 
						WHERE DB_NM ='CMP' 
							AND TBL_NM='COMPANY' 
							AND PK='CO_NBR' 
							AND PK_DTA =".$CoNbr."
						ORDER BY JRN_LIST_NBR ASC 
						LIMIT 1";
		$resultCrt= mysql_query($queryCrt);
		$rowCrt   = mysql_fetch_array($resultCrt);

		$crtTs      = $rowCrt['CRT_TS'];

		$queryUpd   = "SELECT DATE(UPD_TS) AS UPD_TS FROM CMP.COMPANY WHERE CO_NBR = ".$CoNbr;
		$resultUpd  = mysql_query($queryUpd);
		$rowUpd     = mysql_fetch_array($resultUpd);

		$updLast    = $rowUpd['UPD_TS'];

		if ($ArrCb['LAST_ACT_DT'] != '' || $ArrCb['CRT_TS'] !=''){
			if ($lastActDt < $ArrCb['LAST_ACT_DT']){
				$lastActDt = $ArrCb['LAST_ACT_DT'];
			}

			if ($crtTs < $ArrCb['CRT_TS']){
				$crtTs     = $ArrCb['CRT_TS'];
			}
		}

		if ($crtTs == ''){
			$crtTs = $updLast;
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
<script type="text/javascript" src="framework/datepicker/js/mootools-1.2.4-core.js"></script>
<script type="text/javascript" src="framework/datepicker/js/mootools-1.2.4.4-more.js"></script>
<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.js"></script>
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

<link rel="stylesheet" type="text/css" media="screen" href="framework/alert/alert.css" />

<link rel="stylesheet" href="framework/combobox/chosen.css">


<script type="text/javascript">
function checkName(){
			var name 	= document.getElementById('NAME').value;
			console.log(name);

			if (window.XMLHttpRequest) {
				// code for IE7+, Firefox, Chrome, Opera, Safari
				xmlhttp = new XMLHttpRequest();
			} else {
				// code for IE6, IE5
				xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
			}
			xmlhttp.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200) {
				
				console.log(this.responseText);
				
					if (this.responseText == "deny") {
						document.getElementById("response").innerHTML = '<span class="fa fa-error" style="font-family: San Francisco Display, HelveticaNeue,Helvetica Neue, Helvetica, Arial, sans-serif;font-weight:300,bold;font-size: 10pt;"> Nama <b>' + document.getElementById('NAME').value + '</b> sudah pernah digunakan</span>';
						
						document.getElementById("simpan").style.display 		= 'none';
						document.getElementById("simpan").disabled 				= true; 
					} else {
						document.getElementById("response").innerHTML 		= '';
						document.getElementById("simpan").style.display 		= 'block';
						document.getElementById("simpan").disabled 				= false; 
					}
				}
			};
			
			xmlhttp.open("GET","framework/validation/validation.php?form=company&name="+name,true);
			xmlhttp.send();			
		}	
</script>

</head>

<body>

<script>
	parent.document.getElementById('addressDeleteYes').onclick=
	function () { 
		parent.document.getElementById('content').src='address-company.php?DEL_C=<?php echo $CoNbr ?>';
		parent.document.getElementById('addressDelete').style.display='none';
		parent.document.getElementById('fade').style.display='none'; 
	};
</script>

<?php
	$query="SELECT 
				CO_NBR,
				CO_ID,
				NAME,
				SLACK_CHNNL_NM,
				KEYWORDS,
				CO_TYP,
				BUS_TYP,
				ADDRESS,
				CITY_ID,
				ZIP,
				PHONE,
				FAX,
				EMAIL,
				WEB,
				APV_F,
				PAY_TERM,
				BUY_TERM,
				CRDT_MAX,
				MO_DUE_DTE,
				LATE_MAX,
				LATE_INT,
				ACCT_EXEC_NBR,
				TAX_F,
				BNK_ACCT_NM,
				BNK_CO_NBR,
				BNK_ACCT_NBR,
				CAP_LIM,
				CAP_MULT, 
				3RD_PTY_NBR,
				OUT_CMN_F,
				TAX_NBR,
				CO_NBR_PAR,
				UPD_TS,
				UPD.UPD_NAME,
				ACTG_TYP
			FROM CMP.COMPANY
			LEFT JOIN (SELECT NAME AS UPD_NAME, PRSN_NBR FROM CMP.PEOPLE WHERE DEL_NBR=0) UPD ON UPD_NBR=UPD.PRSN_NBR
			WHERE CO_NBR=".$CoNbr;
				
	$result=mysql_query($query,$local);
	$row=mysql_fetch_array($result);
?>

<?php if(($Security==0)&&($CoNbr!=0)) { ?>
	<div class="toolbar-only">
	<p class="toolbar-left"><a href="javascript:void(0)" onclick = "window.scrollTo(0,0);parent.document.getElementById('addressDelete').style.display='block';parent.document.getElementById('fade').style.display='block'"><?php if(($cloud!=false)&&(paramCloud()==1)){echo '<span class="fa fa-trash toolbar" style="cursor:pointer"></span>'; }?></a></p>
	</div>
<?php } ?>
			
<form enctype="multipart/form-data" action="#" method="post" style="width:900px" onSubmit="return checkform();">
	<p>
		<h2>
			<?php
				if((!$cloud)&&($row['NAME']=="")){
					echo "<script>window.scrollTo(0,0);parent.document.getElementById('offline').style.display='block';parent.document.getElementById('fade').style.display='block';</script>";
				}
				echo $row['NAME'];if($row['NAME']==""){echo "Nama Baru";}
			?>
		</h2>
		<h3>
			Nomor Induk: <?php echo $row['CO_NBR'];if($row['CO_NBR']==""){echo "Baru";} ?>
		</h3>
		<input name="CO_NBR" value="<?php echo $row['CO_NBR'];if($row['CO_NBR']==""){echo "-1";} ?>" type="hidden" />
		
		<?php
			if($CoNbr != 0) {
			$query="SELECT PRSN_NBR,NAME,TTL,EMAIL,PHONE FROM CMP.PEOPLE WHERE CO_NBR=".$row['CO_NBR']." AND DEL_NBR=0 AND CO_NBR NOT IN (".$CoNbrAll.") ORDER BY UPD_TS DESC";
			$resultp=mysql_query($query);
			$rows=mysql_num_rows($resultp);
			if($rows>0){
				echo "<h3>Contact</h3><table>";
				echo "<tr>";
				echo "<th class='listable'>No. Induk</th>";
				echo "<th class='listable'>Nama</th>";
				echo "<th class='listable'>Jabatan</th>";
				echo "<th class='listable'>E-Mail</th>";
				echo "<th class='listable'>Telepon</th>";
				echo "</tr>";
				while($rowp=mysql_fetch_array($resultp)){
					echo "<tr $alt onclick=".chr(34)."changeSiblingUrl('leftmenu','address-lm.php?ROW=1');location.href='address-person-edit.php?PRSN_NBR=".$rowp['PRSN_NBR']."';".chr(34).">";
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
			if($SalesSec<=8){
				echo "<h3>History</h3>";
				echo "<iframe id='Chart' src='print-digital-report-customer-trend-chart.php?NBR=C".$CoNbr."' style='height:420px;width:810px;margin-left:-20px'></iframe>";
				
				$query="SELECT 
					PRN_DIG_DESC,
					PRN_DIG_TYP,
					COALESCE(SUM(VOL),0) AS VOL,
					MAX(ORD_DTE) AS ACT_LST_DTE,
					MIN(DAT.PRC) AS PRC_MIN,
					MAX(DAT.PRC) AS PRC_MAX,
					(SELECT 
						#CEILING((TOT_SUB)/COALESCE((ORD_Q*COALESCE(PRN_LEN,1)*COALESCE(PRN_WID,1)),1)/500)*500 AS PRC 
						CEILING((TOT_SUB)/COALESCE((ORD_Q*COALESCE(PRN_LEN,1)*COALESCE(PRN_WID,1)),1)) AS PRC 
					FROM CMP.PRN_DIG_ORD_DET DET 
						INNER JOIN CMP.PRN_DIG_ORD_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR 
						INNER JOIN CMP.PRN_DIG_TYP TYP ON TYP.PRN_DIG_TYP=DET.PRN_DIG_TYP 
					WHERE DATE(DET.CRT_TS)>DATE_SUB(NOW(),INTERVAL 12 MONTH) AND BUY_CO_NBR=$CoNbr AND PRN_DIG_DESC=DAT.PRN_DIG_DESC 
					ORDER BY ORD_TS DESC LIMIT 1
					) AS PRC_LST 
				FROM (
					SELECT 
						PRN_DIG_DESC,
						DET.PRN_DIG_TYP,
						DATE(ORD_TS) AS ORD_DTE,
						(ORD_Q*COALESCE(PRN_LEN,1)*COALESCE(PRN_WID,1)) AS VOL,
						#CEILING((TOT_SUB)/COALESCE((ORD_Q*COALESCE(PRN_LEN,1)*COALESCE(PRN_WID,1)),1)/500)*500 AS PRC 
						CEILING((TOT_SUB)/COALESCE((ORD_Q*COALESCE(PRN_LEN,1)*COALESCE(PRN_WID,1)),1)) AS PRC 
					FROM CMP.PRN_DIG_ORD_DET DET 
						INNER JOIN CMP.PRN_DIG_ORD_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR 
						INNER JOIN CMP.PRN_DIG_TYP TYP ON TYP.PRN_DIG_TYP=DET.PRN_DIG_TYP 
					WHERE DATE(DET.CRT_TS)>DATE_SUB(NOW(),INTERVAL 12 MONTH) AND BUY_CO_NBR=$CoNbr
				) DAT 
				GROUP BY PRN_DIG_DESC,PRN_DIG_TYP 
				ORDER BY 3 DESC LIMIT 10";
				
				//echo "<pre>".$query;
				
				$resultp=mysql_query($query);
				$rows=mysql_num_rows($resultp);
				if($rows>0){
					echo "<h3>Favorite</h3><table>";
					echo "<tr>";
					echo "<th class='listable'>Jenis Barang</th>";
					echo "<th class='listable'>Volume</th>";
					echo "<th class='listable'>Aktifitas</th>";
					echo "<th class='listable'>Min</th>";
					echo "<th class='listable'>Max</th>";
					echo "<th class='listable'>Akhir</th>";
					echo "</tr>";
					$alt="";
					while($rowp=mysql_fetch_array($resultp)){
						if($PrnDigTyp==""){$PrnDigTyp=$rowp['PRN_DIG_TYP'];};
						echo "<tr $alt ";
						if($salesSec<=8){
							echo "onclick=".chr(34)."document.getElementById('Curve').src='print-digital-report-customer-price-curve-chart.php?NBR=C".$CoNbr."&PRN_DIG_TYP=".$rowp['PRN_DIG_TYP']."';".chr(34);
						}
						echo ">";
						echo "<td style='cursor:pointer'>".$rowp['PRN_DIG_DESC']."</td>";
						echo "<td style='text-align:right;cursor:pointer'>".number_format($rowp['VOL'],0,",",".")."</td>";
						echo "<td style='text-align:center;cursor:pointer'>".$rowp['ACT_LST_DTE']."</td>";
						echo "<td style='text-align:right;cursor:pointer'>".number_format($rowp['PRC_MIN'],0,",",".")."</td>";
						echo "<td style='text-align:right;cursor:pointer'>".number_format($rowp['PRC_MAX'],0,",",".")."</td>";
						echo "<td style='text-align:right;cursor:pointer'>".number_format($rowp['PRC_LST'],0,",",".")."</td>";
						echo "</tr>";
						if($alt==""){$alt="class='alt'";}else{$alt="";}
					}
					echo "</table><br/>";
				}
				
				if($PrnDigTyp != '') {
				if($SalesSec<=7){
					echo "<h3>Price Curve</h3>";
					echo "<iframe id='Curve' src='print-digital-report-customer-price-curve-chart.php?NBR=C".$CoNbr."&PRN_DIG_TYP=".$PrnDigTyp."' style='height:420px;width:810px;margin-left:-20px'></iframe>";
				}
				}
			}
		?>
				
		<h3>Details</h3>
		<label class='side'>Nama Perusahaan</label>
		<input id="NAME" name="NAME" value="<?php echo $row['NAME']; ?>" type="text" size="50"  onkeyup='checkName();'/>
		&nbsp;<span id="response"></span>
		<?php
			//if(($_POST['NAME']=="")&&($row['NAME']=="")){echo "&nbsp;&nbsp;<img class='flat' style='vertical-align: text-bottom;' src='img/error.png'> Nama tidak boleh kosong";}
		?><br />
		<label class='side'>Pengusaha Kena Pajak</label>
		<div class='side' style='top:4px'><input name='TAX_F' id='TAX_F' type='checkbox' class='regular-checkbox' <?php if($row['TAX_F']=="1"){echo "checked";} ?>/>&nbsp;<label for="TAX_F"></label></div>
		<div class='combobox'></div>
		<label class='side'>NPWP</label>
		<input name="TAX_NBR" value="<?php echo $row['TAX_NBR']; ?>" type="text" size="50" /><br />
		<label class='side'>Kode Perusahaan</label>
		<input name="CO_ID" value="<?php echo $row['CO_ID']; ?>" size="30" /><br />
		<label class='side'>Jenis Perusahaan</label>
		<input name="CO_TYP" value="<?php echo $row['CO_TYP']; ?>" size="30" /><br />
		<label class='side'>Hashtag</label>
		<input id="KEYWORDS" name="KEYWORDS" value="<?php echo $row['KEYWORDS']; ?>" type="text" size="50" /><br />
		<label class='side'>Tipe Perusahaan</label>
		<div class='side'><select name="BUS_TYP" class="chosen-select">
		<?php
			$query="SELECT BUS_TYP,BUS_DESC FROM CMP.BUS_TYP ORDER BY 2";
			genCombo($query,"BUS_TYP","BUS_DESC",$row['BUS_TYP'],"",$local);
		?>
		</select></div><div class="labelbox"></div>
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
					WHERE CITY_ID!= 'YOGY' AND PROV_ID='DIY'
					ORDER BY 2)
					UNION ALL
					(SELECT CITY_ID, CONCAT(CITY_DESC,' ',CITY_NM) AS CITY_NM
					FROM CMP.CITY CTY
					INNER JOIN CMP.CITY_TYP TYP ON CTY.CITY_TYP=TYP.CITY_TYP
					WHERE CITY_ID!= 'YOGY' AND PROV_ID!='DIY'
					ORDER BY 2)";
			genCombo($query,"CITY_ID","CITY_NM",$row['CITY_ID'],"",$local);
		?>
		</select></div><div class="labelbox"></div>
		<label class='side'>Kode Pos</label>
		<input name="ZIP" value="<?php echo $row['ZIP']; ?>" type="text" size="30" /><br />
		<label class='side'>Nomor Telepon</label>
		<input name="PHONE" value="<?php echo $row['PHONE']; ?>" type="text" size="70" /><br />
		<label class='side'>Nomor Fax</label>
		<input name="FAX" value="<?php echo $row['FAX']; ?>" type="text" size="70" /><br />
		<label class='side'>Alamat E-Mail</label>
		<input name="EMAIL" value="<?php echo $row['EMAIL']; ?>" type="text" size="70" /><br />
		<label class='side'>Situs Web</label>
		<input name="WEB" value="<?php echo $row['WEB']; ?>" type="text" size="70" /><br />
		<label class='side'>Account Executive</label>
		<?php
			if($SalesSec > 1){
				
				if($row['ACCT_EXEC_NBR']=="")
					{ $where = ""; }
					else{ 
						$where = "WHERE PRSN_NBR = ".$row['ACCT_EXEC_NBR']." ";
						}
					
				echo "<div class='side'><select name='ACCT_EXEC_NBR' style='width:300px' class='chosen-select' disabled>";
				
				
				$query="SELECT PRSN_NBR,NAME FROM CMP.PEOPLE ".$where;
				genCombo($query,"PRSN_NBR","NAME",$row['ACCT_EXEC_NBR'],"Kosong",$local);
				echo "</select></div><div class='labelbox'></div>";
				
				echo "<input type='hidden' value='".$row['ACCT_EXEC_NBR']."' name='ACCT_EXEC_NBR'></input>";
				
			}else{
				echo "<div class='side'><select name='ACCT_EXEC_NBR' class='chosen-select'>";
				$query="SELECT PRSN_NBR,NAME FROM CMP.PEOPLE WHERE CO_NBR IN (SELECT CO_NBR FROM NST.PARAM_COMPANY) AND TERM_DTE IS NULL AND DEL_NBR=0 ORDER BY NAME ASC";
				genCombo($query,"PRSN_NBR","NAME",$row['ACCT_EXEC_NBR'],"Kosong",$local);
				echo "</select></div><div class='labelbox'></div>";
			}
						
		?>
		<label class='side'>Nama Channel Slack</label>
		<input id="SLACK_CHNNL_NM" name="SLACK_CHNNL_NM" value="<?php echo $row['SLACK_CHNNL_NM']; ?>" type="text" size="50" /><br />
		<?php if ($Security<1){ ?>
		<label class='side'>Perusahaan Induk</label>
		<div class='side'><select name="CO_NBR_PAR" class="chosen-select" style="width:270px">
		<?php
				$query="SELECT CO_NBR,CONCAT(NAME,' ',ADDRESS,' ',CITY_NM) AS CO_DESC
						FROM CMP.COMPANY COM INNER JOIN
						CMP.CITY CIT ON COM.CITY_ID=CIT.CITY_ID WHERE CO_NBR IN (SELECT CO_NBR FROM NST.PARAM_COMPANY) ORDER BY 2";
			genCombo($query,"CO_NBR","CO_DESC",$row['CO_NBR_PAR'],"Kosong",$local);
		?>
		</select></div><div class="labelbox"></div>
		<?php } ?>

		<div <?php if(($SalesSec<2 && $upperSecurity<7 && $UpperSec<3 && $Acc<8) && ($UpperSec<>1 || $upperSecurity<1)){ echo ""; } else { echo " style='display:none;'"; } ?>>
		<label class='side'>Status Approval</label>
		<input name='APV_F' id='APV_F' type='checkbox' class='regular-checkbox'
		<?php if ($row['APV_F']=='1'){ echo "checked"; } ?> />
		<label for="APV_F"></label><div class="labelbox" style="height:10px;"></div>
		
		<label class='side'>Outsource Commission</label>
		<input name='OUT_CMN_F' id='OUT_CMN_F' type='checkbox' class='regular-checkbox' <?php if ($row['OUT_CMN_F']=='1'){ echo "checked"; } ?> />
		<label for="OUT_CMN_F"></label><div class="labelbox" style="height:10px;"></div>
		
		<label class='side'>Plafon Kredit</label>
		<input name="CRDT_MAX" value="<?php echo $row['CRDT_MAX']; ?>" type="text" size="10"/><br />
		<label class='side'>Tempo Pembayaran (Hari)</label>
		<input name="PAY_TERM" value="<?php echo $row['PAY_TERM']; ?>" type="text" size="10"/><br />
		
		<label class='side'>Tempo pembelian (Hari)</label>
		<input name="BUY_TERM" value="<?php echo $row['BUY_TERM']; ?>" type="text" size="10"/><br />
		</div>

		<label class='side'>Tanggal Pembayaran</label>
		<input name="MO_DUE_DTE" value="<?php echo $row['MO_DUE_DTE']; ?>" type="text" size="10" <?php if($UpperSec>=2) {echo " readonly";} ?> /><br />
		<label class='side'>Batas Keterlambatan (Hari)</label>
		<input name="LATE_MAX" value="<?php echo $row['LATE_MAX']; ?>" type="text" size="10" <?php if($UpperSec>=2) {echo " readonly";} ?> /><br />
		<label class='side'>Bunga Majemuk (Persen)</label>
		<input name="LATE_INT" value="<?php echo $row['LATE_INT']; ?>" type="text" size="10" <?php if($UpperSec>=2) {echo " readonly";} ?> /><br />
		<label class='side'>Nama Pemilik Rekening</label>
		<input name="BNK_ACCT_NM" value="<?php echo $row['BNK_ACCT_NM']; ?>" type="text" size="40" /><br />
		<label class='side'>Nomor Rekening</label>
		<input name="BNK_ACCT_NBR" value="<?php echo $row['BNK_ACCT_NBR']; ?>" type="text" size="40" /><br />
		<label class='side'>Nama Bank</label>
		<div class='side'><select name="BNK_CO_NBR" class="chosen-select" style="width:270px">
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
		<label class='side'>Order Via</label>
		<input value="<?php echo $row['3RD_PTY_NBR']; ?>" type="text" size="10" readonly/><br />
		<label class='side'>Tanggal Pembuatan</label>
		<input name="CRT_TS_DT" value="<?php echo $crtTs; ?>" type="text" size="10" /><br />
		<label class='side'>Tanggal aktivitas Terakhir</label>
		<input name="LAST_ACT_DT" value="<?php echo $lastActDt; ?>" type="text" size="10" /><br />

		<div style="<?php echo $displaylock; ?>">
		<label class='side'>Rekening</label>
		<div class='side'>
			<select name="ACTG_TYP" id="ACTG_TYP" class="chosen-select" style="width:88px">
					<option value="">Pilih</option>
					<option value="1" <?php echo ($row['ACTG_TYP'] == '1') ? "selected" : ""; ?> >1</option>
					<option value="2" <?php echo ($row['ACTG_TYP'] == '2') ? "selected" : ""; ?> >2</option>
					<option value="3" <?php echo ($row['ACTG_TYP'] == '3') ? "selected" : ""; ?> >3</option>
			</select></div><div class="labelbox">
		</div>
		</div>
		<?php
			if(($cloud!=false)&&(paramCloud()==1)){
				echo "<input id='simpan' class='process' type='submit' value='Simpan' />";
			}
		?>
		<div class="userLog" style="margin-left: 0px;width: 400px;">
			<?php echo $row['UPD_TS']." ".shortName($row['UPD_NAME'])." ubah akhir<br />\n"; ?>
			<?php
				$query_log	= "SELECT JRN.*, PPL.NAME 
								FROM CMP.JRN_LIST JRN
								LEFT JOIN CMP.PEOPLE PPL ON JRN.CRT_NBR=PPL.PRSN_NBR
								WHERE JRN.DB_NM='CMP' 
									AND JRN.TBL_NM='COMPANY' 
									AND JRN.PK='CO_NBR' 
									AND JRN.COL_NM<>'UPD_NBR'
									AND JRN.COL_NM<>'UPD_TS'
									AND JRN.PK_DTA='$CoNbr'";
				//echo $query_log;
				$result_log	= mysql_query($query_log);
				while($row_log=mysql_fetch_array($result_log)){
					echo " ".$row_log['CRT_TS']." ".shortName($row_log['NAME'])." ubah ".$row_log['REC_BEG']." menjadi ".$row_log['REC_END']."<br />\n";
				}
				echo "<br />";
			?>
		</div>
	
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
	<script type="text/javascript">
	jQuery("#TAX_F").on("click", function () {
		if (jQuery(this).is(':checked')) {
			jQuery("#ACTG_TYP").html('<option value="1">1</option><option value="2">2</option><option value="3">3</option>');
			jQuery('.chosen-select').trigger("chosen:updated");
		}else{
			jQuery("#ACTG_TYP").html('<option value="">Pilih</option><option value="1">1</option><option value="2">2</option><option value="3">3</option>');
			jQuery('.chosen-select').trigger("chosen:updated");
		}
	});
	</script>
</form>
<div></div>				
</body>
</html>
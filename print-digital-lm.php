<?php
	include "framework/database/connect.php";
	include "framework/security/default.php";
	include "framework/functions/default.php";
	$Security=getSecurity($_SESSION['userID'],"DigitalPrint");
	$SecurityAE=getSecurity($_SESSION['userID'],"Executive");
	$SecurityFE=getSecurity($_SESSION['userID'],"Finance");
	$SecurityAD=getSecurity($_SESSION['userID'],"AddressBook");
	$SecurityAC=getSecurity($_SESSION['userID'],"Accounting");
	if(($locked==1)||($_COOKIE["LOCK"] == "LOCK")){ $displaylock = "display:none;"; }
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<script type="text/javascript" src="framework/functions/default.js"></script>
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">

</head>

<body class="sub">

<?php
	$family=getChildren($_SESSION['personNBR']);
	if($PrsnNbr==''){
		$children=getChildren($_SESSION['personNBR']);
		if($children!=''){$children=$_SESSION['personNBR'].','.$children;}else{$children=$_SESSION['personNBR'];}
	}else{
		$children=$PrsnNbr;
	}
	
	$queryInbox="SELECT
		COUNT(DISTINCT ORD_NBR) AS CNT_NBR
	FROM CMP.PRN_DIG_ORD_HEAD HED
	WHERE HED.ORD_STT_ID!='CP' AND (HED.ORD_NBR IN (SELECT ORD_NBR FROM CMP.JRN_PRN_DIG WHERE CRT_NBR IN (".$children.") GROUP BY ORD_NBR) OR CRT_NBR IN (".$children.") OR HED.UPD_NBR IN (".$children.") OR SLS_PRSN_NBR IN (".$children.")) AND HED.DEL_NBR=0";
	$resultInbox=mysql_query($queryInbox);
	$rowInbox=mysql_fetch_array($resultInbox);
	
	$query="SELECT 
		ORD_STT_ORD,
		STT.ORD_STT_ID,
		ORD_STT_DESC,
		ORD_STT_DET_F,
		COALESCE(COUNT(*),0) AS STT_NBR,
		SUM(CNT_NBR) AS CNT_NBR
	FROM CMP.PRN_DIG_STT STT
		LEFT OUTER JOIN CMP.PRN_DIG_ORD_HEAD HED ON STT.ORD_STT_ID=HED.ORD_STT_ID
		LEFT OUTER JOIN(
			SELECT
				ORD_NBR,
				COUNT(DISTINCT ORD_NBR) AS CNT_NBR
			FROM CMP.PRN_DIG_ORD_DET
			WHERE DEL_NBR = 0
			GROUP BY ORD_NBR
		)DET ON HED.ORD_NBR = DET.ORD_NBR
	WHERE HED.ORD_NBR IS NOT NULL AND STT.ORD_STT_ID!='CP' AND DEL_NBR=0
	GROUP BY 1,2,3,4
	ORDER BY 1";
	//echo $query;
	$result=mysql_query($query);
	echo "<div class='leftmenusel' id='proforma' onclick=".chr(34)."changeSiblingUrl('content','print-digital-tripane.php?STT=ACT');selLeftMenu(this);".chr(34)."><span class='fa fa-fw fa-folder-open leftmenuicon'></span>Aktif</div>";
	
	echo "<div class='leftmenu' onclick=".chr(34)."changeSiblingUrl('content','print-digital-tripane.php?STT=IBX');selLeftMenu(this);".chr(34)."><span class='fa fa-fw fa-inbox leftmenuicon'></span>Inbox&nbsp;&nbsp;<span class='badge'>".$rowInbox['CNT_NBR']."</span></div>";
	
	echo "<div class='leftmenu' onclick=".chr(34)."changeSiblingUrl('content','print-digital-tripane.php?STT=SLM');selLeftMenu(this);".chr(34)."><span class='fa fa-fw fa-history leftmenuicon'></span>Slow Moving</div>";
	while($row=mysql_fetch_array($result))
	{
		if($row['ORD_STT_DET_F']==1){
			echo "<div class='leftmenu' onclick=".chr(34)."changeSiblingUrl('content','print-digital-processing.php?STT=".$row['ORD_STT_ID']."');selLeftMenu(this);".chr(34)."><span class='fa fa-fw fa-".returnIcon($row['ORD_STT_ID'])." leftmenuicon'></span>".$row['ORD_STT_DESC']."&nbsp;&nbsp;<span class='badge'>".$row['CNT_NBR']."</span></div>";
		}else{
			echo "<div class='leftmenu' onclick=".chr(34)."changeSiblingUrl('content','print-digital-tripane.php?STT=".$row['ORD_STT_ID']."');selLeftMenu(this);".chr(34)."><span class='fa fa-fw fa-".returnIcon($row['ORD_STT_ID'])." leftmenuicon'></span>".$row['ORD_STT_DESC']."&nbsp;&nbsp;<span class='badge'>".$row['STT_NBR']."</span></div>";
		}
	}
	echo "<div class='leftmenu' onclick=".chr(34)."changeSiblingUrl('content','print-digital-tripane.php?STT=CP');selLeftMenu(this);".chr(34)."><span class='fa  fa-fw fa-check leftmenuicon'></span>Selesai</div>";

	echo "<div style='$displaylock' class='leftmenu' onclick=".chr(34)."changeSiblingUrl('content','print-digital-tripane.php?STT=POD');selLeftMenu(this);".chr(34)."><span class='fa fa-fw fa-bullhorn leftmenuicon'></span>Payment Overdue</div>";
	
	echo "<div style='$displaylock' class='leftmenu' onclick=".chr(34)."changeSiblingUrl('content','print-digital-list.php?STT=ACT');selLeftMenu(this);".chr(34)."><span class='fa fa-fw fa-list leftmenuicon'></span>Daftar</div>";
	
	echo "<div style='$displaylock' class='leftmenu' onclick=".chr(34)."changeSiblingUrl('content','print-digital-list.php?STT=ALL');selLeftMenu(this);".chr(34)."><span class='fa fa-fw fa-globe leftmenuicon'></span>Semua</div>";
?>
	<div style="<?php echo $displaylock; ?>" class="leftmenu" onclick="changeSiblingUrl('content','print-digital-tripane.php?STT=ALL&TYP=EST');selLeftMenu(this);"><span class='fa fa-fw fa-exchange leftmenuicon'></span>Proforma</div>

	<?php if ($SecurityAE<=0) { ?>
	<div style="<?php echo $displaylock; ?>" class="leftmenu" onclick="changeSiblingUrl('content','print-digital-tripane.php?STT=DEL');selLeftMenu(this);"><span class='fa fa-fw fa-trash leftmenuicon'></span>Telah Dihapus</div>
	<?php } ?>
	
	<?php if(($Security<2 && $SecurityAE<7 && $SecurityFE<3 && $SecurityAC<8) && ($SecurityFE<>1 || $SecurityAE<1)){ ?>
	<div style="<?php echo $displaylock; ?>" class="leftmenu" onclick="changeSiblingUrl('content','print-digital-list-log-error.php');selLeftMenu(this);"><span class='fa fa-fw fa-warning leftmenuicon'></span>Log Error Nota</div>
	<?php } ?>
	<div style="<?php echo $displaylock; ?>" class="leftmenu" onclick="changeSiblingUrl('content','daily-report.php');selLeftMenu(this);"><span class='fa fa-fw fa-clipboard leftmenuicon'></span>Daily Report</div>
<?php if($SecurityFE<=1 || $SecurityAD<=1){ ?>
	<div style="<?php echo $displaylock; ?>" class="leftmenu" onclick="changeSiblingUrl('content','print-digital-receivables.php');selLeftMenu(this);"><span class='fa fa-fw fa-download leftmenuicon'></span>Receivables</div>
<?php } ?>

<?php if($Security<=1){ ?>
	<div class="leftmenu" onclick="changeSiblingUrl('content','print-digital-type.php');selLeftMenu(this);"><span class='fa fa-fw fa-tags leftmenuicon'></span>Daftar Harga</div>
	<div style="<?php echo $displaylock; ?>" class="leftmenu" onclick="changeSiblingUrl('content','print-digital-report-special.php');selLeftMenu(this);"><span class='fa fa-fw fa-clipboard leftmenuicon'></span>Laporan Spesial</div>
	<div style="<?php echo $displaylock; ?>" class="leftmenu" onclick="changeSiblingUrl('content','print-digital-report-customer.php');selLeftMenu(this);"><span class='fa fa-fw fa-clipboard leftmenuicon'></span>Laporan Customer</div>
<?php } ?>
	
	<div style="<?php echo $displaylock; ?>" class="leftmenu" onclick="changeSiblingUrl('content','prn-dig-report-archive-station.php');selLeftMenu(this);"><span class='fa fa-fw fa-clipboard leftmenuicon'></span>Laporan Station</div>
	<div style="<?php echo $displaylock; ?>" class="leftmenu" onclick="changeSiblingUrl('content','print-digital-cap.php');selLeftMenu(this);"><span class='fa fa-fw fa-gift leftmenuicon'></span>CAP</div>
	
	<div style="<?php echo $displaylock; ?>" class="leftmenu" onclick="changeSiblingUrl('content','print-digital-a3-counter-log.php');selLeftMenu(this);">
    <span><span class='fa fa-fw fa-gears leftmenuicon'></span>Counter Log</span>
</body>
</html>
<?php
    function returnIcon($status)
    {
        switch($status) {
        case "NE":
            return "file-o";
        break;
        case "RC":
            return "picture-o";
        break;
        case "LT":
            return "object-group";
        break;
        case "PF":
            return "thumbs-up";
        break;
        case "QU":
            return "hourglass-half";
        break;
        case "PR":
            return "print";
        break;
        case "FN":
            return "scissors";
        break;
        case "RD":
            return "align-justify";
        break;
        case "DL":
            return "truck";
        break;
        case "NS":
            return "flag";
        break;
        case "CP":
            return "check";
        break;
        default:
            return "circle-thin";
        }
    }
?>
<?php
	include "framework/database/connect.php";
	include "framework/security/default.php";
	include "framework/functions/default.php";
    $OrdSttId=$_GET['STT'];
	$Security=getSecurity($_SESSION['userID'],"DigitalPrint");
	$SecurityAE=getSecurity($_SESSION['userID'],"Executive");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<head>
<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
	<script type="text/javascript" src="framework/functions/default.js"></script>
</head>
<body class="sub">

<div class="leftmenusel" onclick="changeSiblingUrl('content','retail-order-tripane.php?IVC_TYP=SL&STT=ACT');selLeftMenu(this);">
	<span class='fa fa-fw fa-folder-open leftmenuicon'></span>Aktif
</div>
<?php
$query="SELECT 
	   ORD_STT_ORD,
	   STT.ORD_STT_ID,
	   ORD_STT_DESC,
	   ORD_STT_DET_F,
	   COALESCE(COUNT(*),0) AS STT_NBR
FROM RTL.ORD_STT STT
	LEFT OUTER JOIN RTL.RTL_ORD_HEAD HED ON STT.ORD_STT_ID= HED.ORD_STT_ID
WHERE STT.ORD_STT_ID!='CP' AND HED.DEL_F = 0
GROUP BY 1,2,3,4
ORDER BY 1";
//echo $query;
$result=mysql_query($query);
while($row=mysql_fetch_array($result)){
	echo "<div class='leftmenu' onclick=".chr(34)."changeSiblingUrl('content','retail-order-tripane.php?IVC_TYP=SL&STT=".$row['ORD_STT_ID']."');selLeftMenu(this);".chr(34)."><span class='fa fa-fw fa-".returnIcon($row['ORD_STT_ID'])." leftmenuicon'></span>".$row['ORD_STT_DESC']."&nbsp;&nbsp;<span class='badge'>".$row['STT_NBR']."</span></div>";
}
?>

<div class="leftmenu" onclick="changeSiblingUrl('content','retail-order-tripane.php?IVC_TYP=SL&STT=CP');selLeftMenu(this);">
	<span class='fa  fa-fw fa-check leftmenuicon'></span>Selesai
</div>
	
<div class="leftmenu" onclick="changeSiblingUrl('content','retail-order-list.php?IVC_TYP=SL&STT=ALL');selLeftMenu(this);">
	<span class='fa fa-fw fa-globe leftmenuicon'></span>Semua
</div>

<div class="leftmenu" onclick="changeSiblingUrl('content','retail-order-tripane.php?TYP=EST&STT=ALL');selLeftMenu(this);">
	<span class='fa  fa-fw fa-exchange leftmenuicon'></span>Proforma
</div>

<div class="leftmenu" id="retail-ck" onclick="changeSiblingUrl('content','retail-order-tripane.php?IVC_TYP=PR');selLeftMenu(this);">
	<span class='fa fa-fw fa-sticky-note leftmenuicon'></span>Cetakan
</div>

<div class="leftmenu" onclick="changeSiblingUrl('content','retail-order-receivables.php');selLeftMenu(this);">
	<span class='fa fa-fw fa-download leftmenuicon'></span>Receivables
</div>
<div class="leftmenu" id="retail-shipping" onclick="changeSiblingUrl('content','retail-transport-tripane.php');selLeftMenu(this);">
	<span class='fa fa-fw fa-truck leftmenuicon'></span>Pengiriman
</div>
</body>
</html>
<?php
    function returnIcon($status)
    {
        switch($status) {
        case "NE":
            return "file-o";
        break;
        case "PR":
            return "cog";
        break;
        case "DR":
            return "truck";
        break;
        case "CP":
            return "check";
        break;
        default:
            return "circle-thin";
        }
    }
?>
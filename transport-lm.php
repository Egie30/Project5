<?php
	include "framework/database/connect.php";
    $OrdSttId=$_GET['STT'];

    if(($locked==1)||($_COOKIE["LOCK"] == "LOCK")){ $displaylock = "display:none;"; }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<script>parent.Pace.restart();</script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<script type="text/javascript" src="framework/functions/default.js"></script>

</head>

<body class="sub">

<div class="leftmenu<?php if($OrdSttId!='IN'){echo "sel";} ?>" onclick="changeSiblingUrl('content','transport-tripane.php?STT=DLO');selLeftMenu(this);">Open</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','transport-tripane.php?STT=DL');selLeftMenu(this);">Ready</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','transport-tripane.php?STT=ACT');selLeftMenu(this);">Active</div>
<?php
	$query="SELECT TRNSP_STT_ORD,STT.TRNSP_STT_ID,TRNSP_STT_DESC,COALESCE(COUNT(*),0) AS STT_NBR
			FROM CMP.TRNSP_STT STT
			LEFT OUTER JOIN CMP.TRNSP_HEAD HED ON STT.TRNSP_STT_ID=HED.TRNSP_STT_ID
			WHERE TRNSP_NBR IS NOT NULL AND STT.TRNSP_STT_ID!='CP'
			AND DEL_NBR=0
			GROUP BY 1,2,3
			ORDER BY 1";
	//echo $query;
	$result=mysql_query($query);
	while($row=mysql_fetch_array($result))
	{
		if($row['TRNSP_STT_ID']=='RP'){
			echo "<div class='leftmenu";
	        if($OrdSttId==$row['TRNSP_STT_ID']){echo "sel";}
	        echo "' onclick=".chr(34)."changeSiblingUrl('content','signtake-tripane.php?STT=".$row['TRNSP_STT_ID']."');selLeftMenu(this);".chr(34).">".$row['TRNSP_STT_DESC']."&nbsp;&nbsp;<span class='badge'>".$row['STT_NBR']."</span></div>";
		} else {
			echo "<div class='leftmenu";
	        if($OrdSttId==$row['TRNSP_STT_ID']){echo "sel";}
	        echo "' onclick=".chr(34)."changeSiblingUrl('content','transport-tripane.php?STT=".$row['TRNSP_STT_ID']."');selLeftMenu(this);".chr(34).">".$row['TRNSP_STT_DESC']."&nbsp;&nbsp;<span class='badge'>".$row['STT_NBR']."</span></div>";
		}  
	}

?>
<div class="leftmenu" onclick="changeSiblingUrl('content','transport-tripane.php?STT=ALL');selLeftMenu(this);">All</div>
<div style="<?php echo $displaylock; ?>" class="leftmenu" onclick="changeSiblingUrl('content','installation.php?IVC_TYP=PO');selLeftMenu(this);">Pemasangan</div>
</body>
</html>

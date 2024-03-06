<?php
	@header("Connection: close\r\n");
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	$barcode=$_GET['BARCODE'];
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" type="text/css" media="screen" href="css/popup.css" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">

</head>

<body style='width:250px'>

<span class='fa fa-times toolbar' style='cursor:pointer' onclick="parent.document.getElementById('printDigitalPopupBarcode').style.display='none';parent.document.getElementById('fade').style.display='none'"></span><br><br>

<div style='text-align:center;width:100%;'><img src='framework/barcode/prn-dig-barcode.php?STRING=<?php echo $barcode ?>'><br/><?php echo substr($barcode,1); ?></div>
<div>
<?php
    $query="SELECT COM.NAME AS BUY_CO_NM,PPL.NAME AS BUY_PRSN_NM,ALIAS,ORD_TTL,DET.ORD_DET_NBR,DET_TTL,
            PRN_DIG_DESC,ORD_Q,PRN_LEN,PRN_WID,FIN_BDR_DESC,
			GRM_CNT_TOP, GRM_CNT_BTM,GRM_CNT_LFT,GRM_CNT_RGT,HND_OFF_DESC,PRFO_F,BK_TO_BK_F,ROLLED_F
			FROM CMP.PRN_DIG_ORD_DET DET INNER JOIN CMP.PRN_DIG_ORD_HEAD HED ON HED.ORD_NBR=DET.ORD_NBR LEFT OUTER JOIN
                CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP LEFT OUTER JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR=PPL.PRSN_NBR LEFT OUTER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR=COM.CO_NBR LEFT OUTER JOIN CMP.PRN_DIG_FIN_BDR_TYP FIN ON DET.FIN_BDR_TYP=FIN.FIN_BDR_TYP LEFT OUTER JOIN CMP.HND_OFF_TYP HND ON DET.HND_OFF_TYP=HND.HND_OFF_TYP
			WHERE ORD_DET_NBR=".substr($barcode,1);
    //echo $query;
    $result=mysql_query($query);
	$row=mysql_fetch_array($result);
    $tag="";
    if($row['BUY_CO_NM']!=""){$tag.=$row['BUY_CO_NM']." › ";}
    if($row['BUY_PRSN_NM']!=""){$tag.=$row['BUY_PRSN_NM']." › ";}
    if($row['ORD_TTL']!=""){$tag.=$row['ORD_TTL']." › ";}
    if($row['DET_TTL']!=""){$tag.=$row['DET_TTL']." › ";}
    $tag.=$row['ORD_DET_NBR']." › ".$row['PRN_DIG_DESC']." › ";
    $tag.=$row['ORD_Q'];
    if($row['PRN_LEN'].$row['PRN_WID']!=""){$tag.=" @ ".$row['PRN_LEN']." x ".$row['PRN_WID'];}
    $tag.=" › ";
    $tag.=$row['FIN_BDR_DESC']." › ";
	
	$keling=$row['GRM_CNT_TOP']!="" ? "A".$row['GRM_CNT_TOP']." ":"";
    $keling.=$row['GRM_CNT_BTM']!="" ? "B".$row['GRM_CNT_BTM']." ":"";
    $keling.=$row['GRM_CNT_LFT']!="" ? "KA".$row['GRM_CNT_LFT']." ":"";
    $keling.=$row['GRM_CNT_RGT']!="" ? "KI".$row['GRM_CNT_RGT']." ":"";
    
    $tag.= $keling!="" ? $keling." › ":"";
	
    if($row['PRFO_F']!=""){$tag.="Perf › ";}
    if($row['BK_TO_BK_F']!=""){$tag.="B2B › ";}
    if($row['ROLLED_F']!=""){$tag.="Roll › ";}
    $tag.=$row['HND_OFF_DESC']." › ";
    $query="SELECT NAME,ALIAS FROM CMP.PEOPLE WHERE PRSN_NBR=".$_SESSION['personNBR'];
    $result=mysql_query($query);
    $row=mysql_fetch_array($result);
    if($row['ALIAS']!=""){
        $tag.=$row['ALIAS'];
    }else{
        $tag.=getInitials($row['NAME']);
    }
?>
</div>
<textarea id='tag' style='margin:7px;width:228px;height:80px;resize:none' onfocus="this.select()"><?php echo $tag; ?></textarea>
<script>
    document.getElementById('tag').focus();
</script>
</body>
</html>



<?php
	include "framework/database/connect.php";
    include "framework/functions/print-digital.php";
    include "framework/functions/crypt.php";
    include "framework/functions/default.php";
    include "framework/security/default.php";

    if($_SESSION['userID']==""){
		header('Location:login.php');
		exit;
	}else{
		$userID=$_SESSION['userID'];
		$query="SELECT NAME,PRSN_NBR FROM CMP.PEOPLE PPL INNER JOIN CMP.POS_TYP POS ON PPL.POS_TYP=POS.POS_TYP WHERE PRSN_ID='".$userID."'";
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		$name=$row['NAME'];
		$prsnNbr=$row['PRSN_NBR'];
	}

    $upperSec=getSecurity($_SESSION['userID'],"Executive");
    $mobileSec=getSecurity($_SESSION['userID'],"Mobile");
    $query="SELECT NAME,PRSN_NBR,CO_NBR FROM CMP.PEOPLE PPL INNER JOIN CMP.POS_TYP POS ON PPL.POS_TYP=POS.POS_TYP WHERE PRSN_ID='".$userID."' AND DEL_NBR=0";
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	$name=$row['NAME'];
	$prsnNbr=$row['PRSN_NBR'];
    if($mobileSec<=5){
        $CoNbr=2776;
    }else if($_SESSION['personNBR'] == 708){
		 $CoNbr=2776;
	}else{
        $CoNbr=$row['CO_NBR'];
    }
    $Cos=getRootCompany($CoNbr);
    if($Cos==''){$Cos=$row['CO_NBR'];}
    $CoNbrs=explode(",",$Cos);
	$result=mysql_query('SELECT * FROM NST.PARAM_LOC;');
	//echo print_r($mobileSec);
	
    $i=0;

    if($_COOKIE["DeviceAuth"]=="y"){
        setCookie("DeviceAuth","y",time()+7*24*3600);;
    }
	
	//grafik marketplace
	$query_grp 		= "SELECT PRM.PARAM_VALUE 
						FROM NST.PARAM PRM
						WHERE PRM.PARAM = 'GRPHC_CRIT_BEG'
						AND CO_NBR = ".$CoNbrDef."";
	$result_grp		= mysql_query($query_grp);
	$row_grp 		= mysql_fetch_array($result_grp);
	$CriticalBegin 	= $row_grp['PARAM_VALUE'];
	
	$query_grp 		= "SELECT PRM.PARAM_VALUE 
						FROM NST.PARAM PRM
						WHERE PRM.PARAM = 'GRPHC_CRIT_END'
						AND CO_NBR = ".$CoNbrDef."";
	$result_grp		= mysql_query($query_grp);
	$row_grp 		= mysql_fetch_array($result_grp);
	$CriticalEnd 	= $row_grp['PARAM_VALUE'];
	
	$query_grp 		= "SELECT PRM.PARAM_VALUE 
						FROM NST.PARAM PRM
						WHERE PRM.PARAM = 'GRPHC_MOD_BEG'
						AND CO_NBR = ".$CoNbrDef."";
	$result_grp		= mysql_query($query_grp);
	$row_grp 		= mysql_fetch_array($result_grp);
	$ModerateBegin	= $row_grp['PARAM_VALUE'];
	
	$query_grp 		= "SELECT PRM.PARAM_VALUE 
						FROM NST.PARAM PRM
						WHERE PRM.PARAM = 'GRPHC_MOD_END'
						AND CO_NBR = ".$CoNbrDef."";
	$result_grp		= mysql_query($query_grp);
	$row_grp 		= mysql_fetch_array($result_grp);
	$ModerateEnd	= $row_grp['PARAM_VALUE'];
	
	$Findiconic = json_decode(simple_crypt(file_get_contents('http://findiconic.nestoronline.com/dashboard-data-mobile.php'),'d'));
	$starttime 	= microtime(true);
    $result 	= mysql_query($query);
    $endtime 	= microtime(true);
    $duration 	= $endtime-$starttime;
	
	$leadDay=0;
	foreach($Findiconic->data as $dt){
		if($leadDay==7){
		    $begDay1	= $dt->ORD_DAY;;
		    $begMonth1	= $dt->ORD_MONTH-1;
		    $begYear1	= $dt->ORD_YEAR;
	   	}
		if($leadDay>=7){$dailyRev.= $dt->REVENUE.","; $dailyVal = $dt->REVENUE*1000000; $dailyRevRev.= $dailyVal.",";}			
		$avgData[] = $dt->REVENUE;				
		$leadDay++;
	}
	
	$dailyRev='['.substr($dailyRev,0,strlen($dailyRev)-1);
	$dailyRev.=']';
	
	$dailyRevRev='['.substr($dailyRevRev,0,strlen($dailyRevRev)-1);
	$dailyRevRev.=']';
	
	$movAvg='[';
	for($avg=7;$avg<=14*7;$avg++){
		$movAvg.=($avgData[$avg-6]+$avgData[$avg-5]+$avgData[$avg-4]+$avgData[$avg-3]+$avgData[$avg-2]+$avgData[$avg-1]+$avgData[$avg])/7;
		$movAvg.=",";
	}
	$movAvg=substr($movAvg,0,strlen($movAvg)-1);
	$movAvg.=']';
	
	$TotRevMarketplace 	= $Findiconic->total*1000000;

    foreach($CoNbrs as $CoNbr){
        $query="SELECT NAME FROM CMP.COMPANY WHERE CO_NBR=".$CoNbr;
        $result=mysql_query($query);
        $row=mysql_fetch_array($result);
        $CoName[]=$row['NAME'];
        $Url=generateUrl($CoNbr,$CoNbrDef);
        $DashboardProdStat=explode(';',simple_crypt(file_get_contents('http://'.$Url.'/mobile/dashboard-prod-stat.php'),'d'));
        $FLJ320P[]	= explode(',',$DashboardProdStat[0]);
        $RVS640[] 	= explode(',',$DashboardProdStat[1]);
        $AJ1800F[]	= explode(',',$DashboardProdStat[2]);
        $MVJ1624[]	= explode(',',$DashboardProdStat[3]);
        $HPL375[] 	= explode(',',$DashboardProdStat[4]);
		$SGH6090[]	= explode(',',$DashboardProdStat[5]);
        $LQ1390[]	= explode(',',$DashboardProdStat[6]);
        $KMC6501[]	= explode(',',$DashboardProdStat[7]);
        $Rev[]		= $DashboardProdStat[8];
        $Flex[]		= explode(',',$DashboardProdStat[9]);
        $Doc[]		= explode(',',$DashboardProdStat[10]);
        $Dte[]		= $DashboardProdStat[11];
        $DRev[]		= $DashboardProdStat[12];
        $volFLJ320P[]	= $DashboardProdStat[13];
        $volKMC6501[]	= $DashboardProdStat[14];
        $volRVS640[] 	= $DashboardProdStat[15];
        $volAJ1800F[]	= $DashboardProdStat[16];
        $volMVJ1624[]	= $DashboardProdStat[17];
        $volHPL375[] 	= $DashboardProdStat[18];
        $volSGH6090[] 	= $DashboardProdStat[19];
        $volLQ1390[] 	= $DashboardProdStat[20];
        $i++;
		
		//echo "<pre>";
		//print_r($DashboardProdStat);
    }
	
	$RevMarketplace = json_decode(simple_crypt(file_get_contents('http://findiconic.nestoronline.com/dashboard-data-mobile-company.php'),'d'));
	$RevMyBalbalan 	= 0;
	$RevHeroCave 	= 0;
	$RevAnimanze 	= 0;
	$RevBunny 		= 0;
	$RevFindiconic 	= 0;
	foreach($RevMarketplace->data as $dt){
		if($dt->OWN_CO_NBR == 4) { $RevFindiconic 	= $dt->REVENUE; }
		if($dt->OWN_CO_NBR == 5) { $RevBunny 	 	= $dt->REVENUE; }
		if($dt->OWN_CO_NBR == 6) { $RevAnimanze	 	= $dt->REVENUE; }
		if($dt->OWN_CO_NBR == 7) { $RevHeroCave	 	= $dt->REVENUE; }
		if($dt->OWN_CO_NBR == 8) { $RevMyBalbalan 	= $dt->REVENUE; }
	}
	
	//$RevBranch		= explode(',',json_decode(simple_crypt(file_get_contents('http://192.168.1.70/campus/mobile/dashboard-data-mobile-branch.php'),'d')));
	$RevBranch		= explode(';',simple_crypt(file_get_contents('http://192.168.1.70/campus/mobile/dashboard-data-mobile-branch.php'),'d'));
	//echo "<pre>";
	//print_r($RevBranch);
	$revCreativeHub[]		= $RevBranch[0];
	$dteCreativeHub[]		= $RevBranch[1];
    $dailyRevCreativeHub[]	= $RevBranch[2];
    $revKopiTugu[]		= $RevBranch[3];
    $dteKopiTugu[]		= $RevBranch[4];
    $dailyRevKopiTugu[]	= $RevBranch[5];

	/*
	echo "<br>".$revCreativeHub[0]."<br><br>";
	echo "<br>".$revKopiTugu[0]."<br><br>";
	echo "1. ".substr($dteKopiTugu[0],0,4);
	echo "<br>2. ".(intval(substr($dteKopiTugu[0],5,2))-1);
	echo "<br>3. ".substr($dteKopiTugu[0],8,2);
	echo "<br>3. ".substr($dteKopiTugu[0],8,2)."<br><br>";
	print_r($dteKopiTugu[0]);
	//echo "br>".$dteKopiTugu."<br><br>".$dailyRevKopiTugu;
	//echo "<br>".$dteKopiTugu[2]."<br><br>";
	*/
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, minimal-ui">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<title>Nestor</title>
<link rel="stylesheet" href="framework/7/css/framework7.ios.css">
<link rel="stylesheet" href="framework/7/css/framework7.ios.colors.css">
<link rel="stylesheet" href="framework/7/css/my-app.css">
<link rel="icon" href="img/icon.png">
<link rel="apple-touch-icon" href="img/nestor-icon-default.png?v=1">
<link rel="stylesheet" href="../css/font-awesome-4.4.0/css/font-awesome.min.css">
<!-- iPhone 5      --><link rel="apple-touch-startup-image" media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2)" href="img/apple-launch-640x1136.png">
<!-- iPhone 6/7/8  --><link rel="apple-touch-startup-image" media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2)" href="img/apple-launch-750x1334.png">
<!-- iPhone 6/7/8+ --><link rel="apple-touch-startup-image" media="(device-width: 414px) and (device-height: 736px) and (-webkit-device-pixel-ratio: 3)" href="img/apple-launch-1242x2208.png">
<!-- iPhone XR     --><link rel="apple-touch-startup-image" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2)" href="img/apple-launch-828x1792.png">
<!-- iPhone X/XS   --><link rel="apple-touch-startup-image" media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3)" href="img/apple-launch-1125x2436.png">
<!-- iPhone XS Max --><link rel="apple-touch-startup-image" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 3)" href="img/apple-launch-1242x2688.png">
<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script src="https://code.highcharts.com/highcharts.js"></script>
</head>

<body>
<div class="statusbar-overlay"></div>
<div class="panel-overlay"></div>
<div class="panel panel-left panel-reveal layout-dark">
    <div class="content-block"><img src="img/nestor-logo-black-outline.svg" style="width:150px"></div>
    <div class="list-block">
        <ul>
            <li>
                <a href="index.php" class="item-link close-panel"> 
                    <div class="item-content">
                        <div class="item-media"><span class="fa fa-fw fa-home"></span></div>
                        <div class="item-inner"> 
                            <div class="item-title">Dashboard</div>
                        </div>
                    </div></a></li>
		<?php if (!in_array($_SESSION['personNBR'], array("3681","3817"))){ ?>
            <li>
                <a href="contacts.php" class="item-link close-panel"> 
                    <div class="item-content">
                        <div class="item-media"><span class="fa fa-fw fa-building-o"></span></div>
                        <div class="item-inner"> 
                            <div class="item-title">Contacts &amp; Accounts</div>
                        </div>
                    </div></a></li>
		<?php } ?>
            <li>
                <a href="print-digital-branch.php?CO_NBRS=<?php echo $Cos; ?>" class="item-link close-panel"> 
                    <div class="item-content">
                        <div class="item-media"><span class="fa fa-fw fa-print"></span></div>
                        <div class="item-inner"> 
                            <div class="item-title">Digital Printing</div>
                        </div>
                    </div></a></li>
            <li>
                <a href="print-digital-price-list.php" class="item-link close-panel"> 
                    <div class="item-content">
                        <div class="item-media"><span class="fa fa-fw fa-tags"></span></div>
                        <div class="item-inner"> 
                            <div class="item-title">Daftar Harga</div>
                        </div>
                    </div></a></li>
            <!--
            <li>
                <a href="modals.html" class="item-link close-panel" style="background-color:#22272b"> 
                    <div class="item-content">
                        <div class="item-media"><span class="fa fa-fw fa-book"></span></div>
                        <div class="item-inner"> 
                            <div class="item-title">Sales</div>
                        </div>
                    </div></a></li>
            <li>
                <a href="bars.html" class="item-link close-panel" style="background-color:#22272b"> 
                    <div class="item-content">
                        <div class="item-media"><span class="fa fa-fw fa-clone"></span></div>
                        <div class="item-inner"> 
                            <div class="item-title">Inventory</div>
                        </div>
                    </div></a></li>
            <li>
                <a href="delivery-branch.php?CO_NBRS=<?php echo $Cos; ?>" class="item-link close-panel" style="background-color:#22272b"> 
                    <div class="item-content">
                        <div class="item-media"><span class="fa fa-fw fa-truck"></span></div>
                        <div class="item-inner"> 
                            <div class="item-title">Delivery</div>
                        </div>
                    </div></a></li>
            -->
            <!--
            <li>
                <a href="panels.html" class="item-link close-panel" style="background-color:#22272b">
                    <div class="item-content">
                        <div class="item-media"><span class="fa fa-fw fa-gavel"></span></div>
                        <div class="item-inner"> 
                            <div class="item-title">Aproval</div>
                        </div>
                    </div></a></li>
            -->
            <li>
                <a href="travel.php" class="item-link close-panel" data-ignore-cache="true"> 
                    <div class="item-content">
                        <div class="item-media"><span class="fa fa-fw fa-tachometer"></span></div>
                        <div class="item-inner"> 
                            <div class="item-title">Travel</div>
                        </div>
                    </div></a></li>
            <?php if($upperSec<=5 || $mobileSec <=5){ ?>
            <li>
                <a href="device-authorization.php" class="item-link close-panel" data-ignore-cache="true"> 
                    <div class="item-content">
                        <div class="item-media"><span class="fa fa-fw fa-mobile"></span></div>
                        <div class="item-inner"> 
                            <div class="item-title">Device Authorization</div>
                        </div>
                    </div></a></li>
            <?php } ?>
            <li>
                <a href="javascript:window.location.href='login.php?COMMAND=LOGOUT'" class="item-link close-panel">
                    <div class="item-content">
                        <div class="item-media"><span class="fa fa-fw fa-power-off"></span></div>
                        <div class="item-inner"> 
                            <div class="item-title">Logout</div>
                        </div>
                    </div></a></li>
        </ul>
    </div>
    <div class="content-block">
        <p>Nestor X Mobile version 2.0.0 Copyright &copy; 2008-<?php echo date('Y'); ?> proreliance.com</p>
    </div>
</div>
<div class="views">
    <div class="view view-main">
        <div class="navbar layout-white">
            <div class="navbar-inner">
                <div class="left"></div>
                <div class="center sliding">Dashboard</div>
                <div class="right"><a href="#" class="open-panel link icon-only color-nestor"><span class="fa fa-bars"></span></a></div>
            </div>
        </div>
        <div class="pages navbar-through toolbar-through layout-white">
            <!--<div data-page="index pull-to-refresh" class="page">-->
            <div data-page="index" class="page">
                <!--<div class="page-content pull-to-refresh-content">-->
                <div class="page-content">
                    <!--
                    <div class="pull-to-refresh-layer">
                        <div class="preloader"></div>
                        <div class="pull-to-refresh-arrow"></div>
                    </div>
                    -->
                    <div class="list-block media-list contacts-block">
                        <ul>
                            <li>
                                <div class="item-inner item-content">
                                    <div class="item-title-row">
                                        <div class="item-subtitle">Hello, <?php echo $name; ?></div>
                                    </div>
                                    <div class="item-title-row">
                                        <div class="item-subtitle">Welcome back to Nestor</div>
                                    </div>
                                </div>
                                <div class="item-inner item-content">
                                    <div class="item-description">Digital Printing</div>
                                </div></li></ul></div>
                    <?php if($upperSec<=4 || ($_SESSION['personNBR'] == 708)){ ?>
                    <div class="list-block media-list contacts-block">
                        <ul>
                            <li>
                                <div class="item-inner item-content">
                                    <div class='item-title-row'>
                                        <div class='item-title'>Today's Revenue</div>
                                    </div>
                                    <div id="chart-rev" class="item-description" style="margin-bottom:3px;height:200px"></div>
                                    <?php
                                        $i=0;
                                        foreach($CoNbrs as $CoNbr){
                                            echo "<div class='item-title-row'>";
                                            echo "<div class='item-description'>@".$CoName[$i]."</div>";
                                            echo "<div class='item-description color-nestor'>Rp. ".number_format($Rev[$i],0,'.',',')."</div>";
                                            echo "</div>";
                                            $TotRev+=$Rev[$i];
                                            $i++;
                                        }
										echo "<div class='item-title-row'>";
                                        echo "<div class='item-description'>@Marketplace</div>";
                                        echo "<div class='item-description color-nestor'>Rp. ".number_format($TotRevMarketplace,0,'.',',')."</div>";
                                        echo "</div>";
										echo "<div class='item-title-row'>";
                                        echo "<div class='item-description'>@Creative Hub</div>";
                                        echo "<div class='item-description color-nestor'>Rp. ".number_format($revCreativeHub[0],0,'.',',')."</div>";
                                        echo "</div>";
										echo "<div class='item-title-row'>";
                                        echo "<div class='item-description'>@Kopi Tugu Gejayan</div>";
                                        echo "<div class='item-description color-nestor'>Rp. ".number_format($revKopiTugu[0],0,'.',',')."</div>";
                                        echo "</div>";
                                        $TotRev+=$TotRevMarketplace;
                                    ?>
                                </div></li>
                            <li>
                                <div class="item-inner item-content">
                                    <div class='item-title-row'>
                                        <div class='item-title'>Total Revenue</div>
                                        <div class='item-title'>Rp. <?php echo number_format($TotRev,0,'.',','); ?></div>
                                    </div>
                                    <div class="item-description">Last updated</div>
                                    <div class='item-title-row'>
                                        <div class="item-description"><?php echo date("Y-m-d H:i:s"); ?></div>
                                        <div class="item description"><a href="javascript:window.location.href=window.location.href"><span class="fa fa-refresh color-nestor" onclick="this.classList.add('fa-spin')"></span></a></div>
                                    </div>
                                    <!--<div class="item-description">Pull to refresh</div>-->
                                </div></li></ul></div>
                    <?php } ?>
                    <?php $i=0;foreach($CoNbrs as $CoNbr){ ?>
                    <div class="list-block media-list contacts-block">
                        <ul>
                            <li>
                                <div class="item-inner item-content">
                                <div class="item-title col-100" style="margin-bottom:10px">Production @<?php echo $CoName[$i]; ?></div>
                                    <div id="chart-vol<?php echo $i; ?>" class="item-description" style="margin-bottom:3px;height:200px"></div>
                                </div></li></ul></div>
                    <div class="list-block media-list contacts-block" style="background-color:#eee">
                    <div class="item-inner item-content">
                        <div class="row">
                            <div class="background-01 col-50 dashboard">
                                <div class="dash-title">Outdoor</div>
                                <div class="dash-number"><?php echo number_format($FLJ320P[$i][0],0,"",""); ?>
                                <span class="dash-bar">{<?php echo intval(100*min($FLJ320P[$i][1]/$FLJ320P[$i][4],1)); ?>,<?php echo intval(100*min($FLJ320P[$i][2]/$FLJ320P[$i][4],1)); ?>,<?php echo intval(100*min($FLJ320P[$i][3]/$FLJ320P[$i][4],1)); ?>}</span></div>
                                <?php echo "<div>".number_format($Flex[$i][0],0,",",".")."/".number_format($Flex[$i][1],0,",",".")."/".$Flex[$i][2]."%"."/".$Flex[$i][3]."% (m)</div>"; ?>
                            </div>
                            <div class="background-03 col-50 dashboard">
                                <div class="dash-title">Indoor</div>
                                <div class="dash-number"><?php echo number_format($RVS640[$i][0],0,"",""); ?>
                                <span class="dash-bar">{<?php echo intval(100*min($RVS640[$i][1]/$RVS640[$i][4],1)); ?>,<?php echo intval(100*min($RVS640[$i][2]/$RVS640[$i][4],1)); ?>,<?php echo intval(100*min($RVS640[$i][3]/$RVS640[$i][4],1)); ?>}</span></div>                                
                                <div>Gabung outdoor (m)</div>
                            </div>
                            <div class="background-04 col-50 dashboard">
                                <div class="dash-title">Direct to Fabric</div>
                                <div class="dash-number"><?php echo Number_format($AJ1800F[$i][0],0,"",""); ?>
                                <span class="dash-bar">{<?php echo intval(100*min($AJ1800F[$i][1]/$AJ1800F[$i][4],1)); ?>,<?php echo intval(100*min($AJ1800F[$i][2]/$AJ1800F[$i][4],1)); ?>,<?php echo intval(100*min($AJ1800F[$i][3]/$AJ1800F[$i][4],1)); ?>}</span></div>                                
                                <div>Gabung outdoor (m)</div>
                            </div>
                            <div class="background-05 col-50 dashboard">
                                <div class="dash-title">Heat Transfer</div>
                                <div class="dash-number"><?php echo number_format($MVJ1624[$i][0],0,"",""); ?>
                                <span class="dash-bar">{<?php echo intval(100*min($MVJ1624[$i][1]/$MVJ1624[$i][4],1)); ?>,<?php echo intval(100*min($MVJ1624[$i][2]/$MVJ1624[$i][4],1)); ?>,<?php echo intval(100*min($MVJ1624[$i][3]/$MVJ1624[$i][4],1)); ?>}</span></div>                                
                                <div>Gabung outdoor (m)</div>
                            </div>
                            <div class="background-09 col-50 dashboard">
                                <div class="dash-title">Latex</div>
                                <div class="dash-number"><?php echo number_format($HPL375[$i][0],0,"",""); ?>
                                <span class="dash-bar">{<?php echo intval(100*min($HPL375[$i][1]/$HPL375[$i][4],1)); ?>,<?php echo intval(100*min($HPL375[$i][2]/$HPL375[$i][4],1)); ?>,<?php echo intval(100*min($HPL375[$i][3]/$HPL375[$i][4],1)); ?>}</span></div>                                
                                <div>Gabung outdoor (m)</div>
                            </div>
                            <div class="background-02 col-50 dashboard">
                                <div class="dash-title">A3+</div>
                                <div class="dash-number"><?php echo number_format($KMC6501[$i][0],0,"",""); ?>
                                <span class="dash-bar">{<?php echo intval(100*min($KMC6501[$i][1]/$KMC6501[$i][4],1)); ?>,<?php echo intval(100*min($KMC6501[$i][2]/$KMC6501[$i][4],1)); ?>,<?php echo intval(100*min($KMC6501[$i][3]/$KMC6501[$i][4],1)); ?>}</span></div>                                
                                <?php echo "<div>".number_format($Doc[$i][0],0,",",".")."/".number_format($Doc[$i][1],0,",",".")."/".$Doc[$i][2]."%"."/".$Doc[$i][3]."% (lbr)</div>"; ?>
                            </div>
							<div class="background-06 col-50 dashboard">
                                <div class="dash-title">UV</div>
                                <div class="dash-number"><?php echo number_format($SGH6090[$i][0],0,"",""); ?>
                                <span class="dash-bar">{<?php echo intval(100*min($SGH6090[$i][1]/$SGH6090[$i][4],1)); ?>,<?php echo intval(100*min($SGH6090[$i][2]/$SGH6090[$i][4],1)); ?>,<?php echo intval(100*min($SGH6090[$i][3]/$SGH6090[$i][4],1)); ?>}</span></div>
								<div>Gabung outdoor (menit)</div>
                            </div>
							<div class="background-07 col-50 dashboard">
                                <div class="dash-title">LASER</div>
                                <div class="dash-number"><?php echo number_format($LQ1390[$i][0],0,"",""); ?>
                                <span class="dash-bar">{<?php echo intval(100*min($LQ1390[$i][1]/$LQ1390[$i][4],1)); ?>,<?php echo intval(100*min($LQ1390[$i][2]/$LQ1390[$i][4],1)); ?>,<?php echo intval(100*min($LQ1390[$i][3]/$LQ1390[$i][4],1)); ?>}</span></div>
								<div>Gabung outdoor (menit)</div>
                            </div>
                        </div></div>
                    </div>
                    <?php $i++;} ?>
					
					<div class="list-block media-list contacts-block">
                        <ul>
                            <li>
                                <div class="item-inner item-content">
                                <div class="item-title col-100" style="margin-bottom:10px">Production @<?php echo "Marketplace"; ?></div>
                                    <div id="chart-vol3" class="item-description" style="margin-bottom:3px;height:200px"></div>
                                </div>
							</li>
						</ul>
					</div>
					<div class="list-block media-list contacts-block" style="background-color:#eee">
                    <div class="item-inner item-content">
                        <div class="row">
                            <div class="background-01 col-50 dashboard">
                                <div class="dash-title">My Balbalan</div>
                                <div class="dash-number" style="font-size: 24px"><?php echo number_format($RevMyBalbalan,0,"",","); ?>
                                </div>
                            </div>
                            <div class="background-03 col-50 dashboard">
                                <div class="dash-title">Hero Cave</div>
                                <div class="dash-number" style="font-size: 24px"><?php echo number_format($RevHeroCave,0,"",","); ?>
                                </div>
                            </div>
                            <div class="background-04 col-50 dashboard">
                                <div class="dash-title">Animanze</div>
                                <div class="dash-number" style="font-size: 24px"><?php echo Number_format($RevAnimanze,0,"",","); ?>
                                </div>
                            </div>
                            <div class="background-05 col-50 dashboard">
                                <div class="dash-title">Findiconic</div>
                                <div class="dash-number" style="font-size: 24px"><?php echo number_format($RevFindiconic,0,"",","); ?>
                                </div>
                            </div>
                            <div class="background-09 col-50 dashboard">
                                <div class="dash-title">Bunny And Cookie</div>
                                <div class="dash-number" style="font-size: 24px"><?php echo number_format($RevBunny,0,"",","); ?>
                                </div>
                            </div>
                        </div>
						</div>
						
						<div class="list-block media-list contacts-block">
							<ul>
								<li>
									<div class="item-inner item-content">
										<div class="row">
											<div class="col-50">
												<div class="item-title col-50" style="margin-bottom:10px">@<?php echo "Creative Hub Revenue"; ?></div>
												<div id="chart-vol4" class="item-description" style="margin-bottom:3px;height:200px"></div>
											</div>
											<div class="col-50">
												<div class="item-title col-50" style="margin-bottom:10px">@<?php echo "Kopi Tugu Gejayan Revenue"; ?></div>
												<div id="chart-vol5" class="item-description" style="margin-bottom:3px;height:200px"></div>
											</div>
										</div>
									</div>
								</li>
							</ul>
						</div>
						<div class="list-block media-list contacts-block" style="background-color:#eee">
                    </div>
					
					
                    <div style="text-align:center;padding-top:5px"><font style="font-weight:600">CONFIDENTIAL</font> and for internal use only.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="framework/7/js/framework7.js"></script>
<script type="text/javascript" src="framework/7/js/my-app.js?ver=2"></script>

<script>
    function displayChart(){
        Highcharts.setOptions({
            colors: [
				{linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#54b6ff'],[1, '#1169d8']]},
                {linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#4edd19'],[1, '#009c21']]},
                {linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#fed75c'],[1, '#f9cb1d']]},
                {linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#fd630a'],[1, '#ea1212']]},
                {linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#ab2e96'],[1, '#500a85']]},
                {linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#ed8f1c'],[1, '#a63d00']]},
                {linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#0ace80'],[1, '#008391']]},
                {linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#d2d2d2'],[1, '#b6b6b6']]},
                {linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#747474'],[1, '#242424']]},
                {linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#7d7d7d'],[1, '#303030']]},
                '#2c83de','#32c028','#F9CB1D','#ea1212','#822694','#cd7115','#08ad90','#b6b6b6','#242424','#575757'],
            chart: {
                style: {
                    fontFamily: '-apple-system,"SF UI Text","Helvetica Neue",Helvetica,Arial,sans-serif'
                }
            },
            credits: {
                enabled: false
            },
            tooltip: {
                enabled: false
            }
        });

        <?php if($upperSec<=5){ ?>
        var chart1 = Highcharts.chart('chart-rev', {

            chart: {
                type: 'column',
                margin: [0,0,25,0]
            },
            xAxis: {
               type: 'datetime',
                dateTimeLabelFormats: {
                    week: '%e %b'   
                }
            },
            yAxis: {
                labels: {
                    x: 0,
                    y: -2,
                    align: 'left'
                },
                tickInterval: 20000000
            },
            title:{
                text: null
            },
            legend: {
                enabled: false
            },
            plotOptions: {
                series: {
                    pointPadding: 0.1,
                    groupPadding: 0,
                    borderWidth: 0,
                    shadow: false,
                    states: {
                        hover: {
                            enabled: false
                        }
                    }
                },
                column: {
                    stacking: 'normal'
                }
            },
            series: [
            <?php $i=0;foreach($CoNbrs as $CoNbr){ ?>
            {
                name: 'Revenue @<?php echo $CoName[$i]; ?>',
                data: <?php echo "[".$DRev[$i].",".$Rev[$i]."]"; ?>,
                pointStart: Date.UTC(<?php echo substr($Dte[$i],0,4); ?>, <?php echo (intval(substr($Dte[$i],5,2))-1); ?>, <?php echo substr($Dte[$i],8,2); ?>),
                pointInterval: 24 * 3600 * 1000 // one day
            },
            <?php $i++;} ?>
			{
                name: 'Revenue Kopi Tugu',
                data: <?php echo "[".$dailyRevKopiTugu[0]."]"; ?>,
                pointStart: Date.UTC(<?php echo substr($dteKopiTugu[0],0,4); ?>, <?php echo (intval(substr($dteKopiTugu[0],5,2))-1); ?>, <?php echo substr($dteKopiTugu[0],8,2); ?>),
                pointInterval: 24 * 3600 * 1000 // one day
            }
            ],

            responsive: {
                rules: [{
                    condition: {
                        maxWidth: 500
                    },
                    chartOptions: {
                        subtitle: {
                            text: null
                        }
                    }
                }]
            }
        })
        <?php } ?>

        <?php $i=0;foreach($CoNbrs as $CoNbr){ ?>
        var chart<?php echo $i; ?> = Highcharts.chart('chart-vol<?php echo $i; ?>', {

            chart: {
                type: 'column',
                margin: [0,0,25,0]
            },
            xAxis: {
               type: 'datetime',
                dateTimeLabelFormats: {
                    week: '%e %b'   
                }
            },
            yAxis: [{
                labels: {
                    x: 0,
                    y: -2,
                    align: 'left'
                }
            },{
                labels: {
                    x: 0,
                    y: -2,
                    align: 'right'
                },
                opposite: true
            }],
            title:{
                text: null
            },
            legend: {
                enabled: false
            },
            plotOptions: {
                series: {
                    pointPadding: 0.1,
                    groupPadding: 0,
                    borderWidth: 0,
                    shadow: false,
                    marker: { enabled: false },
                    states: {
                        hover: {
                            enabled: false
                        }
                    }
                },
                column: {
                    stacking: 'normal'
                }
            },
            series: [{
                name: 'Outdoor',
                data: <?php echo "[".$volFLJ320P[$i].",".intval($FLJ320P[$i][0])."]"; ?>,
                pointStart: Date.UTC(<?php echo substr($Dte[$i],0,4); ?>, <?php echo (intval(substr($Dte[$i],5,2))-1); ?>, <?php echo substr($Dte[$i],8,2); ?>),
                pointInterval: 24 * 3600 * 1000 // one day
            },
            {
                name: 'A3+',
                type: 'areaspline',
                yAxis: 1,
                zIndex: 6,
                lineWidth: 2,
                color: '#32c028',
                fillOpacity: 0.5,
                data: <?php echo "[".$volKMC6501[$i].",".intval($KMC6501[$i][0])."]"; ?>,
                pointStart: Date.UTC(<?php echo substr($Dte[$i],0,4); ?>, <?php echo (intval(substr($Dte[$i],5,2))-1); ?>, <?php echo substr($Dte[$i],8,2); ?>),
                pointInterval: 24 * 3600 * 1000 // one day
            },
            {
                name: 'Indoor',
                color: Highcharts.getOptions().colors[2],
                data: <?php echo "[".$volRVS640[$i].",".intval($RVS640[$i][0])."]"; ?>,
                pointStart: Date.UTC(<?php echo substr($Dte[$i],0,4); ?>, <?php echo (intval(substr($Dte[$i],5,2))-1); ?>, <?php echo substr($Dte[$i],8,2); ?>),
                pointInterval: 24 * 3600 * 1000 // one day
            },
            {
                name: 'Direct Fabric',
                color: Highcharts.getOptions().colors[3],
                data: <?php echo "[".$volAJ1800F[$i].",".intval($AJ1800F[$i][0])."]"; ?>,
                pointStart: Date.UTC(<?php echo substr($Dte[$i],0,4); ?>, <?php echo (intval(substr($Dte[$i],5,2))-1); ?>, <?php echo substr($Dte[$i],8,2); ?>),
                pointInterval: 24 * 3600 * 1000 // one day
            },
            {
                name: 'Heat Transfer',
                color: Highcharts.getOptions().colors[4],
                data: <?php echo "[".$volMVJ1624[$i].",".intval($MVJ1624[$i][0])."]"; ?>,
                pointStart: Date.UTC(<?php echo substr($Dte[$i],0,4); ?>, <?php echo (intval(substr($Dte[$i],5,2))-1); ?>, <?php echo substr($Dte[$i],8,2); ?>),
                pointInterval: 24 * 3600 * 1000 // one day
            },
            {
                name: 'Latex',
                color: Highcharts.getOptions().colors[9],
                data: <?php echo "[".$volHPL375[$i].",".intval($HPL375[$i][0])."]"; ?>,
                pointStart: Date.UTC(<?php echo substr($Dte[$i],0,4); ?>, <?php echo (intval(substr($Dte[$i],5,2))-1); ?>, <?php echo substr($Dte[$i],8,2); ?>),
                pointInterval: 24 * 3600 * 1000 // one day
            },
			{
                name: 'UV',
				color: Highcharts.getOptions().colors[6],
                data: <?php echo "[".$volSGH6090[$i].",".intval($SGH6090[$i][0])."]"; ?>,
                pointStart: Date.UTC(<?php echo substr($Dte[$i],0,4); ?>, <?php echo (intval(substr($Dte[$i],5,2))-1); ?>, <?php echo substr($Dte[$i],8,2); ?>),
                pointInterval: 24 * 3600 * 1000 // one day
            },
            {
                name: 'Laser',
				color: Highcharts.getOptions().colors[7],
                data: <?php echo "[".$volLQ1390[$i].",".intval($LQ1390[$i][0])."]"; ?>,
                pointStart: Date.UTC(<?php echo substr($Dte[$i],0,4); ?>, <?php echo (intval(substr($Dte[$i],5,2))-1); ?>, <?php echo substr($Dte[$i],8,2); ?>),
                pointInterval: 24 * 3600 * 1000 // one day
            }],

            responsive: {
                rules: [{
                    condition: {
                        maxWidth: 500
                    },
                    chartOptions: {
                        subtitle: {
                            text: null
                        }
                    }
                }]
            }
        })
        <?php $i++;} ?>
		
		//Marketplace
		var chart3 = new Highcharts.Chart('chart-vol3', {
				chart: {
					renderTo: 'dailyRev',
					zoomType: 'xy'
				},
				title: {
					//text: 'Marketplace 13-Week Revenue Trend'
					text: null
				},
				subtitle: {
					//text: '7-Day Moving Average'
					text: null
				},
				xAxis: {
			        type: 'datetime',
			        dateTimeLabelFormats: {
			            week: '%e %b'   
			        }
			    },
				yAxis: [{ // Primary yAxis
					min: 0,
					labels: {
						formatter: function() {
							return Highcharts.numberFormat(this.value, 0);
						},
						style: {
							color: '#666666'
						}
					},
					title: {
						//text: '7-Day Moving Average (millions)',
						text: null,
						style: {
							color: '#666666'
						}
					},
					plotBands: [{
			            from: <?php echo $ModerateBegin; ?>,
			            to: <?php echo $ModerateEnd; ?>,
			            color: 'rgba(200, 200, 200, .2)',
			            label: { text: 'Moderate',
				            style: {
			                  color: '#909090'
   				            }
   				        }
   				    },{
			            from: <?php echo $CriticalBegin; ?>,
			            to: <?php echo $CriticalEnd; ?>,
			            color: 'rgba(122, 186, 218, .2)',
			            label: { text: 'Critical',
				            style: {
			                  color: '#909090'
   				            }
   				        }
			        }]
				}, { // Secondary yAxis
					title: {
						//text: 'Daily Revenue (millions)',
						text: null,
						style: {
							color: '#666666'
						}
					},
					labels: {
						formatter: function() {
							return Highcharts.numberFormat(this.value, 0);
						},
						style: {
							color: '#666666'
						}
					},
					opposite: true
				}],
				tooltip: {
					formatter: function() {
						return ''+
							Highcharts.dateFormat('%e %b %Y', this.x) + '<br/>' + (this.series.name == 'Revenue' ? '' : 'Average ') + 'Revenue: '+  Highcharts.numberFormat(this.y*1000000, 0);
					}
				},
				plotOptions: {
			        series: {
						pointPadding: 0.1,
						borderWidth: 0,
			            groupPadding: 0.01,
						shadow: false
			        }
			    },
				legend: {
					layout: 'vertical',
					align: 'left',
					x: 520,
					verticalAlign: 'top',
					y: 20,
					floating: true,
					backgroundColor: '#FFFFFF'
				},
				series: [{
					name: 'Revenue',
					type: 'column',
					yAxis: 1,
					data: <?php echo $dailyRev; ?>,	
					pointStart: Date.UTC(<?php echo $begYear1; ?>, <?php echo $begMonth1; ?>, <?php echo $begDay1; ?>),
			        pointInterval: 24 * 3600 * 1000 // one day
				}] 
			});
			
			//Lantai 1
			//Creative Hub
			var chart4 = new Highcharts.Chart('chart-vol4', {
				chart: {
					renderTo: 'dailyRevCreativeHub',
					zoomType: 'xy'
				},
				title: {
					//text: 'Marketplace 13-Week Revenue Trend'
					text: null
				},
				subtitle: {
					//text: '7-Day Moving Average'
					text: null
				},
				xAxis: {
			        type: 'datetime',
			        dateTimeLabelFormats: {
			            week: '%e %b'   
			        }
			    },
				yAxis: [{ // Primary yAxis
					min: 0,
					labels: {
						formatter: function() {
							return Highcharts.numberFormat(this.value, 0);
						},
						style: {
							color: '#666666'
						}
					},
					title: {
						//text: '7-Day Moving Average (millions)',
						text: null,
						style: {
							color: '#666666'
						}
					},
					plotBands: [{
			            from: <?php echo $ModerateBegin; ?>,
			            to: <?php echo $ModerateEnd; ?>,
			            color: 'rgba(200, 200, 200, .2)',
			            label: { text: 'Moderate',
				            style: {
			                  color: '#909090'
   				            }
   				        }
   				    },{
			            from: <?php echo $CriticalBegin; ?>,
			            to: <?php echo $CriticalEnd; ?>,
			            color: 'rgba(122, 186, 218, .2)',
			            label: { text: 'Critical',
				            style: {
			                  color: '#909090'
   				            }
   				        }
			        }]
				}, { // Secondary yAxis
					title: {
						//text: 'Daily Revenue (millions)',
						text: null,
						style: {
							color: '#666666'
						}
					},
					labels: {
						formatter: function() {
							return Highcharts.numberFormat(this.value, 0);
						},
						style: {
							color: '#666666'
						}
					},
					opposite: true
				}],
				tooltip: {
					enabled:true,
					formatter: function() {
						return ''+
							Highcharts.dateFormat('%e %b %Y', this.x) + '<br/>' + (this.series.name == 'Revenue' ? '' : 'Average ') + 'Revenue: '+  Highcharts.numberFormat(this.y, 0);
					}
				},
				plotOptions: {
			        series: {
						pointPadding: 0.1,
						borderWidth: 0,
			            groupPadding: 0.01,
						shadow: false
			        }
			    },
				legend: {
					layout: 'vertical',
					align: 'left',
					x: 450,
					verticalAlign: 'top',
					y: 0,
					floating: true,
					backgroundColor: '#FFFFFF'
				},
				series: [{
					name: 'Revenue',
					type: 'column',
					yAxis: 1,
					data: [<?php echo $dailyRevKopiTugu[0]; ?>],	
					pointStart: Date.UTC(<?php echo substr($dteKopiTugu[0],0,4); ?>, <?php echo (intval(substr($dteKopiTugu[0],5,2))-1); ?>, <?php echo substr($dteKopiTugu[0],8,2); ?>),
			        pointInterval: 24 * 3600 * 1000 // one day
				}] 
			});
			
			//Kopi Tugu Gejayan
			var chart5 = new Highcharts.Chart('chart-vol5', {
				chart: {
					renderTo: 'dailyRevKopiTugu',
					zoomType: 'xy'
				},
				title: {
					//text: 'Marketplace 13-Week Revenue Trend'
					text: null
				},
				subtitle: {
					//text: '7-Day Moving Average'
					text: null
				},
				xAxis: {
			        type: 'datetime',
			        dateTimeLabelFormats: {
			            week: '%e %b'   
			        }
			    },
				yAxis: [{ // Primary yAxis
					min: 0,
					labels: {
						formatter: function() {
							return Highcharts.numberFormat(this.value, 0);
						},
						style: {
							color: '#666666'
						}
					},
					title: {
						//text: '7-Day Moving Average (millions)',
						text: null,
						style: {
							color: '#666666'
						}
					},
					plotBands: [{
			            from: <?php echo $ModerateBegin; ?>,
			            to: <?php echo $ModerateEnd; ?>,
			            color: 'rgba(200, 200, 200, .2)',
			            label: { text: 'Moderate',
				            style: {
			                  color: '#909090'
   				            }
   				        }
   				    },{
			            from: <?php echo $CriticalBegin; ?>,
			            to: <?php echo $CriticalEnd; ?>,
			            color: 'rgba(122, 186, 218, .2)',
			            label: { text: 'Critical',
				            style: {
			                  color: '#909090'
   				            }
   				        }
			        }]
				}, { // Secondary yAxis
					title: {
						//text: 'Daily Revenue (millions)',
						text: null,
						style: {
							color: '#666666'
						}
					},
					labels: {
						formatter: function() {
							return Highcharts.numberFormat(this.value, 0);
						},
						style: {
							color: '#666666'
						}
					},
					opposite: true
				}],
				tooltip: {
					enabled:true,
					formatter: function() {
						return ''+
							Highcharts.dateFormat('%e %b %Y', this.x) + '<br/>' + (this.series.name == 'Revenue' ? '' : 'Average ') + 'Revenue: '+  Highcharts.numberFormat(this.y, 0);
					}
				},
				plotOptions: {
			        series: {
						pointPadding: 0.1,
						borderWidth: 0,
			            groupPadding: 0.01,
						shadow: false
			        }
			    },
				legend: {
					layout: 'vertical',
					align: 'left',
					x: 450,
					verticalAlign: 'top',
					y: 0,
					floating: true,
					backgroundColor: '#FFFFFF'
				},
				series: [{
					name: 'Revenue',
					type: 'column',
					yAxis: 1,
					data: [<?php echo $dailyRevKopiTugu[0]; ?>],	
					pointStart: Date.UTC(<?php echo substr($dteKopiTugu[0],0,4); ?>, <?php echo (intval(substr($dteKopiTugu[0],5,2))-1); ?>, <?php echo substr($dteKopiTugu[0],8,2); ?>),
			        pointInterval: 24 * 3600 * 1000 // one day
				}] 
			});
    }
    displayChart();
</script>
</body>
</html>

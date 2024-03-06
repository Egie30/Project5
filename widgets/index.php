<?php
if (substr($_SERVER['REMOTE_ADDR'], 0, 8) != substr($_SERVER['SERVER_ADDR'], 0, 8)) {
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off") {
        $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: ' . $redirect);
        exit();
    }
}

@include "framework/database/connect.php";
@include "framework/functions/print-digital.php";
@include "framework/functions/crypt.php";
@include "framework/functions/default.php";
@include "framework/security/default.php";

//    if($_SESSION['userID']==""){
//		header('Location:login.php');
//		exit;
//	}else{
$userID = 'stan';
$_SESSION['userID'] = $userID;
$_SESSION['personNBR'] = '271';
$query
    = "SELECT NAME,PRSN_NBR FROM CMP.PEOPLE PPL INNER JOIN CMP.POS_TYP POS ON PPL.POS_TYP=POS.POS_TYP WHERE PRSN_ID='"
    . $userID . "'";
$result = mysql_query($query);
$row = mysql_fetch_array($result);
$name = $row['NAME'];
$prsnNbr = $row['PRSN_NBR'];
//	}

$upperSec = getSecurity($userID, "Executive");
$mobileSec = getSecurity($userID, "Mobile");
$query
    = "SELECT NAME,PRSN_NBR,CO_NBR FROM CMP.PEOPLE PPL INNER JOIN CMP.POS_TYP POS ON PPL.POS_TYP=POS.POS_TYP WHERE PRSN_ID='"
    . $userID . "' AND DEL_NBR=0";
$result = mysql_query($query);
$row = mysql_fetch_array($result);
$name = $row['NAME'];
$prsnNbr = $row['PRSN_NBR'];
if ($mobileSec <= 5) {
    $CoNbr = 2776;
} else {
    $CoNbr = $row['CO_NBR'];
}
$Cos = getRootCompany($CoNbr);
if ($Cos == '') {
    $Cos = $row['CO_NBR'];
}
$CoNbrs = explode(",", $Cos);
$result = mysql_query('SELECT * FROM NST.PARAM_LOC;');

$i = 0;

//if ($_COOKIE["DeviceAuth"] == "y") {
//    setCookie("DeviceAuth", "y", time() + 7 * 24 * 3600);;
//}

foreach ($CoNbrs as $CoNbr) {
    $query = "SELECT NAME FROM CMP.COMPANY WHERE CO_NBR=" . $CoNbr;
    $result = mysql_query($query);
    $row = mysql_fetch_array($result);
    $CoName[] = $row['NAME'];
    $Url = generateUrl($CoNbr, $CoNbrDef);
    $DashboardProdStat = explode(';',
        simple_crypt(file_get_contents('http://' . $Url . '/mobile/dashboard-prod-stat.php'), 'd'));
    $FLJ320P[] = explode(',', $DashboardProdStat[0]);
    $RVS640[] = explode(',', $DashboardProdStat[1]);
    $AJ1800F[] = explode(',', $DashboardProdStat[2]);
    $MVJ1624[] = explode(',', $DashboardProdStat[3]);
    $HPL375[] = explode(',', $DashboardProdStat[4]);
    $SGH6090[] = explode(',', $DashboardProdStat[5]);
    $LQ1390[] = explode(',', $DashboardProdStat[6]);
    $KMC6501[] = explode(',', $DashboardProdStat[7]);
    $Rev[] = $DashboardProdStat[8];
    $Flex[] = explode(',', $DashboardProdStat[9]);
    $Doc[] = explode(',', $DashboardProdStat[10]);
    $Dte[] = $DashboardProdStat[11];
    $DRev[] = $DashboardProdStat[12];
    $volFLJ320P[] = $DashboardProdStat[13];
    $volKMC6501[] = $DashboardProdStat[14];
    $volRVS640[] = $DashboardProdStat[15];
    $volAJ1800F[] = $DashboardProdStat[16];
    $volMVJ1624[] = $DashboardProdStat[17];
    $volHPL375[] = $DashboardProdStat[18];
    $volSGH6090[] = $DashboardProdStat[19];
    $volLQ1390[] = $DashboardProdStat[20];
    $i++;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, minimal-ui">
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
    <!-- iPhone 5      -->
    <link rel="apple-touch-startup-image"
          media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2)"
          href="img/apple-launch-640x1136.png">
    <!-- iPhone 6/7/8  -->
    <link rel="apple-touch-startup-image"
          media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2)"
          href="img/apple-launch-750x1334.png">
    <!-- iPhone 6/7/8+ -->
    <link rel="apple-touch-startup-image"
          media="(device-width: 414px) and (device-height: 736px) and (-webkit-device-pixel-ratio: 3)"
          href="img/apple-launch-1242x2208.png">
    <!-- iPhone XR     -->
    <link rel="apple-touch-startup-image"
          media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2)"
          href="img/apple-launch-828x1792.png">
    <!-- iPhone X/XS   -->
    <link rel="apple-touch-startup-image"
          media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3)"
          href="img/apple-launch-1125x2436.png">
    <!-- iPhone XS Max -->
    <link rel="apple-touch-startup-image"
          media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 3)"
          href="img/apple-launch-1242x2688.png">
    <script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
    <script src="https://code.highcharts.com/highcharts.js"></script>
</head>

<body>
<div class="statusbar-overlay"></div>
<div class="panel-overlay"></div>
<div class="views">
    <div class="view view-main">
        <div class="pages layout-dark">
            <!--<div data-page="index pull-to-refresh" class="page">-->
            <div data-page="index" class="page">
                <!--<div class="page-content pull-to-refresh-content">-->
                <div class="page-content">
                    <?php if ($upperSec <= 5) { ?>
                        <div class="list-block media-list contacts-block">
                            <ul>
                                <li>
                                    <div class="item-inner item-content">
                                        <div class="row" style="margin-top:5px">
                                            <div class="col-50 item-description">
                                                <div class='item-title-row'>
                                                    <div class='item-title' style="color:#ffffff;margin-bottom:15px">
                                                        Today's Revenue
                                                    </div>
                                                </div>
                                                <?php
                                                $i = 0;
                                                $TotRev = 0;
                                                foreach ($CoNbrs as $CoNbr) {
                                                    echo "<div class='item-title-row' style='line-height:normal'>";
                                                    echo "<div class='item-description' style='font-size:9pt;line-height:normal'>"
                                                        . str_replace("Champion ", "", $CoName[$i]) . "</div>";
                                                    echo "<div class='item-description color-nestor' style='font-size:9pt;line-height:normal'>Rp. "
                                                        . number_format($Rev[$i], 0, '.', ',') . "</div>";
                                                    echo "</div>";
                                                    $TotRev += $Rev[$i];
                                                    $i++;
                                                }
                                                ?>
                                                <div class='item-title-row'>
                                                    <div class='item-description' style="font-size:9pt;color:#ffffff">
                                                        Total
                                                    </div>
                                                    <div class='item-description' style="font-size:9pt;color:#ffffff">
                                                        Rp. <?php echo number_format($TotRev, 0, '.', ','); ?></div>
                                                </div>
                                                <div class='item-title-row' style="margin-top:10px">
                                                    <div class="item-description"
                                                         style="font-size:7pt;line-height:normal">Last
                                                        update:<br><?php echo date("Y-m-d H:i:s"); ?></div>
                                                </div>
                                            </div>
                                            <div class="col-50" id="chart-rev" style="height:130px">
                                            </div>
                                        </div>
                                </li>
                            </ul>
                        </div>
                    <?php } ?>
                    <?php $i = 0;
                    foreach ($CoNbrs as $CoNbr) { ?>
                        <div class="list-block media-list contacts-block">
                            <ul>
                                <li>
                                    <div class="item-inner item-content">
                                        <div class="item-title col-100" style="margin-bottom:10px">Production
                                            @<?php echo $CoName[$i]; ?></div>
                                        <div id="chart-vol<?php echo $i; ?>" class="item-description"
                                             style="margin-bottom:3px;height:120px"></div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="list-block media-list contacts-block">
                            <div class="item-inner item-content">
                                <div class="row">
                                    <div class="background-01 col-50 dashboard">
                                        <div class="dash-title">Outdoor</div>
                                        <div class="dash-number"><?php echo number_format($FLJ320P[$i][0], 0, "",
                                                ""); ?>
                                            <span class="dash-bar">{<?php echo intval(100 * min($FLJ320P[$i][1]
                                                        / $FLJ320P[$i][4], 1)); ?>,<?php echo intval(100
                                                    * min($FLJ320P[$i][2] / $FLJ320P[$i][4],
                                                        1)); ?>,<?php echo intval(100 * min($FLJ320P[$i][3]
                                                        / $FLJ320P[$i][4], 1)); ?>}</span></div>
                                        <?php echo "<div>" . number_format($Flex[$i][0], 0, ",", ".") . "/"
                                            . number_format($Flex[$i][1], 0, ",", ".") . "/" . $Flex[$i][2] . "%" . "/"
                                            . $Flex[$i][3] . "% (m)</div>"; ?>
                                    </div>
                                    <div class="background-03 col-50 dashboard">
                                        <div class="dash-title">Indoor</div>
                                        <div class="dash-number"><?php echo number_format($RVS640[$i][0], 0, "", ""); ?>
                                            <span class="dash-bar">{<?php echo intval(100 * min($RVS640[$i][1]
                                                        / $RVS640[$i][4], 1)); ?>,<?php echo intval(100
                                                    * min($RVS640[$i][2] / $RVS640[$i][4], 1)); ?>,<?php echo intval(100
                                                    * min($RVS640[$i][3] / $RVS640[$i][4], 1)); ?>}</span></div>
                                        <div>Gabung outdoor (m)</div>
                                    </div>
                                    <div class="background-04 col-50 dashboard">
                                        <div class="dash-title">Direct to Fabric</div>
                                        <div class="dash-number"><?php echo Number_format($AJ1800F[$i][0], 0, "",
                                                ""); ?>
                                            <span class="dash-bar">{<?php echo intval(100 * min($AJ1800F[$i][1]
                                                        / $AJ1800F[$i][4], 1)); ?>,<?php echo intval(100
                                                    * min($AJ1800F[$i][2] / $AJ1800F[$i][4],
                                                        1)); ?>,<?php echo intval(100 * min($AJ1800F[$i][3]
                                                        / $AJ1800F[$i][4], 1)); ?>}</span></div>
                                        <div>Gabung outdoor (m)</div>
                                    </div>
                                    <div class="background-05 col-50 dashboard">
                                        <div class="dash-title">Heat Transfer</div>
                                        <div class="dash-number"><?php echo number_format($MVJ1624[$i][0], 0, "",
                                                ""); ?>
                                            <span class="dash-bar">{<?php echo intval(100 * min($MVJ1624[$i][1]
                                                        / $MVJ1624[$i][4], 1)); ?>,<?php echo intval(100
                                                    * min($MVJ1624[$i][2] / $MVJ1624[$i][4],
                                                        1)); ?>,<?php echo intval(100 * min($MVJ1624[$i][3]
                                                        / $MVJ1624[$i][4], 1)); ?>}</span></div>
                                        <div>Gabung outdoor (m)</div>
                                    </div>
                                    <div class="background-09 col-50 dashboard">
                                        <div class="dash-title">Latex</div>
                                        <div class="dash-number"><?php echo number_format($HPL375[$i][0], 0, "", ""); ?>
                                            <span class="dash-bar">{<?php echo intval(100 * min($HPL375[$i][1]
                                                        / $HPL375[$i][4], 1)); ?>,<?php echo intval(100
                                                    * min($HPL375[$i][2] / $HPL375[$i][4], 1)); ?>,<?php echo intval(100
                                                    * min($HPL375[$i][3] / $HPL375[$i][4], 1)); ?>}</span></div>
                                        <div>Gabung outdoor (m)</div>
                                    </div>
                                    <div class="background-02 col-50 dashboard">
                                        <div class="dash-title">A3+</div>
                                        <div class="dash-number"><?php echo number_format($KMC6501[$i][0], 0, "",
                                                ""); ?>
                                            <span class="dash-bar">{<?php echo intval(100 * min($KMC6501[$i][1]
                                                        / $KMC6501[$i][4], 1)); ?>,<?php echo intval(100
                                                    * min($KMC6501[$i][2] / $KMC6501[$i][4],
                                                        1)); ?>,<?php echo intval(100 * min($KMC6501[$i][3]
                                                        / $KMC6501[$i][4], 1)); ?>}</span></div>
                                        <?php echo "<div>" . number_format($Doc[$i][0], 0, ",", ".") . "/"
                                            . number_format($Doc[$i][1], 0, ",", ".") . "/" . $Doc[$i][2] . "%" . "/"
                                            . $Doc[$i][3] . "% (lbr)</div>"; ?>
                                    </div>
                                    <div class="background-06 col-50 dashboard">
                                        <div class="dash-title">UV</div>
                                        <div class="dash-number"><?php echo number_format($SGH6090[$i][0], 0, "",
                                                ""); ?>
                                            <span class="dash-bar">{<?php echo intval(100 * min($SGH6090[$i][1]
                                                        / $SGH6090[$i][4], 1)); ?>,<?php echo intval(100
                                                    * min($SGH6090[$i][2] / $SGH6090[$i][4],
                                                        1)); ?>,<?php echo intval(100 * min($SGH6090[$i][3]
                                                        / $SGH6090[$i][4], 1)); ?>}</span></div>
                                        <div>Gabung outdoor (menit)</div>
                                    </div>
                                    <div class="background-07 col-50 dashboard">
                                        <div class="dash-title">LASER</div>
                                        <div class="dash-number"><?php echo number_format($LQ1390[$i][0], 0, "", ""); ?>
                                            <span class="dash-bar">{<?php echo intval(100 * min($LQ1390[$i][1]
                                                        / $LQ1390[$i][4], 1)); ?>,<?php echo intval(100
                                                    * min($LQ1390[$i][2] / $LQ1390[$i][4], 1)); ?>,<?php echo intval(100
                                                    * min($LQ1390[$i][3] / $LQ1390[$i][4], 1)); ?>}</span></div>
                                        <div>Gabung outdoor (menit)</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php $i++;
                    } ?>
                    <div style="text-align:center;padding-top:5px"><font style="font-weight:600">CONFIDENTIAL</font> and
                        for internal use only.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<script type="text/javascript" src="framework/7/js/framework7.js"></script>
<script type="text/javascript" src="framework/7/js/my-app.js?ver=2"></script>

<script>
  function displayChart () {
    Highcharts.setOptions({
      colors: [
        { linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 }, stops: [[0, '#429cfa'], [1, '#1084f9']] },
        { linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 }, stops: [[0, '#3adb67'], [1, '#39d164']] },
        { linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 }, stops: [[0, '#ffd448'], [1, '#ffd63f']] },
        { linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 }, stops: [[0, '#fd6a65'], [1, '#fd4641']] },
        { linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 }, stops: [[0, '#d98ffa'], [1, '#be5bec']] },
        { linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 }, stops: [[0, '#feb351'], [1, '#fe9f32']] },
        { linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 }, stops: [[0, '#73d7fc'], [1, '#67d2fc']] },
        { linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 }, stops: [[0, '#aeaeb2'], [1, '#98989d']] },
        { linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 }, stops: [[0, '#444446'], [1, '#3a3a3b']] },
        { linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 }, stops: [[0, '#232325'], [1, '#1b1b1d']] },
        '#1084f9', '#39d164', '#ffd63f', '#fd4641', '#be5bec', '#fe9f32', '#67d2fc', '#98989d', '#3a3a3b', '#1b1b1d',
      ],
      chart: {
        style: {
          fontFamily: '-apple-system,"SF UI Text","Helvetica Neue",Helvetica,Arial,sans-serif',
        },
      },
      credits: {
        enabled: false,
      },
      tooltip: {
        enabled: false,
      },
    })

      <?php if($upperSec <= 5){ ?>
    var chart1 = Highcharts.chart('chart-rev', {

      chart: {
        type: 'column',
        margin: [0, 0, 25, 0],
        backgroundColor: '#22272b',
      },
      xAxis: {
        type: 'datetime',
        dateTimeLabelFormats: {
          week: '%e %b',
        },
      },
      yAxis: {
        labels: {
          x: 0,
          y: -2,
          align: 'left',
        },
        tickInterval: 10000000,
        gridLineColor: '#333333',
      },
      title: {
        text: null,
      },
      legend: {
        enabled: false,
      },
      plotOptions: {
        series: {
          pointPadding: .2,
          groupPadding: 0,
          borderWidth: 0,
          shadow: false,
          states: {
            hover: {
              enabled: false,
            },
          },
        },
        column: {
          stacking: 'normal',
        },
      },
      series: [
          <?php $i = 0;foreach($CoNbrs as $CoNbr){ ?>
        {
          name: 'Revenue @<?php echo $CoName[$i]; ?>',
          data: <?php echo "[" . $DRev[$i] . "," . $Rev[$i] . "]"; ?>,
          pointStart: Date.UTC(<?php echo substr($Dte[$i], 0, 4); ?>, <?php echo(intval(substr($Dte[$i], 5, 2))
              - 1); ?>, <?php echo substr($Dte[$i], 8, 2); ?>),
          pointInterval: 24 * 3600 * 1000, // one day
        },
          <?php $i++;} ?>
      ],

      responsive: {
        rules: [
          {
            condition: {
              maxWidth: 500,
            },
            chartOptions: {
              subtitle: {
                text: null,
              },
            },
          },
        ],
      },
    })
      <?php } ?>

      <?php $i = 0;foreach($CoNbrs as $CoNbr){ ?>
    var chart<?php echo $i; ?> = Highcharts.chart('chart-vol<?php echo $i; ?>', {

      chart: {
        type: 'column',
        margin: [0, 0, 25, 0],
      },
      xAxis: {
        type: 'datetime',
        dateTimeLabelFormats: {
          week: '%e %b',
        },
      },
      yAxis: [
        {
          labels: {
            x: 0,
            y: -2,
            align: 'left',
          },
        }, {
          labels: {
            x: 0,
            y: -2,
            align: 'right',
          },
          opposite: true,
        },
      ],
      title: {
        text: null,
      },
      legend: {
        enabled: false,
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
              enabled: false,
            },
          },
        },
        column: {
          stacking: 'normal',
        },
      },
      series: [
        {
          name: 'Outdoor',
          data: <?php echo "[" . $volFLJ320P[$i] . "," . intval($FLJ320P[$i][0]) . "]"; ?>,
          pointStart: Date.UTC(<?php echo substr($Dte[$i], 0, 4); ?>, <?php echo(intval(substr($Dte[$i], 5, 2))
              - 1); ?>, <?php echo substr($Dte[$i], 8, 2); ?>),
          pointInterval: 24 * 3600 * 1000, // one day
        },
        {
          name: 'A3+',
          type: 'areaspline',
          yAxis: 1,
          zIndex: 6,
          lineWidth: 2,
          color: '#32c028',
          fillOpacity: 0.5,
          data: <?php echo "[" . $volKMC6501[$i] . "," . intval($KMC6501[$i][0]) . "]"; ?>,
          pointStart: Date.UTC(<?php echo substr($Dte[$i], 0, 4); ?>, <?php echo(intval(substr($Dte[$i], 5, 2))
              - 1); ?>, <?php echo substr($Dte[$i], 8, 2); ?>),
          pointInterval: 24 * 3600 * 1000, // one day
        },
        {
          name: 'Indoor',
          color: Highcharts.getOptions().colors[2],
          data: <?php echo "[" . $volRVS640[$i] . "," . intval($RVS640[$i][0]) . "]"; ?>,
          pointStart: Date.UTC(<?php echo substr($Dte[$i], 0, 4); ?>, <?php echo(intval(substr($Dte[$i], 5, 2))
              - 1); ?>, <?php echo substr($Dte[$i], 8, 2); ?>),
          pointInterval: 24 * 3600 * 1000, // one day
        },
        {
          name: 'Direct Fabric',
          color: Highcharts.getOptions().colors[3],
          data: <?php echo "[" . $volAJ1800F[$i] . "," . intval($AJ1800F[$i][0]) . "]"; ?>,
          pointStart: Date.UTC(<?php echo substr($Dte[$i], 0, 4); ?>, <?php echo(intval(substr($Dte[$i], 5, 2))
              - 1); ?>, <?php echo substr($Dte[$i], 8, 2); ?>),
          pointInterval: 24 * 3600 * 1000, // one day
        },
        {
          name: 'Heat Transfer',
          color: Highcharts.getOptions().colors[4],
          data: <?php echo "[" . $volMVJ1624[$i] . "," . intval($MVJ1624[$i][0]) . "]"; ?>,
          pointStart: Date.UTC(<?php echo substr($Dte[$i], 0, 4); ?>, <?php echo(intval(substr($Dte[$i], 5, 2))
              - 1); ?>, <?php echo substr($Dte[$i], 8, 2); ?>),
          pointInterval: 24 * 3600 * 1000, // one day
        },
        {
          name: 'Latex',
          color: Highcharts.getOptions().colors[9],
          data: <?php echo "[" . $volHPL375[$i] . "," . intval($HPL375[$i][0]) . "]"; ?>,
          pointStart: Date.UTC(<?php echo substr($Dte[$i], 0, 4); ?>, <?php echo(intval(substr($Dte[$i], 5, 2))
              - 1); ?>, <?php echo substr($Dte[$i], 8, 2); ?>),
          pointInterval: 24 * 3600 * 1000, // one day
        },
        {
          name: 'UV',
          color: Highcharts.getOptions().colors[6],
          data: <?php echo "[" . $volSGH6090[$i] . "," . intval($SGH6090[$i][0]) . "]"; ?>,
          pointStart: Date.UTC(<?php echo substr($Dte[$i], 0, 4); ?>, <?php echo(intval(substr($Dte[$i], 5, 2))
              - 1); ?>, <?php echo substr($Dte[$i], 8, 2); ?>),
          pointInterval: 24 * 3600 * 1000, // one day
        },
        {
          name: 'Laser',
          color: Highcharts.getOptions().colors[7],
          data: <?php echo "[" . $volLQ1390[$i] . "," . intval($LQ1390[$i][0]) . "]"; ?>,
          pointStart: Date.UTC(<?php echo substr($Dte[$i], 0, 4); ?>, <?php echo(intval(substr($Dte[$i], 5, 2))
              - 1); ?>, <?php echo substr($Dte[$i], 8, 2); ?>),
          pointInterval: 24 * 3600 * 1000, // one day
        },
      ],

      responsive: {
        rules: [
          {
            condition: {
              maxWidth: 500,
            },
            chartOptions: {
              subtitle: {
                text: null,
              },
            },
          },
        ],
      },
    })
      <?php $i++;} ?>
  }

  displayChart()
</script>
</body>
</html>

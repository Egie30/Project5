<?php

global $CoNbrDef;
@include "framework/database/connect.php";
@include "framework/functions/print-digital.php";
@include "framework/functions/crypt.php";
@include "framework/functions/default.php";
@include "framework/security/default.php";

$userID = 'stan';
$_SESSION['userID'] = $userID;
$_SESSION['personNBR'] = '271';
$Today = date('Y-m-d');

$query = "SELECT NAME,PRSN_NBR FROM CMP.PEOPLE PPL 
    INNER JOIN CMP.POS_TYP POS ON PPL.POS_TYP=POS.POS_TYP WHERE PRSN_ID='" . $userID . "'";
$result = mysql_query($query);
$row = mysql_fetch_array($result);
$name = $row['NAME'];
$prsnNbr = $row['PRSN_NBR'];

$upperSec = getSecurity($userID, "Executive");
$mobileSec = getSecurity($userID, "Mobile");
$query = "SELECT NAME,PRSN_NBR,CO_NBR FROM CMP.PEOPLE PPL 
    INNER JOIN CMP.POS_TYP POS ON PPL.POS_TYP=POS.POS_TYP WHERE PRSN_ID='" . $userID . "' AND DEL_NBR=0";
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

$seriesData = [];
foreach ($CoNbrs as $CoNbr) {
    $query = "SELECT NAME FROM CMP.COMPANY WHERE CO_NBR=" . $CoNbr;
    $row = mysql_fetch_assoc(mysql_query($query));
    $CoName[] = $row['NAME'];

    $Url = 'http://' . generateUrl($CoNbr, $CoNbrDef) . '/mobile/dashboard-prod-stat.php';

    $DashboardProdStat = explode(';', simple_crypt(file_get_contents($Url), 'd'));

    $Rev[] = $DashboardProdStat[8];

    $currentCo = [];
    $currentCo['name'] = $row['NAME'];
    $currentCo['revenue'] = $DashboardProdStat[8];
    $currentCo['daily_revenue'] = explode(',', $DashboardProdStat[12]);
    $currentCo['date'] = explode(',', $DashboardProdStat[11]);
    $currentCo['url'] = $Url;
    $currentCo['raw'] = $DashboardProdStat;
    $seriesData[] = $currentCo;
}

/* == Champion Paper == */
$PaperSyncURL = 'https://paper.champs.asia/mobile/dashboard-retail-stat.php';
$PaperDashboardProdStat = json_decode((simple_crypt(file_get_contents($PaperSyncURL), 'd')));
$_rev = (in_array($Today, $PaperDashboardProdStat->dtes)) ? $PaperDashboardProdStat->revs[array_search($Today,
    $PaperDashboardProdStat->dtes)] : 0;

$paperSeries['name'] = 'Champion Paper';
$paperSeries['revenue'] = $_rev;
$paperSeries['date'] = $PaperDashboardProdStat->dtes;
$paperSeries['daily_revenue'] = $PaperDashboardProdStat->revs;
$paperSeries['raw'] = $PaperDashboardProdStat;
$seriesData[] = $paperSeries;

/* == Creativehub == */
$BranchSyncURL = 'http://192.168.1.20/mobile/dashboard-data-mobile-branch.php';
$RevBranch = explode(';', simple_crypt(file_get_contents($BranchSyncURL), 'd'));
$creativehubSeries['name'] = 'Champion Creativehub';
$creativehubSeries['revenue'] = $RevBranch[0];
$creativehubSeries['date'] = explode(',', $RevBranch[1]);
$creativehubSeries['daily_revenue'] = explode(',', $RevBranch[2]);
$seriesData[] = $creativehubSeries;

/* == Kopi Tugu == */
// Gejayan
$kopitugugejayanSeries['name'] = 'Kopi Tugu Gejayan';
$kopitugugejayanSeries['revenue'] = $RevBranch[3] ?: '0';
$kopitugugejayanSeries['date'] = explode(',', $RevBranch[4]);
$kopitugugejayanSeries['daily_revenue'] = explode(',', $RevBranch[5]);
$seriesData[] = $kopitugugejayanSeries;

// StreetSide
$StreetSideSyncURL = 'https://paper.champs.asia/mobile/dashboard-data-mobile-branch.php';
$StreetSideStat = json_decode((simple_crypt(file_get_contents($StreetSideSyncURL), 'd')));
$_rev = (in_array($Today, $StreetSideStat->dtes)) ? $StreetSideStat->revs[array_search($Today, $StreetSideStat->dtes)]
    : 0;
$kopitugustreetsideSeries['name'] = 'Kopi Tugu Streetside';
$kopitugustreetsideSeries['revenue'] = $_rev;
$kopitugustreetsideSeries['date'] = $StreetSideStat->dtes;
$kopitugustreetsideSeries['daily_revenue'] = $StreetSideStat->revs;
$kopitugustreetsideSeries['raw'] = $StreetSideStat;
$seriesData[] = $kopitugustreetsideSeries;

/* == Marketplace == */
// Findiconic
$FindiconicSyncURL = 'http://findiconic.nestoronline.com/dashboard-data-mobile.php';
$Findiconic = json_decode(simple_crypt(file_get_contents($FindiconicSyncURL), 'd'));

function kali_sejuta($d)
{
    return $d->REVENUE * 1000000;
}

function extract_date($d)
{
    return $d->Date;
}

$marketplaceDailyRevenue = array_map("kali_sejuta", $Findiconic->data);
$marketplaceSeries['name'] = 'Marketplace';
$marketplaceSeries['revenue'] = array_slice($marketplaceDailyRevenue, -1)[0];
$marketplaceSeries['date'] = array_map("extract_date", $Findiconic->data);
$marketplaceSeries['daily_revenue'] = $marketplaceDailyRevenue;
$seriesData[] = $marketplaceSeries;

// Negaraku
$NegarakuSyncURL = "https://negaraku.rocks/sync/dasboard-negaraku.php";
$Negaraku = json_decode(simple_crypt(file_get_contents($NegarakuSyncURL), 'd'));
$negarakuSeries['name'] = 'Negaraku';
$negarakuSeries['revenue'] = array_slice($Negaraku->dailyRevNegaraku, -1)[0];
$negarakuSeries['date'] = $Negaraku->date;
$negarakuSeries['daily_revenue'] = $Negaraku->dailyRevNegaraku;
$seriesData[] = $negarakuSeries;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, minimal-ui">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <title>Nestor Widgets</title>
    <link rel="stylesheet" href="framework/7/css/framework7.ios.css">
    <link rel="stylesheet" href="framework/7/css/framework7.ios.colors.css">
    <link rel="stylesheet" href="framework/7/css/my-app.css">
    <link rel="icon" href="img/nestor-icon-default.png">
    <link rel="apple-touch-icon" href="img/nestor-icon-default.png?v=1">
    <link rel="stylesheet" href="../css/font-awesome-4.7.0/css/font-awesome.min.css">
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
    <!--    <link rel="stylesheet" href="https://code.highcharts.com/css/highcharts.css">-->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
    <script src="https://code.highcharts.com/highcharts.js" crossorigin="anonymous"></script>
    <!-- <script src="https://code.highcharts.com/stock/modules/stock.js" crossorigin="anonymous"></script> -->
    <style>
        .notice {
            padding-top: 5px;
            position: absolute;
            bottom: 0;
            width: 100%;
            text-align: center;
            color: white;
        }

        .color {
            background-color: white;
            width: 7px;
            height: 7px;
            border-radius: 50%;
            display: inline-block;
        }

        .row {
            margin-bottom: 40px;
        }

        .item-title {
            color: #ffffff;
            margin-bottom: 15px;
        }
    </style>
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
                    <div class="list-block media-list contacts-block">
                        <div class="item-inner item-content">
                            <div class="row">
                                <div class="col-50 item-description">
                                    <div class='item-title-row'>
                                        <div class='item-title'>
                                            Today's Revenue
                                        </div>
                                    </div>
                                    <?php
                                    $i = 0;
                                    $TotRev = 0;
                                    foreach ($CoNbrs as $CoNbr) {
                                        ?>
                                        <div class='item-title-row' style='line-height:normal'>
                                            <div class='item-description'
                                                 style='font-size:9pt;line-height:normal'>
                                                <span class="color"></span> <?php echo str_replace("Champion ", "",
                                                    $CoName[$i]); ?></div>
                                            <div class='item-description'
                                                 style='font-size:9pt;line-height:normal'>
                                                Rp. <?php echo number_format($Rev[$i], 0, ',', '.'); ?></div>
                                        </div>
                                        <?php
                                        $TotRev += $Rev[$i];
                                        $i++;
                                    }

                                    // Manually add paper's revenue
                                    $TotRev += $paperSeries['revenue'];
                                    ?>
                                    <div class='item-title-row' style='line-height:normal'>
                                        <div class='item-description' style="font-size:9pt;line-height:normal">
                                            <span class="color"></span>
                                            Paper
                                        </div>
                                        <div class='item-description' style="font-size:9pt;line-height:normal">
                                            Rp. <?php echo number_format($paperSeries['revenue'], 0, ',', '.'); ?>
                                        </div>
                                    </div>
                                    <div class='item-title-row'>
                                        <div class='item-description' style="font-size:9pt;color:#ffffff">Total</div>
                                        <div class='item-description' style="font-size:9pt;color:#ffffff">
                                            Rp. <?php echo number_format($TotRev, 0, ',', '.'); ?>
                                        </div>
                                    </div>
                                    <div class='item-title-row' style="margin-top:10px">
                                        <div class="item-description" style="font-size:7pt;line-height:normal">Last
                                            update: <?php echo date("Y-m-d H:i"); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-50" id="chart-rev" style="height:130px"></div>
                            </div>
                            <!-- <div class="row">
                                <div class="col-100" id="chart-rev-first-floor" style="height:130px"></div>
                            </div>
                            <div class="row">
                                <div class="col-100" id="chart-rev-kopi" style="height:130px"></div>
                            </div> -->
                            <div class="row">
                                <div class="col-50">
                                    <div class='item-title-row' style='font-size:small'>
                                        <div class='item-title'>
                                            Kopi Tugu Today's Revenue
                                        </div>
                                    </div>
                                    <div class='item-title-row' style='line-height:normal'>
                                        <div class='item-description' style="font-size:9pt;line-height:normal">
                                            <span class="color"></span>
                                            Gejayan
                                        </div>
                                        <div class='item-description' style="font-size:9pt;line-height:normal">
                                            Rp. <?php echo number_format($kopitugugejayanSeries['revenue'], 0, ",",
                                                "."); ?>
                                        </div>
                                    </div>
                                    <div class='item-title-row' style='line-height:normal'>
                                        <div class='item-description' style="font-size:9pt;line-height:normal">
                                            <span class="color"></span>
                                            Streetside
                                        </div>
                                        <div class='item-description' style="font-size:9pt;line-height:normal">
                                            Rp. <?php echo number_format($kopitugustreetsideSeries['revenue'], 0, ",",
                                                "."); ?>
                                        </div>
                                    </div>
                                    <div class='item-title-row'>
                                        <div class='item-description' style="font-size:9pt;color:#ffffff">Total</div>
                                        <div class='item-description' style="font-size:9pt;color:#ffffff">
                                            Rp. <?php echo number_format(0, 0, ',', '.'); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-50" id="chart-rev-kopi-info" style="height:130px"></div>
                            </div>
                            <div class="row">
                                <div class="col-50" id="chart-rev-kopi-split-gejayan" style="height:130px"></div>
                                <div class="col-50" id="chart-rev-kopi-split-streetside" style="height:130px"></div>
                            </div>
                            <div class="row">
                                <div class="col-100" id="chart-rev-creative-hub" style="height:130px"></div>
                            </div>
                            <div class="row">
                                <div class="col-100" id="chart-rev-marketplace" style="height:130px"></div>
                            </div>

                            <div class="row">
                                <div class="col-100" id="chart-rev-negaraku" style="height:130px"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="notice">
                <span style="font-weight:600">CONFIDENTIAL</span> and for internal use only.
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="framework/7/js/framework7.js"></script>
<!--    <script type="text/javascript" src="framework/7/js/my-app.js?ver=2"></script>-->
<script type="text/javascript">
  const Today = new Date()
  Today.setUTCHours(0, 0, 0, 0)
  const Tomorrow = new Date().setDate(Today.getDate() + 1)

  // Series data
  const seriesData = JSON.parse('<?= json_encode($seriesData); ?>')
  console.log(seriesData)
  getSeries = (seriesName, optionalOptions) => {
    let obj = {}
    let s = seriesData.find(o => o.name === seriesName)
    if (s) {
      let drev = (s.daily_revenue)
        ? s.date.map((e, i) => [new Date(e).getTime(), parseInt(s.daily_revenue[i])])
        : []
      if (!(drev.find(d => d[0] === Today.getTime()))) {
        drev.push([Today.getTime(), parseInt(s.revenue)])
      }
      obj = {
        id: s.name.toLowerCase().replaceAll(' ', '-'),
        name: `Revenue @${s.name}`,
        // data: (s.daily_revenue) ? s.daily_revenue.split(',').map(e => parseInt(e)).concat(parseInt(s.revenue)) : [],
        // pointStart: (s.date) ? Date.parse(s.date.split(',')[0]) : [],
        data: drev,
        pointStart: s.date[0],
      }
    }
    return Object.assign({}, obj, optionalOptions)
  }

  formatNumber = n => new Intl.NumberFormat('id',
    { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n)

  Highcharts.setOptions({
    time: { useUTC: false },
    accessibility: { enabled: false },
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
      '#1084f9',
      '#39d164',
      '#ffd63f',
      '#fd4641',
      '#be5bec',
      '#fe9f32',
      '#67d2fc',
      '#98989d',
      '#3a3a3b',
      '#1b1b1d',
    ],
    chart: {
      style: { fontFamily: '-apple-system,"SF UI Text","Helvetica Neue",Helvetica,Arial,sans-serif' },
      backgroundColor: '#22272b',
      // styledMode: true, // use highchart.css
      margin: [0, 0, 25, 0],
      type: 'column',
      zoomType: 'xy',
    },
    credits: { enabled: false },
    tooltip: { enabled: false },
    legend: { enabled: false },
    plotOptions: {
      series: {
        pointPadding: .2,
        // pointWidth: 3,
        groupPadding: 0,
        borderWidth: 0,
        shadow: false,
        states: { hover: { enabled: false } },
        pointInterval: 24 * 3600 * 1000, // 1 day
      },
      areaspline: {
        fillOpacity: 0.5,
      },
      column: { stacking: 'normal' },
    },
    xAxis: {
      type: 'datetime',
      dateTimeLabelFormats: { week: '%e/%m' },
      tickColor: 'white',
      tickWidth: 1,
      tickInterval: 24 * 3600 * 1000 * 7, // 7 days
      labels: {
        y: 22,
        style: { color: 'white', fontSize: '0.6em' },
        autoRotation: undefined,
        rotation: 0,
      },
      max: Today.getTime(),
      min: new Date(Today - 29 * 24 * 60 * 60 * 1000).getTime(), // 4 minggu lalu
      // plotBands: [{
      //   from: 2019,
      //   to: 2020,
      //   color: 'rgba(68, 170, 213, .2)'
      // }
    },
    yAxis: {
      labels: {
        x: 0, y: -2,
        align: 'left',
        style: { color: 'white', fontSize: '0.6em' },
      },
      tickInterval: 100000,
      gridLineColor: '#333333',
    },
    responsive: { rules: [{ condition: { maxWidth: 500 }, chartOptions: { subtitle: { text: null } } }] },
  })

  function displayChart () {
    // Today's Revenue Chart
    Highcharts.chart('chart-rev', {
      title: { text: null },
      series: [
        getSeries('Champion Printing'),
        getSeries('Champion Campus'),
        getSeries('Champion Paper', {
          type: 'scatter',
          marker: { symbol: 'circle', enabled: true, lineWidth: 0, radius: 2, fillColor: '#ffd448', lineColor: null },
        }),
      ],
      yAxis: { tickInterval: 10000000 },
    })

    // Highcharts.chart('chart-rev-first-floor', {
    //   title: { text: 'First Floor', style: { color: 'white', fontSize: '15px' }, y: 1 },
    //   legend: {
    //     enabled: true,
    //     align: 'left',
    //     floating: true,
    //     verticalAlign: 'top',
    //     x: 0, y: 10,
    //     layout: 'vertical',
    //     itemStyle: { color: '#8e8e93', fontWeight: 'normal', fontSize: '0.6em' },
    //     labelFormatter: function () {
    //       // const sum = this.yData.reduce((a, b) => a + b)
    //       return `${this.name} (${formatNumber(this.yData.slice(-1))})`
    //     },
    //   },
    //   colors: Highcharts.getOptions('colors').colors.slice(3),
    //   series: [
    //     getSeries('Kopi Tugu Gejayan', { name: 'Gejayan' }),
    //     getSeries('Champion Creativehub', { name: 'Creativehub' }),
    //   ],
    // })

    // Highcharts.chart('chart-rev-kopi', {
    //   title: { text: 'Kopi Tugu Revenue', style: { color: 'white', fontSize: '15px' }, y: 1 },
    //   legend: {
    //     enabled: true,
    //     align: 'left',
    //     floating: true,
    //     verticalAlign: 'top',
    //     x: 0, y: 10,
    //     layout: 'vertical',
    //     itemStyle: { color: '#8e8e93', fontWeight: 'normal', fontSize: '0.6em' },
    //     labelFormatter: function () {
    //       // const sum = this.yData.reduce((a, b) => a + b)
    //       return `${this.name} (${formatNumber(this.yData.slice(-1))})`
    //     },
    //   },
    //   colors: Highcharts.getOptions('colors').colors.slice(5),
    //   series: [
    //     getSeries('Kopi Tugu Gejayan', { name: 'Gejayan' }),
    //     getSeries('Kopi Tugu Streetside', { name: 'Streetside' }),
    //   ],
    // })

    Highcharts.chart('chart-rev-kopi-info', {
      title: { text: null },
      colors: Highcharts.getOptions('colors').colors.slice(5),
      series: [
        getSeries('Kopi Tugu Gejayan', { name: 'Gejayan' }),
        getSeries('Kopi Tugu Streetside', { name: 'Streetside' }),
      ],
    })

    Highcharts.chart('chart-rev-kopi-split-gejayan', {
      title: { text: 'Kopi Tugu Gejayan', style: { color: 'white', fontSize: '12px' } },
      colors: Highcharts.getOptions('colors').colors.slice(5),
      series: [getSeries('Kopi Tugu Gejayan', { name: 'Gejayan' })],
    })

    Highcharts.chart('chart-rev-kopi-split-streetside', {
      title: { text: 'Kopi Tugu Streetside', style: { color: 'white', fontSize: '12px' } },
      colors: Highcharts.getOptions('colors').colors.slice(6),
      series: [getSeries('Kopi Tugu Streetside', { name: 'Streetside' })],
    })
    Highcharts.chart('chart-rev-creative-hub', {
      chart: { type: 'areaspline' },
      title: { text: 'Creative Hub Revenue', style: { color: 'white', fontSize: '15px' }, margin: 50 },
      colors: Highcharts.getOptions('colors').colors.slice(7),
      series: [
        getSeries('Champion Creativehub', {
          pointInterval: 7 * 24 * 3600 * 1000,
          zoneAxis: 'x',
          zones: [
            {
              value: Tomorrow.getTime(),
            }, { // future zones (prediction/estimation)
              dashStyle: 'dot',
              color: '#f7a35c',
              fillColor: '#fff',
            },
          ],
        }),
      ],
      // xAxis: { }
      yAxis: { tickInterval: 1000000 },
    })

    Highcharts.chart('chart-rev-marketplace', {
      chart: { type: 'spline' },
      title: { text: 'Marketplace Revenue', style: { color: 'white', fontSize: '15px' } },
      colors: Highcharts.getOptions('colors').colors.slice(1),
      series: [getSeries('Marketplace')],
      yAxis: { tickInterval: 50000 },
    })

    Highcharts.chart('chart-rev-negaraku', {
      title: { text: 'Negaraku Revenue', style: { color: 'white', fontSize: '15px' } },
      colors: Highcharts.getOptions('colors').colors.slice(2),
      series: [getSeries('Negaraku')],
    })
  }

  window.addEventListener('DOMContentLoaded', e => {
    displayChart()

    const correctColor = el => {
      const chartRevHc = Highcharts.charts.find(c => c.renderTo === el)
      const colors = el.parentElement.querySelectorAll('span.color')
      for (let i = 0; i < colors.length; i++) {
        colors[i].style.backgroundColor = chartRevHc.series[i].color.stops[0][1]
      }
    }

    // Correcting label color for first info chart
    const chartRev = document.querySelector('#chart-rev')
    correctColor(chartRev)

    const chartRevKopi = document.querySelector('#chart-rev-kopi-info')
    correctColor(chartRevKopi)

    // Debug
    console.info(Highcharts.charts)
  })

  function reRender () {
    Highcharts.charts.forEach(c => c.render())
  }

  function changeType (series, newType) {
    series.chart.addSeries({
      type: newType,
      name: series.name,
      data: series.data.map(d => [d.x, d.y]),
      userOptions: series.userOptions,
    }, false)

    series.remove()
  }

  function lookupSeries (id) {
    const x = ch => ch.series.find(s => s.userOptions.id === id)
    return x(Highcharts.charts.find(x))
  }
</script>
</body>
</html>

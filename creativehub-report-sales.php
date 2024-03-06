<?php

require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
require_once "framework/pagination/pagination.php";

$searchQuery = mysql_escape_string(strtoupper($_REQUEST['s']));
$whereClauses = array("HED.SLS_PRSN_NBR != ''");
$BegDt=$_GET['ORD_TS'];
$EndDt=$_GET['ORD_END_TS'];
if($BegDt==""){
  $BegDt=date("Y-m-01");
}
if($EndDt==""){
  $EndDt=date("Y-m-d");
}

if ($searchQuery != "") {
    $searchQuery = explode(" ", $searchQuery);

    foreach ($searchQuery as $query) {
        $query = trim($query);

        if (empty($query)) {
            continue;
        }

        if (strrpos($query, '%') === false) {
            $query = '%' . $query . '%';
        }
        $whereClauses[] = "(
            HED.ORD_NBR LIKE '" . $query . "'
            OR PPL.NAME  LIKE '" . $query . "')";
    }
}

$whereClauses[] = "HED.DEL_NBR=0";

$whereClauses = implode(" AND ", $whereClauses);

$query = "SELECT
                DATE(HED.ORD_TS),
                DATE(HED.ORD_END_TS), 
                PPL.NAME,
                PPL.PRSN_NBR,
                COUNT(HED.ORD_NBR) AS JML,
                COALESCE(SUM(HED.TOT_AMT), 0) AS TOT_AMT,
                COALESCE(SUM(HED.TOT_REM), 0) AS TOT_REM
          FROM CMP.RTL_ORD_HEAD HED
          LEFT OUTER JOIN CMP.PEOPLE PPL ON HED.SLS_PRSN_NBR = PPL.PRSN_NBR
          WHERE " . $whereClauses . "
                	AND DATE(HED.ORD_TS) >= '".$BegDt."'
                  AND DATE(HED.ORD_END_TS) <= '".$EndDt."'
          GROUP BY HED.SLS_PRSN_NBR";


$result = mysql_query($query);

// if (mysql_num_rows($result) == 0) {
//     echo "<div class='searchStatus'>Data atau nomor yang dicari tidak ada didalam kumpulan data</div>";
//     exit;
// }

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta charset="utf-8">
    <script>if (top.Pace && !top.Pace.running) top.Pace.restart()</script>

<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />
<link rel="stylesheet" type="text/css" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" media="screen" />

<script type="text/javascript" src="framework/datepicker/js/mootools-1.2.4-core.js"></script>
<script type="text/javascript" src="framework/datepicker/js/mootools-1.2.4.4-more.js"></script>
<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.js"></script>
    <style>
        table.tablesorter tbody tr:hover td {
            background-color: #e6e6e6 !important;
            color: #555;
        }

        /* Header sticky to top when scroll */
        table.tablesorter th,
        .sticky {
            position: -webkit-sticky;
            position: sticky;
            top: 0;
            z-index: 1;
            background: #fff;
        }

        table.tablesorter tbody tr {
            cursor: pointer;
        }

        table.tablesorter thead th:first-child,
        table.tablesorter thead th:nth-child(2),
        table.tablesorter tbody td:nth-child(1),
        table.tablesorter tbody td:nth-child(2) {
            text-align: left;
        }

        table.tablesorter thead th:nth-child(4),
        table.tablesorter thead th:nth-child(5),
        table.tablesorter tbody td:nth-child(4),
        table.tablesorter tbody td:nth-child(5) {
            text-align: right;
        }
    </style>
</head>
<body>
<div class="toolbar">
	<p class="toolbar-left">
		&nbsp;
		<input id="ORD_TS" name="ORD_TS" value="<?php echo $BegDt; ?>" type="text" size="10" class="livesearch" style="text-align:center" />
		<script>
			new CalendarEightysix('ORD_TS', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });
		</script>
		<input id="ORD_END_TS" name="ORD_END_TS" value="<?php echo $EndDt; ?>" type="text" size="10" class="livesearch" style="text-align:center" />
		<script>
			new CalendarEightysix('ORD_END_TS', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });
		</script>
		<span class="fa fa-calendar toolbar fa-lg" style="padding-left:0px;cursor:pointer" onclick="location.href='creativehub-report-sales.php?ORD_TS='+document.getElementById('ORD_TS').value+'&ORD_END_TS='+document.getElementById('ORD_END_TS').value"></span>
	</p>
	<p class="toolbar-right"><span class='fa fa-search fa-flip-horizontal toolbar'></span><input type="text" id="livesearch" class="livesearch" placeholder= "Cari"/></p>
</div>

<div class="searchresult" id="liveRequestResults"></div>


<div id="mainResult">
    <table id="mainTable"
           class="tablesorter table-freeze std-row-alt rowstyle-alt colstyle-alt no-arrow searchTable sortable-onload-show">
        <thead>
        <tr>
            <th class="sortable">No</th>
            <th class="sortable">Nama Sales</th>
            <th class="sortable">Jumlah Nota</th>
            <th class="sortable">Total</th>
            <th class="sortable">Sisa</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $i = 1;
        while ($row = mysql_fetch_array($result)) { ?>
            <tr data-sales="<?php echo $row['PRSN_NBR'] ?>">
                <td><?php echo $i ?></td>
                <td><?php echo $row['NAME'] ?></td>
                <td style="text-align: center"><?php echo $row['JML'] ?></td>
                <td><?php echo number_format($row['TOT_AMT'], 0, ",", ".") ?></td>
                <td><?php echo number_format($row['TOT_REM'], 0, ",", ".") ?></td>
            </tr>
            <?php
            $i++;
        } ?>
        </tbody>
    </table>
</div>
<script>
  function handler () {
    $('#mainTable').tablesorter({ widgets: ['zebra'] })
  }

  function liveSearchInit (inputId_, outputId_, processURI_, emptyString_, mainId_, callback_) {
    let searchTerm = ''
    $(inputId_).on('change keyup', function (evt) {
      if (evt.key === 'Escape' || evt.key === 'Enter') {
        $(this).trigger('blur')
        return
      }
      let url = new URL(processURI_)
      let s = $(this).val()
      if (s !== searchTerm) {
        url.searchParams.set('s', s)
        $(outputId_).load(url.href + ' ' + mainId_, function (response, status, xhr) {
          let resp = $($.parseHTML(response)).filter(outputId_)
          console.info(resp)
          if (resp.length === 0) {
            if (emptyString_) {
              let div = $('<div></div>').
                text(emptyString_).
                addClass('searchStatus')
              $(outputId_).html(div)
            } else {
              $(outputId_).html(response)
            }
          }
          if (typeof callback_ === 'function') callback_()
        })
        searchTerm = s
      }
    })
  }

  window.onload = () => {
    window.focus()
    if (top.Pace) top.Pace.stop()

    handler()
    liveSearchInit('#livesearch', '#mainResult', window.location, '', '#mainTable', handler)

    $('#mainResult').on('click', 'tbody tr', function () {
      let sales_nbr = $(this).data('sales')
      let url = new URL('creativehub-report-sales-detail.php', window.location)
      url.searchParams.set('SLS_PRSN_NBR', sales_nbr)
      window.location = url.href
    })
  }
</script>
</body>
</html>

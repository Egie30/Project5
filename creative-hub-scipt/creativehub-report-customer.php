<?php

require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
require_once "framework/pagination/pagination.php";

$searchQuery = mysql_escape_string(strtoupper($_REQUEST['s']));
$whereClauses = array("HED.BUY_CO_NBR != ''");

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
            OR COM.NAME  LIKE '" . $query . "')";
    }
}

$whereClauses[] = "HED.DEL_NBR=0";

$whereClauses = implode(" AND ", $whereClauses);

$query = "SELECT 
	MONTHNAME(HED.ORD_BEG_TS) AS CSH_MONTHNAME,
	HED.ORD_NBR,
	COALESCE(HED.ORD_TTL, '-') AS ORD_TTL,
	COALESCE(SUM(HED.TOT_AMT), 0) AS TOT_AMT,
	COALESCE(SUM(HED.TOT_REM), 0) AS TOT_REM,
	HED.BUY_CO_NBR,
	COM.NAME AS BUY_NAME
FROM CMP.RTL_ORD_HEAD HED
	LEFT OUTER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR = COM.CO_NBR
WHERE " . $whereClauses . "
GROUP BY HED.BUY_CO_NBR";

$result = mysql_query($query);

if (mysql_num_rows($result) == 0) {
    echo "<div class='searchStatus'>Data atau nomor yang dicari tidak ada didalam kumpulan data</div>";
    exit;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta charset="utf-8">
    <script>if (top.Pace && !top.Pace.running) top.Pace.restart()</script>
    <link rel="stylesheet" type="text/css" media="screen" href="css/screen.css"/>
    <link rel="stylesheet" type="text/css" media="screen" href="framework/pagination/pagination.css"/>
    <link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css"/>
    <link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
    <script src="framework/jquery/jquery-latest.min.js"></script>
    <script src="framework/pagination/pagination.js"></script>
    <script src="framework/tablesorter/jquery.tablesorter.js"></script>
    <script src="framework/functions/default.js"></script>
    <script src='framework/jquery-freezeheader/js/jquery.freezeheader.js'></script>
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

        table.tablesorter tbody td:nth-child(1),
        table.tablesorter tbody td:nth-child(2) {
            text-align: left;
        }

        table.tablesorter tbody td:nth-child(3),
        table.tablesorter tbody td:nth-child(4) {
            text-align: right;
        }
    </style>
</head>
<body>
<div class="toolbar">
    <p class="toolbar-left">&nbsp;</p>
    <p class="toolbar-right">
        <span class="fa fa-search fa-flip-horizontal toolbar"></span>
        <input type="text" id="livesearch" class="livesearch" placeholder="Cari"/>
    </p>
</div>

<div id="mainResult">
    <table id="mainTable"
           class="tablesorter table-freeze std-row-alt rowstyle-alt colstyle-alt no-arrow searchTable sortable-onload-show">
        <thead>
        <tr>
            <th class="sortable" style="text-align:center;">No</th>
            <th class="sortable">Name</th>
            <th class="sortable">Total</th>
            <th class="sortable">Sisa</th>
        </tr>
        </thead>
        <tbody>
        <?php
        while ($row = mysql_fetch_array($result)) { ?>
            <tr data-company="<?php echo $row['BUY_CO_NBR'] ?>">
                <td><?php echo $row['BUY_CO_NBR'] ?></td>
                <td><?php echo $row['BUY_NAME'] ?></td>
                <td><?php echo number_format($row['TOT_AMT'], 0, ",", ".") ?></td>
                <td><?php echo number_format($row['TOT_REM'], 0, ",", ".") ?></td>
            </tr>
            <?php
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
      let buy_co_nbr = $(this).data('company')
      let url = new URL('creativehub-report-customer-detail.php', window.location)
      url.searchParams.set('BUY_CO_NBR', buy_co_nbr)
      window.location = url.href
    })
  }
</script>
</body>
</html>

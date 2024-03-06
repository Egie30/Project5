<?php

require_once "framework/database/connect.php";
require_once "framework/functions/default.php";

$searchQuery = mysql_escape_string(strtoupper($_REQUEST['s']));
$statusId = mysql_escape_string($_GET['ORD_STT_ID']);
$SlsPrsnNbr = mysql_escape_string($_GET['SLS_PRSN_NBR']);

$whereClauses = array("HED.DEL_NBR = 0");

if ($_GET['ORD_STT_ID'] != '') {
    $whereClauses[] = "HED.ORD_STT_ID = '" . $statusId . "' ";
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
			OR COM.NAME LIKE '" . $query . "'
			OR PPL.NAME LIKE '" . $query . "'
			OR HED.ORD_TTL LIKE '" . $query . "'
		)";
    }
}

$whereClauses[] = "HED.SLS_PRSN_NBR = '" . $SlsPrsnNbr . "' ";

$whereClauses = implode(" AND ", $whereClauses);

$query = " SELECT 
	HED.ORD_NBR,
	DATE(HED.ORD_BEG_TS) AS ORD_DTE,
	DATE(HED.ORD_BEG_TS) AS CSH_DTE,
	COALESCE(HED.ORD_TTL, 'Nota') AS ORD_TTL,
	HED.ORD_STT_ID,
	STT.ORD_STT_DESC,
	COALESCE(SUM(HED.TOT_AMT), 0) AS TOT_AMT,
	COALESCE(SUM(HED.TOT_REM), 0) AS TOT_REM,
	HED.BUY_CO_NBR,
	(CASE 
		WHEN HED.BUY_CO_NBR != '' THEN COM.NAME 
		WHEN HED.BUY_PRSN_NBR != '' THEN PPL.NAME
		ELSE 'Tunai' END 
	) AS BUY_NAME
FROM CMP.RTL_ORD_HEAD HED
	INNER JOIN CMP.RTL_ORD_STT STT ON HED.ORD_STT_ID=STT.ORD_STT_ID 
	LEFT OUTER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR = COM.CO_NBR
	LEFT OUTER JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR = PPL.PRSN_NBR
WHERE " . $whereClauses . " 
GROUP BY HED.ORD_NBR
ORDER BY HED.ORD_NBR DESC";
$result = mysql_query($query);
//echo "<pre>" . $query . "</pre>";
if (mysql_num_rows($result) == 0) {
    echo "<div class='searchStatus'>Data atau nomor yang dicari tidak ada didalam kumpulan data</div>";
    exit;
}

// Combobox query
$stt_query = "SELECT STT.ORD_STT_ID, STT.ORD_STT_DESC, STT.ORD_STT_ORD 
FROM CMP.RTL_ORD_STT STT
JOIN ( SELECT DISTINCT ORD_STT_ID FROM CMP.RTL_ORD_HEAD WHERE SLS_PRSN_NBR = " . $SlsPrsnNbr . ") H 
ON H.ORD_STT_ID=STT.ORD_STT_ID
ORDER BY ORD_STT_ORD";

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta charset="utf-8">
    <script>if (top.Pace && !top.Pace.running) top.Pace.restart()</script>
    <link rel="stylesheet" type="text/css" media="screen" href="css/screen.css"/>
    <link rel="stylesheet" type="text/css" media="screen" href="framework/combobox/chosen.css"/>
    <link rel="stylesheet" type="text/css" media="screen" href="framework/pagination/pagination.css"/>
    <link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css"/>
    <link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
    <script src="framework/jquery/jquery-latest.min.js"></script>
    <script src="framework/pagination/pagination.js"></script>
    <script src="framework/tablesorter/jquery.tablesorter.js"></script>
    <script src="framework/functions/default.js"></script>
    <script src='framework/jquery-freezeheader/js/jquery.freezeheader.js'></script>
    <script src="framework/combobox/chosen.jquery.js"></script>
    <script src="framework/combobox/ajax-chosen/ajax-chosen.js"></script>
    <script src="framework/combobox/chosen.default.js"></script>
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

        p.toolbar-left > span {
            display: inline-block;
            float: left;
            margin-right: 15px;
            padding-left: 10px;
            padding-top: 10px;
        }

        select#ORD_STT_ID {
            width: 150px;
        }

        table.tablesorter tbody tr {
            cursor: pointer;
        }

        table.tablesorter tbody td:nth-child(2),
        table.tablesorter tbody td:nth-child(3) {
            text-align: center;
        }

        table.tablesorter tbody td:nth-child(5),
        table.tablesorter tbody td:nth-child(6) {
            text-align: left;
        }

        table.tablesorter tbody td:nth-child(7),
        table.tablesorter tbody td:nth-child(8) {
            text-align: right;
        }

        table.tablesorter tfoot td {
            text-align: right;
            font-weight: bold;
            border-top: 1px solid grey;
        }
    </style>
</head>
<body>
<div class="toolbar">
    <p class="toolbar-left">
        <span>
            <select name="ORD_STT_ID" id="ORD_STT_ID" class="chosen-select" title="Filter Status">
                <?php
                genCombo($stt_query, "ORD_STT_ID", "ORD_STT_DESC", $statusId, "Filter Status");
                ?>
            </select>
        </span>
    </p>
    <p class="toolbar-right">
        <span class="fa fa-search fa-flip-horizontal toolbar"></span>
        <input type="text" id="livesearch" class="livesearch" placeholder="Cari"/>
    </p>
</div>
<div id="mainResult">
    <table id="mainTable"
           class="tablesorter table-freeze std-row-alt rowstyle-alt colstyle-alt no-arrow searchTable sortable-onload-show">
        <thead>
        <tr style="text-align:center">
            <th class='sortable'>Nomor Nota</th>
            <th class='sortable'>Tgl Nota</th>
            <th class='sortable'>Tgl Selesai</th>
            <th class='sortable'>Status</th>
            <th class='sortable'>Customer</th>
            <th class='sortable'>Judul Nota</th>
            <th class='sortable'>Total Nota</th>
            <th class='sortable'>Sisa</th>
        </tr>
        </thead>
        <tbody>
        <?php
        while ($row = mysql_fetch_array($result)) { ?>
            <tr data-order="<?php echo $row['ORD_NBR'] ?>">
                <td><?php echo $row['ORD_NBR']; ?></td>
                <td><?php echo $row['ORD_DTE'] ?></td>
                <td><?php echo $row['CSH_DTE'] ?></td>
                <td><?php echo $row['ORD_STT_DESC']; ?></td>
                <td><?php echo $row['BUY_NAME'] ?></td>
                <td><?php echo $row['ORD_TTL'] ?></td>
                <td><?php echo number_format($row['TOT_AMT'], 0, ",", ".") ?></td>
                <td><?php echo number_format($row['TOT_REM'], 0, ",", ".") ?></td>
            </tr>
            <?php
            $subtotal += $row['SUBTOTAL'];
            $totalAmount += $row['TOT_AMT'];
            $totalRemain += $row['TOT_REM'];
        }
        ?>
        </tbody>
        <tfoot>
        <tr>
            <td colspan="6">Total:</td>
            <td><?php echo number_format($totalAmount, 0, ",", ".") ?></td>
            <td><?php echo number_format($totalRemain, 0, ",", ".") ?></td>
        </tr>
        </tfoot>
    </table>
</div>
<script>
  sls_prsn_nbr = "<?php echo $SlsPrsnNbr ?>"

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

    $('#ORD_STT_ID').on('change', function () {
      let url = new URL(window.location)
      url.searchParams.set('SLS_PRSN_NBR', sls_prsn_nbr)
      url.searchParams.set('ORD_STT_ID', $(this).val())
      $('#mainResult').load(url.href + ' #mainTable', handler)
    })

    $('#mainResult').on('click', 'tbody tr', function () {
      let ord_nbr = $(this).data('order')
      let url = new URL('creativehub-edit.php', window.location)
      url.searchParams.set('ORD_NBR', ord_nbr)
      window.location = url.href
    })
  }
</script>
</body>
</html>

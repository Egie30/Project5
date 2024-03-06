<?php

include "framework/database/connect.php";
include "framework/functions/default.php";
include "framework/security/default.php";

//security
$Security = getSecurity($_SESSION['userID'], "Finance");
$Securitys = getSecurity($_SESSION['userID'], "AddressBook");

$buyPrsnNbr = mysql_escape_string($_GET['BUY_PRSN_NBR']);
$buyCoNbr = mysql_escape_string($_GET['BUY_CO_NBR']);
$searchQuery = trim(mysql_escape_string($_REQUEST['s']));

if ($buyCoNbr != "") {
    $whereString = "BUY_CO_NBR=" . $buyCoNbr;
    $queryString = "BUY_CO_NBR=" . $buyCoNbr;
    if ($buyPrsnNbr != "") {
        $whereString .= " AND BUY_PRSN_NBR=" . $buyPrsnNbr;
        $queryString .= "&BUY_PRSN_NBR=" . $buyPrsnNbr;
    } else {
        $whereString .= " AND BUY_PRSN_NBR IS NULL";
    }
} elseif ($buyPrsnNbr != "") {
    $whereString = "BUY_PRSN_NBR=" . $buyPrsnNbr;
    $queryString = "BUY_PRSN_NBR=" . $buyPrsnNbr;
}
if (($buyPrsnNbr == "0") && ($buyCoNbr == "0")) {
    $whereString = "(BUY_CO_NBR IS NULL AND BUY_PRSN_NBR IS NULL)";
}
//echo $whereString;
$filter_date = str_replace("+", " ", $_GET['FLTR_DATE']);
if ($filter_date != "") {
    $data = explode(" ", $filter_date);
    $data_month = $data[0];
    $data_year = $data[1];
    $whereDte = " AND MONTH(ORD_BEG_TS)='" . $data_month
        . "' AND YEAR(ORD_BEG_TS)='" . $data_year . "' ";
}

if ($searchQuery != "") {
    $searchQ = explode(" ", $searchQuery);
    $whereClause = [];
    foreach ($searchQ as $searchQuery) {
        if ($searchQ == "") {
            continue;
        }
        $whereClause[] = "COM.NAME LIKE '%" . $searchQuery . "%'";
        $whereClause[] = "PPL.NAME LIKE '%" . $searchQuery . "%'";
    }
    $whereDte .= " AND (" . implode(" OR ", $whereClause) . ")";
}

if (($buyPrsnNbr != "") || ($buyCoNbr != "")) {
    $query = "SELECT 
                    COUNT(HED.ORD_NBR) AS NBR_ORD, 
                    YEAR(ORD_BEG_TS) AS ORD_YEAR,
                    MONTH(ORD_BEG_TS) AS ORD_MONTH,
                    COM.NAME AS NAME_CO,
                    PPL.NAME AS NAME_PPL,
                    COM.CO_NBR AS BUY_CO_NBR,
                    PPL.PRSN_NBR AS BUY_PRSN_NBR,
                    SUM(TOT_AMT) AS TOT_AMT,
                    COALESCE(SUM(PAY.TND_AMT),0) AS PYMT_DOWN,
                    SUM(TOT_AMT)-COALESCE(SUM(PAY.TND_AMT),0) AS TOT_REM 
                    FROM CMP.RTL_ORD_HEAD HED 
                    INNER JOIN CMP.RTL_ORD_STT STT ON HED.ORD_STT_ID=STT.ORD_STT_ID 
                    LEFT OUTER JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR=PPL.PRSN_NBR 
                    LEFT OUTER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR=COM.CO_NBR
                    LEFT JOIN (
                        SELECT 
                        PYMT.ORD_NBR,
                        COALESCE(SUM(PYMT.TND_AMT),0) AS TND_AMT
                        FROM CMP.RTL_ORD_PYMT PYMT
                        WHERE PYMT.DEL_NBR = 0
                        GROUP BY PYMT.ORD_NBR
                    ) PAY ON PAY.ORD_NBR = HED.ORD_NBR
                    WHERE TOT_REM>0 
                        AND $whereString 
                        AND HED.DEL_NBR=0
                        $whereDte
                    GROUP BY YEAR(ORD_BEG_TS),MONTH(ORD_BEG_TS),COM.NAME,PPL.NAME,COM.CO_NBR,PPL.PRSN_NBR HAVING (SUM(TOT_AMT)-COALESCE(SUM(PAY.TND_AMT),0))>0 ORDER BY 2";
} else {
    $query = "SELECT 
                    COUNT(HED.ORD_NBR) AS NBR_ORD, 
                    MIN(DATE(ORD_BEG_TS)) AS ORD_TS_MIN,
                    MAX(DATE(ORD_BEG_TS)) AS ORD_TS_MAX,
                    COM.NAME AS NAME_CO,
                    PPL.NAME AS NAME_PPL,
                    COM.CO_NBR AS BUY_CO_NBR,
                    PPL.PRSN_NBR AS BUY_PRSN_NBR,
                    SUM(TOT_AMT) AS TOT_AMT,
                    COALESCE(SUM(PAY.TND_AMT),0) AS PYMT_DOWN,
                    SUM(TOT_AMT)-COALESCE(SUM(PAY.TND_AMT),0) AS TOT_REM 
                    FROM CMP.RTL_ORD_HEAD HED 
                    INNER JOIN CMP.RTL_ORD_STT STT ON HED.ORD_STT_ID=STT.ORD_STT_ID 
                    LEFT OUTER JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR=PPL.PRSN_NBR 
                    LEFT OUTER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR=COM.CO_NBR 
                    LEFT JOIN (
                        SELECT 
                        PYMT.ORD_NBR,
                        COALESCE(SUM(PYMT.TND_AMT),0) AS TND_AMT
                        FROM CMP.RTL_ORD_PYMT PYMT
                        WHERE PYMT.DEL_NBR = 0
                        GROUP BY PYMT.ORD_NBR
                    ) PAY ON PAY.ORD_NBR = HED.ORD_NBR
                    WHERE TOT_REM>0 
                        AND HED.DEL_NBR=0 
                        $whereDte
                    GROUP BY COM.NAME,PPL.NAME,COM.CO_NBR,PPL.PRSN_NBR HAVING (SUM(TOT_AMT)-COALESCE(SUM(PAY.TND_AMT),0))>0 ORDER BY 8 DESC";
}

$query_dte = "SELECT  ORD_BEG_TS,CONCAT(MONTH(ORD_BEG_TS),' ',YEAR(ORD_BEG_TS)) AS DTE,
    CONCAT(CASE 
        WHEN MONTH(ORD_BEG_TS)='1' THEN 'Januari'
        WHEN MONTH(ORD_BEG_TS)='2' THEN 'Februari'
        WHEN MONTH(ORD_BEG_TS)='3' THEN 'Maret'
        WHEN MONTH(ORD_BEG_TS)='4' THEN 'April'
        WHEN MONTH(ORD_BEG_TS)='5' THEN 'Mei'
        WHEN MONTH(ORD_BEG_TS)='6' THEN 'Juni'
        WHEN MONTH(ORD_BEG_TS)='7' THEN 'Juli'
        WHEN MONTH(ORD_BEG_TS)='8' THEN 'Agustus'
        WHEN MONTH(ORD_BEG_TS)='9' THEN 'September'
        WHEN MONTH(ORD_BEG_TS)='10' THEN 'Oktober'
        WHEN MONTH(ORD_BEG_TS)='11' THEN 'November'
        WHEN MONTH(ORD_BEG_TS)='12' THEN 'Desember'
    END,' ',YEAR(ORD_BEG_TS)) AS DTE_DESC,
    SUM(TOT_AMT)-COALESCE(SUM(PAY.TND_AMT),0) AS TOT_REM
FROM CMP.RTL_ORD_HEAD HED 
INNER JOIN CMP.RTL_ORD_STT STT ON HED.ORD_STT_ID=STT.ORD_STT_ID 
LEFT JOIN (
    SELECT 
    PYMT.ORD_NBR,
    COALESCE(SUM(PYMT.TND_AMT),0) AS TND_AMT
    FROM CMP.RTL_ORD_PYMT PYMT
WHERE PYMT.DEL_NBR = 0
GROUP BY PYMT.ORD_NBR
) PAY ON PAY.ORD_NBR = HED.ORD_NBR
WHERE HED.DEL_NBR=0
GROUP BY YEAR(ORD_BEG_TS),MONTH(ORD_BEG_TS) HAVING (SUM(TOT_AMT)-COALESCE(SUM(PAY.TND_AMT),0))>0";

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta charset="utf-8">
    <script>if (top.Pace && !top.Pace.running) top.Pace.restart()</script>
    <link rel="stylesheet" type="text/css" media="screen" href="css/screen.css"/>
    <link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css"/>
    <link rel="stylesheet" type="text/css" media="screen" href="framework/combobox/chosen.css"/>

    <script src="framework/database/jquery.min.js"></script>
    <script src="framework/tablesorter/jquery.tablesorter.js"></script>
    <script src="framework/combobox/chosen.jquery.js"></script>
    <script src="framework/combobox/chosen.default.js"></script>
    <script src="framework/combobox/ajax-chosen/ajax-chosen.js"></script>
    <style>
        p.toolbar-right {
            position: inherit;
        }

        table.tablesorter tbody tr:hover td {
            background-color: #e6e6e6 !important;
            color: #555;
        }

        /* Header sticks to the top when page scroll */
        table.tablesorter th,
        .sticky {
            position: -webkit-sticky;
            position: sticky;
            top: 0;
            z-index: 1;
            background: #fff;
        }

        table.tablesorter tfoot td {
            text-align: right;
            font-weight: bold;
            border-top: 1px solid grey;
        }

        #filter-by-date {
            padding-left: 0;
            margin-bottom: 12px;
            cursor: pointer;
            display: none;
        }
    </style>
</head>
<body>
<div class="toolbar">
    <div class="toolbar-text">
        <p class="toolbar-left" style="float:none;margin-top: 6px;">
            <select id="RCV_DATE" name="RCV_DATE" style="width:150px" class="chosen-select" title="Filter Bulan">
                <?php
                genCombo($query_dte, "DTE", "DTE_DESC", $filter_date, "Semua Data"); ?>
            </select>
            <span class="fa fa-calendar toolbar fa-lg" id="filter-by-date"></span>
        </p>
        <p class="toolbar-right">
            <?php
            if ($Security <= 1 || $Securitys == 1) { ?>
                <span class='fa fa-file-excel-o toolbar' id="export" title="Export to Excel"></span>
                <?php
            } ?>
            <span class='fa fa-search fa-flip-horizontal toolbar'></span>
            <input type="text" id="livesearch" class="livesearch" placeholder="Cari"/>
        </p>
    </div>
</div>
<div id="mainResult">
    <table id="mainTable" class="tablesorter searchTable">
        <thead>
        <tr>
            <th style="text-align:right;">Nota</th>
            <?php
            if (($buyPrsnNbr != "") || ($buyCoNbr != "")) {
                echo '<th>Periode</th>';
            }
            ?>
            <th>Nama</th>
            <th>Total</th>
            <th>Pembayaran</th>
            <th style="text-align:right;">Sisa</th>
        </tr>
        </thead>
        <tbody>
        <?php
        //echo $query;
        $result = mysql_query($query);
        $alt = "";
        $SumTotAmt = 0;
        $SumPymtDown = 0;
        $SumTotRem = 0;
        $col = 2;
        while ($row = mysql_fetch_array($result)) {
            $queryString = "BUY_CO_NBR=0&BUY_PRSN_NBR=0";
            if ($row['BUY_CO_NBR'] != "") {
                $queryString = "BUY_CO_NBR=" . $row['BUY_CO_NBR'];
                if ($row['BUY_PRSN_NBR'] != "") {
                    $queryString .= "&BUY_PRSN_NBR=" . $row['BUY_PRSN_NBR'];
                }
            } elseif ($row['BUY_PRSN_NBR'] != "") {
                $queryString = "BUY_PRSN_NBR=" . $row['BUY_PRSN_NBR'];
            }
            if (($buyPrsnNbr != "") || ($buyCoNbr != "")) {
                $url = "creativehub-list.php?STT=COL&YEAR=" . $row['ORD_YEAR']
                    . "&MONTH=" . $row['ORD_MONTH'] . "&FLTR_DATE="
                    . $_GET['FLTR_DATE'] . "&" . $queryString;
            } else {
                $url = "creativehub-receivables.php?" . $queryString
                    . "&FLTR_DATE=" . $_GET['FLTR_DATE'];
            }
            if (($buyPrsnNbr != "") || ($buyCoNbr != "")) {
                $period = "<td style='text-align:center'>" . $row['ORD_YEAR']
                    . "-" . sprintf('%02d', $row['ORD_MONTH']) . "</td>";
                $col = 3;
            }

            if (($row['BUY_CO_NBR'] == "") && ($row['BUY_PRSN_NBR']) == "") {
                $nama = "Tunai";
            } else {
                $nama = $row['NAME_CO'] . " " . $row['NAME_PPL'];
            }
            $SumTotAmt += $row['TOT_AMT'];
            $SumPymtDown += $row['PYMT_DOWN'];
            $SumTotRem += $row['TOT_REM'];
            ?>
            <tr style="cursor:pointer;" onclick="location.href='<?php echo $url ?>';">
                <td style="text-align:right"><?php echo $row['NBR_ORD'] ?></td>
                <?php echo $period ?>
                <td><?php echo $nama ?></td>
                <td style="text-align:right"><?php echo number_format($row['TOT_AMT'], 0, ",", ".") ?></td>
                <td style="text-align:right"><?php echo number_format($row['PYMT_DOWN'], 0, ",", ".") ?></td>
                <td style="text-align:right"><?php echo number_format($row['TOT_REM'], 0, ",", ".") ?></td>
            </tr>
            <?php
        } ?>
        </tbody>
        <tfoot>
        <tr>
            <td colspan="<?php echo $col ?>">Total
            </td>
            <td><?php echo number_format($SumTotAmt, 0, ",", ".") ?></td>
            <td><?php echo number_format($SumPymtDown, 0, ",", ".") ?></td>
            <td><?php echo number_format($SumTotRem, 0, ",", ".") ?></td>
        </tr>
        </tfoot>
    </table>
</div>
<script>
  function hookTablesorter () {
    $('#mainTable').tablesorter({ widgets: ['zebra'] })
  }

  function loadHandler () {
    hookTablesorter()
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

  $(document).ready(function () {
    window.focus()
    if (top.Pace) top.Pace.stop()

    hookTablesorter()

    liveSearchInit('#livesearch', '#mainResult', window.location,
      'Data tidak ditemukan', '#mainTable', loadHandler)

    function filter () {
      let url = new URL(window.location)
      url.searchParams.set('FLTR_DATE', $(this).val())
      $('#mainResult').load(url.href + ' #mainTable', loadHandler)
    }

    $('#RCV_DATE').on('change', filter)
    $('#filter-by-date').on('click', filter)

    $('#export').on('click', function () {
      let url = new URL('creativehub-excel.php', window.location)
      url.searchParams.set('RPT_TYP', 'creativehub-receivables-excel')
      url.searchParams.set('STT', '<?php echo $_GET['STT'] ?>')
      url.searchParams.set('YEAR', '<?php echo $_GET['YEAR'] ?>')
      url.searchParams.set('MONTH', '<?php echo $_GET['MONTH'] ?>')
      url.searchParams.set('BUY_CO_NBR', '<?php echo $_GET['BUY_CO_NBR'] ?>')
      url.searchParams.set('BUY_PRSN_NBR', '<?php echo $_GET['BUY_PRSN_NBR'] ?>')
      console.info(url.href)
      window.open(url.href, '_blank')
    })
  })
</script>
</body>
</html>



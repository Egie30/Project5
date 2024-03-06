<?php

include "framework/database/connect.php";
include "framework/functions/default.php";
include "framework/security/default.php";
require_once "framework/pagination/pagination.php";

date_default_timezone_set("Asia/Jakarta");

//security
$Security = getSecurity($_SESSION['userID'], "Finance");
$Securitys = getSecurity($_SESSION['userID'], "AddressBook");

$year = mysql_escape_string($_GET['YEAR']);
$month = mysql_escape_string($_GET['MONTH']);
$buyPrsnNbr = mysql_escape_string($_GET['BUY_PRSN_NBR']);
$buyCoNbr = mysql_escape_string($_GET['BUY_CO_NBR']);
$searchQuery = trim(mysql_escape_string($_REQUEST['s']));

//Process filter
$OrdSttId = mysql_escape_string($_GET['STT']);

//Process delete entry
$delete = false;
if ($_GET['DEL'] != "") {
    $query = "UPDATE CMP.RTL_ORD_HEAD SET DEL_NBR=" . $_SESSION['personNBR'] . " WHERE ORD_NBR=" . $_GET['DEL'];
    //echo $query;
    $result = mysql_query($query);

    /*
    $query = "UPDATE CMP.RTL_ORD_DET SET DEL_NBR=" . $_SESSION['personNBR'] . " WHERE ORD_NBR=" . $_GET['DEL'];
    //echo $query;
    $result = mysql_query($query);
*/

    $OrdSttId = "ACT";
    $delete = true;
}
//Continue process filter
$activePeriod = 3;
$badPeriod = 12;
if ($OrdSttId == "ALL") {
    $where = "WHERE HED.ORD_STT_ID LIKE '%' AND HED.DEL_NBR=0";
} elseif ($OrdSttId == "CP") {
    $where = "WHERE HED.ORD_STT_ID='CP' 
    AND TIMESTAMPADD(MONTH,$activePeriod,ORD_TS)>=CURRENT_TIMESTAMP 
    AND HED.DEL_NBR=0";
} elseif ($OrdSttId == "DUE") {
    $where = "WHERE TOT_REM>0 
    AND DATE_ADD(CMP_TS,INTERVAL COALESCE(PAY_TERM,0) DAY)<=CURRENT_TIMESTAMP 
    AND HED.DEL_NBR=0";
} elseif ($OrdSttId == "COL") {
    $buyPrsnNbr = $_GET['BUY_PRSN_NBR'];
    $buyCoNbr = $_GET['BUY_CO_NBR'];
    if ($buyCoNbr != "") {
        $whereString = " AND BUY_CO_NBR=" . $buyCoNbr;
        if ($buyPrsnNbr != "") {
            $whereString .= " AND BUY_PRSN_NBR=" . $buyPrsnNbr;
        }
    } else {
        if ($buyPrsnNbr != "") {
            $whereString = " AND BUY_PRSN_NBR=" . $buyPrsnNbr;
        }
    }
    if (($buyPrsnNbr == "0") && ($buyCoNbr == "0")) {
        $whereString = " AND (BUY_CO_NBR IS NULL AND BUY_PRSN_NBR IS NULL)";
    }
    $where = "WHERE HED.DEL_NBR=0 " . $whereString . " 
    AND YEAR(ORD_TS)=" . $_GET['YEAR'] . " 
    AND MONTH(ORD_TS)=" . $_GET['MONTH'] . " 
    AND TOT_REM>0";
} elseif ($OrdSttId == "ACT") {
    $buyPrsnNbr = mysql_escape_string($_GET['BUY_PRSN_NBR']);
    $buyCoNbr = mysql_escape_string($_GET['BUY_CO_NBR']);
    $year = mysql_escape_string($_GET['YEAR']);
    $month = mysql_escape_string($_GET['MONTH']);

    if ($buyCoNbr != "") {
        $whereString = " AND BUY_CO_NBR=" . $buyCoNbr;
        if ($buyPrsnNbr != "") {
            $whereString .= " AND BUY_PRSN_NBR=" . $buyPrsnNbr;
        }
    } else {
        if ($buyPrsnNbr != "") {
            $whereString = " AND BUY_PRSN_NBR=" . $buyPrsnNbr;
        }
    }
    if (($buyPrsnNbr == "0") && ($buyCoNbr == "0")) {
        $whereString = " AND (BUY_CO_NBR IS NULL AND BUY_PRSN_NBR IS NULL)";
    }
    $where = "WHERE (HED.ORD_STT_ID!='CP' OR (HED.ORD_STT_ID='CP' 
    AND TIMESTAMPADD(MONTH,$activePeriod,ORD_TS)>=CURRENT_TIMESTAMP) 
    OR (TOT_REM>0 AND TIMESTAMPADD(MONTH,$badPeriod,ORD_TS)>=CURRENT_TIMESTAMP)) 
    AND HED.DEL_NBR=0";
} else {
    $where = "WHERE HED.ORD_STT_ID='" . $OrdSttId . "' AND HED.DEL_NBR=0";
}

if ($searchQuery != "") {
    $where .= " AND (ORD_TTL LIKE '%" . $searchQuery . "%' OR PPL.NAME LIKE '%" . $searchQuery
        . "%' OR COM.NAME LIKE '%" . $searchQuery . "%' OR ORD_NBR LIKE '%" . $searchQuery . "%')";
}

$query = "SELECT NBR FROM CDW.PRN_DIG_TOP_CUST";
$result = mysql_query($query);
while ($row = mysql_fetch_array($result)) {
    $TopCusts[] = strval($row['NBR']);
}

$query = "SELECT HED.ORD_NBR,IVC_PRN_CNT,ORD_TS,HED.ORD_STT_ID,
        ORD_STT_DESC,BUY_PRSN_NBR,
        PPL.NAME AS NAME_PPL,
        COM.NAME AS NAME_CO,
        BUY_CO_NBR,REF_NBR,ORD_TTL,DUE_TS,FEE_MISC,
        TOT_AMT,PYMT_DOWN,PYMT_REM,TOT_REM,CMP_TS,PU_TS,SPC_NTE,
        HED.CRT_TS,HED.CRT_NBR,HED.UPD_TS,HED.UPD_NBR,CMP_TS,
        DATEDIFF(DATE_ADD(CMP_TS,INTERVAL COALESCE(COM.PAY_TERM,0) DAY),CURRENT_TIMESTAMP) AS PAST_DUE
                    FROM CMP.RTL_ORD_HEAD HED
                    INNER JOIN CMP.RTL_ORD_STT STT ON HED.ORD_STT_ID=STT.ORD_STT_ID
                    LEFT OUTER JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR=PPL.PRSN_NBR
                    LEFT OUTER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR=COM.CO_NBR $where
                    ORDER BY ORD_NBR DESC";
//echo "<pre>".$query."</pre>";

$pagination = pagination($query, 100);
$result = mysql_query($pagination['query']);

if (mysql_num_rows($result) == 0) {
    echo "<div class='searchStatus'>Nama atau nomor yang dicari tidak ada didalam kumpulan data</div>";
    exit();
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <script>if (top.Pace) top.Pace.restart()</script>
    <link rel="stylesheet" media="screen" href="css/screen.css"/>
    <link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
    <link rel="stylesheet" media="screen" href="framework/tablesorter/themes/nestor/style.css"/>
    <link rel="stylesheet" media="screen" href="framework/pagination/pagination.css"/>

    <script src="framework/functions/default.js"></script>
    <script src="framework/database/jquery.min.js"></script>
    <script src='framework/livesearch/livesearch.js'></script>
    <script src="framework/tablesorter/jquery.tablesorter.js"></script>
    <style>
        table.tablesorter tbody tr:hover td {
            background-color: #e6e6e6 !important;
            color: #555;
        }

        table.tablesorter tbody tr {
            transition: background 0.1s ease-in;
            cursor: pointer;
        }

        table.tablesorter thead tr th {
            min-width: max-content;
        }

        /* Break text in long title */
        table.tablesorter tbody tr td:nth-child(3) {
            overflow-wrap: anywhere;
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
    </style>
</head>
<body>
<div class="toolbar">
    <p class="toolbar-left">
        <a href="creativehub-edit.php?ORD_NBR=-1">
            <span class='fa fa-plus toolbar' title="Tambah Nota"></span>
        </a>
    </p>
    <p class="toolbar-right">
        <?php
        if ($Security <= 1 || $Securitys == 1) { ?>
            <span title="Export to Excel" class='fa fa-file-excel-o toolbar' id="export"></span>
            <?php
        } ?>
        <span class='fa fa-search fa-flip-horizontal toolbar'></span>
        <input type="text" id="livesearch" class="livesearch" placeholder="Cari"/>
    </p>
</div>
<div id="mainResult">
    <table id="mainTable" class="tablesorter searchTable">
        <thead>
        <tr>
            <th class="sortable" style="text-align:right;width:5%">No.</th>
            <th class="nosort"></th>
            <th>Judul</th>
            <th>Pemesan</th>
            <th style="width:7%;">Pesan</th>
            <th style="width:7%;">Status</th>
            <?php
            if (($OrdSttId != "DUE") && ($OrdSttId != "COL")) {
                echo "<th style='width:7%;'>Janji</th>";
            }
            ?>
            <th style="width:7%;">Jadi</th>
            <?php
            if (($OrdSttId == "DUE") || ($OrdSttId == "COL")) {
                echo "<th style='width:7%;'>Jatuh Tempo</th>";
            }
            ?>
            <th style="width:7%;">Jumlah</th>
            <th>Sisa</th>
        </tr>
        </thead>
        <tbody>
        <?php
        while ($row = mysql_fetch_array($result)) {
            //Traffic light control
            $due = strtotime($row['DUE_TS']);
            $OrdSttId = $row['ORD_STT_ID'];
            $back = "";
            if ((strtotime("now") > $due) && (in_array($OrdSttId, ["NE", "RC", "QU", "PR", "FN"]))) {
                $back = "print-digital-red";
            } elseif ((strtotime("now + " . $row['JOB_LEN_TOT'] . " minute") > $due)
                && (in_array($OrdSttId, ["NE", "RC", "QU", "PR", "FN"]))
            ) {
                $back = "print-digital-yellow";
            }

            $icon = [];
            if (in_array($row['BUY_CO_NBR'], $TopCusts)) {
                $icon[] = "fa-star";
            }
            if ($row['SPC_NTE'] != "" && $row['SPC_NTE'] != "NULL") {
                $icon[] = ['icon' => "fa-comment listable", 'title' => "Ada Catatan"];
            }
            if ($row['DL_CNT'] > 0) {
                $icon[] = "fa-truck";
            }
            if ($row['PU_CNT'] > 0) {
                $icon[] = "fa-shopping-cart";
            }
            if ($row['NS_CNT'] > 0) {
                $icon[] = "fa-flag listable";
            }
            if ($row['IVC_PRN_CNT'] > 0) {
                $icon[] = ['icon' => "fa-print listable", 'title' => "Invoice Tercetak"];
            }
            if ($row['SLM_HRS'] >= 48 && $row['ORD_STT_ID'] != "CP") {
                $icon[] = ['icon' => "fa-history listable", 'title' => "Jatuh Tempo"];
            }
            ?>
            <tr data-order="<?php echo $row['ORD_NBR'] ?>" title="<?php echo $row['ORD_TTL'] ?>">
                <td style='text-align:right'><?php echo $row['ORD_NBR'] ?></td>
                <td style='text-align:left;white-space:nowrap'>
                    <?php
                    foreach ($icon as $i) { ?>
                        <div class="listable">
                            <span class="fa <?php echo $i['icon'] ?>" title="<?php echo $i['title'] ?>"></span>
                        </div>
                        <?php
                    } // foreach ?>
                </td>
                <td><?php echo $row['ORD_TTL'] ?></td>
                <td><?php echo $row['NAME_PPL'] . " " . $row['NAME_CO'] ?></td>
                <td style='text-align:center'><?php echo parseDateShort($row['ORD_TS']) ?></td>
                <td style='text-align:center'><?php echo $row['ORD_STT_DESC'] ?></td>
                <td style='text-align:center;white-space:nowrap'>
                    <div class='<?php echo $back ?>'><?php echo parseDateShort($row['DUE_TS']) . " " . parseHour($row['DUE_TS']) . ":"
                            . parseMinute($row['DUE_TS']) ?></div>
                </td>
                <td style='text-align:center'><?php echo parseDateShort($row['CMP_TS']) ?></td>
                <?php
                if (($OrdSttId == "DUE") || ($OrdSttId == "COL")) {
                    echo "<td style='text-align:right'>" . $row['PAST_DUE'] . "</td>";
                } ?>
                <td style='text-align:right;'><?php echo number_format($row['TOT_AMT'], 0, ",", ".") ?></td>
                <td style='text-align:right;'><?php echo number_format($row['TOT_REM'], 0, ",", ".") ?></td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
    <?php
    buildPagination($pagination, "creativehub-list.php");
    ?>
</div>

<script>
  const del = '<?php echo  $delete ?>'
  if (del) top.leftmenu.contentWindow.reload()

  function hookTablesorter () {
    $('#mainTable').tablesorter({
      widgets: ['zebra'],
      headers: {
        4: { sorter: 'shortDate' },
        6: { sorter: 'shortDate' },
        7: { sorter: 'shortDate' },
        8: { sorter: 'ipAddress' },
        9: { sorter: 'ipAddress' },
      },
    })
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
              let div = $('<div></div>').text(emptyString_).addClass('searchStatus')
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

    liveSearchInit('#livesearch', '#mainResult', window.location, null, '#mainTable', loadHandler)

    $('#mainResult').on('click', 'tbody tr', function () {
      const ord_nbr = $(this).data('order')
      let url = new URL('creativehub-edit.php', window.location)
      url.searchParams.set('BEG', 'LIST')
      url.searchParams.set('ORD_NBR', ord_nbr)
      window.location = url.href
    })

    $('#export').on('click', function () {
      let url = new URL('creativehub-excel.php', window.location)
      url.searchParams.set('RPT_TYP', 'creativehub-list-excel')
      url.searchParams.set('STT', '<?php echo  $_GET['STT'] ?>')
      url.searchParams.set('YEAR', '<?php echo  $_GET['YEAR'] ?>')
      url.searchParams.set('MONTH', '<?php echo  $_GET['MONTH'] ?>')
      url.searchParams.set('BUY_CO_NBR', '<?php echo  $_GET['BUY_CO_NBR'] ?>')
      url.searchParams.set('BUY_PRSN_NBR', '<?php echo  $_GET['BUY_PRSN_NBR'] ?>')
      window.open(url.href, '_blank')
    })

    $(document).on('keyup', function (evt) {
      if (evt.key === 's') {
        $('#livesearch').trigger('focus')
      }
    })
  })
</script>
</body>
</html>

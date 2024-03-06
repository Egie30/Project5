<?php

include "framework/database/connect.php";
include "framework/functions/default.php";
require_once "framework/pagination/pagination.php";

$searchQuery = trim(mysql_escape_string($_REQUEST['s']));

if ($searchQuery != "") {
    $where = "AND (WIFI.WIFI_UNM LIKE '%" . $searchQuery . "%' )";
}

if ($_GET['DEL'] != "") {
    $DEL = mysql_escape_string($_GET['DEL']);
    $query = "UPDATE CMP.WIFI_VCHR SET DEL_NBR=" . $_SESSION['personNBR'] . " WHERE WIFI_NBR=" . $DEL;
    $result = mysql_query($query);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta charset="utf-8">
    <script>if (top.Pace && !top.Pace.running) top.Pace.restart()</script>
    <link rel="stylesheet" type="text/css" media="screen" href="css/screen.css"/>
    <link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" media="screen" href="framework/pagination/pagination.css"/>
    <link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css">
    <script type="text/javascript" src="framework/jquery/jquery-latest.min.js"></script>
    <script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>
    <style>
        h2 {
            margin-block-start: 0;
            padding-top: 0.83em;
        }

        table {
            width: 100%;
        }

        table tr {
            /*cursor: pointer;*/
        }

        table tr.terpakai
        td:not(:first-child):not(:nth-child(5)):not(:last-child) {
            /*text-decoration: line-through;*/
            position: relative;
            white-space: nowrap;
        }

        table tr.terpakai
        td:not(:first-child):not(:nth-child(5)):not(:last-child):after {
            border-top: 2px solid red;
            position: absolute;
            content: "";
            right: 40%;
            top: 50%;
            left: 40%;
        }

        table tr:nth-child(even) {
            background: #f6f6f6;
        }

        table td:last-child {
            text-align: right;
        }

        table td:last-child span {
            float: left;
        }

        /*table.tablesorter tbody tr:hover td {*/
        /*    background-color: #e6e6e6 !important;*/
        /*    color: #555;*/
        /*}*/

        table.tablesorter tbody td:nth-child(1) {
            text-align: left;
            width: 5%;
        }

        table.tablesorter tbody td {
            text-align: center;
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

        span.btn {
            display: inline-block;
            margin-top: 8px;
        }

        span.btn button {
            padding: 4px;
            border: 1px solid #dddddd;
            background: #fff;
            /*color: #959fb0;*/
            color: #777777;
            border-radius: 4px;
        }

        span.btn button:hover {
            cursor: pointer;
            background: #ddd;
        }
    </style>
</head>
<body>
<div class="toolbar">
    <p class="toolbar-left">
        <!--        <a href="#" id="Tambah">-->
        <!--            <span class="fa fa-plus toolbar" title="Tambah Voucher"></span>-->
        <!--        </a>-->
        <span class="btn" style="display: inline-block">
            <button><i class="fa fa-file-excel-o"></i> Import</button>
        </span>
    </p>
    <p class="toolbar-right">
        <span class="fa fa-search fa-flip-horizontal toolbar"></span>
        <input type="search" id="livesearch" class="livesearch" value="<?php echo $searchQuery ?>" placeholder="Cari">
    </p>
</div>
<div id="mainResult">
    <table id="mainTable" class="tablesorter searchTable">
        <thead>
        <tr>
            <th>No</th>
            <th>Username</th>
            <th>Password</th>
            <th>Durasi</th>
            <th>Status</th>
            <th>Tanggal Import</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $query = "SELECT * FROM CMP.WIFI_VCHR WIFI 
        LEFT JOIN CMP.RTL_ORD_TYP TYP ON TYP.RTL_ORD_TYP=WIFI.TYP_NBR WHERE WIFI.DEL_NBR=0 " . $where;
        $pagination = pagination($query, 50);
        $result = mysql_query($pagination['query']);
        while ($typ = mysql_fetch_array($result)) {
            $status = $typ['REF_NBR'] != 0 ? 'Terpakai' : 'Tersedia';
            ?>
            <tr data-id="<?php echo $typ['WIFI_NBR'] ?>"
                class="<?php echo strtolower($status) ?>">
                <td><?php echo $typ['WIFI_NBR'] ?></td>
                <td><?php echo $typ['WIFI_UNM'] ?></td>
                <td><?php echo preg_replace("/(?!\w{4})(.)/", "*", $typ['WIFI_PWD']) ?></td>
                <td><?php echo $typ['RTL_ORD_DESC'] ?></td>
                <td><?php echo $status ?></td>
                <td><?php echo parseDateShort($typ['CRT_TS']) ?></td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
    <?php
    buildPagination($pagination, "creative-voucher.php");
    ?>
</div>
<script>
  function loadHandler () {
    $('#mainTable').tablesorter({
      widgets: ['zebra'],
      sortList: [[0, 0]],
    })
  }

  window.onload = () => {
    window.focus()
    if (top.Pace) top.Pace.stop()

    let searchTerm = ''
    $('#livesearch').on('change keyup', function (evt) {
      if (evt.key === 'Escape' || evt.key === 'Enter') {
        $(this).trigger('blur')
        return
      }
      let url = new URL(window.location)
      let s = this.value
      if (s !== searchTerm) {
        url.searchParams.set('s', s)
        url.searchParams.delete('page')
        $('#mainResult').load(url.href + ' #mainTable', loadHandler)
        searchTerm = s
      }
    })

    $('#Tambah').on('click', function (e) {
      let url = new URL('creativehub-voucher-edit.php', window.location.href)
      url.searchParams.append('ID', '-1')
      window.location.href = url.href
      return false
    })

    $('button').on('click', function (e) {
      window.location.href = 'creativehub-voucher-import.php'
    })

    $(document).on('keyup', function (evt) {
      if (evt.key === 's') {
        $('#livesearch').trigger('focus')
      }
    })

    $('#mainTable').tablesorter({ widgets: ['zebra'], sortList: [[0, 0]] })
  }
</script>
</body>
</html>

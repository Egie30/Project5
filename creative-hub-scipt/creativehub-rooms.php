<?php

include "framework/database/connect.php";

$searchQuery = trim(mysql_escape_string($_REQUEST['s']));

if ($searchQuery != "") {
    $where = "AND (ROOM.RM_DESC LIKE '%" . $searchQuery . "%' )";
}

if ($_GET['DEL'] != "") {
    $DEL = mysql_escape_string($_GET['DEL']);
    $query = "UPDATE CMP.ROOM SET DEL_NBR=" . $_SESSION['personNBR'] . " WHERE RM_NBR=" . $DEL;
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
    <link rel="stylesheet" href="framework/tablesorter/themes/nestor/style.css">
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
            cursor: pointer;
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

        table.tablesorter tbody tr:hover td {
            background-color: #e6e6e6 !important;
            color: #555;
        }

        table.tablesorter tbody td:nth-child(2) {
            width: 80%;
            text-align: center;
        }

        table.tablesorter tbody td:nth-child(3) {
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

    </style>
</head>
<body>
<div class="toolbar">
    <p class="toolbar-left">
        <a href="#" id="Tambah">
            <span class="fa fa-plus toolbar" title="Tambah Room"></span>
        </a>
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
            <th>Deskripsi</th>
            <th>Warna</th>
            <th>Kapasitas</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $i = 1;
        $query = "SELECT * FROM CMP.ROOM ROOM WHERE ROOM.DEL_NBR=0 " . $where;
        $result = mysql_query($query);
        while ($typ = mysql_fetch_array($result)) { ?>
            <tr data-id="<?php echo $typ['RM_NBR'] ?>">
                <td><?php echo $i ?></td>
                <td><?php echo $typ['RM_DESC'] ?></td>
                <td>
                    <div style="color:white;
                    background:<?php echo $typ['RM_COLR'] ?>;
                    border-radius: 4px">&nbsp;
                        <?php #echo $typ['RM_COLR'] ?>
                    </div>
                </td>
                <td><?php echo $typ['RM_CPCTY'] ?></td>
            </tr>
            <?php
            $i++;
        }
        ?>
        </tbody>
    </table>
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
        $('#mainResult').load(url.href + ' #mainTable', loadHandler)
        searchTerm = s
      }
    })

    $('#mainResult').on('click', '.tablesorter tbody tr', function (e) {
      let id = e.currentTarget.dataset.id
      let url = new URL('creativehub-room-edit.php', window.location.href)
      url.searchParams.append('ID', id)
      window.location.href = url.href
    })

    $('#Tambah').on('click', function (e) {
      let url = new URL('creativehub-room-edit.php', window.location.href)
      url.searchParams.append('ID', '-1')
      window.location.href = url.href
      return false
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

<?php
include 'framework/database/connect.php';
include 'framework/security/default.php';
if (isset($_GET['DEL'])) {
    mysql_query("DELETE FROM PRN_DIG_A3_CNTR_LOG WHERE NBR = " . $_GET['DEL']);
}
$Security = getSecurity($_SESSION['userID'], "DigitalPrint");

$MONTH = date('m');

if (isset($_GET['MONTH'])) {
    $MONTH = $_GET['MONTH'];
}

?>
<!DOCTYPE html>
<html>
<head>
    <script>parent.Pace.restart();</script>
    <link rel="stylesheet" type="text/css" media="screen" href="css/screen.css"/>
    <link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css"/>
    <link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css"/>
    <link rel="stylesheet" href="framework/combobox/chosen.css"/>

    <style>
        thead th {
            /*border:1px solid #cacbcf;*/
        }
    </style>

    <script type="text/javascript" src="framework/database/jquery.min.js"></script>
    <script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>
    <script src="framework/combobox/chosen.jquery.js" type="text/javascript"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $("#mainTable").tablesorter({widgets: ["zebra"]});
            $('.chosen-select').chosen();
        });
    </script>
</head>
<body>
<div class="toolbar">
    <p class="toolbar-left">
        <?php
        if ($Security <= 2) {
            ?>
            <a href="print-digital-a3-counter-log-edit.php?NBR=0">
                <span class="fa fa-plus toolbar" style="cursor:pointer"></span>
            </a>
            <?php
        }
        ?>
    </p>
    <p style="float: right;">
        <select name="MONTH" class="chosen-select" style="width: 150px;"
                onchange="location.href='print-digital-a3-counter-log.php?MONTH='+this.value;">
            <?php
            for ($i = 1; $i <= 12; $i++) {
                if ($i == $MONTH) {
                    $select = 'selected=""';
                } else {
                    $select = '';
                }
                echo '<option value="' . $i . '" ' . $select . '>' . date('F', strtotime(date('d-' . $i . '-Y'))) . '</option>';
            }
            ?>
        </select>
    </p>
</div>
<div class="searchresult" id="liveRequestResults"></div>

<div id="mainResult">
    <table id="mainTable" class="tablesorter searchTable">
        <thead>
        <tr>
            <th class="sortable" rowspan="2">Tanggal</th>
            <th class="sortable" rowspan="2">Mesin</th>
            <th class="sortable" rowspan="2">Petugas Pagi</th>
            <th class="sortable" rowspan="2">Petugas Malam</th>
            <th class="sortable" colspan="2">Full Color</th>
            <th class="sortable" colspan="2">Black/ White</th>
            <th class="sortable" colspan="2">Total Counter</th>
            <th class="sortable" colspan="3">Prestasi</th>
        </tr>
        <tr>
            <th>Open</th>
            <th>Close</th>
            <th>Open</th>
            <th>Close</th>
            <th>Open</th>
            <th>Close</th>
            <th>FC</th>
            <th>BW</th>
            <th>Total</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $rs = mysql_query("SELECT LOG.*, PPL_PAGI.NAME AS NAME_PAGI, PPL_MALAM.NAME AS NAME_MALAM, EQP.PRN_DIG_EQP_DESC AS EQP
                            FROM PRN_DIG_A3_CNTR_LOG LOG
                            LEFT JOIN PEOPLE PPL_PAGI ON PPL_PAGI.PRSN_NBR = LOG.PRSN_NBR_OPN
                            LEFT JOIN PEOPLE PPL_MALAM ON PPL_MALAM.PRSN_NBR= LOG.PRSN_NBR_CLSE
                            LEFT JOIN PRN_DIG_EQP EQP ON EQP.PRN_DIG_EQP = LOG.PRN_DIG_EQP
                            WHERE MONTH(LOG_DTE) = " . $MONTH . "
                            ORDER BY LOG_DTE");
        while ($row = mysql_fetch_array($rs)) {
            $achv_fc = $row['FC_CLSE'] - $row['FC_OPN'];
            $achv_bw = $row['BW_CLSE'] - $row['BW_OPN'];
            $achv_total = $achv_fc + $achv_bw;
            ?>
            <tr style="cursor: pointer;"
                onclick="location.href='print-digital-a3-counter-log-edit.php?NBR=<?php echo $row['NBR']; ?>';">
                <td align="center"><?php echo date('d-m-Y', strtotime($row['LOG_DTE'])); ?></td>
                <td><?php echo $row['EQP']; ?></td>
                <td><?php echo $row['NAME_PAGI']; ?></td>
                <td><?php echo $row['NAME_MALAM']; ?></td>
                <td align="right"><?php echo number_format($row['FC_OPN'], 0, ',', '.'); ?></td>
                <td align="right"><?php echo number_format($row['FC_CLSE'], 0, ',', '.'); ?></td>
                <td align="right"><?php echo number_format($row['BW_OPN'], 0, ',', '.'); ?></td>
                <td align="right"><?php echo number_format($row['BW_CLSE'], 0, ',', '.'); ?></td>
                <td align="right"><?php echo number_format($row['TC_OPN'], 0, ',', '.'); ?></td>
                <td align="right"><?php echo number_format($row['TC_CLSE'], 0, ',', '.'); ?></td>
                <td align="right"><?php echo number_format($achv_fc, 0, ',', '.'); ?></td>
                <td align="right"><?php echo number_format($achv_bw, 0, ',', '.'); ?></td>
                <td align="right"><?php echo number_format($achv_total, 0, ',', '.'); ?></td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
</div>
</body>
</html>
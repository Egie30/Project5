<?php
include "framework/database/connect.php";
include "framework/database/connect-cloud.php";

$del = $_GET['DEL_A'];
if (isset($del)) {
    #delete master
    mysql_query("DELETE FROM PRN_DIG_BRKR_PLAN_TYP WHERE PLAN_TYP = '$del'");
    mysql_query("DELETE FROM PRN_DIG_BRKR_PLAN_TYP WHERE PLAN_TYP = '$del'", $cloud);
    #delete detail
    mysql_query("DELETE FROM PRN_DIG_BRKR_TYP_CAT WHERE PLAN_TYP = '$del'");
    mysql_query("DELETE FROM PRN_DIG_BRKR_TYP_CAT WHERE PLAN_TYP = '$del'", $cloud);
    mysql_query("DELETE FROM PRN_DIG_BRKR_TYP_EQP WHERE PLAN_TYP = '$del'");
    mysql_query("DELETE FROM PRN_DIG_BRKR_TYP_EQP WHERE PLAN_TYP = '$del'", $cloud);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8"/>
    <script>parent.Pace.restart();</script>
    <link rel="stylesheet" type="text/css" media="screen" href="css/screen.css"/>
    <link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css"/>
    <link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css"/>

    <script type="text/javascript" src="framework/functions/default.js"></script>
    <script src="framework/database/jquery.min.js"></script>
    <script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
    <script type="text/javascript" src="framework/tablesorter/jquery-latest.js"></script>
    <script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>
</head>
<body>
<div class="toolbar">
    <p class="toolbar-left"><a href="print-digital-komisi-edit.php?PLAN_TYP=0"><span class='fa fa-plus toolbar'
                                                                                     style="cursor:pointer"
                                                                                     onclick="location.href ="></span></a>
    </p>
    <p class="toolbar-right">
        <span class='fa fa-search fa-flip-horizontal toolbar'></span>
        <input type="text" id="livesearch" class="livesearch"/>
    </p>
</div>

<div class="searchresult" id="liveRequestResults"></div>

<div id="mainResult">
    <table id="mainTable" class="tablesorter searchTable">
        <thead>
        <tr>
            <th class="sortable" style="text-align:right;">No.</th>
            <th class="sortable">Jenis Broker</th>
            <th class="sortable">Keterangan</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $query = "SELECT * FROM PRN_DIG_BRKR_PLAN_TYP";
        $rs = mysql_query($query);

        $i = 1;
        while ($row = mysql_fetch_array($rs)) {
            ?>
            <tr style="cursor: pointer;"
                onclick="location.href = 'print-digital-komisi-edit.php?PLAN_TYP=<?php echo $row['PLAN_TYP']; ?>';">
                <td><?php echo $i++; ?></td>
                <td><?php echo $row['PLAN_TYP']; ?></td>
                <td><?php echo $row['PLAN_DESC']; ?></td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
</div>
<br/>
</body>
</html>
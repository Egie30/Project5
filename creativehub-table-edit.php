<?php

include "framework/database/connect.php";

$tableNbr = mysql_escape_string($_GET['ID']);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tableNbr = mysql_escape_string($_POST['ID']);
    $Desc = mysql_escape_string($_POST['NAME']);
    $Capacity = mysql_escape_string($_POST['CPCTY']);

    if ($Capacity == "") {
        $Capacity = "NULL";
    }

    if ($tableNbr == "-1") {
        $query = "INSERT INTO CMP.TABEL (TBL_DESC, TBL_CPCTY, CRT_TS, CRT_NBR) 
        VALUES ('" . $Desc . "'," . $Capacity . ",CURRENT_TIMESTAMP," . $_SESSION['personNBR'] . ")";
        mysql_query($query);
//        echo "<pre>" . $query . "</pre>";
        $query = "SELECT MAX(TBL_NBR) AS NBR FROM " . $tabletable;
        $result = mysql_fetch_assoc(mysql_query($query));
        $tableNbr = $result['NBR'];
    } else {
        $query = "UPDATE CMP.TABEL
        SET TBL_DESC='" . $Desc . "',
        TBL_CPCTY=" . $Capacity . ",
        UPD_TS=CURRENT_TIMESTAMP,
        UPD_NBR=" . $_SESSION['personNBR'] . "
        WHERE TBL_NBR=" . $tableNbr;
        mysql_query($query);
    }
}

$title = "Edit Meja";
$nbr = $tableNbr;
if ($tableNbr == "-1") {
    $title = "Meja Baru";
    $nbr = "Baru";
} else {
    $query = "SELECT * FROM CMP.TABEL WHERE TBL_NBR=" . $tableNbr;
    $result = mysql_query($query);
    $row = mysql_fetch_assoc($result);
    $RoomDesc = $row['TBL_DESC'];
    $Capacity = $row['TBL_CPCTY'];
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
    <link rel="stylesheet" href="framework/combobox/chosen.css">
    <style>
        h2 {
            margin-block-start: 0;
            padding-top: 0.83em;
        }

        span.toolbar {
            cursor: pointer;
        }
    </style>
</head>
<body>
<?php
if ($tableNbr != '-1') { ?>
    <div class="toolbar-only">
        <p class="toolbar-left">
            <a href="javascript:void(0)"
               onclick="deleteAction()">
                <span class="fa fa-trash toolbar"></span>
            </a>
        </p>
    </div>
    <?php
} ?>
<!--<h3>Kategori: --><?php
//= $CATEGORY ?><!--</h3>-->

<form method="post" action="" class="mainResult">
    <p></p>
    <h2><?php echo $title ?></h2>
    <input type="hidden" name="ID" value="<?php echo $tableNbr ?>">
    <br>
    <div>
        <label for="NAMA">Nama Meja</label><br>
        <input type="text" id="NAME" name="NAME" value="<?php echo $RoomDesc ?>">
    </div>
    <div>
        <label for="CPCTY">Kapasitas</label><br>
        <input type="number" id="CPCTY" name="CPCTY" value="<?php echo $Capacity ?>">
    </div>
    <br>
    <input class="process" type="submit" value="Simpan">
</form>
<script src="framework/database/jquery.min.js" type="text/javascript"></script>
<script src="framework/combobox/chosen.jquery.js" type="text/javascript"></script>
<script>
  window.onload = () => {
    window.focus()
    if (top.Pace) top.Pace.stop()
  }

  top.document.getElementById('catDeleteYes').onclick = function () {
    window.location = "creativehub-tables.php?DEL=<?php echo $tableNbr ?>"
    top.document.getElementById('catDelete').style.display = 'none'
    top.document.getElementById('fade').style.display = 'none'
  }

  const deleteAction = function (e) {
    window.scrollTo(0, 0)
    top.document.getElementById('catDelete').style.display = 'block'
    top.document.getElementById('fade').style.display = 'block'
  }

  const config = {
    '.chosen-select': {},
    '.chosen-select-deselect': { allow_single_deselect: true },
    '.chosen-select-no-single': { disable_search_threshold: 10 },
    '.chosen-select-no-results': { no_results_text: 'Data tidak ketemu' },
    '.chosen-select-width': { width: '95%' },
  }
  for (let selector in config) {
    $(selector).chosen(config[selector])
  }

</script>
</body>
</html>


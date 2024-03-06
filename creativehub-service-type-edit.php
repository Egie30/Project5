<?php
include "framework/database/connect.php";
include "framework/functions/default.php";

$RtlOrdTyp = mysql_escape_string($_GET['ID']);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $RtlOrdTyp = mysql_escape_string($_POST['TYP_ID']);
    $CatId = mysql_escape_string($_POST['ORD_CAT']);
    $RtlOrdDesc = mysql_escape_string($_POST['NAME']);
    $InvNbr = mysql_escape_string($_POST['INV_NBR']);
    $RtlOrdPrc = mysql_escape_string($_POST['PRC']);

    if ($InvNbr == "") {
        $InvNbr = "NULL";
    }

    if ($RtlOrdTyp == "-1") {
        $query = "INSERT INTO CMP.RTL_ORD_TYP (RTL_ORD_DESC, INV_NBR, RTL_ORD_PRC, CRT_TS, CRT_NBR, CAT_ID) 
        VALUES ('" . $RtlOrdDesc . "'," . $InvNbr . "," . $RtlOrdPrc . ",CURRENT_TIMESTAMP," .
            $_SESSION['personNBR'] . "," . $CatId . ")";
        mysql_query($query);
        // echo "<pre>" . $query . "</pre>";
        $query = "SELECT MAX(RTL_ORD_TYP) AS TYP FROM " . $typtable;
        $result = mysql_fetch_array(mysql_query($query));
        $RtlOrdTyp = $result['TYP'];
    } else {
        $query = "UPDATE CMP.RTL_ORD_TYP
        SET RTL_ORD_DESC='" . $RtlOrdDesc . "',
        INV_NBR=" . $InvNbr . ",
        RTL_ORD_PRC=" . $RtlOrdPrc . ",
        UPD_TS=CURRENT_TIMESTAMP,
        UPD_NBR=" . $_SESSION['personNBR'] . "
        WHERE RTL_ORD_TYP=" . $RtlOrdTyp;
        mysql_query($query);
    }
}

$title = "Edit Layanan";
$nbr = $RtlOrdTyp;
if ($RtlOrdTyp == "-1") {
    $title = "Layanan Baru";
    $nbr = "Baru";
    $CatId = mysql_escape_string($_GET['CAT']);
} else {
    $query = "SELECT * FROM CMP.RTL_ORD_TYP WHERE RTL_ORD_TYP=" . $RtlOrdTyp;
    $result = mysql_query($query);
    $row = mysql_fetch_array($result);
    $RtlOrdDesc = $row['RTL_ORD_DESC'];
    $RtlOrdPrc = $row['RTL_ORD_PRC'];
    $InvNbr = $row['INV_NBR'];
    $CatId = $row['CAT_ID'];
}

$query = "SELECT CATEGORY FROM CMP.RTL_ORD_TYP_CAT WHERE CAT_ID=" . $CatId;
$row = mysql_fetch_array(mysql_query($query));
$CATEGORY = $row['CATEGORY'];
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
	<script type="text/javascript" src="framework/functions/default.js"></script>
    <style>
        h2 {
            margin-block-start: 0;
            padding-top: 0.83em;
        }

        span.toolbar {
            cursor: pointer;
        }

        #ORD_CAT {
            width: 150px;
        }
    </style>
</head>
<body>
<?php
if ($RtlOrdTyp != '-1') { ?>
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
    <h3><?php echo $nbr ?></h3>
    <div>
        <label for="ORD_CAT">Kategori</label><br>
		<select name="ORD_CAT" class="chosen-select" style='width:200px'>
		<?php
			$query="SELECT * FROM CMP.RTL_ORD_TYP_CAT ORDER BY CATEGORY";
			genCombo($query,"CAT_ID","CATEGORY",$row['CAT_ID'],"Pilih Kategori");
		?>
		</select><br /><div class="combobox"></div>
    </div>
	
    <input type="hidden" name="TYP_ID" value="<?php echo $RtlOrdTyp ?>">
    <br>
    <div>
        <label for="NAMA">Nama Layanan</label><br>
        <input type="text" id="NAME" name="NAME" value="<?php echo $RtlOrdDesc ?>">
    </div>
    <div>
        <label for="NAMA">Inventory Number</label><br>
        <input type="text" id="INV_NBR" name="INV_NBR" value="<?php echo $InvNbr ?>">
    </div>
    <div>
        <label for="NAMA">Harga</label><br>
        <input type="number" id="PRC" name="PRC" value="<?php echo $RtlOrdPrc ?>">
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
		window.location = "creativehub-service-type.php?DEL=<?php echo $RtlOrdTyp ?>"
		top.document.getElementById('catDelete').style.display = 'none'
		top.document.getElementById('fade').style.display = 'none'
	}

	const deleteAction = function (e) {
		window.scrollTo(0, 0)
		top.document.getElementById('catDelete').style.display = 'block'
		top.document.getElementById('fade').style.display = 'block'
	}

	var config = {
		'.chosen-select'           : {},
		'.chosen-select-deselect'  : {allow_single_deselect:true},
		'.chosen-select-no-single' : {disable_search_threshold:10},
		'.chosen-select-no-results': {no_results_text:'Data tidak ketemu'},
		'.chosen-select-width'     : {width:"95%"}
	}
	for (var selector in config) {
		$(selector).chosen(config[selector]);
	}

</script>

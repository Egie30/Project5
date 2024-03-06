<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
require_once "framework/security/default.php";
require_once "framework/functions/fingerprint.php";

//$Security = getSecurity($_SESSION['userID'], "AddressBook");

$result = "-1";
if ($_POST) {
    #daftarkan
    $fingerprint = new Fingerprint();
    $pin = $_POST['PRSN_NBR'];

    $rs = mysql_query("SELECT NAME FROM PEOPLE WHERE PRSN_NBR = " . $pin);
    $row = mysql_fetch_array($rs);

    $response = json_decode($fingerprint->setUser($pin, $row['NAME']));
    if ($response->Result) {
        $result = 1;
    } else {
        $result = 0;
    }
}
if (isset($_GET)) {
    #upload semua absensi yg terbaru
    $do = $_GET['do'];
    if ($do == 'upload') {
        $fingerprint = new Fingerprint();
        #1. save ke ddatabase lokal
        $fingerprint->getScanLog();

        #2. upload ke nestor.asia
//        echo $fingerprint->uploadScanLog();
        return;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
    <script>parent.Pace.restart();</script>
    <link rel="stylesheet" type="text/css" media="screen" href="css/screen.css"/>
    <link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css"/>
    <link rel="stylesheet" href="framework/combobox/chosen.css"/>

    <script src="framework/database/jquery.min.js" type="text/javascript"></script>
    <script src="framework/combobox/chosen.jquery.js" type="text/javascript"></script>
    <script type="text/javascript">
        var result = <?php echo $result;?>;

        if (result == 1) {
            parent.document.getElementById('fingerprintSukses').style.display = 'block';
            parent.document.getElementById('fade').style.display = 'block';
        } else if (result == 0) {
            parent.document.getElementById('fingerprintGagal').style.display = 'block';
            parent.document.getElementById('fade').style.display = 'block';
        }
    </script>
</head>
<body>
</br>

<form method="POST" action="">

<table>
	<tr>
		<td style='font-weight:bold'>
			Registrasi User Fingerprint
		</td>
	</tr>
	<tr>
		<td>
			Pilih Karyawan &nbsp;&nbsp;
			<br /><br />
                <select id="people" style="width:550px" class="chosen-select" name="PRSN_NBR">
                    <?php
                    $query = "SELECT p.PRSN_NBR, p.NAME
                        FROM PEOPLE p
                        INNER JOIN POS_TYP pt ON pt.POS_TYP = p.POS_TYP
                        WHERE p.TERM_DTE IS NULL AND p.CO_NBR IN (1002,271,997,1099) ORDER BY 2";
                    genCombo($query, "PRSN_NBR", "NAME", "");
                    ?>
                </select>
                <input type="submit" class="process submit_button" value="Daftarkan">
		</td>
	</tr>
	<tr>
		<td>
			<br /><br />
		</td>
	</tr>
	<tr>
		<td style='font-weight:bold'>
			Proses Data Fingerprint
		</td>
	</tr>
	<tr>
		<td>
			<input type="button" class="process submit_button" value="Upload" onclick="upload(this);">
		</td>
	</tr>
</table>         

</form>

<!--
<table style='margin-left:auto;margin-right:auto;width:100%;'>
    <tr style='border:0px;'>
        <td style='border:0px;vertical-align:text-bottom'>
            
        </td>
    </tr>
</table>
-->

<br/>
<script type="text/javascript">
    var config = {
        '.chosen-select': {},
        '.chosen-select-deselect': {allow_single_deselect: true},
        '.chosen-select-no-single': {disable_search_threshold: 10},
        '.chosen-select-no-results': {no_results_text: 'Data tidak ketemu'},
        '.chosen-select-width': {width: "95%"}
    }
    for (var selector in config) {
        $(selector).chosen(config[selector]);
    }
    function upload(e) {
        $(e).val("Processing");
        $.getJSON('fingerprint-people.php?do=upload', function (json) {
            if (json.Result) {
                $(e).val("Upload");
                alert('Berhasil diupload');
            }
        });
    }
</script>
</body>
</html>
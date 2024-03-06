<?php
//include "framework/database/connect.php";
include "framework/database/connect-cloud.php";
include "framework/functions/default.php";
include "framework/security/default.php";
date_default_timezone_set("Asia/Jakarta");

$Security 	= getSecurity($_SESSION['userID'], "DigitalPrint");

$PrsnNbr 	= $_GET['PRSN_NBR'];
$CoNbr 		= $_GET['CO_NBR'];
$UpdTs		= $_GET['CRTIME'];
$change 	= false;

$date		= parseDate($UpdTs);
$jam		= parseHour($UpdTs);
$menit		= parseMinute($UpdTs);


for ($i=0;$i<=23;$i++){
	if ($i<=9){$ArrJam[]= "0".$i;
	}else {$ArrJam[]=$i;}
}
for ($i=0;$i<=60;$i++){
	if ($i<=9){$ArrMenit[]= "0".$i;
	}else {$ArrMenit[]=$i;}
}

$querys = "SELECT 	PPL.PRSN_NBR,
							NAME
						FROM CMP.PEOPLE PPL
						WHERE DEL_NBR=0 AND PPL.PRSN_NBR=".$PrsnNbr;
$results= mysql_query($querys);
$rows	= mysql_fetch_array($results);

//Proses insert dan update komisi
if ($_POST) {
	$UpdNew = $_POST['DT_UPD']." ".$_POST['DUE_HR'].":".$_POST['DUE_MIN'].":00";
	//New
	if(empty($UpdTs)){
		$query = "INSERT INTO $PAY.ATND_CLOK (PRSN_NBR,CRT_TS) VALUE ($PrsnNbr,'$UpdNew')";
		$result= mysql_query($query,$cloud);
		$query = str_replace($PAY,"PAY",$query);
		$result= mysql_query($query,$local);
		
		$change = true;
		$date 	= $_POST['DT_UPD'];
		$jam	= $_POST['DUE_HR'];
		$menit	= $_POST['DUE_MIN'];
	}
	//Update
	if(isset($UpdTs)){
		$query = "UPDATE $PAY.ATND_CLOK SET 
					CRT_TS = '".$UpdNew."' 
					WHERE PRSN_NBR= ".$PrsnNbr."
					AND CRT_TS= '".$UpdTs."'";
					
		$result= mysql_query($query,$cloud);
		$query = str_replace($PAY,"PAY",$query);
		$result= mysql_query($query,$local);
		$change= true;
		$date 	= $_POST['DT_UPD'];
		$DueHr	= $_POST['DUE_HR'];
		$DueMin	= $_POST['DUE_MIN'];
	}
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8"/>
    <script>parent.Pace.restart();</script>
    <link rel="stylesheet" type="text/css" media="screen" href="css/screen.css"/>
    <link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css"/>
    <link rel="stylesheet" type="text/css" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css"
          media="screen"/>
    <link rel="stylesheet" href="framework/combobox/chosen.css"/>

    <script type="text/javascript" src="framework/database/jquery.min.js"></script>
    <script type="text/javascript">jQuery.noConflict();</script>
    <script type="text/javascript" src="framework/datepicker/js/mootools-1.2.4-core.js"></script>
    <script type="text/javascript" src="framework/datepicker/js/mootools-1.2.4.4-more.js"></script>
    <script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.js"></script>
    <script type="text/javascript" src="framework/combobox/chosen.jquery.js" type="text/javascript"></script>
	<script type="text/javascript" src="framework/functions/default.js"></script>
    <script type="text/javascript">
        var change = <?php echo $change;?>;
        if (change) {
            parent.document.getElementById('content').contentWindow.location.reload();
        }	
    </script>
</head>
<body>
<span class='fa fa-times toolbar' style='margin-left:10px' onclick="pushFormOut();"></span>
<h2 style="padding-left: 10px;">
	<?php echo $rows['NAME'];?>
</h2>
<h3 style="padding-left: 10px;">
	Nomor Induk Karyawan: <?php echo $PrsnNbr;?>
</h3>
<form enctype="multipart/form-data" action="#" method="post" style="width:457px;height:455px;"
      onsubmit="reloadParent();">
    <table>
        <tr>
            <td style=" width: 80px; ">Tanggal</td>
            <td>
				<?php 
					if (empty($date)){$date=date('Y-m-d');}
					else{
						if ($_POST){
						$DueHr=$_POST['DT_UPD'];
						}else{
							$date=$date;	
						}
					}
				?>
                <input name="DT_UPD" type="text" style="width:100px;" id="input-tanggal"
                       value="<?php echo $date; ?>">
                <script>
                    new CalendarEightysix('input-tanggal', {
                        'offsetY': 0,
                        'offsetX': 0,
                        'format': '%Y-%m-%d',
                        'prefill': true,
                        'slideTransition': Fx.Transitions.Back.easeOut,
                        'draggable': true
                    });
                </script>
            </td>
        </tr>
        <tr>
            <td>Waktu</td>
			<td>
				<?php 
				if (empty($UpdTs)){$DueHr="00";}
				else {if ($_POST){
						$DueHr=$_POST['DUE_HR'];
					}else{
						$DueHr=$jam;	
					}
				}
				?>
				<select class="chosen-select" style='width:53px' name="DUE_HR" <?php echo $headerEnable; ?> ><br /><div class='labelbox'></div>
				<?php genComboArrayVal($ArrJam,$DueHr); ?>
				</select>
				<?php 
				if (empty($UpdTs)){$DueMin="00";}
				else {
					if ($_POST){
						$DueHr=$_POST['DUE_MIN'];
					}else{
						$DueMin = $menit;	
					}
				}
				?>
				<select class="chosen-select" style='width:53px' name="DUE_MIN" <?php echo $headerEnable; ?> ><br />
				<?php genComboArrayVal($ArrMenit,$DueMin); ?>
			</select>
			</td>
        </tr>
        <tr>
            <td colspan="2">
                <input class="process" type="submit" id="submit_button" value="Save">
            </td>
        </tr>
    </table>
</form>

<script type="text/javascript">
    jQuery(document).ready(function () {
        var config = {
            '.chosen-select': {},
            '.chosen-select-deselect': {allow_single_deselect: true},
            '.chosen-select-no-single': {disable_search_threshold: 10},
            '.chosen-select-no-results': {no_results_text: 'Data tidak ketemu'},
            '.chosen-select-width': {width: "95%"}
        }
        for (var selector in config) {
            jQuery(selector).chosen(config[selector]);
        }
    });
</script>
</body>
</html>
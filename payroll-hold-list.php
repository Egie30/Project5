<?php
	include "framework/database/connect.php";
	include "framework/database/connect-cloud.php";
	include "framework/functions/komisi.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	include "framework/alert/alert.php";
	$PrsnNbr=$_GET['PRSN_NBR'];
	$PymtDte=$_GET['PYMT_DTE'];
	
	//$CoNbr=$_GET['CO_NBR'];
	$Security=getSecurity($_SESSION['userID'],"Payroll");
	
	if(($_POST['PRSN_NBR']!="")&&($cloud!=false)){
		if($_POST['PAY_HLD_TYP'] == "on"){$PayHldTyp = 3;} else {$PayHldTyp = 1;}
		$query = "UPDATE PAY.PAY_HLD_LST SET PAY_HLD_TYP='$PayHldTyp', UPD_TS=CURRENT_TIMESTAMP, UPD_NBR='$_SESSION[personNBR]' WHERE PRSN_NBR='$_POST[PRSN_NBR]' AND PYMT_DTE='$_POST[PYMT_DTE]'";
		//echo $query;
		mysql_query($query);
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<script>parent.Pace.restart();</script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" media="screen" />
<link rel="stylesheet" href="framework/combobox/chosen.css"/>
<link rel="stylesheet" type="text/css" media="screen" href="css/popup.css" />

<script type="text/javascript" src="framework/liveSearch/livepop.js"></script>
<script type="text/javascript" src="framework/functions/default.js"></script>
<script type="text/javascript" src="framework/liveSearch/livepop.js"></script>
<script type="text/javascript" src="framework/datepicker/js/mootools-1.2.4-core.js"></script>
<script type="text/javascript" src="framework/datepicker/js/mootools-1.2.4.4-more.js"></script>
<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.js"></script>
<script src="framework/database/jquery.min.js"></script>

<script src="framework/combobox/chosen.jquery.js" type="text/javascript"></script>


<script type="text/javascript">
	$.noConflict();
	jQuery(document).ready(function () {
        jQuery('.chosen-select').chosen();		
    });
	window.addEvent('domready', function() {
	//Datepicker
	new CalendarEightysix('textbox-id');
	//Calendar
	new CalendarEightysix('block-element-id');
	});
	MooTools.lang.set('id-ID', 'Date', {
		months:    ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
		days:      ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
		dateOrder: ['date', 'month', 'year', '/']
	});
	MooTools.lang.setLanguage('id-ID');
</script>

<link rel="stylesheet" type="text/css" media="screen" href="framework/alert/alert.css" />
<script>
function myFunction() {
	setTimeout(function () {
        document.location.reload()
    }, 100);
}
</script>
</head>

<body>
<table class="submenu">
	<tr>
		<td class="submenu" style="background-color:">
			<?php
				$query="SELECT PYMT_DTE
						FROM PAY.PAYROLL
						WHERE PRSN_NBR=".$PrsnNbr."
						AND DEL_NBR=0
						ORDER BY 1 DESC
						LIMIT 0,12";
				//echo $query;
				$result=mysql_query($query, $local);
				while($row=mysql_fetch_array($result))
				{
					echo "<a class='submenu' href='payroll-hold-list.php?PRSN_NBR=".$PrsnNbr."&PYMT_DTE=".$row['PYMT_DTE']."&CO_NBR=$CoNbr&CO_PAY=".$_GET['CO_PAY']."'><div class='";
					if($PymtDte==$row['PYMT_DTE']){echo "arrow_box";}else{echo "leftsubmenu";}
					echo "'>".$row['PYMT_DTE']."</div></a>";
				}
			?>	
		</td>
		<td class="subcontent">
			<?php
			if($PymtDte=="")
				{
					$query="SELECT LST.PRSN_NBR,COALESCE(SUM(LST.PAY_HLD_AMT),0) AS PAY_HLD_AMT,PPL.NAME FROM PAY.PAY_HLD_LST LST 
							INNER JOIN CMP.PEOPLE PPL ON LST.PRSN_NBR=PPL.PRSN_NBR
					WHERE PPL.CO_NBR IN (SELECT CO_NBR FROM NST.PARAM_PAYROLL) AND LST.PAY_HLD_TYP='1' AND LST.PRSN_NBR='$PrsnNbr'";
					$result=mysql_query($query, $local);
					$row=mysql_fetch_array($result);
					//echo $query;
				}else{
					$query="SELECT LST.PRSN_NBR,LST.PYMT_DTE,LST.PAY_HLD_AMT,LST.PAY_HLD_TYP,PPL.NAME FROM PAY.PAY_HLD_LST LST 
							INNER JOIN CMP.PEOPLE PPL ON LST.PRSN_NBR=PPL.PRSN_NBR
					WHERE PPL.CO_NBR IN (SELECT CO_NBR FROM NST.PARAM_PAYROLL) AND LST.PYMT_DTE='$PymtDte' AND LST.PRSN_NBR='$PrsnNbr'";
					$result=mysql_query($query, $local);
					$row=mysql_fetch_array($result);
					$days=$row['PYMT_DAYS'];
					//echo $query;
				}
				
			?>
					
			<form enctype="multipart/form-data" action="#" method="post" style="width:600px" onSubmit="return checkform();">
				
				<p>
					<h2>
						<?php echo $row['NAME'] ?>
					</h2>
					<h3>
						Perincian Gaji Ditahan Karyawan Nomor Induk: <?php echo $row['PRSN_NBR'];if($row['PRSN_NBR']==""){echo "Nomor Baru";} ?>
					</h3>
					<br/>
					<input name="PRSN_NBR" value="<?php echo $row['PRSN_NBR']; ?>" type="hidden" />
					
					<?php if($PymtDte=="") { ?>
						<table>
							<tr>
								<td>Jumlah total gaji ditahan</td>
								<td colspan="2"><input id="PAY_HLD_AMT" size="20" value="<?php echo $row['PAY_HLD_AMT']; ?>" readonly></input></td>
							</tr>
						</table>
					<?php } else { ?> 
					<table>
						<tr><td style='width:170px'>Tanggal gajian</td><td><input id="PYMT_DTE" name="PYMT_DTE" size="20" value="<?php echo $row['PYMT_DTE']; ?>"></input></td></tr>
						<script>
							new CalendarEightysix('PYMT_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });
						</script>
						<?php 
						if ($row['PAY_HLD_TYP']==1){
						?>
						<tr>
							<td>Jumlah gaji ditahan</td>
								<td colspan="2"><input id="PAY_HLD_AMT" size="20" value="<?php echo $row['PAY_HLD_AMT']; ?>" readonly></input></td>
						</tr>
						<tr>
                        <td>Gaji dihanguskan</td>
                        <td>
                            <?php
                            if (($row['PAY_HLD_TYP']==3)||($row['PAY_HLD_TYP']==2)){
                                $check2 = 'checked=""';
                            } else {
                                $check2 = '';
                            }
                            ?>
							<input name='PAY_HLD_TYP' id='PAY_HLD_TYP' type='checkbox' class='regular-checkbox'
                                   <?php echo $check2; ?>/><label for="PAY_HLD_TYP"></label>
                        </td>
						</tr>
						<?php } else if ($row['PAY_HLD_TYP']==2) { ?>
							<tr>
							<td>Jumlah gaji diberikan</td>
								<td colspan="2"><input id="PAY_HLD_AMT" size="20" value="<?php echo $row['PAY_HLD_AMT']; ?>" readonly></input></td>
							</tr>
						<?php } else if ($row['PAY_HLD_TYP']==3){ ?>
							<tr>
							<td>Jumlah gaji dihanguskan</td>
								<td colspan="2"><input id="PAY_HLD_AMT" size="20" value="<?php echo $row['PAY_HLD_AMT']; ?>" readonly></input></td>
							</tr>
						<?php } ?>
					</table>
					<?php if (($row['PAY_HLD_TYP']==1)||($row['PAY_HLD_TYP']==3)) { ?>
					<table>
						<tr style="std"><td colspan="3"><input class="process" type="submit" value="Simpan"/><div></div></td></tr>	
						</tr>
					</table>
					<?php } ?>
					<?php } ?>
				</p>
			</form>
		</td>
	</tr>
</table>	
</body>
</html>
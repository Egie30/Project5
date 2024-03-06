<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	include "framework/alert/alert.php";
	
	$PrsnNbr		= $_GET['PRSN_NBR'];
	$TrvlMonth		= $_GET['ORG_M'];
	$TrvlYear		= $_GET['ORG_Y'];
	$approvFlag		= $_GET['FLAG'];
	$travelNumber	= $_GET['AUTH_TRVL_NBR'];
	
	if($approvFlag != ''){
		$query="UPDATE CMP.AUTH_TRVL SET 
			VRFD_F=". $approvFlag .",
			VRFD_TS=CURRENT_TIMESTAMP,
			VRFD_NBR=".$_SESSION['personNBR']."
			WHERE AUTH_TRVL_NBR=".$travelNumber;
		$result=mysql_query($query);
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
	/*
	function calcPay(){
		<?php
		$query	= "SELECT REM_TYPE,REM_LMT,REM_AMT FROM REM_SCHED";	
		$result	= mysql_query($query);
		while($row = mysql_fetch_array($result)){
			echo "if(document.getElementById('REM_TYPE').value == '".$row['REM_TYPE']."'){ \n";
				echo "document.getElementById('TOT_AMT').value=Math.ceil(document.getElementById('TOT_DIST').value*(1+".$row['REM_AMT'].")); \n";
			echo "} \n";
		}
		?>
	}
	*/
	</script>
</head>
<body>

<div id="mainResult">
	<table class="submenu">
		<tr>
			<td class="submenu">
				<?php
					$query="SELECT 
	DATE_FORMAT(ORIG_TS, '%m-%Y') AS MTH_TRVL, MONTH(ORIG_TS) AS ORG_M, YEAR(ORIG_TS) AS ORG_Y 
FROM CMP.AUTH_TRVL WHERE PRSN_NBR=".$PrsnNbr." 
GROUP BY MONTH(ORIG_TS),YEAR(ORIG_TS) ORDER BY ORG_Y DESC, ORG_M DESC LIMIT 0 , 12 ";
					//echo $query;
					$result=mysql_query($query);
					while($row=mysql_fetch_array($result))
					{
						echo "<a class='submenu' href='travel-list-edit.php?PRSN_NBR=".$PrsnNbr."&ORG_M=".$row['ORG_M']."&ORG_Y=".$row['ORG_Y']."'><div class='";
						if($TrvlMonth == $row['ORG_M'] && $TrvlYear == $row['ORG_Y']){echo "arrow_box";}else{echo "leftsubmenu";}
						echo "'>".$row['MTH_TRVL']."</div></a>";
					}
				?>	
			</td>
			<td class="subcontent">
				<form enctype="multipart/form-data" action="#" method="post" style="width:700px" onSubmit="return checkform();">
					<?php
					$query	= "SELECT PRSN_NBR,NAME FROM CMP.PEOPLE WHERE PRSN_NBR=".$PrsnNbr;
					$result	= mysql_query($query);
					$row	= mysql_fetch_array($result);
					?>
					<h2>
						<?php echo $row['NAME'] ?>
					</h2>
									
					<h3>
						Nomor Induk: <?php echo $row['PRSN_NBR'];if($row['PRSN_NBR']==""){echo "Nomor Baru";} ?>
					</h3>
				<table id="mainTable" class="tablesorter searchTable">
					<thead>
						<tr>
							<th class="listable">Tanggal</th>
							<th class="listable" colspan="2">Jarak</th>
							<th class="listable">Waktu Tempuh</th>
							<th class="listable">Kecepatan</th>
							<th class="listable">Validasi</th>
						</tr>
					</thead>
					<tbody>
					<?php
						if($TrvlMonth == "" && $TrvlYear == ""){
							$query="SELECT 
								AUTH_TRVL_NBR,
								DATE(ORIG_TS) AS ORG_DTE,
								TRV.PRSN_NBR AS PRSN_NBR,
								NAME,
								ORIG_LAT,
								ORIG_LNG,
								DEST_LAT,
								DEST_LNG,
								DIST,
								SUM(CASE WHEN VRFD_F = 1 THEN DIST ELSE 0 END) AS TOT_DIST,
								CONCAT(FLOOR(TIMESTAMPDIFF(MINUTE,ORIG_TS,DEST_TS)/60),'h ',MOD(TIMESTAMPDIFF(MINUTE,ORIG_TS,DEST_TS),60),'m') AS TRVL_TM,
								(SUM(CASE WHEN VRFD_F = 1 THEN DIST ELSE 0 END) / CONCAT(FLOOR(TIMESTAMPDIFF(MINUTE,ORIG_TS,DEST_TS)/60),'h ',MOD(TIMESTAMPDIFF(MINUTE,ORIG_TS,DEST_TS),60),'m')) AS AVG_SPEEDS,
								(TIMESTAMPDIFF(MINUTE,ORIG_TS,DEST_TS) / 60) * SUM(CASE WHEN VRFD_F = 1 THEN DIST ELSE 0 END) AS AVG_SPEED,
								VRFD_F
							FROM CMP.AUTH_TRVL TRV 
								INNER JOIN CMP.PEOPLE PPL ON TRV.PRSN_NBR=PPL.PRSN_NBR
							WHERE TRV.PRSN_NBR = ". $PrsnNbr ."
							GROUP BY ORIG_TS DESC";
						}else{
							$query="SELECT 
								AUTH_TRVL_NBR,
								DATE(ORIG_TS) AS ORG_DTE,
								TRV.PRSN_NBR AS PRSN_NBR,
								NAME,
								ORIG_LAT,
								ORIG_LNG,
								DEST_LAT,
								DEST_LNG,
								DIST,
								SUM(CASE WHEN VRFD_F = 1 THEN DIST ELSE 0 END) AS TOT_DIST,
								CONCAT(FLOOR(TIMESTAMPDIFF(MINUTE,ORIG_TS,DEST_TS)/60),'h ',MOD(TIMESTAMPDIFF(MINUTE,ORIG_TS,DEST_TS),60),'m') AS TRVL_TM,
								(SUM(CASE WHEN VRFD_F = 1 THEN DIST ELSE 0 END) / CONCAT(FLOOR(TIMESTAMPDIFF(MINUTE,ORIG_TS,DEST_TS)/60),'h ',MOD(TIMESTAMPDIFF(MINUTE,ORIG_TS,DEST_TS),60),'m')) AS AVG_SPEEDS,
								(TIMESTAMPDIFF(MINUTE,ORIG_TS,DEST_TS) / 60) * SUM(CASE WHEN VRFD_F = 1 THEN DIST ELSE 0 END) AS AVG_SPEED,
								VRFD_F
							FROM CMP.AUTH_TRVL TRV 
								INNER JOIN CMP.PEOPLE PPL ON TRV.PRSN_NBR=PPL.PRSN_NBR
							WHERE TRV.PRSN_NBR=".$PrsnNbr." AND MONTH(ORIG_TS)='".$TrvlMonth."' AND YEAR(ORIG_TS)='".$TrvlYear."'
							GROUP BY AUTH_TRVL_NBR
							ORDER BY ORIG_TS DESC";
						}
						
						$result=mysql_query($query);
						while($row=mysql_fetch_array($result)){
							
						?>
						<tr>
							<td class='listable'><?php echo $row['ORG_DTE'];?></td>
							<td class='listable' align='right'>
								<div class="listable-btn" onclick="window.scrollTo(0,0);parent.document.getElementById('printDigitalPopupEditContent').src='travel-list-map.php?AUTH_TRVL_NBR=<?php echo $row['AUTH_TRVL_NBR'];?>&X=<?php echo $row['DEST_LAT'];?>&Y=<?php echo $row['DEST_LNG'];?>';parent.document.getElementById('printDigitalPopupEdit').style.display='block';parent.document.getElementById('fade').style.display='block'">
									<span class="fa fa-map-marker listable-btn" style="font-size:14px"></span>
								</div>
							</td>
							<td class='listable' align='right'><?php echo number_format($row['DIST'],1,',','.'); ?> km</td>
							<td class='listable' align='right'><?php echo $row['TRVL_TM'];?></td>
							<td class='listable' align='right'><?php echo number_format($row['AVG_SPEED'],1,',','.');?> km/jam</td>
							<td class='listable' align='center'>
								<div class='side' style='top:4px'>
									<input name="AUTH_TRVL_NBR[]" type="hidden" value="<?php echo $row['AUTH_TRVL_NBR'];?>">
									<input name='VRFD_F' value='<?php echo number_format($row['DIST'],1,'.',','); ?>' id='VRFD_F_<?php echo $row['AUTH_TRVL_NBR'];?>' type='checkbox' class='regular-checkbox' onclick="window.scrollTo(0,0);
										parent.document.getElementById('fade').style.display='block';
										parent.document.getElementById('travelApproval<?php if($row['VRFD_F']=="1"){echo "No";}else{ echo "Yes";} ?>').style.display='block';
										parent.document.getElementById('travelApproval<?php if($row['VRFD_F']=="1"){echo "No";}else{ echo "Yes";} ?>Yes').onclick=
										function () {
											parent.document.getElementById('content').src='travel-list-edit.php?FLAG=<?php if($row['VRFD_F']=="1"){echo "0";}else{ echo "1";} ?>&VRFD_F=<?php if($row['VRFD_F']=="1"){ echo "0";}else{ echo "1";}?>&AUTH_TRVL_NBR=<?php echo $row['AUTH_TRVL_NBR']; ?>&PRSN_NBR=<?php echo $PrsnNbr; ?>';
											parent.document.getElementById('travelApproval<?php if($row['VRFD_F']=="1"){echo "No";}else{ echo "Yes";} ?>').style.display='none';
											parent.document.getElementById('fade').style.display='none'; 
										};totalDist();" <?php if($row['VRFD_F']=="1"){echo "checked";} ?>/>&nbsp;
									<label for="VRFD_F_<?php echo $row['AUTH_TRVL_NBR'];?>"></label>
								</div>
							</td>
						</tr>
						<?php
						$totalDist += number_format($row['TOT_DIST'],1,'.',',');
						}
						?>
						<tr class="flat" style="height:10px">
							<td class="flat" colspan="7"><hr style="height:1px;border:0px;border-bottom:1px #CCCCCC solid" /></td>
						</tr>
					</tbody>
					</tfoot>
						<tr>
							<td><b>Total Jarak</b></td>
							<td colspan="3"><b><?php echo number_format($totalDist,1,'.',',');?> km</b><!--<input name="TOT_DIST" style="width: 100%;" value="<?php echo $totalDist;?>" onkeyup="calcPay();" onchange="calcPay();"></input>--></td>
						</tr>
					</tfoot>
				</table>
				</form>
			</td>
		</tr>
	</table>
</div>
	<script src="framework/database/jquery.min.js" type="text/javascript"></script>
	<script src="framework/combobox/chosen.jquery.js" type="text/javascript"></script>
	<script type="text/javascript">
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
	<script type="text/javascript">
	function totalDist() {
		var input = document.getElementsByName("VRFD_F");
		var total = 0;
		for (var i = 0; i < input.length; i++) {
			if (input[i].checked) {
				total += parseFloat(input[i].value);
			}
		}
		document.getElementById("TOT_DIST").value = total.toFixed(2);
	}
	</script>
</body>
</html>
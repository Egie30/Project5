<?php
	include "framework/database/connect-cloud.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	include "framework/alert/alert.php";
	include "framework/slack/slack.php";
	
	$Security 		= getSecurity($_SESSION['userID'],"AddressBook");
	$upperSecurity 		= getSecurity($_SESSION['userID'],"Executive");
	$slackChannelName = "time-off";

	if ($_GET['TM_OFF_M']==''){
		$_GET['TM_OFF_M']=date('m');
	}

	if ($_GET['TM_OFF_Y']==''){
		$_GET['TM_OFF_Y']=date('Y');
	}

	$PrsnNbr		= $_GET['PRSN_NBR'];
	$month			= $_GET['TM_OFF_M'];
	$year			= $_GET['TM_OFF_Y'];
	$approvFlag		= $_GET['FLAG'];
	$TmOffNbr		= $_GET['TM_OFF_NBR'];
	$Cuti 			= 8;

	if($cloud!=false){
		//Process delete entry
		if($_GET['DEL_A']!="")
		{
			$query="UPDATE $PAY.TM_OFF SET DEL_NBR=".$_SESSION['personNBR'].",UPD_TS=CURRENT_TIMESTAMP,UPD_NBR=".$_SESSION['personNBR']." WHERE TM_OFF_NBR=".$_GET['DEL_A'];
	   		$result=mysql_query($query,$cloud);
			$query=str_replace($PAY,"PAY",$query);
			$result=mysql_query($query,$local);

			$query_cek = "SELECT TM_OFF_NBR, TM_OFF_BEG_DTE, TM_OFF_END_DTE, PPL.NAME AS PPL_NAME, COM.NAME AS COM_NAME, DEL.NAME AS DEL_NAME
						  FROM PAY.TM_OFF TMO 
						  LEFT OUTER JOIN CMP.PEOPLE PPL ON PPL.PRSN_NBR=TMO.PRSN_NBR
						  LEFT OUTER JOIN CMP.PEOPLE DEL ON DEL.PRSN_NBR=TMO.DEL_NBR
						  LEFT OUTER JOIN CMP.COMPANY COM ON COM.CO_NBR= PPL.CO_NBR
						  WHERE TM_OFF_NBR=".$_GET['DEL_A'];
			$result_cek= mysql_query($query_cek, $local);
			$row_cek   = mysql_fetch_array($result_cek);

	    		$message="*Time off* atas nama  *".$row_cek['PPL_NAME']."*  dari perusahaan *".$row_cek['COM_NAME']."*  mulai tanggal *".$row_cek['TM_OFF_BEG_DTE']."* sampai tanggal *".$row_cek['TM_OFF_END_DTE']."* telah dihapus oleh *".$row_cek['DEL_NAME']."*.";

	       	 	slackChampion($message,$slackChannelName);
		}
	}

	if($approvFlag != '' && $cloud!=false){
		$query="UPDATE $PAY.TM_OFF SET 
			TM_OFF_F=". $approvFlag .",
			UPD_TS=CURRENT_TIMESTAMP,
			UPD_NBR=".$_SESSION['personNBR']."
			WHERE TM_OFF_NBR=".$TmOffNbr;
		
		$result=mysql_query($query,$cloud);
		$query=str_replace($PAY,"PAY",$query);
		$result=mysql_query($query,$local);
		//echo $query;

		$query_cek = "SELECT TM_OFF_NBR, TM_OFF_BEG_DTE, TM_OFF_END_DTE, PPL.NAME AS PPL_NAME, COM.NAME AS COM_NAME, UPD.NAME AS UPD_NAME
					  FROM PAY.TM_OFF TMO 
					  LEFT OUTER JOIN CMP.PEOPLE PPL ON PPL.PRSN_NBR=TMO.PRSN_NBR
					  LEFT OUTER JOIN CMP.PEOPLE UPD ON UPD.PRSN_NBR=TMO.UPD_NBR
					  LEFT OUTER JOIN CMP.COMPANY COM ON COM.CO_NBR= PPL.CO_NBR
					  WHERE TM_OFF_NBR=".$TmOffNbr;
		$result_cek= mysql_query($query_cek);
		$row_cek   = mysql_fetch_array($result_cek);

		if ($approvFlag==1){$sttFlag ="disetujui";}else{$sttFlag="tidak disetujui";}

		$message="*Time off* atas nama  *".$row_cek['PPL_NAME']."*  dari perusahaan *".$row_cek['COM_NAME']."*  mulai tanggal *".$row_cek['TM_OFF_BEG_DTE']."* sampai tanggal *".$row_cek['TM_OFF_END_DTE']."* telah *".$sttFlag."* oleh *".$row_cek['UPD_NAME']."*.";

	    	slackChampion($message,$slackChannelName);
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
		function check(TmOffNbr,PrsnNbr,TmOffF,cntTmOff){
			var table    = document.getElementById("mainTable");
			var row 	 = table.rows.namedItem("SISA_CUTI");
			var cells 	 = row.cells.namedItem("TotCuti").innerHTML;
			var val 	 = parseInt(cells.replace(' hari',''));
			var cuti 	 = "<?php echo $Cuti;?>";
			 

			sisaCuti     = val - cntTmOff;

			if (TmOffF=='1'){ stt = "No";flag="0";}else{stt="Yes";flag="1";}

			if (sisaCuti<0 && TmOffF!=1){
				window.scrollTo(0,0);
				parent.document.getElementById('MaxCuti').style.display='block';
				parent.document.getElementById('fade').style.display='block';
				document.getElementById('TM_OFF_F_'+TmOffNbr).checked=false;
				
			}else{
				window.scrollTo(0,0);
				parent.document.getElementById('fade').style.display='block';
				parent.document.getElementById('Approval'+stt).style.display='block';
				parent.document.getElementById('Approval'+stt+'Yes').onclick=function () {
						parent.document.getElementById('content').src='time-off-detail.php?FLAG='+flag+'&TM_OFF_F='+flag+'&TM_OFF_NBR='+TmOffNbr+'&PRSN_NBR='+PrsnNbr;
						parent.document.getElementById('Approval'+stt).style.display='none';
						parent.document.getElementById('fade').style.display='none'; 
				};
			}
			
		}

	</script>
</head>
<body>

<div id="mainResult">
	<table class="submenu">
		<tr>
			<td class="submenu">
				<?php
					$query="SELECT 
							DATE_FORMAT(TM_OFF_BEG_DTE, '%m-%Y') AS MTH_TM_OFF,
							MONTH(TM_OFF_BEG_DTE) AS TM_OFF_M,
							YEAR(TM_OFF_BEG_DTE) AS TM_OFF_Y
						FROM PAY.TM_OFF
						WHERE PRSN_NBR=".$PrsnNbr." AND DEL_NBR=0
						GROUP BY MONTH(TM_OFF_BEG_DTE),YEAR(TM_OFF_BEG_DTE)
						ORDER BY 1 DESC
						LIMIT 0,12";
					//echo $query;
					$result=mysql_query($query,$local);
					while($row=mysql_fetch_array($result))
					{
						echo "<a class='submenu' href='time-off-detail.php?PRSN_NBR=".$PrsnNbr."&TM_OFF_M=".$row['TM_OFF_M']."&TM_OFF_Y=".$row['TM_OFF_Y']."'><div class='";
						if($month == $row['TM_OFF_M'] && $year == $row['TM_OFF_Y']){echo "arrow_box";}else{echo "leftsubmenu";}
						echo "'>".$row['MTH_TM_OFF']."</div></a>";
					}
				?>	
			</td>
			<td class="subcontent">
				<form enctype="multipart/form-data" action="#" method="post" style="width:700px" onSubmit="return checkform();">
					<?php
					$query	= "SELECT PRSN_NBR,NAME FROM CMP.PEOPLE WHERE PRSN_NBR=".$PrsnNbr;
					$result	= mysql_query($query,$local);
					$row	= mysql_fetch_array($result);
					?>
					<h2>
						<?php echo $row['NAME'] ?>
					</h2>
					<h3>
						Nomor Induk: <?php echo $row['PRSN_NBR'];?>
					</h3>
				<table id="mainTable" class="tablesorter searchTable">
					<thead>
						<tr>
							<th class="listable" style="width: 5%">No.</th>
							<th class="listable" style="width: 12%">Mulai</th>
							<th class="listable" style="width: 12%">Selesai</th>
							<th class="listable">Jumlah Cuti</th>
							<th class="listable" style="width: 50%">Alasan</th>
							<th class="listable">Status</th>
						</tr>
					</thead>
					<tbody>
					<?php
						if($month == "" && $year == ""){
							$query="SELECT 
								TM_OFF_NBR,
								PRSN_NBR,
								CONCAT(TM_OFF_BEG_DTE,' - ',TM_OFF_END_DTE) AS TM_OFF_DTE,
								TM_OFF_BEG_DTE,
								TM_OFF_END_DTE,
								TM_OFF_RSN,
								TM_OFF_F,
								DATEDIFF(TM_OFF_END_DTE,TM_OFF_BEG_DTE)+1 AS CNT_TM_OFF,
								(CASE WHEN TM_OFF_F=1 THEN DATEDIFF(TM_OFF_END_DTE,TM_OFF_BEG_DTE)+1 ELSE 0 END) AS CNT_DTE
							FROM PAY.TM_OFF  
							WHERE PRSN_NBR = ". $PrsnNbr ." AND DEL_NBR=0
							GROUP BY TM_OFF_NBR DESC";
						}else{
							$query="SELECT 
										TM_OFF_NBR,
										PRSN_NBR,
										CONCAT(TM_OFF_BEG_DTE,' - ',TM_OFF_END_DTE) AS TM_OFF_DTE,
										TM_OFF_BEG_DTE,
										TM_OFF_END_DTE,
										TM_OFF_RSN,
										TM_OFF_F,
										DATEDIFF(TM_OFF_END_DTE,TM_OFF_BEG_DTE)+1 AS CNT_TM_OFF,
										(CASE WHEN TM_OFF_F=1 THEN DATEDIFF(TM_OFF_END_DTE,TM_OFF_BEG_DTE)+1 ELSE 0 END) AS CNT_DTE
							FROM PAY.TM_OFF  
							WHERE PRSN_NBR=".$PrsnNbr." 
								AND MONTH(TM_OFF_BEG_DTE)='".$month."' 
								AND YEAR(TM_OFF_BEG_DTE)='".$year."'
								AND DEL_NBR=0
							GROUP BY TM_OFF_NBR
							ORDER BY TM_OFF_NBR DESC";
						}
						// echo $query;
						$result=mysql_query($query,$local);
						while($row=mysql_fetch_array($result)){
							
						?>
						<tr>
							<td class='listable' onclick="updateTmOff('<?php echo $row['TM_OFF_NBR']?>')" style="cursor: pointer;"><?php echo $row['TM_OFF_NBR'];?></td>
							<td class='listable' onclick="updateTmOff('<?php echo $row['TM_OFF_NBR']?>')" align='right'style="cursor: pointer;"><?php echo $row['TM_OFF_BEG_DTE']; ?></td>
							<td class='listable' onclick="updateTmOff('<?php echo $row['TM_OFF_NBR']?>')" align='right'style="cursor: pointer;"><?php echo $row['TM_OFF_END_DTE']; ?></td>
							<td class='listable onclick="updateTmOff('<?php echo $row['TM_OFF_NBR']?>')"' align='right'style="cursor: pointer;"><?php echo number_format($row['CNT_TM_OFF'],0,'.',',');?></td>
							<td class='listable'  onclick="updateTmOff('<?php echo $row['TM_OFF_NBR']?>')" style="cursor: pointer;"><?php echo $row['TM_OFF_RSN'];?></td>

							<?php if (($Security<3)&&($upperSecurity<6)){ ?>
							<?php if ($PrsnNbr != $_SESSION['personNBR']){ ?> 
							<td class='listable' align='center'>
								<div class='side' style='top:4px'>
									<input name="TM_OFF_NBR[]" type="hidden" value="<?php echo $row['TM_OFF_NBR'];?>">
									<input name='TM_OFF_F' value="<?php echo $row['CNT_DTE'];?>" id='TM_OFF_F_<?php echo $row['TM_OFF_NBR'];?>' type='checkbox' class='regular-checkbox' onclick="check(<?php echo $row['TM_OFF_NBR'].",".$row['PRSN_NBR'].",".$row['TM_OFF_F'].",".$row['CNT_TM_OFF'];?>);" <?php if($row['TM_OFF_F']=="1"){echo "checked";} ?>/>&nbsp;
									<label for="TM_OFF_F_<?php echo $row['TM_OFF_NBR'];?>"></label>
								</div>
							</td>
							<?php }else{
								echo "<td class='listable' align='center'>";
								if ($row['TM_OFF_F']==1){echo "Approve";}else{echo "Disapprove";} 
								echo "</td>";
							}
							?>

							<?php } else {?>
							<td>
								<?php if ($row['TM_OFF_F']==1){echo "Approve";}else{echo "Disapprove";}?>
							</td>
							<?php } ?>
						</tr>
						<?php $totalCntDte+= $row['CNT_DTE'];
						}
						if ($year != date("Y")){
							$totalCntDte = 8;		
						} 
						?>
						<tr class="flat" style="height:10px">
							<td class="flat" colspan="7"><hr style="height:1px;border:0px;border-bottom:1px #CCCCCC solid" /></td>
						</tr>
					</tbody>
					</tfoot>
						<tr>
							<?php 
								$query_cnt = "SELECT SUM(DATEDIFF(TM_OFF_END_DTE,TM_OFF_BEG_DTE)+1) AS CNT_TM_OFF 
											  FROM PAY.TM_OFF 
											  WHERE DEL_NBR = 0 
											  	AND PRSN_NBR = ".$PrsnNbr." 
											  	AND YEAR(TM_OFF_BEG_DTE)=".$year." AND TM_OFF_F=1 
											  GROUP BY PRSN_NBR";
								$result_cnt= mysql_query($query_cnt, $local);
								$row_cnt   = mysql_fetch_array($result_cnt);
								$totalCuti = $row_cnt['CNT_TM_OFF'];
							?>
							<td colspan="2"><b>Total Cuti</b></td>
							<td colspan="3"><b><?php echo number_format($totalCntDte,0,'.',',');?> hari</b></td>
						</tr>
						<tr id="SISA_CUTI">
							<td colspan="2" style="font-weight: 700;">Total Sisa Cuti</td>
							<td colspan="3" id="TotCuti" style="font-weight: 700;"><?php echo number_format($Cuti-$totalCuti,0,'.',',');?> hari</td>
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
		};
		for (var selector in config) {
			$(selector).chosen(config[selector]);
		}
	</script>
	<script type="text/javascript">
		function updateTmOff(loaNbr){
			location.href="time-off-edit.php?TM_OFF_NBR="+loaNbr;
		}
	</script>
</body>
</html>
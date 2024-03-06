<?php
	include "framework/database/connect-cloud.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	include "framework/alert/alert.php";
	include "framework/slack/slack.php";
	
	$Accounting 		= getSecurity($_SESSION['userID'],"Accounting");

	$PrsnNbr		= $_GET['PRSN_NBR'];
	$month			= $_GET['LOA_M'];
	$year			= $_GET['LOA_Y'];
	$approvFlag		= $_GET['FLAG'];
	$loaNbr			= $_GET['LOA_NBR'];
	$slack          = false;
	$delete 		= false;
	$approved 		= false;

	//Proses Delete Loa 
	if($cloud!=false){
		if($_GET['DEL_L']!="")
		{
			$query="UPDATE $PAY.LOA  SET DEL_NBR=".$_SESSION['personNBR'].",UPD_TS=CURRENT_TIMESTAMP,UPD_NBR=".$_SESSION['personNBR']." 
			WHERE LOA_NBR=".$_GET['DEL_L'];
			$result=mysql_query($query,$cloud);
			$query=str_replace($PAY,"PAY",$query);
			$result=mysql_query($query,$local);
			$slack  = true;
			$delete = true;
			$loaNbr =$_GET['DEL_L'];
		}
	}

	//Proses untuk Approved Loa
	if($approvFlag != '' && $cloud!=false){
		$query="UPDATE $PAY.LOA SET 
			LOA_F=". $approvFlag .",
			UPD_TS=CURRENT_TIMESTAMP,
			UPD_NBR=".$_SESSION['personNBR']."
			WHERE LOA_NBR=".$loaNbr;
		
		$result=mysql_query($query,$cloud);
		$query=str_replace($PAY,"PAY",$query);
		$result=mysql_query($query,$local);
		$slack    = true;
		$approved = true;
	}

	if ($slack){
		$slackChannelName = "time-off";

		$query = "SELECT LOA.PRSN_NBR,
						 PPL.NAME,
						 COM.NAME AS COM_NAME,
						 LOA.LOA_BEG_DTE,
						 LOA.LOA_END_DTE,
						 LOA.LOA_RSN,
						 UPD.NAME AS UPD_NAME,
						 DEL.NAME AS DEL_NAME
				  FROM PAY.LOA 
				  LEFT OUTER JOIN CMP.PEOPLE PPL ON PPL.PRSN_NBR = LOA.PRSN_NBR
				  LEFT OUTER JOIN CMP.PEOPLE UPD ON UPD.PRSN_NBR = LOA.UPD_NBR
				  LEFT OUTER JOIN CMP.PEOPLE DEL ON DEL.PRSN_NBR = LOA.DEL_NBR
				  LEFT OUTER JOIN CMP.COMPANY COM ON COM.CO_NBR = PPL.CO_NBR
				  WHERE LOA_NBR=".$loaNbr;
		$result = mysql_query($query, $local);
		$rowSl  = mysql_fetch_array($result);

		if ($approvFlag==1){$sttFlag ="disetujui";}else{$sttFlag="tidak disetujui";}

		if ($delete){
			$message="*Leave Of Absence* atas nama  *".$rowSl['NAME']."*  dari perusahaan *".$rowSl['COM_NAME']."*  mulai tanggal *".$rowSl['LOA_BEG_DTE']."* sampai tanggal *".$rowSl['LOA_END_DTE']."* telah dihapus oleh *".$rowSl['DEL_NAME']."*.";
		}

		if ($approved){
			$message="*Leave Of Absence* atas nama  *".$rowSl['NAME']."*  dari perusahaan *".$rowSl['COM_NAME']."*  mulai tanggal *".$rowSl['LOA_BEG_DTE']."* sampai tanggal *".$rowSl['LOA_END_DTE']."* telah *".$sttFlag."* oleh *".$rowSl['UPD_NAME']."*.";
		}

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
</head>
<body>

<div id="mainResult">
	<table class="submenu">
		<tr>
			<td class="submenu">
				<?php
					$query="SELECT 
							DATE_FORMAT(LOA_BEG_DTE, '%m-%Y') AS MTH_LOA,
							MONTH(LOA_BEG_DTE) AS LOA_M,
							YEAR(LOA_BEG_DTE) AS LOA_Y
						FROM PAY.LOA
						WHERE PRSN_NBR=".$PrsnNbr." AND DEL_NBR=0
						GROUP BY MONTH(LOA_BEG_DTE),YEAR(LOA_BEG_DTE)
						ORDER BY 1 DESC
						LIMIT 0,12";
					// echo $query;
					$result=mysql_query($query,$local);
					while($row=mysql_fetch_array($result))
					{
						echo "<a class='submenu' href='leave-of-absence-detail.php?PRSN_NBR=".$PrsnNbr."&LOA_M=".$row['LOA_M']."&LOA_Y=".$row['LOA_Y']."'><div class='";
						if($month == $row['LOA_M'] && $year == $row['LOA_Y']){echo "arrow_box";}else{echo "leftsubmenu";}
						echo "'>".$row['MTH_LOA']."</div></a>";
					}
				?>	
			</td>
			<td class="subcontent">
				<!-- <div style="padding-top: 5px;padding-left: 5px; margin-bottom: -20px; ">
					<?php if((paramCloud()==1)){?> <a href="leave-of-absence-edit.php?LOA_NBR=0"><span class='fa fa-plus toolbar' style="cursor:pointer;<?php echo $display; ?>" onclick="location.href="></span></a> <?php } ?>
				</div> -->
				<form enctype="multipart/form-data" action="#" method="post" style="width:800px" onSubmit="return checkform();">
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
							<th class="listable">Tanggal Awal</th>
							<th class="listable">Tanggal Akhir</th>
							<th class="listable" style="width: 50%">Alasan</th>
							<th class="listable">Approve</th>
						</tr>
					</thead>
					<tbody>
					<?php
						if($month == "" && $year == ""){
							$query="SELECT 
										LOA.LOA_NBR,
										LOA.LOA_RSN,
										LOA.LOA_BEG_DTE,
										LOA.LOA_END_DTE,
										LOA.LOA_F
									FROM PAY.LOA  
									WHERE LOA.PRSN_NBR = ". $PrsnNbr ." AND LOA.DEL_NBR=0
									ORDER BY LOA_NBR DESC";
						}else{
							$query="SELECT 
										LOA.LOA_NBR,
										LOA.LOA_RSN,
										LOA.LOA_BEG_DTE,
										LOA.LOA_END_DTE,
										CASE WHEN LOA_F=1 
											THEN DATEDIFF(LOA.LOA_END_DTE,LOA.LOA_END_DTE)+1 ELSE 0 END AS TOT_LOA,
										LOA.LOA_F
									FROM PAY.LOA 
									WHERE LOA.PRSN_NBR=".$PrsnNbr." 
										AND MONTH(LOA_BEG_DTE)='".$month."' 
										AND YEAR(LOA_BEG_DTE)='".$year."'
										AND LOA.DEL_NBR=0
									GROUP BY LOA_NBR
									ORDER BY LOA_NBR DESC";
						}
						// ECHO $query;
						$result=mysql_query($query,$local);
						while($row=mysql_fetch_array($result)){
							
						?>
						<tr>
							<td class='listable' onclick="updateLoa('<?php echo $row['LOA_NBR']?>','<?php echo $PrsnNbr;?>')" style="cursor: pointer;text-align: right;"><?php echo $row['LOA_NBR'];?></td>
							<td class='listable' onclick="updateLoa('<?php echo $row['LOA_NBR']?>','<?php echo $PrsnNbr;?>')" align='right'style="cursor: pointer;"><?php echo $row['LOA_BEG_DTE']; ?></td>
							<td class='listable' onclick="updateLoa('<?php echo $row['LOA_NBR']?>','<?php echo $PrsnNbr;?>')" align='right'style="cursor: pointer;"><?php echo $row['LOA_END_DTE']; ?></td>
							<td class='listable'  onclick="updateLoa('<?php echo $row['LOA_NBR']?>','<?php echo $PrsnNbr;?>')" style="cursor: pointer;"><?php echo $row['LOA_RSN'];?></td>
							
							<?php if ($Accounting<8){?>
							<td class='listable' align='center'>
								<div class='side' style='top:4px'>
									<input name="LOA_NBR[]" type="hidden" value="<?php echo $row['LOA_NBR'];?>">
									<input name='LOA_F' value="<?php echo $row['TOT_LOA'];?>" id='LOA_F_<?php echo $row['LOA_NBR'];?>' type='checkbox' class='regular-checkbox' onclick="window.scrollTo(0,0);
										parent.document.getElementById('fade').style.display='block';
										parent.document.getElementById('Approval<?php if($row['LOA_F']=="1"){echo "No";}else{ echo "Yes";} ?>').style.display='block';
										parent.document.getElementById('Approval<?php if($row['LOA_F']=="1"){echo "No";}else{ echo "Yes";} ?>Yes').onclick=
										function () {
											parent.document.getElementById('content').src='leave-of-absence-detail.php?FLAG=<?php if($row['LOA_F']=="1"){echo "0";}else{ echo "1";} ?>&LOA_F=<?php if($row['LOA_F']=="1"){ echo "0";}else{ echo "1";}?>&LOA_NBR=<?php echo $row['LOA_NBR']; ?>&PRSN_NBR=<?php echo $PrsnNbr; ?>&LOA_M=<?php echo $month;?>&LOA_Y=<?php echo $year;?>';
											parent.document.getElementById('Approval<?php if($row['LOA_F']=="1"){echo "No";}else{ echo "Yes";} ?>').style.display='none';
											parent.document.getElementById('fade').style.display='none'; 
										};" <?php if($row['LOA_F']=="1"){echo "checked";} ?>/>&nbsp;
									<label for="LOA_F_<?php echo $row['LOA_NBR'];?>"></label>
								</div>
							</td>
							<?php } else {?>
							<td>
								<?php if ($row['LOA_F']==1){echo "Disapprove";}else{echo "Approved";}?>
							</td>
							<?php } 
								$totalLoa += $row['TOT_LOA'];
							?>
						</tr>
						<?php } ?>
						<tr class="flat" style="height:10px">
							<td class="flat" colspan="7"><hr style="height:1px;border:0px;border-bottom:1px #CCCCCC solid" /></td>
						</tr>
					</tbody>
					</tfoot>
						<?php 
							//Proses hitung total Loa yang telah dibuat dan te;ah di berikan
							echo "<tr>";
							echo "<td colspan='3'><b>Total izin yang di setujui</b></td>";
							echo "<td colspan='2'><b>".number_format($totalLoa,0,'.',',')." </b></td>";
							echo "</tr>";
						?>
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
		function updateLoa(loaNbr,PrsnNbr){
			location.href="leave-of-absence-edit.php?LOA_NBR="+loaNbr+"&PRSN_NBR="+PrsnNbr;
		}
	</script>
</body>
</html>
<?php
	include "framework/database/connect-cloud.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	include "framework/alert/alert.php";
	
	$Finance 		= getSecurity($_SESSION['userID'],"Finance");
	$DigitalPrint 	= getSecurity($_SESSION['userID'],"DigitalPrint");
	$Payroll 		= getSecurity($_SESSION['userID'],"Payroll");

	$PrsnNbr		= $_GET['PRSN_NBR'];
	$month			= $_GET['PEER_M'];
	$year			= $_GET['PEER_Y'];
	$approvFlag		= $_GET['FLAG'];
	$peerFmNbr		= $_GET['PEER_FORM_NBR'];

	//Proses Delete Peer 
	if($cloud!=false){
		if($_GET['DEL_L']!="")
		{
			$query="UPDATE $PAY.PEER_FORM  SET DEL_NBR=".$_SESSION['personNBR'].",UPD_TS=CURRENT_TIMESTAMP,UPD_NBR=".$_SESSION['personNBR']." 
			WHERE PEER_FORM_NBR=".$_GET['DEL_L'];
			$result=mysql_query($query,$cloud);
			$query=str_replace($PAY,"PAY",$query);
			$result=mysql_query($query,$local);
		}
	}

	//Proses untuk Approved Peer
	if($approvFlag != '' && $cloud!=false){
		$query="UPDATE $PAY.PEER_FORM SET 
			PEER_APV_F=". $approvFlag .",
			UPD_TS=CURRENT_TIMESTAMP,
			UPD_NBR=".$_SESSION['personNBR']."
			WHERE PEER_FORM_NBR=".$peerFmNbr;
		
		$result=mysql_query($query,$cloud);
		$query=str_replace($PAY,"PAY",$query);
		$result=mysql_query($query,$local);
		 
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
							DATE_FORMAT(PEER_DTE, '%m-%Y') AS MTH_PEER,
							MONTH(PEER_DTE) AS PEER_M,
							YEAR(PEER_DTE) AS PEER_Y
						FROM PAY.PEER_FORM
						WHERE PRSN_NBR=".$PrsnNbr." AND DEL_NBR=0
						GROUP BY MONTH(PEER_DTE),YEAR(PEER_DTE)
						ORDER BY PEER_DTE DESC
						LIMIT 0,12";
					//echo $query;
					$result=mysql_query($query,$local);
					while($row=mysql_fetch_array($result))
					{
						echo "<a class='submenu' href='peer-form-detail.php?PRSN_NBR=".$PrsnNbr."&PEER_M=".$row['PEER_M']."&PEER_Y=".$row['PEER_Y']."'><div class='";
						if($month == $row['PEER_M'] && $year == $row['PEER_Y']){echo "arrow_box";}else{echo "leftsubmenu";}
						echo "'>".$row['MTH_PEER']."</div></a>";
					}
				?>	
			</td>
			<td class="subcontent">
				<!-- <div style="padding-top: 5px;padding-left: 5px; margin-bottom: -20px; ">
					<?php if((paramCloud()==1)){?> <a href="peer-form-edit.php?PEER_FORM_NBR=0"><span class='fa fa-plus toolbar' style="cursor:pointer;<?php echo $display; ?>" onclick="location.href="></span></a> <?php } ?>
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
							<th class="listable">Tanggal</th>
							<th class="listable">Jenis Peer</th>
							<th class="listable">Peer Dibuat</th>
							<th class="listable" style="width: 25%">Alasan</th>
							<th class="listable" style="width: 25%">Komentar</th>
							<th class="listable">Approve</th>
						</tr>
					</thead>
					<tbody>
					<?php
						if($month == "" && $year == ""){
							$query="SELECT 
										PRF.PEER_FORM_NBR,
										PRF.PEER_DTE,
										PPL.NAME,
										PRF.PEER_RSN,
										PRF.PEER_CMNT,
										PRF.PEER_APV_F,
										PP.NAME AS CRT_NAME,
										PRT.PEER_TYP_DESC
									FROM PAY.PEER_FORM PRF 
									LEFT OUTER JOIN CMP.PEOPLE PPL ON PPL.PRSN_NBR = PRF.PRSN_NBR 
									LEFT OUTER JOIN CMP.PEOPLE PP ON PP.PRSN_NBR = PRF.CRT_NBR
									LEFT OUTER JOIN PAY.PEER_TYP PRT ON PRT.PEER_TYP_NBR = PRF.PEER_TYP 
									WHERE PRF.PRSN_NBR = ". $PrsnNbr ." AND PRF.DEL_NBR=0
									ORDER BY PEER_FORM_NBR DESC";
						}else{
							$query="SELECT 
										PRF.PEER_FORM_NBR,
										PRF.PEER_DTE,
										PPL.NAME,
										PRF.PEER_RSN,
										PRF.PEER_CMNT,
										PRF.PEER_APV_F,
										PP.NAME AS CRT_NAME,
										PRT.PEER_TYP_DESC
									FROM PAY.PEER_FORM PRF 
									LEFT OUTER JOIN CMP.PEOPLE PPL ON PPL.PRSN_NBR = PRF.PRSN_NBR 
									LEFT OUTER JOIN CMP.PEOPLE PP ON PP.PRSN_NBR = PRF.CRT_NBR
									LEFT OUTER JOIN PAY.PEER_TYP PRT ON PRT.PEER_TYP_NBR = PRF.PEER_TYP 
									WHERE PRF.PRSN_NBR=".$PrsnNbr." 
										AND MONTH(PEER_DTE)='".$month."' 
										AND YEAR(PEER_DTE)='".$year."'
										AND PRF.DEL_NBR=0
									GROUP BY PEER_FORM_NBR
									ORDER BY PEER_FORM_NBR DESC";
						}
						
						$result=mysql_query($query,$local);
						while($row=mysql_fetch_array($result)){
							
						?>
						<tr>
							<td class='listable' onclick="updatePeer('<?php echo $row['PEER_FORM_NBR']?>','<?php echo $PrsnNbr;?>')" style="cursor: pointer;text-align: right;"><?php echo $row['PEER_FORM_NBR'];?></td>
							<td class='listable' onclick="updatePeer('<?php echo $row['PEER_FORM_NBR']?>','<?php echo $PrsnNbr;?>')" align='right'style="cursor: pointer;"><?php echo $row['PEER_DTE']; ?></td>
							<td class='listable'  onclick="updatePeer('<?php echo $row['PEER_FORM_NBR']?>','<?php echo $PrsnNbr;?>')" style="cursor: pointer;"><?php echo $row['PEER_TYP_DESC'];?></td>
							<td class='listable'  onclick="updatePeer('<?php echo $row['PEER_FORM_NBR']?>','<?php echo $PrsnNbr;?>')" style="cursor: pointer;"><?php echo dispNameScreen(shortName($row['CRT_NAME']));?></td>
							<td class='listable'  onclick="updatePeer('<?php echo $row['PEER_FORM_NBR']?>','<?php echo $PrsnNbr;?>')" style="cursor: pointer;"><?php echo $row['PEER_RSN'];?></td>
							<td class='listable'  onclick="updatePeer('<?php echo $row['PEER_FORM_NBR']?>','<?php echo $PrsnNbr;?>')" style="cursor: pointer;"><?php echo $row['PEER_CMNT'];?></td>

							<?php if ($Payroll<9 && $DigitalPrint<3 && $Finance<9){?>
							<td class='listable' align='center'>
								<div class='side' style='top:4px'>
									<input name="PEER_FORM_NBR[]" type="hidden" value="<?php echo $row['PEER_FORM_NBR'];?>">
									<input name='PEER_APV_F' value="<?php echo $row['CNT_DTE'];?>" id='PEER_APV_F_<?php echo $row['PEER_FORM_NBR'];?>' type='checkbox' class='regular-checkbox' onclick="window.scrollTo(0,0);
										parent.document.getElementById('fade').style.display='block';
										parent.document.getElementById('Approval<?php if($row['PEER_APV_F']=="1"){echo "No";}else{ echo "Yes";} ?>').style.display='block';
										parent.document.getElementById('Approval<?php if($row['PEER_APV_F']=="1"){echo "No";}else{ echo "Yes";} ?>Yes').onclick=
										function () {
											parent.document.getElementById('content').src='peer-form-detail.php?FLAG=<?php if($row['PEER_APV_F']=="1"){echo "0";}else{ echo "1";} ?>&PEER_APV_F=<?php if($row['PEER_APV_F']=="1"){ echo "0";}else{ echo "1";}?>&PEER_FORM_NBR=<?php echo $row['PEER_FORM_NBR']; ?>&PRSN_NBR=<?php echo $PrsnNbr; ?>&PEER_M=<?php echo $month;?>&PEER_Y=<?php echo $year;?>';
											parent.document.getElementById('Approval<?php if($row['PEER_APV_F']=="1"){echo "No";}else{ echo "Yes";} ?>').style.display='none';
											parent.document.getElementById('fade').style.display='none'; 
										};" <?php if($row['PEER_APV_F']=="1"){echo "checked";} ?>/>&nbsp;
									<label for="PEER_APV_F_<?php echo $row['PEER_FORM_NBR'];?>"></label>
								</div>
							</td>
							<?php } else {?>
							<td>
								<?php if ($row['PEER_APV_F']==1){echo "Disapprove";}else{echo "Approved";}?>
							</td>
							<?php } ?>
						</tr>
						<?php } ?>
						<tr class="flat" style="height:10px">
							<td class="flat" colspan="7"><hr style="height:1px;border:0px;border-bottom:1px #CCCCCC solid" /></td>
						</tr>
					</tbody>
					</tfoot>
						<?php 
							//Proses hitung total peer yang telah dibuat dan te;ah di berikan
							$querys= "SELECT
										PEER_TYP_DESC,
									 	SUM(CASE WHEN PRSN_NBR=".$PrsnNbr." THEN PEER_APV_F ELSE 0 END) AS PEER_GIVEN,
									 	SUM(CASE WHEN CRT_NBR=".$PrsnNbr." THEN PEER_APV_F ELSE 0 END) AS PEER_CRT
									FROM PAY.PEER_FORM PRF 
									LEFT OUTER JOIN PAY.PEER_TYP PRT ON PRF.PEER_TYP= PRT.PEER_TYP_NBR
									WHERE (PRSN_NBR=".$PrsnNbr." OR CRT_NBR=".$PrsnNbr.")
										AND PEER_APV_F=1
										AND MONTH(PEER_DTE)='".$month."'
										AND YEAR(PEER_DTE)='".$year."'
										AND PRF.DEL_NBR=0
									GROUP BY PRF.PEER_TYP";
							$results= mysql_query($querys,$local);
							//echo $querys;
							if (mysql_num_rows($results)>0){
							while ($rows=mysql_fetch_array($results)) {
								if ($rows['PEER_GIVEN']>0){
									echo "<tr>";
									echo "<td colspan='3'><b>Total ".$rows['PEER_TYP_DESC']." Diperoleh</b></td>";
									echo "<td colspan='2'><b>".number_format($rows['PEER_GIVEN'],0,'.',',')." </b></td>";
									echo "</tr>";
								}
								if ($rows['PEER_CRT']>0){
									echo "<tr>";
									echo "<td colspan='3'><b>Total ".$rows['PEER_TYP_DESC']." Diberikan</b></td>";
									echo "<td colspan='2'><b>".number_format($rows['PEER_CRT'],0,'.',',')." </b></td>";
									echo "</tr>";
								}
							}
							}
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
		function updatePeer(peerFmNbr,PrsnNbr){
			location.href="peer-form-edit.php?PEER_FORM_NBR="+peerFmNbr+"&PRSN_NBR="+PrsnNbr;
		}
	</script>
</body>
</html>
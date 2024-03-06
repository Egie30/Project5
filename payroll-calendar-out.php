<?php 
	// include "framework/database/connect.php";

	//echo $filterOption;	
	//echo $filter_date;
	
	if($filterOption=="FLR_ATND"){
		$disabledF 	= "disabled";
	} else {
		$disabledF 	= "";
	}
	
	/*
	$queryconfig 	= "SELECT PAY_BEG_DTE, PAY_END_DTE FROM PAY.PAY_CONFIG_DTE WHERE PAY_ACT_F=1";
	$resultconfig 	= mysql_query($queryconfig);	
	$rowconfig 	= mysql_fetch_array($resultconfig);
	$PayBegDte 	= $rowconfig['PAY_BEG_DTE'];
	$PayEndDte 	= $rowconfig['PAY_END_DTE'];
	*/
	
	if ($filter_date!="") {
		$data	= explode(" ",$filter_date);
		$month	= $data[0];
		$year	= $data[1];
	}
	
	if($filterOption == "FLR_ATND"){
		if($_GET['BULAN'] == ""){
			$_GET['BULAN'] = date('m');
		}
		if($_GET['TAHUN'] == ""){
			$_GET['TAHUN'] = date('Y');
		}
		$PayBegDte 	= $_GET['TAHUN']."-".$_GET['BULAN']."-01";
		$PayEndDte 	= $_GET['TAHUN']."-".$_GET['BULAN']."-31";
	}else{
		$PayBegDte 	= $year."-".$month."-01";
		$PayEndDte 	= $year."-".$month."-31";
	}
	
	function convertToHoursMins($time, $format = '%02d:%02d') {
		if ($time < 1) {
			return;
		}
		$hours 		= floor($time / 60);
		$minutes 	= ($time % 60);
		return sprintf($format, $hours, $minutes);
	}

	if ($_POST['PRSN_NBR'] != "") {
		if (isset($_POST['APV_F'])) {
			$ClokNbr 	= "";
			$apv 		= $_POST['APV_F'];
        	for ($i=0; $i < count($apv) ; $i++){
				if($apv[$i]>0){
					$ClokNbr = $ClokNbr.$apv[$i].",";
				}
        	}
			//echo 'cloknbr'.$ClokNbr;
			if($ClokNbr != ""){
				$WhereIn 	= substr($ClokNbr,0,-1);

				$queryUpd 	= "UPDATE PAY.MACH_CLOK_PROCESS SET OT_TM_F=0, UPD_TS=CURRENT_TIMESTAMP, UPD_NBR='".$_SESSION['personNBR']."' 
							WHERE DATE(CLOK_IN_TS) BETWEEN '".$PayBegDte."' AND '".$PayEndDte."' AND PRSN_NBR = '".$_POST['PRSN_NBR']."'";
				$resultUpd 	= mysql_query($queryUpd);

				$queryUpdCek 	= "UPDATE PAY.MACH_CLOK_PROCESS SET OT_TM_F=1, UPD_TS=CURRENT_TIMESTAMP, UPD_NBR='".$_SESSION['personNBR']."'  
							WHERE DATE(CLOK_IN_TS) BETWEEN '".$PayBegDte."' AND '".$PayEndDte."'
								AND CLOK_NBR IN (".$WhereIn.") AND PRSN_NBR = '".$_POST['PRSN_NBR']."'";
				$resultUpdCek	= mysql_query($queryUpdCek);
				//echo $queryUpdCek;
			} 
    	} else {
			$queryUpd 	= "UPDATE PAY.MACH_CLOK_PROCESS SET OT_TM_F=0, UPD_TS=CURRENT_TIMESTAMP, UPD_NBR='".$_SESSION['personNBR']."' 
							WHERE DATE(CLOK_IN_TS) BETWEEN '".$PayBegDte."' AND '".$PayEndDte."' AND PRSN_NBR = '".$_POST['PRSN_NBR']."'";
			$resultUpd 	= mysql_query($queryUpd);
		}
	}
?>

<form enctype="multipart/form-data" action="#" method="post">
	<table class="HeadTab" style="width:500px";>
		<input type="hidden" name="PRSN_NBR" id="PRSN_NBR" value="<?php echo $PrsnNbr;?>" >
		<tr>
			<!-- table left bulan sekarang -->
			<td class="CalRight" style="width:100px";>
				<table class="TblRight">
					<tr>
						<td class='time-card-top' colspan=8>Bulan 
							<?php 
								$PymtDteOld = date('F', strtotime($PayBegDte));
								echo $PymtDteOld;
							?>
						</td>
					</tr>

					<tr>
						<td class='time-card-top' rowspan=2 style='vertical-align : middle;'>Tgl</td>
						<td class='time-card-top' colspan=2>I</td>
						<td class='time-card-top' colspan=2>II</td>
						<td class='time-card-top' colspan=2>III</td>
						<td class='time-card-top' rowspan=2 style='vertical-align : middle;'>Total</td>
						<td class='time-card-top' rowspan=2 style='vertical-align : middle;'>Lembur</td>
						<td class='time-card-top' rowspan=2 style='vertical-align : middle;'>Approve</td>
						<td class='time-card-center' style='vertical-align : middle;'></td>
						<td class='time-card-center' <?php echo $hide;?> style='vertical-align : middle;'></td>
					</tr>

					<tr>
						<td class='time-card'>In</td>
						<td class='time-card'>Out</td>
						<td class='time-card'>In</td>
						<td class='time-card'>Out</td>
						<td class='time-card'>In</td>
						<td class='time-card'>Out</td>
					</tr>
						<?php
							$nbrDays 	= 0;
							$month 		= parseMonth($PymtDte);
							$year 		= parseYear($PymtDte);

							$queryHol = "SELECT DATE_FORMAT(HLDY_DTE, '%D') AS DAY 
											FROM PAY.HOLIDAY 
											WHERE HLDY_DTE BETWEEN '".$PayBegDte."' AND '".$PayEndDte."'";
							$resHol   = mysql_query($queryHol);
							$OldHoliday = array();
							while ($rowHol = mysql_fetch_array($resHol)) {
								$OldHoliday[]= $rowHol['DAY'];
							}
							$outAll = 0;
							for($day=1;$day<=31;$day++){
								if (in_array($day, $OldHoliday)){
									//echo "tanggal ".$day." adalah tanggal merah";
									$MultLembur    = 2;
									$colorCalendar = "color:#d92115;";
								}else{
									$MultLembur    = 1;
									$colorCalendar ='';
								}
								echo "<tr style='".$colorCalendar."'>";
								if($day<=31){
									echo "<td class='time-card'>".$day."</td>";
									$query 	= "SELECT 	CLOK_NBR,
														CLOK_IN_TS,
														CLOK_OT_TS,
														HOUR(CLOK_IN_TS) AS HR_IN,
														OT_TM_F,
														TIMESTAMPDIFF(MINUTE, CLOK_IN_TS, CLOK_OT_TS) AS JAM_ALL,
                                                        (TIMESTAMPDIFF(MINUTE, CLOK_IN_TS, CLOK_OT_TS) - 480) AS LEMBUR_MIN,
														(CASE WHEN TIME(CLOK_IN_TS) < '12:00:00'
															  THEN  
																CASE WHEN TIMEDIFF(CLOK_IN_TS, CONCAT(DATE(CLOK_IN_TS),' ','09:00:00')) > '00:15:00'
																THEN 'OV' ELSE '' END
															  WHEN TIME(CLOK_IN_TS) >= '12:00:00' AND TIME(CLOK_IN_TS) < '18:00:00'
															  THEN 
															  	CASE WHEN TIMEDIFF(CLOK_IN_TS, CONCAT(DATE(CLOK_IN_TS),' ','12:15:00')) > '00:15:00'
															  	THEN 'OV' ELSE '' END
															  WHEN TIME(CLOK_IN_TS) >= '18:00:00'
															  	THEN
															  	CASE WHEN TIMEDIFF(CLOK_IN_TS, CONCAT(DATE(CLOK_IN_TS),' ','18:15:00')) > '00:15:00'
															  	THEN 'OV' ELSE '' END
														END) AS OVER
													FROM PAY.MACH_CLOK_PROCESS
													WHERE PRSN_NBR=".$PrsnNbr." AND
													DAY(CLOK_IN_TS)=".$day." AND
													DATE(CLOK_IN_TS) BETWEEN '".$PayBegDte."' AND '".$PayEndDte."' 
													ORDER BY CLOK_IN_TS";
									//echo "<pre>".$query."</pre>";
									$result = mysql_query($query);
									$shift 	  = 3;
									$morning  =0; 
									$afternoon=0; 
									$night    =0; 
									$hours    =0;
									$morningOT  =0; 
									$afternoonOT=0; 
									$nightOT    =0;
									$in_1=""; 
									$ot_1="";
									$in_2=""; 
									$ot_2="";
									$in_3=""; 
									$ot_3="";
									$ov_1="";
									$ov_2="";
									$ov_3="";

									$jum 	= mysql_num_rows($result);
									$data 	= array();
									$sttOvr = array();

									if($jum==3) {
										$i=0;

										while($rowh=mysql_fetch_array($result)){
											if($rowh['LEMBUR_MIN']>0){
												$data[$i]=$rowh['CLOK_IN_TS']."|".$rowh['CLOK_OT_TS']."|".$rowh['HR_IN']."|".$rowh['LEMBUR_MIN']."|".$rowh['CLOK_NBR'];
											} else {
												$data[$i]=$rowh['CLOK_IN_TS']."|".$rowh['CLOK_OT_TS']."|".$rowh['HR_IN']."|0|".$rowh['CLOK_NBR'];
											}
											$sttOvr[]=$rowh['OVER'];
											$i++;
											if($rowh['OT_TM_F']==1){ $cekF = "checked"; } else { $cekF = ""; }	
											$cek 	= '<input '.$cekF.' '.$disabledF.' type="checkbox" id="APV_F_'.$day.'" name="APV_F[]" value="'.$rowh['CLOK_NBR'].'" onchange="toggleCheckbox(this)">';
										}	

										$one=explode("|",$data[0]);
										$in_1=$one[0]; 
										$ot_1=$one[1];
										$ov_1=$sttOvr[0]; 

										if($one[1]!=0){
											$morning	= strtotime($one[1])-strtotime($one[0]);
											$morningOT 	= $one[3]*$MultLembur;
										}

										$two=explode("|",$data[1]);
										$in_2=$two[0]; 
										$ot_2=$two[1];
										$ov_2=$sttOvr[1];
										if($two[1]!=0){
											$afternoon 		= strtotime($two[1])-strtotime($two[0]);
											$afternoonOT 	= $two[3]*$MultLembur;
										}

										$three= explode("|",$data[2]);
										$in_3 = $three[0]; 
										$ot_3 = $three[1];
										$ov_3 = $sttOvr[2];

										if($three[1]!=0){
											$night 		= strtotime($three[1])-strtotime($three[0]);
											$nightOT	= $three[3]*$MultLembur;
										}
										$LemburAll 	= $morningOT+$afternoonOT+$nightOT;
										$cek1 	= '<input '.$cekF.' type="hidden" id="L_APV_F_'.$day.'" name="L_APV_F[]" value="'.$LemburAll.'">';
										//$cek 	= '<input '.$cekF.' type="checkbox" id="APV_F_'.$day.'" name="APV_F[]" value="'.$ClokNbrAll.'" onchange="toggleCheckbox(this)">';
										
									} else if($jum==2){
										$i=0;
										while($rowh=mysql_fetch_array($result)){
											if($rowh['LEMBUR_MIN']>0){
												$data[$i]=$rowh['CLOK_IN_TS']."|".$rowh['CLOK_OT_TS']."|".$rowh['HR_IN']."|".$rowh['LEMBUR_MIN']."|".$rowh['CLOK_NBR'];
											} else {
												$data[$i]=$rowh['CLOK_IN_TS']."|".$rowh['CLOK_OT_TS']."|".$rowh['HR_IN']."|0|".$rowh['CLOK_NBR'];
											}
											$sttOvr[]=$rowh['OVER'];
											$i++;
											if($rowh['OT_TM_F']==1){ $cekF = "checked"; } else { $cekF = ""; }
											$cek 	= '<input '.$cekF.' '.$disabledF.' type="checkbox" id="APV_F_'.$day.'" name="APV_F[]" value="'.$rowh['CLOK_NBR'].'" onchange="toggleCheckbox(this)">';
										}
										//echo $data[0]."<br>".$data[1];

										$one=explode("|",$data[0]);
										if($one[2]<12) {
											$posisi1 = $one[3]." - 1";
											$in_1=$one[0]; 
											$ot_1=$one[1];
											$ov_1=$sttOvr[0];
											if($one[1]!=0){
												$morning	= strtotime($one[1])-strtotime($one[0]);
												$morningOT 	= $one[3]*$MultLembur;
												//$morningOT 	= ((strtotime($one[1])-strtotime($one[0]))/60)*$MultLembur;
											}
										} else if(($one[2]>=12)&&($one[2]<18)){
											$posisi1 = $one[2]." - 2";
											$in_2=$one[0]; 
											$ot_2=$one[1];
											$ov_2=$sttOvr[0];
											if($one[1]!=0){
												$afternoon		= strtotime($one[1])-strtotime($one[0]);
												$afternoonOT 	= $one[3]*$MultLembur;
											}
										}

										$two=explode("|",$data[1]);
										if(($two[2]>=12)&&($two[2]<18)){
											$posisi2 = $two[2]." - 3";
											$in_2=$two[0]; 
											$ot_2=$two[1];
											$ov_2=$sttOvr[1];
											if($two[1]!=0){
												$afternoon 		= strtotime($two[1])-strtotime($two[0]);
												$afternoonOT	= $two[3]*$MultLembur;
											}
										} else if($two[2]>=18){
											$posisi2 = $two[2]." - 4";
											$in_3=$two[0]; 
											$ot_3=$two[1];
											$ov_3=$sttOvr[1];
											if($two[1]!=0){
												$night		= strtotime($two[1])-strtotime($two[0]);
												$nightOT 	= ((strtotime($two[1])-strtotime($two[0]))/60)*$MultLembur;
											}
										}
										
										$LemburAll 	= $morningOT+$afternoonOT+$nightOT;
									//	echo $morningOT."-".$afternoonOT."-".$nightOT;
										$cek1 	= '<input '.$cekF.' type="hidden" id="L_APV_F_'.$day.'" name="L_APV_F[]" value="'.$LemburAll.'">';
										//$cek 	= '<input '.$cekF.' checked type="checkbox" id="APV_F_'.$day.'" name="APV_F[]" value="'.$ClokNbrAll.'" onchange="toggleCheckbox(this)">';
										
									} else if($jum==1){
										$i=0;
										while($rowh=mysql_fetch_array($result)){
											if($rowh['LEMBUR_MIN']>0){
												$data[$i]=$rowh['CLOK_IN_TS']."|".$rowh['CLOK_OT_TS']."|".$rowh['HR_IN']."|".$rowh['LEMBUR_MIN']."|".$rowh['CLOK_NBR'];
											} else {
												$data[$i]=$rowh['CLOK_IN_TS']."|".$rowh['CLOK_OT_TS']."|".$rowh['HR_IN']."|0|".$rowh['CLOK_NBR'];
											}
											$sttOvr[]=$rowh['OVER'];
											$i++;
											if($rowh['OT_TM_F']==1){ $cekF = "checked"; } else { $cekF = ""; }
											$cek 	= '<input '.$cekF.' '.$disabledF.' type="checkbox" id="APV_F_'.$day.'" name="APV_F[]" value="'.$rowh['CLOK_NBR'].'" onchange="toggleCheckbox(this)">';
										}

										$one=explode("|",$data[0]);
										if($one[2]<12) {
											$in_1=$one[0]; 
											$ot_1=$one[1];
											$ov_1=$sttOvr[0];
											if($one[1]!=0){
												$morning 	= strtotime($one[1])-strtotime($one[0]);
												$morningOT 	= $one[3]*$MultLembur;
											}
										} else if(($one[2]>=12)&&($one[2]<18)){
											$in_2=$one[0]; 
											$ot_2=$one[1];
											$ov_2=$sttOvr[0];
											if($one[1]!=0){
												$afternoon 		= strtotime($one[1])-strtotime($one[0]);
												$afternoonOT	= $one[3]*$MultLembur;
											}
										} else if($one[2]>=18){
											$in_3=$one[0]; 
											$ot_3=$one[1];
											$ov_3=$sttOvr[0];
											if($one[1]!=0){
												$night 		= strtotime($one[1])-strtotime($one[0]);
												$nightOT 	= $one[3]*$MultLembur;
											}
										}
										$LemburAll 	= $morningOT+$afternoonOT+$nightOT;
										$cek1 	= '<input '.$cekF.' type="hidden" id="L_APV_F_'.$day.'" name="L_APV_F[]" value="'.$LemburAll.'">';
										//$cek 	= '<input '.$cekF.' type="checkbox" id="APV_F_'.$day.'" name="APV_F[]" value="'.$ClokNbrAll.'" onchange="toggleCheckbox(this)">';
										
									} else {
										$in_1=""; 
										$ot_1="";
										$in_2=""; 
										$ot_2="";
										$in_3=""; 
										$ot_3="";
										$ov_1=""; 
										$ov_2=""; 
										$ov_3="";
										$cekF 	= "";
										$cek1 	= '<input type="hidden" id="L_APV_F_'.$day.'" name="L_APV_F[]" value="0">';
										$cek 	= '<input '.$disabledF.' type="checkbox" id="APV_F_'.$day.'" name="APV_F[]" value="0" onchange="toggleCheckbox(this)">';
										$LemburAll = 0;
									}
									
									$OverTime1=''; $OverTime2=''; $OverTime3='';
									if ($in_1==''){
										if ($in_2 == ''){
											if ($in_3 !=''){
												if ($ov_3 == 'OV'){
													$OverTime3 = "color:#fbad06;";
												}else{
													$OverTime1 = "";
													$OverTime2 = "";
												}
											}
											
										}else{
											if ($ov_2 == 'OV'){
												$OverTime2 = "color:#fbad06;";
											}else{
												$OverTime1 = "";
												$OverTime3 = "";
											}
										}
									}else{ 

										if ($ov_1 == 'OV' && $in_1 !=''){
											$OverTime1 = "color:#fbad06;"; 

										}else{
											$OverTime2 ="";
											$OverTime3 ="";
										}
									}

									echo "<td id='time-".$day."0102' class='time-card-print' style='".$OverTime1."'>
												<div id='ValTime' style='float:left;margin-top:5px;'>".parseTimeShort($in_1)."</div>
												<div id='time-upDown-".$day."0102' class='time-upDown'>
												<div id='time-up-".$day."0102' class='up-down-clock'>
												<div class='listUp'><span class='fa fa-chevron-up'></span></div>
												<div class='listDown'><span class='fa fa-chevron-down'></span><div>
												</div></div>
										 </td>";
									echo "<td id='time-".$day."0202' class='time-card-print'>
												<div id='ValTime' style='float:left;margin-top:5px;'>".parseTimeShort($ot_1)."</div>
												<div id='time-upDown-".$day."0202' class='time-upDown'>
												<div id='time-up-".$day."0202' class='up-down-clock'>
												<div class='listUp'><span class='fa fa-chevron-up'></span></div>
												<div class='listDown'><span class='fa fa-chevron-down'></span><div>
												</div></div>
										 </td>";
									echo "<td id='time-".$day."0302' class='time-card-print' style='".$OverTime2."'>
												<div id='ValTime' style='float:left;margin-top:5px;'>".parseTimeShort($in_2)."</div>
												<div id='time-upDown-".$day."0302' class='time-upDown'>
												<div id='time-up-".$day."0302' class='up-down-clock'>
												<div class='listUp'><span class='fa fa-chevron-up'></span></div>
												<div class='listDown'><span class='fa fa-chevron-down'></span><div>
												</div></div>
										 </td>"; 											 
									echo "<td id='time-".$day."0402' class='time-card-print'>
												<div id='ValTime' style='float:left;margin-top:5px;'>".parseTimeShort($ot_2)."</div>
												<div id='time-upDown-".$day."0402' class='time-upDown'>
												<div id='time-up-".$day."0402' class='up-down-clock'>
												<div class='listUp'><span class='fa fa-chevron-up'></span></div>
												<div class='listDown'><span class='fa fa-chevron-down'></span><div>
												</div></div>
										 </td>";
									echo "<td id='time-".$day."0502' class='time-card-print' style='".$OverTime3."'>
												<div id='ValTime' style='float:left;margin-top:5px;'>".parseTimeShort($in_3)."</div>
												<div id='time-upDown-".$day."0502' class='time-upDown'>
												<div id='time-up-".$day."0502' class='up-down-clock'>
												<div class='listUp'><span class='fa fa-chevron-up'></span></div>
												<div class='listDown'><span class='fa fa-chevron-down'></span><div>
												</div></div>
										 </td>"; 											 
									echo "<td id='time-".$day."0602' class='time-card-print'>
												<div id='ValTime' style='float:left;margin-top:5px;'>".parseTimeShort($ot_3)."</div>
												<div id='time-upDown-".$day."0602' class='time-upDown'>
												<div id='time-up-".$day."0602' class='up-down-clock'>
												<div class='listUp'><span class='fa fa-chevron-up'></span></div>
												<div class='listDown'><span class='fa fa-chevron-down'></span><div>
												</div></div>
										  </td>";

									$hours 	=($morning+$afternoon+$night)/3600;
									
									$out	= $hours-8; 
									if(($cekF=="checked")&&($LemburAll>0)) { 
										$LemburSum = $LemburSum+$LemburAll; 
									}
									echo "<td id='diff-".$day."02' class='time-card-print' style='margin-top:5px;vertical-align : middle;'>".number_format($hours,1)."</td>";
									
									if($LemburAll<=0){
										echo "<td class='time-card-print' style='float:center;margin-top:5px;vertical-align : middle;'>00 : 00</td>";
									} else {
										echo "<td class='time-card-print' style='float:center;margin-top:5px;vertical-align : middle;'>".convertToHoursMins($LemburAll, '%02d : %02d')."</td>";
									}
									//echo convertToHoursMins(250, '%02d hours %02d minutes');
									
									echo "<td class='time-card-print' style='float:center;margin-top:5px;vertical-align : middle;'>".$cek1.$cek."</td>";

									if($hours>0){
										if($hours<=5){$nbrDays+=0.5;}else{$nbrDays+=1;}
									}

									$totHours+=$hours;
								}
							}

							echo "</tr>
								  <tr>
								  		<td class='time-card'></td>
								  		<td class='time-card'></td>
								  		<td class='time-card'></td>
								  		<td class='time-card'></td>
								  		<td class='time-card'></td>
								  		<td class='time-card'></td>
								  		<td class='time-card'></td>
								  		<td class='time-card'></td>
										<td class='time-card'></td>
										<td class='time-card'></td>
								  </tr>";
						?>
				</table>
			</td>
		</tr>
	</table>
	<!--<h3>Total Lembur yang Disetujui <?php echo convertToHoursMins($LemburSum, '%02d jam %02d menit'); ?></h3> <br/>-->
	<div id="lemburSum">
		<h3>Total Lembur yang Disetujui <?php echo convertToHoursMins($LemburSum, '%02d jam %02d menit'); ?></h3> <br/>
	</div>
	<?php if($filterOption!="FLR_ATND"){ ?>
	<input class="process" type="submit" value="Simpan"/>
	<?php } ?>
</form>
<script>
function timeConvert(n) {
	var num 		= n;
	var hours 		= (num / 60);
	var rhours 		= Math.floor(hours);
	var minutes 	= (hours - rhours) * 60;
	var rminutes 	= Math.round(minutes);
	return rhours + " jam " + rminutes + " menit";
}

function toggleCheckbox(element){	
	var totAll=0, valMenit, valJamMenit;
	for(let i = 1; i <= 31; i++){
		var idCheck 		= "APV_F_"+i;
		var idCheckHelp 	= "L_APV_F_"+i; 
		console.log(idCheck);
		if(document.getElementById(idCheck).checked == true){
			valMenit 	= parseInt(document.getElementById(idCheckHelp).value);
			totAll 		= totAll + valMenit;
			console.log(totAll);
		} 
	}
	console.log("total menit lembur " + totAll);
	document.getElementById("lemburSum").innerHTML = "<h3>Total Lembur yang Disetujui " + timeConvert(totAll) + "</h3>";
}
</script>
<!-- </body> -->
<!-- </html> -->
<?php 
	// include "framework/database/connect.php";
?>
	<table class="HeadTab">
		<?php if ($_GET['PYMT_DTE']==''){$yearCal= date('Y');}else{ $yearCal =  date('Y',strtotime($_GET['PYMT_DTE']));} ?>
		<?php if ($_GET['PYMT_DTE']==''){$monthCal = date('m');}else{  $monthCal=  date('m',strtotime($_GET['PYMT_DTE']));} ?>

		<input type="hidden" name="MONTH" id="MONTH" value="<?php echo $monthCal;?>" >
		<input type="hidden" name="YEAR" id="YEAR" value="<?php echo $yearCal;?>" >
		<tr>
			<!-- table left old month -->
			<td class="CalLeft">
				<table class="TblLeft">
					<tr>
						<td class="time-card-top" colspan=8>Bulan 
							<?php 
								$PymtDteOld = date('F', strtotime($PymtDte." -1 month"));
								echo $PymtDteOld;
							?>
						</td>
					</tr>
					<tr>
						<td class='time-card-top' rowspan=2>Tgl</td>
						<td class='time-card-top' colspan=2>I</td>
						<td class='time-card-top' colspan=2>II</td>
						<td class='time-card-top' colspan=2>III</td>
						<td class='time-card-top' rowspan=2>Total</td>
						<td class='time-card-center'></td>
						<td class='time-card-center' <?php echo $hide;?>></td>
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
							$OldMonth	= date('m', strtotime($PymtDte." -1 month"));

							if ($OldMonth==12) {
								$year = parseYear($PymtDte-1);
							} else { 
								$year=parseYear($PymtDte); 
							}
							$queryHol = "SELECT DATE_FORMAT(HLDY_DTE, '%D') AS DAY 
											FROM PAY.HOLIDAY 
											WHERE YEAR(HLDY_DTE) = ".$year. " 
												AND MONTH(HLDY_DTE)=".$OldMonth;
							$resHol   = mysql_query($queryHol);
							$OldHoliday = array();
							while ($rowHol = mysql_fetch_array($resHol)) {
								$OldHoliday[]= $rowHol['DAY'];
							}


							for($day=1;$day<=31;$day++){
								if (in_array($day, $OldHoliday)){
									$colorCalendar = "color:#d92115;";
								}else{
									$colorCalendar ='';
								}
								echo "<tr style='".$colorCalendar."'>";

								if($day<=31){
									echo "<td class='time-card'>".$day."</td>";
									$query 	= "SELECT 	CLOK_IN_TS,
														CLOK_OT_TS,
														HOUR(CLOK_IN_TS) AS HR_IN,
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
												FROM PAY.MACH_CLOK
												WHERE PRSN_NBR=".$PrsnNbr." 
													  AND DAY(CLOK_IN_TS)=".$day." 
													  AND MONTH(CLOK_IN_TS)=".$OldMonth." 
													  AND YEAR(CLOK_IN_TS)=".$year." 
												ORDER BY CLOK_IN_TS";

									$result = mysql_query($query);
									
									$shift 	      = 3;
									$morning_min  = 0; 
									$afternoon_min= 0; 
									$night_min    = 0; 
									$hours_min    = 0;
									
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
											$data[$i]=$rowh['CLOK_IN_TS']."|".$rowh['CLOK_OT_TS']."|".$rowh['HR_IN'];
											$sttOvr[]=$rowh['OVER'];
												$i++;
										}

										$one = explode("|",$data[0]);
										$in_1= $one[0]; 
										$ot_1= $one[1];
										$ov_1= $sttOvr[0];

										if($one[1]!=0){
											$morning_min=strtotime($one[1])-strtotime($one[0]);
										}

										$two = explode("|",$data[1]);
										$in_2= $two[0]; 
										$ot_2= $two[1];
										$ov_2= $sttOvr[1];

										if($two[1]!=0){
											$afternoon_min=strtotime($two[1])-strtotime($two[0]);
										}

										$three= explode("|",$data[2]);
										$in_3 = $three[0]; 
										$ot_3 = $three[1];
										$ov_3 = $sttOvr[2];

										if($three[1]!=0){
											$night_min=strtotime($three[1])-strtotime($three[0]);
										}							
									} else if($jum==2){
										$i=0;
										while($rowh=mysql_fetch_array($result)){
											$data[$i]=$rowh['CLOK_IN_TS']."|".$rowh['CLOK_OT_TS']."|".$rowh['HR_IN'];
											$sttOvr[]=$rowh['OVER'];
											$i++;
										}

										$one=explode("|",$data[0]);
										if($one[2]<12) {
											$in_1=$one[0]; 
											$ot_1=$one[1];
											$ov_1=$sttOvr[0];

											if($one[1]!=0){
												$morning_min=strtotime($one[1])-strtotime($one[0]);
											}
												
										} else if(($one[2]>=12)&&($one[2]<18)){
											$in_2=$one[0]; 
											$ot_2=$one[1];
											$ov_2=$sttOvr[0];

											if($one[1]!=0){
												$afternoon_min=strtotime($one[1])-strtotime($one[0]);
											}
										}

										$two=explode("|",$data[1]);
										if(($two[2]>=12)&&($two[2]<18)){
											$in_2=$two[0]; 
											$ot_2=$two[1];
											$ov_2=$sttOvr[1];

											if($two[1]!=0){
												$afternoon_min=strtotime($two[1])-strtotime($two[0]);
											}
										} else if($two[2]>=18){
											$in_3=$two[0]; 
											$ot_3=$two[1];
											$ov_3=$sttOvr[1];

											if($two[1]!=0){
												$night_min=strtotime($two[1])-strtotime($two[0]);
											}
										}
									} else if($jum==1){
										$i=0;

										while($rowh=mysql_fetch_array($result)){
											$data[$i]=$rowh['CLOK_IN_TS']."|".$rowh['CLOK_OT_TS']."|".$rowh['HR_IN'];
											$sttOvr[]=$rowh['OVER'];
											$i++;
										}

										$one=explode("|",$data[0]);
										if($one[2]<12) {
											$in_1=$one[0]; 
											$ot_1=$one[1];
											$ov_1=$sttOvr[0];

											if($one[1]!=0){
												$morning_min=strtotime($one[1])-strtotime($one[0]);
											}
										} else if(($one[2]>=12)&&($one[2]<18)){
											$in_2=$one[0]; 
											$ot_2=$one[1];
											$ov_2=$sttOvr[0];

											if($one[1]!=0){
												$afternoon_min=strtotime($one[1])-strtotime($one[0]);
											}
										} else if($one[2]>=18){
											$in_3=$one[0]; 
											$ot_3=$one[1];
											$ov_3=$sttOvr[0];

											if($one[1]!=0){
												$night_min=strtotime($one[1])-strtotime($one[0]);
											}
										}
									} else {
										$in_1=""; $ot_1="";
										$in_2=""; $ot_2="";
										$in_3=""; $ot_3="";
										$ov_1=""; $ov_2=""; $ov_3= "";
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
									
									
									echo "<td id='time-".$day."0101' class='time-card-print' style='".$OverTime1."'>
											  <div id='ValTime' style='float:left;margin-top:5px;'>".parseTimeShort($in_1)."</div>
											  <div id='time-upDown-".$day."0101' class='time-upDown'>
											  <div id='time-up-".$day."0101' class='up-down-clock'>
											  <div class='listUp'><span class='fa fa-chevron-up'></span></div>
											  <div class='listDown'><span class='fa fa-chevron-down'></span><div>
											  </div></div>
										  </td>"; 											 
									echo "<td id='time-".$day."0201' class='time-card-print'>
										  	  <div id='ValTime' style='float:left;margin-top:5px;'>".parseTimeShort($ot_1)."</div>
										  	  <div id='time-upDown-".$day."0201' class='time-upDown'>
										  	  <div id='time-up-".$day."0201' class='up-down-clock'>
										  	  <div class='listUp'><span class='fa fa-chevron-up'></span></div>
										  	  <div class='listDown'><span class='fa fa-chevron-down'></span><div>
										  	  </div></div>
										  </td>";
									echo "<td id='time-".$day."0301' class='time-card-print' style='".$OverTime2."'>
											  <div id='ValTime' style='float:left;margin-top:5px;'>".parseTimeShort($in_2)."</div>
											  <div id='time-upDown-".$day."0301' class='time-upDown'>
											  <div id='time-up-".$day."0301' class='up-down-clock'>
											  <div class='listUp'><span class='fa fa-chevron-up'></span></div>
											  <div class='listDown'><span class='fa fa-chevron-down'></span><div>
											  </div></div>
										  </td>"; 											 
									echo "<td id='time-".$day."0401' class='time-card-print'>
											  <div id='ValTime' style='float:left;margin-top:5px;'>".parseTimeShort($ot_2)."</div>
											  <div id='time-upDown-".$day."0401' class='time-upDown'>
											  <div id='time-up-".$day."0401' class='up-down-clock'>
											  <div class='listUp'><span class='fa fa-chevron-up'></span></div>
											  <div class='listDown'><span class='fa fa-chevron-down'></span><div>
											  </div></div>
										  </td>";
									echo "<td id='time-".$day."0501' class='time-card-print' style='".$OverTime3."'>
											  <div id='ValTime' style='float:left;margin-top:5px;'>".parseTimeShort($in_3)."</div>
											  <div id='time-upDown-".$day."0501' class='time-upDown'>
											  <div id='time-up-".$day."0501' class='up-down-clock'>
											  <div class='listUp'><span class='fa fa-chevron-up'></span></div>
											  <div class='listDown'><span class='fa fa-chevron-down'></span><div>
											  </div></div>
										  </td>"; 											 
									echo "<td id='time-".$day."0601' class='time-card-print'>
											  <div id='ValTime' style='float:left;margin-top:5px;'>".parseTimeShort($ot_3)."</div>
											  <div id='time-upDown-".$day."0601' class='time-upDown'>
											  <div id='time-up-".$day."0601' class='up-down-clock'>
											  <div class='listUp'><span class='fa fa-chevron-up'></span></div>
											  <div class='listDown'><span class='fa fa-chevron-down'></span><div>
											  </div></div>
										  </td>";

									$hours_min=($morning_min+$afternoon_min+$night_min)/3600;
									echo "<td id='diff-".$day."01' class='time-card-print'>".number_format($hours_min,1)."</td>";	
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
								  </tr>";
							?>
				</table>
			</td>

			<!-- table left bulan sekarang -->
			<td class="CalRight">
				<table class="TblRight">
					<tr>
						<td class='time-card-top' colspan=8>Bulan 
							<?php 
								$PymtDteOld = date('F', strtotime($PymtDte));
								echo $PymtDteOld;
							?>
						</td>
					</tr>

					<tr>
						<td class='time-card-top' rowspan=2>Tgl</td>
						<td class='time-card-top' colspan=2>I</td>
						<td class='time-card-top' colspan=2>II</td>
						<td class='time-card-top' colspan=2>III</td>
						<td class='time-card-top' rowspan=2>Total</td>
						<td class='time-card-center'></td>
						<td class='time-card-center' <?php echo $hide;?>></td>
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
											WHERE YEAR(HLDY_DTE) = ".$year. " 
												AND MONTH(HLDY_DTE)=".$month;
							$resHol   = mysql_query($queryHol);
							$OldHoliday = array();
							while ($rowHol = mysql_fetch_array($resHol)) {
								$OldHoliday[]= $rowHol['DAY'];
							}

							for($day=1;$day<=31;$day++){
								if (in_array($day, $OldHoliday)){
									$colorCalendar = "color:#d92115;";
								}else{
									$colorCalendar ='';
								}
								echo "<tr style='".$colorCalendar."'>";
								if($day<=31){
									echo "<td class='time-card'>".$day."</td>";
									$query 	= "SELECT 	CLOK_IN_TS,
														CLOK_OT_TS,
														HOUR(CLOK_IN_TS) AS HR_IN,
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
													FROM PAY.MACH_CLOK
													WHERE PRSN_NBR=".$PrsnNbr." 
														  AND DAY(CLOK_IN_TS)=".$day." 
														  AND MONTH(CLOK_IN_TS)=".$month." 
														  AND YEAR(CLOK_IN_TS)=".$year." 
													ORDER BY CLOK_IN_TS";
									$result = mysql_query($query);
									$shift 	  = 3;
									$morning  =0; 
									$afternoon=0; 
									$night    =0; 
									$hours    =0;
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
											$data[$i]=$rowh['CLOK_IN_TS']."|".$rowh['CLOK_OT_TS']."|".$rowh['HR_IN'];
											$sttOvr[]=$rowh['OVER'];
											$i++;
										}	

										$one=explode("|",$data[0]);
										$in_1=$one[0]; 
										$ot_1=$one[1];
										$ov_1=$sttOvr[0]; 

										if($one[1]!=0){
											$morning=strtotime($one[1])-strtotime($one[0]);
										}

										$two=explode("|",$data[1]);
										$in_2=$two[0]; 
										$ot_2=$two[1];
										$ov_2=$sttOvr[1];
										if($two[1]!=0){
											$afternoon=strtotime($two[1])-strtotime($two[0]);
										}

										$three= explode("|",$data[2]);
										$in_3 = $three[0]; 
										$ot_3 = $three[1];
										$ov_3 = $sttOvr[2];

										if($three[1]!=0){
											$night=strtotime($three[1])-strtotime($three[0]);
										}
									} else if($jum==2){
										$i=0;
										while($rowh=mysql_fetch_array($result)){
											$data[$i]=$rowh['CLOK_IN_TS']."|".$rowh['CLOK_OT_TS']."|".$rowh['HR_IN'];
											$sttOvr[]=$rowh['OVER'];
											$i++;
										}

										$one=explode("|",$data[0]);
										if($one[2]<12) {
											$in_1=$one[0]; 
											$ot_1=$one[1];
											$ov_1=$sttOvr[0];
											if($one[1]!=0){
												$morning=strtotime($one[1])-strtotime($one[0]);
											}
										} else if(($one[2]>=12)&&($one[2]<18)){
											$in_2=$one[0]; 
											$ot_2=$one[1];
											$ov_2=$sttOvr[0];
											if($one[1]!=0){
												$afternoon=strtotime($one[1])-strtotime($one[0]);
											}
										}

										$two=explode("|",$data[1]);
										if(($two[2]>=12)&&($two[2]<18)){
											$in_2=$two[0]; 
											$ot_2=$two[1];
											$ov_2=$sttOvr[1];
											if($two[1]!=0){
												$afternoon=strtotime($two[1])-strtotime($two[0]);
											}
										} else if($two[2]>=18){
											$in_3=$two[0]; 
											$ot_3=$two[1];
											$ov_3=$sttOvr[1];
											if($two[1]!=0){
												$night=strtotime($two[1])-strtotime($two[0]);
											}
										}
									} else if($jum==1){
										$i=0;
										while($rowh=mysql_fetch_array($result)){
											$data[$i]=$rowh['CLOK_IN_TS']."|".$rowh['CLOK_OT_TS']."|".$rowh['HR_IN'];
											$sttOvr[]=$rowh['OVER'];
											$i++;
										}

										$one=explode("|",$data[0]);
										if($one[2]<12) {
											$in_1=$one[0]; 
											$ot_1=$one[1];
											$ov_1=$sttOvr[0];
											if($one[1]!=0){
												$morning=strtotime($one[1])-strtotime($one[0]);
											}
										} else if(($one[2]>=12)&&($one[2]<18)){
											$in_2=$one[0]; 
											$ot_2=$one[1];
											$ov_2=$sttOvr[0];
											if($one[1]!=0){
												$afternoon=strtotime($one[1])-strtotime($one[0]);
											}
										} else if($one[2]>=18){
											$in_3=$one[0]; 
											$ot_3=$one[1];
											$ov_3=$sttOvr[0];
											if($one[1]!=0){
												$night=strtotime($one[1])-strtotime($one[0]);
											}
										}
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

									$hours=($morning+$afternoon+$night)/3600;
									echo "<td id='diff-".$day."02' class='time-card-print'>".number_format($hours,1)."</td>";

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
								  </tr>";
						?>
				</table>
			</td>
		</tr>
	</table>
<!-- </body> -->
<!-- </html> -->

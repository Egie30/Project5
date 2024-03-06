<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	include "framework/alert/alert.php";
	$PrsnNbr=$_GET['PRSN_NBR'];
	$PymtDte=$_GET['PYMT_DTE'];
	$Security=getSecurity($_SESSION['userID'],"Payroll");
	//Process changes here
	if($_POST['PRSN_NBR']!="")
	{
		$PrsnNbr=$_POST['PRSN_NBR'];
		$PymtDte=$_POST['PYMT_DTE'];
		
		//Take care of nulls
		if($_POST['BASE_AMT']==""){$BaseAmt="0";}else{$BaseAmt=$_POST['BASE_AMT'];}
		if($_POST['BASE_CNT']==""){$BaseCnt="0";}else{$BaseCnt=$_POST['BASE_CNT'];}
		if($_POST['BASE_TOT']==""){$BaseTot="0";}else{$BaseTot=$_POST['BASE_TOT'];}		
		if($_POST['ADD_AMT']==""){$AddAmt="0";}else{$AddAmt=$_POST['ADD_AMT'];}
		if($_POST['ADD_CNT']==""){$AddCnt="0";}else{$AddCnt=$_POST['ADD_CNT'];}
		if($_POST['ADD_TOT']==""){$AddTot="0";}else{$AddTot=$_POST['ADD_TOT'];}		
		if($_POST['OT_AMT']==""){$OTAmt="0";}else{$OTAmt=$_POST['OT_AMT'];}
		if($_POST['OT_CNT']==""){$OTCnt="0";}else{$OTCnt=$_POST['OT_CNT'];}
		if($_POST['OT_TOT']==""){$OTTot="0";}else{$OTTot=$_POST['OT_TOT'];}
		if($_POST['MISC_AMT']==""){$MiscAmt="0";}else{$MiscAmt=$_POST['MISC_AMT'];}
		if($_POST['MISC_CNT']==""){$MiscCnt="0";}else{$MiscCnt=$_POST['MISC_CNT'];}
		if($_POST['MISC_TOT']==""){$MiscTot="0";}else{$MiscTot=$_POST['MISC_TOT'];}
		if($_POST['BON_ATT_AMT']==""){$BonAttAmt="0";}else{$BonAttAmt=$_POST['BON_ATT_AMT'];}
		if($_POST['BON_WK_AMT']==""){$BonWkAmt="0";}else{$BonWkAmt=$_POST['BON_WK_AMT'];}
		if($_POST['BON_MO_AMT']==""){$BonMoAmt="0";}else{$BonMoAmt=$_POST['BON_MO_AMT'];}
		if($_POST['CRDT_WK']==""){$CrdtWk="0";}else{$CrdtWk=$_POST['CRDT_WK'];}
		if($_POST['DEBT_WK']==""){$DebtWk="0";}else{$DebtWk=$_POST['DEBT_WK'];}
		if($_POST['PAY_AMT']==""){$PayAmt="0";}else{$PayAmt=$_POST['PAY_AMT'];}
		if($_POST['CRDT_AMT']==""){$CrdtAmt="0";}else{$CrdtAmt=$_POST['CRDT_AMT'];}
		
		//Process add new
		$query="SELECT COUNT(*) AS CNT FROM PAY.PAYROLL_LOC WHERE PRSN_NBR=".$PrsnNbr." AND PYMT_DTE='".$PymtDte."' AND DEL_NBR=0";
		//echo $query."<BR>";
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		if($row['CNT']==0)
		{
			$query="INSERT INTO PAY.PAYROLL_LOC (PRSN_NBR,PYMT_DTE) VALUES (".$PrsnNbr.",'".$PymtDte."')";
			$result=mysql_query($query);
		}
		
		$query="UPDATE PAY.PAYROLL_LOC
	   			SET BASE_AMT=".$BaseAmt.",
	   				BASE_CNT=".$BaseCnt.",
	   				BASE_TOT=".$BaseTot.",
	   				ADD_AMT=".$AddAmt.",
	   				ADD_CNT=".$AddCnt.",
	   				ADD_TOT=".$AddTot.",
	   				OT_AMT=".$OTAmt.",
	   				OT_CNT=".$OTCnt.",
	   				OT_TOT=".$OTTot.",
	   				MISC_AMT=".$MiscAmt.",
	   				MISC_CNT=".$MiscCnt.",
	   				MISC_TOT=".$MiscTot.",
	   				BON_ATT_AMT=".$BonAttAmt.",
	   				BON_WK_AMT=".$BonWkAmt.",
	   				BON_MO_AMT=".$BonMoAmt.",
	   				CRDT_WK=".$CrdtWk.",
	   				DEBT_WK=".$DebtWk.",
	   				PAY_AMT=".$PayAmt.",
	   				CRDT_AMT=".$CrdtAmt.",
					UPD_DTE=CURRENT_DATE,
					UPD_NBR=".$_SESSION['personNBR']."
					WHERE PRSN_NBR=".$PrsnNbr."
					AND PYMT_DTE='".$PymtDte."'";
		//echo $query;
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
<script type="text/javascript" src="framework/datepicker/js/mootools-1.2.4-core.js"></script>
<script type="text/javascript" src="framework/datepicker/js/mootools-1.2.4.4-more.js"></script>
<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.js"></script>

<script type="text/javascript">
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

<script type="text/javascript">
	function checkform()
	{
		if(document.getElementById('NAME').value=="")
		{
			window.scrollTo(0,0);
			document.getElementById('nameBlank').style.display='block';document.getElementById('fade').style.display='block';
			return false;
		}

		return true;
	}
</script>

<script type="text/javascript">
	function applyVal(sourceObj,destinationID)
	{
		document.getElementById(destinationID).value=sourceObj.value;
	}
	function applyAtt(checkObj){
		var checkPremi = document.getElementById("BON_ATT_F");
		var checkBonus = document.getElementById("BON_WK_F");
		
		if (checkPremi.checked == true){
			document.getElementById('BON_ATT_AMT').value=((document.getElementById('BASE_AMT').value + document.getElementById('ADD_AMT').value) / 6 ) *document.getElementById('BON_ATT_DAY').value;
		}else{
			document.getElementById('BON_ATT_AMT').value=0;
		}
		
		if (checkBonus.checked == true){
			document.getElementById('BON_WK_AMT').value=<?php echo $row['BON_WK_AMT']; ?>;
		}else{
			document.getElementById('BON_WK_AMT').value=0;
		}
		
		
		/*
		if (checkBox.checked == true){
			multi=1;
		}else{
			multi=0;
		}
		
		document.getElementById('BON_ATT_AMT').value=((multi*document.getElementById('BASE_AMT').value+multi*document.getElementById('ADD_AMT').value) / 6 ) *document.getElementById('BON_ATT_DAY').value;
		*/
		calcPay();
	}
	function getInt(objectID){
		if(document.getElementById(objectID).value=="")
		{
			return 0;
		}else{
			return parseInt(document.getElementById(objectID).value);
		}
	}
	function calcPay(){
		document.getElementById('BASE_TOT').value=document.getElementById('BASE_AMT').value*document.getElementById('BASE_CNT').value;
		document.getElementById('ADD_TOT').value=document.getElementById('ADD_AMT').value*document.getElementById('ADD_CNT').value;
		document.getElementById('OT_TOT').value=document.getElementById('OT_AMT').value*document.getElementById('OT_CNT').value;
		document.getElementById('MISC_TOT').value=document.getElementById('MISC_AMT').value*document.getElementById('MISC_CNT').value;
		document.getElementById('SUB_AMT').value=parseInt(document.getElementById('BASE_TOT').value)
												+parseInt(document.getElementById('ADD_TOT').value)
												+parseInt(document.getElementById('OT_TOT').value)
												+parseInt(document.getElementById('MISC_TOT').value)
												+getInt('BON_ATT_AMT')
												+getInt('BON_WK_AMT')
												+getInt('BON_MO_AMT');
		document.getElementById('PAY_AMT').value=getInt('SUB_AMT')-getInt('CRDT_WK')-getInt('DEBT_WK');
	}
</script>

<link rel="stylesheet" type="text/css" media="screen" href="framework/alert/alert.css" />

</head>

<body>

<script>
	parent.document.getElementById('payrollDeleteYes').onclick=
	function () { 
		parent.document.getElementById('content').src='payroll-wage.php?DEL=<?php echo $PrsnNbr; ?>&DATE=<?php echo $PymtDte; ?>';
		parent.document.getElementById('payrollDelete').style.display='none';
		parent.document.getElementById('fade').style.display='none'; 
	};
</script>


<table class="submenu">
	<tr>
		<td class="submenu" style="background-color:">
			<?php
				$query="SELECT PYMT_DTE
						FROM PAY.PAYROLL_LOC
						WHERE PRSN_NBR=".$PrsnNbr."
						AND DEL_NBR=0
						ORDER BY 1 DESC
						LIMIT 0,12";
				//echo $query;
				$result=mysql_query($query);
				while($row=mysql_fetch_array($result))
				{
					echo "<a class='submenu' href='payroll-wage-edit.php?PRSN_NBR=".$PrsnNbr."&PYMT_DTE=".$row['PYMT_DTE']."'><div class='";
					if($PymtDte==$row['PYMT_DTE']){echo "arrow_box";}else{echo "leftsubmenu";}
					echo "'>".$row['PYMT_DTE']."</div></a>";
				}
			?>	
		</td>
		<td class="subcontent">

			<?php if(($Security<=2)&&($PymtDte!=0)) { ?>
				<div class="toolbar-only">
				<table class="toolbar">
				<tr>
				<td style="padding:0;margin:0;">
				   <a href="javascript:void(0)" onclick = "window.scrollTo(0,0);parent.document.getElementById('payrollDelete').style.display='block';parent.document.getElementById('fade').style.display='block'"><img style="cursor:pointer" class="toolbar-left" src="img/delete.png"></a>
				</td>
				<td align="right" style="padding:0;margin:0;">
					<a href="payroll-prn-dig-edit-print-week.php?PRSN_NBR=<?php echo $PrsnNbr; ?>&CONBR=<?php echo $_GET['CO_NBR']; ?>&PYMT_DTE=<?php echo $PymtDte; ?>"><span class='fa fa-print toolbar' style="cursor:pointer" onclick="location.href="></span></a>
				</td>
				</tr>
				</table>
				</div>
			<?php } ?>
		
			<?php
				if($PymtDte=="")
				{
					$query="SELECT PPL.PRSN_NBR,NAME,POS_TYP,PPAY.PAY_TYP,PPAY.PAY_BASE,PPAY.PAY_ADD,PPAY.PAY_OT,PPAY.PAY_CONTRB,PPAY.PAY_MISC,PPAY.DED_DEF
							FROM CMP.PEOPLE PPL
							LEFT JOIN PAY.PEOPLE PPAY ON PPL.PRSN_NBR = PPAY.PRSN_NBR
							WHERE PPL.DEL_NBR=0 AND PPL.PRSN_NBR=".$PrsnNbr;
					$result=mysql_query($query);
					$row=mysql_fetch_array($result);
					$PymtDte=date("Y-m-d H:i:s");
				}else{
					$query="SELECT PAY.PRSN_NBR,NAME,PYMT_DTE,BASE_AMT AS PAY_BASE,BASE_CNT,BASE_TOT,ADD_AMT AS PAY_ADD,ADD_CNT,ADD_TOT,OT_AMT AS PAY_OT,OT_CNT,OT_TOT,MISC_AMT	,MISC_CNT,MISC_TOT,BON_ATT_AMT,BON_WK_AMT,BON_MO_AMT,CRDT_WK,DEBT_WK AS DED_DEF,PAY_AMT,CRDT_AMT,PAY.UPD_DTE,PAY.UPD_NBR
							FROM PAY.PAYROLL_LOC PAY INNER JOIN CMP.PEOPLE PPL ON PAY.PRSN_NBR=PPL.PRSN_NBR
							WHERE PAY.DEL_NBR=0 AND PAY.PRSN_NBR=".$PrsnNbr." AND PYMT_DTE='".$PymtDte."'";
					$result=mysql_query($query);
					$row=mysql_fetch_array($result);
				}
				// echo $query;
			
			?>		
					
			<form enctype="multipart/form-data" action="#" method="post" style="width:500px" onSubmit="return checkform();">
				<p>
					<h2>
						<?php echo $row['NAME'] ?>
					</h2>
									
					<h3>
						Perincian Gaji Karyawan Nomor Induk: <?php echo $row['PRSN_NBR'];if($row['PRSN_NBR']==""){echo "Nomor Baru";} ?>
					</h3>
					<br />

					<table>
					<tr>
						<!-- table left -->
						<td>
						<table>
							<tr>
								<td class='time-card-top' colspan=8>Bulan 
								<?php 
									$PymtDteOld = date('F', strtotime($PymtDte." -1 month"));
									echo $PymtDteOld;?></td>
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
								$nbrDays=0;
								$OldMonth	= date('m', strtotime($PymtDte." -1 month"));
								if ($OldMonth==12) 
									$year=parseYear($PymtDte-1);
								else $year=parseYear($PymtDte);
								//echo $year;
								for($day=1;$day<=31;$day++){
									echo "<tr>";
									if($day<=31){
										echo "<td class='time-card'>".$day."</td>";
										$query="SELECT CLOK_IN_TS,CLOK_OT_TS,HOUR(CLOK_IN_TS) AS HR_IN
												  FROM PAY.MACH_CLOK
												  WHERE PRSN_NBR=".$PrsnNbr." AND DAY(CLOK_IN_TS)=$day AND MONTH(CLOK_IN_TS)=".$OldMonth." AND YEAR(CLOK_IN_TS)=".$year." ORDER BY CLOK_IN_TS";
										
										//echo $query."<br>";//$day
										$result=mysql_query($query);
										$shift=3;
										$morning_min=0;$afternoon_min=0;$night_min=0;$hours_min=0;
										$in_1=""; $ot_1="";
										$in_2=""; $ot_2="";
										$in_3=""; $ot_3="";
										$jum=mysql_num_rows($result);
										//$row=mysql_fetch_array($result,MYSQL_BOTH)
										$data=array();
										//echo "xxxxxxxx ".$jum."<br/>";
										if($jum==3) {
											$i=0;
											while($rowh=mysql_fetch_array($result)){
												$data[$i]=$rowh['CLOK_IN_TS']."|".$rowh['CLOK_OT_TS']."|".$rowh['HR_IN'];
												$i++;
											}	
											
											//echo "<br/>".$data[0];
											$one=explode("|",$data[0]);
											$in_1=$one[0]; $ot_1=$one[1];
											
											if($one[1]!=0){
												$morning_min=strtotime($one[1])-strtotime($one[0]);
											}
											
											//echo "<br/>".$data[1];
											$two=explode("|",$data[1]);
											$in_2=$two[0]; $ot_2=$two[1];
											
											if($two[1]!=0){
												$afternoon_min=strtotime($two[1])-strtotime($two[0]);
											}
											
											//echo "<br/>".$data[2];
											$three=explode("|",$data[2]);
											$in_3=$three[0]; $ot_3=$three[1];
											
											if($three[1]!=0){
												$night_min=strtotime($three[1])-strtotime($three[0]);
											}
											
										} else if($jum==2){
											$i=0;
											while($rowh=mysql_fetch_array($result)){
												$data[$i]=$rowh['CLOK_IN_TS']."|".$rowh['CLOK_OT_TS']."|".$rowh['HR_IN'];
												$i++;
											}
											
											//echo "<br/>".$data[0];
											$one=explode("|",$data[0]);
											if($one[2]<12) {
												$in_1=$one[0]; $ot_1=$one[1];
												
												if($one[1]!=0){
													$morning_min=strtotime($one[1])-strtotime($one[0]);
												}
												
											} else if(($one[2]>=12)&&($one[2]<18)){
												$in_2=$one[0]; $ot_2=$one[1];
												
												if($one[1]!=0){
													$afternoon_min=strtotime($one[1])-strtotime($one[0]);
												}
												
											}
											
											//echo "<br/>".$data[1];
											$two=explode("|",$data[1]);
											if(($two[2]>=12)&&($two[2]<18)){
												$in_2=$two[0]; $ot_2=$two[1];
												
												if($two[1]!=0){
													$afternoon_min=strtotime($two[1])-strtotime($two[0]);
												}
												
											} else if($two[2]>=18){
												$in_3=$two[0]; $ot_3=$two[1];
												
												if($two[1]!=0){
													$night_min=strtotime($two[1])-strtotime($two[0]);
												}
												
											}
										} else if($jum==1){
											$i=0;
											while($rowh=mysql_fetch_array($result)){
												$data[$i]=$rowh['CLOK_IN_TS']."|".$rowh['CLOK_OT_TS']."|".$rowh['HR_IN'];
												$i++;
											}
											//echo "<br/>".$data[0];
											$one=explode("|",$data[0]);
											if($one[2]<12) {
												$in_1=$one[0]; $ot_1=$one[1];
												
												if($one[1]!=0){
													$morning_min=strtotime($one[1])-strtotime($one[0]);
												}
												
											} else if(($one[2]>=12)&&($one[2]<18)){
												$in_2=$one[0]; $ot_2=$one[1];
												
												if($one[1]!=0){
													$afternoon_min=strtotime($one[1])-strtotime($one[0]);
												}
												
											} else if($one[2]>=18){
												$in_3=$one[0]; $ot_3=$one[1];
												
												if($one[1]!=0){
													$night_min=strtotime($one[1])-strtotime($one[0]);
												}
												
											}
										}
										else {
											$in_1=""; $ot_1="";
											$in_2=""; $ot_2="";
											$in_3=""; $ot_3="";
										}
											echo "<td class='time-card-print'>".parseTimeShort($in_1)."</td>"; 											 
											echo "<td class='time-card-print'>".parseTimeShort($ot_1)."</td>";
											echo "<td class='time-card-print'>".parseTimeShort($in_2)."</td>"; 											 
											echo "<td class='time-card-print'>".parseTimeShort($ot_2)."</td>";
											echo "<td class='time-card-print'>".parseTimeShort($in_3)."</td>"; 											 
											echo "<td class='time-card-print'>".parseTimeShort($ot_3)."</td>";
										
										$hours_min=($morning_min+$afternoon_min+$night_min)/3600;
										echo "<td class='time-card-print'>".number_format($hours_min,1)."</td>";
										echo "<td class='time-card-center'".$hide.">
											<div class='listable-btn'><span class='fa fa-pencil  listable-btn' onclick=".chr(34)."slideFormIn
											('payroll-clock-edit.php?day=".$year."-".$month."-".$day."&NBR=".$PrsnNbr."');".chr(34)."></span></div>
											</td>";
										
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
						<!-- table left -->
						<td>
						<table>
							<tr>
								<td class='time-card-top' colspan=8>Bulan 
								<?php 
									$PymtDteOld = date('F', strtotime($PymtDte));
									echo $PymtDteOld;?></td>
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
								$nbrDays=0;
								$month=parseMonth($PymtDte);
								$year=parseYear($PymtDte);
								for($day=1;$day<=31;$day++){
									echo "<tr>";
									if($day<=31){
										echo "<td class='time-card'>".$day."</td>";
										$query="SELECT CLOK_IN_TS,CLOK_OT_TS,HOUR(CLOK_IN_TS) AS HR_IN
												  FROM PAY.MACH_CLOK
												  WHERE PRSN_NBR=".$PrsnNbr." AND DAY(CLOK_IN_TS)=$day AND MONTH(CLOK_IN_TS)=".$month." AND YEAR(CLOK_IN_TS)=".$year." ORDER BY CLOK_IN_TS";
										//echo $query."<br>";//$day
										$result=mysql_query($query);
										$shift=3;
										$morning=0;$afternoon=0;$night=0;$hours=0;
										$in_1=""; $ot_1="";
										$in_2=""; $ot_2="";
										$in_3=""; $ot_3="";
										$jum=mysql_num_rows($result);
										//$row=mysql_fetch_array($result,MYSQL_BOTH)
										$data=array();
										//echo "xxxxxxxx ".$jum."<br/>";
										if($jum==3) {
											$i=0;
											while($rowh=mysql_fetch_array($result)){
												$data[$i]=$rowh['CLOK_IN_TS']."|".$rowh['CLOK_OT_TS']."|".$rowh['HR_IN'];
												$i++;
											}	
											
											//echo "<br/>".$data[0];
											$one=explode("|",$data[0]);
											$in_1=$one[0]; $ot_1=$one[1];
											if($one[1]!=0){
												$morning=strtotime($one[1])-strtotime($one[0]);
											}
											
											//echo "<br/>".$data[1];
											$two=explode("|",$data[1]);
											$in_2=$two[0]; $ot_2=$two[1];
											if($two[1]!=0){
												$afternoon=strtotime($two[1])-strtotime($two[0]);
											}
											
											//echo "<br/>".$data[2];
											$three=explode("|",$data[2]);
											$in_3=$three[0]; $ot_3=$three[1];
											if($three[1]!=0){
												$night=strtotime($three[1])-strtotime($three[0]);
											}
											
										} else if($jum==2){
											$i=0;
											while($rowh=mysql_fetch_array($result)){
												$data[$i]=$rowh['CLOK_IN_TS']."|".$rowh['CLOK_OT_TS']."|".$rowh['HR_IN'];
												$i++;
											}
											
											//echo "<br/>".$data[0];
											$one=explode("|",$data[0]);
											if($one[2]<12) {
												$in_1=$one[0]; $ot_1=$one[1];
												if($one[1]!=0){
													$morning=strtotime($one[1])-strtotime($one[0]);
												}
											} else if(($one[2]>=12)&&($one[2]<18)){
												$in_2=$one[0]; $ot_2=$one[1];
												if($one[1]!=0){
													$afternoon=strtotime($one[1])-strtotime($one[0]);
												}
											}
											
											//echo "<br/>".$data[1];
											$two=explode("|",$data[1]);
											if(($two[2]>=12)&&($two[2]<18)){
												$in_2=$two[0]; $ot_2=$two[1];
												if($two[1]!=0){
													$afternoon=strtotime($two[1])-strtotime($two[0]);
												}
											} else if($two[2]>=18){
												$in_3=$two[0]; $ot_3=$two[1];
												if($two[1]!=0){
													$night=strtotime($two[1])-strtotime($two[0]);
												}
											}
										} else if($jum==1){
											$i=0;
											while($rowh=mysql_fetch_array($result)){
												$data[$i]=$rowh['CLOK_IN_TS']."|".$rowh['CLOK_OT_TS']."|".$rowh['HR_IN'];
												$i++;
											}
											//echo "<br/>".$data[0];
											$one=explode("|",$data[0]);
											if($one[2]<12) {
												$in_1=$one[0]; $ot_1=$one[1];
												if($one[1]!=0){
													$morning=strtotime($one[1])-strtotime($one[0]);
												}
											} else if(($one[2]>=12)&&($one[2]<18)){
												$in_2=$one[0]; $ot_2=$one[1];
												if($one[1]!=0){
													$afternoon=strtotime($one[1])-strtotime($one[0]);
												}
											} else if($one[2]>=18){
												$in_3=$one[0]; $ot_3=$one[1];
												if($one[1]!=0){
													$night=strtotime($one[1])-strtotime($one[0]);
												}
											}
										}
										else {
											$in_1=""; $ot_1="";
											$in_2=""; $ot_2="";
											$in_3=""; $ot_3="";
										}
											echo "<td class='time-card-print'>".parseTimeShort($in_1)."</td>"; 											 
											echo "<td class='time-card-print'>".parseTimeShort($ot_1)."</td>";
											echo "<td class='time-card-print'>".parseTimeShort($in_2)."</td>"; 											 
											echo "<td class='time-card-print'>".parseTimeShort($ot_2)."</td>";
											echo "<td class='time-card-print'>".parseTimeShort($in_3)."</td>"; 											 
											echo "<td class='time-card-print'>".parseTimeShort($ot_3)."</td>";
										
										$hours=($morning+$afternoon+$night)/3600;
										echo "<td class='time-card-print'>".number_format($hours,1)."</td>";
								
										if($hours>0){
											if($hours<=5){$nbrDays+=0.5;}else{$nbrDays+=1;}
										}
										$totHours+=$hours;
										echo "<td class='time-card-center'".$hide.">
											<div class='listable-btn'><span class='fa fa-pencil  listable-btn' onclick=".chr(34)."slideFormIn
											('payroll-clock-edit.php?day=".$year."-".$month."-".$day."&NBR=".$PrsnNbr."');".chr(34)."></span></div>
											</td>";
										
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
				
						
					<br />		
					<input name="PRSN_NBR" value="<?php echo $row['PRSN_NBR']; ?>" type="hidden" />
					<table>
						<tr><td>Tanggal gajian</td><td><input id="PYMT_DTE" name="PYMT_DTE" size="20" value="<?php echo $row['PYMT_DTE']; ?>"></input></td></tr>
						<script>
							new CalendarEightysix('PYMT_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });
						</script>
						<tr>
							<td>Masuk</td>
							<td colspan="2">
								<input size="5" onkeyup="applyVal(this,'BASE_CNT');applyVal(this,'ADD_CNT');applyVal(this,'BON_ATT_DAY');calcPay();" value="<?php echo $row['BASE_CNT']; ?>"></input> hari
							</td>
						</tr>
						<tr>
							<td>Lembur</td>
							<td colspan="2"><input size="5" onkeyup="applyVal(this,'OT_CNT');calcPay();" value="<?php echo $row['OT_CNT']; ?>"></input> jam</td>
						</tr>
						
						<tr style="height:10px"><td colspan="3"><hr style="height:1px;border:0px;border-bottom:1px #CCCCCC solid" /></td></tr>
					
						<tr>
							<td>Gaji pokok</td>
							<td align="right">
								<input name="BASE_AMT" id="BASE_AMT" size="15" onkeyup="calcPay();" value="<?php echo $row['PAY_BASE']; ?>"></input> X 
								<input name="BASE_CNT" id="BASE_CNT" size="5" readonly tabindex="-1" onkeyup="calcPay();" value="<?php echo $row['BASE_CNT']; ?>"></input>
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							</td>
							<td>= Rp. <input name="BASE_TOT" id='BASE_TOT' size="15"  onkeyup="calcPay();" value="<?php echo $row['BASE_TOT']; ?>"></td>
						</tr>
						<tr>
							<td>Gaji lembur&nbsp;</td>
							<td align="right">
								<input name="OT_AMT" id="OT_AMT" size="15" onkeyup="calcPay();" value="<?php echo $row['PAY_OT']; ?>"></input> X <input name="OT_CNT" id="OT_CNT" size="5" readonly tabindex="-1" onkeyup="calcPay();" value="<?php echo $row['OT_CNT']; ?>"></input>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							</td>
							<td>= Rp. <input name="OT_TOT" id="OT_TOT" size="15" onkeyup="calcPay();" value="<?php echo $row['OT_TOT']; ?>"></td>
						</tr>
						
						<tr>
							<td>Uang tunjangan</td>
							<td align="right">
							<input name="ADD_AMT" id="ADD_AMT" size="15" onkeyup="calcPay();" value="<?php echo $row['PAY_ADD']; ?>"></input> X <input name="ADD_CNT" id="ADD_CNT" size="5" readonly tabindex="-1" onkeyup="calcPay();" value="<?php echo $row['ADD_CNT']; ?>"></input>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							</td>
							<td>= Rp. <input name="ADD_TOT" id="ADD_TOT" size="15" onkeyup="calcPay();" value="<?php echo $row['ADD_TOT']; ?>"></td>
						</tr>
			
						<tr>
							<td>Gaji lain-lain</td>
							<td align="right">
								<input name="MISC_AMT" id="MISC_AMT" size="15" onkeyup="calcPay();" value="<?php echo $row['MISC_AMT']; ?>"></input> X 
								<input name="MISC_CNT" id="MISC_CNT" size="5" onkeyup="calcPay();" value="<?php echo $row['MISC_CNT']; ?>"></input>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							</td>
							<td>= Rp. <input name="MISC_TOT" id="MISC_TOT" size="15" onkeyup="calcPay();" value="<?php echo $row['MISC_TOT']; ?>"></td>
						</tr>
	
						<tr>
							<td>Uang premi</td>
							<td align="right">
								<input name="MISC_AMT" id="MISC_AMT" size="15" onkeyup="calcPay();" value="<?php echo $row['PAY_BASE'] + $row['PAY_ADD']; ?>"></input> : 
								<input name="BON_ATT_DAY" id="BON_ATT_DAY" size="5" readonly tabindex="-1" onkeyup="calcPay();" value="<?php echo $row['BASE_CNT']; ?>" readonly>
								<input type="checkbox" id="BON_ATT_F" onchange="applyAtt(this);calcPay();" <?php if($row['BON_ATT_AMT']>0){echo "checked";} ?>>&nbsp;
							</td>
							<td>= Rp. <input name="BON_ATT_AMT" id="BON_ATT_AMT" size="15" onkeyup="calcPay();" value="<?php echo $row['BON_ATT_AMT']; ?>"></td>
						</tr>
						<tr>
							<td>Bonus mingguan</td>
							<td>
								<input type="checkbox" id="BON_WK_F" onchange="applyAtt(this);calcPay();" <?php if($row['BON_WK_AMT']>0){echo "checked";} ?>>&nbsp;
							</td>
							<td>
								= Rp. <input name="BON_WK_AMT" id="BON_WK_AMT" size="15" onkeyup="calcPay();" value="<?php echo $row['BON_WK_AMT']; ?>">
							</td>
						</tr>
						<tr>
							<td>Bonus bulanan</td>
							<td></td>
							<td>
								= Rp. <input name="BON_MO_AMT" id="BON_MO_AMT" size="15" onkeyup="calcPay();" value="<?php echo $row['BON_MO_AMT']; ?>">
							</td>
						</tr>
			
						<tr><td align="right" colspan="2"><strong>Jumlah&nbsp;</strong></td><td>= Rp. <input size="15" id="SUB_AMT" readonly tabindex="-1" value="<?php echo $row['PAY_AMT']+$row['CRDT_WK']+$row['DED_DEF']; ?>"></td></tr>
			
						<tr><td>Jumlah bon harian</td><td></td><td>= Rp. <input name="CRDT_WK" id="CRDT_WK" size="15" onkeyup="calcPay();" value="<?php echo $row['CRDT_WK']; ?>"></td></tr>
						<tr><td>Uang titipan</td><td></td><td>= Rp. <input name="DEBT_WK" id="DEBT_WK" size="15" onkeyup="calcPay();" value="<?php echo $row['DED_DEF']; ?>"></td></tr>
						
						<tr style="height:10px"><td colspan="3"><hr style="height:1px;border:0px;border-bottom:1px #CCCCCC solid" /></td></tr>
					
						<tr><td align="right" colspan="2"><strong>Total&nbsp;</strong></td><td>= Rp. <input name="PAY_AMT" id="PAY_AMT" size="15" readonly tabindex="-1" value="<?php echo $row['PAY_AMT']; ?>"></td></tr>
			
						<tr style="height:10px"><td colspan="3"><hr style="height:1px;border:0px;border-bottom:1px #CCCCCC solid" /></td></tr>

						<tr>
							<td>Jumlah bon</td><td></td>
							<td>= Rp. <input size="15" id="CRDT_AMT" name="CRDT_AMT" value="<?php echo $row['CRDT_AMT']; ?>"></input></td>
						</tr>
			
						<tr style="std"><td colspan="3"><input class="process" type="submit" value="Simpan"/><div></div></td></tr>	
						</tr>
					</table>		
		
				</p>
			</form>

		</td>
	</tr>
</table>	
</body>
</html>

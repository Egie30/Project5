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
		$query="SELECT COUNT(*) AS CNT FROM CMP.PAYROLL_LOC WHERE PRSN_NBR=".$PrsnNbr." AND PYMT_DTE='".$PymtDte."'";
		//echo $query."<BR>";
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		if($row['CNT']==0)
		{
			$query="INSERT INTO CMP.PAYROLL_LOC (PRSN_NBR,PYMT_DTE) VALUES (".$PrsnNbr.",'".$PymtDte."')";
			$result=mysql_query($query);
		}
		
		$query="UPDATE CMP.PAYROLL_LOC
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
	function applyAtt(checkObj)
	{
		if(checkObj.value=="on"){multi=1;}else{multi=0;}
		document.getElementById('BON_ATT_AMT').value=multi*document.getElementById('BASE_AMT').value+multi*document.getElementById('ADD_AMT').value;
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
		parent.document.getElementById('content').src='payroll-WAGE.php?DEL=<? echo $PrsnNbr; ?>&DATE=<?php echo $PymtDte; ?>';
		parent.document.getElementById('payrollDelete').style.display='none';
		parent.document.getElementById('fade').style.display='none'; 
	};
</script>


<table class="submenu">
	<tr>
		<td class="submenu" style="background-color:">
			<?php
				$query="SELECT PYMT_DTE
						FROM CMP.PAYROLL_LOC
						WHERE PRSN_NBR=".$PrsnNbr."
						ORDER BY 1 DESC
						LIMIT 0,12";
				//echo $query;
				$result=mysql_query($query);
				while($row=mysql_fetch_array($result))
				{
					echo "<a class='submenu' href='payroll-wage-edit.php?PRSN_NBR=".$PrsnNbr."&PYMT_DTE=".$row['PYMT_DTE']."'><div class='leftsubmenu'>".$row['PYMT_DTE']."</div></a>";
				}
			?>	
		</td>
		<td class="subcontent">

			<?php if(($Security==0)&&($PymtDte!=0)) { ?>
				<div class="toolbar-only">
				<p class="toolbar-left"><a href="javascript:void(0)" onclick = "window.scrollTo(0,0);parent.document.getElementById('payrollDelete').style.display='block';parent.document.getElementById('fade').style.display='block'"><img style="cursor:pointer" class="toolbar-left" src="img/delete.png"></a></p>
				</div>
			<?php } ?>
		
			<?php
				if($PymtDte=="")
				{
					$query="SELECT PRSN_NBR,NAME,POS_TYP,PAY_TYP,PAY_BASE,PAY_ADD,PAY_OT,PAY_MISC,DED_DEF
							FROM CMP.PEOPLE
							WHERE PRSN_NBR=".$PrsnNbr;
				}else{
					$query="SELECT PAY.PRSN_NBR,NAME,PYMT_DTE,BASE_AMT AS PAY_BASE,BASE_CNT,BASE_TOT,ADD_AMT AS PAY_ADD,ADD_CNT,ADD_TOT,OT_AMT AS PAY_OT,OT_CNT,OT_TOT,MISC_AMT	,MISC_CNT,MISC_TOT,BON_ATT_AMT,BON_WK_AMT,BON_MO_AMT,CRDT_WK,DEBT_WK AS DED_DEF,PAY_AMT,CRDT_AMT,PAY.UPD_TS,PAY.UPD_NBR
							FROM CMP.PAYROLL_LOC PAY INNER JOIN CMP.PEOPLE PPL ON PAY.PRSN_NBR=PPL.PRSN_NBR
							WHERE PAY.PRSN_NBR=".$PrsnNbr." AND PYMT_DTE='".$PymtDte."'";
				}
				//echo $query;
				$result=mysql_query($query);
				$row=mysql_fetch_array($result);
			?>		
					
			<form enctype="multipart/form-data" action="#" method="post" style="width:500px" onSubmit="return checkform();">
				<p>
					<h2>
						<?php echo $row['NAME'] ?>
					</h2>
									
					<h3>
						Perincian Gaji Karyawan Nomor Induk: <?php echo $row['PRSN_NBR'];if($row['PRSN_NBR']==""){echo "Nomor Baru";} ?>
					</h3>
							
					<input name="PRSN_NBR" value="<?php echo $row['PRSN_NBR']; ?>" type="hidden" />
					<table>
						<tr><td>Tanggal gajian</td><td><input id="PYMT_DTE" name="PYMT_DTE" size="20" value="<?php echo $row['PYMT_DTE']; ?>"></input></td></tr>
						<script>
							new CalendarEightysix('PYMT_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });
						</script>
						<tr>
							<td>Masuk</td>
							<td colspan="2"><input size="5" onkeyup="applyVal(this,'BASE_CNT');applyVal(this,'ADD_CNT');calcPay();" value="<?php echo $row['BASE_CNT']; ?>"></input> hari</td>
						</tr>
						<tr>
							<td>Lembur</td>
							<td colspan="2"><input size="5" onkeyup="applyVal(this,'OT_CNT');calcPay();" value="<?php echo $row['OT_CNT']; ?>"></input> jam</td>
						</tr>
						
						<tr style="height:10px"><td colspan="3"><hr style="height:1px;border:0px;border-bottom:1px #CCCCCC solid" /></td></tr>
					
						<tr>
							<td>Gaji pokok</td>
							<td><input name="BASE_AMT" id="BASE_AMT" size="15" onkeyup="calcPay();" value="<?php echo $row['PAY_BASE']; ?>"></input> X <input name="BASE_CNT" id="BASE_CNT" size="5" readonly tabindex="-1" onkeyup="calcPay();" value="<?php echo $row['BASE_CNT']; ?>"></input>&nbsp;</td>
							<td>= Rp. <input name="BASE_TOT" id='BASE_TOT' size="15"  onkeyup="calcPay();" value="<?php echo $row['BASE_TOT']; ?>"></td>
						</tr>
						<tr>
							<td>Gaji lembur&nbsp;</td>
							<td><input name="OT_AMT" id="OT_AMT" size="15" onkeyup="calcPay();" value="<?php echo $row['PAY_OT']; ?>"></input> X <input name="OT_CNT" id="OT_CNT" size="5" readonly tabindex="-1" onkeyup="calcPay();" value="<?php echo $row['OT_CNT']; ?>"></input>&nbsp;</td>
							<td>= Rp. <input name="OT_TOT" id="OT_TOT" size="15" onkeyup="calcPay();" value="<?php echo $row['OT_TOT']; ?>"></td>
						</tr>
						
						<tr>
							<td>Uang tunjangan</td>
							<td><input name="ADD_AMT" id="ADD_AMT" size="15" onkeyup="calcPay();" value="<?php echo $row['PAY_ADD']; ?>"></input> X <input name="ADD_CNT" id="ADD_CNT" size="5" readonly tabindex="-1" onkeyup="calcPay();" value="<?php echo $row['ADD_CNT']; ?>"></input>&nbsp;</td>
							<td>= Rp. <input name="ADD_TOT" id="ADD_TOT" size="15" onkeyup="calcPay();" value="<?php echo $row['ADD_TOT']; ?>"></td>
						</tr>
			
						<tr>
							<td>Gaji lain-lain</td>
							<td><input name="MISC_AMT" id="MISC_AMT" size="15" onkeyup="calcPay();" value="<?php echo $row['MISC_AMT']; ?>"></input> X <input name="MISC_CNT" id="MISC_CNT" size="5" onkeyup="calcPay();" value="<?php echo $row['MISC_CNT']; ?>"></input>&nbsp;</td>
							<td>= Rp. <input name="MISC_TOT" id="MISC_TOT" size="15" onkeyup="calcPay();" value="<?php echo $row['MISC_TOT']; ?>"></td>
						</tr>
	
						<tr>
							<td>Uang premi</td>
							<td align="right"><input type="checkbox" onchange="applyAtt(this);calcPay();" <?php if($row['BON_ATT_AMT']>0){echo "checked";} ?>>&nbsp;</td>
							<td>= Rp. <input name="BON_ATT_AMT" id="BON_ATT_AMT" size="15" onkeyup="calcPay();" value="<?php echo $row['BON_ATT_AMT']; ?>"></td>
						</tr>
						<tr><td>Bonus mingguan</td><td></td><td>= Rp. <input name="BON_WK_AMT" id="BON_WK_AMT" size="15" onkeyup="calcPay();" value="<?php echo $row['BON_WK_AMT']; ?>"></td></tr>
						<tr><td>Bonus bulanan</td><td></td><td>= Rp. <input name="BON_MO_AMT" id="BON_MO_AMT" size="15" onkeyup="calcPay();" value="<?php echo $row['BON_MO_AMT']; ?>"></td></tr>
			
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

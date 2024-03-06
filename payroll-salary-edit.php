<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	include "framework/alert/alert.php";
	$PrsnNbr=$_GET['PRSN_NBR'];
	$PymtDte=$_GET['PYMT_DTE'];
	$Security=getSecurity($_SESSION['userID'],"Salary");
	//Process changes here
	if($_POST['PRSN_NBR']!="")
	{
		$PrsnNbr=$_POST['PRSN_NBR'];
		$PymtDte=$_POST['PYMT_DTE'];
		
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
		//Take care of nulls
		if($_POST['PAY_BASE']==""){$PayBase="0";}else{$PayBase=$_POST['PAY_BASE'];}
		if($_POST['ABS_CNT']==""){$AbsCnt="0";}else{$AbsCnt=$_POST['ABS_CNT'];}
		if($_POST['ABS_AMT']==""){$AbsAmt="0";}else{$AbsAmt=$_POST['ABS_AMT'];}
		if($_POST['DEBT_WK']==""){$DebtWk="0";}else{$DebtWk=$_POST['DEBT_WK'];}
		if($_POST['PAY_AMT']==""){$PayAmt="0";}else{$PayAmt=$_POST['PAY_AMT'];}
		if($_POST['CRDT_AMT']==""){$CrdtAmt="0";}else{$CrdtAmt=$_POST['CRDT_AMT'];}

		$query="UPDATE CMP.PAYROLL_LOC
	   			SET BASE_AMT=".$PayBase.",
	   				ABS_CNT=".$AbsCnt.",
	   				ABS_AMT=".$AbsAmt.",
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
	function applyAbs(sourceObj,destinationID,dateID,valueID)
	{
		var strDate=document.getElementById(dateID).value.split("-");
		var nbrDays=32-new Date(strDate[0],strDate[1]-1,32).getDate();
		document.getElementById(destinationID).value=parseInt(sourceObj.value/nbrDays*document.getElementById(valueID).value/1000)*1000;
	}
	function getInt(objectID)
	{
		if(document.getElementById(objectID).value=="")
		{
			return 0;
		}else{
			return parseInt(document.getElementById(objectID).value);
		}
	}
	function calcPay(){
		document.getElementById('PAY_AMT').value=getInt('PAY_BASE')-getInt('DEBT_WK')-getInt('ABS_AMT');
	}
</script>

<link rel="stylesheet" type="text/css" media="screen" href="framework/alert/alert.css" />

</head>

<body>

<script>
	parent.document.getElementById('payrollDeleteYes').onclick=
	function () { 
		parent.document.getElementById('content').src='payroll-salary.php?DEL=<? echo $PrsnNbr; ?>&DATE=<?php echo $PymtDte; ?>';
		parent.document.getElementById('payrollDelete').style.display='none';
		parent.document.getElementById('fade').style.display='none'; 
	};
</script>

<table class="submenu">
	<tr>
		<td class="submenu">
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
					echo "<a class='submenu' href='payroll-salary-edit.php?PRSN_NBR=".$PrsnNbr."&PYMT_DTE=".$row['PYMT_DTE']."'><div class='leftsubmenu'>".$row['PYMT_DTE']."</div></a>";
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
					$query="SELECT PAY.PRSN_NBR,NAME,PYMT_DTE,BASE_AMT AS PAY_BASE,BASE_CNT,BASE_TOT,ADD_AMT AS PAY_ADD,ADD_CNT,ADD_TOT,OT_AMT AS PAY_OT,OT_CNT,OT_TOT,MISC_AMT	,MISC_CNT,MISC_TOT,BON_ATT_AMT,BON_WK_AMT,BON_MO_AMT,CRDT_WK,DEBT_WK AS DED_DEF,ABS_CNT,ABS_AMT,PAY_AMT,CRDT_AMT,PAY.UPD_TS,PAY.UPD_NBR
							FROM CMP.PAYROLL_LOC PAY INNER JOIN CMP.PEOPLE PPL ON PAY.PRSN_NBR=PPL.PRSN_NBR
							WHERE PAY.PRSN_NBR=".$PrsnNbr." AND PYMT_DTE='".$PymtDte."'";
				}
				//echo $query;
				$result=mysql_query($query);
				$row=mysql_fetch_array($result);
			?>
					
			<form enctype="multipart/form-data" action="#" method="post" style="width:460px" onSubmit="return checkform();">
				<p>
					<h2>
						<?php echo $row['NAME'] ?>
					</h2>
									
					<h3>
						Perincian Gaji Karyawan Nomor Induk: <?php echo $row['PRSN_NBR'];if($row['PRSN_NBR']==""){echo "Nomor Baru";} ?>
					</h3>
							
				<input name="PRSN_NBR" value="<?php echo $row['PRSN_NBR']; ?>" type="hidden" />
				<table class="flat">
					<tr class="flat"><td class="flat">Tanggal gajian&nbsp;&nbsp;</td><td class="flat"><input id="PYMT_DTE" name="PYMT_DTE" size="20" value="<?php echo $row['PYMT_DTE']; ?>"></input></td></tr>					
					<script>
						new CalendarEightysix('PYMT_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });
					</script>

					<tr class="flat">
						<td class="flat">Absen</td>
						<td class="flat"><input size="5" id="ABS_CNT" name="ABS_CNT" onkeyup="applyAbs(this,'ABS_AMT','PYMT_DTE','PAY_BASE');calcPay();" value="<?php echo $row['ABS_CNT']; ?>"></input> hari</td>
					</tr>

					<tr class="flat" style="height:10px"><td class="flat" colspan="2"><hr style="height:1px;border:0px;border-bottom:1px #CCCCCC solid" /></td></tr>

					<tr class="flat">
						<td class="flat">Gaji Bulanan</td>
						<td class="flat">&nbsp;= Rp. <input size="20" id="PAY_BASE" name="PAY_BASE" onkeyup="calcPay();" value="<?php echo $row['PAY_BASE']; ?>"></input></td>
					</tr>
					<tr class="flat">
						<td class="flat">Potongan</td>
						<td class="flat">&nbsp;= Rp. <input size="20" id="ABS_AMT" name="ABS_AMT" onkeyup="calcPay();" value="<?php echo $row['ABS_AMT']; ?>"></input></td>
					</tr>
					<tr class="flat">
						<td class="flat">Uang titipan</td>
						<td class="flat">&nbsp;= Rp. <input size="20" id="DEBT_WK" name="DEBT_WK" onkeyup="calcPay();" value="<?php echo $row['DED_DEF']; ?>"></input></td>
					</tr>
					
					<tr class="flat" style="height:10px"><td class="flat" colspan="2"><hr style="height:1px;border:0px;border-bottom:1px #CCCCCC solid" /></td></tr>
					
					<tr class="flat"><td class="flat" align="right"><strong>Total&nbsp;</strong></td><td class="flat">&nbsp;= Rp. <input name="PAY_AMT" id="PAY_AMT" size="20" readonly tabindex="-1" value="<?php echo $row['PAY_AMT']; ?>"></td></tr>
					
					<tr class="flat" style="height:10px"><td class="flat" colspan="3"><hr style="height:1px;border:0px;border-bottom:1px #CCCCCC solid" /></td></tr>

					<tr class="flat">
						<td class="flat">Jumlah bon</td>
						<td class="flat">&nbsp;= Rp. <input size="20" id="CRDT_AMT" name="CRDT_AMT" value="<?php echo $row['CRDT_AMT']; ?>"></input></td>
					</tr>
				
					<tr class="flat" style="flat"><td class="flat" colspan="2"><input class="process" type="submit" value="Simpan"/><div></div></td></tr>	

				</table>		
				</p>		
			</form>

		</td>
	</tr>
</table>
<div></div>				
</body>
</html>

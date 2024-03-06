<?php
	include "framework/database/connect-cloud.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	include "framework/alert/alert.php";
	
	date_default_timezone_set('Asia/Jakarta');

	$PrsnNbr=$_GET['PRSN_NBR'];
	$PymtDte=$_GET['PYMT_DTE'];
	$Security=getSecurity($_SESSION['userID'],"Salary");
	
	if($cloud!=false){
		//Process changes here
		if($_POST['PRSN_NBR']!="")
		{
			$j=syncTable("EMPL_CRDT","PRSN_NBR,PYMT_DTE","PAY",$PAY,$local,$cloud);
		
			$PrsnNbr=$_POST['PRSN_NBR'];
			$PymtDte=$_POST['PYMT_DTE'];

			//Process add new
			$query="SELECT COUNT(*) AS CNT FROM $PAY.EMPL_CRDT WHERE DEL_NBR=0 AND PRSN_NBR=".$PrsnNbr." AND PYMT_DTE='".$PymtDte."'";
			$result=mysql_query($query,$cloud);
			$row=mysql_fetch_array($result);
			if($row['CNT']==0)
			{
				$query="INSERT INTO $PAY.EMPL_CRDT (PRSN_NBR,PYMT_DTE) VALUES (".$PrsnNbr.",'".$PymtDte."')";
				$result=mysql_query($query,$cloud);
				$query=str_replace($PAY,"PAY",$query);
				$result=mysql_query($query,$local);
			}

			//Take care of nulls
			if($_POST['PYMT_NBR']==""){$PymtNbr="0";}else{$PymtNbr=$_POST['PYMT_NBR'];}		
			if($_POST['CRDT_PRNC']==""){$CrdtPrnc="0";}else{$CrdtPrnc=round($_POST['CRDT_PRNC']);}
			if($_POST['CRDT_AMT']==""){$CrdtAmt="0";}else{$CrdtAmt=$_POST['CRDT_AMT'];}		
			if($_POST['CRDT_DEF']==""){$CrdtDef="0";}else{$CrdtDef=$_POST['CRDT_DEF'];}
			if($_POST['DED_DEF']==""){$CrdtDef="0";}else{$CrdtDef=round($_POST['DED_DEF']);}
			if($_POST['CRDT_F']=="on"){$CrdtF=1;}else{$CrdtF=0;}
		
			$query="UPDATE $PAY.EMPL_CRDT
	   					SET	CRDT_AMT=".$CrdtAmt.",
						PYMT_NBR=".$PymtNbr.",
						CRDT_PRNC=".$CrdtPrnc.",
						CRDT_F = ".$CrdtF.",
						UPD_NBR=".$_SESSION['personNBR'].",
						UPD_TS=CURRENT_TIMESTAMP
					WHERE PRSN_NBR=".$PrsnNbr."
						AND PYMT_DTE='".$PymtDte."'";
			//echo $query;
			$result=mysql_query($query,$cloud);
			$query=str_replace($PAY,"PAY",$query);
			$result=mysql_query($query,$local);
		
			if($_POST['DED_DEF']==""){$DedDef="0";}else{$DedDef=$_POST['DED_DEF'];}	
			$query="UPDATE $PAY.PEOPLE
				SET DED_DEF=".$DedDef.",
				UPD_TS=CURRENT_TIMESTAMP,
				UPD_NBR=".$_SESSION['personNBR']."
				WHERE PRSN_NBR=".$PrsnNbr;
			
			//echo $query;
			
			$result=mysql_query($query,$cloud);
			$query=str_replace($PAY,"PAY",$query);
			$result=mysql_query($query,$local);
		}
		
		
		//checking employee credit
		$query_credit="SELECT (SUM(CRDT.CRDT_AMT)-(SELECT COALESCE(SUM(PAY.DEBT_MO),0) 
						FROM $PAY.PAYROLL PAY WHERE PAY.PRSN_NBR=".$PrsnNbr." AND PAY.DEL_NBR=0)) REM_CRDT   
						FROM $PAY.EMPL_CRDT CRDT WHERE CRDT.PRSN_NBR=".$PrsnNbr." AND CRDT.DEL_NBR=0";
		$result_credit=mysql_query($query_credit, $cloud);
		//$result_credit=mysql_query($query_credit, $local);
		$row_credit=mysql_fetch_array($result_credit);	
		$RemCrdt=$row_credit['REM_CRDT'];
		
		//echo $query_credit."<br /><br />";
		
		if($RemCrdt == '') { $RemCrdt = 0; } else { $RemCrdt = $row_credit['REM_CRDT']; } 

		
		$query_hire 	= "SELECT TIMESTAMPDIFF(MONTH, PPL.HIRE_DTE , CURRENT_DATE) AS HIRE_DTE FROM PEOPLE PPL WHERE PRSN_NBR=".$PrsnNbr;
		$result_hire	= mysql_query($query_hire, $cloud);
		$row_hire		= mysql_fetch_array($result_hire);

		//echo $query_hire;
		
		$HireDte		= $row_hire['HIRE_DTE'];

		if($HireDte == '') { $HireDte = 0; } else { $HireDte = $row_hire['HIRE_DTE']; } 
		
		//end of checking employee credit
		
		
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

<script type="text/javascript" src="framework/mootools/mootools-latest.min.js"></script>
<script type="text/javascript" src="framework/mootools/mootools-latest-more.js"></script>
<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.min.js"></script>

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

	
<link rel="stylesheet" type="text/css" media="screen" href="framework/alert/alert.css" />

	
</head>

<body>

<script>
	parent.document.getElementById('payrollDeleteYes').onclick=
	function () { 
		parent.document.getElementById('content').src='employee-credit.php?CO_NBR=<?php echo $CoNbr; ?>&DEL_L=<?php echo $PrsnNbr; ?>&PYMT_DTE=<?php echo $PymtDte; ?>';
		parent.document.getElementById('payrollDelete').style.display='none';
		parent.document.getElementById('fade').style.display='none'; 
	};
</script>

<script>
	parent.document.getElementById('checkHireYes').onclick=
	function () { 
		parent.document.getElementById('checkHire').style.display='none';
		parent.document.getElementById('fade').style.display='none'; 
	};
</script>

<script>
	parent.document.getElementById('checkCreditYes').onclick=
	function () { 
		parent.document.getElementById('checkCredit').style.display='none';
		parent.document.getElementById('fade').style.display='none'; 
	};
</script>

<table class="submenu">
	<tr>
		<td class="submenu">
			<?php
				$query="SELECT PYMT_DTE
						FROM PAY.EMPL_CRDT
						WHERE PRSN_NBR=".$PrsnNbr."
						AND DEL_NBR = 0
						ORDER BY 1 DESC
						LIMIT 0,12";
				//echo $query;
				$result=mysql_query($query);
				while($row=mysql_fetch_array($result))
				{
					echo "<a class='submenu' href='employee-credit-edit.php?PRSN_NBR=".$PrsnNbr."&PYMT_DTE=".$row['PYMT_DTE']."'><div class='";
					if($PymtDte==$row['PYMT_DTE']){echo "arrow_box";}else{echo "leftsubmenu";}
					echo "'>".$row['PYMT_DTE']."</div></a>";
				}
			?>	
		</td>
		<td class="subcontent">

			<?php if(($Security==0)&&($PymtDte!=0)) { ?>
				<div class="toolbar-only">
				<p class="toolbar-left"><a href="javascript:void(0)" onclick = "window.scrollTo(0,0);parent.document.getElementById('payrollDelete').style.display='block';parent.document.getElementById('fade').style.display='block'"><span class="fa fa-trash toolbar" style="cursor:pointer"></span></a></p>
				</div>
			<?php } ?>
		
		
			<?php
			
				if($PymtDte=="")
				{
					$query_cek="SELECT PPL.PRSN_NBR,NAME,POS_TYP,PPAY.PAY_TYP,PPAY.PAY_BASE,PPAY.PAY_ADD,PPAY.PAY_OT,PPAY.PAY_CONTRB,PPAY.PAY_MISC,PPAY.DED_DEF,HIRE_DTE,DATE_ADD(HIRE_DTE,INTERVAL 1 YEAR) AS DTE
							FROM CMP.PEOPLE PPL
							LEFT JOIN PAY.PEOPLE PPAY ON PPL.PRSN_NBR = PPAY.PRSN_NBR
							WHERE PPL.PRSN_NBR=".$PrsnNbr;
				}
				
				else
				{
					$query_cek="SELECT BON.PRSN_NBR AS PRSN_NBR,PPL.NAME,PPAY.PAY_BASE,PPAY.PAY_ADD,PYMT_DTE,CRDT_AMT,PPAY.DED_DEF,BON.UPD_TS,BON.UPD_NBR,CRDT_PRNC,PYMT_NBR, CRDT_F,HIRE_DTE,DATE_ADD(HIRE_DTE,INTERVAL 1 YEAR) AS DTE
							FROM PAY.EMPL_CRDT BON 
							INNER JOIN CMP.PEOPLE PPL ON BON.PRSN_NBR=PPL.PRSN_NBR
							LEFT JOIN PAY.PEOPLE PPAY ON PPL.PRSN_NBR = PPAY.PRSN_NBR
							WHERE BON.DEL_NBR=0 AND BON.PRSN_NBR=".$PrsnNbr." AND PYMT_DTE='".$PymtDte."'";
				}
				$result_cek=mysql_query($query_cek);
				$row_cek=mysql_fetch_array($result_cek);
				
				
		
			?>
			

			<form enctype="multipart/form-data" action="#" method="post" style="width:600px" onSubmit="return checkform();">
				<p>
					<h2>
						<?php echo $row_cek['NAME'] ?>
					</h2>
									
					<h3>
						Bon Karyawan Nomor Induk: <?php echo $row_cek['PRSN_NBR'];if($row_cek['PRSN_NBR']==""){echo "Nomor Baru";} ?>
					</h3>
							
				<input name="PRSN_NBR" value="<?php echo $row_cek['PRSN_NBR']; ?>" type="hidden" />
				<table class="flat">
					<tr class="flat"><td class="flat" width="150px">Tanggal &nbsp;&nbsp;</td><td class="flat" width="600px"><input id="PYMT_DTE" name="PYMT_DTE" size="20" value="<?php echo $row_cek['PYMT_DTE']; ?>"></input></td></tr>					
					<script>
						new CalendarEightysix('PYMT_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });
					</script>					
					<tr class="flat">
						<td class="flat">Bon Pokok</td>
						<td class="flat"><input size="20" id="CRDT_PRNC" name="CRDT_PRNC" value="<?php echo $row_cek['CRDT_PRNC']; ?>"></input>&nbsp;&nbsp;&nbsp; 
						<span id="ALERT"></span><span id="ALERT_BON"></span></td>
					</tr>
					<tr class="flat">
						<td class="flat">Periode cicilan</td>
						<td class="flat"><input size="20" id="PYMT_NBR" name="PYMT_NBR" value="<?php echo $row_cek['PYMT_NBR']; ?>"></input>&nbsp;&nbsp;&nbsp; <span id="ALERT_PYMT" ></span></td>
					</tr>
					
					<tr class="flat">
						<td class="flat">Jumlah bon</td>
						<td class="flat"><input size="20" readonly id="CRDT_AMT" name="CRDT_AMT" value="<?php echo $row_cek['CRDT_AMT']; ?>"></input></td>
					</tr>

					<tr class="flat">
						<td class="flat">Cicilan per bulan</td>
						<td class="flat"><input id="DED_DEF" readonly name="DED_DEF" value="<?php 
						if($_GET['PYMT_DTE'] != '') { echo round($row_cek['CRDT_AMT']/$row_cek['PYMT_NBR']); } else { if($RemCrdt <= 0) { echo "0"; } else { echo $row_cek['DED_DEF']; } } 
						?>" type="text" size="20" />&nbsp;&nbsp;&nbsp;<span id="ALERT_INSTALLMENT" ></span></td>
					</tr>	

					<tr class="flat">
						<td class="flat">Credit</td>
						<td class="flat"><input name='CRDT_F' id='CRDT_F' type='checkbox' class='regular-checkbox' <?php if($row_cek['CRDT_F']=="1"){echo "checked";} ?>/>&nbsp;<label for="CRDT_F"></td>
					</tr>
				
					
					<?php
					if($_GET['PYMT_DTE'] != "") { 
					?>
						
						<tr class="flat" style="height:10px"><td class="flat" colspan="2"><hr style="height:1px;border:0px;border-bottom:1px #CCCCCC solid" /></td></tr>
						
						<?php 
							if(($cloud!=false)&&(paramCloud()==1)){
								if (date('Y-m-d')>=$row_cek['DTE']){
									echo '<tr class="flat" style="flat"><td class="flat" colspan="2"><input class="process" id="submit_button" type="submit" value="Simpan"/><div></div></td></tr>';
								}
							} 
					}
					else if($row_cek['CRDT_PRNC'] != "") {
					
						if(($cloud!=false)&&(paramCloud()==1)){
							if (date('Y-m-d')>=$row_cek['DTE']){
								echo '<tr class="flat" style="flat"><td class="flat" colspan="2"><input class="process" id="submit_button" type="submit" value="Simpan"/><div></div></td></tr>';
							}
						}
					
					}
					else 
					{
						if (($_GET['PYMT_DTE'] == "") && ($HireDte < 6)) {
						?>
							<!-- checkCredit : get from index.php -->
							<script type="text/javascript">
							window.scrollTo(0,0);
							window.top.document.getElementById('checkHire').style.display='block';window.top.document.getElementById('fade').style.display='block';
							</script>
						<?php
						}
						else if (($_GET['PYMT_DTE'] == "") && ($RemCrdt > 0))
						{ 
						?>
						<!-- checkCredit : get from index.php -->
							<script type="text/javascript">
							window.scrollTo(0,0);
							window.top.document.getElementById('checkCredit').style.display='block';window.top.document.getElementById('fade').style.display='block';
							</script>
						<?php 
						}
						else 
						{
							
						
						}
						
						echo '<tr class="flat" style="height:10px"><td class="flat" colspan="2"><hr style="height:1px;border:0px;border-bottom:1px #CCCCCC solid" /></td></tr>';
						if(($cloud!=false) && (paramCloud()==1) && ($Security == 0) ){
							if (date('Y-m-d')>=$row_cek['DTE']){
								echo '<tr class="flat" style="flat"><td class="flat" colspan="2"><input class="process" id="submit_button" type="submit" value="Simpan"/><div></div></td></tr>';
							}
						} 
							
					}
						 ?>
				</table>		
				</p>		
			</form>

		</td>
	</tr>
</table>

<script src="framework/jquery/jquery-latest.min.js" type="text/javascript"></script>

<script type="text/javascript">
	jQuery.noConflict();
	
	(function($) {
        $(document).ready(function() {

		var payBase = <?php echo $row_cek['PAY_BASE']; ?>;
		var payAdd = <?php echo $row_cek['PAY_ADD']; ?>;
		var param = 2;
		
		$('#CRDT_PRNC, #PYMT_NBR').on('keyup change click', function() {
					
				var CrdtPrnc = $('#CRDT_PRNC').val();
				var pymtNbr = $('#PYMT_NBR').val();
				
				if(CrdtPrnc != '') {
					if( CrdtPrnc > (4*payBase)) {
						$('#ALERT_BON').html('<img src="img/error.png" style="border:none;"> Maksimum 4 x gaji pokok');
						//bonPok 	= 4*payBase;
						bonPok 	= CrdtPrnc;
					}
					else {
						$('#ALERT_BON').html('');
						bonPok 	= CrdtPrnc;
					}
				}
				
				if( pymtNbr != '') {
					if(pymtNbr > 10) {
						$('#ALERT_PYMT').html('<img src="img/error.png" style="border:none;"> Maksimum 10 x cicilan');
					}
					else if(pymtNbr <= 0){
						$('#ALERT_PYMT').html('<img src="img/error.png" style="border:none;"> Minimum 1x cicilan ');
						}
						else {
							$('#ALERT_PYMT').html('');
						}
				}
				
			/*if((pymtNbr.value!='') 
			&& (pymtNbr >= 0)
			&& (pymtNbr <= 10) 
			&& (CrdtPrnc !='') 
			&& (CrdtPrnc <= (4*payBase)) 
			)*/
			if((pymtNbr.value!='') 
			&& (pymtNbr >= 0)
			&& (CrdtPrnc !='')
			) 
			{
		
				if(pymtNbr < 1){
					pymtNbr=1;
					var bonTot	= 0;
					var dedDef	= 0;
				}
				else if(pymtNbr == 1) {
					if ( CrdtPrnc > (4*payBase)) {
						$('#ALERT_BON').html('<img src="img/error.png" style="border:none;"> Maksimum gaji pokok + gaji tambahan');
						var bonTot	= 0;
						var dedDef	= 0;
					}
					else {
					var bonTot	= bonPok;
					var dedDef	= bonPok;
					}
				}
				else {
					
					var dedDef	= Math.round( ((param/100) * bonPok) / ( 1 - Math.pow( ( 1 + (param/100)), - pymtNbr) ));
					
					var bonTot	= Math.round( dedDef * pymtNbr );
					
					if(dedDef > (65/100)*(payBase+payAdd)) {
						$('#ALERT_INSTALLMENT').html('<img src="img/error.png" style="border:none;"> Maks cicilan adalah 65% dari gaji pokok+gaji tambahan');
					}
					else {
						$('#ALERT_INSTALLMENT').html('');
					}
				
				}
			
				$('#CRDT_AMT').val(bonTot);
				$('#DED_DEF').val(dedDef);
				}

			});
		});
    })(jQuery);
</script>
</body>
</html>
<?php
require_once "framework/database/connect-cashier.php";
require_once "framework/alert/alert.php";
require_once "framework/functions/default.php";

if ($_SESSION['userID'] != "") {
    $userID = $_SESSION['userID'];
    $query  = "SELECT NAME FROM CMP.PEOPLE PPL INNER JOIN CMP.POS_TYP POS ON PPL.POS_TYP=POS.POS_TYP WHERE PRSN_ID='" . $userID . "'";
    $result = mysql_query($query, $cmp);
    $row    = mysql_fetch_array($result);
    $name   = $row['NAME'];
}

$query     = "SELECT NAME FROM CMP.COMPANY WHERE CO_NBR=" . $CoNbrDef;
$result    = mysql_query($query, $cmp);
$row       = mysql_fetch_array($result);
$DefCoName = $row['NAME'];

	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>POS <?php echo $POSID;?> - Nestor</title>

	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="shortcut icon" href="favicon.ico" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/cashier.css" />
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" media="screen" href="framework/alert/alert.css" />
	<link rel="stylesheet" href="framework/pace/pace-custom.css" />
	<script type="text/javascript" src="framework/pace/pace.min.js"></script>
	<script type="text/javascript" src="framework/functions/default.js"></script>
	<script type="text/javascript">
		// Display Slideshow
		function displaySlideshow() {
			document.getElementById('slides').style.display='inline';
			document.getElementById('listing').src='about:blank';
			document.getElementById('total').src='about:blank';
			document.getElementById('data').style.display='none';
		    document.getElementById('search').contentDocument.getElementById('notification').style.display='none';
		    document.getElementById('search').contentDocument.getElementById('notification').style.innerHTML='';
		}

		// Force redirect ro login page
		function forceLogin() {
			document.getElementById('top-left-default').style.display='inline';
			document.getElementById('top-right').style.display='none';
			document.getElementById('top-center').style.display='none';
			document.getElementById('top-left').style.display='none';
			
			displaySlideshow();

			document.getElementById('search').src='cashier-login.php?COMMAND=LOGOUT&POS_ID=<?php echo $POSID; ?>';
		}
	</script>
</head>
<body class="index" style='overflow:hidden'>
<?php createAlert("recordDelete", "Menghapus Data", "Data akan dihapus. Apakah operasi akan diteruskan?"); ?>
<?php createStop("nameBlank", "Nama Kosong", "Kotak nama tidak boleh kosong. Pastikan kotak nama terisi sebelum menyimpan data."); ?>
<?php createStop("memberBlank", "Nomor Member Kosong", "Kotak nomor member tidak boleh kosong. Pastikan kotak nomor member terisi sebelum menyimpan data."); ?>
<?php createAlert("cashierReport", "Report", "Apakah anda akan melakukan reset."); ?>

<script>
	document.getElementById('cashierReportYes').onclick = function() {
		changeUrl('search','cashier-hold.php?POS_ID=<?php echo $POSID;?>&DEFCO=<?php echo $CoNbrDef;?>&CSH=<?php echo $_SESSION['userID'];?>');
		parent.document.getElementById('cashierReport').style.display='none';
		parent.document.getElementById('fade').style.display='none'; 
	};
</script>

<table class="main" >
	<tr class="topmenu">
		<td class="topmenu" colspan="2">
			<?php  if (empty($_SESSION['userID'])) { ?>
				<p id='top-left-default' class="top-left">	
					<img title="Nestor" src="img/nestor-logo-toolbar.png" style='border:0px;vertical-align:5px;padding-left:10px;padding-right:20px;'>
				</p>
			<?php } else { ?>
				<p id='top-left-default' class="top-left" style="display:none">	
					<img title="Nestor" src="img/nestor-logo-toolbar.png" style='border:0px;vertical-align:5px;padding-left:10px;padding-right:20px;'>
				</p>
				<p id='top-left' class="top-left">	
					<img title="Nestor" src="img/nestor-logo-toolbar.png" style='border:0px;vertical-align:5px;padding-left:10px;padding-right:20px;'>
					
					<span class="fa-stack fa-1x topmenusel" onclick="changeUrl('search','cashier-search.php?POS_ID=<?php echo $POSID; ?>&TS=<?php echo $_GET['TS'];?>&CSH=<?php echo $userID; ?>');selTopMenu(this);">
						<span class="fa fa-home fa-stack-1x topmenuicon"></span>
					</span>
									
					<span class="fa-stack fa-1x topmenu" onclick="changeUrl('search','cash-flow-edit.php?RA=B&DIV=PRN&POS_ID=<?php echo $POSID; ?>&POSIP=<?php echo $POSIP; ?>&CSH=<?php echo $userID; ?>');selTopMenu(this);">
						<span class="fa fa-download fa-stack-1x topmenuicon"></span>
					</span>
				
					<span class="fa-stack fa-1x topmenu" onclick="changeUrl('search','cash-flow-edit.php?RA=E&DIV=PRN&POS_ID=<?php echo $POSID; ?>&POSIP=<?php echo $POSIP; ?>&CSH=<?php echo $userID; ?>');selTopMenu(this);">
						<span class="fa fa-upload fa-stack-1x topmenuicon"></span>
					</span>
				
					<span class="fa-stack fa-1x topmenu" onclick="changeUrl('search','expense-edit.php?EXP_NBR=0&POS_ID=<?php echo $POSID; ?>&POSIP=<?php echo $POSIP; ?>&CSH=<?php echo $userID; ?>');selTopMenu(this);">
						<span class="fa fa-money fa-stack-1x topmenuicon"></span>
					</span>
					
					<span class="fa-stack fa-1x topmenu" onclick="changeUrl('search','address-person.php');selTopMenu(this);">
						<span class="fa fa-building fa-stack-1x topmenuicon"></span>
					</span>
				
					<span class="fa-stack fa-1x topmenu" onclick="changeUrl('search','cash-day-report-edit.php?CSH_DAY_NBR=0&POS_ID=<?php echo $POSID; ?>&POSIP=<?php echo $POSIP; ?>&CSH=<?php echo $userID; ?>');selTopMenu(this);">
						<span class="fa fa-bank fa-stack-1x topmenuicon"></span>
					</span>
					
					<span class="fa-stack fa-1x topmenu">
						<span class="fa fa-file-text fa-stack-1x topmenuicon"></span>
					</span>
					
					<!--
					<span class="fa-stack fa-1x topmenu" onclick="changeUrl('bottom','http://<?php echo $POSIP; ?>/cashier-bottom-report.php?POS_ID=<?php echo $POSID; ?>');selTopMenu(this);">
						<span class="fa fa-file-text fa-stack-1x topmenuicon"></span>
					</span>
					-->
					
					<span class="fa-stack fa-1x topmenu" onclick="parent.document.getElementById('slides').style.display='inline';document.getElementById('listing').src='about:blank';document.getElementById('listing').style.backgroundColor='transparent';document.getElementById('total').src='about:blank';document.getElementById('total').style.backgroundColor='transparent';parent.document.getElementById('data').style.display='none';selTopMenu(this);">
						<span class="fa fa-tv fa-stack-1x topmenuicon"></span>
					</span>

				</p>
				<p id='top-right' class="top-right">
					<span class="fa-stack fa-1x topmenu" onclick="
						document.getElementById('top-center').style.display='none';
						document.getElementById('top-right').style.display='none';
						document.getElementById('top-left').style.display='none';
						document.getElementById('search').src='cashier-login.php?COMMAND=LOGOUT&POS_ID=<?php echo $POSID; ?>&TS=<?php echo $_GET['TS'];?>';
						document.getElementById('listing').src='about:blank';document.getElementById('listing').style.backgroundColor='transparent';
						document.getElementById('total').src='about:blank';document.getElementById('total').style.backgroundColor='transparent';
						parent.document.getElementById('data').style.display=none;parent.document.getElementById('slides').style.display='';">
						<span class="fa fa-power-off fa-stack-1x topmenuicon"></span>
					</span>
				</p>
				<p id='top-center' class="top-center">
					<img src="address-person/showimg.php?PRSN_NBR=<?php echo $_SESSION['personNBR']; ?>"
						style="border-radius:50% 50% 50% 50%;width:30px;height:30px;vertical-align:-55%;margin-top:-50px;padding-right:2px;border:0px;">
					<?php echo dispNameScreen($name)." <span style='color:#888888'>@</span> <span style='color:#9abfeb'>".$DefCoName."</span>"; ?>
				</p>
			<?php
			} ?>
		</td>
	</tr>
	<tr>
		<td class="search">
			<iframe id="search" borderframe=0 src="cashier-search.php?POS_ID=<?php echo $POSID; ?>"></iframe>
		</td>
		<td style="width:800px;border:0px;padding:0px;background-color:#222222">
			<div id="slides">
				<iframe style="height:480px;border-bottom:1px solid #cccccc;border-left:1px solid #cccccc;" src="slider-show.php"></iframe>
			</div>
			<table id="data" class="data-data" style="background-image:url(img/slider/logo.jpg);display:none;">
				<tr>
					<td class="listing">
						<iframe id="listing" borderframe=0></iframe>
					</td>
					<td class="total">
						<iframe id="total" borderframe=0></iframe>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td class="bottom" colspan="3">
			<iframe id="bottom" borderframe=0 src="http://<?php echo $POSIP; ?>/cashier-bottom.php?CSH=<?php echo $_SESSION['userID'];?>" style='height:113px'></iframe>
		</td>
	</tr>
	<tr class="footer">
		<td class="footer" colspan="3">
			<p class="bottom-left"><font style="font-weight:600">CONFIDENTIAL</font> and internal use only</p>
			<p class="bottom-right">Nestor version 3.1.2  Copyright &copy; 2008-<?php echo date("Y");?> proreliance.com</p>
		</td>
	</tr>
</table>
<!-- Shadow box background -->
<div id='fade' class='black_overlay'></div>
</body>
</html>
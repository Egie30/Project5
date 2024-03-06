<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
require_once "framework/security/default.php";
	
$bookNumber = $_GET['BK_NBR'];
$security = getSecurity($_SESSION['userID'], "Executive");	


if($bookNumber == -1) {
$query = "SELECT ACT_F FROM RTL.ACCTG_BK WHERE ACT_F = 1";
$result = mysql_query($query);

if (mysql_num_rows($result) > 0) {
	?>
	<script>
		parent.document.getElementById('bookAdd').style.display='block';
		parent.document.getElementById('fade').style.display='block';
		parent.document.getElementById('content').src='accounting-book.php';
	</script>
	<?php
	}
}
	
	if ($_POST['BK_NBR'] != "") {
		$bookNumber = $_POST['BK_NBR'];
		
		if ($bookNumber == -1) {
			$query = "SELECT COALESCE(MAX(BK_NBR),0) + 1 AS NEW_NBR FROM RTL.ACCTG_BK";
			$result = mysql_query($query);
			$row = mysql_fetch_array($result);
			$bookNumber = $row['NEW_NBR'];
			
			$query       = "INSERT INTO RTL.ACCTG_BK (BK_NBR, CRT_TS, CRT_NBR) VALUES (" . $bookNumber . ", CURRENT_TIMESTAMP, " . $_SESSION['personNBR'] . ")";
			$result = mysql_query($query);
		
		}

		$query = "UPDATE RTL.ACCTG_BK SET
				BEG_DTE ='" . $_POST['BEG_DTE'] . "',
				END_DTE ='" . $_POST['END_DTE'] . "',
				UPD_TS=CURRENT_TIMESTAMP,
				UPD_NBR=".$_SESSION['personNBR']."
				WHERE BK_NBR = " . $bookNumber;

		$result = mysql_query($query);
		
	}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" />

	<script type="text/javascript">parent.Pace.restart();</script>
	<script type="text/javascript" src="framework/mootools/mootools-latest.min.js"></script>
	<script type="text/javascript" src="framework/mootools/mootools-latest-more.js"></script>
	<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.min.js"></script>
</head>
<body>
<script>
	parent.document.getElementById('recordDeleteYes').onclick=
	function () {
		parent.document.getElementById('content').src='accounting-book.php?DEL_L=<?php echo $bookNumber; ?>';
		parent.document.getElementById('recordDelete').style.display='none';
		parent.document.getElementById('fade').style.display='none';
	};
</script>

<?php
$query="SELECT BK_NBR, BEG_DTE, END_DTE, ACT_F
	FROM RTL.ACCTG_BK
	WHERE BK_NBR = " . $bookNumber;

$result = mysql_query($query);
$row = mysql_fetch_array($result);

if(empty($row) || ($row['ACT_F'] != 0)) {	$readonly = "";	}
	else { $readonly = "readonly"; }
	
?>

<?php if (($row['ACT_F'] == 1) && ($Security == 0) && ($row['BK_NBR'] != "")) { ?>
	<div class="toolbar-only">
	<p>
		<a href="javascript:void(0)" onclick="window.scrollTo(0,0);parent.document.getElementById('recordDelete').style.display='block';parent.document.getElementById('fade').style.display='block'"><img style="cursor:pointer" class="toolbar-left" src="img/delete.png"></a>
	</p>
	</div>
<?php } ?>

<form enctype="multipart/form-data" action="#" method="post" style="width:400px">
	<p>
		<h2>
			Nomor Buku: 
			<?php if ($row['BK_NBR'] == "") {
				echo "Baru";
			} else {
				echo $row['BK_NBR'];
			}?>
		</h2>
		<input name="BK_NBR" value="<?php echo $row['BK_NBR'];if($row['BK_NBR']==""){echo "-1";} ?>" type="hidden" />
		
		<label>Begin Date</label><br />
		<input id="BEG_DTE" name="BEG_DTE" value="<?php echo $row['BEG_DTE']; ?>" type="text" size="30" <?php echo $readonly; ?> /><br />
		<?php if($readonly!="readonly"){ ?>
		<script>
			new CalendarEightysix('BEG_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });
		</script>
		<?php } ?>
		
		<label>End Date</label><br />
		<input id="END_DTE" name="END_DTE" value="<?php echo $row['END_DTE']; ?>" type="text" size="30" <?php echo $readonly; ?> /><br />
		<?php if($readonly!="readonly"){ ?>
			<script>
			new CalendarEightysix('END_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });
		</script>
		<?php } ?>
		</span>
		<input class="process" type="submit" value="Simpan"/>
	</p>
</form>
</body>
</html>

<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
require_once "framework/security/default.php";

$security   = getSecurity($_SESSION['userID'], "Executive");

$Actg		= $_GET['ACTG'];

$ArrayActg	= array(
				0 => "ALL",
				1 => "PT",
				2 => "CV",
				3 => "PR"
				);

$glNumber	= $_GET['GL_NBR'];

$bookNumber	= $_GET['BK_NBR'];


//Process GL_NBR here
if ($_POST['GL_NBR'] != "") {
    $glNumber = $_POST['GL_NBR'];

    //Take care of nulls and timestamps
    if ($_POST['GL_DESC'] == "") {
        $description = "NULL";
    } else {
        $description = "'" . $_POST['GL_DESC'] . "'";
    }

    if ($_POST['REF'] == "") {
        $reference = "NULL";
    } else {
        $reference = "'" . $_POST['REF'] . "'";
    }

    //Process add new
    if ($glNumber == -1) {
        $query       = "SELECT COALESCE(MAX(GL_NBR),0)+1 AS NEW_NBR FROM RTL.ACCTG_GL_HEAD";
        $result      = mysql_query($query);
        $row         = mysql_fetch_array($result);

        $glNumber = $row['NEW_NBR'];
        $query       = "INSERT INTO RTL.ACCTG_GL_HEAD(GL_NBR, BK_NBR, CRT_TS, CRT_NBR)
        	VALUES (" . $glNumber . ", " .$bookNumber. ", CURRENT_TIMESTAMP, " . $_SESSION['personNBR'] . ")";
        $result      = mysql_query($query);
    }
	
	if ($_SESSION['PLUS_MODE'] == 1) {	$TaxF = 1;	}
		else {   $TaxF = ($_POST['TAX_F'] == "on") ? 1 : 0;    }
    
    $query = "UPDATE RTL.ACCTG_GL_HEAD
				SET GL_DTE='" . $_POST['GL_DTE'] . "',
					GL_DESC=" . $description . ",
					REF=" . $reference . ",
					UPD_TS=CURRENT_TIMESTAMP,
					UPD_NBR=" . $_SESSION['personNBR'] . ",
					TAX_F='".$TaxF."' ";
	if($Actg != 0) {
		$query .= ", ACTG_TYP = '".$Actg."' ";
	}				
	$query .="WHERE GL_NBR=" . $glNumber;
	
    $result      = mysql_query($query);
		
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="stylesheet" href="framework/combobox/chosen.css">
	<link rel="stylesheet" type="text/css" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" media="screen" />
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
	
	<script type="text/javascript">parent.Pace.restart();</script>
	<script type="text/javascript" src="framework/mootools/mootools-latest.min.js"></script>
	<script type="text/javascript" src="framework/mootools/mootools-latest-more.js"></script>
	<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.min.js"></script>
	<script type="text/javascript" src="framework/jquery/jquery-latest.min.js"></script>
	<script type="text/javascript" src="framework/combobox/chosen.jquery.js"></script>
	<script type="text/javascript" src="framework/combobox/ajax-chosen/ajax-chosen.js"></script>
	<script type="text/javascript" src="framework/combobox/chosen.default.js"></script>
	<script type="text/javascript" src="framework/functions/default.js"></script>
	<script type="text/javascript">jQuery.noConflict();</script>
</head>
<body>
<script type="text/javascript">
	parent.document.getElementById('recordDeleteYes').onclick=
	function () { 
		parent.document.getElementById('content').src='general-journal.php?DEL_L=<?php echo $glNumber;?>&BK_NBR=<?php echo $bookNumber; ?>&ACTG=<?php echo $Actg; ?>';
		parent.document.getElementById('recordDelete').style.display='none';
		parent.document.getElementById('fade').style.display='none'; 
	};
</script>


<div style="display:none;">
	<input id="refresh-list" type="button" value="Refresh" onclick="syncGetContent('edit-list','general-journal-edit-list.php?GL_NBR=<?php echo $glNumber;?>');" />
</div>

<?php
$query = "SELECT HED.GL_NBR, SUB.CD_SUB_NBR, SUB.CD_SUB_DESC, SUB.CD_NBR, HED.GL_DTE, HED.GL_DESC, HED.REF, HED.GL_TYP_NBR, DET.CRT, HED.DEPN_NBR,
		CD.CD_CAT_NBR, CD.CD_ACC_NBR, CD.CD_NBR,
		HED.TAX_F
	FROM RTL.ACCTG_GL_HEAD HED
		LEFT OUTER JOIN RTL.ACCTG_GL_DET DET ON HED.GL_NBR = DET.GL_NBR
		LEFT OUTER JOIN RTL.ACCTG_CD_SUB SUB ON SUB.CD_SUB_NBR = DET.CD_SUB_NBR
		LEFT OUTER JOIN RTL.ACCTG_CD CD ON SUB.CD_NBR = CD.CD_NBR
	WHERE HED.GL_NBR = " . $glNumber;

$result = mysql_query($query);
$row = mysql_fetch_array($result);

if($row['GL_TYP_NBR'] != 0) { $readonly = "readonly"; }
	else { $readonly = ""; }

?>

<table class="submenu">
	<tr>
		<td class="submenu" style="background-color:">
			<?php
			foreach($ArrayActg as $key => $value) {
				echo "<a class='submenu' href='general-journal.php?ACTG=".$key."&BK_NBR=".$bookNumber."'><div class='";
					if ($key == $Actg){ echo "arrow_box"; } else { echo "leftsubmenu"; }
					echo "'>".$value."</div></a>";
			}
			?>	
		</td>
		<td class="subcontent">
		
<?php if($security == 0 && $glNumber != 0) { ?>
	<div class="toolbar-only">
		<?php if($readonly == "") { ?>
		<p class="toolbar-left">
			<a title="Hapus" href="javascript:void(0)" onclick="window.scrollTo(0,0);parent.document.getElementById('recordDelete').style.display='block';parent.document.getElementById('fade').style.display='block'"><img style="cursor:pointer" class="toolbar-left" src="img/delete.png"></a>
		</p>
		<?php } ?>
		
		<?php 
		$record 	= mysql_query($query);
		while($data = mysql_fetch_array($record)) {
		if(($data['CD_CAT_NBR'] == 1) && ($data['CD_ACC_NBR'] == 3) && ($data['CRT'] != 0)) { 
			$query_csh = "SELECT 
					SUB.CD_SUB_NBR,
					SUB.CD_SUB_DESC,
					DET.DEB AS VALUE
				FROM RTL.ACCTG_GL_HEAD HED
					LEFT OUTER JOIN RTL.ACCTG_GL_DET DET ON HED.GL_NBR = DET.GL_NBR
					LEFT OUTER JOIN RTL.ACCTG_CD_SUB SUB ON SUB.CD_SUB_NBR = DET.CD_SUB_NBR
					LEFT OUTER JOIN RTL.ACCTG_CD CD ON SUB.CD_NBR = CD.CD_NBR
				WHERE HED.GL_NBR = " . $glNumber." 
					AND SUB.CD_SUB_NBR NOT IN (
						SELECT SUBS.CD_SUB_NBR
						FROM RTL.ACCTG_CD_SUB SUBS
							LEFT OUTER JOIN RTL.ACCTG_CD CDS
								ON SUBS.CD_NBR = CDS.CD_NBR
						WHERE CD_CAT_NBR = 1 AND CD_ACC_NBR = 3
					)
					AND DET.CRT = 0
					
				";
			$result_csh	= mysql_query($query_csh);
			$row_csh	= mysql_fetch_array($result_csh);
			
			$note		= $row_csh['CD_SUB_DESC'];
			$cdSubNbr	= $row_csh['CD_SUB_NBR'];
			$value		= $row_csh['VALUE'];
		?>
		
		<p class="toolbar-right">
				<a><img class="toolbar-right" style="cursor:pointer" src="img/print.png" onclick="parent.document.getElementById('retailPopupEditContent').src='general-journal-edit-print.php?GL_NBR=<?php echo $glNumber; ?>&NOTE=<?php echo $note; ?>&CD_SUB_NBR=<?php echo $cdSubNbr; ?>&VALUE=<?php echo $value; ?>';parent.document.getElementById('retailPopupEdit').style.display='block';parent.document.getElementById('fade').style.display='block';"></a>
		</p>
		<?php 
		}
		} ?>
		
		<?php 
		$query_depn		= "SELECT SUB.CD_NBR, DET.DEB FROM RTL.ACCTG_GL_DET DET 
							LEFT OUTER JOIN RTL.ACCTG_CD_SUB SUB 
								ON DET.CD_SUB_NBR = SUB.CD_SUB_NBR
							WHERE GL_NBR = ".$glNumber;
		$result_depn	= mysql_query($query_depn);
		
		$ArrayDepn 		= array();
		
		while($row_depn	= mysql_fetch_array($result_depn)) {
			$ArrayDepn[]	= $row_depn['CD_NBR'];
		}
		
		if(in_array("2", $ArrayDepn)) {
		?>
		<p class="toolbar-right">
				<a><img class="toolbar-right" style="cursor:pointer" src="img/plus.png" onclick="parent.document.getElementById('retailPopupEditContent').src='depreciation-edit.php?GL_NBR=<?php echo $glNumber; ?>&DEPN_NBR=<?php echo $row['DEPN_NBR']; ?>';parent.document.getElementById('retailPopupEdit').style.display='block';parent.document.getElementById('fade').style.display='block';"></a>
		</p>
		<?php 
			}
			
		?>
	</div>
<?php } ?>

	<form id="general-journal-form" enctype="multipart/form-data" action="#" method="post" style="width:700px" onSubmit="return checkForm();">
		<p>
			<h3>
				Nomor Jurnal: <?php echo $row['GL_NBR'];if($row['GL_NBR']==""){echo "Baru";} ?>
			</h3>
			
			<!-- Header -->
			<div style="float:left;width:140px;">
				<input id="GL_NBR" name="GL_NBR" type="hidden" value="<?php echo $row['GL_NBR'];if($row['GL_NBR']==""){echo "-1";}?>"/>
				
				<label>Tanggal</label>
				<?php  if ($row['GL_DTE'] != "") {
					$row['GL_DTE'] = parseDate($row['GL_DTE']);
				}?>
				<input name="GL_DTE" id="GL_DTE" value="<?php echo $row['GL_DTE'];?>" type="text" style="width:110px;" <?php echo $readonly; ?>/>
				<?php if($readonly == "") { ?>
				<script>
					new CalendarEightysix('GL_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });
				</script>
				<?php } ?>
			</div>

			<div style="float:left;">
				<label>Referensi</label><br />
				<input name="REF" id="REF" value="<?php echo $row['REF']; ?>" type="text" style="width:460px;" <?php echo $readonly; ?>/>
			</div>
		
			<div style="clear:both"></div>

			<div>
				<label>Deskripsi</label><br />
				<input name="GL_DESC" id="GL_DESC" value="<?php echo $row['GL_DESC']; ?>" type="text" style="width:600px;" <?php echo $readonly; ?> />
			</div>
		
			<?php if (!$_SESSION['PLUS_MODE']) { ?>
			<input name='TAX_F'  id='TAX_F'  type='checkbox' class='regular-checkbox'  <?php if($row['TAX_F']=="1"){echo "checked";} ?>/>&nbsp;<label for="TAX_F"></label><label class='checkbox' for="TAX_F" style='cursor:pointer'>Jurnal Eksternal</label><br /><div class="combobox"></div>
			<?php } ?>
			
			<!-- listing -->
			<div id="edit-list" class="edit-list"></div>
			<script>getContent('edit-list','general-journal-edit-list.php?GL_NBR=<?php echo $glNumber;?>&GL_TYP_NBR=<?php echo $row['GL_TYP_NBR']; ?>');</script>
			
			<div style="clear:both"></div>

			<input class="process" id="submit" type="submit" value="Simpan" />

			<script type="text/javascript">
				function checkForm() {
					var totalDebit = jQuery("#GL_TOT_DEB").val(),
						totalCredit = jQuery("#GL_TOT_CRT").val();

					if (totalDebit != totalCredit) {
						window.scrollTo(0, 0);
						parent.document.getElementById('accountingBalance').style.display = 'block';
						parent.document.getElementById('fade').style.display='block';

						return false;
					}
				}
			</script>
		</p>		
	</form>

	</td>
	</tr>
</table>

</body>
</html>

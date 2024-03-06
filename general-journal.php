<?php
require_once "framework/database/connect.php";
	
if ($_GET['DEL_L']!="") {
	$query = "UPDATE RTL.ACCTG_GL_HEAD SET DEL_NBR=" . $_SESSION['personNBR'] . " WHERE GL_NBR =" . $_GET['DEL_L'];
	$result = mysql_query($query);
}

$bookNumber = $_GET['BK_NBR'];

if($locked == 1) { $Actg = 2; }
	else { $Actg = $_GET['ACTG']; }
	
$ArrayActg	= array(
				0 => "ALL",
				1 => "PT",
				2 => "CV",
				3 => "PR"
				);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/pagination/pagination.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/combobox/chosen.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">

	<script type="text/javascript" src="framework/liveSearch/livesearch.js"></script>
	<script type="text/javascript" src="framework/jquery/jquery-latest.min.js"></script>
	<script type="text/javascript" src="framework/pagination/pagination.js"></script>
	<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>
	<script type="text/javascript" src="framework/uri/src/URI.min.js"></script>
	<script type="text/javascript" src="framework/functions/default.js"></script>
	<script type="text/javascript" src='framework/jquery-freezeheader/js/jquery.freezeheader.js'></script>
	<script type="text/javascript" src="framework/combobox/chosen.jquery.js"></script>
	<script type="text/javascript" src="framework/combobox/chosen.default.js"></script>
</head>
<body>


<table class="submenu">
	<tr><?php
		if($locked == 0) {
		echo '<td class="submenu" style="background-color:">';
			
			foreach($ArrayActg as $key => $value) {
				echo "<a class='submenu' href='?ACTG=".$key."&BK_NBR=".$bookNumber."'><div class='";
					if ($key == $Actg){ echo "arrow_box"; } else { echo "leftsubmenu"; }
					echo "'>".$value."</div></a>";
			}
			}
		echo '</td>';
		?>	
		<td class="subcontent">
			
		<div class="toolbar">
		<div class="combobox"></div>
		<?php if ($Actg != 0) { ?>
			<p class="toolbar-left"><a href="general-journal-edit.php?BK_NBR=<?php echo $bookNumber; ?>&GL_NBR=-1&ACTG=<?php echo $Actg;?>">
			<span class="fa fa-plus toolbar" style="cursor:pointer"></span></a></p>
		<?php } ?>
		
		<div style="display: inline-block; float: left; margin-top: 6px; margin-right: 15px;">
		<label>Periode </label>&nbsp;
		<select name="BK_NBR" id="BK_NBR" style='width:150px' class="chosen-select" onchange="location.href='?ACTG=<?php echo $Actg; ?>&BK_NBR=' + document.getElementById('BK_NBR').value">
			<?php
				$query="SELECT BK_NBR, BEG_DTE, END_DTE, CONCAT(BEG_DTE, ' s/d ',END_DTE) AS TANGGAL, MONTH(BEG_DTE) AS BK_MONTH, YEAR(BEG_DTE) AS BK_YEAR 
						FROM RTL.ACCTG_BK WHERE DEL_NBR = 0  AND ACT_F = 1 ORDER BY 3 DESC";
				
				$result = mysql_query($query);
				
				$bulan = array("", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");	
				
				while($row = mysql_fetch_array($result)) {
				
					if($row['BK_NBR'] == $bookNumber){ $pilih="selected";}
						else {$pilih="";}

						echo("<option value=".$row['BK_NBR']." ".$pilih.">".$bulan[$row['BK_MONTH']]." ".$row['BK_YEAR']."</option>"."\n");
				}
			?>
		</select>
		<input type="hidden" id="ACTG" name="ACTG" value="<?php echo $Actg; ?>">
		</div>

	<!--
	<div style="display: inline-block; float: right; margin-right: 15px;">
	<span class="fa fa-search fa-flip-horizontal toolbar"></span><input type="text" id="livesearch" class="livesearch" /></div>
	</div>
	-->

	<div class="searchresult" id="liveRequestResults"></div>
	<br />
	<div id="mainResult"></div>

	</td>
	</tr>
</table>




<script type="text/javascript">
		
	function setDefaultQuery(url) {
		url.setQuery(URI.parseQuery(location.search));
		url.setQuery("ACTG", document.getElementById("ACTG").value);
		url.setQuery("BK_NBR", document.getElementById("BK_NBR").value);
		return url;
	}
	
	var url = new URI("general-journal-ls.php");
	
	url.setQuery(URI.parseQuery(location.search));	
	
	$("#mainResult").load(url.build().toString(), function () {
        $("#mainResult table").tablesorter({ widgets:["zebra"]});
    });

	
	//getContent("mainResult", url.build().toString());
</script>
<script type="text/javascript">

	var url = new URI("general-journal-ls.php");
	
	URI.removeQuery(url, "s");
	
	liveReqInit("livesearch", "liveRequestResults", url.build().toString(), "", "mainResult");
</script>
</body>
</html>			
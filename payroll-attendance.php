<?php
	include "framework/database/connect.php";
	include "framework/security/default.php";

	$arrayPrsn 	= array();
	$wherePrsn 	= "";

	function queryPPL($id){
		//fungsi untuk melakukan seleksi data berdasarkan parent id nya
		$resultPPL	= mysql_query("SELECT PRSN_NBR FROM CMP.PEOPLE WHERE MGR_NBR = '" . $id ."' AND DEL_NBR=0 AND TERM_DTE IS NULL");
		return $resultPPL;
	}

	function show_prsn($id) {
		global $arrayPrsn, $wherePrsn;
		$list_prsn = queryPPL($id);
		if (mysql_num_rows($list_prsn)>0) {
			while($prsn = mysql_fetch_assoc($list_prsn)){
				array_push($arrayPrsn,$prsn['PRSN_NBR']);
				$wherePrsn .= $prsn['PRSN_NBR'].',';
			  	show_prsn($prsn['PRSN_NBR']);
			}
		}
	}

	if($_GET['TYP']=="PAYROLL_OUT") {
		show_prsn($_SESSION['personNBR']);
		$wherePrsn .= $_SESSION['personNBR'];
		$Security = getSecurity($_SESSION['userID'],"AddressBook");
		echo "------------------------".$wherePrsn."<br><br>";
		if($Security < 1){
			$wherePrsn = "";
		}
		echo "------------------------".$wherePrsn."<br><br>";
	} else {
		$Security = getSecurity($_SESSION['userID'],"Payroll");
		echo "xxxxxxxxxxxxxxxxxxxxxxxx".$wherePrsn."<br><br>";
	}

	//echo "security ".$Security;
	echo $wherePrsn;
	
	$_GET['CO_NBR'] = "ALL";
	$CoNbr 			= "SELECT CO_NBR FROM NST.PARAM_COMPANY";
	$searchQuery    = strtoupper($_REQUEST['s']);
	$whereClauses   = array();
	$searchQuery 	= explode(" ", $searchQuery);
	
	if ($searchQuery != "") {
			foreach ($searchQuery as $querySch) {
				$querySch = trim($querySch);

				if (empty($querySch)) {
					continue;
				}

				if (strrpos($querySch, '%') === false) {
					$querySch = '%' . $querySch . '%';
				}
				$whereClauses[] = "(
					PPL.PRSN_NBR LIKE '" . $querySch . "'
					OR PPL.NAME LIKE  '" . $querySch . "'
					OR POS_DESC LIKE  '" . $querySch . "'
					OR COM.NAME  LIKE '" . $querySch . "'
				)";
		}//foreach
	}//if
	$whereClauses 	= implode(" AND ", $whereClauses);
		
	$query		= "SELECT 	PPL.PRSN_NBR,
							PPL.NAME,
							POS_DESC,
							COM.NAME AS CO_NAME
						FROM CMP.PEOPLE PPL
						LEFT OUTER JOIN PAY.PEOPLE PPAY ON PPL.PRSN_NBR = PPAY.PRSN_NBR
						INNER JOIN CMP.POS_TYP POS ON PPL.POS_TYP=POS.POS_TYP
						INNER JOIN CMP.COMPANY COM ON PPL.CO_NBR=COM.CO_NBR 
						LEFT OUTER JOIN PAY.PAYROLL PAY ON PPL.PRSN_NBR=PAY.PRSN_NBR
						WHERE ";
	if (isset($_GET['s'])){ $query 		.= $whereClauses." AND "; }
	if ($wherePrsn != ""){ $query.= "PPL.PRSN_NBR IN (".$wherePrsn.") AND "; }
	$query 		.= "TERM_DTE IS NULL 
					AND PPAY.PAY_TYP='MON'
					AND PPL.CO_NBR IN (".$CoNbr.") 
					AND PPL.DEL_NBR=0
					AND (PAY.DEL_NBR=0 OR PAY.DEL_NBR IS NULL) 
					GROUP BY PPL.PRSN_NBR,NAME,POS_DESC ORDER BY 2";
	$result		= mysql_query($query);
	$alt		= "";

	//echo "<pre>".$query;
		
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<script>parent.Pace.restart();</script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />

<script src="framework/database/jquery.min.js"></script>
<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
<script type="text/javascript" src="framework/tablesorter/jquery-latest.js"></script>
<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>
<script type="text/javascript" src="framework/uri/src/URI.min.js"></script>
	
</head>

<body>
<?php 
if (empty($_GET['s'])){
?>
	<div class="toolbar">
		<p class="toolbar-left"></p>
		<p class="toolbar-right">
			<span class='fa fa-search fa-flip-horizontal toolbar' style='cursor:default'></span><input type="text" id="livesearch" class="livesearch" /></p>
	</div>
<?php
}
?>
<div class="searchresult" id="liveRequestResults"></div>

<div id="mainResult">
	<table id="mainTable" class="tablesorter searchTable">
		<thead>
			<tr style="text-align:center">
				<th style="width : 5%;">No.</th>
				<th style="width : 15%;">ID Karyawan</th>
				<th style="width : 30%;">Nama</th>
				<th style="width : 30%;">Jabatan</th>
				<th style="width : 30%;">Lokasi</th>
			</tr>
		</thead>
		<tbody>
		<?php
			$i=1;
			while($row=mysql_fetch_array($result))
			{
				if (($i % 2) == 0) { $style	= 'background-color:#eee;';}
				else {$style	= 'background-color:white';}
			?>
		<?php if($_GET['TYP']=="PAYROLL_OUT") { ?>
			<?php if(($row['PRSN_NBR']==$_SESSION['personNBR'])&&($Security > 0)){ ?>
				<tr style= "cursor:pointer;<?php echo $style;?>">
			<?php } else { ?>
				<tr style= "cursor:pointer;<?php echo $style;?>" onclick= "PayrollOut(<?php echo $row['PRSN_NBR']; ?>)">	
			<?php }	 ?>
		<?php } else { ?>
			<tr style= "cursor:pointer;<?php echo $style;?>" onclick= "AttendanceEdit(<?php echo $row['PRSN_NBR']; ?>)">
		<?php } ?>
				<td style="text-align:center; "><?php echo $i;?></td>
				<td style="text-align:center;"><?php echo $row['PRSN_NBR'];?></td>
				<td><?php echo $row['NAME'];?></td>
				<td><?php echo $row['POS_DESC'];?></td>
				<td><?php echo $row['CO_NAME'];?></td>
			</tr>
		<?php
			$i++;
			}
		?>
		</tbody>
	</table>
</div>

<script>
	//Action for search
	var url = new URI("payroll-attendance.php?TYP=<?php echo $_GET['TYP']; ?>");
	
	url.setQuery("CO_NBR");
	URI.removeQuery(url, "s");
	
	liveReqInit("livesearch", "liveRequestResults", url.build().toString(), "", "mainResult");
	
	
	//Action Onclick on Attendance Table for Edit 
	function AttendanceEdit(x,y){
		document.location.href='payroll-attendance-ls.php?PRSN_NBR='+x;
	}

	function PayrollOut(x,y){
		document.location.href='payroll-out.php?PRSN_NBR='+x;
	}
	
	$(document).ready(function()
		{
			$("#mainTable").tablesorter({ widgets:["zebra"]});  
		}
	);
</script>
</body>
</html>



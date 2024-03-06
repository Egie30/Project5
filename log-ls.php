<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

</head>
<body>
<?php
	require_once "framework/database/connect.php";
	require_once "framework/functions/default.php";
	require_once "framework/security/default.php";


	$searchQuery = trim(strtoupper(urldecode($_REQUEST[s])));

	$query = "SELECT LOG.CMPTR_NAME, LOG.MACH_TYP, LOG.FIL_NM, LOG.PRN_DIM, LOG.BEG_TS , LOG.END_TS, LOG.DUR_PRN
			FROM CMP.log_file LOG WHERE LOG.CMPTR_NAME LIKE '%".$searchQuery."%' OR LOG.MACH_TYP LIKE
			'%".$searchQuery."%' OR LOG.FIL_NM LIKE '%".$searchQuery."%' OR LOG.PRN_DIM LIKE '%".$searchQuery."%' OR LOG.BEG_TS LIKE '%".$searchQuery."%' OR LOG.END_TS LIKE '%".$searchQuery."%' OR LOG.DUR_PRN LIKE '%".$searchQuery."%' $where";
		$result=mysql_query($query);
		if(mysql_num_rows($result)==0){
		echo "<div class='searchStatus'>Nama atau nomor yang dicari tidak ada didalam kumpulan data</div>";
		exit;
	}

 	
?>

<br \>
<table id="searchTable" class="tablesorter searchTable">
	<thead>
			<tr >
				<th class="sortable">Computer</th>
				<th class="sortable">Printer</th>
				<th class="sortable">Type</th>
				<th class="sortable">File</th>
				<th class="sortable">Dimension</th>
				<th class="sortable">Date</th>
				<th class="sortable">Start</th>
				<th class="sortable">End</th>
				<th class="sortable">Duration</th>
			</tr>
			</thead>
	<tbody>
		<?php
		$alt="";
		$i=1;
		while($row=mysql_fetch_array($result))
		{
			$date = date_create($row['BEG_TS']);
			$date1 = date_create($row['END_TS']);
			$diff = date_diff( $date, $date1 );
			echo "<tr>";
				echo "<td style='text-align:left'>".$row['CMPTR_NAME']."</td>";
				echo "<td style='text-align:left'>".utf8_encode($row['MACH_TYP'])."</td>";
				echo "<td style='text-align:left'>".utf8_encode($row['PRN_TYP'])."</td>";
				echo "<td style='text-align:left'>".$row['FIL_NM']."</td>";
				echo "<td style='text-align:left'>".utf8_encode($row['PRN_DIM'])."</td>";
				echo "<td style='text-align:left'>".date_format($date,'Y-m-d')."</td>";
				echo "<td style='text-align:left'>".date_format($date,'H:i:s')."</td>";
				// echo "<td style='text-align:left'>".$row['END_TS']."</td>";
				echo "<td style='text-align:left'>".date_format($date1,'H:i:s')."</td>";
				echo "<td style='text-align:left'>".$diff->h . ' jam, '.$diff->i . ' menit, '.$diff->s . ' second '."</td>";
				echo "</tr>";
		}

		?>
	</tbody>
</table>

</body>
</html>
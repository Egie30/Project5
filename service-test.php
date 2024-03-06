<?php
	include "framework/database/connect.php";
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<script type="text/javascript" src='framework/tablesort/tablesort.js'></script>

</head>

<body>

<div class="toolbar">
	<p class="toolbar-left"><a href="stationery-edit.php?LOG_NBR=0"><img class="toolbar-left" src="img/add.png" onclick="location.href="></a></p>
	<p class="toolbar-right"><img class="toolbar-right" src="img/search.png"><input type="text" id="livesearch" class="livesearch" /></p>
</div>

<div class="searchresult" id="liveRequestResults"></div>

<div id="mainResult">

	<table id="mainTable" class="rowstyle-alt colstyle-alt no-arrow searchTable">
		<thead>
			<tr>
				<th class="sortable" style="text-align:right;">No.</th>
				<th class="sortable">Tanggal</th>
				<?php
					if($whse==""){echo "<th class='sortable'>Gudang</th>";}
				?>
				<th class="sortable">Jenis Barang</th>
				<th class="sortable">Status</th>
				<th class="sortable">Jumlah</th>
			</tr>
		</thead>
		<tbody>
			tests
		</tbody>
	</table>

</div>
</body>
</html>



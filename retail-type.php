<?php
	include "framework/database/connect.php";
	include "framework/security/default.php";
	
	$UpperSec=getSecurity($_SESSION['userID'],"Stationery");
	
	//Process delete entry
	if($_GET['DEL_L']!="")
	{
		$query="DELETE FROM CMP.RTL_TYP WHERE RTL_TYP_NBR='".$_GET['DEL_L']."'";
		//echo $query;
		$result=mysql_query($query);
	}
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
	<?php if($UpperSec==0){ ?>
	<p class="toolbar-left"><a href="retail-type-edit.php?RTL_TYP_NBR=0"><img class="toolbar-left" src="img/add.png" onclick="location.href="></a></p>
	<?php } ?>
	<p class="toolbar-right"><img class="toolbar-right" src="img/search.png"><input type="text" id="livesearch" class="livesearch" /></p>
</div>

<div class="searchresult" id="liveRequestResults"></div>

<div id="mainResult">
	<table id="mainTable" class="rowstyle-alt colstyle-alt no-arrow searchTable">
		<thead>
			<tr>
				<th class="sortable">No.</th>
				<th class="sortable">Barcode</th>
				<th class="sortable">No. Stock</th>
				<th class="sortable">Deskripsi</th>
				<th class="sortable">Supplier</th>
				<th class="sortable-currency" style="border-right:0px;">Harga</th>
			</tr>
		</thead>
		<tbody>
		<?php
			$query="SELECT RTL_TYP_NBR,RTL_BRC,RTL_NBR,CONCAT(STA.NAME,' ',COLR_DESC,' ',MATR,' ',SIZE,' ',TYPE) AS NAME_DESC,COM.NAME,RTL_PRC
					FROM RTL_TYP RTL INNER JOIN STATIONERY STA ON RTL.RTL_NBR=STA.STA_NBR INNER JOIN CMP.COMPANY COM ON STA.CO_NBR=COM.CO_NBR INNER JOIN CMP.STA_COLR CLR ON STA.COLR_NBR=CLR.COLR_NBR";
			$result=mysql_query($query);
			$alt="";
			while($row=mysql_fetch_array($result))
			{
				echo "<tr $alt ";
				if($UpperSec<5){ 
					echo "style='cursor:pointer;' onclick=".chr(34)."location.href='retail-type-edit.php?RTL_TYP_NBR=".$row['RTL_TYP_NBR']."';".chr(34);
				}
				echo ">";
				echo "<td style='text-align:right'>".$row['RTL_TYP_NBR']."</td>";
				echo "<td style='text-align:center'>".$row['RTL_BRC']."</td>";
				echo "<td style='text-align:right'>".$row['RTL_NBR']."</td>";
				echo "<td>".$row['NAME_DESC']."</td>";
				echo "<td>".$row['NAME']."</td>";
				echo "<td style='text-align:right'>".number_format($row['RTL_PRC'],0,'.',',');
				echo "</tr>";
				if($alt==""){$alt="class='alt'";}else{$alt="";}
			}
		?>
		</tbody>
	</table>
</div>

<script>liveReqInit('livesearch','liveRequestResults','retail-type-ls.php','','mainResult');</script>

<script>fdTableSort.init();</script>

</body>
</html>



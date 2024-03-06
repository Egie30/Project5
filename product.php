<?php
	include "framework/database/connect-cloud.php";
	include "framework/security/default.php";
	if($_GET['DEL_D']!="") {
		echo "oke";
	}
	if($cloud!=false){
		//Process delete entry
		if($_GET['DEL']!="")
		{
			$query="UPDATE $CMP.PROD_LST SET DEL_NBR='".$_SESSION['personNBR']."' WHERE PROD_NBR='".$_GET['DEL']."'";
			//echo $query;
	   		$result= mysql_query($query,$cloud);
			$query = str_replace($CMP,"CMP",$query);
			$result= mysql_query($query,$local);
		}
	}
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
</head>

<body>

<?php
	if(($_GET['DEL_L']!="")&&(!$cloud)){
		echo "<script>window.scrollTo(0,0);parent.document.getElementById('offline').style.display='block';parent.document.getElementById('fade').style.display='block';</script>";			
	}	
?>

<div class="toolbar">
	<p class="toolbar-left"><a href="product-edit.php?PROD_NBR="><span class='fa fa-plus toolbar' style="cursor:pointer" onclick="location.href="></span></a></p>
	<p class="toolbar-right"><span class='fa fa-search fa-flip-horizontal toolbar'></span><input type="text" id="livesearch" class="livesearch" /></p>
</div>

<div class="searchresult" id="liveRequestResults"></div>

<div id="mainResult">
	<table id="mainTable" class="tablesorter searchTable">
		<thead>
			<tr style="text-align:center;">
				<th>No.</th>
				<th>Nama Produk</th>
				<th>Harga Produk</th>
			</tr>
		</thead>
		<tbody>
		<?php
			$i = 1;
			$query="SELECT PROD_NBR, PROD_DESC, PROD_PRC FROM CMP.PROD_LST WHERE DEL_NBR=0";
			//echo $query;
			$result=mysql_query($query,$local);
			$alt="";
			while($row=mysql_fetch_array($result))
			{
				echo "<tr $alt ";
					echo "style='cursor:pointer;' onclick=".chr(34)."location.href='product-edit.php?PROD_NBR=".$row['PROD_NBR']."';".chr(34);
				echo ">";
				echo "<td style='text-align:center;'>".$row['PROD_NBR']."</td>";
				echo "<td>".$row['PROD_DESC']."</td>";
				echo "<td style='text-align:right'>".number_format($row['PROD_PRC'],0,',','.')."</td>";
				echo "</tr>";
				
				$i++;
			}
		?>
		</tbody>
	</table>
</div>
<script>
	$(document).ready(function()
		{
			$("#mainTable").tablesorter({ widgets:["zebra"]});  
		}
	);
</script>
<script>liveReqInit('livesearch','liveRequestResults','product-ls.php','','mainResult');</script>
</body>
</html>



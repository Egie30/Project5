<?php
	include "framework/functions/default.php"; /*NEW*/
	if($_GET['DEL_A']!=""){
		include "framework/database/connect-cloud.php";
	}else{
		include "framework/database/connect.php";
	}
 $CoEx	= "SELECT CO_NBR FROM NST.PARAM_COMPANY";

	$Typ 	= $_GET['TYP'];
	if($Typ=="APV"){
		$whereClause = "AND PPL.APV_F=0";
	}

	if(($locked==1)||($_COOKIE["LOCK"] == "LOCK")){ 
		$whereClause.= "AND PPL.CO_NBR NOT IN (1002, 271)"; 
	}	

	if($cloud!=false){
		//Process delete entry
		if($_GET['DEL_A']!="")
		{
			$query="UPDATE $CMP.PEOPLE SET DEL_NBR=".$_SESSION['personNBR'].",UPD_TS=CURRENT_TIMESTAMP WHERE PRSN_NBR=".$_GET['DEL_A'];
	   		$result=mysql_query($query,$cloud);
			$query=str_replace($CMP,"CMP",$query);
			$result=mysql_query($query,$local);

			$query_pay 	= "UPDATE $PAY.PEOPLE SET DEL_NBR=".$_SESSION['personNBR'].", UPD_TS=CURRENT_TIMESTAMP WHERE PRSN_NBR=".$_GET['DEL_A'];
			$result_pay = mysql_query($query_pay, $cloud);
			$query_pay 	= str_replace($PAY, "PAY", $query_pay);
			$result_pay = mysql_query($query_pay, $local);

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
	if(($_GET['DEL_A']!="")&&(!$cloud)){
		echo "<script>window.scrollTo(0,0);parent.document.getElementById('offline').style.display='block';parent.document.getElementById('fade').style.display='block';</script>";			
	}	
?>

<div class="toolbar">
	<p class="toolbar-left"><?php if((paramCloud()==1)){?> <a href="address-person-edit.php?PRSN_NBR=0"><span class='fa fa-plus toolbar' style="cursor:pointer" onclick="location.href="></span></a> <?php } ?></p>
	<p class="toolbar-right"><span class='fa fa-search fa-flip-horizontal toolbar' style='cursor:default'></span><input type="text" id="livesearch" class="livesearch" /></p>
</div>

<div class="searchresult" id="liveRequestResults"></div>

<div id="mainResult">
	<table id="mainTable" class="tablesorter" style="width: 100%">
		<thead>
			<tr>
				<th style="text-align:right;width:5%;">No.</th>
				<th>Nama</th>
				<th>Alamat</th>
				<th>Perusahaan</th>
				<th>Telpon</th>
			</tr>
		</thead>
		<tbody>
		<?php
			$query="SELECT PRSN_NBR,PPL.NAME,CONCAT(PPL.ADDRESS,', ',CITY_NM) AS ADDR,PPL.PHONE,COM.NAME AS COMPANY
					FROM CMP.PEOPLE PPL
					LEFT OUTER JOIN CMP.CITY CTY ON PPL.CITY_ID=CTY.CITY_ID
					LEFT OUTER JOIN CMP.COMPANY COM ON PPL.CO_NBR=COM.CO_NBR
					WHERE TERM_DTE IS NULL AND PPL.DEL_NBR=0 ".$whereClause."
					ORDER BY PPL.UPD_TS DESC";
			$result=mysql_query($query);
			$alt="";
			while($row=mysql_fetch_array($result))
			{
				echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='address-person-edit.php?PRSN_NBR=".$row['PRSN_NBR']."&CO_NBR=".$CoNbrDef."';".chr(34).">";
				echo "<td style='text-align:right'>".$row['PRSN_NBR']."</td>";
				echo "<td>".$row['NAME']."</td>";
				echo "<td>".$row['ADDR']."</td>";
				echo "<td>".$row['COMPANY']."</td>";
				echo "<td>".$row['PHONE']."</td>";
				echo "</tr>";
				//if($alt==""){$alt="class='alt'";}else{$alt="";}
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

<script>liveReqInit('livesearch','liveRequestResults','address-person-ls.php?TYP=<?php echo $Typ; ?>','','mainResult');</script>

</body>
</html>



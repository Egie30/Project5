<?php

	include "framework/functions/default.php"; /*NEW*/
	if($_GET['DEL_C']!=""){
		include "framework/database/connect-cloud.php";
	}else{
		include "framework/database/connect.php";
	}

	$Typ 	= $_GET['TYP'];
	if($Typ=="APV"){
		$whereClause = "WHERE COM.APV_F=0 AND COM.DEL_NBR=0";
	}

	if(($locked==1)||($_COOKIE["LOCK"] == "LOCK")){ 
		$whereClause.= "AND COM.CO_NBR NOT IN (1002, 271)"; 
	}
	
	if($cloud!=false){
		//Process delete entry
		if($_GET['DEL_C']!="")
		{
			$query="UPDATE $CMP.COMPANY SET DEL_NBR=".$_SESSION['personNBR'].",UPD_TS=CURRENT_TIMESTAMP WHERE CO_NBR=".$_GET['DEL_C'];
	   		$result=mysql_query($query,$cloud);
			$query=str_replace($CMP,"CMP",$query);
			$result=mysql_query($query,$local);
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
	if(($_GET['DEL_C']!="")&&(!$cloud)){
		echo "<script>window.scrollTo(0,0);parent.document.getElementById('offline').style.display='block';parent.document.getElementById('fade').style.display='block';</script>";			
	}	
?>

<div class="toolbar">
	<p class="toolbar-left"><?php if((paramCloud()==1)){?> <a href="address-company-edit.php?CO_NBR=0"><span class='fa fa-plus toolbar' style="cursor:pointer" onclick="location.href="></span></a> <?php } ?></p>
	<p class="toolbar-right"><span class='fa fa-search fa-flip-horizontal toolbar' style='cursor:default'></span><input type="text" id="livesearch" class="livesearch" /></p>
</div>

<div class="searchresult" id="liveRequestResults"></div>

<div style="width:100%" id="mainResult">
	<table id="mainTable" class="tablesorter" style="width: 100%">
		<thead>
			<tr>
				<th style="text-align:right;width:5%">No.</th>
				<th style="width:20%">Perusahaan</th>
				<th style="width:30%">Alamat</th>
				<th style="width:10%">Telpon</th>
				<th style="width:10%;white-space:nowrap;">Aktivitas</th>
				<th style="width:10%;white-space:nowrap;">Tanggal Buat</th>
				<th style="width:10%;white-space:nowrap">Ubah Akhir</th>
			</tr>
		</thead>
		<tbody>
		<?php
			$query="SELECT 
				COM.CO_NBR,
				COM.NAME,
				CONCAT(COM.ADDRESS,', ',CITY_NM) AS ADDR,
				COM.PHONE,
				COM.SUP_F,
				CUST.NBR AS TOP50,
				(SELECT NAME FROM COMPANY C WHERE C.CO_NBR = COM.3RD_PTY_NBR) AS VIA_3RD_PTY_NBR,
				DATE(COM.LAST_ACT_TS) AS LAST_ACT_TS,
				PPL.NAME AS PPL_NAME,
				DATE(CRT_TS_COM) AS CRT_TS_COM,
				DATE(COM.UPD_TS) AS UPD_TS,
				DATE(JRN.CRT_TS) AS CRT_TS,
				
				CASE 
					WHEN JRN.CRT_TS != '' THEN DATE(JRN.CRT_TS)
					ELSE DATE(COM.UPD_TS) 
				END AS CRT_DTE
				
			FROM CMP.COMPANY COM
				INNER JOIN CMP.CITY CTY ON COM.CITY_ID=CTY.CITY_ID
				LEFT OUTER JOIN CMP.PEOPLE PPL ON COM.UPD_NBR = PPL.PRSN_NBR
				LEFT OUTER JOIN (
					SELECT 
						NBR, REV_TOT FROM CDW.PRN_DIG_TOP_CUST 
					WHERE TYP = 'CO_NBR' 
					ORDER BY REV_TOT DESC LIMIT 50
				) CUST ON CUST.NBR = COM.CO_NBR
				LEFT OUTER JOIN(
					SELECT 
						MIN(CRT_TS) AS CRT_TS, PK_DTA
					FROM JRN_LIST 
					WHERE DB_NM ='CMP' AND TBL_NM='COMPANY' AND PK='CO_NBR'
					GROUP BY PK_DTA
				) JRN ON JRN.PK_DTA = COM.CO_NBR
			".$whereClause."
			ORDER BY 2";
			//echo $query;
			$result=mysql_query($query);
			$alt="";
			
			while($row=mysql_fetch_array($result))
			{
				
				$crt_last    = '';
				$last_act_ts = $row['LAST_ACT_TS'];
				
				if ($row['CRT_TS_COM'] == '0000-00-00 00:00:00' || $row['CRT_TS_COM'] == ''){
					$crt_last   = $row['CRT_DTE'];
				}else{
					$crt_last = $row['CRT_TS_COM'];
				}

				echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='address-company-edit.php?CO_NBR=".$row['CO_NBR']."';".chr(34).">";
				echo "<td style='text-align:right'>".$row['CO_NBR']."</td>";
				echo "<td>".$row['NAME']. "&nbsp;" .
						($row['VIA_3RD_PTY_NBR'] != '' ?
                                 '&nbsp;<span style="cursor:pointer;padding: 1px 3px 0px 3px;
                                                    background-color: #989898;
                                                    border-radius: 3px;
                                                    -webkit-border-radius: 3px;
                                                    -moz-border-radius: 3px;
                                                    color: #ffffff;
                                                    width:90px;
                                                    text-align: left;
                                                    font-size: 9pt;    
                                                    vertical-align: 1px;
                                                    overflow:hidden;
                                                    text-overflow:ellipsis;    
                                                    white-space:nowrap;">Via ' . $row['VIA_3RD_PTY_NBR'] . '</span>' : '').
													"&nbsp;" .
						($row['SUP_F'] == 1 ? '<i class="fa fa-industry"></i>' : '') . '&nbsp;' .
                        ($row['TOP50'] != '' ? '<i class="fa fa-star"></i>' : '') .
				"</td>";
				echo "<td>".$row['ADDR']."</td>";
				echo "<td>".$row['PHONE']."</td>";
				if($row['LAST_ACT_TS'] != '') {
					echo "<td style='text-align:center'>".$last_act_ts."</td>";
				}
				else {
					echo "<td>&nbsp;</td>";
				}
				if ($crt_last !=''){
					echo "<td style='text-align:center'>".$crt_last."</td>";
				}else{
					echo "<td>&nbsp;</td>";	
				}
				echo "<td>".shortName($row['PPL_NAME'])."</td>";
				echo "</tr>";
				
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

<script>liveReqInit('livesearch','liveRequestResults','address-company-ls.php?TYP=<?php echo $Typ; ?>','','mainResult');</script>

</body>
</html>

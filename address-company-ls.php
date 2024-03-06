<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	$searchQuery = trim(strtoupper(urldecode($_REQUEST[s])));

	$Typ 	= $_GET['TYP'];
	if($Typ=="APV"){
		$whereClause = "AND COM.APV_F=0";
	}

	if(($locked==1)||($_COOKIE["LOCK"] == "LOCK")){ 
		$whereClause.= "AND COM.CO_NBR NOT IN (1002, 271)"; 
	}

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
			WHERE COM.DEL_NBR=0 ".$whereClause." AND (COM.NAME LIKE '%".$searchQuery."%' OR COM.CO_NBR LIKE '%".$searchQuery."%' OR COM.CO_ID LIKE '%".$searchQuery."%' OR COM.KEYWORDS LIKE '%".$searchQuery."%')
			ORDER BY 2";
	$result=mysql_query($query);
	if(mysql_num_rows($result)==0){
		echo "<div class='searchStatus'>Nama atau nomor yang dicari tidak ada didalam kumpulan data</div>";
		exit;
	}
?>

<table style="width:100%" id="searchTable" class="tablesorter searchTable">
	<thead>
		<tr>
			<th style="text-align:right;width:5%">No.</th>
			<th style="width:25%">Perusahaan</th>
			<th style="width:35%">Alamat</th>
			<th style="width:15%">Telpon</th>
			<th style="width:10%;white-space:nowrap">Aktivitas</th>
			<th style="width:10%;white-space:nowrap;">Tanggal Buat</th>
			<th style="width:10%;white-space:nowrap">Ubah Akhir</th>
		</tr>
	</thead>
	<tbody>
	<?php
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
			if($crt_last !=''){
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
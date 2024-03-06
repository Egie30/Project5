<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	
	include "framework/security/default.php";
	
	$Security=getSecurity($_SESSION['userID'],"AddressBook");
	if($Security>=8){
		$where="WHERE ACCT_EXEC_NBR='".$_SESSION['userID']."'";
	}else{
		$where="WHERE ACCT_EXEC_NBR IS NOT NULL";
	}
	
	$searchQuery = trim(strtoupper(urldecode($_REQUEST[s])));

	$query = "SELECT COM.CO_NBR,COM.NAME,CONCAT(COM.ADDRESS,', ',CITY_NM) AS ADDR,COM.PHONE,PPL.NAME AS ACCT_EXEC_NAME,STG_DESC,ACT_DESC,RAT_DESC,ACT_NTE,DET.UPD_TS  FROM CMP.COMPANY COM INNER JOIN CMP.CITY CTY ON COM.CITY_ID=CTY.CITY_ID INNER JOIN CMP.PEOPLE PPL ON PPL.PRSN_NBR=COM.ACCT_EXEC_NBR LEFT OUTER JOIN (
		SELECT LED.CO_NBR,LED.LEAD_NBR,STG_DESC,ACT_DESC,RAT_DESC,ACT_TS,ACT_NTE,UPD_TS FROM CMP.LEAD_DET LED INNER JOIN CMP.LEAD_STG STG ON LED.LEAD_STG=STG.STG_TYP INNER JOIN CMP.LEAD_ACT ACT ON LED.LEAD_ACT=ACT.ACT_TYP INNER JOIN CMP.LEAD_RAT RAT ON LED.LEAD_RAT=RAT.RAT_TYP INNER JOIN (SELECT CO_NBR,MAX(LEAD_NBR) AS LEAD_NBR FROM CMP.LEAD_DET WHERE DEL_NBR=0 GROUP BY CO_NBR) LST ON LED.LEAD_NBR=LST.LEAD_NBR
		) DET ON COM.CO_NBR=DET.CO_NBR $where 
		AND (COM.CO_NBR LIKE '%".$searchQuery."%' OR COM.NAME LIKE '%".$searchQuery."%' OR COM.ADDRESS LIKE '%".$searchQuery."%')
		ORDER BY 2";
	
	$result=mysql_query($query);
	if(mysql_num_rows($result)==0){
		echo "<div class='searchStatus'>Nama atau nomor yang dicari tidak ada didalam kumpulan data</div>";
		exit;
	}

	while($row=mysql_fetch_array($result)){
			echo "<div id='O".($row['CO_NBR'])."' class='tripane-list' onclick=".chr(34)."changeSiblingUrl('rightpane','lead-management-act.php?CO_NBR=".$row['CO_NBR']."');selLeftPane(this);".chr(34);
			if($firstRow==""){
				echo "style='background-color:#eef8fb'";
			}
			echo ">";
			
			//$back="";
			if($row['RAT_DESC']=='Hot'){
				$dot="<div class='listable' style='display:inline;float:right'><span class='fa fa-circle listable' style='font-size:8pt;color:#d92115'></span></div>";
			}elseif($row['RAT_DESC']=='Warm'){
				$dot="<div class='listable' style='display:inline;float:right'><span class='fa fa-circle listable' style='font-size:8pt;color:#fbad06'></span></div>";
			}elseif($row['RAT_DESC']=='Cold'){
				$dot="";
			}

			echo "<div style='font-weight:bold;color:#666666;font-size:12pt;display:inline;float:left'>".$row['CO_NBR']."</div>";
			echo "<div style='display:inline;float:right;'>".$row['RAT_DESC']."</div>";
			echo "<div style='clear:both'></div>";
			echo $dot;
			echo "<div style='width:75%'>";
			echo "<div style='font-weight:700;color:#3464bc;display:inline;float:left;padding-right:9px;'>".$row['NAME']."</div>&nbsp;";
			if($row['STG_DESC']!=''){
				echo "<div style='display:inline;float:left;'>(".$row['STG_DESC'].")</div>";
			}
			echo "</div>";
			echo "<div style='clear:both'></div>";
			echo "<div>".trim($row['ADDR']." ".$row['PHONE'])."</div>";
			echo "<div style='clear:both'></div>";
			echo "<div>".parseDateShort($row['UPD_TS'])."&nbsp;";
			echo "<span style='font-weight:700'>".$row['ACT_DESC']."</span>";
			echo "</div>";
			echo "<div style='text-align: right;'>".$row['ACCT_EXEC_NAME']."</div>";
			echo "</div>";
			//if($alt=="class='tripane-list-alt'"){$alt="class='tripane-list'";}else{$alt="class='tripane-list-alt'";}
			if($firstRow==""){$firstRow=$row['CO_NBR'];}
		}
?>

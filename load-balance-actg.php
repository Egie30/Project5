<?php
$shipper	= mysql_connect("192.168.1.20","nestor","{;?qSpu!Fw)^jX8E","CMP"); //192.168.1.20
$receiver 	= mysql_connect("192.168.1.10","nestor","cG>cFk3q!RMh]qa#","CMP"); //192.168.1.10

$query      = "SELECT LD_BAL_ACTG FROM CDW.UPD_LAST";
$result     = mysql_query($query, $shipper);
$row        = mysql_fetch_array($result);
$UpdMax 	= $row['LD_BAL_ACTG'];
//$UpdMax 	= "2019-08-12 01:20:00";
echo $UpdMax.'<br/>';

$query      = "SELECT * FROM NST.LD_BAL_LIST WHERE BAL_TYP='A'"; //NST.LD_BAL_LIST
$result     = mysql_query($query, $shipper);

while ($row=mysql_fetch_array($result)) {
	echo $row['DB_NM'].'|'.$row['TBL_NM'].'<br/>';

	$queryShipper 	= "SELECT * FROM ".$row['DB_NM'].".".$row['TBL_NM']." WHERE UPD_TS > '".$UpdMax."'";
	$resultShipper 	= mysql_query($queryShipper, $shipper);
	echo $queryShipper.'<br/>';
	
	$n=1;
	while ($rowShipper=mysql_fetch_array($resultShipper)) {
		echo 'Data ke-'.$n++.'<br/>';
		$primary 	= explode(',', $row['COL_NM']);
		$whereDel	= "";
		for($i=0;$i<count($primary);$i++){
			$whereDel .= $primary[$i]."='".$rowShipper[$primary[$i]]."', ";
		}

		$queryDel 	= "DELETE FROM ".$row['DB_NM'].".".$row['TBL_NM']." WHERE ".substr($whereDel, 0, -2);
		mysql_query($queryDel, $receiver);

		$query_clm	= "SELECT 
							COLUMN_NAME, IS_NULLABLE 
						FROM INFORMATION_SCHEMA.COLUMNS 
						WHERE TABLE_SCHEMA = '".$row['DB_NM']."'
							AND TABLE_NAME = '".$row['TBL_NM']."'";	
		$result_clm	= mysql_query($query_clm, $receiver);
		$val 		= "";
		while($row_clm=mysql_fetch_assoc($result_clm)) {
			$col 	= $row_clm['COLUMN_NAME'];
			if(($row_clm['IS_NULLABLE']=='YES')&&($rowShipper[$col]=="")){
				$val 	.= "NULL, ";
			} else {
				$val 	.= "'".$rowShipper[$col]."', ";
			}
		}

		$queryIns 	= "INSERT INTO ".$row['DB_NM'].".".$row['TBL_NM']." VALUES (".substr($val, 0, -2).")";
		mysql_query($queryIns, $receiver);

		echo $queryDel.'<br/>';
		echo $queryIns.'<br/>';

		echo '<br/>';
		# code...
	}
	echo '<br/>';
	# code...
}

$query      = "SELECT * FROM NST.LD_BAL_LIST WHERE BAL_TYP IN ('PH','PD')"; //NST.LD_BAL_LIST
$result     = mysql_query($query, $shipper);

while ($row=mysql_fetch_array($result)) {
	echo $row['DB_NM'].'|'.$row['TBL_NM'].'<br/>';

	if($row['BAL_TYP']=='PH'){
		$queryShipper 	= "SELECT * FROM ".$row['DB_NM'].".".$row['TBL_NM']." HED WHERE UPD_TS > '".$UpdMax."' AND HED.ACTG_TYP=2";
		$resultShipper 	= mysql_query($queryShipper, $shipper);
		//echo $queryShipper.'<br/>';
	} else if($row['BAL_TYP']=='PD'){
		$queryShipper 	= "SELECT DET.* FROM ".$row['DB_NM'].".".$row['TBL_NM']." DET
									LEFT JOIN ".$row['DB_NM'].".".substr($row['TBL_NM'], 0, -3)."HEAD HED
									ON ".$row['BAL_ON']."
							WHERE DET.UPD_TS > '".$UpdMax."' AND HED.ACTG_TYP=2";
		$resultShipper 	= mysql_query($queryShipper, $shipper);
		//echo $queryShipper.'<br/>';
	}
	echo $queryShipper.'<br/>';
	
	$n=1;
	while ($rowShipper=mysql_fetch_array($resultShipper)) {
		echo 'Data ke-'.$n++.'<br/>';
		$primary 	= explode(',', $row['COL_NM']);
		$whereDel	= "";
		for($i=0;$i<count($primary);$i++){
			$whereDel .= $primary[$i]."='".$rowShipper[$primary[$i]]."', ";
		}

		$queryDel 	= "DELETE FROM ".$row['DB_NM'].".".$row['TBL_NM']." WHERE ".substr($whereDel, 0, -2);
		mysql_query($queryDel, $receiver);

		$query_clm	= "SELECT 
							COLUMN_NAME, IS_NULLABLE  
						FROM INFORMATION_SCHEMA.COLUMNS 
						WHERE TABLE_SCHEMA = '".$row['DB_NM']."'
							AND TABLE_NAME = '".$row['TBL_NM']."'";	
		$result_clm	= mysql_query($query_clm, $receiver);
		$val 		= "";
		while($row_clm=mysql_fetch_assoc($result_clm)) {
			$col 	= $row_clm['COLUMN_NAME'];
			if(($row_clm['IS_NULLABLE']=='YES')&&($rowShipper[$col]=="")){
				$val 	.= "NULL, ";
			} else {
				$val 	.= "'".$rowShipper[$col]."', ";
			}
		}

		$queryIns 	= "INSERT INTO ".$row['DB_NM'].".".$row['TBL_NM']." VALUES (".substr($val, 0, -2).")";
		mysql_query($queryIns, $receiver);

		echo $queryDel.'<br/>';
		echo $queryIns.'<br/>';

		echo '<br/>';
		# code...
	}
	echo '<br/>';
	# code...

	//Cek di 20 untuk menghapus 10 yang rekeningnya berubah
	echo $row['DB_NM'].'|'.$row['TBL_NM'].'<br/>';

	if($row['BAL_TYP']=='PH'){
		$queryShipper 	= "SELECT * FROM ".$row['DB_NM'].".".$row['TBL_NM']." HED WHERE UPD_TS > '".$UpdMax."' AND HED.ACTG_TYP != 2 AND HED.ACTG_TYP != 0 AND HED.ACTG_TYP IS NOT NULL";
		$resultShipper 	= mysql_query($queryShipper, $shipper);
		echo $queryShipper.'<br/>';

		$n=1;
		$dataDel='';
		while ($rowShipper=mysql_fetch_array($resultShipper)) {
			$dataDel 	.= $rowShipper[$row['COL_NM']].',';
		}
		$dataDel 		= substr($dataDel, 0, -1);
		echo 'DATA DELETE '.$dataDel.'<br/>';

		$queryDelete 	= "DELETE FROM ".$row['DB_NM'].".".$row['TBL_NM']." WHERE ".$row['COL_NM']." IN (".$dataDel.")";
		mysql_query($queryDelete, $receiver);
		echo $queryDelete.'<br/>';
	} 
}

echo "<br/>===============================================PENJUALAN===============================================<br/>";

$query      = "SELECT * FROM NST.LD_BAL_LIST WHERE BAL_TYP='CS'"; //NST.LD_BAL_LIST
$result     = mysql_query($query, $shipper);
while ($row=mysql_fetch_array($result)){
	echo $row['DB_NM'].'|'.$row['TBL_NM'].'<br/>';

	$queryShipper 	= "SELECT CSH.* FROM ".$row['DB_NM'].".".$row['TBL_NM']." CSH
						LEFT JOIN CMP.PRN_DIG_ORD_HEAD HED ON CSH.RTL_BRC = HED.ORD_NBR
						WHERE CSH.CSH_FLO_TYP = 'FL' AND HED.ACTG_TYP = 2 AND CSH.CRT_TS > '".$UpdMax."'";
	$resultShipper 	= mysql_query($queryShipper, $shipper);
	echo $queryShipper.'<br/>';
	
	$n=1;
	while ($rowShipper=mysql_fetch_array($resultShipper)) {
		echo 'Data ke-'.$n++.'<br/>';
		$primary 	= explode(',', $row['COL_NM']);
		$whereDel	= "";
		for($i=0;$i<count($primary);$i++){
			$whereDel .= $primary[$i]."='".$rowShipper[$primary[$i]]."', ";
		}

		$queryDel 	= "DELETE FROM ".$row['DB_NM'].".".$row['TBL_NM']." WHERE ".substr($whereDel, 0, -2);
		mysql_query($queryDel, $receiver);

		$query_clm	= "SELECT 
							COLUMN_NAME, IS_NULLABLE 
						FROM INFORMATION_SCHEMA.COLUMNS 
						WHERE TABLE_SCHEMA = '".$row['DB_NM']."'
							AND TABLE_NAME = '".$row['TBL_NM']."'";	
		$result_clm	= mysql_query($query_clm, $receiver);
		$val 		= "";
		while($row_clm=mysql_fetch_assoc($result_clm)) {
			$col 	= $row_clm['COLUMN_NAME'];
			if(($row_clm['IS_NULLABLE']=='YES')&&($rowShipper[$col]=="")){
				$val 	.= "NULL, ";
			} else {
				$val 	.= "'".$rowShipper[$col]."', ";
			}
		}

		$queryIns 	= "INSERT INTO ".$row['DB_NM'].".".$row['TBL_NM']." VALUES (".substr($val, 0, -2).")";
		mysql_query($queryIns, $receiver);

		echo $queryDel.'<br/>';
		echo $queryIns.'<br/>';

		echo '<br/>';
		# code...
	}
	echo '<br/>';
}

echo "<br/>===============================================PEMBAYARAN===============================================<br/>";

$query      = "SELECT * FROM NST.LD_BAL_LIST WHERE BAL_TYP='PY'"; //NST.LD_BAL_LIST
$result     = mysql_query($query, $shipper);
while ($row=mysql_fetch_array($result)){
	echo $row['DB_NM'].'|'.$row['TBL_NM'].'<br/>';

	$queryShipper 	= "SELECT PYMT.* FROM ".$row['DB_NM'].".".$row['TBL_NM']." PYMT 
						LEFT JOIN ".$row['DB_NM'].".".substr($row['TBL_NM'], 0, -4)."HEAD HED 
						ON ".$row['BAL_ON']."
						WHERE HED.ACTG_TYP = 2 AND PYMT.DEL_NBR=0 AND PYMT.UPD_TS > '".$UpdMax."'";
	$resultShipper 	= mysql_query($queryShipper, $shipper);
	echo $queryShipper.'<br/>';
	
	$n=1;
	while ($rowShipper=mysql_fetch_array($resultShipper)) {
		echo 'Data ke-'.$n++.'<br/>';
		$primary 	= explode(',', $row['COL_NM']);
		$whereDel	= "";
		for($i=0;$i<count($primary);$i++){
			$whereDel .= $primary[$i]."='".$rowShipper[$primary[$i]]."', ";
		}

		$queryDel 	= "DELETE FROM ".$row['DB_NM'].".".$row['TBL_NM']." WHERE ".substr($whereDel, 0, -2);
		mysql_query($queryDel, $receiver);

		$query_clm	= "SELECT 
							COLUMN_NAME, IS_NULLABLE 
						FROM INFORMATION_SCHEMA.COLUMNS 
						WHERE TABLE_SCHEMA = '".$row['DB_NM']."'
							AND TABLE_NAME = '".$row['TBL_NM']."'";	
		$result_clm	= mysql_query($query_clm, $receiver);
		$val 		= "";
		while($row_clm=mysql_fetch_assoc($result_clm)) {
			$col 	= $row_clm['COLUMN_NAME'];
			if(($row_clm['IS_NULLABLE']=='YES')&&($rowShipper[$col]=="")){
				$val 	.= "NULL, ";
			} else {
				$val 	.= "'".$rowShipper[$col]."', ";
			}
		}

		$queryIns 	= "INSERT INTO ".$row['DB_NM'].".".$row['TBL_NM']." VALUES (".substr($val, 0, -2).")";
		mysql_query($queryIns, $receiver);

		echo $queryDel.'<br/>';
		echo $queryIns.'<br/>';

		echo '<br/>';
		# code...
	}
	echo '<br/>';
}

echo "<br/>===============================================INV_MOV===============================================<br/>";

$query      = "SELECT * FROM NST.LD_BAL_LIST WHERE BAL_TYP='MO'"; //NST.LD_BAL_LIST
$result     = mysql_query($query, $shipper);
while ($row=mysql_fetch_array($result)){
	echo $row['DB_NM'].'|'.$row['TBL_NM'].'<br/>';

	$queryShipper 	= "SELECT MOV.* FROM ".$row['DB_NM'].".".$row['TBL_NM']." MOV 
						LEFT JOIN RTL.RTL_STK_DET DET ON MOV.ORD_DET_NBR = DET.ORD_DET_NBR
						LEFT JOIN RTL.RTL_STK_HEAD HED ON DET.ORD_NBR = HED.ORD_NBR
						WHERE HED.ACTG_TYP = 2 AND MOV.CRT_TS > '".$UpdMax."'";
	$resultShipper 	= mysql_query($queryShipper, $shipper);
	echo $queryShipper.'<br/>';
	
	$n=1;
	while ($rowShipper=mysql_fetch_array($resultShipper)) {
		echo 'Data ke-'.$n++.'<br/>';
		$primary 	= explode(',', $row['COL_NM']);
		$whereDel	= "";
		for($i=0;$i<count($primary);$i++){
			$whereDel .= $primary[$i]."='".$rowShipper[$primary[$i]]."', ";
		}

		$queryDel 	= "DELETE FROM ".$row['DB_NM'].".".$row['TBL_NM']." WHERE ".substr($whereDel, 0, -2);
		mysql_query($queryDel, $receiver);

		$query_clm	= "SELECT 
							COLUMN_NAME, IS_NULLABLE 
						FROM INFORMATION_SCHEMA.COLUMNS 
						WHERE TABLE_SCHEMA = '".$row['DB_NM']."'
							AND TABLE_NAME = '".$row['TBL_NM']."'";	
		$result_clm	= mysql_query($query_clm, $receiver);
		$val 		= "";
		while($row_clm=mysql_fetch_assoc($result_clm)) {
			$col 	= $row_clm['COLUMN_NAME'];
			if(($row_clm['IS_NULLABLE']=='YES')&&($rowShipper[$col]=="")){
				$val 	.= "NULL, ";
			} else {
				$val 	.= "'".$rowShipper[$col]."', ";
			}
		}

		$queryIns 	= "INSERT INTO ".$row['DB_NM'].".".$row['TBL_NM']." VALUES (".substr($val, 0, -2).")";
		mysql_query($queryIns, $receiver);

		echo $queryDel.'<br/>';
		echo $queryIns.'<br/>';

		echo '<br/>';
		# code...
	}
	echo '<br/>';
}

//change upd_last
$query      = "UPDATE CDW.UPD_LAST SET LD_BAL_ACTG=CURRENT_TIMESTAMP";
mysql_query($query, $shipper);
?>
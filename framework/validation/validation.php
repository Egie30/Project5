<?php
include "../database/connect.php";
$result=null;
$FormId=$_GET['form'];

if($FormId=='add'){
	$PRSN_ID=mysql_real_escape_string($_GET['PRSN_ID']);
	$grab_user=mysql_query("SELECT PRSN_ID FROM CMP.PEOPLE WHERE PRSN_ID='$PRSN_ID'");
	if(mysql_num_rows($grab_user)==0){
		$result['action']='success';
	}else{
		if($_GET['prs']<>$PRSN_ID){
			$result['action']='error';
		}else{
			$result['action']='success';
		}
	}
	$result['PRSN_ID']=$_GET['PRSN_ID'];
	echo json_encode($result);
	
}elseif($FormId=='brc'){
	$INV_BCD=mysql_real_escape_string($_GET['INV_BCD']);
	$grab_user=mysql_query("SELECT INV_BCD FROM RTL.INVENTORY WHERE INV_BCD='$INV_BCD'");
	if(mysql_num_rows($grab_user)==0){
		$result['action'] = 'success';
	}else{
		if($_GET['bcd']<>$INV_BCD){
			$result['action']='error';
		}else{
			$result['action']='success';
		}
	}
	$result['INV_BCD']=$_GET['INV_BCD'];
	echo json_encode($result);
}else if ($FormId=='PRN_DIG_TYP'){
	$INV_BCD = mysql_real_escape_string($_GET['PRN_DIG_TYP']);
	$query   = "SELECT PRN_DIG_EQP FROM CMP.PRN_DIG_TYP WHERE PRN_DIG_TYP='".$_GET['PRN_DIG_TYP']."'";
	$results  = mysql_query($query);

	if (mysql_num_rows($results)<0){
		$result['action'] = "error";
	}else{
		$row = mysql_fetch_array($results);

		$result['action']      = "success";
		$result['PRN_DIG_EQP'] = $row['PRN_DIG_EQP'];
	}

	echo json_encode($result);
}
else if ($FormId=='company') {
	
	$nama = mysql_real_escape_string($_GET['name']);
	
	$query   = "SELECT NAME FROM CMP.COMPANY WHERE NAME='".$nama."'";
	$results  = mysql_query($query);
	
	if (mysql_num_rows($results) > 0){
		echo "deny";
	} else {
		$row = mysql_fetch_array($results);
		echo "allow";
	}

}
?>

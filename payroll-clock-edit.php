<?php
include "framework/database/connect.php";
include "framework/functions/default.php";
include "framework/functions/print-digital.php";

$clockIn	= "";
$clockOut	= "";
$all		= "";
$day		= $_GET['day'];
$NBR		= $_GET['NBR'];

//Memisahkan Tanggal, bulan dan tahun
$m			= parseMonth($day);
$Y			= parseYear($day);
$d			= parseDateOnly($day);
$dateShort	= parseDateShort($day);

//Menngambil nilai POST
$info		= $_POST['info'];
$h			= $_POST['h'];
$i			= $_POST['i'];
$clock		= $h.':'.$i.':00'; 
$sift		= $_POST['sift'];

//Menjadikan time: 00:00:00 dan time in & out bernilai NULL dan mensort berdasarkan info
if($info 		=="In"){
	 $clIn		= "'".$day." ".$clock."'"; 
	 $clOut		= 'NULL';
}elseif($info	== "Out") {
	$clIn		= 'NULL';
	$clOut		= "'".$day.' '.$clock."'";
}

//Mysql untuk menampilkan nama dan no induk 
$query  	= mysql_query("SELECT 	NAME, 
									PRSN_NBR 
						   FROM 	PEOPLE 
						   WHERE PRSN_NBR = ".$NBR);
$row		= mysql_fetch_array($query);

//Mysql untuk menampilkan data yang sudah di masukkan
$queryAll	= "SELECT 	@n := @n + 1 NM,
						CLOK_NBR,
						PRSN_NBR, 
						CLOK_IN_TS, 
						CLOK_OT_TS,
						DATE_FORMAT ((CLOK_IN_TS), '%H') AS JM,
						DATE_FORMAT ((CLOK_IN_TS), '%i') AS MN,
						TIME (CLOK_IN_TS) AS TM
				FROM   CMP.MACH_CLOK,(SELECT @n := 0)M
				WHERE  	PRSN_NBR= $NBR AND DATE(CLOK_IN_TS)= '$day' ORDER BY 1";
$resultAll	= mysql_query($queryAll);
$numAll 	= mysql_num_rows($resultAll);

while ($all = mysql_fetch_array($resultAll)){
	$nm[]		= $all['NM'];
	$clockNbr[]	= $all['CLOK_NBR'];
	$clockInTs[]= $all['CLOK_IN_TS'];
}
//Menjadikan post sift  menjadi clock in ts
if ($_POST['sift']==1){
	$idnbr = $clockNbr[0];
}elseif ($_POST['sift']==2){
	$idnbr =$clockNbr[1];
}elseif ($_POST['sift']==3){
	$idnbr =$clockNbr[2];
}
//Membuat array jam dan menit untuk combobox
for ($i=0; $i<=24; $i++){if ($i<=9){$allH[]= '0'.$i;	}else {$allH[]=$i;} }
for ($i=0; $i<=60; $i++){if ($i<=9){$alli[]= '0'.$i;	}else {$alli[]=$i;} }

//Menentukan field yang akan diisi berdasarkan post info
if ($info  == "In"){
	$Field  = "CLOK_IN_TS";
}else {
	$Field = "CLOK_OT_TS";
}

if(isset($_POST['submit'])){
		if ($numAll >0){
			if ($_POST['sift'] == $nm[0] || $_POST['sift'] == $nm[1] || $_POST['sift'] == $nm[2]){
				$query 	= "UPDATE CMP.MACH_CLOK SET 
									 ".$Field."= '".$day." ".$clock."', 
									 UPD_TS=CURRENT_TIMESTAMP 
									 WHERE  PRSN_NBR=$NBR AND CLOK_NBR ='$idnbr'";
				$result	= mysql_query($query);
			}else{
				$query  = "INSERT INTO CMP.MACH_CLOK(PRSN_NBR, CLOK_IN_TS, CLOK_OT_TS, UPD_TS) 
							VALUES (".$NBR.",".$clIn.",".$clOut.", CURRENT_TIMESTAMP)";
				$result	= mysql_query($query);
				echo $query;
			}
		}elseif($numAll <=0) {
				$query  = "INSERT INTO CMP.MACH_CLOK(PRSN_NBR, CLOK_IN_TS, CLOK_OT_TS, UPD_TS) 
							VALUES (".$NBR.",".$clIn.",".$clOut.", CURRENT_TIMESTAMP)";
				$result	= mysql_query($query);
				echo $query;
		}
		
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" type="text/css" media="screen" href="css/popup.css" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<link rel="stylesheet" href="framework/combobox/chosen.css">

<script type="text/javascript" src="framework/liveSearch/livepop.js"></script>
<script type="text/javascript" src="framework/functions/default.js"></script>
<script>
	function myFunction(){
		parent.document.getElementById('content').contentDocument.getElementById('Refresh').click();
	}
</script>
</head>
<body>
<span class='fa fa-times toolbar' style='margin-left:10px' onclick="slideFormOut();" ></span>
<form name="mForm" enctype="multipart/form-data" action="#" method="post" style="width:400px" onSubmit="return checkform();">
	<h3><?php echo $row['NAME'];?></h3>
	<h3>Nomor Induk : <?php echo $row['PRSN_NBR'];?></h3>
	<table>
	<tbody>
	  <tr>
		<td>Tanggal</td>
		<td> <?php echo date ("d-m-Y",strtotime($day))?> </td>
	  </tr>
	  <tr>
		<td>Sift</td>
		<td>
			<select class="chosen-select" style='width:60px' name="sift" id="sift"><br />
				<div class='labelbox'></div>
				<?php genComboArrayVal(array('1','2','3')); ?>
			</select>
		</td>
	  </tr>	  
	  <tr>
		<td>Tipe</td>
		<td>
			<select class="chosen-select" style='width:60px' name="info"><br />
				<div class='labelbox'></div>
				<?php genComboArrayVal(array('In','Out')); ?>
			</select>
		</td>
	  </tr>
	  <tr >
		<td>Waktu</td>
		<td >
			<select class="chosen-select" style='width:53px' name="h"><br />
				<div class='labelbox'></div>
				<?php genComboArrayVal($allH); ?>
			</select>
			&nbsp;&nbsp;:&nbsp;&nbsp; 
			<select class="chosen-select" style='width:53px' name="i"><br />
				<div class='labelbox'></div>
				<?php genComboArrayVal($alli); ?>
			</select>
		</td >
	  </tr>
	</tbody>
	</table>
	<input id="submit" class="process" name="submit" type="submit" value="Simpan" onclick="myFunction()"/>
</form>
</body>
<script src="framework/database/jquery.min.js" type="text/javascript"></script>
<script src="framework/combobox/chosen.jquery.js" type="text/javascript"></script>

<script type="text/javascript">
		var config = {
			'.chosen-select'           : {},
			'.chosen-select-deselect'  : {allow_single_deselect:true},
			'.chosen-select-no-single' : {disable_search_threshold:10},
			'.chosen-select-no-results': {no_results_text:'Data tidak ketemu'},
			'.chosen-select-width'     : {width:"95%"}
	   	}
		for (var selector in config) {
			$(selector).chosen(config[selector]);
		}
</script>
</html>

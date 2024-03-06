<?php
include "framework/functions/default.php";

$paper 				= 'A4';
$glNumber	 		= $_GET["GL_NBR"];
$printNumber		= $_GET['PRN_NBR'];
$formattedglNumber 	= leadZero($glNumber, 7);

if ($glNumber != '') {
    $title .= ' #' . $glNumber;
}

$query    = "SELECT NAME
            FROM RTL.PEOPLE PPL
               INNER JOIN RTL.POS_TYP POS ON PPL.POS_TYP=POS.POS_TYP
            WHERE PRSN_ID='" . $_SESSION["userID"] . "'";
$result   = mysql_query($query);
$row      = mysql_fetch_array($result);
$operator = $row["NAME"];

$query  = "SELECT 
			PRN.PRN_NBR,
			PRN.CD_SUB_NBR,
			PRN.VAL,
			PRN.NAME,
			PRN.ADDRESS,
			SUB.CD_SUB_DESC AS NOTE,
			HED.GL_NBR,
			HED.REF,
			HED.GL_DTE,
			PPL.NAME AS HED_NAME
		FROM RTL.ACCTG_PRN PRN	
		LEFT OUTER JOIN RTL.ACCTG_CD_SUB SUB
			ON PRN.CD_SUB_NBR = SUB.CD_SUB_NBR
		LEFT OUTER JOIN RTL.ACCTG_GL_HEAD HED
			ON PRN.GL_NBR = HED.GL_NBR
		LEFT OUTER JOIN RTL.PEOPLE PPL
			ON HED.CRT_NBR = PPL.PRSN_NBR
		WHERE PRN.PRN_NBR = ".$printNumber."
		";

$result = mysql_query($query);
$row	= mysql_fetch_assoc($result);

$title = "BUKTI PENGELUARAN KAS";		
		  
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xml:lang="en" xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<title><?php echo $title; ?></title>
<link rel="stylesheet" href="http://necolas.github.io/normalize.css/latest/normalize.css">
<style type="text/css">
@page {
	margin-top: 20px;
}
body {
	font-family: "Courier", Georgia, Serif;
	margin: 40px 0.25cm;
	text-align: left;
	color: black;
	font-size:12px;
	line-height: 18px;
}

table {
	border-collapse: collapse;
	border: none;
}

#header {
	font-family: "Times New Roman", Georgia, Serif;
	border-bottom: 2px solid;
	padding-bottom: 20px;
}

#header h1 {
	margin: 0;
	font-size:20px;
}

#body {
	font-family: "Courier New", Courier, "Lucida Sans Typewriter", "Lucida Typewriter", "monospace";
}

#body table{
	margin-bottom: 15px;
}

#body table:last-child{
	margin-bottom: 0;
}

#header, #footer {
	position: fixed;
	left: 0;
	right: 0;
}

#header {
	top: 0;
}

#footer {
	font-family: "Times New Roman", Georgia, Serif;
	font-size:10px;
	bottom: 700px;
}

.border-gold {
	border-color: rgb(92,51,23);
}

.text-center {
	text-align:center;
}

.text-left {
	text-align:left;
}

.text-right {
	text-align:right;
}
</style>
</head>
<body>
<div id="header">
	<table>
		<tr>
		<td><img src="img/logo.png" height="50px" width="100px"></td>
		<td style="width:25px">&nbsp;</td>
		<td><h1 class="text-center"><?php echo $title; ?></h1></td>
		</tr>
	</table>
</div>
<br /><br />

<div id="body">

<table>
	<tr><td>No. Jurnal</td><td>:</td><td><?php echo $row['GL_NBR']; ?></td></tr>
	<tr><td>No. Referensi Jurnal</td><td>:</td><td><?php echo $row['REF']; ?></td></tr>
	<tr><td>Tanggal Cetak</td><td>:</td><td><?php echo date('Y-m-d h:i:s'); ?></td></tr>
	<tr><td>Dicetak Oleh</td><td>:</td><td><?php echo $operator; ?></td></tr>
</table>

<br />



<table style="width:100%">
	<tr><td>HARI/TANGGAL</td><td>:</td><td><?php echo date("d-m-Y", strtotime(row['GL_DTE'])); ?></td></tr>
	<tr><td>NAMA</td><td>:</td><td><span style="text-decoration:underline;"><?php echo $row['NAME']; ?></span></td></tr>
	<tr><td>ALAMAT</td><td>:</td><td><span style="text-decoration:underline;"><?php echo $row['ADDRESS']; ?></td></tr>
	<tr><td></td><td></td><td>TELAH TERIMA UANG DARI "VINOLIA BABY & KIDS"</td></tr>
	<tr><td></td><td></td><td>UANG SEBANYAK : <?php echo ucfirst(convert_number_to_words($row['VAL'])); ?> rupiah</td></tr>
	<tr><td></td><td></td><td>GUNA MEMBAYAR : <span style="text-decoration:underline;"><?php echo $row['NOTE']; ?></span></td></tr>
	<tr><td>TERBILANG</td><td>:</td><td>Rp. <?php echo number_format($row['VAL'], 0, ",", "."); ?></td></tr>
</table>

<br /><br />

<center>
<table>
	<tr>
		<td>
			<table>
				<tr><td style="text-align:center">Mengetahui</td></tr>
				<tr><td><br /></td></tr>
				<tr><td><br /></td></tr>
				<tr><td style="text-align:center">( ............. )</td></tr>
			</table>
		</td>
		<td>
			<table>
				<tr><td style="text-align:center">Diperiksa</td></tr>
				<tr><td><br /></td></tr>
				<tr><td><br /></td></tr>
				<tr><td style="text-align:center">( ............. )</td></tr>
			</table>
		</td>
		<td>
			<table>
				<tr><td style="text-align:center">Dibukukan</td></tr>
				<tr><td><br /></td></tr>
				<tr><td><br /></td></tr>
				<tr><td style="text-align:center">( ............. )</td></tr>
			</table>
		</td>
		<td>
			<table>
				<tr><td style="text-align:center;width:150px">Pemberi</td></tr>
				<tr><td><br /></td></tr>
				<tr><td><br /></td></tr>
				<tr><td style="text-align:center">( <?php echo $row['HED_NAME']; ?> )</td></tr>
			</table>
		</td>
		<td>
			<table>
				<tr><td style="text-align:center">Penerima</td></tr>
				<tr><td><br /></td></tr>
				<tr><td><br /></td></tr>
				<tr><td style="text-align:center">( ............. )</td></tr>
			</table>
		</td>
	</tr>
</table>

</center>

</div>


</body>
</html>

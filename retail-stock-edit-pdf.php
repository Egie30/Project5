<?php
ob_start();
define('FPDF_FONTPATH','framework/pdf/font/');
require('framework/pdf/fpdf.php');
include "framework/database/connect.php";
include "framework/functions/dotmatrix.php";

$OrdNbr=$_GET['ORD_NBR'];
$PrnTyp=$_GET['PRN_TYP'];
$IvcTyp=$_GET['IVC_TYP'];

	$query="SELECT NAME FROM CMP.PEOPLE PPL INNER JOIN CMP.POS_TYP POS ON PPL.POS_TYP=POS.POS_TYP WHERE PRSN_ID='".$_SESSION['userID']."'";
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	$name=$row['NAME'];

	$query="SELECT ORD_NBR,DATE_FORMAT(CRT_TS,'%d-%m-%Y') AS CRT_DT,DATE_FORMAT(ORD_DTE,'%d-%m-%Y') AS ORD_DT,FEE_MISC,PYMT_DOWN,PYMT_REM,DISC_AMT,TOT_REM, 
			SHP.NAME AS SHP_NAME,SHP.ADDRESS AS SHP_ADDRESS,RCV.ADDRESS AS RCV_ADDRESS,RCV.NAME AS RCV_NAME,REF_NBR,IVC_PRN_CNT,TAX_APL_ID,TAX_AMT,TOT_AMT
			FROM RTL.RTL_STK_HEAD HED
			LEFT OUTER JOIN CMP.COMPANY SHP ON HED.SHP_CO_NBR=SHP.CO_NBR
			LEFT OUTER JOIN CMP.COMPANY RCV ON HED.RCV_CO_NBR=RCV.CO_NBR
			WHERE ORD_NBR='$OrdNbr' ";
	//echo $query;
	$result=mysql_query($query);
	$hrow=mysql_fetch_array($result);
	//Title
	if($IvcTyp=="SL"){
	$Title='NOTA SALES';
	}else if($IvcTyp=="PO"){
	$Title='PURCHASE ORDER';
	}else{
	$Title='NOTA PEMBELIAN';
	}
	
	$NotaNb='Nota No. '.leadZero($OrdNbr,6);
	$NotaDt='Tanggal Nota: '.$hrow['ORD_DT'];
	$NotaSh=$hrow['SHP_NAME']." ".$hrow['SHP_ADDRESS'];
	$NotaRc=$hrow['RCV_NAME']." ".$hrow['RCV_ADDRESS'];
	

	
class PDF extends FPDF {

}

$pdf=new PDF('P','cm','Letter');
$pdf->Open();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetMargins(0.75,1,1);
$pdf->Image('img/logochampion.png',0.8,1,4);
$pdf->SetFont('Arial','',8);
$pdf->Cell(0,0.5,'',0,0,'L');
$pdf->SetFont('Arial','B',15);
$pdf->Cell(0,0.5,$Title,0,1,'R');
$pdf->Cell(0,0.1,'',0,1,'L');
$pdf->SetFont('Arial','',8);
$pdf->Cell(0,0.5,$NotaNb,0,1,'R');
$pdf->Cell(0,0.5,$NotaDt,0,1,'R');
$pdf->Cell(19.9,0.5,'Dicetak Oleh : '.$name,0,1,'R');
$pdf->Cell(1.7,0.5,'Pengirim  : ',0,0,'L');
$pdf->Cell(11,0.5,$NotaSh,0,0,'L');
$pdf->Cell(7.2,0.5,'Tanggal Cetak : '.date( 'd-m-Y H:i', strtotime(now)),0,1,'R');
$pdf->Cell(1.7,0.5,'Penerima : ',0,0,'L');
$pdf->Cell(0,0.5,$NotaRc,0,0,'L');
$pdf->SetFont('Arial','B',12);
$pdf->SetMargins(0.75,1,0.5);
$query="SELECT ORD_DET_NBR,ORD_NBR,DET.INV_NBR,DET.INV_DESC,INV.INV_BCD,INV.PRC,INV.NAME,ORD_Q,DET.INV_PRC,FEE_MISC,
		DISC_PCT,DISC_AMT,TOT_SUB,ORD_X,ORD_Y,ORD_Z,CRT_TS,CRT_NBR,DET.UPD_TS,DET.UPD_NBR
		FROM RTL.RTL_STK_DET DET LEFT OUTER JOIN
		RTL.INVENTORY INV ON DET.INV_NBR=INV.INV_NBR
		WHERE ORD_NBR='$OrdNbr'
		ORDER BY DET.ORD_DET_NBR ASC";
$query = mysql_query( $query );
                                                                                     
if ($PrnTyp=="SL" || $PrnTyp=="PO"){
//Header
   $x=$pdf->GetY();
   $pdf->SetY($x+1);
   $pdf->SetFont('Arial','B',8);
   $pdf->Cell(20,0.01,'',1,1,'C');
   $pdf->Cell(1.5,0.5,'Jumlah',0,0,'L');
   $pdf->Cell(3,0.5,'',0,0,'L');
   $pdf->Cell(7,0.5,'Deskripsi Pesanan',0,0,'L');
   $pdf->Cell(1.5,0.5,'Harga',0,0,'R');
   $pdf->Cell(1.5,0.5,'',0,0,'R');
   $pdf->Cell(2,0.5,'Disc',0,0,'R');
   $pdf->Cell(1.5,0.5,'',0,0,'R');
   $pdf->Cell(2,0.5,'Subtotal',0,1,'R');//last col
   $pdf->Cell(20,0.01,'',1,1,'R');

//Detail
 while( $result= mysql_fetch_array( $query )){
   $Jml = $result['ORD_Q'];
   $Brc = $result['INV_BCD'];  
   $Nma = $result['NAME']." ".$result['INV_DESC'];
   $Fak = $result['INV_PRC'];
   $Dis = number_format($result['DISC_PCT'],0,",",".")."/".number_format($result['DISC_AMT'],0,",",".");
   $Sub1 = number_format($result['TOT_SUB'],0,",",".");
   $Jual = $result['PRC'];
   $Sub2 = number_format($result['PRC']*$result['ORD_Q'],0,",",".");

   $pdf->SetFont('Arial','',8);

   $pdf->Cell(1.5,0.5,$Jml,0,0,'C');
   $pdf->Cell(3,0.5,$Brc,0,0,'L');
   $pdf->Cell(7,0.5,$Nma,0,0,'L');
   $pdf->Cell(1.5,0.5,$Fak,0,0,'R');
   $pdf->Cell(1.5,0.5,'',0,0,'R');
   $pdf->Cell(2,0.5,$Dis,0,0,'R');
   $pdf->Cell(1.5,0.5,'',0,0,'R');
   $pdf->Cell(2,0.5,$Sub1,0,1,'R');//last col
   $TSub1+=$Sub1;
   $TSub2+=$Sub2;
}
//Footer
   $pdf->Cell(20,0.01,'',1,1,'L');
   $pdf->SetFont('Arial','B',8);
   $pdf->Cell(14.5,0.5,'',0,0,'R');
   $pdf->Cell(2,0.5,'Biaya Tambahan',0,0,'R');
   $pdf->Cell(1.5,0.5,'',0,0,'R');
   $pdf->Cell(2,0.5,number_format($hrow['FEE_MISC'],0,",","."),0,1,'R');
   $pdf->Cell(6,0.5,'Penerima',0,0,'C');
   $pdf->Cell(6,0.5,'Penjual',0,0,'C');
   $pdf->Cell(2.5,0.5,'',0,0,'C');
   $pdf->Cell(2,0.5,'Total',0,0,'R');
   $pdf->Cell(1.5,0.5,'',0,0,'R');
   $pdf->Cell(2,0.5,number_format($hrow['TOT_AMT'],0,",","."),0,1,'R');
   $pdf->Cell(14.5,0.5,'',0,0,'R');
   $pdf->Cell(2,0.5,'Uang Muka',0,0,'R');
   $pdf->Cell(1.5,0.5,'',0,0,'R');
   $pdf->Cell(2,0.5,number_format($hrow['PYMT_DOWN'],0,",","."),0,1,'R');
   $pdf->Cell(6,0.5,'(_________________)',0,0,'C');
   $pdf->Cell(6,0.5,'(_________________)',0,0,'C');
   $pdf->Cell(2.5,0.5,'',0,0,'C');
   $pdf->Cell(2,0.5,'Pelunasan',0,0,'R');
   $pdf->Cell(1.5,0.5,'',0,0,'R');
   $pdf->Cell(2,0.5,number_format($hrow['PYMT_REM'],0,",","."),0,1,'R');
   $pdf->Cell(14.5,0.5,'',0,0,'R');
   $pdf->Cell(2,0.5,'Sisa',0,0,'R');
   $pdf->Cell(1.5,0.5,'',0,0,'R');
   $pdf->Cell(2,0.5,number_format($hrow['TOT_REM'],0,",","."),0,1,'R');
   $pdf->Cell(20,0.5,'Terima kasih atas kepercayaan anda. Silakan hubungi kami untuk produk stationery/paper yang lain.',0,1,'L');

}else{
//Header
   $x=$pdf->GetY();
   $pdf->SetY($x+1);
   $pdf->SetFont('Arial','B',8);
   $pdf->Cell(20,0.01,'',1,1,'L');
   $pdf->Cell(1.5,0.5,'Jumlah',0,0,'L');
   $pdf->Cell(1.5,0.5,'PID',0,0,'C');
   $pdf->Cell(3,0.5,'Barcode',0,0,'L');
   $pdf->Cell(7,0.5,'Nama',0,0,'L');
   $pdf->Cell(1.5,0.5,'',0,0,'R');
   $pdf->Cell(1.5,0.5,'Faktur',0,0,'R');
   $pdf->Cell(2,0.5,'Disc',0,0,'R');
   $pdf->Cell(2,0.5,'Subtotal',0,1,'R');//last col
   /*
   $pdf->Cell(1.5,0.5,'Faktur',0,0,'L');
   $pdf->Cell(1.5,0.5,'Disc',0,0,'L');
   $pdf->Cell(2,0.5,'Subtotal',0,0,'L');
   $pdf->Cell(1.5,0.5,'Jual',0,0,'L');
   $pdf->Cell(2,0.5,'Subtotal',0,1,'L');//last col
   */
   $pdf->Cell(20,0.01,'',1,1,'L');

//Detail
 while( $result= mysql_fetch_array( $query )){
	//if($result['ORD_X']!='' || $result['ORD_X']!=Null){$X= " Uk ".$result['ORD_X'];}
   //if($result['ORD_Y']!='' || $result['ORD_Y']!=Null){$Y= "x".$result['ORD_Y'];}
   if($result['ORD_X']!=''){ $X= " Uk ".$result['ORD_X']; }elseif($result['ORD_X']=='' || $result['ORD_X']==Null){$X="";}
   if($result['ORD_Y']!=''){ $Y= "x".$result['ORD_Y']; }elseif($result['ORD_Y']=='' || $result['ORD_Y']==Null){$Y="";}
   if($result['ORD_Z']!=''){ $Z= "x".$result['ORD_Z']; }elseif($result['ORD_Z']=='' || $result['ORD_Z']==Null){$Z="";}

   $Jml = $result['ORD_Q'];
   $Pid = leadZero($result['ORD_DET_NBR'],6);
   $Brc = $result['INV_BCD'];
   $Nma = $result['NAME']." ".$result['INV_DESC']."".$X."".$Y."".$Z;

   $Fak = $result['INV_PRC'];
   $Dis = number_format($result['DISC_PCT'],0,",",".")."/".number_format($result['DISC_AMT'],0,",",".");
   $Sub1 = $result['TOT_SUB'];
   $Jual = $result['PRC'];
   $Sub2 = number_format($result['PRC']*$result['ORD_Q'],0,",",".");

   $pdf->SetFont('Arial','',8);

   $pdf->Cell(1.5,0.5,$Jml,0,0,'C');
   $pdf->Cell(1.5,0.5,$Pid,0,0,'C');
   $pdf->Cell(3,0.5,$Brc,0,0,'L');
   $pdf->Cell(7,0.5,$Nma,0,0,'L');
   $pdf->Cell(1.5,0.5,'',0,0,'R');
   $pdf->Cell(1.5,0.5,$Fak,0,0,'R');
   $pdf->Cell(2,0.5,$Dis,0,0,'R');
   $pdf->Cell(2,0.5,number_format($Sub1,0,",","."),0,1,'R');//last col
   /*
   $pdf->Cell(1.5,0.5,$Fak,0,0,'R');
   $pdf->Cell(1.5,0.5,$Dis,0,0,'R');
   $pdf->Cell(2,0.5,$Sub1,0,0,'R');
   $pdf->Cell(1.5,0.5,$Jual,0,0,'R');
   $pdf->Cell(2,0.5,$Sub2,0,1,'R');//last col
   */
   $TSub1+=$Sub1;
 }
//Footer
   $pdf->Cell(20,0.01,'',1,1,'L');
   $pdf->SetFont('Arial','B',8);
   $pdf->Cell(18,0.5,'TOTAL',0,0,'R');
   $pdf->Cell(2,0.5,number_format($TSub1,0,",","."),0,1,'R');
   $pdf->Cell(16.9,0.5,'',0,0,'R');
   $pdf->Cell(3.1,0.01,'',1,1,'R');
 /*  $pdf->SetFont('Arial','',8);
   $pdf->Ln();
   $pdf->Cell(4,0.5,'Mengetahui',0,0,'C');
   $pdf->Cell(4,0.5,'Pembuat',0,0,'C');
   $pdf->Cell(4,0.5,'Penerima',0,1,'C');
   $pdf->Ln();
   $pdf->Ln();
   $pdf->Cell(4,0.5,'(_________________)',0,0,'C');
   $pdf->Cell(4,0.5,'(_________________)',0,0,'C');
   $pdf->Cell(4,0.5,'(_________________)',0,0,'C');
   */
}
$pdf->Output();
?>

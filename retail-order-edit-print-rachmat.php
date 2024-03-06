<?php
	include "framework/functions/dotmatrix.php";
	include "framework/database/connect.php";
	
	$header = followSpace('HYPER COMP',57)."NOTA JUAL".chr(13).chr(10);
	$header.=followSpace("Jl. Cendrawasih No.3, Caturtunggal, Depok, Sleman",111).chr(13).chr(10);
	$header.=followSpace("Telp. 083852625566",108).chr(13).chr(10);
	$header.=followSpace("Pelanggan    : CASH",25).chr(10);
	$header.=followSpace("Tanggal      : 03 Juni 2021",30).chr(13).chr(10);
    $header.=str_repeat("-",135).chr(13).chr(10);
	$header.=" Qty          Kode                             Deskripsi                                     Harga            Disc             Subtotal".chr(13).chr(10);
    $header.=str_repeat("-",135).chr(13).chr(10);

	$string=$header;
	
	$string.=leadSpace('12',5)."  ";
	$string.=followSpace('KL-AMP01',13)."  ";
	$string.=followSpace("Kabel AMP UTP Cat 5E ",50)."   ";
	$string.=leadSpace('5500',24)."  ";
	$string.=leadSpace("0",19)."  ";
	$string.=leadSpace('66000',13);
		
	$string.=chr(13).chr(10);	
	$string.=leadSpace(' 1',5)."  ";
	$string.=followSpace('141044',13)."  ";
	$string.=followSpace("Coneector RJ45 AMP ",50)."   ";
	$string.=leadSpace('100000',24)."  ";
	$string.=leadSpace("0",19)."  ";
	$string.=leadSpace('100000',13);
	$string.=chr(13).chr(10);
	
	$string.=leadSpace('14',5)."  ";
	$string.=followSpace('0790069419935',13)."  ";
	$string.=followSpace("Switch Dlink DES-1008 ",50)."   ";
	$string.=leadSpace('2500',24)."  ";
	$string.=leadSpace("0",19)."  ";
	$string.=leadSpace('35000',13);
	$string.=chr(13).chr(10);
		
	$string.=pRow(9-$rowCount);
    $string.=str_repeat("-",135).chr(13).chr(10);
	$string.=leadSpace("",9)."  ";
	$string.=pSpace(100)."Total ".leadSpace('201000',18).chr(13).chr(10);
	$string.="      Toko     ".pSpace(7)."     Pelanggan     ".pSpace(5)."                   ".pSpace(14).$Summary2.chr(13).chr(10);
	$string.=$Summary3.chr(13).chr(10);
	$string.=pSpace(81).$Summary4.chr(13).chr(10);
	$string.="(________________)".pSpace(6)."(_________________)".pSpace(5)."                   ".pSpace(14).$Summary5.chr(13).chr(10);
	$string.=chr(13).chr(10);

	echo "<pre style='font-size:9pt;letter-spacing:-1.25px;'>";
	echo $string;
	echo "</pre>";
	
	if($PrnTyp=='SL'){
		$string=str_replace($dspHeader,$prnHeader,$string);
	}

	$fh=fopen("print-digital/ORD-".$OrdNbr.".txt", "w");
	fwrite($fh, chr(15).$string.chr(18));
	fclose($fh);
?>

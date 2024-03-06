<?php
	include "framework/functions/dotmatrix.php";
	include "framework/database/connect.php";
	
	$i = 0;
	
	$query_pymt		= "SELECT MAX(PYMT_DTE) AS PYMT_DTE FROM PAY.PAYROLL";
	$result_pymt		= mysql_query($query_pymt);
	$row_pymt 		= mysql_fetch_array($result_pymt);
	
	$PymtDte		= $row_pymt['PYMT_DTE'];
	
	
	$query_tot	= "SELECT COUNT(PPL.PRSN_NBR) AS CNT, SUM(PAY_AMT) AS PAY_TOT
			FROM PAY.PAYROLL PAY INNER JOIN
			CMP.PEOPLE PPL ON PPL.PRSN_NBR=PAY.PRSN_NBR WHERE CO_NBR IN (SELECT CO_NBR FROM NST.PARAM_PAYROLL WHERE CO_NBR<>1099) AND TERM_DTE IS NULL AND PAY.DEL_NBR=0 AND PYMT_DTE='".$PymtDte."' AND MONTH(PYMT_DTE)=MONTH(CURRENT_DATE) AND BNK_ACCT_NBR IS NOT NULL AND BNK_CO_NBR=859 AND PPL.DEL_NBR = 0";
			
	$result_tot	= mysql_query($query_tot);
	$row_tot	= mysql_fetch_array($result_tot);
	
	$i		= $row_tot['CNT'];
	$payTot		= $row_tot['PAY_TOT'];
	
	$periode = 1;

	function getDatePay($start,$periode){
		$start   = date("Y-m-d",strtotime($start.'1 Day'));
		$day     = date("d", strtotime($start));

		//Holiday
		$query   = "SELECT HLDY_DTE FROM PAY.HOLIDAY WHERE MONTH(HLDY_DTE)='".date('m')."' AND YEAR(HLDY_DTE)='".date('Y')."'";
		$result  = mysql_query($query);
		while ($row= mysql_fetch_array($result)) {
			$holiday[]= $row['HLDY_DTE'];
		}

		//Cek Saturday
		$querySat = "SELECT WEEKDAY('".$start."') AS DAY";
		$resSat   = mysql_query($querySat);
		$rowSat   = mysql_fetch_array($resSat);
		$DayIndex = $rowSat['DAY'];
		
		if (in_array($start, $holiday)){
			return getDatePay($start, $periode);
		}else {
			if ($DayIndex == 5){
				return getDatePay($start, $periode);
			}else{
				if($periode<=1){
					$periode=$periode+1;
					return getDatePay($start, $periode);
				}else{
					return $day;
				}
			}
			
		}
	}
	//echo $query_tot;
	
		$header="00000000000003700138GAJI";
		$header.=getDatePay(date('Y-m-d'),$periode);
		$header.="01037320777000MF";
		$header.=leadZero($i,5);
		$header.=leadZero($payTot,14).".00";
		$header.=date("m");
		$header.=date("Y").chr(13).chr(10);
		
		
	echo "<pre style='font-size:9pt;letter-spacing:-1.25px;'>";
	echo $header;
	
	$query_comp 	= "SELECT CO_NBR FROM NST.PARAM_PAYROLL WHERE CO_NBR<>1099";
	$result_comp	= mysql_query($query_comp);
	
	while ($row_comp = mysql_fetch_array($result_comp)) {
	
	$companyNumber = $row_comp['CO_NBR'];
	
	$query	= "SELECT NAME,BNK_ACCT_NBR,PAY_AMT,PPL.PRSN_NBR,CO_NBR
			FROM PAY.PAYROLL PAY INNER JOIN
			CMP.PEOPLE PPL ON PPL.PRSN_NBR=PAY.PRSN_NBR WHERE CO_NBR = ".$companyNumber." AND TERM_DTE IS NULL AND PAY.DEL_NBR=0 AND PYMT_DTE='".$PymtDte."' AND MONTH(PYMT_DTE)=MONTH(CURRENT_DATE) AND BNK_ACCT_NBR IS NOT NULL AND BNK_CO_NBR=859 AND PPL.DEL_NBR = 0 ORDER BY CO_NBR, PPL.PRSN_NBR";
		
	//echo $query."<br /><br />";
	
	$result	= mysql_query($query);

	$i=0;
	
	$string	= "";
	
	while($row = mysql_fetch_array($result))
	{	
		
		$i++;
		$string.="0".$row['BNK_ACCT_NBR'].leadZero($row['PAY_AMT'],13)."00".leadZero($row['PRSN_NBR'],10).followSpace(strtoupper($row['NAME']),30);
		
		if($row['CO_NBR']==271){
			$string.="CPRN";
		}elseif($row['CO_NBR']==997){
			$string.="PROL";
		}elseif($row['CO_NBR']==889){
			$string.="TJYN";
		}elseif($row['CO_NBR']==1002){
			$string.="CCPS";
		}elseif($row['CO_NBR']==1099){
			$string.="CGND";
		}elseif($row['CO_NBR']==2996){
			$string.="CVCP";
		}elseif($row['CO_NBR']==2997){
			$string.="CVCC";
		}elseif($row['CO_NBR']==3680){
			$string.="KOPR";
		}elseif($row['CO_NBR']==3110){
			$string.="CCRP";
		}

		$string.=chr(13).chr(10);
		//$payTot+=$row['PAY_AMT'];
					
	}
				
		//$string.=chr(13).chr(10);
		
		//echo "<pre style='font-size:9pt;letter-spacing:-1.25px;'>";
		echo $string;
		//echo "</pre>";
		
		$string=str_replace($dspHeader,$prnHeader,$string);
	}
	
	echo "</pre>";
?>
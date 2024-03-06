<?php
	include "framework/database/connect.php";
	include "framework/security/default.php";	
	$security=getSecurity($_SESSION['userID'],"DigitalPrint");
	$UpperSec=getSecurity($_SESSION['userID'],"Accounting");
	
	$OrdNbr	= $_GET['ORD_NBR'];
	$type	= $_GET['TYP'];
	$DelDet	= $_GET['DEL_D'];
	$DelAdd	= $_GET['DEL_A'];
	$Show	= $_GET['SHOW'];
	if ($Show=="NO"){
		$WhereDel="DET.DEL_NBR<>0";
		$OpenS = "<strike>";
		$CloseS= "</strike>";
	} else {
		$WhereDel="DET.DEL_NBR=0";
		$OpenS = "";
		$CloseS= "";
	}
	$TotNet	= 0;
	
	if($type == "EST"){
		$headtable 	= "CMP.PRN_DIG_ORD_HEAD_EST";
		$detailtable= "CMP.PRN_DIG_ORD_DET_EST";
		$tableAdd	= "CMP.PRN_DIG_ORD_VAL_ADD_EST";
	}else{
		$headtable 	= "CMP.PRN_DIG_ORD_HEAD";
		$detailtable= "CMP.PRN_DIG_ORD_DET";
		$tableAdd	= "CMP.PRN_DIG_ORD_VAL_ADD";
	}
	
	if($DelDet!=""){
		//$query="DELETE FROM CMP.PRN_DIG_ORD_DET WHERE ORD_NBR=".$OrdNbr." AND ORD_DET_NBR=".$DelDet;
		$query="UPDATE ". $detailtable ." SET DEL_NBR=".$_SESSION['personNBR'].",UPD_TS=CURRENT_TIMESTAMP WHERE ORD_NBR=".$OrdNbr." AND ORD_DET_NBR=".$DelDet;
		
		$result=mysql_query($query);
		if(file_exists("print-digital\\".$DelDet)){
			unlink("print-digital\\".$DelDet);
		}
		
		$query="DELETE FROM ".$tableAdd." WHERE ORD_NBR=".$OrdNbr." AND ORD_DET_NBR=".$DelDet;
		$result=mysql_query($query);
		
		$queryd 	= "SELECT ORD_DET_NBR FROM ". $detailtable ." WHERE ORD_DET_NBR_PAR = ".$DelDet;
		$resultd	= mysql_query($queryd);
		while($rowd = mysql_fetch_array($resultd)) {
			$query="UPDATE ". $detailtable ." SET DEL_NBR=".$_SESSION['personNBR'].",UPD_TS=CURRENT_TIMESTAMP WHERE ORD_NBR=".$OrdNbr." AND ORD_DET_NBR=".$rowd['ORD_DET_NBR'];
			$result=mysql_query($query);
			if(file_exists("print-digital\\".$rowd['ORD_DET_NBR'])){
				unlink("print-digital\\".$rowd['ORD_DET_NBR']);
			}
			
			$query="DELETE FROM ".$tableAdd." WHERE ORD_NBR=".$OrdNbr." AND ORD_DET_NBR=".$rowd['ORD_DET_NBR'];
			$result=mysql_query($query);
			
		}
	}
	if($DelAdd!=""){
		$query="DELETE FROM ".$tableAdd." WHERE ORD_NBR=".$OrdNbr." AND ORD_VAL_ADD_NBR=".$DelAdd;
		$result=mysql_query($query);
	}
	
	//Process security
	$query="SELECT ORD_STT_ID FROM ". $headtable ." WHERE ORD_NBR=".$OrdNbr;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	If($security==0){
		$detailAdd="<div class='listable-btn'><span class='fa fa-plus listable-btn' onclick=".chr(34)."if(document.getElementById('ORD_NBR').value==-1){parent.parent.document.getElementById('invoiceAdd').style.display='block';parent.parent.document.getElementById('fade').style.display='block';return;};slideFormIn('print-digital-edit-list-detail.php?ORD_DET_NBR=0&ORD_NBR=".$OrdNbr."&TYP=".$type."');".chr(34)."></span></div>
		<div class='listable-btn'><span class='fa fa-tag listable-btn' onclick=".chr(34)."if(document.getElementById('ORD_NBR').value==-1){parent.parent.document.getElementById('invoiceAdd').style.display='block';parent.parent.document.getElementById('fade').style.display='block';return;};slideFormIn('print-digital-edit-list-product-detail.php?ORD_DET_NBR=0&ORD_NBR=".$OrdNbr."&TYP=".$type."');".chr(34)."></span></div>";
	}
	if(($security==1)&&(in_array($row["ORD_STT_ID"],array('NE','RC','QU')))){
		$detailAdd="<div class='listable-btn'><span class='fa fa-plus listable-btn' onclick=".chr(34)."if(document.getElementById('ORD_NBR').value==-1){parent.parent.document.getElementById('invoiceAdd').style.display='block';parent.parent.document.getElementById('fade').style.display='block';return;};slideFormIn('print-digital-edit-list-detail.php?ORD_DET_NBR=0&ORD_NBR=".$OrdNbr."&TYP=".$type."');".chr(34)."></span></div>
		<div class='listable-btn'><span class='fa fa-tag listable-btn' onclick=".chr(34)."if(document.getElementById('ORD_NBR').value==-1){parent.parent.document.getElementById('invoiceAdd').style.display='block';parent.parent.document.getElementById('fade').style.display='block';return;};slideFormIn('print-digital-edit-list-product-detail.php?ORD_DET_NBR=0&ORD_NBR=".$OrdNbr."&TYP=".$type."');".chr(34)."></span></div>";
	}

?>
<table style="background:#ffffff;">
	<tr>
		<th class="listable">Jum</th>
        <th class="listable"style='padding:2px'><input name='SEL_IMG' id='SEL_IMG' type='checkbox' class='regular-checkbox' onclick="toggleCheckBox(this)"/><label for='SEL_IMG' style='margin-top:5px;margin-right:0px'></label></th>
		<th class="listable">Deskripsi</th>
		<th class="listable">PID</th>
		<?php if($security != 7 && $UpperSec != 8){ ?>
		<th class="listable">Harga</th>
		<th class="listable">Lain2</th>
		<th class="listable">Disc</th>
		<th class="listable">Subtot</th>
		<?php } ?>
		<th class="listable" style="width: 10%;"><?php if($OrdNbr!=0){echo $detailAdd;} ?></th>
	</tr>
	<?php
		$query="SELECT ORD_DET_NBR,DET.ORD_NBR,DET_TTL,PRN_DIG_DESC,DET.PRN_DIG_PRC,ORD_Q,FIL_LOC,PRN_LEN,PRN_WID,FEE_MISC,FAIL_CNT,DISC_PCT,DISC_AMT,VAL_ADD_AMT,TOT_SUB,ROLL_F,HND_OFF_TYP,HND_OFF_TS,SORT_BAY_ID,TYP.PRN_DIG_CD,EQP.PRN_DIG_EQP_COLR
				FROM ". $detailtable ." DET LEFT OUTER JOIN
				     CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP LEFT OUTER JOIN
					 CMP.PRN_DIG_EQP EQP ON TYP.PRN_DIG_EQP=EQP.PRN_DIG_EQP
				WHERE ORD_NBR=".$OrdNbr." AND ORD_DET_NBR_PAR IS NULL AND ".$WhereDel." AND DET.ORD_NBR!=0 ORDER BY 1";
		//echo $query;
		$result=mysql_query($query);
		$alt="";
		$typeCode 	= array();
		while($rowd=mysql_fetch_array($result))
		{
            $OrdQ='';
            $PrnDigPrc=0;
            $FeeMisc=0;
            $DiscAmt=0;
            $TotSub=0;
            $rowDetail='';
			$query="SELECT ORD_DET_NBR,DET.ORD_NBR,ORD_DET_NBR_PAR,DET_TTL,TYP.PRN_DIG_TYP,PRN_DIG_DESC,DET.PRN_DIG_PRC,ORD_Q,FIL_LOC,PRN_LEN,PRN_WID,FEE_MISC,FAIL_CNT,DISC_PCT,DISC_AMT,VAL_ADD_AMT,TOT_SUB,ROLL_F,HND_OFF_TYP,HND_OFF_TS,SORT_BAY_ID,PPL.NAME,DET.UPD_TS,DET.N_UP, DET.PROD_NBR,TYP.PRN_DIG_CD,EQP.PRN_DIG_EQP_COLR
					FROM ". $detailtable ." DET LEFT OUTER JOIN
						     CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP LEFT OUTER JOIN
						 CMP.PRN_DIG_EQP EQP ON TYP.PRN_DIG_EQP=EQP.PRN_DIG_EQP
						LEFT JOIN CMP.PEOPLE PPL ON DET.DEL_NBR=PPL.PRSN_NBR
					WHERE ORD_DET_NBR=".$rowd['ORD_DET_NBR']." OR ORD_DET_NBR_PAR=".$rowd['ORD_DET_NBR']." AND ".$WhereDel." ORDER BY 1";
			//echo $query;
			$resultc=mysql_query($query);
			while($rowc=mysql_fetch_array($resultc))
			{
				if ($Show=="NO") 
					{ $rowDetail.="<tr $alt title='Dihapus Oleh ".$rowc['NAME']." Pada ".$rowc['UPD_TS']."' "; }
				else 
					{ $rowDetail.="<tr $alt "; }

				// variable link/url untuk membedakan url edit biasanya atau url ke edit product
				if ($rowc['PROD_NBR']!=''){
					$link = "print-digital-edit-list-product-detail.php";
				}else{
					$link = "print-digital-edit-list-detail.php";
				}

				If($security==0){
					$rowDetail.="onclick=".chr(34)."slideFormIn('".$link."?ORD_DET_NBR=".$rowc['ORD_DET_NBR']."&ORD_NBR=".$OrdNbr."&TYP=".$type."&SHOW=".$Show."&STT=".$_GET['STT']."');".chr(34);
				}			
				if(($security==1)&&(in_array($row["ORD_STT_ID"],array('NE','RC','QU','PF')))){
					$rowDetail.="onclick=".chr(34)."slideFormIn('".$link."?ORD_DET_NBR=".$rowc['ORD_DET_NBR']."&ORD_NBR=".$OrdNbr."&TYP=".$type."&SHOW=".$Show."&STT=".$_GET['STT']."');".chr(34);
				}
				if(($security==1)&&(in_array($row["ORD_STT_ID"],array('RD','DL','CP')))){
				    $rowDetail.="onclick=".chr(34)."slideFormIn('".$link."?ORD_DET_NBR=".$rowc['ORD_DET_NBR']."&ORD_NBR=".$OrdNbr."&TYP=".$type."&readonly=1&SHOW=".$Show."&STT=".$_GET['STT']."');".chr(34);
				}
				if(($security==2)&&(in_array($row["ORD_STT_ID"],array('PF','QU','PR','FN','RD')))){
				    $rowDetail.="onclick=".chr(34)."slideFormIn('".$link."?ORD_DET_NBR=".$rowc['ORD_DET_NBR']."&ORD_NBR=".$OrdNbr."&TYP=".$type."&readonly=1&SHOW=".$Show."&STT=".$_GET['STT']."');".chr(34);
				}
				$rowDetail.=">";
				if($rowc['ORD_DET_NBR_PAR']!="" && $type != "EST"){
					$rowDetail.="<td></td>";
				}
				
				if($type == "EST" && $rowc['ORD_DET_NBR_PAR']==""){$align="left";}elseif($type == "EST" && $rowc['ORD_DET_NBR_PAR']!=""){$align="right";}else{$align="right";}
				$rowDetail.="<td style='cursor:pointer;text-align:".$align."'>".$OpenS.$rowc['ORD_Q'].$CloseS."</td>";
				
				if($rowc['ORD_DET_NBR_PAR']=="" || $type == "EST"){
                $rowDetail.="<td style='padding:2px;text-align:center'><input name='SEL_IMG_".$rowc['ORD_DET_NBR']."' id='SEL_IMG_".$rowc['ORD_DET_NBR']."' type='checkbox' class='regular-checkbox' onclick=".chr(34)."event.cancelBubble=true;".chr(34)."/><label for='SEL_IMG_".$rowc['ORD_DET_NBR']."' style='margin-right:0px;margin-top:5px' onclick=".chr(34)."event.cancelBubble=true;".chr(34)."></label></td>";
				}
				
				if(($rowc['PRN_LEN']!="")&&($rowc['PRN_WID']!="")){$prnDim=" ".$rowc['PRN_LEN']."x".$rowc['PRN_WID'];}else{$prnDim="";}
				if ($rowc['N_UP']!=""){$nUp=" (1 jadi ".$rowc['N_UP'].")";}else{$nUp="";}
				$rowDetail.="<td style='cursor:pointer;' ";
				$rowDetail.=">".$OpenS.trim($rowc['DET_TTL']." ".$rowc['PRN_DIG_DESC']).$prnDim.$nUp.$CloseS."</td>";
				$rowDetail.="<td style='cursor:pointer;text-align:right;white-space:nowrap'>";
				$rowDetail.="<div class='print-digital-grey' style='cursor:pointer' onclick=".chr(34)."event.cancelBubble=true;parent.parent.document.getElementById('printDigitalPopupBarcodeContent').src='print-digital-edit-list-barcode.php?BARCODE=P".str_pad($rowc['ORD_DET_NBR'],6,0,STR_PAD_LEFT)."';parent.parent.document.getElementById('printDigitalPopupBarcode').style.display='block';parent.parent.document.getElementById('fade').style.display='block';".chr(34).">".$rowc['ORD_DET_NBR']."</div> ";
				if($rowc['HND_OFF_TYP']=='PU'){$rowDetail.="<span class='fa fa-shopping-cart fa-fw listable' style='cursor:pointer'</span>";}
				if($rowc['HND_OFF_TYP']=='DL'){$rowDetail.="<span class='fa fa-truck fa-fw listable' style='cursor:pointer'</span>";}
				if($rowc['HND_OFF_TYP']=='NS'){$rowDetail.="<span class='fa fa-flag fa-fw listable' style='cursor:pointer'</span>";}
				$rowDetail.=" ".$rowc['SORT_BAY_ID'];
				$rowDetail.="</td>";
                if(($rowc['ORD_DET_NBR_PAR']=='')&&($rowc['PRN_DIG_TYP']=='PROD')){
                    $OrdQ=$rowc['ORD_Q'];
                }
                $PrnDigPrc+=$rowc['PRN_DIG_PRC'];
                $FeeMisc+=$rowc['FEE_MISC']*$rowc['ORD_Q']*($rowc['PRN_LEN'] ?: 1)*($rowc['PRN_WID'] ?: 1);
                $DiscAmt+=$rowc['DISC_AMT']*$rowc['ORD_Q']*($rowc['PRN_LEN'] ?: 1)*($rowc['PRN_WID'] ?: 1);
                $TotSub+=$rowc['TOT_SUB'];
                if(($rowc['ORD_DET_NBR_PAR']=='')&&($rowc['PRN_DIG_TYP']=='PROD')){
					if($security != 7 && $UpperSec != 8){
				    $rowDetail.="<td style='cursor:pointer;text-align:right;'>[PRN_DIG_PRC]</td>";
				    $rowDetail.="<td style='cursor:pointer;text-align:right;'>[FEE_MISC]</td>";
				    $rowDetail.="<td style='cursor:pointer;text-align:right;'>[DISC_AMT]</td>";
				    $rowDetail.="<td style='cursor:pointer;text-align:right;'>[TOT_SUB]</td>";
					}
                }else{
                    if(($OrdQ!='')&&($rowc['ORD_DET_NBR_PAR']!='')){
                        $color='color:#bbbbbb;';
                    }else{
                        $color='';
                    }
					if($security != 7 && $UpperSec != 8){
                    $rowDetail.="<td style='cursor:pointer;text-align:right;$color'>".$OpenS.number_format($rowc['PRN_DIG_PRC'],0,",",".").$CloseS."</td>";
				    $rowDetail.="<td style='cursor:pointer;text-align:right;$color'>".$OpenS.number_format($rowc['FEE_MISC'],0,",",".").$CloseS."</td>";
				    $rowDetail.="<td style='cursor:pointer;text-align:right;$color'>".$OpenS.number_format($rowc['DISC_PCT'],0,",",".")."/".number_format($rowc['DISC_AMT'],0,",",".").$CloseS."</td>";
				    $rowDetail.="<td style='cursor:pointer;text-align:right;$color'>".$OpenS.number_format($rowc['TOT_SUB'],0,",",".").$CloseS."</td>";
					}
                }
				$rowDetail.="<td style='cursor:pointer;text-align:center;padding-left:2px;padding-right:2px;white-space:nowrap'>";
				If($security==0){
					if($rowc['ORD_DET_NBR_PAR']==""){
						$rowDetail.="<div class='listable-btn'><span class='fa fa-list-ul listable-btn' onclick=".chr(34)."event.cancelBubble=true;slideFormIn('print-digital-edit-list-detail.php?ORD_DET_NBR=0&ORD_NBR=".$OrdNbr."&ORD_DET_NBR_PAR=".$rowc['ORD_DET_NBR']."&TYP=".$type."');".chr(34)."></span></div>";
					}else{
						$rowDetail.="<img class='listable' src='img/blank16x16.png'>";
					}
					$rowDetail.="<div class='listable-btn'><span class='fa fa-trash listable-btn'  onclick=".chr(34)."event.cancelBubble=true;syncGetContent('edit-list','print-digital-edit-list.php?ORD_NBR=".$OrdNbr."&DEL_D=".$rowc['ORD_DET_NBR']."&TYP=".$type."');calcAmt();".chr(34)."></span></div>";
				}
				if(($security==1)&&(in_array($row["ORD_STT_ID"],array('NE','RC','QU')))){
					if($rowc['ORD_DET_NBR_PAR']==""){
						$rowDetail.="<div class='listable-btn'><span class='fa fa-list-ul listable-btn' onclick=".chr(34)."event.cancelBubble=true;slideFormIn('print-digital-edit-list-detail.php?ORD_DET_NBR=0&ORD_NBR=".$OrdNbr."&ORD_DET_NBR_PAR=".$rowc['ORD_DET_NBR']."&TYP=".$type."');".chr(34)."></span></div>";
					}else{
						$rowDetail.="<img class='listable' src='img/blank16x16.png'>";
					}
					$rowDetail.="<div class='listable-btn'><span class='fa fa-trash listable-btn'  onclick=".chr(34)."event.cancelBubble=true;getContent('edit-list','print-digital-edit-list.php?ORD_NBR=".$OrdNbr."&DEL_D=".$rowc['ORD_DET_NBR']."&TYP=".$type."');calcAmt();".chr(34)."></span></div>";
				}
				if(file_exists("print-digital\\".$rowc['ORD_DET_NBR'])){
					$rowDetail.="<a href='download.php?ORD_DET_NBR=".$rowc['ORD_DET_NBR']."'><div class='listable-btn'><span class='fa fa-link listable-btn'></span></div>";
				}
				//if($rowd['ROLL_F']=="1"){
				//	echo "<img class='listable' src='img/cut.png' style='cursor:pointer;' onclick=".chr(34)."parent.parent.document.getElementById('printDigitalPopupJournalContent').src='print-digital-edit-list-stock-journal.php?ORD_DET_NBR=".$rowd['ORD_DET_NBR']."&ORD_NBR=".$OrdNbr."';parent.parent.document.getElementById('printDigitalPopupJournal').style.display='block';parent.parent.document.getElementById('fade').style.display='block'".chr(34).">";
				//}
				$rowDetail.="</td></tr>";
				$query="SELECT ORD_VAL_ADD_NBR,VAL_ADD_Q,VAL_ADD_DESC,VAD.VAL_ADD_PRC,FEE_MISC,DISC_PCT,DISC_AMT,TOT_SUB
						 FROM ".$tableAdd." VAD LEFT OUTER JOIN
						      CMP.PRN_DIG_VAL_ADD_TYP TYP ON VAD.VAL_ADD_TYP=TYP.VAL_ADD_TYP
						 WHERE ORD_DET_NBR=".$rowd['ORD_DET_NBR'];
				$resulta=mysql_query($query);
				while($rowa=mysql_fetch_array($resulta))
				{
					$rowDetail.="<tr $alt>";
					$rowDetail.="<td></td>";
					$rowDetail.="<td style='text-align:right;'>".$rowa['VAL_ADD_Q']."</td>";
					$rowDetail.="<td>".$rowa['VAL_ADD_DESC']."</td>";
					$rowDetail.="<td style='text-align:right;'>".$rowa['ORD_VAL_ADD_NBR']."</td>";
					$rowDetail.="<td style='text-align:right;'>".number_format($rowa['VAL_ADD_PRC'],0,",",".")."</td>";
					$rowDetail.="<td style='text-align:right;'>".number_format($rowa['VAL_ADD_AMT'],0,",",".")."</td>";
					$rowDetail.="<td style='text-align:right;'>".number_format($rowa['FEE_MISC'],0,",",".")."</td>";
					$rowDetail.="<td style='text-align:right;'>".number_format($rowa['DISC_PCT'],0,",",".")."/".number_format($rowa['DISC_AMT'],0,",",".")."</td>";
					$rowDetail.="<td style='text-align:right;'>".number_format($rowa['TOT_SUB'],0,",",".")."</td>";
					$rowDetail.="<td style='text-align:center;' style='padding-left:2px;padding-right:2px;'>";
					$rowDetail.="<img class='listable' src='img/write.png' style='cursor:pointer;' onclick=".chr(34)."parent.parent.document.getElementById('printDigitalPopupEditContent').src='print-digital-edit-list-add.php?ORD_VAL_ADD_NBR=".$rowa['ORD_VAL_ADD_NBR']."&ORD_DET_NBR=".$rowd['ORD_DET_NBR']."&ORD_NBR=".$OrdNbr."';parent.parent.document.getElementById('printDigitalPopupEdit').style.display='block';parent.parent.document.getElementById('fade').style.display='block'".chr(34).">";
					$rowDetail.="<img class='listable' src='img/trash.png' onclick=".chr(34)."syncGetContent('edit-list','print-digital-edit-list.php?ORD_NBR=".$OrdNbr."&DEL_A=".$rowa['ORD_VAL_ADD_NBR']."');calcAmt();".chr(34).">";
					$TotNet+=$rowa['TOT_SUB'];
					$rowDetail.="</td></tr>";
				}
				if($alt==""){$alt="class='alt'";}else{$alt="";}
				$TotNet+=$rowc['TOT_SUB'];
			}
            if($OrdQ!=''){
                $PrnDigPrc=number_format(($TotSub+$DiscAmt-$FeeMisc)/$OrdQ,0,",",".");
                $FeeMisc=number_format(($FeeMisc)/$OrdQ,0,",",".");
                $DiscAmt=number_format(($DiscAmt)/$OrdQ,0,",",".");
                $denom=($TotSub+$DiscAmt);if($denom==0){$denom=1;}
                $DiscPct=number_format(($DiscAmt/($denom)*100),0,",",".");
                $TotSub=number_format($TotSub,0,",",".");
                $rowDetail=str_replace("[PRN_DIG_PRC]",$PrnDigPrc,$rowDetail);
                $rowDetail=str_replace("[DISC_AMT]",$DiscPct."/".$DiscAmt,$rowDetail);
                $rowDetail=str_replace("[FEE_MISC]",$FeeMisc,$rowDetail);
                $rowDetail=str_replace("[TOT_SUB]",$TotSub,$rowDetail);
            }
            echo $rowDetail;
			$typeCode[]	= $rowd['PRN_DIG_CD'];
		}
		$printCode 	= "'".implode("','",array_unique($typeCode))."'";
		$query="SELECT PRN_DIG_CD,PRN_DIG_EQP_COLR
			FROM CMP.PRN_DIG_TYP TYP
				LEFT OUTER JOIN CMP.PRN_DIG_EQP EQP ON TYP.PRN_DIG_EQP=EQP.PRN_DIG_EQP
		WHERE TYP.PRN_DIG_CD IN (".$printCode.")
		GROUP BY PRN_DIG_CD";
		$result=mysql_query($query);
		while($rowx=mysql_fetch_array($result)){
			$codeTypex[]	= $rowx['PRN_DIG_CD'];
			$colorTypex[]	= $rowx['PRN_DIG_EQP_COLR'];
		}
		$printType 		= join(" ",$codeTypex);
		if($printType != ''){
		$printTypeColor	= join(" ",$colorTypex);
		}
	?>
</table>
<input type="hidden" id="TOT_NET" value="<?php echo $TotNet; ?>" />
<input type="hidden" name="PRN_DIG_CD" value="<?php echo trim($printType); ?>" />
<input type="hidden" name="PRN_DIG_EQP_COLR" value="<?php echo trim($printTypeColor); ?>" />
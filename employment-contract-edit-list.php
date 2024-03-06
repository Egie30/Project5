<?php
	include "framework/database/connect-cloud.php";
	include "framework/security/default.php";

	$Security = getSecurity($_SESSION['userID'],"Remote");

	//echo 'aaa'.$Security;

	$PRSN_NBR = $_GET['PRSN_NBR'];

	$DelDet   = $_GET['DEL_D'];

	if($DelDet!="")
	{
		$query  = "DELETE FROM $CMP.EMPL_CNTRCT WHERE PRSN_NBR=".$PRSN_NBR." AND EMPL_CNTRCT_NBR=".$DelDet;
		$result = mysql_query($query,$cloud);
	}

?>
	<table id="mainTable" class="rowstyle-alt colstyle-alt no-arrow searchTable">
		<thead>
			<tr>
				<th class="listable" style="text-align:center;">Jenis Kontrak</th>
				<th class="listable" style="text-align:center;">Tanggal Mulai</th>
				<th class="listable" style="text-align:center;">Tanggal Selesai</th>
				<?php if($Security<=5) { ?>
				<th class="listable">
				<div class='listable-btn'>
				<span class='fa fa-plus listable-btn' onclick="if(document.getElementById('idkrywn').value == ''){parent.parent.document.getElementById('invoiceAdd').style.display='block';parent.parent.document.getElementById('fade').style.display='block';return;}pushFormIn('employment-contract-edit-detail.php?PRSN_NBR=<?php echo $PRSN_NBR; ?>');">
				</span>
				</div>
				</th>
				<?php }?>
			</tr>
		</thead>
		<tbody>

		<?php 
		$query 	= "SELECT CNTRCT.EMPL_CNTRCT_NBR AS EMPL_CNTRCT_NBR,
						  CNTRCT.PRSN_NBR AS PRSN_NBR,
						  CNTRCT_TYP.EMPL_CNTRCT_DESC AS EMPL_CNTRCT_DESC,
						  CNTRCT.BEG_DTE AS BEG_DTE,
						  CNTRCT.END_DTE AS END_DTE
					FROM $CMP.EMPL_CNTRCT CNTRCT
					LEFT JOIN $CMP.EMPL_CNTRCT_TYP CNTRCT_TYP ON CNTRCT_TYP.EMPL_CNTRCT_TYP = CNTRCT.EMPL_CNTRCT_TYP
					WHERE PRSN_NBR = '".$PRSN_NBR."' ORDER BY CNTRCT.BEG_DTE DESC";
				 // echo $query;
		$resultc  	= mysql_query($query,$cloud);
		$alt="";

		while($rowc = mysql_fetch_array($resultc))
		{
				$begdet   = strtotime($rowc['BEG_DTE']);
				$begdetz  = date("d M Y", $begdet);

				$enddet   = strtotime($rowc['END_DTE']);
				$enddetz  = date("d M Y", $enddet);

				echo "<tr $alt onclick=".chr(34)."pushFormIn('employment-contract-edit-detail.php?EMPL_CNTRCT_NBR=".$rowc['EMPL_CNTRCT_NBR']."&PRSN_NBR=".$PRSN_NBR."')".chr(34).">";
				echo "<td style = 'cursor:pointer;text-align:left'>".$rowc['EMPL_CNTRCT_DESC']."</td>";

				echo "<td style = 'cursor:pointer;text-align:center;'>".$begdetz."</td>";
				echo "<td style = 'cursor:pointer;text-align:center;'>".$enddetz."</td>";
				if($Security<=5) 
				{ 
				echo "<td style = 'cursor:pointer;text-align:center;'>
				<div class='listable-btn'>
				<span class='fa fa-trash listable-btn'  onclick=".chr(34)."event.cancelBubble=true;getContent('edit-list','employment-contract-edit-list.php?PRSN_NBR=".$PRSN_NBR."&DEL_D=".$rowc['EMPL_CNTRCT_NBR']."');".chr(34).">
				</span>
				</div>";
				echo "</td>";
				}
				echo "</tr>";
				if($alt == "") { $alt = "class='alt'"; } else { $alt = ""; }
		}
		?>
		</tbody>
	</table>



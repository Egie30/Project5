<?php
	include "framework/functions/default.php";
    include "framework/database/connect.php";
    include "framework/functions/crypt.php";

	$OrdSttId=$_GET['STT'];
    $PrsnNbr=$_GET['PRSN_NBR'];
    $CoNbr=$_GET['CO_NBR'];
    $Url=generateUrl($CoNbr,$CoNbrDef);
    $Orders=explode(chr(30),simple_crypt(file_get_contents('http://'.$Url.'/mobile/print-digital-branch-list-data.php?STT='.$OrdSttId.'&PRSN_NBR='.$PrsnNbr),'d'));
?>
<div class="navbar">
    <div class="navbar-inner">
        <div class="left sliding"><a href="#" class="back link color-nestor"><span class="fa fa-chevron-left"></span></a></div>
        <div class="center sliding">List</div>
        <div class="right"><a href="#" class="open-panel link icon-only color-nestor"><span class="fa fa-bars"></span></a></div>
    </div>
</div>
<div class="pages navbar-through">
    <div data-page="print-digital-list" class="page">
        <div class="page-content contacts-content">
            <div class="list-block media-list contacts-block">
                <ul>
                    <?php
                        foreach($Orders as $Order){
                            $OrderDetail=explode(chr(31),$Order);
                            echo "<li><a href='print-digital-branch-view.php?CO_NBR=".$CoNbr."&ORD_NBR=".$OrderDetail[0]."' class='item-link'>";
                            echo "<div class='item-inner item-content'>";
                            echo "<div class='item-title-row'>";
                            echo "<div class='item-title'>".$OrderDetail[0]."</div>";
                            echo "<div class='item-subtitle'>".parseDateTimeLiteralShort($OrderDetail[1])."</div>";
                            echo "</div>";
                 
                            //Traffic light control
                            $due=strtotime($OrderDetail[1]);
                            $OrdSttId=$OrderDetail[2];
                            if((strtotime("now")>$due)&&(($OrdSttId=="NE")||($OrdSttId=="RC")||($OrdSttId=="QU")||($OrdSttId=="PR")||($OrdSttId=="FN"))){
                                $dot="<div class='item-after'><span class='fa fa-circle' style='line-height:22px;color:#d92115'></span></div>";
                            }elseif((strtotime("now + ".$OrderDetail[4]." minute")>$due)&&(($OrdSttId=="NE")||($OrdSttId=="RC")||($OrdSttId=="QU")||($OrdSttId=="PR")||($OrdSttId=="FN"))){
                                $dot="<div class='item-after'><span class='fa fa-circle' style='line-height:22px;color:#fbad06'></span></div>";
                            }else{
                                $dot="";
                            }

                            echo "<div class='item-description' style='margin-left:-3px'>";
                            if(trim($OrderDetail[4]." ".$OrderDetail[5])==""){$name="Tunai";}else{$name=trim($$OrderDetail[4]." ".$OrderDetail[5]);}
                            if($OrderDetail[7]=='T'){
                                echo "<span class='fa fa-fw fa-star'></span>";
                            }				
                            if($$OrderDetail[8]!=""){
                                echo "<span class='fa fa-fw fa-comment'></span>";
                            }
                            if($OrderDetail[9]>0){
                                echo "<span class='fa fa-fw fa-truck' style='margin-left:-1px'></span>";
                            }
                            if($OrderDetail[10]>0){
                                echo "<span class='fa fa-fw fa-shopping-cart'></span>";
                            }
                            if($OrderDetail[11]>0){
                                echo "<span class='fa fa-fw fa-flag'></span>";
                            }
                            if($OrderDetail[12]>0){
                                echo "<span class='fa fa-fw fa-print'></span>";
                            }
                            echo "</div>";
                            echo "<div class='item-subtitle-row'>";
                            echo "<div class='item-subtitle color-nestor'>".$name."</div>";
                            echo "<div class='item-after' style='font-size:15px'>$dot</div>";
                            echo "</div>"; 
                            echo "<div class='item-text' >".htmlentities($OrderDetail[13],ENT_QUOTES)."</div>";
                            echo "<div class='item-subtitle-row'>";
                            echo "<div class='item-description' style='color:#000000'>".parseDateShort($OrderDetail[14])."&nbsp;";
                            echo "<span style='font-weight:700' style='color:#000000'>".$OrderDetail[15]."</span></div>";
                            echo "<div class='item-after' style='font-size:15px;color:#000000'>";
                            if($OrderDetail[16]==0){
                                echo "<span class='fa fa-fw fa-circle' style='line-height:22px;color:#3464bc'></span>";
                            }elseif($OrderDetail[17]==$OrderDetail[16]){
                                echo "<span class='fa fa-fw fa-circle-o' style='line-height:22px;color:#3464bc'></span>";
                            }else{
                                echo "<span class='fa fa-fw fa-dot-circle-o' style='line-height:22px;color:#3464bc'></span>";
                            }
                            echo "&nbsp;Rp. ".number_format($OrderDetail[17],0,',','.')."</div>";
                            echo "</div>";
                            echo "</div></li></a>";
                        }
                    ?>
                </ul>
            </div>
        </div>
    </div>
</div>

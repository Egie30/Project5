<?php
	include "framework/functions/default.php";
    include "framework/database/connect.php";
    include "framework/functions/crypt.php";
    $OrdNbr=$_GET['ORD_NBR'];
    $CoNbr=$_GET['CO_NBR'];
    $Url=generateUrl($CoNbr,$CoNbrDef);
    $Details=explode(chr(30),simple_crypt(file_get_contents('http://'.$Url.'/mobile/print-digital-branch-view-data.php?ORD_NBR='.$OrdNbr),'d'));

    $Header=explode(chr(31),$Details[0]);
    unset($Details[0]);
?>
<div class="navbar">
    <div class="navbar-inner">
        <div class="left sliding"><a href="#" class="back link color-nestor"><span class="fa fa-chevron-left"></span></a></div>
        <div class="center sliding">Nota</div>
        <div class="right"><a href="#" class="open-panel link icon-only color-nestor"><span class="fa fa-bars"></span></a></div>
    </div>
</div>
<div class="pages navbar-through">
    <div data-page="media-lists timeline-vertical" class="page">
        <div class="page-content contacts-content">
            <div class="list-block media-list contacts-block">
                <ul>
                    <li>
                        <div class="item-inner item-content">
                            <div class="item-title"><?php echo $OrdNbr; ?></div>
                            <div class="item-value color-nestor">
                                <?php echo $Header[0]; ?>
                            </div>
                            <div class="item-value subtitle">
                                <?php echo $Header[1]; ?>
                            </div>
                            <div class="item-title"><?php echo $Header[2]; ?></div>
                        </div></li>
                </ul>
            </div>
            <div class="content-block-title">Aktifitas</div>
            <div class="timeline">
                <?php
                    $prevDate='';
                    $ready=false;
                    foreach($Details as $Data){
                        $detail=explode(chr(31),$Data);
                        echo "<div class='timeline-item'>";
                        echo "<div class='timeline-item-date'>";
                        if($prevDate!=parseDateOnly($detail[0])){
                            echo parseDateOnly($detail[0])." <small>".strtoupper(parseMonthName($detail[0]))."</small>";
                        }
                        echo "</div>";
                        echo "<div class='timeline-item-divider'></div>";
                        echo "<div class='timeline-item-content'>";
                        echo "<div class='timeline-item-inner'>";
                        echo "<div class='timeline-item-time'>".parseTimeShort($detail[0])."</div>";
                        echo "<div class='timeline-item-title ";
                        //if(($detail['title']=='Deadline')&&(!$ready)){
                        //    echo " color-04";
                        //}
                        echo "'>".$detail[1];
                        if($detail[1]=='Deadline'){
                            echo " ".$Header[3];
                        }
                        echo "</div>";
                        echo "<div class='timeline-item-text'>".$detail[2]."</div>";
                        echo "<div class='timeline-item-time'>".$detail[3]."</div>";
                        echo "</div>";
                        echo "</div>";
                        echo "</div>";
                        $prevDate=parseDateOnly($detail['timestamp']);
                        if($detail['1']=='Jadi'){$ready=true;}else{$ready=false;}
                    }
                ?>
            </div>
        </div>
    </div>
</div>

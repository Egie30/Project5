<?php
	include "framework/functions/default.php";
    include "framework/database/connect.php";
    include "framework/functions/crypt.php";

    $CoNbr=$_GET['CO_NBR'];
    $Url=generateUrl($CoNbr,$CoNbrDef);
    $Stats=explode(';',simple_crypt(file_get_contents('http://'.$Url.'/mobile/print-digital-branch-status-data.php'),'d'));
?>

<div class="navbar">
    <div class="navbar-inner">
        <div class="left sliding"><a href="#" class="back link color-nestor"><span class="fa fa-chevron-left"></span></a></div>
        <div class="center sliding">Digital Printing</div>
        <div class="right"><a href="#" class="open-panel link icon-only color-nestor"><span class="fa fa-bars"></span></a></div>
    </div>
</div>
<div class="pages">
    <div data-page="list-view" class="page">
        <div class="page-content">
            <div class="list-block contacts-block">
                <ul>
                    <li><a href="print-digital-branch-list.php?STT=ACT&CO_NBR=<?php echo $CoNbr; ?>" class="item-link item-content">
                        <div class="item-media"><span class='fa fa-fw fa-folder-open'></span></div>
                        <div class="item-inner"> 
                            <div class="item-title">Aktif</div>
                        </div></a></li>
                    <!--
                    <li><a href="#" class="item-link item-content">
                        <div class="item-media"><span class='fa fa-fw fa-inbox'></span></div>
                        <div class="item-inner"> 
                            <div class="item-title">Inbox</div>
                        </div></a></li>
                    -->
                    <?php
                        foreach($Stats as $Stat){
                            $StatDetail=explode(",",$Stat);
                            echo "<li><a href='print-digital-branch-list.php?STT=".$StatDetail[0]."&CO_NBR=".$CoNbr."' class='item-link item-content'>";
                            echo "<div class='item-media'><span class='fa fa-fw fa-".$StatDetail[1]."'></span></div>";
                            echo "<div class='item-inner'>";
                            echo "<div class='item-title'>".$StatDetail[2]."</div>";
                            echo "<div class='item-after'> <span class='badge'>".$StatDetail[3]."</span></div>";
                            echo "</div></a></li>";
                        }
                    ?>
                    <li><a href="#" class="item-link item-content">
                        <div class="item-media"><span class='fa fa-fw fa-check'></span></div>
                        <div class="item-inner"> 
                            <div class="item-title">Selesai</div>
                        </div></a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php
    function returnIcon($status)
    {
        switch($status) {
        case "NE":
            return "file-o";
        break;
        case "RC":
            return "picture-o";
        break;
        case "LT":
            return "object-group";
        break;
        case "PF":
            return "thumbs-up";
        break;
        case "QU":
            return "hourglass-half";
        break;
        case "PR":
            return "print";
        break;
        case "FN":
            return "scissors";
        break;
        case "RD":
            return "align-justify";
        break;
        case "DL":
            return "truck";
        break;
        case "NS":
            return "flag";
        break;
        case "CP":
            return "check";
        break;
        default:
            return "circle-thin";
        }
    }
?>

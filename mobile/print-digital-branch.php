<?php
	include "framework/functions/default.php";
    include "framework/database/connect.php";
    $Cos=$_GET['CO_NBRS'];
    $query="SELECT CO_NBR,NAME FROM CMP.COMPANY WHERE CO_NBR IN ($Cos)";
    $result=mysql_query($query);
?>
<div class="navbar">
    <div class="navbar-inner">
        <div class="left sliding"><a href="#" class="back link color-nestor"><span class="fa fa-chevron-left"></span></a></div>
        <div class="center sliding">Cabang</div>
        <div class="right"><a href="#" class="open-panel link icon-only color-nestor"><span class="fa fa-bars"></span></a></div>
    </div>
</div>
<div class="pages navbar-through">
    <div data-page="list-view" class="page">
        <div class="page-content">
            <div class="list-block contacts-block">
                <ul>
                    <?php
                        while($row=mysql_fetch_array($result)){
                            echo "<li><a href='print-digital-branch-status.php?CO_NBR=".$row['CO_NBR']."' class='item-link item-content'>";
                            echo "<div class='item-inner'>";
                            echo "<div class='item-title'>".$row['NAME']."</div>";
                            echo "</div></a></li>";
                        }
                    ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php
    include "framework/database/connect.php";
    include "framework/functions/default.php";
    $query="SELECT TYP.PRN_DIG_TYP,PRN_DIG_TYP_PAR,PRN_DIG_DESC,PRN_DIG_EQP_DESC,INV_NBR,PRN_DIG_PRC,PROMO_DISC_AMT,PLAN_DESC
					  FROM CMP.PRN_DIG_TYP TYP INNER JOIN 
					  CMP.PRN_DIG_VOL_PLAN_TYP PLN ON TYP.PLAN_TYP=PLN.PLAN_TYP INNER JOIN
					  CMP.PRN_DIG_EQP EQP ON TYP.PRN_DIG_EQP=EQP.PRN_DIG_EQP LEFT OUTER JOIN
					  (SELECT PRN_DIG_TYP,SUM(PROMO_DISC_AMT) AS PROMO_DISC_AMT FROM CMP.PRN_DIG_PROMO WHERE BEG_DT<=CURRENT_DATE AND END_DT>=CURRENT_DATE GROUP BY PRN_DIG_TYP) PRO ON TYP.PRN_DIG_TYP=PRO.PRN_DIG_TYP
					  WHERE DEL_NBR=0
					  ORDER BY 3";
    $result=mysql_query($query);
    $row=mysql_fetch_array($result);
?>
<div class="navbar">
    <div class="navbar-inner">
        <div class="left sliding"><a href="#" class="back link color-nestor"><span class="fa fa-chevron-left"></span></a></div>
        <div class="center sliding">Daftar Harga</div>
        <div class="right"><a href="#" class="open-panel link icon-only color-nestor"><span class="fa fa-bars"></span></a></div>
    </div>
</div>
<div class="pages navbar-through">
    <div data-page="searchbar" class="page">
        <form data-search-list=".search-here" data-search-in=".item-title" class="searchbar searchbar-init">
            <div class="searchbar-input">
                <input type="search" placeholder="Search"/><a href="#" class="searchbar-clear"></a>
            </div><a href="#" class="searchbar-cancel">Cancel</a>
        </form>
        <div class="searchbar-overlay"></div>    
        <div class="page-content contacts-content">
            <div class="list-block searchbar-not-found">
                <ul>
                    <li class="item-content">
                        <div class="item-inner">
                            <div class="item-title">Nothing found</div>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="list-block search-here searchbar-found contacts-block">
                <div class="list-group">
                <ul>
                    <?php
                        while($row=mysql_fetch_array($result)){
                            echo "<li>";
                            echo "<div class='item-content'>";
                            echo "<div class='item-inner'>";
                            echo "<div class='item-title' style='font-size:15px'>".$row['PRN_DIG_DESC']."</div>";
                            echo "<div class='item-after item-subtitle' style='color:#000000'>".number_format($row['PRN_DIG_PRC'],0,',','.')."</div>";
                            echo "</div>";
                            echo "</div></li>";
                        }
                    ?>
                </ul>
                </div>
            </div>
        </div>
    </div>
</div>

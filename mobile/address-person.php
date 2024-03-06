<?php
	include "framework/functions/default.php";
    include "framework/database/connect.php";
    $query="SELECT PRSN_NBR,PPL.NAME,CONCAT(PPL.ADDRESS,', ',CITY_NM) AS ADDR,PPL.PHONE,COM.NAME AS COMPANY
            FROM CMP.PEOPLE PPL
            LEFT OUTER JOIN CMP.CITY CTY ON PPL.CITY_ID=CTY.CITY_ID
            LEFT OUTER JOIN CMP.COMPANY COM ON PPL.CO_NBR=COM.CO_NBR
            WHERE TERM_DTE IS NULL AND PPL.DEL_NBR=0 AND PPL.NAME<>''
            ORDER BY PPL.NAME ASC";
    $result=mysql_query($query)
?>
<div class="navbar">
    <div class="navbar-inner">
        <div class="left sliding"><a href="#" class="back link color-nestor"><span class="fa fa-chevron-left"></span></a></div>
        <div class="center sliding">Contacts</div>
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
                            $groupTitle="";
                            while($row=mysql_fetch_array($result)){
                                $groupTitleNew=strtoupper(substr($row['NAME'],0,1));
                                if($groupTitle!=$groupTitleNew){
                                    $groupTitle=$groupTitleNew;
                                    echo "<li class='list-group-title'>".$groupTitle."</li>";
                                }
                                echo "<li><a href='address-person-view.php?PRSN_NBR=".$row["PRSN_NBR"]."' class='item-link'>";
                                echo "<div class='item-content'>";
                                echo "<div class='item-inner'>";
                                echo "<div class='item-title'>".$row['NAME']."</div>";
                                echo "</div>";
                                echo "</div>";
                                echo "</a></li>";
                            }
                        ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

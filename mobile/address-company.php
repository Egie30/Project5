<?php
	include "framework/functions/default.php";
    include "framework/database/connect.php";
    $query="SELECT COM.CO_NBR,COM.NAME,CONCAT(COM.ADDRESS,', ',CITY_NM) AS ADDR,COM.PHONE,COM.SUP_F,
					CUST.NBR AS TOP50,
                    (SELECT NAME FROM COMPANY C WHERE C.CO_NBR = COM.3RD_PTY_NBR) AS VIA_3RD_PTY_NBR,
					DATE(COM.LAST_ACT_TS) AS LAST_ACT_TS,
					PPL.NAME AS PPL_NAME
					FROM CMP.COMPANY COM
					INNER JOIN CMP.CITY CTY ON COM.CITY_ID=CTY.CITY_ID
						LEFT OUTER JOIN CMP.PEOPLE PPL ON COM.UPD_NBR = PPL.PRSN_NBR
						LEFT OUTER JOIN 
						(
							SELECT NBR, REV_TOT FROM CDW.PRN_DIG_TOP_CUST 
							WHERE TYP = 'CO_NBR' 
							ORDER BY REV_TOT DESC LIMIT 50
						) CUST ON CUST.NBR = COM.CO_NBR
					ORDER BY 2";
    $result=mysql_query($query)
?>
<div class="navbar">
    <div class="navbar-inner">
        <div class="left sliding"><a href="#" class="back link color-nestor"><span class="fa fa-chevron-left"></span></a></div>
        <div class="center sliding">Accounts</div>
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
                                echo "<li><a href='address-company-view.php?CO_NBR=".$row["CO_NBR"]."' class='item-link'>";
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

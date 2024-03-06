<?php
	include "framework/functions/default.php";
    include "framework/database/connect.php";
	$CoNbr=$_GET['CO_NBR'];
    $query="SELECT COM.CO_NBR,COM.NAME,COM.ADDRESS,CITY_NM,COM.ZIP,COM.PHONE,COM.SUP_F,COM.EMAIL,
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
                    WHERE COM.CO_NBR=$CoNbr
					ORDER BY 2";
    $result=mysql_query($query);
    $row=mysql_fetch_array($result);
?>
<div class="navbar">
    <div class="navbar-inner">
        <div class="left sliding"><a href="#" class="back link color-nestor"><span class="fa fa-chevron-left"></span></a></div>
        <div class="center sliding">Contacts</div>
        <div class="right"><a href="#" class="open-panel link icon-only color-nestor"><span class="fa fa-bars"></span></a></div>
    </div>
</div>
<div class="pages navbar-through">
    <div data-page="media-lists" class="page">
        <div class="page-content contacts-content">
            <div class="list-block media-list contacts-block">
                <ul>
                    <li>
                        <div class="item-inner item-content">
                            <div class="item-title-row">
                                <div class="item-title"><?php echo $row['NAME']; ?></div>
                            </div>
                            <div class="item-subtitle"><?php echo $row['CO_NBR']; ?></div>
                        </div></li>
                    <li>
                        <div class="item-inner item-content">
                            <div class="item-title-row">
                                <div class="item-label">Telepon</div>
                            </div>
                            <div class="item-value">
                                <?php
                                    $phones=explode(",",str_replace(";",",",str_replace("/",",",$row['PHONE'])));
                                        foreach($phones as $phone){
                                            echo "<a href='tel:".$phone."' class='external'>".$phone."</a></br>";
                                        }
                                ?>
                            </div>
                        </div></li>
                    <li>
                        <div class="item-inner item-content">
                            <div class="item-title-row">
                                <div class="item-label">E-Mail</div>
                            </div>
                            <div class="item-value">
                                <a href="mailto:<?php echo $row['EMAIL']; ?>"><?php echo $row['EMAIL']; ?></a>
                            </div>
                        </div></li>
                    <li>
                        <div class="item-inner item-content">
                            <div class="item-title-row">
                                <div class="item-label">Alamat</div>
                            </div>
                            <div class="item-value">
                                <?php echo $row['ADDRESS']; ?><br>
                                <?php echo $row['CITY_NM']; ?><br>
                                <?php echo $row['ZIP']; ?><br>
                            </div>
                        </div></li>
                </ul>
            </div>
            
            <div class="list-block contacts-block">
                <ul>
                    <li class='list-group-title'>Peer</li>
                    <?php
                        $query="SELECT PRSN_NBR,PPL.NAME,CONCAT(PPL.ADDRESS,', ',CITY_NM) AS ADDR,PPL.PHONE,COM.NAME AS COMPANY
                                FROM CMP.PEOPLE PPL
                                LEFT OUTER JOIN CMP.CITY CTY ON PPL.CITY_ID=CTY.CITY_ID
                                LEFT OUTER JOIN CMP.COMPANY COM ON PPL.CO_NBR=COM.CO_NBR
                                WHERE TERM_DTE IS NULL AND PPL.DEL_NBR=0 AND PPL.NAME<>'' AND PPL.CO_NBR=$CoNbr
                                ORDER BY PPL.NAME ASC";
                        $result=mysql_query($query);
                        while($row=mysql_fetch_array($result)){
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
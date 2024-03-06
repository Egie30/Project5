<?php
	include "framework/functions/default.php";
    include "framework/database/connect.php";
	$PrsnNbr=$_GET['PRSN_NBR'];
    $query="SELECT PRSN_NBR,PRSN_ID,PPL.NAME,ALIAS,PPL.KEYWORDS,TTL,MBR_NBR,DOB,PPL.ADDRESS,PPL.CITY_ID,COM.NAME AS CO_NAME,CTY.CITY_NM,PPL.ZIP,PPL.PHONE,PPL.FAX,PPL.EMAIL,HIRE_DTE,PAY_TYP,PAY_BASE,PAY_ADD,PAY_OT,PAY_MISC,
			DED_DEF,PWD,POS_TYP,MGR_NBR,DRV_LIC,RSN_CRD,GNDR,TERM_DTE,BONUS,PPL.CO_NBR,PPL.BRKR_PLAN_TYP,PPL.BNK_CO_NBR,
			PPL.BNK_ACCT_NBR,PPL.CAP_LIM,PPL.CAP_MULT,BON_MULT,PPL.UPD_TS,PPL.UPD_NBR,COM.NAME AS CO_NAME,COM.ADDRESS AS CO_ADDRESS,
            COM.PHONE AS CO_PHONE,COM.ZIP AS CO_ZIP,CT1.CITY_NM AS CO_CITY_NM
			FROM CMP.PEOPLE PPL
			LEFT OUTER JOIN CMP.CITY CTY ON PPL.CITY_ID=CTY.CITY_ID
            LEFT OUTER JOIN CMP.COMPANY COM ON PPL.CO_NBR=COM.CO_NBR
			LEFT OUTER JOIN CMP.CITY CT1 ON COM.CITY_ID=CT1.CITY_ID
			WHERE PRSN_NBR=".$PrsnNbr;
    $result=mysql_query($query);
    $row=mysql_fetch_array($result);
?>
<div class="navbar">
  <div class="navbar-inner">
    <div class="left sliding"><a href="index.html" class="back link"><span class="fa fa-chevron-left"></span><span>Back</span></a></div>
    <div class="right"><a href="#" class="open-panel link icon-only"><span class="fa fa-bars"></span></a></div>
  </div>
</div>
<div class="pages navbar-through">
  <div data-page="media-lists" class="page">
    <div class="page-content contacts-content">
      <div class="list-block media-list contacts-block">
        <ul>
          <li>
              <div class="item-inner item-content">
                <img style='width:108px;height:108px;border-radius:50% 50% 50% 50%;margin-bottom:5px' src="../address-person/showimg.php?PRSN_NBR=<?php echo $row['PRSN_NBR']; ?>">
                <div class="item-title-row">
                  <div class="item-title"><?php echo $row['NAME']; ?></div>
                </div>
                <div class="item-subtitle"><?php echo $row['PRSN_NBR']; ?></div>
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
                   <?php echo $row['ADDRESS']; ?></br>
                   <?php echo $row['CITY_NM']; ?></br>
                   <?php echo $row['ZIP']; ?></br>
                </div>
              </div></li>
          <li>
              <div class="item-inner item-content">
                <div class="item-title-row">
                  <div class="item-label">Perusahaan</div>
                </div>
                <div class="item-value">
                   <?php echo $row['CO_NAME']; ?></br>
                   <?php echo $row['CO_ADDRESS']; ?></br>
                   <?php echo $row['CO_CITY_NM']; ?></br>
                   <?php echo $row['CO_ZIP']; ?></br>
                </div>
              </div></li>
          <li>
              <div class="item-inner item-content">
                <div class="item-title-row">
                  <div class="item-label">Telepon Perusahaan</div>
                </div>
                <div class="item-value">
                   <?php
                      $phones=explode(",",str_replace(";",",",str_replace("/",",",$row['CO_PHONE'])));
                      foreach($phones as $phone){
                          echo "<a href='tel:".$phone."' class='external'>".$phone."</a></br>";
                      }
                   ?>
                </div>
              </div></li>
        </ul>
      </div>
    </div>
  </div>
</div>
<?php
    $display=$_GET['DISP'];
	
	include "framework/database/connect.php";

    //NALB Redirect
    if(substr($_SERVER['REMOTE_ADDR'],0,8)==substr($_SERVER['SERVER_ADDR'],0,8)){
    if($_SERVER['SERVER_ADDR']!=$OLTA){
        header('Location:http://'.$_SERVER['SERVER_ADDR'].'/index.html');
        exit;
    }
}
    include "framework/alert/alert.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";

    //Mobile detection
    include_once 'framework/mobile/user_agent.php';
    //$ua = new UserAgent();

    //if($ua->is_mobile()){
    //    header("Location:mobile/");
    //    exit;
    //}

	if($_SESSION['userID']==""){
		header('Location:login.php');
		exit;
	}else{
		$userID=$_SESSION['userID'];
		$query="SELECT NAME,PRSN_NBR FROM CMP.PEOPLE PPL INNER JOIN CMP.POS_TYP POS ON PPL.POS_TYP=POS.POS_TYP WHERE PRSN_ID='".$userID."'";
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		$name=$row['NAME'];
		$prsnNbr=$row['PRSN_NBR'];
	}
	$Security	= getSecurity($_SESSION['userID'],"Finance");
	$upperSec	= getSecurity($_SESSION['userID'],"Executive");
	$SecurityAct= getSecurity($_SESSION['userID'],"Accounting");
	//Get Company
	$result=mysql_query('SELECT * FROM NST.PARAM_LOC;');
	$DefCo=mysql_fetch_array($result);
	$DefCoNbr=$DefCo['CO_NBR_DEF'];
	$query="SELECT NAME FROM CMP.COMPANY WHERE CO_NBR=".$DefCoNbr;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	$DefCoName=$row['NAME'];
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<title>Nestor</title>

<link rel="shortcut icon" href="favicon.ico" />

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<meta name="viewport" content="width=1024" />

<script src="framework/pace/pace.min.js"></script>

<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" type="text/css" media="screen" href="framework/alert/alert.css" />
<link rel="stylesheet" href="css/font-awesome-4.7.0/css/font-awesome.min.css">

<link rel="stylesheet" href="framework/jgrowl/jquery.jgrowl.min.css" />
<script src="framework/database/jquery.min.js"></script>
<script src="framework/jgrowl/jquery.jgrowl.min.js"></script>

<script type="text/javascript" src="framework/functions/default.js"></script>

<script type="text/javascript">
    $(document).ready(function(){
        window.msgGrowl=function(message){
            $.jGrowl(message, {
                        position: 'bottom-right'
                    });
        }
        //msgGrowl("The quick brown fox jumps over the lazy dog.");
    });
</script>
<style>
    div.leftmenu-width{transition:.25s;width:200px}
    div.leftmenu-items{transition:.25s;width:200px;left:0px}
    @media only screen and (max-width: 1279px){
        div.leftmenu-width{
            width:0px;transition:.25s;
        }
        div.leftmenu-items{
            left:-200px;transition:.25s;
            width:200px;transition:.25s
        }
    }
    @media only screen and (min-width: 1280px){
        span.leftmenu-icon{display:none}
        div.leftmenu-items{
            left:0px;transition:.25s;
            width:200px;transition:.25s
        }
    }
	
	span.topmenu.active {
		color: #3464bc;
		opacity: 1;
	}
	span.topmenu.active:hover {
		opacity: .7;
	}
</style>
<script language="javascript">
    window.onresize=function(){
        if($(window).width()>=1280){
            document.getElementById('leftmenu-items').style.left='0px';
            document.getElementById('content-container').style.width='100%';
            newWidth=document.getElementById('content-container').clientWidth-210;
            document.getElementById('content-container').style.width=newWidth+'px';
        }else{
            document.getElementById('leftmenu-items').style.left='-200px';
            document.getElementById('content-container').style.width='100%';
            newWidth=document.getElementById('content-container').clientWidth-20;
            document.getElementById('content-container').style.width=newWidth+'px';
        }
    }
</script>
</head>

<body class="index">
<div id="site-wrapper">
<div id="site-canvas">

<!-- Shadow box background -->
<div id='fade' class='black_overlay'></div>
<div id='menu' class='clear_overlay' onclick="if(document.getElementById('leftmenu-items').style.left=='0px'){document.getElementById('leftmenu-items').style.left='-200px';}this.style.display='none'"></div>

<div id="site-form">
    <iframe id="siteFormContent" src="about:blank" style="width:calc(100% - 10px);height:calc(100%);"></iframe>
</div>

    
<!-- Dialog boxes -->
<?php createStop("offline","Offline","Server tidak tersedia untuk operasi data.  Silakan mencoba beberapa saat lagi."); ?>

<?php createAlert("addressDelete","Menghapus Data","Data akan dihapus. Apakah operasi akan diteruskan?"); ?>
<?php createStop("addressBlank","Nama Kosong","Kotak nama tidak boleh kosong. Pastikan kotak nama terisi sebelum menyimpan data."); ?>

<?php createAlert("payrollDelete","Menghapus Data","Data akan dihapus. Apakah operasi akan diteruskan?"); ?>

<?php createAlert("invoiceDelete","Menghapus Order","Apabila menghapus order, semua data didalam order juga akan terhapus. Apakah operasi akan diteruskan?"); ?>
<?php createStop("invoiceAdd","Mengisi Item","Untuk pertama kali mengisi item order, simpan kerangka nota/order dahulu."); ?>

<?php createAlert("inventoryActivityDelete","Menghapus Data","Data akan dihapus. Apakah operasi akan diteruskan?"); ?>

<?php createAlert("inventoryListDelete","Menghapus Data","Data akan dihapus. Apakah operasi akan diteruskan?"); ?>

<?php createAlert("printDigitalTypeDelete","Menghapus Data","Data akan dihapus. Apakah operasi akan diteruskan?"); ?>

<?php createAlert("retailTypeDelete","Menghapus Data","Data akan dihapus. Apakah operasi akan diteruskan?"); ?>

<?php createAlert("expenseDelete","Menghapus Data","Data akan dihapus. Apakah operasi akan diteruskan?"); ?>
<?php createStop("expenseOverLimit","Over Limit","Jumlah pengeluaran diatas limit.  Turunkan pengeluaran atau pengeluarkan dibuat oleh atasan."); ?>

<?php createAlert("registerDelete","Menghapus Transaksi","Transaksi ini akan dihapus. Apakah operasi akan diteruskan?"); ?>
<?php createStop("registerActive","Cash Register Aktif","Cash register masih aktif.  Selesaikan transaksi dahulu sebelum dapat membuka transaksi lagi."); ?>

<?php createAlert("transportDelete","Menghapus Pengiriman","Surat jalan ini akan dihapus. Apakah operasi akan diteruskan?"); ?>
<?php createAlert("transportCreate","Membuat Surat Jalan","Sebuah surat jalan akan dibuat dari daftar dibawah ini. Apakah operasi akan diteruskan?"); ?>
<?php createStop("transportBlank","Pilihan Kosong","Tidak ada item yang dipilih untuk dikirim."); ?>
<?php createAlert("convertCreate","Membuat Nota Pembelian","Sebuah nota pembelian akan dibuat dari daftar dibawah ini. Apakah operasi akan diteruskan?"); ?>
<?php createStop("convertBlank","Pilihan Kosong","Tidak ada item yang dipilih untuk dipindahkan ke pembelian."); ?>
<?php createStop("securityLog", "Counter Log", "Silakan gunakan tombol tambah untuk menambah log malam. Edit hanya dilakukan oleh Supervisor."); ?>

<?php createAlert("proformaCreate","Membuat Nota Printing","Sebuah nota pembelian akan dibuat dari daftar dibawah ini. Nota performa hanya bisa diduplikat satu kali. Apakah operasi akan diteruskan?"); ?>
<?php createStop("proformaBlank","Pilihan Kosong","Tidak ada item yang dipilih untuk dipindahkan ke Nota Printing."); ?>
<?php createStop("proformaCheck","Duplikat Nota","Proforma ini sudah dipindahkan ke nota asli. Duplikat tidak diperbolehkan"); ?>
<?php createAlert("sliderDelete","Menghapus Slider","Slider akan dihapus. Apakah operasi akan diteruskan?"); ?>
<?php createAlert("catDelete","Menghapus Data","Data akan dihapus. Apakah operasi akan diteruskan?"); ?>
<?php createAlert("catSubDelete","Menghapus Data","Data akan dihapus. Apakah operasi akan diteruskan?"); ?>
<?php createAlert("catDiscDelete","Menghapus Data","Data akan dihapus. Apakah operasi akan diteruskan?"); ?>
<?php createAlert("catShelfDelete","Menghapus Data","Data akan dihapus. Apakah operasi akan diteruskan?"); ?>
<?php createAlert("invAudDelete","Menghapus Data","Data akan dihapus. Apakah operasi akan diteruskan?"); ?>
<?php createAlert("invMoveDelete","Menghapus Data","Data akan dihapus. Apakah operasi akan diteruskan?"); ?>
<?php createAlert("syncConfirm", "Sinkronisasi Data", "Data akan disinkronkan. Apakah operasi akan diteruskan?"); ?>
<?php createStop("jumBlank","Jumlah Kosong","Kotak jumlah tidak boleh kosong. Pastikan kotak jumlah terisi sebelum menyimpan data."); ?>
<?php createAlert("travelApprovalNo", "Membatalkan Perjalanan", "Data akan dibatalkan. Apakah operasi akan diteruskan?"); ?>
<?php createAlert("travelApprovalYes", "Menyetujui Perjalanan", "Data akan disetujui. Apakah operasi akan diteruskan?"); ?>
<?php createAlert("ApprovalNo", "Disapprove", "Data akan dibatalkan. Apakah operasi akan diteruskan?"); ?>
<?php createAlert("ApprovalYes", "Approved", "Data akan disetujui. Apakah operasi akan diteruskan?"); ?>
<?php createAlert("DeleteData","Menghapus Data","Data akan dihapus. Apakah operasi akan diteruskan?"); ?>
<?php createStop("MaxCuti", "Maksimal Entry", "Data cuti sudah memenuhi batas maksimal."); ?>
<?php createAlert("absence","Konversi Cuti","Karyawan tersebut memiliki hari tidak masuk yang tidak terdapat leave of absence dan di time off. Apakah Anda Setuju jika hari tersebut dijadikan time off?"); ?>

<?php createAlert("recordDelete","Menghapus Data","Data akan dihapus. Apakah operasi akan diteruskan?"); ?>

<!-- Popup boxes -->
<link rel="stylesheet" href="framework/popup/popup.css" type="text/css" />

<div id="printDigitalReason" class="popup_retail_stock_barcode_white_content">
	<iframe id="printDigitalReasonContent" src="about:blank" style="width:300px;height:180px;"></iframe>
</div>

<div id="printDigitalPopupEdit" class="popup_digital_print_white_content">
	<iframe id="printDigitalPopupEditContent" src="about:blank" style="width:500px;height:580px;"></iframe>
</div>

<div id="printDigitalPopupJournal" class="popup_digital_print_journal_white_content">
	<iframe id="printDigitalPopupJournalContent" src="about:blank" style="overflow:hidden;width:400px;height:200px;"></iframe>
</div>

<div id="printDigitalPopupBarcode" class="popup_digital_print_barcode_white_content">
	<iframe id="printDigitalPopupBarcodeContent" src="about:blank" style="overflow:hidden;width:250px;height:240px;"></iframe>
</div>
<div id="retailStockBarcodeWhite" class="popup_retail_stock_barcode_white_content">
	<iframe id="retailStockBarcodeWhiteContent" src="about:blank" style="overflow:hidden;width:300px;height:180px;"></iframe>
</div>
<table class="main">
    <?php
        if($_SERVER['HTTP_USER_AGENT']=="nestor-macos"){
            echo "<tr style='background-color:#22272b;-webkit-app-region:drag;-webkit-user-select:none;'><td style='height:18px;' colspan='2'>";
            echo "</td></tr>";
        }
    ?>
	<tr class="topmenu">
        <td class="topmenu" colspan="2">
            <p class="top-left">
                <span class="fa-stack fa-1x topmenu leftmenu-icon" onclick="if(document.getElementById('leftmenu-items').style.left=='0px'){document.getElementById('leftmenu-items').style.left='-200px';}else{document.getElementById('leftmenu-items').style.left='0px';document.getElementById('menu').style.display='block'}">
                    <span class="fa fa-bars fa-stack-2x topmenuicon"></span>
                </span>
                <img src="img/nestor-logo-white.svg" style='width:83px;height:42px;border:0px;margin-top:-42px;vertical-align:-20px;padding-left:0px;padding-right:20px;'>
                <span class="fa-stack fa-1x topmenusel" onclick="changeUrl('leftmenu','home-lm.php');changeUrl('content','home.php');selTopMenu(this);">
                    <span class="fa fa-home fa-stack-2x topmenuicon"></span>
                </span>
				
                <?php if (!in_array($_SESSION['personNBR'], array("3681","3817"))){ ?>
				<span class="fa-stack fa-1x topmenu" onclick="changeUrl('leftmenu','address-lm.php');changeUrl('content','address-person.php');selTopMenu(this);">
					<span class="fa fa-building-o fa-stack-2x topmenuicon"></span>
				</span>
				<?php }else{ ?>
				<span class="fa-stack fa-1x topmenu" onclick="changeUrl('leftmenu','address-lm.php');changeUrl('content','lead-management-tripane.php');selTopMenu(this);">
					<span class="fa fa-building-o fa-stack-2x topmenuicon"></span>
				</span>
				<?php } ?>
				
                <span class="fa-stack fa-1x topmenu" onclick="changeUrl('leftmenu','payroll-lm.php');changeUrl('content','payroll-orgchart.php');selTopMenu(this);">
                    <span class="fa fa-users fa-stack-2x topmenuicon"></span>
                </span>
                <?php if($Security<=2){ ?>
                <span class="fa-stack fa-1x topmenu" onclick="changeUrl('leftmenu','finance-lm.php');changeUrl('content','cash-register-report.php');selTopMenu(this);">
                    <span class="fa fa-money fa-stack-2x topmenuicon"></span>
                </span>
            <?php } ?>
                <span class="fa-stack fa-1x topmenu" onclick="changeUrl('leftmenu','calendar-lm.php');changeUrl('content','calendar-list.php');selTopMenu(this);">
                    <span class="fa fa-calendar fa-stack-2x topmenuicon"></span>
                </span>
                <span class="fa-stack fa-1x topmenu" onclick="changeUrl('leftmenu','print-digital-lm.php');changeUrl('content','print-digital-tripane.php?STT=ACT');selTopMenu(this);">
                    <span class="fa fa-print fa-stack-2x topmenuicon"></span>
                </span>
                <span id='transport' class="fa-stack fa-1x topmenu" onclick="changeUrl('leftmenu','transport-lm.php');changeUrl('content','transport-tripane.php?STT=DLO');selTopMenu(this);">
                    <span class="fa fa-truck fa-stack-2x topmenuicon"></span>
                </span>
				<span class="fa-stack fa-1x topmenu" onclick="changeUrl('leftmenu','retail-order-lm.php');changeUrl('content','retail-order-tripane.php?IVC_TYP=SL&STT=ACT');selTopMenu(this);">
                    <span class="fa fa-cube fa-stack-2x topmenuicon"></span>
                </span>
                <span class="fa-stack fa-1x topmenu" onclick="changeUrl('leftmenu','inventory-lm.php');changeUrl('content','inventory-list.php');selTopMenu(this);">
                    <span class="fa fa-clone fa-stack-2x topmenuicon"></span>
                </span>
                <span class="fa-stack fa-1x topmenu" onclick="changeUrl('leftmenu','retail-lm.php');changeUrl('content','category.php');selTopMenu(this);">
                    <span class="fa fa-industry fa-stack-2x topmenuicon"></span>
                </span>
		<?php if ($Security<8) {?>
                <span class="fa-stack fa-1x topmenu" onclick="changeUrl('leftmenu','forms-lm.php');changeUrl('content','forms-sale-day.php?DAYS=0');selTopMenu(this);">
                    <span class="fa fa-file-text fa-stack-2x topmenuicon"></span>
                </span>
		<?php } ?>
		
 				<?php 
				if ($locked==0) {
					if ($SecurityAct == 2) {
					
					?>
					<span class="fa-stack fa-1x topmenu" onclick="changeUrl('leftmenu','report-lm.php');changeUrl('content','store-inventory-matter.php?ACTG=0&GROUP=PRN_DIG_TYP');selTopMenu(this);">
						<span class="fa fa-archive fa-stack-2x topmenuicon"></span>
					</span>
					<?php 
					}
				
					if (($SecurityAct <= 1) || ($SecurityAct == 3)) {
					
					?>
					<span class="fa-stack fa-1x topmenu" onclick="changeUrl('leftmenu','report-lm.php');changeUrl('content','bank-statement.php?ACTG=0');selTopMenu(this);">
						<span class="fa fa-archive fa-stack-2x topmenuicon"></span>
					</span>
					<?php	
					}
					
				}
				?>
				
				<?php
				if (($locked==0) && ($SecurityAct == 0)){
				?>
				
				<span class="fa-stack fa-1x topmenu" onclick="changeUrl('leftmenu','accounting-lm.php');changeUrl('content','accounting-account-major.php');selTopMenu(this);" style="margin-right:0px">
                    <span class="fa  fa-balance-scale fa-stack-2x topmenuicon" style="margin-left:0.7px"></span>
                </span>
				
				<?php
				}
				?>
               
				<?php if($upperSec <= 3){ ?>
                <span class="fa-stack fa-1x topmenu" onclick="changeUrl('leftmenu','config-lm.php');changeUrl('content','config.php');selTopMenu(this);">
                    <span class="fa fa-wrench fa-stack-2x topmenuicon"></span>
                </span>
                <?php } ?>
   			</p>
			<p class="top-right">
		    <?php
                        if ($Security < 1) {
                            $class = "";
                            if ($_COOKIE["LOCK"] == "LOCK") {
                                $class = "active";
                            }
                            ?>
                            <span class="fa-stack fa-1x topmenu <?php echo $class; ?>" onclick="toggleCookie(this);">
                                    <span class="fa fa-user-secret fa-stack-2x topmenuicon"></span>
                            </span>
                            <?php
                        }
                    if ((($upperSec<=6)&&($locked==0)) || (($upperSec<=6)&&($display=="ON")) ) {
                        if($locked==0){
                            echo "<span class='fa-stack fa-1x topmenu' onclick=".chr(34)."window.scrollTo(0,0);parent.document.getElementById('syncConfirm').style.display='block';parent.document.getElementById('fade').style.display='block';".chr(34).">"; //OLTP
                        }else{
                            echo "<span class='fa-stack fa-1x topmenu' onclick=".chr(34)."location.href='http://".$OLTA."/login.php?COMMAND=UNLOCK';".chr(34).">"; //OLAP
                        }
                        if($locked==0){
                            $command="unlock";
                        }else{
                            $command="lock";
                        }
                        echo "<span class='fa fa-$command fa-stack-2x topmenuicon'></span>";
                        echo "</span>";
                    }
                ?>
                <span class="fa-stack fa-1x topmenu" onclick="location.href='http://<?php echo $OLTA; ?>/login.php?COMMAND=LOGOUT';">
                    <span class="fa fa-power-off fa-stack-2x topmenuicon"></span>
                </span>
   			</p>
			<p class="top-center">
				<img src='address-person/showimg.php?PRSN_NBR=<?php echo $prsnNbr; ?>' style='border-radius:50% 50% 50% 50%;width:30px;height:30px;vertical-align:-65%;margin-top:-50px;padding-right:2px;border:0px;'>
				<?php echo dispNameScreen(shortName($name))." <span style='color:#999999'>@</span> <span style='color:#ffffff'>".$DefCoName."</span>"; ?>
			</p>
		</td>
	</tr>
	<tr >
		<td class="leftmenu" style="border-bottom:0px">
			<!-- Set minimum width -->
			<div id='leftmenu-items' class='leftmenu-items' style="height:calc(100% - 45px);overflow:hidden;position:absolute;background-color:#22272b;transition:.25s;z-index:1001">
                <iframe id="leftmenu" borderframe=0 src="home-lm.php" style="width:217px; overflow-y:scroll;-ms-overflow-y:scroll;background-color:#22272b" onmouseover="this.focus();"></iframe></div>
            <div class="leftmenu-width" style="height:0%;overflow:hidden;transition:.25s;"></div>
		</td>
        <td class="content" style='padding-left:10px;border-bottom:0px;transition:.25s;background-color:#ffffff'>
            <!-- Match equal height -->
            <div id="content-container" style="border:none;height:calc(100% - <?php if($_SERVER['HTTP_USER_AGENT']=='nestor-macos'){echo '99';}else{echo'70';}?>px);overflow:auto;-webkit-overflow-scrolling:touch;position:fixed">
                <iframe id="content" borderframe=0 src="home.php" style='display:block;border:0px;border-right:10px;background-color:#ffffff'></iframe>
            </div>
        </td>
    </tr>
    <tr class="footer">
        <td class="footer-left">
        </td>
        <td class="footer-right">
            <p class="bottom-left">
            <font style="font-weight:600">CONFIDENTIAL</font> and for internal use only</p>
            <p class="bottom-right">Nestor X version 5.0.1 Copyright &copy; 2008-<?php echo date('Y'); ?> proreliance.com</p>
        </td>
    </tr>
</table>
    
</div>
</div>
<script type="text/javascript">
document.getElementById('syncConfirmYes').onclick =
        function () {
            sync();
            parent.document.getElementById('syncConfirm').style.display = 'none';
            parent.document.getElementById('fade').style.display = 'none';
        };
function sync() {
        document.getElementById('fade').style.display = 'block';
        msgGrowl("Memindah data...");

        var action = setInterval(function(){
            msgGrowl("Memindah data...");
        },5000);

        $.ajax({
            url: 'http://<?php echo $OLTP; ?>/dbsync/main.php?command=sync.table',
	    type: 'GET',
            success: function (r) {
		if (r) {
            msgGrowl(r);
            msgGrowl("Memindah data penjualan...");

            action = setInterval(function () {
                msgGrowl("Memindah data penjualan...");
            }, 5000);

            $.ajax({
                url: 'http://<?php echo $OLTP; ?>/dbsync/main.php?command=sync.penjualan',
                success: function (r) {
                    clearInterval(action);
                    if (r) {
                        msgGrowl(r);
                        msgGrowl("Memindah data pembelian...");

                        action = setInterval(function () {
                            msgGrowl("Memindah data pembelian...");
                        }, 5000);

                        $.ajax({
                            url: 'http://<?php echo $OLTP; ?>/dbsync/main.php?command=sync.pembelian',
                            success: function (r) {
                                clearInterval(action);
                                if (r) {
                                    msgGrowl(r);
                                    msgGrowl("Proses Selesai");

                                    document.getElementById('fade').style.display = 'none';
                                    location.href = 'http://<?php echo $OLTA; ?>/login.php?COMMAND=LOCK';
                                }
                            }
                        });
                    }
                }
            });
        }
                
            },
            error(xhr, status, error){
                msgGrowl("Gagal Sinkronisasi..." +xhr.responseText+" "+status+" "+ error+" ");
                clearInterval(action);
	        document.getElementById('fade').style.display = 'none';
            }
        });
    }

    function toggleCookie(e) {
	console.log("Cookie "+getCookie("LOCK"));
        if (getCookie("LOCK") == 'UNLOCK') {
            document.cookie = "LOCK=LOCK";
            $(e).addClass('active');

            msgGrowl("Koneksi pindah ke 10");
        } else {
            document.cookie = "LOCK=UNLOCK";
            $(e).removeClass('active');

            msgGrowl("Koneksi pindah ke 20");
        }

        document.getElementById('content').contentWindow.location.reload(true);
    }

    function getCookie(cname) {
        var name = cname + "=";
        var ca = document.cookie.split(';');
        for(var i=0; i<ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1);
            if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
        }
        return "";
    }

    //Can this be more elegant?
    if($(window).width()>=1280){
        document.getElementById('content-container').style.width='100%';
        newWidth=document.getElementById('content-container').clientWidth-210;
        document.getElementById('content-container').style.width=newWidth+'px';
    }else{
        document.getElementById('content-container').style.width='100%';
        newWidth=document.getElementById('content-container').clientWidth-20;
        document.getElementById('content-container').style.width=newWidth+'px';
    }
</script>
</body>
</html>
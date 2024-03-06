<?php
require_once "framework/database/connect-cashier.php";
require_once "framework/functions/default.php";

if (!isset($_SESSION['userID']) || $_SESSION['userID'] == "") {
    header('Location:cashier-login.php?POS_ID=' . $POSID);
    exit(0);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/cashier.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/alert/alert.css" />
    <link rel="stylesheet" type="text/css" href="framework/popup/popup.css" />
	<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
	<script type="text/javascript">
		var key;

		function doCommand(event) {
			var action = "",
				inputWrapper = document.getElementById('livesearch'),
				inputValue = inputWrapper.value;

			// Do calculation 
			if (inputValue.substr(0, 1) == "=") {
				if (inputValue.substr(1, 1).toUpperCase() == "+") {
					// Register a new member
					action = 'DAFTAR';
					inputValue = inputValue.substr(2);
				} else if (inputValue.substr(1, 1).toUpperCase() == "@") {
					// Voucher
					action = 'PVCR';
					inputValue = inputValue.substr(2);
				} else if (inputValue.substr(1, 2).toUpperCase() == "CP") {
					// Print Nota
					action = 'CPN';
					inputValue = inputValue.substr(3);
				} else if (inputValue.substr(1, 2).toUpperCase() == "OC") {
					// Open Cash Drawwer
					action = 'OCD';
					inputValue = inputValue.substr(3);
				} else if (inputValue.substr(1, 1).toUpperCase() == "B") {
					// Debit card payment
					action = 'PDEB';
					inputValue = inputValue.substr(2);
				} else if (inputValue.substr(1, 1).toUpperCase() == "C") {
					// Credit card payment
					action = 'PCRT';
					inputValue = inputValue.substr(2);
				} else if (inputValue.substr(1, 1).toUpperCase() == "D") {
					// Apply discount amout
					action = 'DSA';
					inputValue = inputValue.substr(2);
				} else if (inputValue.substr(1, 2).toUpperCase() == "RD") {
					// Apply discount amout
					action = 'DSRD';
					inputValue = inputValue.substr(3);
				} else if (inputValue.substr(1, 2).toUpperCase() == "RP") {
					// Apply discount amout
					action = 'DSRP';
					inputValue = inputValue.substr(3);
				} else if (inputValue.substr(1, 1).toUpperCase() == "F") {
					// Transfer
					action = 'PTRF';
					inputValue = inputValue.substr(2);
				} else if (inputValue.substr(1, 1).toUpperCase() == "K") {
					// Check payment
					action = 'PCHK';
					inputValue = inputValue.substr(2);
				} else if (inputValue.substr(1, 1).toUpperCase() == "L") {
					// Cancel credit card surcharge
					action = 'RMC';
					inputValue = inputValue.substr(2);

				} else if (inputValue.substr(1, 1).toUpperCase() == "M") {
					// Member discount
					action = 'DSCM';
					inputValue = inputValue.substr(2);
				} else if (inputValue.substr(1, 1).toUpperCase() == "P") {
					// Apply discount percentage
					action = 'DSP';
					inputValue = inputValue.substr(2);
				} else if (inputValue.substr(1, 1).toUpperCase() == "R") {
					// Return a mechandise
					action = 'RET';
					inputValue = inputValue.substr(2);
				} else if (inputValue.substr(1, 1).toUpperCase() == "Q") {
					// Change the quantity of the latest item
					action = 'MLTQ';
					inputValue = inputValue.substr(2);
				} else if (inputValue.substr(1, 1).toUpperCase() == "S") {
					// Apply credit card surcharge
					action = 'SUR';
					inputValue = inputValue.substr(2);
				} else if (inputValue.substr(1, 1).toUpperCase() == "SS") {
					// Apply credit card surcharge
					action = 'SURS';
					inputValue = inputValue.substr(2);
				} else if (inputValue.substr(1, 1).toUpperCase() == "T") {
					// Cash payment
					action = 'PCSH';
					inputValue = inputValue.substr(2);
				} else if (inputValue.substr(1, 1).toUpperCase() == "U") {
					// Cash back
					action = 'BAK';
					inputValue = inputValue.substr(2);
				} else if (inputValue.substr(1, 1).toUpperCase() == "X") {
					// Change the quantity to the latest item
					action = 'MLT';
					inputValue = inputValue.substr(2);
				} else if (inputValue.substr(1, 1).toUpperCase() == "Z") {
					// Reset discount
					action = 'DSZ';
					inputValue = inputValue.substr(2);
				} else if (inputValue.substr(1, 1).toUpperCase() == "A") {
					// Remove PPN
					action = 'PRM';
					inputValue = inputValue.substr(2);
				} else if (inputValue.substr(1, 1).toUpperCase() == "E") {
					// Add sales
					action = 'SAL';
					inputValue = inputValue.substr(2);
				} else if (inputValue.substr(1, 1).toUpperCase() == "G") {
					// Remove sales
					action = 'VSL';
					inputValue = inputValue.substr(2);
				} else if (inputValue.substr(1, 1).toUpperCase() == "J") {
					// Add PPN
					action = 'PPN';
					inputValue = inputValue.substr(2);
				} else if (inputValue.substr(1, 1).toUpperCase() == "N") {
					// Reset payment
					action = 'PREM';
					inputValue = inputValue.substr(2);
				} else if (inputValue.substr(1, 3).toUpperCase() == "IVC") {
					// Add receiving invoice
					action = 'IVC';
					inputValue = inputValue.substr(4);
				} else if (inputValue.substr(1, 3).toUpperCase() == "VVC") {
					// Add receiving invoice
					action = 'VVC';
					inputValue = inputValue.substr(4);
				} else if (inputValue.substr(1, 1).toUpperCase() == "O") {
					// Add digital printing invoice
					action = 'ORD';
					inputValue = inputValue.substr(2);
				} else if (inputValue.substr(1, 1).toUpperCase() == "V") {
					// Remove digital printing invoice
					action = 'VDO';
					inputValue = inputValue.substr(2);
				}
			} else if (inputValue.substr(0, 1) == "-") {
				if (inputValue.substr(1, 1).toUpperCase() == "A") {
					// Clear all active transaction
					action = 'RMVALL';
					inputValue = inputValue.substr(2);
				} else {
					// Remove item from the lists
					action = 'RMV';
					inputValue = inputValue.substr(1);
				}
			} else if (inputValue.substr(0, 1) == ">") {
				// Hold transaction
				action = 'CHL';
				inputValue = '';
			} else if (inputValue.substr(0, 1) == "<") {
				// Unhold transaction
				action = 'OHL';
				inputValue = inputValue.substr(1);
			} else if (inputValue.substr(0, 1) == "!") {
				// Remove Last Transaction
				action = 'RMLT';
				//inputValue = inputValue.substr(1);
			} else {
				// Add item to listing
				action = 'ADD';
			}

			doListingRequest(inputValue, action);
		}

		function doListingRequest(value, action) {
			parent.document.getElementById('listing').src = 'cashier-listing.php?POS_ID=<?php echo $POSID; ?>&VALUE=' + value + '&ACTION=' + action;
			showListingView();
		}

		function showListingView() {
			parent.document.getElementById('data').style.display = '';
			parent.document.getElementById('slides').style.display = 'none';
			document.getElementById('livesearch').value = '';
			document.getElementById('liveRequestResults').style.display = 'none';
			document.getElementById('livesearch').focus();
		}
	</script>
</head>
<body>
<div style="padding:10px">
	<span class="fa fa-search fa-flip-horizontal toolbar" style='margin:5px;font-size:13px;' onclick="liveReqStart();"></span>
	<input type="text" id="livesearch" class="livesearch" style="width:400px" autocomplete="off" autofocus onkeydown="if (event) {
                            key = event.keyCode;
                        } else {
                            key = window.event.keyCode;
                        }
                        if (key == 13 && event.ctrlKey) {
                            document.getElementById('livesearch').value = '';
                        } else if (key == 13) {
                            doCommand(event);
                        }"/>
	<div id="notification" style="margin:5px;padding:10px;border-radius:3px;-webkit-border-radius:3px;-moz-boder-radius:3px;background-color:#dddddd;display:none"></div>
	<div class="searchresult" id="liveRequestResults"></div>

	<div id="mainResult"></div>
	
	<div id='fadeCashier' class='black_overlay'></div>
	
	<div id="popupLogin" class="popup-white-content">
		<span class='fa fa-times' style="cursor:pointer" onclick="document.getElementById('popupLogin').style.display = 'none';
			document.getElementById('fadeCashier').style.display = 'none';"/></span>
		
		<iframe id="popupLoginContent" src=""></iframe>
	</div>
			
</div>
<script type="text/javascript">liveReqInit('livesearch','liveRequestResults','cashier-search-ls.php?POS_ID=<?php echo $POSID; ?>','','mainResult');</script>
</body>
</html>
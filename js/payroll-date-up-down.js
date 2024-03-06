	function checkEm(e,tsTime){
		var day = e.slice(0,-4);
		var col = e.slice(0,-2).slice(-2);
		var cal = e.slice(-2);
		if (col>1){
			return thisDay(day,col,cal,tsTime);
		}else{
			return befDay(day,col,cal,tsTime);
		}
	}
	function thisDay(day,col,cal,tsTime){
		cal   = cal.replace(/^0+/, "");

		for (j=1; j<=col; j++){
			var xj     = col - j;
			var xjCol  = day+"0"+xj+'0'+cal;
			var dataXj = jQuery('#time-'+xjCol+">#ValTime").text();

			if (dataXj.length!=0){
				xj = xj+1;
				data =[day+'0'+xj+'0'+cal,tsTime];
				return data;
			}else{
				if (xj == 0){
					return befDay(day, col, cal,tsTime);
				}
			}
		}
	}
	function befDay(day, col, cal, tsTime){
		var vrDay = new Date(tsTime);
		var m     = vrDay.getMonth()+1;
		var y 	  = vrDay.getFullYear();
		var LstDay= new Date(y,m,0);
		cal   	  = cal.replace(/^0+/, '');
		day       = day-1;

		if (day==0){
			LstDay.setMonth(LstDay.getMonth()+0,0);
			m  = LstDay.getMonth()+0;
			m  = m+1;
			y  = LstDay.getFullYear();
			day= LstDay.getDate();
			cal= cal-1;		
		}

		if (col==0){col= 6;} 
			for (var ix=1;ix<7;ix++){
			var dx    = 7-ix;
			var ixCol = day+"0"+dx+'0'+cal;
			var datas = jQuery('#time-'+ixCol+">#ValTime").text().trim();
			if (datas.length!=0){
				dx= dx+1;
				data = [day+'0'+dx+'0'+cal,y+"-"+m+"-"+day];
				return data;
			}
		}
	}

	//Proses Hover pada time 
	jQuery(document).ready(function(){
		jQuery("td[id^=time-]").hover(function() {
			var yrTime 		= "<?php if ($_GET['PYMT_DTE']==''){echo date('Y');}else{ echo date('Y',strtotime($_GET['PYMT_DTE']));} ?>";
			var moTime 		= "<?php if ($_GET['PYMT_DTE']==''){echo date('m');}else{ echo date('m',strtotime($_GET['PYMT_DTE']));} ?>";
			var prsnNbr 	= "<?php echo $_GET['PRSN_NBR'];?>";
			var idTime		= this.id;
			var idNumber 	= idTime.match(/\d+/); 
			var TimeVal 	= jQuery("#"+this.id+">#ValTime").text();
			
			if (parseInt(idNumber[0].slice(-2))==1){
				moTime =moTime-1;
				if (moTime==0){
					moTime=12;
					yrTime=yrTime-1;
				}
			}
			var dayTime 	= idNumber[0].slice(0,-4);

			if (TimeVal.length>0){
				jQuery('#time-upDown-'+idNumber[0]).css('visibility','visible');
			}
		}, function(){
			var idNumber 	= this.id.match(/\d+/);
			jQuery("#time-upDown-"+idNumber).css('visibility','hidden');
		});//hover

		//Prosess klik pada time-up
		jQuery('div[id^=time-up-]>.listUp').click(function(){
			var yrTime 		= jQuery('#YEAR').val();
			var moTime 		= jQuery('#MONTH').val();
			var prsnNbr 	= jQuery('#PRSN_NBR').val();
			var id 		 	= jQuery(this).parents('div').attr('id');
			var idNumber 	= id.replace(/[^\d]/g, '');
			var dayTime 	= idNumber.slice(0,-4);

			if (parseInt(idNumber.slice(-2))==1){
				moTime = moTime-1;
				if (moTime==0){
					moTime=12;
					yrTime=yrTime-1;
				}
			}

			var dataTo 		= checkEm(idNumber,yrTime+"-"+moTime+"-"+dayTime);
			var idNumTo 	= dataTo[0];
			var colTo 	  	= parseInt(idNumTo.slice(0,-2).slice(-2))-1;
			var getIdNumIn  = idNumTo.slice(0,-4)+"0"+colTo+idNumTo.slice(-2);
			var getTmIn   	= jQuery("#time-"+getIdNumIn+">#ValTime").text().trim();
			var getTmTsIn 	= dataTo[1];
			var TimeVal 	= jQuery('#time-'+idNumber+'>#ValTime').text();

			jQuery.ajax({
				type:'GET',
				url:'payroll-time-up-down.php',
				data: {
						'TYP_ID':"UP",
						'TIME_TS_TO':getTmTsIn,
						'TIME_TS_FM':yrTime+"-"+moTime+"-"+dayTime,
						'TIME_TO':getTmIn,
						'TIME_FM':TimeVal,
						'PRSN_NBR':prsnNbr,
					 }
			}).done(function(data){
				data = jQuery.parseJSON(data);

				jQuery("#time-"+idNumTo+">#ValTime").text(data.CLOK_OT_TS_TO);

				if ( jQuery.inArray(parseInt(idNumber.slice(0,-2).slice(-2)),[1,3,5])!=-1){
						jQuery("#time-"+idNumber+">#ValTime").empty();

						if (parseInt(data.CLOK_IN_TS_FM.slice(0,-3))<12){
							jQuery("#time-"+idNumber+">#ValTime").empty();
							jQuery("#time-"+idNumber.slice(0,-4)+"01"+idNumber.slice(-2)+">#ValTime").text(data.CLOK_IN_TS_FM);
						}else if (parseInt(data.CLOK_IN_TS_FM.slice(0,-3))>=12 && parseInt(data.CLOK_IN_TS_FM.slice(0,-3))<18){
							jQuery("#time-"+idNumber+">#ValTime").empty();
							jQuery("#time-"+idNumber.slice(0,-4)+"03"+idNumber.slice(-2)+">#ValTime").text(data.CLOK_IN_TS_FM);
						}else if (parseInt(data.CLOK_IN_TS_FM.slice(0,-3))>=18){
							jQuery("#time-"+idNumber+">#ValTime").empty();
							jQuery("#time-"+idNumber.slice(0,-4)+"05"+idNumber.slice(-2)+">#ValTime").text(data.CLOK_IN_TS_FM);
						}
				}
				
				var colBfr  	= parseInt(idNumber.slice(0,-2).slice(-2))+1;
				var idNumberBfr = idNumber.slice(0,-4)+"0"+colBfr+idNumber.slice(-2);
				
				jQuery("#time-"+idNumberBfr+">#ValTime").empty();
				
				//Merubah  hitungan jam kerja
				jQuery("#diff-"+idNumber.slice(0,-4)+""+idNumber.slice(-2)).empty();
				jQuery("#diff-"+idNumber.slice(0,-4)+""+idNumber.slice(-2)).text(data.DIFF_FM);	
				
				jQuery("#diff-"+idNumTo.slice(0,-4)+""+idNumTo.slice(-2)).empty();
				jQuery("#diff-"+idNumTo.slice(0,-4)+""+idNumTo.slice(-2)).text(data.DIFF_TO);

				jQuery('#MACH_CLOK_HR').val(data.DIFF_TOT);
				jQuery('#MACH_CLOK_DAY').val(data.DIFF_TOT_DAY);
			}).fail(function(){
				console.log('Fail');
			});	
		});//clik
		jQuery('div[id^=time-up-]>.listDown').click(function(){
			var yrTime 		= jQuery('#YEAR').val();
			var moTime 		= jQuery('#MONTH').val();
			var prsnNbr 	= jQuery('#PRSN_NBR').val();
			var id 		 	= jQuery(this).parents('div').attr('id');
			var idNumber 	= id.replace(/[^\d]/g, '');
			var dayTime 	= idNumber.slice(0,-4);
			var TimeVal     = jQuery('#time-'+idNumber+'>#ValTime').text();
			var colTo 		= idNumber.slice(-2);
			var dayTo 		= parseInt(dayTime)+1;
			var dateFm 		= new Date(yrTime+'-'+moTime+'-'+dayTime);
			var dateFrm		= dateFm.getFullYear()+'-'+(dateFm.getMonth()+1)+'-'+dayTime;
			var dataTo 		= checkEm(idNumber,yrTime+"-"+moTime+"-"+dayTime);
			var idNumTo 	= dataTo[0];

			if (parseInt(idNumber.slice(-2))==1){
				dateFm.setMonth(dateFm.getMonth()+0,0);
				dateFrm		= dateFm.getFullYear()+'-'+(dateFm.getMonth()+1)+'-'+dayTime;
				if (dayTime==(dateFm.getDate())){
					colTo   = '02';
					dayTo   = 1;
					dateFm.setMonth(dateFm.getMonth()+2,0);
				}
			}

			var dateTo 		= dateFm.getFullYear()+'-'+(dateFm.getMonth()+1)+'-'+dayTo;
			
			jQuery.ajax({
				type:'GET',
				url:'payroll-time-up-down.php',
				data: {
						'TYP_ID':"DOWN",
						'TIME_TS_TO':dateTo,
						'TIME_TS_FM':dateFrm,
						'TIME_TO':"",
						'TIME_FM':TimeVal,
						'PRSN_NBR':prsnNbr,
					 }
			}).done(function(data){

				data = jQuery.parseJSON(data);

				jQuery("#time-"+idNumTo+">#ValTime").text(data.CLOK_OT_TS_TO);

				if (parseInt(data.TIME_TO.slice(0,-3))<12){
					var idNumTo = dayTo+'01'+colTo;
					jQuery("#time-"+idNumTo+">#ValTime").empty();
					jQuery("#time-"+idNumTo+">#ValTime").text(data.TIME_TO);

				}else if (parseInt(data.TIME_TO.slice(0,-3))>=12 && parseInt(data.TIME_TO.slice(0,-3))<18){
					var idNumTo = dayTo+'03'+colTo;
					jQuery("#time-"+idNumTo+">#ValTime").empty();
					jQuery("#time-"+idNumTo+">#ValTime").text(data.TIME_TO);

				}else if (parseInt(data.TIME_TO.slice(0,-3))>=18){
					var idNumTo = dayTo+'05'+colTo;
					jQuery("#time-"+idNumTo+">#ValTime").empty();
					jQuery("#time-"+idNumTo+">#ValTime").text(data.TIME_TO);
				}
				
				jQuery("#time-"+idNumber+">#ValTime").empty();
				
			 	//Merubah  hitungan jam kerja
				jQuery("#diff-"+idNumTo.slice(0,-4)+""+colTo).empty();
				jQuery("#diff-"+idNumTo.slice(0,-4)+""+colTo).text(data.DIFF_FM);	
				
				jQuery("#diff-"+idNumber.slice(0,-4)+""+idNumber.slice(-2)).empty();
				jQuery("#diff-"+idNumber.slice(0,-4)+""+idNumber.slice(-2)).text(data.DIFF_TO);

				jQuery('#MACH_CLOK_HR').val(data.DIFF_TOT);
				jQuery('#MACH_CLOK_DAY').val(data.DIFF_TOT_DAY);

			}).fail(function(){
				console.log('Fail');
			});

		});//listDown
	});//document
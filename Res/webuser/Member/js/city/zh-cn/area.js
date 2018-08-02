function showLocation(province , city , town) {
	
	var loc	= new Location();
	var title	= ['省份' , '地级市' , '市、县、区','U.S.A','州','市'];
	$.each(title , function(k , v) {
		title[k]	= '<option value="">'+v+'</option>';
	})
	
	$('#loc_province').append(title[0]);
	$('#loc_city').append(title[1]);
	$('#loc_town').append(title[2]);
	$('#loc_province1').append(title[3]);
	$('#loc_city1').append(title[4]);
	$('#loc_town1').append(title[5]);
	
	$("#loc_province,#loc_city,#loc_town").select2();
	$('#loc_province').change(function() {
		$('#loc_city').empty();
		$('#loc_city').append(title[1]);
		loc.fillOption('loc_city' , '0,'+$('#loc_province').val());
		$('#loc_city').change()
	});
	
	$('#loc_city').change(function() {
		$('#loc_town').empty();
		$('#loc_town').append(title[2]);
		loc.fillOption('loc_town' , '0,' + $('#loc_province').val() + ',' + $('#loc_city').val());
	});
	
	$('#loc_town').change(function() {
		$('input[name=location_id]').val($(this).val());
	});

	$("#loc_province1,#loc_city1,#loc_town1").select2();
	$(document).ready(function(){
		$('#loc_city1').empty();
		$('#loc_city1').append(title[4]);
		$('#loc_province1').attr("disabled","disabled"); 
		loc.fillOption('loc_city1' , '0,4845');
		$('#loc_city1').change()
	});
	// 	$('#loc_province1').change(function() {
	// 	$('#loc_city1').empty();
	// 	$('#loc_city1').append(title[4]);
	// 	loc.fillOption('loc_city1' , '0,'+$('#loc_province1').val());
	// 	$('#loc_city1').change()
	// });

	
	$('#loc_city1').change(function() {
		$('#loc_town1').empty();
		$('#loc_town1').append(title[5]);
		loc.fillOption('loc_town1' , '0,4845,'+ $('#loc_city1').val());
		// loc.fillOption('loc_town1' , '0,' + $('#loc_province1').val() + ',' + $('#loc_city1').val());
	});
	
	$('#loc_town1').change(function() {
		$('input[name=location_id]').val($(this).val());
	});
	
	if (province) {
		loc.fillOption('loc_province' , '0' , province);
		loc.fillOption('loc_province1' , '0' , province);
		
		if (city) {
			loc.fillOption('loc_city' , '0,'+province , city);
			loc.fillOption('loc_city1' , '0,'+province , city);
			if (town) {
				loc.fillOption('loc_town' , '0,'+province+','+city , town);
				loc.fillOption('loc_town1' , '0,'+province+','+city , town);
			}
		}
		
	} else {
		loc.fillOption('loc_province' , '0');
		loc.fillOption('loc_province1' , '0');
	}
		
}

$(function(){
		showLocation();
		$('#btnval').click(function(){
			alert($('#loc_province').val() + ' - ' + $('#loc_city').val() + ' - ' +  $('#loc_town').val()) 
		})
		$('#btntext').click(function(){
			alert($('#loc_province').select2('data').text + ' - ' + $('#loc_city').select2('data').text + ' - ' +  $('#loc_town').select2('data').text) 
		})
	});
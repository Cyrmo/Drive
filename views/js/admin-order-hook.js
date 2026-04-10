/*
* Prestatill Drive - Click & Collect
*
* Drive Module & Click & Collect for Prestashop
*
*  @author    Laurent Baumgartner laurent@prestatill.com
*  @copyright 2017-2020 Prestatill SAS
*  @license   Prestatill SAS
*/

$(document).ready(function(){
	
	var drive_url = $('#drive_base_url').val();
	
	$('body').on('click tap','.order_creneau button', function() {
		if(confirm($('#confirm_msg').val()))
		{
			$('.order_creneau_edit').slideToggle();
			$('.order_creneau_new_button').toggleClass('active');
			
			_selectStore($('.order_creneau_edit #id_store').val());
		}
	});
	
	$('body').on('click tap','.order_creneau_new_button button', function() {
		
		$('.order_creneau_edit').slideToggle();
		$('.order_creneau_new_button').toggleClass('active');
		
	});
	
	$('body').on('click tap','.order_creneau_edit button[name="submitSlotCreate"]', function() {
		
		$(this).prop('disabled',true);
		$('#table_dispo_overlay').show();
		$.ajax({
            url: $('#table_dispo').data('url'),
            type: 'json',
            action: 'adminCreateSlot',
            method: 'post',
            data: {
                action: 'adminCreateSlot',
                hour: $('#slot_hour').val(),
                date: $('#slot_date').val(),
                id_order: $('input[name="oc_id_order"]').val(),
                send_email: $('[name="order_send_mail_modif"]:checked').val(),
                type: $('.order_creneau_new_button').length,
            },
            success: function(data) {

                if (data.message.success == false) {
                } else {
                	window.location.reload();
                }
            } 
        });
	});
	
	$('body').on('change','.order_creneau_edit #id_store', function() {
		_selectStore($(this).val());
	});
	
	// 1.4.0
	$('body').on('click tap','.add_creneau_edit button[name="submitSlotCreate"]', function() {
		
		$(this).prop('disabled',true);
		$('#table_dispo_overlay').show();
		$.ajax({
            url: $('#table_dispo').data('url'),
            type: 'json',
            action: 'adminCreateSlot',
            method: 'post',
            data: {
                action: 'adminCreateSlot',
                hour: $('#slot_hour').val(),
                date: $('#slot_date').val(),
                id_order: $('input[name="oc_id_order"]').val(),
                send_email: $('[name="order_send_mail_modif"]:checked').val(),
                type: $('.order_creneau_new_button').length,
            },
            success: function(data) {

                if (data.message.success == false) {
                } else {
                	window.location.reload();
                }
            } 
        });
	});
	
	$('body').on('change','.add_creneau_edit #id_store', function() {
		_selectStore($(this).val());
	});
	
	$('body').on('click tap', '#table_creneau td span.dispo', function() {
        $('#table_creneau td span').removeClass('selected');
        var slot_date = $(this).data('date');
		var slot_hour = $(this).data('hour');
        $(this).addClass('selected');
        $.ajax({
            url: $('#table_dispo').data('url'),
            action: 'assignSlotFromBO',
            type: 'json',
            method: 'post',
            data: {
            	slot: $(this).data(),
            	action: 'assignSlotFromBO',
            	id_order: $('input[name="oc_id_order"]').val(),
				id_store: $('.order_creneau_edit select[name="id_store"]').val(),
            	}, 
        }).done(function(response) {
            if (response.success == true) {
				$('#slot_date').val(slot_date);
				$('#slot_hour').val(slot_hour);
            } else {
                alert('error');
            }
        }).fail(function() {
            alert('Erroor');
        });
    });
    
    function _createDaysTable(id_store) {
    	$.ajax({
            url: $('#table_dispo').data('url'),
            type: 'json',
            action: 'initTable',
            method: 'post',
            data: {
                action: 'initTable',
                id_store: id_store,
				init_bo: 1,
            },
            success: function(data) {

                if (data.status == 'success') {
	                	
                	// On affiche en fonction du mode d'affichage retenu
            		_viewList(data.message.table_days);
            		
            		var size = $('body').find('#list_creneau').data('size');
            		$('body').find('#list_creneau').width(size*230+'px');
            		changeclass(data.message.table_days);
            		adjustListDisplay(data.message.table_days);
            		$('#table_legend').hide().remove();
            		reserved(data.message.table_days);
            		vacation(data.message.table_days);
                
                	$('#list_creneau td span:not(".disabled"):first').trigger('click');
                	$('#table_dispo_overlay').hide();
                }
            } 
        });
    }
    
    function _selectStore(id_store) { 
    	
    	var id_order = $('input[name="oc_id_order"]').val();
    	$('#table_dispo_overlay').show();
    	
        $.ajax({
            url: $('#table_dispo').data('url'),
            action: 'assignStoreFromBO',
            type: 'json',
            method: 'post',
            data: {
                action: 'assignStoreFromBO',
                store: id_store,
                id_order: id_order
            },
            success: function(data) {
                if (data.status == 'success') {
                	$('#slot_date').val('');
					$('#slot_hour').val('');
               		_createDaysTable(id_store);
                } else {
                    alert(data.message);
                }
            }
        });
    }

	function _viewList(message) {
	    	
		var html = '';
		var size = Object.keys(message.days).length;
		
		html += '<table id="list_creneau" data-size="'+parseInt(size)+'" class="col-sm-12 viewList"><thead>';
	    for (var i = 1; i <= size; i++) {
	        html += '<td><span data-date="' + $.format.date("" + message.days[i].dateen, "d MMM yyyy") + '">' + message.days[i].day + " " + $.format.date("" + message.days[i].date, "d MMM yyyy") + '</span></td>';
	    }
		html += '</thead>';
		html += '</table>';
		
		$('#table_dispo_head').html(html);
		
		html = '<table id="table_creneau" data-size="'+parseInt(size)+'" class="col-sm-12 viewList">';
		
		var sizeh = Object.keys(message.creneau).length;
		html +='<tr><td>';
	    for (var h = 0; h < sizeh - 1; h++) {
	        for (var i = 1; i <= size; i++) {
	            var additional_class = 'indispo';
	            html += '<span class="creneau ' + additional_class + '" data-date="'+message.days[i].dateen+'" data-id-day="' + message.days[i].id_day + '" data-datetime="' + message.days[i].dateen + ' ' + message.creneau[h] + '" data-hour="' + message.creneau[h] + '" data-date="' + message.days[i].date + '">';
	            html += '' + $.format.date("0000-00-00 " + message.creneau[h] + "", "H:mm") + ' - ' + $.format.date("0000-00-00 " + message.creneau[h + 1] + "", "H:mm") + '</span>';
	        }
	       
	    }
		html += '</td></tr>';
		html += '</tbody></table>';
	    html += '<div class="clear"></div>';
	    $('#table_dispo').html(html);
	    
	}
	
	function _viewTable(message) {
	    var html = '';
	    html += '<table id="table_creneau" class="col-sm-12"><thead>';
	    html += '<td></td>';
	    var size = Object.keys(message.days).length;
	    for (var i = 1; i <= size; i++) {
	        html += '<td>' + message.days[i].day + " " + $.format.date("" + message.days[i].date, "d MMM yyyy") + '</td>';
	    }
	    html += '</thead>';
	    var sizeh = Object.keys(message.creneau).length;
	    for (var h = 0; h < sizeh - 1; h++) {
	        html += '<tr><td>' + $.format.date("0000-00-00 " + message.creneau[h] + "", "H:mm") + ' - ' + $.format.date("0000-00-00 " + message.creneau[h + 1] + "", "H:mm") + '</td>';
	        for (var i = 1; i <= size; i++) {
	            var additional_class = 'indispo';
	            html += '<td class="creneau ' + additional_class + '" data-id-day="' + message.days[i].id_day + '" data-datetime="' + message.days[i].dateen + ' ' + message.creneau[h] + '" data-hour="' + message.creneau[h] + '" data-date="' + message.days[i].date + '"></td>';
	        }
	        html += '</tr>';
	    }
	    html += '</table>';
	    html += '<div class="clear"></div>';
	        
	    $('#table_dispo').html(html);
	}
	
	function diplayOrHideColumns(current_start_column, current_nb_display_column, nb_colmuns) {
	    for (var i = 2; i <= nb_colmuns + 1; i++) {
	        if (i < current_start_column || i >= current_start_column + current_nb_display_column) {
	            $('#table_creneau tr td:nth-child(' + i + ')').hide();
	        } else {
	            $('#table_creneau tr td:nth-child(' + i + ')').show();
	        }
	    }
	}
	
	function changeclass(message) {
	    $('#table_creneau .indispo').each(function() {
	        for (var e = 1; e <= 7; e++) {
	            if (message.open[e] != undefined && $(this).data('idDay') == message.open[e].id_day && message.open[e].nonstop == true && $(this).data('hour') >= message.open[e].hour_open_am && $(this).data('hour') < message.open[e].hour_close_pm) {
	                $(this).removeClass('indispo');
	                $(this).addClass('dispo');
	            } else if (message.open[e] != undefined && $(this).data('idDay') == message.open[e].id_day && message.open[e].nonstop == false && (($(this).data('hour') >= message.open[e].hour_open_am && $(this).data('hour') < message.open[e].hour_close_am) || ($(this).data('hour') >= message.open[e].hour_open_pm && $(this).data('hour') < message.open[e].hour_close_pm))) {
	                $(this).removeClass('indispo');
	                $(this).addClass('dispo');
	            }
	        }
	    });
	}
	
	function adjustListDisplay(message) {
		
		// On gère les carrences
	    /*Date d'aujourd'hui à l'instant T*/
	    var date_today = Date.now();        
	    var carence = message.creneau_carence*60*1000;
	    var add = date_today + carence; 
	    var date_limit = new Date(add);
	    var datetime = date_limit.getFullYear()+'-'+((date_limit.getMonth()+1)+'').padStart(2,'0')+'-'+(date_limit.getDate()+'').padStart(2,'0')+' '+(date_limit.getHours()+'').padStart(2,'0')+':'+(date_limit.getMinutes()+'').padStart(2,'0')+':'+(date_limit.getSeconds()+'').padStart(2,'0');
	    
	   
		$('#table_creneau tbody tr span').each(function(){
	    	if($(this).data('datetime') <= datetime)
	    	{
	    		$(this).removeClass('dispo').addClass('indispo');	
	    	}
	    });
	    
	    // On check les vacances 
	    vacation(message);
	    
	    // On supprime les jours où il n'y a aucune dispo dans l'entête
	    $('body').find('#list_creneau tr td span').each(function(){
	    	var the_day = $(this).data('date');
	    	if($('#table_creneau tr td span[data-date="'+the_day+'"].dispo').length == 0)
	    		$(this).addClass('disabled');
	    });
	    
	    $('#table_dispo_head').scrollLeft(0);
	    
	    // On affiche les premières dispos
	    $('body').find('#table_dispo_head #list_creneau tr td span').each(function()
	    {
	    	if($(this).hasClass('disabled') == false)
	    	{
	    		var distance_left = $(this).position().left;
	    		var div_width = $(this).outerWidth();
	    		$('#table_dispo_head').scrollLeft(parseInt(distance_left-div_width));
	    		$(this).trigger('click');
	    		return false;
	    	}
	    		
	    });
	    
	    $('body').on('click tap', '#list_creneau.viewList td span', function(){
	    	if($(this).hasClass('disabled') == false)
	    	{
	    		$('#list_creneau.viewList td span').removeClass('active');
		    	$(this).addClass('active');
		    	$('#table_creneau tr td span').hide();
		    	var the_day = $(this).data('date');
		    	$('#table_creneau tr td span[data-date="'+the_day+'"].dispo').fadeIn(250).css('display','inline-block');
		    	$('#table_creneau tr td span[data-date="'+the_day+'"].red').fadeIn(250).css('display','inline-block');
	    	}
	    });
	    
	    //var the_day = $('#list_creneau span.active').data('date');
		//$('#table_creneau tr td span[data-date="'+the_day+'"].red').fadeIn(250).css('display','inline-block');
	    
	    $('#nav_buttons').hide().remove();
	    
	}
	
	
	function carrence(message) { 
		/*Date d'aujourd'hui à l'instant T*/
	    var date_today = Date.now();        
	    var carence = message.creneau_carence*60*1000;
	    var add = date_today + carence; 
	    var date_limit = new Date(add);
	    var datetime = date_limit.getFullYear()+'-'+((date_limit.getMonth()+1)+'').padStart(2,'0')+'-'+(date_limit.getDate()+'').padStart(2,'0')+' '+(date_limit.getHours()+'').padStart(2,'0')+':'+(date_limit.getMinutes()+'').padStart(2,'0')+':'+(date_limit.getSeconds()+'').padStart(2,'0');
	    
	    $('#table_creneau tbody tr td').each(function(){
	    	if($(this).data('datetime') <= datetime)
	    	{
	    		$(this).removeClass('dispo').addClass('indispo');	
	    	}
	    });
	}
	
	function vacation(message) {
	    $('#table_creneau .creneau').each(function() {
	        var sizevacation = Object.keys(message.vacations).length;
	        for (var j = 0; j < sizevacation; j++) {
	            var datetime_creneau = $(this).data('datetime');
	            var date_creneau = new Date(datetime_creneau);
	            var date_creneau_format = $.format.date(date_creneau, "yyyy-MM-dd");
	            if (date_creneau_format >= message.vacations[j].vacation_start && date_creneau_format <= message.vacations[j].vacation_end) {
	                $(this).addClass('red indispo');
	                $(this).removeClass('dispo');
	            }
	        }
	    });
	}
	
	function reserved(message) {
		// On ajoute les reservés
	    var sizer = message.reserved!=null?Object.keys(message.reserved).length:0;
	    if(sizer > 0)
	    {
		    for (var key in message.reserved) {
			    if(message.reserved[key] < message.creneau_limit) {
			  	    $('[data-datetime="'+key+'"]').addClass('busy');
			    } else if (message.reserved[key] >= message.creneau_limit) {
			  	    $('[data-datetime="'+key+'"]').removeClass('dispo').addClass('red indispo');
			    }
		    }
	    }
	}
	
	function disabledButtonsDays() {
	    var date = new Date();
	    var date_today = $.format.date(date, "E d MMM yyyy");
	    $('#prev_days, #next_days').removeClass('disabled');
	    if ($('#table_creneau > thead > tr > td:nth-child(2)').html() == date_today) {
	        $('#prev_days').addClass('disabled');
	    }
	}
});
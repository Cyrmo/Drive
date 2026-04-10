/*
* Prestatill Drive - Click & Collect
*
* Drive Module & Click & Collect for Prestashop
*
*  @author    Laurent Baumgartner laurent@prestatill.com
*  @copyright 2017-2020 Prestatill SAS
*  @license   Prestatill SAS
*/

var ps_url = $('#psd_base_url').val();
var nb_colmuns;
var current_start_column = 2;
var current_nb_display_column = 7;
var opc_checkout = $('body#order-opc').length;

$(document).ready(function() {
    $('#active_js').addClass('disabled');
    $('button[name=processCarrier]').css("display", "block");
    $('#extra_carrier').css("display", "block");
    $('#delivery_options_address').css("display", "block");
    $('#HOOK_PAYMENT').css("display", "block");
    $('#form > div.order_carrier_content > div.box').css("display", "block");
    $('.delivery_options_address').css("display", "block");
    $('button[name=confirmDeliveryOption]').hide();
    $('#nav_buttons').hide();
    $('#prev_days').addClass('selected');
    $('#verifyCreneau, .material-icons').hide().remove();
    // 1.4.0
    $('#carrier_message').hide();
    $('#store_list').hide();
    // 2.0.0
    $('#store_selector_modal').prependTo('body');
    
    // On recharge la page si elle reste affichée plus de 10 minutes pour éviter que le créneau ne devienne obsolète si on reste sur la page
    refresh(600);
    
    // 1.4.0 : Ajout d'informations sur les produits du panier
    if($('body').attr('id') == 'order')
    {
        $('.carriers_list').each(function(){
            $(this).appendTo($(this).parents('tr').find('.cart_description'));
        });
        setTimeout(function(){
            $('.carriers_list').slideDown();
        }, 100)
    }
    
    $('.delivery_option_radio:checked').parents('.delivery-option').next().append($('#table_box'));
    
    $('#checkout-payment-step').on('click tap', function(e){
    	if($(this).hasClass('no_slot_checked'))
    	{
    		$('#checkout-delivery-step span.step-edit').trigger('click');
    	}
    });
    
    // 1.4.0 : Check si on a au moins un transporteur commun
    if($('input[name="step"]').val() == 3 || $('#checkout-delivery-step').hasClass('js-current-step'))
    {
        // Si on a aucun transporteur de disponible, on supprime tout et on affiche le message
        if($('#common_carriers').length == 1 && $('#common_carriers').val() == 0)
        {
            $('.delivery_options_address').hide().remove();
            $('button[name="processCarrier"]').hide().remove();
            $('#carrier_message').fadeIn();
        } 
    }
    
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
    
    $('body').on('click tap', '#table_creneau span.dispo', function() {
        $('#table_creneau span').removeClass('selected');
        $(this).addClass('selected');
        $('#table_box').attr('data-creneau', '1');
        $.ajax({
            url: $('#table_dispo').data('url'),
            action: 'assignSlot',
            type: 'json',
            method: 'post',
            data: {
            	slot: $(this).data(),
            	action: 'assignSlot',
            	}, 
        }).done(function(response) {
            if (response.success == true) {
                $('#creneau_day').html(response.msg);
                $('#creneau_selected').removeClass('alert-warning').addClass('alert-success');
                $('#table_legend, #nav_buttons').hide();
                
                // 2.0.0 : On valide la réservation côté front
                _validateSlotReservation();
                
                if($('#creneau_selected').length > 0)
                {
                    // 2.1.0
                    $('body').find('#creneau_valid').slideUp();

                    $([document.documentElement, document.body]).animate({
                    	scrollTop: $('#creneau_selected').offset().top-105
                    },1000);
                }
               
            } else {
                alert('error');
            }
        }).fail(function() {
            alert('An error has occured, please check your HTTPS parameters on Prestashop Back-office');
        });
    });
    
    $('body').on('click tap', '#store_list .clickable', function() {
    	
    	if($(this).hasClass('active_store') == false)
    	{
    		$('#store_list li.clickable').removeClass('active_store');
	        $(this).addClass('active_store');
	        var id_store = $(this).data('id_store');
	        _selectStore(id_store);
    	}
    });
       
    $('body#order-opc').on('click', '#SubmitLogin,#submitAccount', function() {
    	setTimeout(function(){
    		$('.delivery_option_radio:checked').parents('table').parent().append($('#table_box'));
    		_filterStoresByCarrier(0);
			_hideOrShow();
			//console.log(_checkStoresCarrier());
            if (_checkStoresCarrier() == 0) {
                _reInitStore();
            }
    	},1000);
    });
    
    $('body').on('click tap', '#table_creneau td.dispo', function() {
        $('#table_creneau td').removeClass('selected');
        $(this).addClass('selected');
        $('#table_box').attr('data-creneau', '1');
        $.ajax({
            url: $('#table_dispo').data('url'),
            action: 'assignSlot',
            type: 'json',
            method: 'post',
            data: {
            	slot: $(this).data(),
            	action: 'assignSlot',
            	},
        }).done(function(response) {
            if (response.success == true) {
            	if(opc_checkout == 1) {
            		$('body#order-opc').find('#opc_delivery_methods #creneau_day').html(response.msg);
        			$('body#order-opc').find('#opc_delivery_methods #creneau_selected').removeClass('alert-warning').addClass('alert-success');
        			$('body#order-opc').find('#opc_payment_methods #creneau_day').html(response.msg);
        			$('body#order-opc').find('#opc_payment_methods #creneau_selected').removeClass('alert-warning').addClass('alert-success');
        			
        			// 2.0.0 : On valide la réservation côté front
                    _validateSlotReservation();
        			
            	} else {
            		$('#creneau_day').html(response.msg);
                	$('#creneau_selected').removeClass('alert-warning').addClass('alert-success');
            	}
            	if($('#creneau_selected').length > 0)
                {
                    // 2.1.0
                    $('body').find('#creneau_valid').slideUp();

                	$([document.documentElement, document.body]).animate({
                    	scrollTop: $('#creneau_selected').offset().top-105
                    },1000);
                }
                
            } else {
                alert('error');
            }
        }).fail(function() {
            alert('An error has occured, please check your HTTPS parameters on Prestashop Back-office');
        });
    });
    
    $('#opc_payment_methods-content').on('click tap', '.payment_module a', function(e){
    	var link = this.href;
    	if (_checkStoresCarrier() > 0) {
	        var creneau = $('#table_box').attr('data-creneau');
	        if ($('#table_box').attr('data-creneau') == 0 && !$('#shop_selected').hasClass('alert-success')) {
	            $('#creneau_selected').fancybox({
	                content: $('#choose_store_msg').text(),
	            });
	            $('#creneau_selected').click();
	            e.preventDefault();
	        } else if (!$('#shop_selected').hasClass('alert-success')) {
	            $('#creneau_selected').fancybox({
	                content: $('#choose_store_msg').text(),
	            });
	            $('#creneau_selected').click();
	            e.preventDefault();
	        } else if ($('#table_box').attr('data-creneau') == 0) {
	            $('#creneau_selected').fancybox({
	                content: $('#choose_slot_msg').text(),
	            });
	            $('#creneau_selected').click();
	            e.preventDefault();
	        } else {
	            e.preventDefault();
	            $.ajax({
	                url: $('#table_dispo').data('url'),
	                action: 'processCarrier',
	                type: 'json',
	                method: 'post',
	                data: {
	                    action: 'processCarrier',
	                },
	                success: function(data) {
	                    if (data.status == 'success') {
	                        if (data.message.success == false) { /*.preventDefault();	*/
	                            $('#creneau_selected').fancybox({
	                                content: data.message.alert,
	                            });
	                            $('#creneau_selected').click();
	                            //_createDaysTable();
	                        } else {
	                            window.location = link;
	                        }
	                    } else {
	                        alert(data.message);
	                    }
	                }
	            });
	        }
        }
    });
    
    $('#form').on('submit', function(e) {
        if ($(this).hasClass('ok')) { /*alert('tout est ok');*/
            return;
        }
        if (_checkStoresCarrier() > 0) {
	        var creneau = $('#table_box').attr('data-creneau');
	        if ($('#table_box').attr('data-creneau') == 0 && !$('#shop_selected').hasClass('alert-success')) {
	            $('#creneau_selected').fancybox({
	                content: $('#choose_store_msg').text(),
	            });
	            $('#creneau_selected').click();
	            e.preventDefault();
	        } else if (!$('#shop_selected').hasClass('alert-success')) {
	            $('#creneau_selected').fancybox({
	                content: $('#choose_store_msg').text(),
	            });
	            $('#creneau_selected').click();
	            e.preventDefault();
	        } else if ($('#table_box').attr('data-creneau') == 0) {
	            $('#creneau_selected').fancybox({
	                content: $('#choose_slot_msg').text(),
	            });
	            $('#creneau_selected').click();
	            e.preventDefault();
	        } else {
	            e.preventDefault();
	            $.ajax({
	                url: $('#table_dispo').data('url'),
	                action: 'processCarrier',
	                type: 'json',
	                method: 'post',
	                data: {
	                    action: 'processCarrier',
	                },
	                success: function(data) {
	                    if (data.status == 'success') {
	                        if (data.message.success == false) { /*.preventDefault();	*/
	                            $('#creneau_selected').fancybox({
	                                content: $('#error_slot_msg').text(),
	                            });
	                            $('#creneau_selected').click();
	                            //_createDaysTable();
	                        } else {
	                            $('#form').addClass('ok').submit();
	                        }
	                    } else {
	                        alert(data.message);
	                    }
	                }
	            });
	        }
        }
    });
    
    /*
     * IE FIX !
     */
    if (!String.prototype.padStart) {
	    String.prototype.padStart = function padStart(targetLength,padString) {
	        targetLength = targetLength>>0; //truncate if number or convert non-number to 0;
	        padString = String((typeof padString !== 'undefined' ? padString : ' '));
	        if (this.length > targetLength) {
	            return String(this);
	        }
	        else {
	            targetLength = targetLength-this.length;
	            if (targetLength > padString.length) {
	                padString += padString.repeat(targetLength/padString.length); //append to original to ensure we are longer than needed
	            }
	            return padString.slice(0,targetLength) + String(this);
	        }
	    };
	}
    
    if(opc_checkout == 1)
	{
		_filterStoresByCarrier(0);
    	setTimeout(function(){
    		$('.delivery_option_radio:checked').parents('table').parent().append($('#table_box'));
			_hideOrShow();
    	},1000);
    }
    else
    { 
        //_filterStoresByCarrier(0);
    	$('.delivery_option_radio:checked').parents('table').parent().append($('#table_box'));
		_hideOrShow();
    }
    
    // 2.0.0 : Modal fix on 1.6
    $('body').on('click tap', '.modal .close, .modal .closing', function(){
       $('#store_selector_modal').trigger('click'); 
    });
    
    if (_checkStoresCarrier() > 0) {
        $('body#order-opc').find('#opc_payment_methods #creneau_day').text('');
        $('body#order-opc').find('#opc_payment_methods #creneau_selected').removeClass('alert-success').addClass('alert-warning');
        
        if($('.delivery_option_radio').length > 0) {
            _reInitStore();
        }
            
    }
    
    $('#center_column').on('click tap', '.delivery_option_radio', function(e) {
        if(opc_checkout == 1)
        {
            setTimeout(function(){
                $('.delivery_option_radio:checked').parents('table').parent().append($('#table_box'));
                _filterStoresByCarrier(0);
                _hideOrShow();
            },1000);
        }
        else
        { 
            $('.delivery_option_radio:checked').parents('table').parent().append($('#table_box'));
            //1.4.0 : check of stores <> carriers
            _filterStoresByCarrier(0);
            _hideOrShow();
            if (_checkStoresCarrier() == 0) {
            _reInitStore();
            }
        }
    });
    
    //2.0.0 : Change Slot pour changer de créneau
    $('body').on('click tap', '#creneau_selected .changeSlot', function(){
       
       //console.log('ON SOUHAITE CHANGER DE CRENEAU');
       var id_store = $(this).data('id_store');
       if(id_store > 0)
       {
            _hideReservationSlot(true);
            _createDaysTable(id_store);
            $('body').find('#creneau_day').html('');
            $('body').find('#creneau_selected').removeClass('alert-success').addClass('alert-warning');
            $('body').find('#store_list').hide();
       }
       
    });
    
    //2.0.0 : Change Slot pour changer de magasin
    $('body').on('click tap', '#shop_selected .changeStore', function(){
       _changeStoreAndSlot();
    });

});

function diplayOrHideColumns(current_start_column, current_nb_display_column, nb_colmuns) {
    for (var i = 2; i <= nb_colmuns + 1; i++) {
        if (i < current_start_column || i >= current_start_column + current_nb_display_column) {
            $('#table_creneau tr td:nth-child(' + i + ')').hide();
        } else {
            $('#table_creneau tr td:nth-child(' + i + ')').show();
        }
    }
}

function _createDaysTable(id_store) {
    $('#table_dispo_overlay').fadeIn();
    if(opc_checkout == 1)
    {
        $('#order-opc').find('input.delivery_option_radio:checked').parents('table').parent().append($('#table_box'));
    }
    else
    {
        $('.delivery_option_radio:checked').parents('table').parent().append($('#table_box'));
    }

    if (_checkStoresCarrier() > 0) {
        $.ajax({
            url: $('#table_dispo').data('url'),
            action: 'initTable',
            type: 'json',
            method: 'post',
            data: {
                action: 'initTable',
                id_store: id_store,
                init_bo: 0,
            },
            success: function(data) {

                if (data.status == 'success') {
                    
                    // On affiche en fonction du mode d'affichage retenu
                    if(data.message.display_table == 1)
                    {
                        _viewList(data.message.table_days);
                        
                        var size = $('body').find('#list_creneau').data('size');
                        $('body').find('#list_creneau').width(size*230+'px');
                    } 
                    else
                    {
                        _viewTable(data.message.table_days);
                        nb_colmuns = data.message.table_days.nb_days_view;
                        diplayOrHideColumns(current_start_column, current_nb_display_column, nb_colmuns);
                        changeclass(data.message.table_days);
                        carrence(data.message.table_days);
                        disabledButtonsDays();
                        reserved(data.message.table_days);  
                        vacation(data.message.table_days);   
                        _adjustTableDisplay();                  
                    }
                    
                    // On affiche en fonction du mode d'affichage retenu
                    if(data.message.display_table == 1)
                    {
                        changeclass(data.message.table_days);
                        adjustListDisplay(data.message.table_days, data.message.hide_empty_days);
                        
                        // 2.0.2
                        var size = $('body').find('#list_creneau td').length;
                        $('body').find('#list_creneau').width(size*230+'px');

                        $('#table_legend, #nav_buttons').hide().remove();
                        reserved(data.message.table_days);
                        vacation(data.message.table_days);
                    }
                    else 
                    {
                        var nbr_days = Object.keys(data.message.table_days.days).length;
                        if(nbr_days > 7)
                        {
                            $('#nav_buttons').show();  
                        }
                        else
                        {
                            $('#nav_buttons').hide().remove();
                        }
                    }
                    
                    if(data.message.creneau != null) {
                        $('#creneau_day').html(data.message.creneau);
                        var creneau = data.message.creneau;
                        var infos = creneau.split(" ");
                        $('#table_dispo td[data-date="'+infos[0]+' '+infos[1]+' '+infos[2]+'"][data-hour="'+infos[4]+'"]').addClass('selected');
                        $('#table_box').attr('data-creneau', '1');
                    }
                    else
                    {
                        $('#table_box').attr('data-creneau',0);
                    }
                    
                    if(data.message.display_table == 1)
                    {
                        //1.2.1
                        if($('body').find('#creneau_valid').hasClass('active'))
                        {
                            $([document.documentElement, document.body]).animate({
                                scrollTop: $('#creneau_valid').offset().top-15
                            },1000);
                        }
                        else
                        {
                            $([document.documentElement, document.body]).animate({
                                scrollTop: $('#table_creneau').offset().top-105
                            },1000);
                        }

                    }
                    else
                    {
                        //1.2.2
                        if($('#creneau_selected').length > 0)
                        {
                            $([document.documentElement, document.body]).animate({
                                scrollTop: $('#table_dispo').offset().top-65
                            },1000);
                        }
                    }
                    
                    setTimeout(function(){
                        //console.log('trigger');
                        $('#table_dispo_overlay').fadeOut();
                    },500);
                    
                    // 2.2.0 : Si aucun créneau disponible on affiche le message, sinon on le masque
                    //$('#no_slot_available').appendTo('#table_dispo tbody tr td');
                    if($('#table_dispo .dispo').length == 0)
                    {
                        $('#no_slot_available').slideDown();
                    }
                    else
                    {
                        $('#no_slot_available').slideUp();
                    }
                    
                } else {
                    alert(data.message);
                }
            }
        });
    }
    _hideOrShow();
}

function _adjustTableDisplay()
{
    // Adjut Table Display 
    $('body').find('#table_creneau tbody tr').each(function(){
        if($(this).children('td.dispo').length == 0)
        {
            $(this).css('height','1px').css('padding','0');
            $(this).find('td').css('height','1px').css('padding','0');
            $(this).find('td').text('');
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

function _viewStore(message) {
    
    var store = message.store;
    var id_lang = message.id_lang;
    if(message.store != undefined) {
        var html = '';
        html += '<div id="store_selected">';
        html += '<input type="hidden" name="valueIdStore" value="' + store.id + '"/>';
        if (id_lang > 0) {
            html += '<span>' + store.name[id_lang] + '<br/>' + store.address1[id_lang] + '<br/>';
            if(store.address2[id_lang] != '')
                html += store.address2[id_lang] + '<br/>';
        }
        else
        {
            html += '<span>' + store.name + '<br/>' + store.address1 + '<br/>';
            if(store.address2 != '')
                html += store.address2 + '<br/>';
        }
        html += store.postcode + ' ' + store.city + '<br/>';
        if (message.phone) {
            html += store.phone + '<br/>';
        }
        html += '</span>';
        html += '</div>';
        $('#shop').html(html);
    }
    else {
        $('#shop').html('');
        $('#store_list li.clickable').removeClass('active_store');
    }
    
    if($('#store_selector_active').length == 0)
    {
        if($('body').find('#store_list .clickable').length == 1) {
            setTimeout(function(){
                //console.log('trigger');
                $('body').find('#store_list .clickable').trigger('click');
            },500);
        }
    }
}

function _hideOrShow() {
    //console.log($('.delivery_option_radio:checked').val());
    if (_checkStoresCarrier() > 0 && $('.delivery_option_radio:checked').val() != 'undefined') {
        $('#shop_selected, #creneau_selected, #store_list, #table_creneau, #table_box, #table_dispo').show();
        
   } else {
        $('#shop_selected, #creneau_selected, #store_list, #table_creneau, #table_box, #table_dispo').hide();
        $('#table_box').attr('data-creneau','');
   }
   
   if (_checkStoresCarrier() > 0) {
       $('body#order-opc').find('#opc_payment_methods #creneau_day').text('');
       $('body#order-opc').find('#opc_payment_methods #creneau_selected').removeClass('alert-success').addClass('alert-warning');
   }
   
   if($.trim($('body').find('#HOOK_TOP_PAYMENT #creneau_day').text()) != '')
    {
        $('body').find('#HOOK_TOP_PAYMENT #creneau_selected').show();
    }
    //$('#table_dispo_overlay').hide();
}

function _reInitStore() {
    //$('#table_dispo_overlay').fadeIn();
    $.ajax({
        url: $('#table_dispo').data('url'),
        action: 'reInitStore',
        type: 'json',
        method: 'post',
        data: {
            action: 'reInitStore',
        },
        success: function(data) {
            if (data.status == 'success') {
                //viewStore(data.message.store);
                $('#creneau_day').html('');
                $('#table_box').attr('data-creneau', '0');
                $('#table_dispo_overlay').hide();
                $('#store_list li.clickable').removeClass('active_store');
                $('#table_creneau').remove();
                $('#creneau_selected').removeClass('alert-success').addClass('alert-warning');
                $('#shop_selected').removeClass('alert-success').addClass('alert-warning');
                $('#table_dispo_overlay').hide();
                $('#table_dispo_head').hide();
                $('#table_legend, #nav_buttons').hide();
                _filterStoresByCarrier(0);

            } else {
                alert(data.message);
            }
        }
    });
    
    $('body').on('click tap', '#next_days', function() {
        current_start_column = Math.min(current_start_column + current_nb_display_column, nb_colmuns - current_nb_display_column + 2);
        diplayOrHideColumns(current_start_column, current_nb_display_column, nb_colmuns);
        if(parseInt(current_start_column+current_nb_display_column) >= nb_colmuns)
            $('#next_days').addClass('selected');
            
        $('#prev_days').removeClass('selected');
    });
    
    $('body').on('click tap', '#prev_days', function() {
        current_start_column = Math.max(2, current_start_column - current_nb_display_column);
        diplayOrHideColumns(current_start_column, current_nb_display_column, nb_colmuns);
        if(current_start_column <= current_nb_display_column)
            $('#prev_days').addClass('selected');
            
        $('#next_days').removeClass('selected');
    });
}

function _selectStore(id_store) { /*$('.delivery_option_radio:checked').parents('table').parent().append($('#table_box'));*/
    $('#table_dispo_overlay').fadeIn();
    $.ajax({
        url: $('#table_dispo').data('url'),
        action: 'assignStore',
        type: 'json',
        method: 'post',
        data: {
            action: 'assignStore',
            store: id_store,
            id_carrier: $('#id_carrier').val(),
        },
        success: function(data) {
            if (data.status == 'success') {
                _viewStore(data.message);
                
                if(data.message.slot_enabled == 1)
                {
                    if(data.message.msg != null && id_store == data.message.store.id && data.message.crenau_valid == true)
                    {
                        $('body').find('#creneau_day').html(data.message.msg);
                        $('body').find('#creneau_selected').removeClass('alert-warning').addClass('alert-success');
                        $('#table_box').attr('data-creneau', '1');
                        _hideOrShow();
                        $('body').find('#table_dispo_head, #table_dispo, #table_legend, #nav_buttons').hide();
                        $('#table_dispo_overlay').hide();
                    }
                    else
                    {
                        if(data.message.crenau_valid == false)
                        {
                            $('body').find('#creneau_valid').addClass('active').show();
                        }

                        _createDaysTable(id_store);
                        $('#creneau_selected').removeClass('alert-success').addClass('alert-warning');
                        $('#creneau_day').html('');
                        $('#table_box').attr('data-creneau', '0');
                        $('#table_dispo_head, #table_dispo').show();
                        $('#table_legend, #nav_buttons').show();
                    }
                    $('body').find('#store_list').hide();
                    
                    //2.0.1
                    if($('body').find('#nbr_stores').val() == 1)
                    {
                        $('body').find('#shop_selected button').hide();
                    }
                    else
                    {
                        $('body').find('#shop_selected button').show();
                    }
                }
                else
                {
                    $('#table_dispo_overlay').hide();
                    $('#table_legend, #nav_buttons').hide().remove();
                    $('#creneau_selected').remove();
                    $('#creneau_day').html(-1);
                    $('#table_box').attr('data-creneau', -1);
                    $('button[name=confirmDeliveryOption]').show();
                    $('#checkout-payment-step').removeClass('no_slot_checked');
                    
                }
                
                if(id_store > 0) {
                    $('#choose_store_msg').hide();
                    $('#shop_selected').removeClass('alert-warning').addClass('alert-success');
                    $('#shop_selected').slideDown();
                    $('body').find('#store_list').hide();
                }
                
            } else {
                alert(data.message);
            }
        }
    });
}

function disabledButtonsDays() {
    var date = new Date();
    var date_today = $.format.date(date, "E d MMM yyyy");
    $('#prev_days, #next_days').removeClass('disabled');
    if ($('#table_creneau > thead > tr > td:nth-child(2)').html() == date_today) {
        $('#prev_days').addClass('disabled');
    }
}

function _viewTable(message) {
    var html = '';
    html += '<table id="table_creneau" class="col-sm-12"><thead>';
    html += '<td>H/J</td>';
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

function adjustListDisplay(message, hide_empty_days) {
    
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
        
        // 2.0.2 : masquage des jours fermés
        if(hide_empty_days == 1)
        {
            $(this).parent().remove();
        }
        else
        {
            $(this).addClass('disabled');
        }

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
    
    $('#nav_buttons').hide().remove();
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
            var date_creneau_format = datetime_creneau.substr(0,10);
            //console.log([datetime_creneau,date_creneau_format,message.vacations[j].vacation_start,message.vacations[j].vacation_end]);
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
                if(message.creneau_limit > 0)
                {
                    if(message.reserved[key]/message.creneau_limit >= 0.50)
                    {
                        $('[data-datetime="'+key+'"]').addClass('busy');
                    }
                }
            } else if (message.reserved[key] >= message.creneau_limit) {
                $('[data-datetime="'+key+'"]').removeClass('dispo').addClass('red indispo');
            }
        }
    }
}

function refresh(time) {
    setTimeout(function () { window.location.reload(); }, time*1000);
}

// 1.4.0 : check stores <> carrier
function _checkStoresCarrier()
{
    var count_stores = 0;
    if($('input[data-id_store]').length > 0)
    {
        $('input[data-id_store]').each(function(){
            if($('.delivery_option_radio:checked').val() == $(this).val() + "," 
            || $('.delivery_option_radio:checked').val() == $(this).val())
            {
                count_stores++;
            }
        });
    }
    
    // 2.0.0
    if($('#store_selector_modal .store_list li.active').length == 1)
    {
        count_stores++;
    }
    
    return count_stores;
}

function _filterStoresByCarrier(force)
{
    //$('#table_dispo_overlay').fadeIn();
    $('#store_list').hide();
    $.ajax({
        url: $('#table_dispo').data('url'),
        action: 'filterStoresByCarrier',
        type: 'json',
        method: 'post',
        data: {
            action: 'filterStoresByCarrier',
            id_carrier: $('.delivery_option_radio:checked').val(),
            force: force,
        },
        success: function(data) {
            if (data.status == 'success') {
                // 2.0.0
                //console.log('rechargement forcé ? : ' + force);
                if(data.message.id_store != null && force == false)
                {
                    //console.log('select_store');
                    $('#store_list').html('').slideUp();
                    //$('#store_list').html('on a déjà un magasin sélectionné');
                    _selectStore(data.message.id_store);
                    
                    // On vérifie ensuite si un créneau est déjà réservé ou non (si oui on l'affiche, sinon on affiche le tableau des dispos)
                }
                else
                {
                    var stores = data.message.tpl;
                    $('#store_list').html(stores);
                    
                    if($('#store_selector_active').length == 0)
                    {
                        if($('body').find('#store_list .clickable').length == 1) {
                            setTimeout(function(){
                                $('body').find('#store_list .clickable').trigger('click');
                                //@TODO : Gérer ça côté PHP si qu'un seul résultat, le sélectionner d'office
                            },500);
                        }
                    }
                    $('body').find('#creneau_selected, #table_dispo_head, #table_dispo, #shop_selected, #hd_legend, #nav_buttons').hide(); 
                    $('#choose_store_msg').show(); 
                    $('#store_list').slideDown();
                }
                
                // 2.0.2
                console.log(data.message.nbr_stores);
                $('body').find('#nbr_stores').val(data.message.nbr_stores);
                
            } else {
               // alert(data.message);
            }
        }
    });
}
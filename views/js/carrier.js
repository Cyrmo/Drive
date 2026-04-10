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

$(document).ready(function() {
    $('#active_js').addClass('disabled');
    $('button[name=processCarrier]').css("display", "block");
    $('#extra_carrier').css("display", "block");
    $('#delivery_options_address').css("display", "block");
    $('#HOOK_PAYMENT').css("display", "block");
    $('#form > div.order_carrier_content > div.box').css("display", "block");
    $('.delivery_options_address').css("display", "block");
    $('button[name=confirmDeliveryOption]').hide();
    $('#verifyCreneau').appendTo('#js-delivery');
    $('#nav_buttons').hide();
    $('#prev_days').addClass('selected');
    // 1.4.0
    $('#carrier_message').hide();
    $('#store_list').hide();
    // 2.0.0
    $('#store_selector_modal').appendTo('body');
    
    setTimeout(function(){
        _hideOrShow();
       	if($('#checkout-payment-step').hasClass('-current'))
       	{
       	    // FIX NO CARRIER : 1.3.1
            if($('body').find('#creneau_selected #creneau_day').text().trim() == '') { 
                $('#creneau_selected').hide(); 
            }
        }	
   	
    }, 500);
   
    // 1.4.0 : Ajout d'informations sur les produits du panier
    if($('body').attr('id') == 'cart')
    {
        $('.carriers_list').each(function(){
            
            $(this).appendTo($(this).parents('li'));
        });
        setTimeout(function(){
            $('.carriers_list').slideDown();
        }, 100)
    }
   
    // On recharge la page si elle reste affichée plus de 10 minutes pour éviter que le créneau ne devienne obsolète si on reste sur la page
    refresh(600);
    
    $('.delivery-option input[type=radio]:checked').parents('.delivery-option').next().append($('#table_box'));
    
    $('#checkout-payment-step').on('click tap', function(e){
    	if($(this).hasClass('no_slot_checked'))
    	{
    		$('#checkout-delivery-step span.step-edit').trigger('click');
    	}
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
    
    // 1.4.0 : Check si on a au moins un transporteur commun
    if($('#checkout-delivery-step').hasClass('-current') || $('#checkout-delivery-step').hasClass('js-current-step'))
    {
        // Si on a aucun transporteur de disponible, on supprime tout et on affiche le message
        if($('#common_carriers').length == 1 && $('#common_carriers').val() == 0)
        {
            $('.delivery-options-list').hide().remove();
            $('#carrier_message').fadeIn();
        }
        // On vérifie également s'il y a un transporteur éligible
        else {
            $('.delivery-option input[type=radio]:checked').parents('.delivery-option').next().append($('#table_box'));
            setTimeout(function(){
                _filterStoresByCarrier(0);
                _hideOrShow();
                //console.log(_checkStoresCarrier());
                if (_checkStoresCarrier() == 0) {
                    _reInitStore();
                }
            }, 500);
        }
    }
    
    // 1.4.0 : test
    $('#cart').on('click tap', '.cart-item .qty', function() {
        //console.log('qty');    
    
    });
    
    $('#cart').on('click tap', '.remove-from-cart', function(){
        //console.log('delete');
    });

    $('body').on('click tap', '.modal-backdrop, .modal-content .close', function() {
        $('#modal_creneau, #modal, .modal-backdrop').removeClass('in').addClass('out').hide();
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
    
    $('body').on('click tap', '#table_creneau td.dispo', function() {
        $('#table_creneau td').removeClass('selected');
        $('#table_dispo_overlay').fadeIn();
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
                $('#table_dispo_overlay').hide();
                // 2.0.0 : On valide la réservation côté front
                _validateSlotReservation();
               
            } else {
                //alert('error');
            }
            if($('#creneau_selected').length > 0)
                {
                // 2.1.0
                $('body').find('#creneau_valid').slideUp();
                $([document.documentElement, document.body]).animate({
                	scrollTop: $('#creneau_selected').offset().top-105
                },1000 );
            }
        }).fail(function() {
            //alert('An error has occured, please check your HTTPS parameters on Prestashop Back-office');
        });
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
                
                // 2.0.0 : On valide la réservation côté front
                _validateSlotReservation();
                
                if($('#creneau_selected').length > 0)
                {
                    // 2.1.0
                    $('body').find('#creneau_valid').slideUp();
                    $([document.documentElement, document.body]).animate({
                        scrollTop: $('#creneau_selected').offset().top-105
                    },1000 );
                }
               
            } else {
                //alert('error');
            }
        }).fail(function() {
            //alert('An error has occured, please check your HTTPS parameters on Prestashop Back-office');
        });
    });
    
    // 1.2.6
    $('body').on('click tap', '#checkout-delivery-step h1', function() {
        _reInitStore();
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
    
    $('#verifyCreneau').on('click tap', function(e) {
        var creneau = $('#table_box').attr('data-creneau');
        var msg_error = null;
        
        if (_checkStoresCarrier() > 0) {
            if ($('#table_box').attr('data-creneau') == 0 && ($('#store_list li.active_store').length == 0 && $('body').find('#shop_selected').hasClass('alert-warning'))) {
                msg_error = $('#choose_store_msg').text(); 
                $('#modal_creneau.modal').addClass('in').show().find('.js-modal-content').html(msg_error);
                $('body').append('<div class="modal-backdrop in fade"></div>');
                e.preventDefault();
            } else if ($('#store_list li.active_store').length == 0 && $('body').find('#shop_selected').hasClass('alert-warning')) {
                msg_error = $('#choose_store_msg').text();
                $('#modal_creneau.modal').addClass('in').show().find('.js-modal-content').html(msg_error);
                $('body').append('<div class="modal-backdrop in fade"></div>');
                e.preventDefault();
            } else if ($('#table_box').attr('data-creneau') == 0) {
                msg_error = $('#choose_slot_msg').text();
                $('#modal_creneau.modal').addClass('in').show().find('.js-modal-content').html(msg_error);
                $('body').append('<div class="modal-backdrop in fade"></div>');
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
		                    if (data.message.success == false) {
		                       $('.modal').addClass('in').show().find('.js-modal-content').html($('#error_slot_msg').text());
		                       $('body').append('<div class="modal-backdrop in fade"></div>');
		                       //_createDaysTable();
		                    } else {
		                       $('button[name=confirmDeliveryOption]').click();
		                    }
		                } else {
		                    //alert(data.message);
		                }
		            }
		        });
            }
        }
    });
    
    $('body').find('#next_days').on('click tap', function() {
        current_start_column = Math.min(current_start_column + current_nb_display_column, nb_colmuns - current_nb_display_column + 2);
        diplayOrHideColumns(current_start_column, current_nb_display_column, nb_colmuns);
        if(parseInt(current_start_column+current_nb_display_column) >= nb_colmuns)
            $('#next_days').addClass('selected');
            
        $('body').find('#prev_days').removeClass('selected');
    });
    
    $('body').find('#prev_days').on('click tap', function() {
        current_start_column = Math.max(2, current_start_column - current_nb_display_column);
        diplayOrHideColumns(current_start_column, current_nb_display_column, nb_colmuns);
        if(current_start_column <= current_nb_display_column)
            $('#prev_days').addClass('selected');
            
        $('body').find('#next_days').removeClass('selected');
    });
    
    /*
     * On click on delivery option when drive carrier not checked on loading
     */
    $('body').on('click tap', '.delivery-option input[type=radio]', function(e) {
            $('.delivery-option input[type=radio]:checked').parents('.delivery-option').next().append($('#table_box'));
            //1.4.0 : check of stores <> carriers
            //console.log('select_carrier');
            _filterStoresByCarrier(0);
            _hideOrShow();
            //console.log(_checkStoresCarrier());
            if (_checkStoresCarrier() == 0) {
                _reInitStore();
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
    
    if (_checkStoresCarrier() > 0) {
        if(($.trim($('#checkout-payment-step #creneau_day').text()) == ''))
        {
            if($('#slot_disabled').val() != 1)
            {
                $('#checkout-delivery-step span.step-edit').trigger('click');
                $('#checkout-payment-step').addClass('no_slot_checked');
                if($('.delivery-option').length > 0) {
                    //1.4.0 : check of stores <> carriers
                    //_filterStoresByCarrier(0);
                    //_reInitStore();
                }
            }
        }
    }
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
                        
                        _adjustScrollDisplay();
                    }
                    else {
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
                    
                    
                    
                    if($('body').attr('id') == 'checkout')
                    {
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
                            
                            _adjustScrollDisplay();
                        }
                        else
                        {
                            //1.2.2
                            $([document.documentElement, document.body]).animate({
                                scrollTop: $('#table_creneau').offset().top-65
                            },1000);
                        }
                    }
                    
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
                    //alert(data.message);
                }
                
                setTimeout(function(){
                    //console.log('trigger');
                    $('#table_dispo_overlay').fadeOut();
                },500);
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
    if (_checkStoresCarrier() > 0) {
        $('#shop_selected, #creneau_selected, #store_list, #table_creneau, #verifyCreneau, #table_dispo').show();
        $('#table_box').slideDown();
        $('button[name=confirmDeliveryOption]').hide();     
    } else {
       $('#shop_selected, #creneau_selected, #store_list, #table_creneau, #table_box, #table_dispo, #verifyCreneau').hide();
       
       if(_checkStoresCarrier() > 0) {
            $('button[name=confirmDeliveryOption]').hide();
       }
       else 
       {
            if($('body').find('#HDverifyCreneau').css('display') == 'none' || $('body').find('#HDverifyCreneau').css('display') == undefined)
                $('button[name=confirmDeliveryOption]').show();
       }
   }
   //$('#table_dispo_overlay').hide();
}

// 1.4.0 : check stores <> carrier
function _checkStoresCarrier()
{
    var count_stores = 0;
    if($('input[data-id_store]').length > 0)
    {
        $('input[data-id_store]').each(function(){
            if($('.delivery-option input[type=radio]:checked').val() == $(this).val() + "," 
            || $('.delivery-option input[type=radio]:checked').val() == $(this).val())
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
                //_viewStore(data.message.store);
                $('#creneau_day').html('');
                $('#table_box').attr('data-creneau', '0');
                $('#store_list li.clickable').removeClass('active_store');
                $('#table_creneau').remove();
                $('#creneau_selected').removeClass('alert-success').addClass('alert-warning');
                $('#shop_selected').removeClass('alert-success').addClass('alert-warning');
                $('#table_dispo_overlay').hide();
                $('#table_dispo_head').hide();
                $('#table_legend, #nav_buttons').hide();
                _filterStoresByCarrier(0);

            } else {
                //alert(data.message);
            }
        }
    });
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
            id_carrier: $('.delivery-option input[type=radio]:checked').val(),
            force: force,
        },
        success: function(data) {
            if (data.status == 'success') {
                // 2.0.0
                //console.log('rechargement forcé ? : ' + force);
                if(data.message.id_store != null && force == false)
                {
                    _selectStore(data.message.id_store);
                    $('#store_list').html('').slideUp();
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
                    else
                    {
                        // 2.1.0 : si on change de carrier, on ne peut pas aller plus loin sans sélection
                        $('#table_box').attr('data-creneau', '0');
                    }
                    $('body').find('#creneau_selected, #table_dispo_head, #table_dispo, #shop_selected, #hd_legend, #nav_buttons, #table_legend').hide(); 
                    $('#choose_store_msg').show();
                    $('#store_list').slideDown();
                }
                
                // 2.0.2
                //console.log(data.message.nbr_stores);
                $('body').find('#nbr_stores').val(data.message.nbr_stores);
            } else {
               // alert(data.message);
            }
        }
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
                        $('body').find('#table_dispo_head, #table_dispo, #table_legend, #nav_buttons, #store_list').hide();
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
                        $('#table_dispo_head').show();
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
                //alert(data.message);
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
    
    _adjustScrollDisplay();
    
    //var the_day = $('#list_creneau span.active').data('date');
    //$('#table_creneau tr td span[data-date="'+the_day+'"].red').fadeIn(250).css('display','inline-block');
    
    $('#nav_buttons').hide().remove();
}

function _adjustScrollDisplay()
{
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
    setTimeout(function () {
    
        // UPDATE 1.4.0 : Si on est sur l''étape de paiement, on force le choix d'un nouveau créneau après X min
        if($('#checkout-payment-step').hasClass('-current') || $('#checkout-payment-step').hasClass('js-current-step'))
        {
            $('#checkout-delivery-step span.step-edit').trigger('click');
        }
        else
        {
            window.location.reload(); 
        }    
        
    }, time*1000);
}
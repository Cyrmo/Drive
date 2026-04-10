/*
 * Prestatill Drive - Click & Collect
 *
 * Drive Module & Click & Collect for Prestashop
 *
 *  @author    Laurent Baumgartner laurent@prestatill.com
 *  @copyright 2017-2020 Prestatill SAS
 *  @license   Prestatill SAS
 */

var pdc_address = null;

$(document).ready(function() {
    
    // Click sur la géolocalisation
    $('body').on('click tap', '#store_selector_modal i', function(){
        _processGeolocation();        
    });   
    
    // Click sur CHANGE SLOT pour changer de créneau
    $('body').on('click tap', '#store_selector_modal .reserved_slot .changeSlot', function(){
       
       //console.log('ON SOUHAITE CHANGER DE CRENEAU');
       $('body').find('.psd_store_selector').removeClass('active_slot').addClass('active');
       $('body').find('#store_selector_modal .reservation_ok').removeClass('active');
       var id_store = $(this).data('id_store');
       if(id_store > 0)
       {
            _createDaysTable(id_store);
            _hideReservationSlot(true);
       }
       
    });
    
    // Click sur SELECT a store 
    $('body').on('click tap', '#store_selector_modal .store_list button', function(){
        
        pdc_address = '';
        if($(this).hasClass('selectStore'))
        {
            var psd_id_store = $(this).data('id_store');
            
            
            $('#store_selector_modal .store_list li').removeClass('active');
            
            $(this).parents('li').addClass('active');
            $(this).removeClass('selectStore');
        
            $.ajax({
                url: $('#ajax_url').val(),
                action: 'selectStoreFroModal',
                type: 'json',
                method: 'post',
                data: {
                    action: 'selectStoreFroModal',
                    id_store: psd_id_store,
                    },
            }).done(function(response) {
                if (response.status == 'success') {
                    $('body').find('.psd_store_selector').removeClass('active_slot').addClass('active');
                    $('#store_selector_modal .store_list').html(response.message.tpl);
                    //$(this).html(response.message.button);
                    
                    $('body').find('.psd_store_selector span').html(response.message.infos.substring(0,38)+'...');
                    $('#store_selector_modal .store_list').slideDown();
                    $('#store_selector_modal .search_cp').slideUp();
                    
                    // On est sur une fiche produit, on inject l'informatino sur la page pour avoir la mise à jour de l'information
                    if($('body').attr('id') == 'product')
                    {
                        _createDaysTable(psd_id_store);
                        setTimeout(function(){
                            window.location.reload();
                        },500);
                        
                    }
                    else if($('#slot_enabled').val() == 1)
                    {
                        _createDaysTable(psd_id_store);
                        // On vide les zones de réservation de créneau
                        _hideReservationSlot(true);
                    }
                    else
                    {
                        $('#store_selector_modal .modal-footer .closing').trigger('click');
                    }
                } 
            }).fail(function() {
                
            });
        }
        else
        {
            _changeStoreAndSlot();
        }
    });
    
    $('body').on('click tap', '#shop_selected .changeStore', function(){
        _changeStoreOnCarrier();
    });
    
    // Click manuel sur l'icon de localisation
    $('body').on('click tap', '#localisation-icon', function(){
        _processGeolocation();
    });
    
    // Click sur le bouton chooseStore d'une fiche produit
    $('body').on('click tap', '.chooseStore', function(){
        $('body').find('.psd_store_selector').trigger('click');
    });
    
    // Click sur le bouton de la modal
    $('body').on('click tap', '.psd_store_selector', function(){
        
        _manageModal();
        
        if($('#store_selector_modal .store_list li.active').length > 0)
        {
            $('#store_selector_modal .search_cp').hide();
            $('#store_selector_modal .store_list').show();
        }
        else if($('#store_selector_modal #localisation-icon').length > 0)
        {
            _processGeolocation();
        }
        
        // On vide les zones de réservation de créneau
        _hideReservationSlot(false);
        
        if($('.reserved_slot').length > 0)
        {
            if(!$('#store_selector_modal .reservation_ok').hasClass('active'))
            {
                // Si on a pas sélectionné de créneau
                var id_store = $('#store_selector_modal .store_list button').data('id_store');
                if(id_store > 0)
                {
                    _createDaysTable(id_store);
                    _hideReservationSlot(true);
                }
            }
        }
        
        $('#store_selector_modal .search_cp input').focus();
        
    });
    
    // Click sur recherche de CP
    $('body').on('click tap', '#store_selector_modal #search_cp_button', function(){
        
        // Check si mêmes informations pour éviter de renvoyer pour rien 
        if(pdc_address == $('#store_selector_modal .search_cp input').val())
            return;

        pdc_address = $('#store_selector_modal .search_cp input').val();
        
        $('#store_selector_modal .store_list li').removeClass('active');
        
        if(pdc_address != '' && pdc_address.length >= 3)
        {
            $('body').find('#modal_loader').fadeIn();
            $.ajax({
                url: $('#ajax_url').val(),
                action: 'searchCP',
                type: 'json',
                method: 'post',
                data: {
                    action: 'searchCP',
                    address: pdc_address,
                    },
            }).done(function(response) {
                if (response.status == 'success') {
                    $('#store_selector_modal .store_list').html('').html(response.message.tpl);
                    $('#store_selector_modal .search_cp input').val('');
                } else {
                    $('#store_selector_modal .store_list li:first-child').html('').html(response.message.tpl);
                    $('#store_selector_modal .store_list li:first-child').siblings().slideUp().remove();
                }
                
                // On vide les zones de réservation de créneau
                _hideReservationSlot(false);
            
                $('body').find('#modal_loader').fadeOut();
                $('#store_selector_modal .store_list').show();
                $([document.documentElement, document.body]).animate({
                        
                    },1000
                );
            }).fail(function() {
                
            });
        }
    });
    
    // On gère l'appuie sur ENTER pour lancer la recherche de CP
    $('body').on('keydown', '#store_selector_modal .search_cp input', function(e){
        
        if (e.keyCode == 13) // touche entrée
        {
            $('body').find('#store_selector_modal #search_cp_button').trigger('click');
        }
    });
    
    manageStoreSelectorMenu();
    
    $( window ).resize(function() {
        manageStoreSelectorMenu();
    });
    
    $('body').find('.psd_store_selector').hover(
        function() {
            $(this).removeClass('closed');
        }, function() {
            $(this).addClass('closed');
        }
    );
    
    $(window).resize(function() {
        _manageModal();
    });
    
});


// 2.0.0 : Change Store
function _changeStoreAndSlot()
{
    //console.log('on passe ici ?');
    $('#store_selector_modal .search_cp').slideDown();
    $('#store_selector_modal .store_list').slideUp();
    $('#store_selector_modal .search_cp input').focus();
    $('#store_selector_modal .reservation_ok').hide().removeClass('active');
    _hideReservationSlot(false);
    
    if($('#store_selector_modal #localisation-icon').length > 0)
    {
        _processGeolocation();
    }
}

function _changeStoreOnCarrier()
{
    _filterStoresByCarrier(1);
    _hideReservationSlot(false);
    $('body').find('#creneau_day').html('');
    $('body').find('#creneau_selected').removeClass('alert-success').addClass('alert-warning');
    $('body').find('#store_selected').html('');
    $('body').find('#shop_selected').removeClass('alert-success').addClass('alert-warning');
}

// 2.0.0
function _hideReservationSlot(show)
{
    if(show == false)
    {
        $('#table_dispo_head').hide().html('');
        $('#table_dispo').hide().html('');
        $('#store_selector_modal .bookInfos').hide();
        //TEST
        $('#store_list').hide();
    }
    else
    {
        $('#table_dispo_head').slideDown();
        $('#table_dispo').slideDown();
        $('#store_selector_modal .reservation_ok').slideUp();
        $('#store_selector_modal .bookInfos').show();
    }
    _manageModal();
}

function _processGeolocation()
{
    var options = {
      enableHighAccuracy: true,
      timeout: 5000,
      maximumAge: 0
    };
    
    navigator.geolocation.getCurrentPosition(success, error, options);
}

// 2.0.0 : manage modal height
function _manageModal() {
    
    //console.log('modal ouverte... on resize');
    if($('#store_selector_modal').length > 0)
    {
        // modal-header + modal-footer + margin-top + margin-bottom = 184
        var modal_height = parseInt($(window).height()-184);
        
        // Responsive 
        if($(window).width() < 576)
        {
            modal_height -= 40;
        }
        
        $('#store_selector_modal .modal-body').css('max-height',modal_height);
        
    }    
}

function success(pos) {
    
    $('body').find('#modal_loader').fadeIn();
    
    var crd = pos.coords;
    var lat = crd.latitude;
    var long = crd.longitude;
  
    $.ajax({
        url: $('#ajax_url').val(),
        action: 'geolocCustomer',
        type: 'json',
        method: 'post',
        data: {
            action: 'geolocCustomer',
            lat: lat,
            long: long,
            },
    }).done(function(response) {
        if (response.status == 'success') {
            $('#store_selector_modal .store_list').html('').html(response.message.tpl);
            $('#store_selector_modal .bookInfos').hide();
        } else {
            $('#store_selector_modal .store_list li:first-child').html('').html(response.message.tpl);
            $('#store_selector_modal .store_list li:first-child').siblings().slideUp().remove();
            
            // On vide les zones de réservation de créneau
            _hideReservationSlot(false);
        }
        $('body').find('#modal_loader').fadeOut();
        $('#store_selector_modal .store_list').slideDown();
        $([document.documentElement, document.body]).animate({
                
            },1000
        );
    }).fail(function() {
        
    });

  //console.log(`Latitude : ${crd.latitude}`);
  //console.log(`Longitude : ${crd.longitude}`);
}

// 2.0.0 : Gestion du menu storeSelector
function manageStoreSelectorMenu()
{
    if($('body').find('.psd_store_selector').hasClass('fixed'))
        $('body').find('.psd_store_selector').appendTo('body');
        
    setTimeout(function(){
        $('body').find('.psd_store_selector').addClass('closed');
    },2000);
    
}

function _validateSlotReservation()
{
    //console.log('ON A CHOISIT UN CRENEAU');
    
    var url =  $('#table_dispo').data('url');
    if($('#ajax_url').val() != undefined)
    {
        var url = $('#ajax_url').val();
    }
        
        $('body').find('#modal_loader').fadeIn();
        $.ajax({
            url: url,
            action: 'validateSlotReservation',
            type: 'json',
            method: 'post',
            data: {
                action: 'validateSlotReservation',
                },
        }).done(function(response) {
            if (response.status == 'success') {
                $('#store_selector_modal .reserved_slot .reservation_ok').addClass('active').slideDown();
                $('#store_selector_modal .reserved_slot .reservation_ok span').html(response.message.message_creneau);
                $('#store_selector_modal .reserved_slot .reservation_ok h4 b').html(response.message.duration);
                // On ajoute l'information du store sur le bouton de modification de créneau
                $('body').find('#store_selector_modal .reserved_slot .reservation_ok .changeSlot').data('id_store', response.message.id_store);
                
                // On modifie l'affichage de l'information dans le header
                $('body').find('.psd_store_selector').addClass('active_slot');
                $('body').find('.psd_store_selector span').html(response.message.message_creneau);
                
                // 2.0.2
                $('body').find('#creneau_selected .changeSlot').data('id_store', response.message.id_store);
                
                // On vide les zones de réservation de créneau
                _hideReservationSlot(false);
            } else {
                //$('#store_selector_modal .reserved_slot .reservation_ok').html(response.message.tpl);
            }
            $('body').find('#modal_loader').fadeOut();
        }).fail(function() {
            
        });
}

function error(err) {
    
  console.warn('ERREUR (${err.code}): ${err.message}');
  
}
/**
 * DFS Click & Collect Front JS - Pure PrestaShop 9 Approach
 */

var dfsErrorMessage = "Merci de bien sélectionner le lieu de retrait, la date et le créneau horaire de retrait";

function checkNativeDfsValid() {
    if (typeof dfs_trigger_carrier === "undefined") return true;
    var radio = $("input[name^=\u0022delivery_option[\u0022][value^=\u0022" + dfs_trigger_carrier + "\u0022]");
    if (radio.length === 0) {
        radio = $("input[name^=\u0022delivery_option[\u0022][value=\u0022" + parseInt(dfs_trigger_carrier) + ",\u0022]");
    }
    if (radio && radio.length > 0 && radio.is(":checked")) {
        var container = $(".dfs-clickcollect-container:visible");
        if (container.length === 0) return true;
        
        var store = container.find(".dfs_store").val();
        var date = container.find(".dfs_date").val();
        var time = container.find(".dfs_time").val();
        
        if (store && date && time) {
            return true;
        }
        return false;
    }
    return true; 
}

function showNativeDfsError() {
    var container = $(".dfs-clickcollect-container:visible");
    if (container.length > 0) {
        if ($(".dfs-native-error").length === 0) {
            container.prepend("<div class=\"dfs-native-error\" style=\"color:#d9534f; border:1px solid #d9534f; background-color:#ffebe8; padding:10px; margin-bottom:15px; border-radius:4px; font-weight:bold;\">" + dfsErrorMessage + "</div>");
        }
        $("html, body").animate({ scrollTop: container.closest('.delivery-option').offset().top - 50 }, 500);
    }
}

function getNativeRadio() {
    if (typeof dfs_trigger_carrier === "undefined") return null;
    var radio = $("input[name^=\u0022delivery_option[\u0022][value^=\u0022" + dfs_trigger_carrier + "\u0022]");
    if (radio.length === 0) {
        radio = $("input[name^=\u0022delivery_option[\u0022][value=\u0022" + parseInt(dfs_trigger_carrier) + ",\u0022]");
    }
    return radio;
}

window.checkDfsValid = function() {
    if (checkNativeDfsValid()) {
        $(".dfs-native-error").remove();
    }
};

$(document).ready(function() {
    function rebindNativeDfs() {
        // Obsolete: All events are now document-delegated in displayCarrierExtraContent.tpl
        // so they natively survive PrestaShop partial reloads without manual evaluation loops.
    }

    function initVisibility() {
        var initRadio = getNativeRadio();
        var nativeExtraBox = $(".dfs-clickcollect-container").closest('.carrier__extra-content-wrapper, .carrier-extra-content');
        if (nativeExtraBox.length === 0) nativeExtraBox = $(".dfs-clickcollect-container").parent().parent();

        if (!initRadio || !initRadio.is(":checked")) {
            $(".dfs-clickcollect-container").hide();
        } else {
            $(".dfs-clickcollect-container").show();
        }
    }

    // Call on load
    initVisibility();

    $("body").on("change click", "input[name^=\u0022delivery_option[\u0022], .delivery-option input[type='radio'], .delivery-option label, .delivery-option", function (e) {
        setTimeout(function() {
            var radio = getNativeRadio();

            if (radio && radio.length > 0 && radio.is(":checked")) {
                $(".dfs-clickcollect-container").show();
            } else {
                $(".dfs-clickcollect-container").hide();
            }
        }, 10);
    });

    if (typeof prestashop !== "undefined") {
        prestashop.on("updatedDeliveryForm", function (event) {
            initVisibility();
            
            // Si déjà sélectionné, on relance visuellement
            $('.dfs-clickcollect-container').each(function() {
                var initialStore = $(this).find('.dfs_store').val();
                if (initialStore && parseInt(initialStore) > 0) {
                   $(this).find('.dfs_store_selector').val(initialStore).trigger('change');
                }
            });
        });
    }

    // Extraction de la logique AJAX depuis le TPL pour survivre au nettoyage JS de Hummingbird
    function clearSelection(container, level) {
        var ajax_url = container.data('ajax-url');
        if (!ajax_url) return;
        
        if (level === 'store') {
            container.find('.dfs_date').val('');
            container.find('.dfs_time').val('');
            container.find('.dfs_time_container, .dfs_validation_message').hide();
            
            $.ajax({
                url: ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'saveSelection',
                    id_store: container.find('.dfs_store_selector').val() || 0,
                    date: '',
                    time: ''
                }
            });
        } else if (level === 'date') {
            container.find('.dfs_time').val('');
            container.find('.dfs_time_container, .dfs_validation_message').hide();
            
             $.ajax({
                url: ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'saveSelection',
                    id_store: container.find('.dfs_store_selector').val(),
                    date: container.find('.dfs_date').val(),
                    time: ''
                }
            });
        }
    }
    
    $(document).off('change', '.dfs_store_selector').on('change', '.dfs_store_selector', function() {
        console.error('DFS CHANGE EVENT FIRED');
        var $this = $(this);
        var container = $this.closest('.dfs-clickcollect-container');
        var id_store = $this.val();
        console.error('ID STORE SELECTED:', id_store);
        
        if (!id_store) return;
        
        container.find('.dfs_store').val(id_store);
        clearSelection(container, 'store');
        
        container.find('.dfs_store_details').hide();
        container.find('.dfs_store_details_' + id_store).show();
        
        container.find('.dfs_date_scroll').html('<div style="padding:15px; color:#999;">Chargement des dates...</div>');
        container.find('.dfs_date_container').show();
        
        var ajax_url = container.attr('data-ajax-url') || container.data('ajax-url');
        console.error('AJAX URL IS:', ajax_url);
        if (!ajax_url) return;

        $.ajax({
            url: ajax_url,
            type: 'POST',
            dataType: 'json',
            data: { action: 'getDates', id_store: id_store },
            success: function(res) {
                console.error('DFS AJAX SUCCESS:', res);
                if (res.success && res.dates) {
                    if (res.dates.length === 0) {
                        container.find('.dfs_date_scroll').html('<div style="padding:15px; color:red;">Aucun calendrier n\'est configuré pour ce magasin.</div>');
                        return;
                    }
                    var html = '';
                    $.each(res.dates, function(i, date) {
                        var dayMarkup = date.dayName ? '<div style="font-size:0.85em; font-weight:normal; margin-bottom:3px; opacity:0.8; text-transform:capitalize;">' + date.dayName + '</div>' : '';
                        html += '<div class="dfs-date-btn" data-date="'+date.value+'" style="cursor:pointer; display:inline-block; padding:10px 15px; margin-right:10px; margin-bottom:10px; background:#f8f9fa; border:1px solid #ddd; border-radius:8px; color:#555; transition:all 0.2s ease; text-align:center; vertical-align:top; min-width:90px;">' + dayMarkup + '<div style="font-weight:bold; font-size:1.05em;">' + date.label + '</div></div>';
                    });
                    container.find('.dfs_date_scroll').html(html);
                } else {
                    container.find('.dfs_date_scroll').html('<div style="padding:15px; color:red;">Aucune date disponible.</div>');
                }
            },
            error: function(xhr, status, error) {
                container.find('.dfs_date_scroll').html('<div style="padding:15px; color:red; font-size:12px;">Erreur réseau AJAX : '+error+'. Veuillez recharger la page.</div>');
                console.error('DFS Click Collect AJAX Error:', error, xhr.responseText);
            }
        });
    });
    
    $(document).off('click', '.dfs-date-btn').on('click', '.dfs-date-btn', function() {
        var $this = $(this);
        var container = $this.closest('.dfs-clickcollect-container');
        container.find('.dfs-date-btn').css({ background: '#f8f9fa', color: '#555', borderColor: '#ddd' });
        $this.css({ background: '#2cb1c1', color: '#fff', borderColor: '#2cb1c1' });
        var dateObj = $this.data('date');
        container.find('.dfs_date').val(dateObj);
        
        clearSelection(container, 'date');
        
        container.find('.dfs_time_container').show();
        container.find('.dfs_time_scroll').html('<div style="padding:15px; color:#999;">Chargement des créneaux...</div>');
        
        var ajax_url = container.data('ajax-url');
        if (!ajax_url) return;

        $.ajax({
            url: ajax_url,
            type: 'POST',
            dataType: 'json',
            data: { action: 'getSlots', id_store: container.find('.dfs_store_selector').val(), date: dateObj },
            success: function(res) {
                if (res.success && res.slots) {
                    var html = '';
                    if (res.slots.length === 0) {
                        html = '<div style="padding:15px; color:red;">Aucun créneau ce jour là.</div>';
                    } else {
                        $.each(res.slots, function(i, slot) {
                            html += '<div class="dfs-time-btn" data-time="'+slot.hour+'" style="cursor:pointer; display:inline-block; padding:8px 16px; margin-right:10px; background:#f8f9fa; border:1px solid #ddd; border-radius:20px; color:#555; transition:0.2s;">' + slot.hour + '</div>';
                        });
                    }
                    container.find('.dfs_time_scroll').html(html);
                }
            },
            error: function(xhr, status, error) {
                container.find('.dfs_time_scroll').html('<div style="padding:15px; color:red; font-size:12px;">Erreur réseau AJAX créneaux.</div>');
            }
        });
    });
    
    $(document).off('click', '.dfs-time-btn').on('click', '.dfs-time-btn', function() {
         var $this = $(this);
         var container = $this.closest('.dfs-clickcollect-container');
         container.find('.dfs-time-btn').css({ background: '#f8f9fa', color: '#555', borderColor: '#ddd' });
         $this.css({ background: '#2cb1c1', color: '#fff', borderColor: '#2cb1c1' });
         var timeVal = $this.data('time');
         container.find('.dfs_time').val(timeVal);
         
         var ajax_url = container.data('ajax-url');
         if (!ajax_url) return;

         /* Save Complete Selection */
         $.ajax({
            url: ajax_url,
            type: 'POST',
            dataType: 'json',
            data: { 
                action: 'saveSelection', 
                id_store: container.find('.dfs_store_selector').val(), 
                date: container.find('.dfs_date').val(),
                time: timeVal
            },
            success: function(res) {
                if(res.success) {
                    container.find('.dfs_validation_message').slideDown();
                    if(typeof window.checkDfsValid === 'function') window.checkDfsValid();
                }
            }
        });
    });

    /* Initial Load */
    $('.dfs-clickcollect-container').each(function() {
        var initialStore = $(this).find('.dfs_store').val();
        if (initialStore && parseInt(initialStore) > 0) {
           $(this).find('.dfs_store_selector').val(initialStore).trigger('change');
        }
    });

    document.addEventListener("click", function(e) {
        if (typeof jQuery === 'undefined') return;
        var target = e.target.closest("#checkout-delivery-step button[type='submit'], #checkout-delivery-step .continue, button.continue, button[name='confirmDeliveryOption']");
        if (target) {
            if (!checkNativeDfsValid()) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                showNativeDfsError();
                return false;
            }
        }
    }, true);
    
    document.addEventListener("submit", function(e) {
        if (typeof jQuery === 'undefined') return;
        var form = e.target.closest("form#js-delivery, #checkout-delivery-step form");
        if (form) {
            if (!checkNativeDfsValid()) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                showNativeDfsError();
                return false;
            }
        }
    }, true);

    setTimeout(rebindNativeDfs, 300);
});

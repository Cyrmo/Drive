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
	
	$('body').on('click tap','#product_days_availability .psd', function() {
		
		$(this).toggleClass('available unavailable');
		
		$.ajax({
            url: $('#product_days_availability').data('url'),
            type: 'json',
            action: 'adminSetProductAvailability',
            method: 'post',
            data: {
                action: 'adminSetProductAvailability',
                id_product: $('#product_days_availability').data('id_product'),
                id_product_attribute: $('#product_days_availability').data('id_product_attribute'),
                id_day: $(this).data('id_day'),
                availability: $(this).hasClass('available')?1:0,
            },
            success: function(data) {

            } 
        });
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
	
});
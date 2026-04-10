/*
* Prestatill Drive - Click & Collect
*
* Drive Module & Click & Collect for Prestashop
*
*  @author    Laurent Baumgartner laurent@prestatill.com
*  @copyright 2017-2020 Prestatill SAS
*  @license   Prestatill SAS
*/

$(document).ready(function() 
{
	$('.menu_tab').click(function()
	{
        $('.menu_tab').removeClass('active');
        $('.tab-pane').removeClass('active');
   		$(this).addClass('active');
	});
	
	$('.hour_open tr>td>input').each(function()
	{
		var id = $(this).attr('data-id');	
		_updateinput(id,false);
	});
	
	if($('#parameter input[name=PRESTATILL_DRIVE_NB_PRODUCTS_DISPO]').prop('checked') == true)
	{
		$('#parameter .nb_dispo .products').show();  		
		$('#parameter .nb_dispo .orders').hide();  	
		$('#parameter .catBox').slideDown();	
	}
	else
	{
		$('#parameter .nb_dispo .orders').show();		
		$('#parameter .nb_dispo .products').hide();	
		$('#parameter .catBox').slideUp();	
	}
   
   $('.hour_open tr>td>input').click(function()
   {
		var id = $(this).attr('data-id');	
		_updateinput(id,false);
	});
	
	$('.hour_open tr td:first-child input[type=checkbox]').click(function()
   	{
   		var id = $(this).attr('data-id');
   		_updateinput(id);
   		
   	});
   		
   	$('.hour_open tr td:last-child input').each(function()
   	{
   		var id = $(this).attr('data-id');
   		_disabledInputWhenNonstopChecked(id);
   	});
   	
   	$('.hour_open tr td:last-child input').click(function()
   	{
   		var id = $(this).attr('data-id');
   		_disabledInputWhenNonstopChecked(id);
   	});	
   		
   $('#PRESTATILL_DRIVE_OPEN').keyup(function()
   {
   		var value = $(this).val();
   		var valueformat = new Date(value);
   });		
   
   $('#add_vacation_button').click(function(e)
   {
   		$('#add_vacation').removeClass('disabled');
   		e.preventDefault();
   		$(this).hide();
   });
   
   $('#parameter_form input, #clickandcollect input').on('click',function()
   {
   		if($(this).prop('checked') == true)
   		{
   			$(this).parent().parent().addClass('active');
   		}
   		else
   		{
   			$(this).parent().parent().removeClass('active');
   		}
   });
   
   $('#submitVacation').click(function(e)
   {
       if ($('#PRESTATILL_DRIVE_VACATION_START').val() == '' && $('#PRESTATILL_DRIVE_VACATION_END').val() == '')
       {
           e.preventDefault();
           alert('Start vacation day and End vacation day is empty');
       }
       else if ($('#PRESTATILL_DRIVE_VACATION_START').val() == '')
       {
           e.preventDefault();
           alert('Start vacation day is empty');
       }  
       else if ($('#PRESTATILL_DRIVE_VACATION_END').val() == '')
       {
           e.preventDefault();
           alert('End vacation day is empty');
       }    
   });  
   
   /* BACK OFFICE */
   store_select();
   
   $('.store_list li').on('click', function(){
   		$('.store_list li').removeClass('active');
   		$(this).addClass('active');
   		store_select();
   });
   
   // On désactive les inputs des drives inactifs
   $('.drive_disabled input').prop('disabled',true);
   
   $('#openingdays').on('change', '.select_date_1', function(){
   		var id_day = parseInt($(this).val());
		$('.select_date_2 option').hide();
		if(id_day > 0)
		{
			if(id_day == 7)
			{
				$('.select_date_2 option[value="'+1+'"]').show();
				$('.select_date_2 option[value="'+7+'"]').show();
				$('.select_date_2 option[value="'+parseInt(1)+'"]').show().prop('selected',true);
			}
			else
			{
				$('.select_date_2 option[value="'+id_day+'"]').show();
				$('.select_date_2 option[value="'+parseInt(id_day+1)+'"]').show().prop('selected',true);
			}
			
		}
		else 
		{
			$('.select_date_2 option[value="0"]').show().prop('selected',true);
		}
		
		//$('.select_date_2 option[value="0"]').prop('disabled',true);
   });
   
   $('#config_form .drive_enabled').on('change', function(){
   	
   		if($(this).val() == 1)
   		{
   			$(this).parents('section').find('.table-responsive input:not(.drive_enabled), .table-responsive select, .table-responsive button').prop('disabled',false);
   			$(this).parents('section').find('.table-responsive').removeClass('drive_disabled');
   		}
   		else
   		{
   			$(this).parents('section').find('.table-responsive input:not(.drive_enabled), .table-responsive select, .table-responsive button').prop('disabled',true);
   			$(this).parents('section').find('.table-responsive').addClass('drive_disabled');
   		}
   });
   
   //$('#openingdays .carence_supp input').prop('disabled',false);
   
   $('#openingdays .carence_supp button').on('click',function(e){
   		$(this).prop('disabled',true);
   		e.preventDefault();
   		var id_store = $(this).data('id_store');
   		var id_lang = $(this).data('id_lang');
   		var id_day = $('#PRESTATILL_CARENCE_SUPP_DAY_'+id_store).val();
   		var id_day_end = $('#PRESTATILL_CARENCE_SUPP_DAY_END_'+id_store).val();
   		var hour_limit = $('#PRESTATILL_CARENCE_SUPP_HOUR_LIMIT_'+id_store).val();
   		var hour_limit_end = $('#PRESTATILL_CARENCE_SUPP_HOUR_LIMIT_END_'+id_store).val();
   		var waiting_time = $('#PRESTATILL_DRIVE_CARENCE_SUPP_'+id_store).val();
   		var id_shop_group = $('#id_shop_group').val();
   		var id_shop = $('#id_shop').val();
   		
   		$('#PRESTATILL_CARENCE_SUPP_HOUR_LIMIT_'+id_store).removeClass('error');
   		$('#PRESTATILL_DRIVE_CARENCE_SUPP_'+id_store).removeClass('error');
   		
   		if(hour_limit == '' || hour_limit == '00:00')
   		{
   			$('#PRESTATILL_CARENCE_SUPP_HOUR_LIMIT_'+id_store).addClass('error');
   		}
   		else if(waiting_time <= 0)
   		{
   			$('#PRESTATILL_DRIVE_CARENCE_SUPP_'+id_store).addClass('error');
   		}
   		else
   		{
   			$.ajax({
	            url: $(this).data('url'),
	            type: 'json',
	            action: 'addCarenceSupp',
	            method: 'post',
	            data: {
	            	action: 'addCarenceSupp',
		            id_day: id_day,
		            id_day_end: id_day_end,
		            id_store: id_store,
		            hour_limit: hour_limit,
		            hour_limit_end: hour_limit_end,
		            waiting_time: waiting_time,
		            id_lang: id_lang,
		            id_shop_group: id_shop_group,
		            id_shop: id_shop,
	            },
	        }).done(function(data) {
	            if (data.status == 'success') {  
	            	$('#carence_supp_'+id_store+' table tbody').html(data.message.tpl);
	            	$('#openingdays .carence_supp tbody tr').hide();
					$('#openingdays #carence_supp_'+id_store+' .carence_supp_td_'+id_store+'').show();
	            } else {
	                
	            }
	        }).fail(function() {
	            alert('Erroor');
	        });
   		}
   		$(this).prop('disabled',false);
   });
   
   $('#openingdays').on('click', '.deleteCarenceSupp', function(e) {
   	
   		e.preventDefault();
   		var id_store = $(this).data('id_store');
   		var id_day = $(this).data('id_day');
   		var id_shop_group = $('#id_shop_group').val();
   		var id_shop = $('#id_shop').val();
   		
   		$.ajax({
            url: $(this).data('url'),
            type: 'json',
            action: 'deleteCarenceSupp',
            method: 'post',
            data: {
            	action: 'deleteCarenceSupp',
	            id_day: id_day,
	            id_store: id_store,
	            id_shop_group: id_shop_group,
		        id_shop: id_shop,
            },
        }).done(function(data) {
            if (data.status == 'success') {  
            	$('#carence_supp_'+id_store+' table tbody').html(data.message.tpl);
            	$('#openingdays .carence_supp tbody tr').hide();
				$('#openingdays #carence_supp_'+id_store+' .carence_supp_td_'+id_store+'').show();
            } else {
                
            }
        }).fail(function() {
            alert('Erroor');
        });
   });
   
   // 1.3.1 PRESTATILL_DRIVE_NB_PRODUCTS_DISPO
    $('#parameter .products_orders').on('change', function(){

   		if($(this).val() == 1)
   		{
			$('#parameter .nb_dispo .products').show();  		
			$('#parameter .nb_dispo .orders').hide();  	
			$('#parameter .catBox').slideDown();	
		}
		else
		{
			$('#parameter .nb_dispo .orders').show();		
			$('#parameter .nb_dispo .products').hide();	
			$('#parameter .catBox').slideUp();	
		}
   });
   
   // 2.0.0 : Store selector <> Slot reservation
   $('input[name="PRESTATILL_DRIVE_ENABLE_STORE_SELECTOR"]').on('change', function(){
       if($(this).val() == 0)
       {
           $('#PRESTATILL_DRIVE_ENABLE_SLOT_RESERVATION_off').trigger('click').prop('disabled',true);
           $('#PRESTATILL_DISPLAY_CARRIERS_ON_CART_off').trigger('click').prop('disabled',true);
           $('#PRESTATILL_DISPLAY_STORES_ON_PRODUCT_off').trigger('click').prop('disabled',true);
       }
       else
       {
           $('#PRESTATILL_DRIVE_ENABLE_SLOT_RESERVATION_on').trigger('click');
           $('#PRESTATILL_DRIVE_ENABLE_SLOT_RESERVATION_off').prop('disabled',false);
           $('#PRESTATILL_DISPLAY_CARRIERS_ON_CART_on').trigger('click').prop('disabled',false);
           $('#PRESTATILL_DISPLAY_CARRIERS_ON_CART_off').prop('disabled',false);
           $('#PRESTATILL_DISPLAY_STORES_ON_PRODUCT_on').trigger('click').prop('disabled',false);
           $('#PRESTATILL_DISPLAY_STORES_ON_PRODUCT_off').prop('disabled',false);
       }
   });
   
   $('input[name="PRESTATILL_DRIVE_ENABLE_SLOT_RESERVATION"]').on('change', function(){
       if($(this).val() == 1)
       {
           $('#PRESTATILL_DRIVE_ENABLE_STORE_SELECTOR_on').trigger('click');
       }
   });
   
   $('input[name="PRESTATILL_DISPLAY_CARRIERS_ON_CART"]').on('change', function(){
       if($(this).val() == 1)
       {
           $('#PRESTATILL_DRIVE_ENABLE_STORE_SELECTOR_on').trigger('click');
       }
   });
   
   $('input[name="PRESTATILL_DISPLAY_STORES_ON_PRODUCT"]').on('change', function(){
       if($(this).val() == 1)
       {
           $('#PRESTATILL_DRIVE_ENABLE_STORE_SELECTOR_on').trigger('click');
       }
   });
     
});

function store_select()
{
	var store_selected = $('.store_list .active').text();
	var id_store_selected = $('.store_list .active').data('id_store');
   $('#openingdays h3 .store_name').text(store_selected);
   
   $('#openingdays section').hide();
   $('#openingdays .carence_supp tbody tr').hide();
   $('#openingdays #config_form_'+id_store_selected+'').show();
   $('#openingdays #carence_supp_'+id_store_selected+' .carence_supp_td_'+id_store_selected+'').show();
   $('#openingdays .drive_disabled .carence_supp select, #openingdays .drive_disabled .carence_supp button').prop('disabled',true);
   $('#openingdays .drive_enabled .carence_supp select, #openingdays .drive_enabled .carence_supp button').prop('disabled',false);
}

function _updateinput(id,check_all = true)
{
	   if ($('.hour_open tr>td>input[data-id='+id+']').prop("checked") == false)
	   {
	   		$('.hour_open .no input[data-id='+id+']').attr('disabled','disabled');
	   		$('.hour_open tr > td > input[data-id='+id+']').attr('value','--');
	   }
	   else if(check_all == true)
	   {
	   		$('.hour_open .no input[data-id='+id+']').prop('disabled', false);
	   		$('.hour_open tr > td > input[name=PRESTATILL_DRIVE_OPENING_AM_'+id+']').attr('value','08:00:00');
	   		$('.hour_open tr > td > input[name=PRESTATILL_DRIVE_CLOSING_AM_'+id+']').attr('value','12:00:00');
	   		$('.hour_open tr > td > input[name=PRESTATILL_DRIVE_OPENING_PM_'+id+']').attr('value','14:00:00');
	   		$('.hour_open tr > td > input[name=PRESTATILL_DRIVE_CLOSING_PM_'+id+']').attr('value','18:00:00');
	   		$('.hour_open tr > td > input[name=PRESTATILL_DRIVE_OPENING_NONSTOP_'+id+']').prop('checked',false).val(0);
	   }
}

function _disabledInputWhenNonstopChecked(id)
{
	if($('#'+id+'_nonstop').prop("checked") == true)
	{
		$('#'+id+'_closing_am').attr('disabled','disabled').val("00:00:00");
		$('#'+id+'_opening_pm').attr('disabled','disabled').val("00:00:00");
	}
	else
	{
		$('#'+id+'_closing_am').prop('disabled', false);
		$('#'+id+'_opening_pm').prop('disabled', false);
	}
}
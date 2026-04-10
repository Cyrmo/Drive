{*
* Prestatill Drive - Click & Collect
*
* Drive Module & Click & Collect for Prestashop
*
*  @author    Laurent Baumgartner laurent@prestatill.com
*  @copyright 2017-2020 Prestatill SAS
*  @license   Prestatill SAS
*}

<h3><i class="icon-car"></i> {l s='Pick-up and slot selector' mod='prestatilldrive'}</h3>
<form role="form" class="form-horizontal"  action="#" method="POST" id="collect_form" name="collect_form">  
	{if $p_version_update < '2.2.0'}
	<div class="alert alert-danger">{l s='Please reset the Drive Module to enable all new features.' mod='prestatilldrive'}</div>
	{/if}
	<div class="new_features_box" class="col-xs-12">
	   <h4>{l s='Welcome to the Drive, Click & Collect Module for Prestashop' mod='prestatilldrive'}<span class="label">{$module_version|escape:'htmlall':'UTF-8'}</span></h4>
	   <p>{l s='To work, you just have to configure some parameters on this page, on "parameters" and "opening days" tabulations on the left side.' mod='prestatilldrive'}</p>
	   <p>{l s='Since the version 2.0.0, the Drive Module includes a "Store selector" and the possibility for your customers to reserve a Pick-up slot.' mod='prestatilldrive'}</p>
       <p>{l s='You can configure this options below.' mod='prestatilldrive'}</p>
       <p><b>{l s='If you encounter any problem regarding the correct functioning of the module with your store, do not hesitate to contact our support.' mod='prestatilldrive'}</b></p>
	</div>
	<h4>{l s='Store selection parameters' mod='prestatilldrive'}</h4>
	<div class="form-group">
	    <div class="alert-info alert">
            {l s='The store selector allows a customer to define their preferred pick-up store, which will be used first for Drive orders.' mod='prestatilldrive'}
            {l s='Only stores linked to a carrier (tabulation parameters) will be displayed to your customers.' mod='prestatilldrive'}
        </div>
        <div class="alert-warning alert">
            {l s='Some parameters below can\'t be activated if the store selector is disabled.' mod='prestatilldrive'}
        </div>
		<label class="control-label col-lg-3" for="PRESTATILL_DRIVE_ENABLE_STORE_SELECTOR">
			<span class="label-tooltip" data-toggle="tooltip" title="Define if you want to display a popup on customer's first visit to select his nearest store.">
				{l s='Enable store selector popup on front' mod='prestatilldrive'}
			</span>
		</label>
		<div class="col-lg-9">
			<div class="col-lg-4">
				<span class="switch prestashop-switch fixed-width-lg">
					<input class="slot_enabled" type="radio" name="PRESTATILL_DRIVE_ENABLE_STORE_SELECTOR" id="PRESTATILL_DRIVE_ENABLE_STORE_SELECTOR_on" value="1" {if $store_selector == 1} checked="checked"{/if}>
						<label for="PRESTATILL_DRIVE_ENABLE_STORE_SELECTOR_on" class="radioCheck">
							{l s='Enabled' mod='prestatilldrive'}
						</label>
					<input class="slot_enabled" type="radio" name="PRESTATILL_DRIVE_ENABLE_STORE_SELECTOR" id="PRESTATILL_DRIVE_ENABLE_STORE_SELECTOR_off" value="0" {if $store_selector == 0} checked="checked"{/if}>
						<label for="PRESTATILL_DRIVE_ENABLE_STORE_SELECTOR_off" class="radioCheck">
							{l s='Disabled' mod='prestatilldrive'}
						</label>
					<a class="slide-button btn"></a>
				</span>
			</div>
		</div>
		<div class="illustration">
            <img src="../modules/prestatilldrive/views/img/badge-2.jpg" />
        </div>
        <div class="form-group">
            <label class="control-label col-lg-3" for="PRESTATILL_SEARCH_STORE">
                <span class="label-tooltip" data-toggle="tooltip" title="Define if you want to show only stores around the customers">
                    {l s='Show only stores around the Customers' mod='prestatilldrive'}
                </span>
            </label>
            <div class="col-lg-9">
                <div class="col-lg-4">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input class="slot_enabled" type="radio" name="PRESTATILL_SEARCH_STORE" id="PRESTATILL_SEARCH_STORE_on" value="1" {if $store_search == 1} checked="checked"{/if}>
                            <label for="PRESTATILL_SEARCH_STORE_on" class="radioCheck">
                                {l s='Enabled' mod='prestatilldrive'}
                            </label>
                        <input class="slot_enabled" type="radio" name="PRESTATILL_SEARCH_STORE" id="PRESTATILL_SEARCH_STORE_off" value="0" {if $store_search == 0} checked="checked"{/if}>
                            <label for="PRESTATILL_SEARCH_STORE_off" class="radioCheck">
                                {l s='Disabled' mod='prestatilldrive'}
                            </label>
                        <a class="slide-button btn"></a>
                    </span>
                </div>
            </div>
        </div>
        <hr>
        <div class="form-group">
            <div class="alert-info alert">
                {l s='The Module is using the free OpenStreetMap API. If you prefer to use the google api, just enter your key below' mod='prestatilldrive'}
            </div>
            <label class="control-label col-lg-3" for="PRESTATILL_SEARCH_RADIUS">
                <span class="label-tooltip" data-toggle="tooltip" title="Define a zone in KM around the customer's delivery Address">
                    {l s='Search store radius' mod='prestatilldrive'}
                </span>
            </label>
            <div class="col-lg-9">              
                <div class="col-lg-3">
                    <div class="input-group">
                        <input type="number" step="1" min="0" name="PRESTATILL_SEARCH_RADIUS" style="text-align: center" id="PRESTATILL_SEARCH_RADIUS" {if $search_radius}value="{$search_radius|escape:'htmlall':'UTF-8'}"{/if}/>          
                        <span class="input-group-addon">km</span>               
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-lg-3" for="PS_API_KEY">
                <span class="label-tooltip" data-toggle="tooltip" title="Enter your Google Maps API KEY if you don't want to use OPENSTREETMAP API">
                    {l s='Google Maps API KEY : ' mod='prestatilldrive'}
                </span>
            </label>
            <div class="col-lg-9">              
                <div class="col-xs-12">
                    <div class="input-group col-xs-12">
                        <input type="text" class="form-control" name="PS_API_KEY" style="text-align: center" id="PS_API_KEY" {if $nb_dispo}value="{$gg_api_key|escape:'htmlall':'UTF-8'}"{/if}/>    
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <div class="form-group">
            <div class="new"><span>{l s='NEW FEATURE' mod='prestatilldrive'}</span></div>
            <label class="control-label col-lg-3" for="PRESTATILL_DISPLAY_STORES_ON_PRODUCT">
                <span class="label-tooltip" data-toggle="tooltip" title="Define if you want to show on which carriers a product is available on product page">
                    {l s='Display available stores for Click & collect on Product page (front)' mod='prestatilldrive'}
                </span>
            </label>
            <div class="col-lg-9">
                <div class="col-lg-4">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input class="slot_enabled" type="radio" name="PRESTATILL_DISPLAY_STORES_ON_PRODUCT" id="PRESTATILL_DISPLAY_STORES_ON_PRODUCT_on" value="1" {if $store_product_display == 1} checked="checked"{/if}>
                            <label for="PRESTATILL_DISPLAY_STORES_ON_PRODUCT_on" class="radioCheck">
                                {l s='Enabled' mod='prestatilldrive'}
                            </label>
                        <input class="slot_enabled" type="radio" name="PRESTATILL_DISPLAY_STORES_ON_PRODUCT" id="PRESTATILL_DISPLAY_STORES_ON_PRODUCT_off" value="0" {if $store_product_display == 0} checked="checked"{/if}>
                            <label for="PRESTATILL_DISPLAY_STORES_ON_PRODUCT_off" class="radioCheck">
                                {l s='Disabled' mod='prestatilldrive'}
                            </label>
                        <a class="slide-button btn"></a>
                    </span>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="new"><span>{l s='NEW FEATURE' mod='prestatilldrive'}</span></div>
            <label class="control-label col-lg-3" for="PRESTATILL_DISPLAY_CARRIERS_ON_CART">
                <span class="label-tooltip" data-toggle="tooltip" title="Define if you want to show on which carriers a product is available on cart resume">
                    {l s='Display available carriers for Click & collect on Cart resume for each products (front)' mod='prestatilldrive'}
                </span>
            </label>
            <div class="col-lg-9">
                <div class="col-lg-4">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input class="slot_enabled" type="radio" name="PRESTATILL_DISPLAY_CARRIERS_ON_CART" id="PRESTATILL_DISPLAY_CARRIERS_ON_CART_on" value="1" {if $carrier_product_display == 1} checked="checked"{/if}>
                            <label for="PRESTATILL_DISPLAY_CARRIERS_ON_CART_on" class="radioCheck">
                                {l s='Enabled' mod='prestatilldrive'}
                            </label>
                        <input class="slot_enabled" type="radio" name="PRESTATILL_DISPLAY_CARRIERS_ON_CART" id="PRESTATILL_DISPLAY_CARRIERS_ON_CART_off" value="0" {if $carrier_product_display == 0} checked="checked"{/if}>
                            <label for="PRESTATILL_DISPLAY_CARRIERS_ON_CART_off" class="radioCheck">
                                {l s='Disabled' mod='prestatilldrive'}
                            </label>
                        <a class="slide-button btn"></a>
                    </span>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
	</div>
    <hr>
    <h4>{l s='Slot reservation parameters' mod='prestatilldrive'}</h4>
	<div class="form-group">
	    <div class="alert-info alert">
            {l s='The slot selector allows a customer to block / book a pick-up slot for during a defined period of time while shopping.' mod='prestatilldrive'}
        </div>
        <label class="control-label col-lg-3" for="PRESTATILL_DRIVE_ENABLE_SLOT_RESERVATION">
            <span class="label-tooltip" data-toggle="tooltip" title="Define if you want to enable or disable the possibility for the customer to reserve a slot for a defined duration.">
                {l s='Enable slot reservation' mod='prestatilldrive'}
            </span>
        </label>
        <div class="col-lg-9">
            <div class="col-lg-4">
                <span class="switch prestashop-switch fixed-width-lg">
                    <input class="slot_enabled" type="radio" name="PRESTATILL_DRIVE_ENABLE_SLOT_RESERVATION" id="PRESTATILL_DRIVE_ENABLE_SLOT_RESERVATION_on" value="1" {if $slot_reservation == 1} checked="checked"{/if}>
                        <label for="PRESTATILL_DRIVE_ENABLE_SLOT_RESERVATION_on" class="radioCheck">
                            {l s='Enabled' mod='prestatilldrive'}
                        </label>
                    <input class="slot_enabled" type="radio" name="PRESTATILL_DRIVE_ENABLE_SLOT_RESERVATION" id="PRESTATILL_DRIVE_ENABLE_SLOT_RESERVATION_off" value="0" {if $slot_reservation == 0} checked="checked"{/if}>
                        <label for="PRESTATILL_DRIVE_ENABLE_SLOT_RESERVATION_off" class="radioCheck">
                            {l s='Disabled' mod='prestatilldrive'}
                        </label>
                    <a class="slide-button btn"></a>
                </span>
            </div>
        </div>
        <div class="illustration">
            <img src="../modules/prestatilldrive/views/img/badge.jpg" />
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-lg-3" for="PRESTATILL_DRIVE_SLOT_RESERVATION_DURATION">
            <span class="label-tooltip" data-toggle="tooltip" title="Define duration in minutes">
                {l s='Slot reservation duration' mod='prestatilldrive'}
            </span>
        </label>
        <div class="col-lg-9">              
            <div class="col-lg-3">
                <div class="input-group">
                    <input type="number" step="1" min="0" name="PRESTATILL_DRIVE_SLOT_RESERVATION_DURATION" style="text-align: center" id="PRESTATILL_DRIVE_SLOT_RESERVATION_DURATION" {if $slot_reservation_duration}value="{$slot_reservation_duration|escape:'htmlall':'UTF-8'}"{/if}/>          
                    <span class="input-group-addon">min</span>               
                </div>
            </div>
        </div>
    </div>
	<div class="clearfix"></div>
			
	<div class="panel-footer">
		 <div class="btn-group pull-right">
            <button name="submitCollect" id="submitCollect" type="submit" class="btn btn-default"><i class="process-icon-save"></i> {l s='Save' mod='prestatilldrive'}</button>
        </div>
   </div>
</form>
{*
* Prestatill Drive - Click & Collect
*
* Drive Module & Click & Collect for Prestashop
*
*  @author    Laurent Baumgartner laurent@prestatill.com
*  @copyright 2017-2020 Prestatill SAS
*  @license   Prestatill SAS
*}

<h3><i class="icon-cogs"></i> {l s='Parameters' mod='prestatilldrive'}</h3>
<form role="form" class="form-horizontal"  action="#" method="POST" id="parameter_form" name="parameter_form">	
	<h4>{l s='General parameters' mod='prestatilldrive'}</h4>
	<div class="form-group">
		<label class="control-label col-lg-3" for="PRESTATILL_DRIVE_ENABLE_SLOT_CHOICE">
			<span class="label-tooltip" data-toggle="tooltip" title="{l s='Define if you want to that your customers can choose a pick-up slot for their order or just their pick-up store.' mod='prestatilldrive'}">
				{l s='Enable Pick-up slot or just store pick-up' mod='prestatilldrive'}
			</span>
		</label>
		<div class="col-lg-9">
			<div class="col-lg-4">
				<span class="switch prestashop-switch fixed-width-lg">
					<input class="slot_enabled" type="radio" name="PRESTATILL_DRIVE_ENABLE_SLOT_CHOICE" id="PRESTATILL_DRIVE_ENABLE_SLOT_CHOICE_on" value="1" {if $slot_enabled == 1} checked="checked"{/if}>
						<label for="PRESTATILL_DRIVE_ENABLE_SLOT_CHOICE_on" class="radioCheck">
							{l s='Enabled' mod='prestatilldrive'}
						</label>
					<input class="slot_enabled" type="radio" name="PRESTATILL_DRIVE_ENABLE_SLOT_CHOICE" id="PRESTATILL_DRIVE_ENABLE_SLOT_CHOICE_off" value="0" {if $slot_enabled == 0} checked="checked"{/if}>
						<label for="PRESTATILL_DRIVE_ENABLE_SLOT_CHOICE_off" class="radioCheck">
							{l s='Disabled' mod='prestatilldrive'}
						</label>
					<a class="slide-button btn"></a>
				</span>
			</div>
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-lg-3" for="PRESTATILL_DRIVE_DISPLAY_TABLE">
		      {l s='Display available slots in a list instead of a table' mod='prestatilldrive'}
		</label>
		<div class="col-lg-9">
			<div class="col-lg-4">
				<span class="switch prestashop-switch fixed-width-lg">
					<input class="slot_enabled" type="radio" name="PRESTATILL_DRIVE_DISPLAY_TABLE" id="PRESTATILL_DRIVE_DISPLAY_TABLE_on" value="1" {if $display_table == 1} checked="checked"{/if}>
						<label for="PRESTATILL_DRIVE_DISPLAY_TABLE_on" class="radioCheck">
							{l s='Yes' mod='prestatilldrive'}
						</label>
					<input class="slot_enabled" type="radio" name="PRESTATILL_DRIVE_DISPLAY_TABLE" id="PRESTATILL_DRIVE_DISPLAY_TABLE_off" value="0" {if $display_table == 0} checked="checked"{/if}>
						<label for="PRESTATILL_DRIVE_DISPLAY_TABLE_off" class="radioCheck">
							{l s='No' mod='prestatilldrive'}
						</label>
					<a class="slide-button btn"></a>
				</span>
			</div>
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-lg-3" for="PRESTATILL_DRIVE_SEND_EMAIL">
		  {l s='Send a separate email with pick up informations' mod='prestatilldrive'}
		</label>
		<div class="col-lg-9">
			<div class="col-lg-4">
				<span class="switch prestashop-switch fixed-width-lg">
					<input class="slot_enabled" type="radio" name="PRESTATILL_DRIVE_SEND_EMAIL" id="PRESTATILL_DRIVE_SEND_EMAIL_on" value="1" {if $send_email == 1} checked="checked"{/if}>
						<label for="PRESTATILL_DRIVE_SEND_EMAIL_on" class="radioCheck">
							{l s='Enabled' mod='prestatilldrive'}
						</label>
					<input class="slot_enabled" type="radio" name="PRESTATILL_DRIVE_SEND_EMAIL" id="PRESTATILL_DRIVE_SEND_EMAIL_off" value="0" {if $send_email == 0} checked="checked"{/if}>
						<label for="PRESTATILL_DRIVE_SEND_EMAIL_off" class="radioCheck">
							{l s='Disabled' mod='prestatilldrive'}
						</label>
					<a class="slide-button btn"></a>
				</span>
			</div>
		</div>
	</div>
	<div class="clearfix"></div>
	<hr>
	<h4>{l s='Stores parameters' mod='prestatilldrive'}</h4>
	<div class="form-group">
		<label class="control-label col-lg-3" for="PRESTATILL_SEND_MAIL_TO_STORE">
			<span class="label-tooltip" data-toggle="tooltip" title="{l s='You must enter a valid email address for each store available for the Drive pick up' mod='prestatilldrive'}">
				{l s='Send an email to the store concerned by the Pick up' mod='prestatilldrive'}
			</span>
		</label>
		<div class="col-lg-9">
			<div class="col-lg-4">
				<span class="switch prestashop-switch fixed-width-lg">
					<input class="slot_enabled" type="radio" name="PRESTATILL_SEND_MAIL_TO_STORE" id="PRESTATILL_SEND_MAIL_TO_STORE_on" value="1" {if $send_mail_to_store == 1} checked="checked"{/if}>
						<label for="PRESTATILL_SEND_MAIL_TO_STORE_on" class="radioCheck">
							{l s='Enabled' mod='prestatilldrive'}
						</label>
					<input class="slot_enabled" type="radio" name="PRESTATILL_SEND_MAIL_TO_STORE" id="PRESTATILL_SEND_MAIL_TO_STORE_off" value="0" {if $send_mail_to_store == 0} checked="checked"{/if}>
						<label for="PRESTATILL_SEND_MAIL_TO_STORE_off" class="radioCheck">
							{l s='Disabled' mod='prestatilldrive'}
						</label>
					<a class="slide-button btn"></a>
				</span>
			</div>
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-lg-3" for="PRESTATILL_JOIN_INVOICE_TO_STORE_EMAIL">
			<span class="label-tooltip" data-toggle="tooltip" title="{l s='If yes, the store owner will receive a copy of the invoice attached to the email' mod='prestatilldrive'}">
				{l s='Join the invoice (if exists) in store\'s email as attached file' mod='prestatilldrive'}
			</span>
		</label>
		<div class="col-lg-9">
			<div class="col-lg-4">
				<span class="switch prestashop-switch fixed-width-lg">
					<input class="slot_enabled" type="radio" name="PRESTATILL_JOIN_INVOICE_TO_STORE_EMAIL" id="PRESTATILL_JOIN_INVOICE_TO_STORE_EMAIL_on" value="1" {if $attach_invoice == 1} checked="checked"{/if}>
						<label for="PRESTATILL_JOIN_INVOICE_TO_STORE_EMAIL_on" class="radioCheck">
							{l s='Enabled' mod='prestatilldrive'}
						</label>
					<input class="slot_enabled" type="radio" name="PRESTATILL_JOIN_INVOICE_TO_STORE_EMAIL" id="PRESTATILL_JOIN_INVOICE_TO_STORE_EMAIL_off" value="0" {if $attach_invoice == 0} checked="checked"{/if}>
						<label for="PRESTATILL_JOIN_INVOICE_TO_STORE_EMAIL_off" class="radioCheck">
							{l s='Disabled' mod='prestatilldrive'}
						</label>
					<a class="slide-button btn"></a>
				</span>
			</div>
		</div>
	</div>
	<hr>
	<h4>{l s='Slots parameters' mod='prestatilldrive'}</h4>
	<div class="form-group">
		<label class="control-label col-lg-3" for="PRESTATILL_DRIVE_CARENCE">
			<span class="label-tooltip" data-toggle="tooltip" title="{l s='Time between receiving the order and the first available delivery slot' mod='prestatilldrive'}">
				{l s='Waiting time :' mod='prestatilldrive'}
			</span>
		</label>
		<div class="col-lg-9">				
			<div class="col-lg-3">
				<div class="input-group">
					<input type="number" name="PRESTATILL_DRIVE_CARENCE" style="text-align: center" id="PRESTATILL_DRIVE_CARENCE" {if $carence}value="{$carence|escape:'htmlall':'UTF-8'}"{/if} />	
					<span class="input-group-addon">min</span>				
				</div>
			</div>
		</div>
	</div>
	<div class="form-group">
	    <div class="new"><span>{l s='NEW FEATURE' mod='prestatilldrive'}</span></div>
        <label class="control-label col-lg-3" for="PRESTATILL_DRIVE_CARRENCE_SUPP_NO_STOCK_DURATION">
            <span class="label-tooltip" data-toggle="tooltip" title="{l s='If the cart contains out of stock product, you can define an additional waiting time before the next pick-up slot' mod='prestatilldrive'}">
                {l s='Additional waiting time for out of stock products :' mod='prestatilldrive'}
            </span>
        </label>
        <div class="col-lg-9">              
            <div class="col-lg-3">
                <div class="input-group">
                    <input type="number" name="PRESTATILL_DRIVE_CARRENCE_SUPP_NO_STOCK_DURATION" style="text-align: center" id="PRESTATILL_DRIVE_CARRENCE_SUPP_NO_STOCK_DURATION" value="{if $awaiting_delay_no_stock}{$awaiting_delay_no_stock|escape:'htmlall':'UTF-8'}{else}0{/if}" />  
                    <span class="input-group-addon">min</span>              
                </div>
            </div>
        </div>
    </div>
	<div class="form-group">
		<label class="control-label col-lg-3" for="PRESTATILL_DRIVE_OPEN">
			<span class="label-tooltip" data-toggle="tooltip" title="Ex: 08:00:00">
				{l s='Opening Time of the drive' mod='prestatilldrive'}
			</span>
		</label>
		<div class="col-lg-9">				
			<div class="col-lg-3">
				<div class="input-group">
					<input type="time" step="2" name="PRESTATILL_DRIVE_OPEN" style="text-align: center" id="PRESTATILL_DRIVE_OPEN" {if $opendrive}value="{$opendrive|escape:'htmlall':'UTF-8'}"{/if}/>			
					<span class="input-group-addon">h</span>				
				</div>
			</div>
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-lg-3" for="PRESTATILL_DRIVE_CLOSE">
			<span class="label-tooltip" data-toggle="tooltip" title="Ex: 20:00:00">
				{l s='Closing Time of the drive :' mod='prestatilldrive'}
			</span>
		</label>
		<div class="col-lg-9">				
			<div class="col-lg-3">
				<div class="input-group">
					<input type="time" step="1" name="PRESTATILL_DRIVE_CLOSE" style="text-align: center" id="PRESTATILL_DRIVE_CLOSE" {if $closedrive}value="{$closedrive|escape:'htmlall':'UTF-8'}"{/if}/>			
					<span class="input-group-addon">h</span>				
				</div>
			</div>
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-lg-3" for="PRESTATILL_DRIVE_NB_DAY">
			<span class="label-tooltip" data-toggle="tooltip" title="Define how much days to display in front when customer will choose a pick up slot">
				{l s='Number of days to display' mod='prestatilldrive'}
			</span>
		</label>
		<div class="col-lg-9">				
			<div class="col-lg-3">
				<div class="input-group">
					<input type="number" name="PRESTATILL_DRIVE_NB_DAY" style="text-align: center" id="PRESTATILL_DRIVE_NB_DAY" {if $nbdayview}value="{$nbdayview|escape:'htmlall':'UTF-8'}"{/if}/>			
					<span class="input-group-addon">{l s='days' mod='prestatilldrive'}</span>				
				</div>
			</div>
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-lg-3" for="PRESTATILL_DRIVE_DUREE">
			<span class="label-tooltip" data-toggle="tooltip" title="Ex: 60 min">
				{l s='Duration of slot (in min) : ' mod='prestatilldrive'}
			</span>
		</label>
		<div class="col-lg-9">				
			<div class="col-lg-3">
				<div class="input-group">
					<input type="number" name="PRESTATILL_DRIVE_DUREE" style="text-align: center" id="PRESTATILL_DRIVE_DUREE" {if $duree}value="{$duree|escape:'htmlall':'UTF-8'}"{/if}/>			
					<span class="input-group-addon">min</span>				
				</div>
			</div>
		</div>
	</div>
	<div class="form-group">
        <div class="new"><span>{l s='NEW FEATURE' mod='prestatilldrive'}</span></div>
        <label class="control-label col-lg-3" for="PRESTATILL_DRIVE_HIDE_EMPTY_DAYS">
            <span class="label-tooltip" data-toggle="tooltip" title="Define if you want to display days which don't have any slot available or not">
                {l s='Hide days that contains no available pick-up slots (only on list mode)' mod='prestatilldrive'}
            </span>
        </label>
        <div class="col-lg-9">
            <div class="col-lg-4">
                <span class="switch prestashop-switch fixed-width-lg">
                    <input class="slot_enabled" type="radio" name="PRESTATILL_DRIVE_HIDE_EMPTY_DAYS" id="PRESTATILL_DRIVE_HIDE_EMPTY_DAYS_on" value="1" {if $hide_empty_days == 1} checked="checked"{/if}>
                        <label for="PRESTATILL_DRIVE_HIDE_EMPTY_DAYS_on" class="radioCheck">
                            {l s='Yes' mod='prestatilldrive'}
                        </label>
                    <input class="slot_enabled" type="radio" name="PRESTATILL_DRIVE_HIDE_EMPTY_DAYS" id="PRESTATILL_DRIVE_HIDE_EMPTY_DAYS_off" value="0" {if $hide_empty_days == 0} checked="checked"{/if}>
                        <label for="PRESTATILL_DRIVE_HIDE_EMPTY_DAYS_off" class="radioCheck">
                            {l s='No' mod='prestatilldrive'}
                        </label>
                    <a class="slide-button btn"></a>
                </span>
            </div>
        </div>
    </div>
	<hr>
	<div class="form-group">
		<label class="control-label col-lg-3" for="PRESTATILL_DRIVE_NB_PRODUCTS_DISPO">
			<span class="label-tooltip" data-toggle="tooltip" title="Define if you want to limit a slot by number of procuts for specifics categories OR by number of orders.">
				{l s='Enable max number of products' mod='prestatilldrive'}
			</span>
		</label>
		<div class="col-lg-9">
			<div class="col-lg-4">
				<span class="switch prestashop-switch fixed-width-lg">
					<input class="products_orders" type="radio" name="PRESTATILL_DRIVE_NB_PRODUCTS_DISPO" id="PRESTATILL_DRIVE_NB_PRODUCTS_DISPO_on" value="1" {if $max_products == 1} checked="checked"{/if}>
						<label for="PRESTATILL_DRIVE_NB_PRODUCTS_DISPO_on" class="radioCheck">
							{l s='Yes' mod='prestatilldrive'}
						</label>
					<input class="products_orders" type="radio" name="PRESTATILL_DRIVE_NB_PRODUCTS_DISPO" id="PRESTATILL_DRIVE_NB_PRODUCTS_DISPO_off" value="0" {if $max_products == 0} checked="checked"{/if}>
						<label for="PRESTATILL_DRIVE_NB_PRODUCTS_DISPO_off" class="radioCheck">
							{l s='No' mod='prestatilldrive'}
						</label>
					<a class="slide-button btn"></a>
				</span>
			</div>
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-lg-3 nb_dispo" for="PRESTATILL_DRIVE_NB_DISPO">
			<span class="label-tooltip orders" data-toggle="tooltip" title="Number of orders possible for the same slot">
				{l s='Number of orders for the same slot : ' mod='prestatilldrive'}
			</span>
			<span class="label-tooltip products" data-toggle="tooltip" title="Number of products possible for the same slot">
				{l s='Number of products for the same slot : ' mod='prestatilldrive'}
			</span>
		</label>
		<div class="col-lg-9">				
			<div class="col-lg-3">
				<div class="input-group nb_dispo">
					<input type="number" name="PRESTATILL_DRIVE_NB_DISPO" style="text-align: center" id="PRESTATILL_DRIVE_NB_DISPO" {if $nb_dispo}value="{$nb_dispo|escape:'htmlall':'UTF-8'}"{/if}/>	
					<span class="input-group-addon products">{l s='Products' mod='prestatilldrive'}</span>	
					<span class="input-group-addon orders">{l s='Orders' mod='prestatilldrive'}</span>	
				</div>
			</div>
			<div class="catBox col-lg-12">
				{$categories}
				<div class="col-xs-12 alert alert-info">
					{l s='Select product categories to consider in product limitation.' mod='prestatilldrive'}<br />
					<b>{l s='The condition is based on id_category_default from each product.' mod='prestatilldrive'}</b>
				</div>
				
			</div>
		</div>
	</div>
	<hr>
	<h4>{l s='Carrier & States parameters' mod='prestatilldrive'}</h4>
	<div class="form-group">
		<label class="control-label col-lg-3" for="PRESTATILL_DRIVE_STATE_PREPARE">
			<span class="label-tooltip" data-toggle="tooltip">
					{l s='Choice a state for Order to prepare' mod='prestatilldrive'}
			</span>
		</label>
		<div class="col-lg-9">
			{foreach from=$states item=state name=foo}					
				<div class="col-lg-4 drive_state">
					<div class="input-group {foreach from=$arrayState item=idstate}{if $idstate == $state.id_order_state}active{/if}{/foreach}">
						<label for="PRESTATILL_DRIVE_STATE_PREPARE{$state.id_order_state|escape:'htmlall':'UTF-8'}" >				
							<input {foreach from=$arrayState item=idstate}{if $idstate == $state.id_order_state}checked="checked"{/if}{/foreach}type="checkbox" id="PRESTATILL_DRIVE_STATE_PREPARE{$state.id_order_state|escape:'htmlall':'UTF-8'}" name="PRESTATILL_DRIVE_STATE_PREPARE{$state.id_order_state|escape:'htmlall':'UTF-8'}" value="{$state.id_order_state|escape:'htmlall':'UTF-8'}" />
							{$state.name|escape:'htmlall':'UTF-8'}
						</label>
					</div>
				</div>
				{if $smarty.foreach.foo.index % 3 == 2}
					<div class="clearfix"></div>
				{/if}
			{/foreach}
		</div>
	</div>
	<hr>
	<h4>{l s='Invoice & Delivery parameters' mod='prestatilldrive'}</h4>
	<div class="form-group">
		<label class="control-label col-lg-3" for="PRESTATILL_DRIVE_MODIFY_PDF">
			<span class="label-tooltip" data-toggle="tooltip" title="Define if you want to modify the delivery address on the orders invoice & delivery documents.">
				{l s='Modify the delivery and invoice PDF delivery address with store address ? (not retroactive)' mod='prestatilldrive'}
			</span>
		</label>
		<div class="col-lg-9">
			<div class="col-lg-4">
				<span class="switch prestashop-switch fixed-width-lg">
					<input class="slot_enabled" type="radio" name="PRESTATILL_DRIVE_MODIFY_PDF" id="PRESTATILL_DRIVE_MODIFY_PDF_on" value="1" {if $modify_pdf == 1} checked="checked"{/if}>
						<label for="PRESTATILL_DRIVE_MODIFY_PDF_on" class="radioCheck">
							{l s='Yes' mod='prestatilldrive'}
						</label>
					<input class="slot_enabled" type="radio" name="PRESTATILL_DRIVE_MODIFY_PDF" id="PRESTATILL_DRIVE_MODIFY_PDF_off" value="0" {if $modify_pdf == 0} checked="checked"{/if}>
						<label for="PRESTATILL_DRIVE_MODIFY_PDF_off" class="radioCheck">
							{l s='No' mod='prestatilldrive'}
						</label>
					<a class="slide-button btn"></a>
				</span>
			</div>
		</div>
	</div>
	<hr />
	<h4>{l s='Notifications parameters' mod='prestatilldrive'}</h4>
	<div class="form-group">
		<label class="control-label col-lg-3" for="PRESTATILL_DRIVE_SEND_REMINDER">
			<span class="label-tooltip" data-toggle="tooltip" title="Send an email with pick-up store and pick-up day and hour">
				{l s='Send an email reminder to the customers with their slots informations' mod='prestatilldrive'}
			</span>
		</label>
		<div class="col-lg-9">
			<div class="col-lg-4">
				<span class="switch prestashop-switch fixed-width-lg">
					<input class="slot_enabled" type="radio" name="PRESTATILL_DRIVE_SEND_REMINDER" id="PRESTATILL_DRIVE_SEND_REMINDER_on" value="1" {if $send_reminder == 1} checked="checked"{/if}>
						<label for="PRESTATILL_DRIVE_SEND_REMINDER_on" class="radioCheck">
							{l s='Yes' mod='prestatilldrive'}
						</label>
					<input class="slot_enabled" type="radio" name="PRESTATILL_DRIVE_SEND_REMINDER" id="PRESTATILL_DRIVE_SEND_REMINDER_off" value="0" {if $send_reminder == 0} checked="checked"{/if}>
						<label for="PRESTATILL_DRIVE_SEND_REMINDER_off" class="radioCheck">
							{l s='No' mod='prestatilldrive'}
						</label>
					<a class="slide-button btn"></a>
				</span>
			</div>
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-lg-3" for="PRESTATILL_DRIVE_SEND_REMINDER_TIME">
			<span class="label-tooltip" data-toggle="tooltip" title="{l s='Define when send the email reminder' mod='prestatilldrive'}">
				{l s='Send reminder email X minutes before time slot' mod='prestatilldrive'}
			</span>
		</label>
		<div class="col-lg-9">				
			<div class="col-lg-3">
				<div class="input-group">
					<input type="number" name="PRESTATILL_DRIVE_SEND_REMINDER_TIME" style="text-align: center" id="PRESTATILL_DRIVE_SEND_REMINDER_TIME" {if $send_reminder_time}value="{$send_reminder_time|escape:'htmlall':'UTF-8'}"{/if} />	
					<span class="input-group-addon">min</span>				
				</div>
			</div>
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-lg-3" for="PRESTATILL_DRIVE_SEND_REMINDER">
			<span class="label-tooltip" data-toggle="tooltip" title="Define if you want to modify the delivery address on the orders invoice & delivery documents.">
				{l s='Use this link to create a cron task' mod='prestatilldrive'}
			</span>
		</label>
		<div class="col-lg-9">
			<div class="col-lg-12">
				<div class="alert alert-info">{l s='You can for example turn on the cron task below every 5 minutes, but not necessary at exact time (example : 11:03, 11:08...)' mod='prestatilldrive'}</div>
				<a href="{$cron_url|escape:'htmlall':'UTF-8'}">
					<i class="icon-external-link-sign"></i>
					<!-- {l s='beetween' mod='prestatilldrive'}{l s='and' mod='prestatilldrive'} -->
					{$cron_url|escape:'htmlall':'UTF-8'}
				</a>
			</div>
		</div>
	</div>
			
	<div class="panel-footer">
		 <div class="btn-group pull-right">
            <button name="submitParameters" id="submitParameters" type="submit" class="btn btn-default"><i class="process-icon-save"></i> {l s='Save' mod='prestatilldrive'}</button>
        </div>
   </div>
</form>

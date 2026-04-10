{*
* Prestatill Drive - Click & Collect
*
* Drive Module & Click & Collect for Prestashop
*
*  @author    Laurent Baumgartner laurent@prestatill.com
*  @copyright 2017-2020 Prestatill SAS
*  @license   Prestatill SAS
*}

<div class="order_creneau_edit">
	<div id="table_dispo_head" class="scroll scroll4"></div>
	<input type="hidden" name="oc_id_order" id="oc_id_order" value="{$id_order|escape:'htmlall':'UTF-8'}" />
	<div id="table_dispo" data-url="{Context::getContext()->link->getModuleLink('prestatilldrive', 'validateordercarrier')|escape:'htmlall':'UTF-8'}"></div>
	<div class="col-xs-12 col-md-4 col177">
		<select name="id_store" id="id_store" class="custom-select">
			<option value="0" {if  $id_store == 0}selected {/if}>{l s='Select a Pick Up Store' mod='prestatilldrive'}</option>
			{foreach from=$stores item=store}
				<option value="{$store.id_store|escape:'htmlall':'UTF-8'}" {if $creneau != null}{if $store.id_store == $creneau->id_store} selected {/if}{/if}>{$store.name|escape:'htmlall':'UTF-8'}</option>
			{/foreach}
		</select>
	</div>
	<div class="col-xs-12 col-md-3 col177">
		<input type="date" name="slot_date" id="slot_date" class="form-control" value="" readonly="readonly"/>
	</div>
	<div class="col-xs-12 col-md-2 col177">
		<input name="slot_hour" id="slot_hour" type="hour" class="form-control" value="" readonly="readonly" />
	</div>
	<div class="col-xs-12 col-md-3 col177">
		<button name="submitSlotCreate" class="btn btn-primary">{l s='Validate Slot' mod='prestatilldrive'}</button>
	</div>
	<div class="clearfix"></div>
	<div class="colx-xs-12">
		<div class="send_email">
			<label class="control-label col-lg-8 col177" for="order_send_mail_modif">
				<span>
					{l s='Send an Email to inform the customer for the modification ?' mod='prestatilldrive'}
				</span>
			</label>
			<div class="col-lg-4 col177" style="text-align:right;">
				<span class="switch prestashop-switch fixed-width-lg" style="display:inline-block;margin-right:0;">
					<input class="drive_enabled" type="radio" name="order_send_mail_modif" id="order_send_mail_modif_on" value="1" checked="checked">
						<label for="order_send_mail_modif_on" class="radioCheck">
							{l s='Yes' mod='prestatilldrive'}
						</label>
					<input class="drive_enabled" type="radio" name="order_send_mail_modif" id="order_send_mail_modif_off" value="0">
						<label for="order_send_mail_modif_off" class="radioCheck">
							{l s='No' mod='prestatilldrive'}
						</label>
					<a class="slide-button btn"></a>
				</span>
			</div>
			<div class="clearfix"></div>
		</div>
	</div>
	<div class="clearfix"></div>
	<input id="drive_base_url" type="hidden" value="{$base_dir|escape:'htmlall':'UTF-8'}" />
</div>
<div class="message_validation alert alert-success" style="display:none;">{l s='Slot updated with success !' mod='prestatilldrive'}</div>
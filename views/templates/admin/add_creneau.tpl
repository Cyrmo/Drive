{*
* Prestatill Drive - Click & Collect
*
* Drive Module & Click & Collect for Prestashop
*
*  @author    Laurent Baumgartner laurent@prestatill.com
*  @copyright 2017-2020 Prestatill SAS
*  @license   Prestatill SAS
*}

<div class="add_creneau_edit">
	<div class="col-xs-9">
		<div class="alert alert-info">
			{l s='You can manually block some slots by adding them below. This blocks the entire slot for customers.' mod='prestatilldrive'}
		</div>

		<div id="table_dispo_head" class="scroll scroll4"></div>
		<input type="hidden" name="oc_id_order" id="oc_id_order" value="0" />
		<div id="table_dispo" data-url="{Context::getContext()->link->getModuleLink('prestatilldrive', 'validateordercarrier')|escape:'htmlall':'UTF-8'}"></div>
		<div class="col-xs-12 col-md-5">
			<select name="id_store" id="id_store">
				<option value="0" {if  $id_store == 0}selected {/if}>{l s='Select a Pick Up Store' mod='prestatilldrive'}</option>
				{foreach from=$stores item=store}
					<option value="{$store.id_store|escape:'htmlall':'UTF-8'}" {if $creneau != null}{if $store.id_store == $creneau->id_store} selected {/if}{/if}>{$store.name|escape:'htmlall':'UTF-8'}</option>
				{/foreach}
			</select>
		</div>
		<div class="col-xs-12 col-md-3">
			<input type="date" name="slot_date" id="slot_date" class="form-control" value="" readonly="readonly"/>
		</div>
		<div class="col-xs-12 col-md-2">
			<input name="slot_hour" id="slot_hour" type="hour" class="form-control" value="" readonly="readonly" />
		</div>
		<div class="col-xs-12 col-md-2">
			<button name="submitSlotCreate" class="btn btn-primary">{l s='Validate Slot' mod='prestatilldrive'}</button>
		</div>
		<div class="clearfix"></div>
		<input id="drive_base_url" type="hidden" value="{$base_dir|escape:'htmlall':'UTF-8'}" />
	</div>
	<div class="col-lg-3">
        
    </div>
	<div class="clearfix"></div>
</div>
<div class="message_validation alert alert-success" style="display:none;">{l s='Slot updated with success !' mod='prestatilldrive'}</div>
{*
* Prestatill Drive - Click & Collect
*
* Drive Module & Click & Collect for Prestashop
*
*  @author    Laurent Baumgartner laurent@prestatill.com
*  @copyright 2017-2020 Prestatill SAS
*  @license   Prestatill SAS
*}

{if $msg_creneau || $store_name}
	<div id="table_dispo_overlay"></div>
	<div class="order_creneau">
		{if $day_creneau != null}
		<span>{l s='Pick up slot :' mod='prestatilldrive'} <b>{$msg_creneau|escape:'htmlall':'UTF-8'}</b></span><br />
		{/if}
		{if $id_lang > 0}
			<span>{l s='Pick up store :' mod='prestatilldrive'} <b>{$store_name.$id_lang|escape:'htmlall':'UTF-8'} {$store_address.$id_lang|escape:'htmlall':'UTF-8'} {$store_postcode|escape:'htmlall':'UTF-8'} {$store_city|escape:'htmlall':'UTF-8'}</b></span>
		{else}
			<span>{l s='Pick up store :' mod='prestatilldrive'} <b>{$store_name|escape:'htmlall':'UTF-8'} {$store_address|escape:'htmlall':'UTF-8'} {$store_postcode|escape:'htmlall':'UTF-8'} {$store_city|escape:'htmlall':'UTF-8'}</b></span>
		{/if}
		<button class="btn btn-default" data-type="edit"><i class="icon-pencil"></i><span>{l s='Edit slot' mod='prestatilldrive'}</span></button>
		<input type="hidden" id="confirm_msg" value="{l s='Be carefull, by clicking OK, the current Pick Up Slot will directly be released until you validate a new one.' mod='prestatilldrive'}"/>
		<div class="clearfix"></div>
		{* SINCE 2.2.0 : Addition of PIN CODE *}
		{if $creneau->pin_code}
		  {l s='Pick up PIN CODE :' mod='prestatilldrive'} <b>{$creneau->pin_code}</b>
		{/if}
	</div> 
	{include file="./admin_order_edit_slot.tpl"}
{/if}

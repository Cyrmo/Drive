{*
* Prestatill Drive - Click & Collect
*
* Drive Module & Click & Collect for Prestashop
*
*  @author    Laurent Baumgartner laurent@prestatill.com
*  @copyright 2017-2020 Prestatill SAS
*  @license   Prestatill SAS
*}

{if !empty($ids_carrier)}
	{foreach from=$ids_carrier item=id_carrier key=id_store}
		<input data-id_store="{$id_store|escape:'htmlall':'UTF-8'}" type="hidden" value="{$id_carrier|escape:'htmlall':'UTF-8'}"/>
	{/foreach}
{/if}

<div id="carrier_message" >
    <div class="alert alert-danger">
        {$message|escape:'htmlall':'UTF-8'}
    </div>
    {if $ps_version < '1.7'}
    <a href="{$cart_link_16|escape:'htmlall':'UTF-8'}" class="btn btn-primary pull-right float-xs-right">{l s='Get to cart summary' mod='prestatilldrive'}</a>
    <div class="clearfix"></div>
    <hr />
    {else}
    <a href="{$cart_link_17|escape:'htmlall':'UTF-8'}?action=show" class="btn btn-primary pull-right float-xs-right">{l s='Get to cart summary' mod='prestatilldrive'}</a>
    <div class="clearfix"></div>
    {/if}
</div>

<input type="hidden" id="common_carriers" name="common_carriers" value="{$common_carriers|escape:'htmlall':'UTF-8'}" />

<div id="table_box" class="col-xs-12" data-creneau="{if isset($creneau_day) && isset($creneau_hour)}1{else}0{/if}">
	<input type="hidden" value="{$base_url|escape:'htmlall':'UTF-8'}" id="psd_base_url" name="psd_base_url" />
	<div id="table_dispo_overlay"></div>
	{if $slot_enabled == 1}
	<h3 class="psd_subtitle">{l s='Choice a slot to pick your order' mod='prestatilldrive'}</h3>	
	{else}
	<h3 class="psd_subtitle">{l s='Choice a store to pick your order' mod='prestatilldrive'}</h3>	
	{/if}
	<div class="clear"></div>
	{if $slot_enabled == 1}
		<div class="alert alert-warning" id="choose_store_msg">{l s='Choose a store to see the opening days and hours' mod='prestatilldrive'}</div>
		<div class="alert alert-warning" id="choose_slot_msg">{l s='Choose an available slot to pick-up your order' mod='prestatilldrive'}</div>
        <div class="alert alert-warning" id="error_slot_msg">{l s='The Pick-up slot you want is no longer available. Please choose another one' mod='prestatilldrive'}</div>
        <div class="alert alert-warning" id="creneau_valid">{l s='Your cart contains products with a greater preparation time that your reserved Pick-up slot. Please choose a new Pick-up slot to finalise your order.' mod='prestatilldrive'}</div>
	{else}
		<div class="alert alert-warning" id="choose_store_msg">{l s='Choose a store' mod='prestatilldrive'}</div>
	{/if}
	<div class="row">
        {include file="./shop.tpl"}     
    </div>
	{if ($nbr_stores == false)}
    	{if !empty($stores)}
    		{l s='Error : ' mod='prestatilldrive'}{$stores|escape:'htmlall':'UTF-8'}
		{/if}
        {l s='No stores next to you. Please choose another carrier or contact the customer service if you think there is a mistake.' mod='prestatilldrive'}
	{else}
		<ul id="store_list">
			{foreach $stores as $store}
				<li class="clickable {if $ps_version < '1.7'}col-xs-12 col-md-6{/if}" data-id_store="{$store.id_store|escape:'htmlall':'UTF-8'}">
					<b>{$store.name|escape:'htmlall':'UTF-8'}</b><br />
					{if $store.address1}{$store.address1|escape:'htmlall':'UTF-8'} <br />{/if}
					{$store.postcode|escape:'htmlall':'UTF-8'} {$store.city|escape:'htmlall':'UTF-8'}
				    <button type="button" class="btn btn-secondary selectStore" data-id_store="{$store.id_store|escape:'htmlall':'UTF-8'}">
                        {l s='Select' mod='prestatilldrive'}
                    </button>
				</li>
			{/foreach}
		</ul>
	{/if}
	{if $slot_enabled == 1}
	<div id="nav_buttons" >
		<div class="btn btn-default buttondays" id="prev_days">< {l s='Previous Days' mod='prestatilldrive'}</div>
		<div class="btn btn-default buttondays" id="next_days">{l s='Following Days' mod='prestatilldrive'} ></div>
	</div>
	<div class="clearfix"></div>
	
	<div id="table_dispo_head" class="scroll scroll4"></div>
	<div id="table_dispo" data-url="{Context::getContext()->link->getModuleLink('prestatilldrive', 'validateordercarrier')|escape:'htmlall':'UTF-8'}"></div>
	
	<div id="table_legend">
		<ul>
			<li class="dispo">{l s='Available' mod='prestatilldrive'}</li>
			<li>{l s='Unavailable' mod='prestatilldrive'}</li>
			<li class="busy">{l s='Busy' mod='prestatilldrive'}</li>
			<li class="vacation	">{l s='Full' mod='prestatilldrive'}</li>
		</ul>
	</div>
	
	<div class="row">
		{include file="./order.tpl"}		
	</div>
    <div class="alert alert-warning" id="no_slot_available">{l s='There is no available pick-up slot for the moment. You can check product availabilities on your Cart or contact your pick-up store for more details.' mod='prestatilldrive'}</div>
	{else}
	<input type="hidden" id="slot_disabled" name="slot_disabled" value="1" />
    <div id="table_dispo" data-url="{Context::getContext()->link->getModuleLink('prestatilldrive', 'validateordercarrier')|escape:'htmlall':'UTF-8'}"></div>
	{/if}
	<div class="clear"></div>
	<input type="hidden" id="nbr_stores" value="">
</div>

<div class="modal fade" id="modal_creneau">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <button type="button" class="close" data-dismiss="modal" aria-label="{l s='Close' mod='prestatilldrive'}">
                  <span aria-hidden="true">&times;</span>
            </button>
            <div class="js-modal-content"></div>
        </div>
    </div>
</div>
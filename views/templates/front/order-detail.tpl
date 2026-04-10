{*
* Prestatill Drive - Click & Collect
*
* Drive Module & Click & Collect for Prestashop
*
*  @author    Laurent Baumgartner laurent@prestatill.com
*  @copyright 2017-2020 Prestatill SAS
*  @license   Prestatill SAS
*}

{if $msg_creneau || $drive_store}
<div id="drive_order_detail" class="box text-center">
	<h3>{l s='Informations about your pick up slot' mod='prestatilldrive'}</h3>
	{if $creneau->id_day > 0}
	<div class="col-xs-12 col-md-6">
		<h4>{l s='Your slot' mod='prestatilldrive'}</h4>
		<p class="alert alert-info">
			<span>{$msg_creneau|escape:'htmlall':'UTF-8'}</span>
		</p>
	</div>
	{/if}

	<div id="delivery-pickup" class="col-xs-12 {if $creneau->id_day > 0} col-md-6{/if}">
		<h4>{l s='Your pick up store' mod='prestatilldrive'}</h4>
		<p class="alert alert-info">
			{if $id_lang > 0}
				{$drive_store->name.$id_lang|escape:'htmlall':'UTF-8'}<br/>
				{$drive_store->address1.$id_lang|escape:'htmlall':'UTF-8'} {$drive_store->postcode|escape:'htmlall':'UTF-8'} {$drive_store->city|escape:'htmlall':'UTF-8'}
			{else}
				{$drive_store->name|escape:'htmlall':'UTF-8'}<br/>
				{$drive_store->address1|escape:'htmlall':'UTF-8'} {$drive_store->postcode|escape:'htmlall':'UTF-8'} {$drive_store->city|escape:'htmlall':'UTF-8'}
			{/if}
			{if $drive_store->phone}<br />{$drive_store->phone|escape:'htmlall':'UTF-8'}{/if}
		</p>
	</div>
	<div class="col-xs-12">
	    {if $pin_code}
          {include file='./pin-code.tpl'}
        {/if}
	</div>
	<div class="clearfix"></div>
	
</div>
{/if}

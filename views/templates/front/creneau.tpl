{*
* Prestatill Drive - Click & Collect
*
* Drive Module & Click & Collect for Prestashop
*
*  @author    Laurent Baumgartner laurent@prestatill.com
*  @copyright 2017-2020 Prestatill SAS
*  @license   Prestatill SAS
*}

{if $slot_enabled == 1}
<div id="creneau_selected" class="alert alert-success">
		<h5>{l s='Your slot' mod='prestatilldrive'}</h5>
	<div id="creneau_day" class="">
		{if isset($creneau)}
			{$creneau|escape:'htmlall':'UTF-8'}
		{else}
			{l s='no selected slot' mod='prestatilldrive'}
		{/if}
	</div>
</div>
{/if}
<div id="store_selected" class="background-light col-sm-12"></div>
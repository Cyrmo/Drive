{*
* Prestatill Drive - Click & Collect
*
* Drive Module & Click & Collect for Prestashop
*
*  @author    Laurent Baumgartner laurent@prestatill.com
*  @copyright 2017-2020 Prestatill SAS
*  @license   Prestatill SAS
*}
	
<div id="creneau_selected" class="alert alert-warning">
	<h5>{l s='Your slot' mod='prestatilldrive'}</h5>
	<div id="creneau_day" class="">
		{if isset($creneau_day) && isset($creneau_hour)}
			{$creneau_day|escape:'htmlall':'UTF-8'|date_format:"%A, %e %B, %Y"} à {$creneau_hour|escape:'htmlall':'UTF-8'|date_format:"%R"}	
		{else}
			{l s='no selected slot' mod='prestatilldrive'}
		{/if}
	</div>
	<button type="button" class="btn btn-secondary changeSlot" data-id_store="{$id_store|escape:'htmlall':'UTF-8'}">
        {l s='Change slot' mod='prestatilldrive'}
    </button>
    <div class="clearfix"></div>
</div>
<button class="continue btn btn-primary float-xs-right" id="verifyCreneau">{l s='Continue' mod='prestatilldrive'}</button>
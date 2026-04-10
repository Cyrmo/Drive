{*
* Prestatill Drive - Click & Collect
*
* Drive Module & Click & Collect for Prestashop
*
*  @author    Laurent Baumgartner laurent@prestatill.com
*  @copyright 2017-2020 Prestatill SAS
*  @license   Prestatill SAS
*}

{if !empty($stores)}
{foreach $stores as $store}
	<li class="clickable {if !$ps_17}col-xs-12 col-md-6{/if}" data-id_store="{$store.id_store|escape:'htmlall':'UTF-8'}">
		<b>{$store.name|escape:'htmlall':'UTF-8'}</b><br />
        {if $store.address1}{$store.address1|escape:'htmlall':'UTF-8'} <br />{/if}
        {$store.postcode|escape:'htmlall':'UTF-8'} {$store.city|escape:'htmlall':'UTF-8'}
        <button type="button" class="btn btn-secondary selectStore" data-id_store="{$store.id_store|escape:'htmlall':'UTF-8'}">
            {l s='Select' mod='prestatilldrive'}
        </button>	
     </li>
{/foreach}
{else}
    <div class="alert alert-warning">
        {l s='No stores next to you. Please choose another carrier or contact the customer service if you think there is a mistake.' mod='prestatilldrive'}
    </div>
{/if}

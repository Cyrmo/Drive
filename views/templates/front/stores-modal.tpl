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
    {foreach from=$stores item=store}
        <li {if $active}class="active"{/if}><b>{$store.name|escape:'htmlall':'UTF-8'}</b> {if isset($store.distance)} <span class="km">{$store.distance|escape:'htmlall':'UTF-8'|round:0}km</span>{/if}<br />
            {$store.address1|escape:'htmlall':'UTF-8'}<br />
            {$store.postcode|escape:'htmlall':'UTF-8'} {$store.city|escape:'htmlall':'UTF-8'}
            <input type="hidden" data-id_store="{$store.id_store|escape:'htmlall':'UTF-8'}" />
            <button type="button" class="btn btn-secondary {if !$active}selectStore{/if}" data-id_store="{$store.id_store|escape:'htmlall':'UTF-8'}">
                {if $active}{l s='Change store' mod='prestatilldrive'}{else}{l s='Select' mod='prestatilldrive'}{/if}
            </button>
        </li>
    {/foreach}
    {else}
    <li class="alert alert-info">
        {l s='No store near you, try to find a Pick-up store on enter a Postcode or City name...' mod='prestatilldrive'}
    </li>
{/if}
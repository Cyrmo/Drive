{*
* Prestatill Drive - Click & Collect
*
* Drive Module & Click & Collect for Prestashop
*
*  @author    Laurent Baumgartner laurent@prestatill.com
*  @copyright 2017-2020 Prestatill SAS
*  @license   Prestatill SAS
*}

{if !empty($stores_carriers)}
    <div class="card card-block carriers_list">
        {if $id_store != null}  
        {if !empty($carriers)}
            <p class="h6 card-title">{l s='Availabilities for Click & collect :' mod='prestatilldrive'} {if $store_limited}{l s='in stores near you' mod='prestatilldrive'}{/if}</p>
        {else}
            <p class="h6 card-title">{l s='This product is not available for Click & collect' mod='prestatilldrive'} {if $store_limited && $id_store != null}{l s='in your selected pick-up store' mod='prestatilldrive'}{/if}</p>
        {/if}
        <ul>
        {foreach from=$stores_carriers item=carrier key=id_carrier}
            {if $carrier.id_carrier|array_key_exists:$carriers}
                <li><i class="{if $ps_17}material-icons">done{else}icon icon-check">{/if}</i> {$carrier.name|escape:'htmlall':'UTF-8'}</li>
            {else}
                <li class="unaivalaible"><i class="{if $ps_17}material-icons">error{else}icon icon-exclamation-circle">{/if}</i> {$carrier.name|escape:'htmlall':'UTF-8'}</li>
            {/if}
        {/foreach}
        </ul>
        {else}
        <p class="h6 card-title">{l s='Availabilities for Pick-up in store :' mod='prestatilldrive'}</p>
        <ul>
            <li class="center"><a class="btn btn-secondary chooseStore">
                <i class="{if $ps_17}material-icons">airport_shuttle{else}icon icon-car">{/if}</i>
                {l s='Select your Pick-up store' mod='prestatilldrive'}</a>
            </li>
        </ul>
        {/if}
        
    </div>
{else if $store_limited}
    <div class="card card-block carriers_list">
        <p class="h6 card-title">{l s='Availabilities for Click & collect :' mod='prestatilldrive'}</p>
        <ul>
            {if $id_store}
                <li class="unaivalaible"><i class="{if $ps_17}material-icons">error{else}icon icon-exclamation-circle">{/if}</i> {if !empty($selected_store)}{$selected_store.name|escape:'htmlall':'UTF-8'}{else}{l s='in your Pick-up store' mod='prestatilldrive'}{/if}</li>
            {else}
                <li class="center"><a class="btn btn-secondary chooseStore">
                    <i class="{if $ps_17}material-icons">airport_shuttle{else}icon icon-car">{/if}</i>
                    {l s='Select your Pick-up store' mod='prestatilldrive'}</a>
                </li>
            {/if}
        </ul>
    </div>
{/if}    

{*
* Prestatill Drive - Click & Collect
*
* Drive Module & Click & Collect for Prestashop
*
*  @author    Laurent Baumgartner laurent@prestatill.com
*  @copyright 2017-2020 Prestatill SAS
*  @license   Prestatill SAS
*}

<div class="psd_store_selector fixed {if $selected_store != false}active{/if} {if $selected_slot}active_slot{/if}" data-toggle="modal" data-target="#store_selector_modal">
    {if $selected_store == false || ($selected_store == true && $slot_enabled  == false)}
    <i class="{if $ps_17}material-icons">airport_shuttle{else}icon icon-car">{/if}</i>
        <span>
            {if $selected_store && $slot_enabled == false}
                {$selected_store.name|escape:'htmlall':'UTF-8'|truncate:42:"..."}
            {else}
                {l s='Select your Pick-up store' mod='prestatilldrive'}
            {/if}
        </span>
    {else}
    <i class="{if $ps_17}material-icons">airport_shuttle{else}icon icon-car">{/if}</i>
        <span>
            {if $selected_slot != false}
                {$selected_slot|escape:'htmlall':'UTF-8'}
            {else}
                {l s='Book your Pick-up slot now ' mod='prestatilldrive'}
            {/if}
        </span>
    {/if}
</div>

<div class="modal fade" id="store_selector_modal" tabindex="-1" role="dialog" >
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <input type="hidden" name="ajax_url" id="ajax_url" value="{Context::getContext()->link->getModuleLink('prestatilldrive', 'validateordercarrier')|escape:'htmlall':'UTF-8'}" />
                <input type="hidden" name="slot_enabled" id="slot_enabled" value="{if $slot_choice_enabled == 0}0{else if $slot_enabled}1{else}0{/if}" />
                <h5 class="modal-title">{if $slot_choice_enabled}{l s='Book your Pick-up slot now' mod='prestatilldrive'}{else}{l s='Select your Pick-up store' mod='prestatilldrive'}{/if}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="search_cp {if $selected_store != false}none{/if}">
                {if $search_enabled}<i id="localisation-icon"  class="{if $ps_17}material-icons">control_point{else}icon icon-bullseye">{/if}</i>{/if}
                <input type="text" class="form-control" placeholder="{l s='Postcode, City...' mod='prestatilldrive'}" />
                <button id="search_cp_button" class="btn btn-primary">{l s='Search' mod='prestatilldrive'}</button>
            </div>
            <div class="modal-body">
                <div class="store_list_box scroll scroll4">
                    <div id="modal_loader"></div>
                    <ul class="store_list">
                        {if $selected_store != false}
                            <li class="active">
                                <h4>{l s='Your selected Pick-up store' mod='prestatilldrive'}</h4>
                                <span>
                                {$selected_store.name|escape:'htmlall':'UTF-8'|truncate:42:"..."} {if isset($selected_store.distance)} <span class="km">{$selected_store.distance|escape:'htmlall':'UTF-8'|round:0}km</span>{/if}<br />
                                {$selected_store.address1|escape:'htmlall':'UTF-8'}<br />
                                {$selected_store.postcode|escape:'htmlall':'UTF-8'} {$selected_store.city|escape:'htmlall':'UTF-8'}
                                </span>
                                <button type="button" class="btn btn-secondary" data-id_store="{$selected_store.id_store|escape:'htmlall':'UTF-8'}">
                                    {l s='Change store' mod='prestatilldrive'}
                                </button>
                                <div class="clearfix"></div>
                            </li>
                        {else}
                            <li class="no_padd"><div class="alert alert-info">{if $search_enabled}{l s='Enter a postcode or city name, click on localisation button to find a store near you, or select your store bellow...' mod='prestatilldrive'}{else}{l s='Enter a postcode or city name to find a store...' mod='prestatilldrive'}{/if}</div></li>
                            {* include file='../front/stores-modal.tpl' *}
                        {/if}
                    </ul>
                </div>
                {if $slot_enabled && $slot_choice_enabled}
                <div class="reserved_slot">
                    <div class="reservation_ok {if $selected_slot}active{else}none{/if}">
                        <h4>{l s='Your reserved slot for' mod='prestatilldrive'} <b>{$slot_reservation_duration|escape:'htmlall':'UTF-8'}</b> {l s='minutes' mod='prestatilldrive'}</h4>
                            <span>{if $selected_slot != false}{$selected_slot|escape:'htmlall':'UTF-8'}{/if}</span>
                            <button type="button" class="btn btn-secondary changeSlot" data-id_store="{$selected_store.id_store|escape:'htmlall':'UTF-8'}">
                                    {l s='Change slot' mod='prestatilldrive'}
                            </button>
                            <div class="clearfix"></div>
                    </div>
                    {if $selected_slot == false}
                        <div class="bookInfos alert alert-info">
                            <i class="{if $ps_17}material-icons">timer{else}icon icon-clock-o">{/if}</i>
                            {l s='Book now your Pick-up slot for the next' mod='prestatilldrive'} {$total_duration|escape:'htmlall':'UTF-8'}</b> {l s='minutes' mod='prestatilldrive'}
                        </div>
                    {/if}
                    <div id="table_dispo_head" class="scroll scroll4"></div>
                    <div id="table_dispo" data-url="{Context::getContext()->link->getModuleLink('prestatilldrive', 'validateordercarrier')|escape:'htmlall':'UTF-8'}"></div>
                </div>
                {/if}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary closing" data-dismiss="modal">
                    {l s='Close' mod='prestatilldrive'}
                </button>
            </div>
        </div>
    </div>
</div>
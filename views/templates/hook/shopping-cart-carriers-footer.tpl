{*
* Prestatill Drive - Click & Collect
*
* Drive Module & Click & Collect for Prestashop
*
*  @author    Laurent Baumgartner laurent@prestatill.com
*  @copyright 2017-2020 Prestatill SAS
*  @license   Prestatill SAS
*}

<input type="hidden" id="common_carrier" name="common_carrier" value="{$common_carrier|escape:'htmlall':'UTF-8'}" />
{if $common_carrier == false || $date_carence == true}
<div id="carrier_infos" class="card cart-container">
    <div class="card-block">
        <h1 class="h1">{l s='Carriers' mod='prestatilldrive'}</h1>
    </div>
    <hr class="separator">
    
    <div class="list">
        <div class="alert alert-warning">
            {l s='Your cart contains products with a greater preparation time that your reserved slot. You can validate your order but you will have to choose a new Pick-up slot.' mod='prestatilldrive'}
            <button type="button" class="btn btn-secondary changeSlot" data-toggle="modal" data-target="#store_selector_modal">
                {l s='Change slot' mod='prestatilldrive'}
            </button>
        </div>        
    </div>
</div>
{/if}

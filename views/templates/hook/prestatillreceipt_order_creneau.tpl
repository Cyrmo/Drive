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
<div class="col-xs-12">
    <div class="panel" id="customer_informations">
        <div class="panel-heading">
            <i class="icon-car"></i> {l s='Pick-up slot informations' mod='prestatilldrive'}
        </div>
        <div class="center">
            <div class="order_creneau">
                {if $day_creneau != null}
                <span>{l s='Pick up slot :' mod='prestatilldrive'} <b>{$msg_creneau|escape:'htmlall':'UTF-8'}</b></span><br />
                {/if}
                {if $id_lang > 0}
                    <span>{l s='Pick up store :' mod='prestatilldrive'} <b>{$store_name.$id_lang|escape:'htmlall':'UTF-8'} {$store_address.$id_lang|escape:'htmlall':'UTF-8'} {$store_postcode|escape:'htmlall':'UTF-8'} {$store_city|escape:'htmlall':'UTF-8'}</b></span>
                {else}
                    <span>{l s='Pick up store :' mod='prestatilldrive'} <b>{$store_name|escape:'htmlall':'UTF-8'} {$store_address|escape:'htmlall':'UTF-8'} {$store_postcode|escape:'htmlall':'UTF-8'} {$store_city|escape:'htmlall':'UTF-8'}</b></span>
                {/if}
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
</div>
{/if}

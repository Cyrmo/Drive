{*
* Prestatill Drive - Click & Collect
*
* Drive Module & Click & Collect for Prestashop
*
*  @author    Laurent Baumgartner laurent@prestatill.com
*  @copyright 2017-2020 Prestatill SAS
*  @license   Prestatill SAS
*}
{if $display_common_carriers || $common_carrier == 0}
    {if !empty($stores_carriers)}
        <div class="carriers_list">
            {foreach from=$stores_carriers item=carrier}
            {if $carrier.id_carrier|array_key_exists:$carriers}
                 <span>{if $ps_version > '1.7'}<i class="material-icons">done</i>{else}<i class="icon icon-check"></i>{/if} {$carrier.name|escape:'htmlall':'UTF-8'}</span>
            {else}
                 <span class="unaivalaible">{if $ps_version > '1.7'}<i class="material-icons">error</i>{else}<i class="icon icon-check"></i>{/if} {$carrier.name|escape:'htmlall':'UTF-8'}</span>
            {/if}
            {/foreach}
        </div>
    {else}
        {if $common_carrier == 1}
            <div class="carriers_list">
                <span class="unaivalaible"><i class="material-icons">error</i> {l s='Not available on Pick-up store near you' mod='prestatilldrive'}</span>
            </div>
        {/if}
    {/if}    

<script>
    // 1.4.0 : Ajout d'informations sur les produits du panier
    if (window.jQuery)
    {
       if($('body').attr('id') == 'cart')
       {
            $('.carriers_list').each(function(){
                $(this).appendTo($(this).parents('li'));
            });
            
            if($('.carriers_list').length == 0)
            {
                $('#carrier_infos').hide();
            }
       }
    }
</script>
{/if}

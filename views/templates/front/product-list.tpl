{*
* Prestatill Drive - Click & Collect
*
* Drive Module & Click & Collect for Prestashop
*
*  @author    Laurent Baumgartner laurent@prestatill.com
*  @copyright 2017-2020 Prestatill SAS
*  @license   Prestatill SAS
*}

{if !empty($products)}
    <table style="text-align: center;" cellpadding="5" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th style="border:1px solid #ddd;width:10%;">{l s='#ID' mod='prestatilldrive'}</th>
                <th style="border:1px solid #ddd;width:20%;">{l s='Ref.' mod='prestatilldrive'}</th>
                <th style="border:1px solid #ddd;width:60%;">{l s='Product' mod='prestatilldrive'}</th>
                <th style="border:1px solid #ddd;width:10%;">{l s='Qty' mod='prestatilldrive'}</th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$products item=product}
            <tr>
                <td style="border:1px solid #ddd;background:#FFF;">{$product.product_id|escape:'htmlall':'UTF-8'}</td>
                <td style="border:1px solid #ddd;background:#FFF;">{$product.product_reference|escape:'htmlall':'UTF-8'}</td>
                <td style="border:1px solid #ddd;background:#FFF;text-align:left;">{$product.product_name|escape:'htmlall':'UTF-8'}</td>
                <td style="border:1px solid #ddd;background:#FFF;">{$product.product_quantity|escape:'htmlall':'UTF-8'}</td>
            </tr>
            {/foreach}
        </tbody>
    </table>
{/if}

{*
* Prestatill Drive - Click & Collect
*
* Drive Module & Click & Collect for Prestashop
*
*  @author    Laurent Baumgartner laurent@prestatill.com
*  @copyright 2017-2020 Prestatill SAS
*  @license   Prestatill SAS
*}

<span style="font-weight:400;">{l s='Customer:' mod='prestatilldrive'}</span> <b>{$customer->firstname|escape:'htmlall':'UTF-8'} {$customer->lastname|escape:'htmlall':'UTF-8'} ({$customer->id|escape:'htmlall':'UTF-8'})</b><br />
<span style="font-weight:400;">{l s='Email:' mod='prestatilldrive'}</span> {$customer->email|escape:'htmlall':'UTF-8'}<br />
<span style="font-weight:400;">{l s='Phone:' mod='prestatilldrive'}</span> <b>{if $address->phone}{$address->phone|escape:'htmlall':'UTF-8'}{/if} {if $address->phone_mobile} / {$address->phone_mobile|escape:'htmlall':'UTF-8'}{/if}</b>
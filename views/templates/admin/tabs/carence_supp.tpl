{*
* Prestatill Drive - Click & Collect
*
* Drive Module & Click & Collect for Prestashop
*
*  @author    Laurent Baumgartner laurent@prestatill.com
*  @copyright 2017-2020 Prestatill SAS
*  @license   Prestatill SAS
*}

{if $carence_supp}<!-- <i class="fa fa-arrow-right"></i>-->
	{foreach from=$carence_supp item=supp key=k}
		<tr class="carence_supp_td_{$supp.id_store|escape:'htmlall':'UTF-8'}">
			<td style="text-align:center;">{if $supp.id_day > 0}{$formatted_days[$supp.id_day]|escape:'htmlall':'UTF-8'}{else}{l s='All days' mod='prestatilldrive'}{/if}</td>
			<td style="text-align:center;">{$supp.hour_limit|escape:'htmlall':'UTF-8'}</td>
			<td style="text-align: center;">{if $supp.id_day_end > 0 && $supp.id_day_end != $supp.id_day}{$formatted_days[$supp.id_day_end]|escape:'htmlall':'UTF-8'}{else}<i class="fa fa-arrow-right"></i>{/if}</td>
			<td style="text-align: center;">{$supp.hour_limit_end|escape:'htmlall':'UTF-8'}</td>
			{assign var='convert' value=$supp.waiting_time/60}
			<td style="text-align:center;"><span class="success">+{$convert|escape:'htmlall':'UTF-8'|round:2} {l s='hours' mod='prestatilldrive'}</span></td>
			<td style="text-align:center;">
				<button class="deleteCarenceSupp btn btn-default" data-id_store="{$supp.id_store|escape:'htmlall':'UTF-8'}" data-id_day="{$supp.id_day|escape:'htmlall':'UTF-8'}" data-url="{Context::getContext()->link->getModuleLink('prestatilldrive', 'validateordercarrier')|escape:'htmlall':'UTF-8'}">{l s='Delete' mod='prestatilldrive'}</button>
			</td>
		</tr>
	{/foreach}
{/if}
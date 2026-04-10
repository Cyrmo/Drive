{*
* Prestatill Drive - Click & Collect
*
* Drive Module & Click & Collect for Prestashop
*
*  @author    Laurent Baumgartner laurent@prestatill.com
*  @copyright 2017-2020 Prestatill SAS
*  @license   Prestatill SAS
*}

{if !empty($days) && !empty($stores)}
<form role="form" class="form-horizontal"  action="#" method="POST" id="config_form" name="config_form">
	{foreach from=$days item=day name=foo key=k}		
	<section id="config_form_{$k|escape:'htmlall':'UTF-8'}">
		<h3><i class="icon-calendar"></i> {l s='Opening Days for' mod='prestatilldrive'} <span class="store_name"></span></h3>
		<div class="form-group">
			{if $slot_enabled == 0}
				<div class="alert alert-danger">{l s='Warning : You must enable the slots display in parameters to see table of opening days & hours in front of your store.' mod='prestatilldrive'}</div>
			{/if}
			<label class="control-label col-lg-4" for="PRESTATILL_DRIVE_ENABLE_STORE_{$k|escape:'htmlall':'UTF-8'}">
				<span>
					{l s='Enable this store for Drive' mod='prestatilldrive'}
				</span>
			</label>
			<div class="col-lg-8">
				<div class="col-lg-4">
					<span class="switch prestashop-switch fixed-width-lg">
						<input class="drive_enabled" type="radio" name="PRESTATILL_DRIVE_ENABLE_STORE_{$k|escape:'htmlall':'UTF-8'}" id="PRESTATILL_DRIVE_ENABLE_STORE_{$k|escape:'htmlall':'UTF-8'}_on" value="1" {if $drive_enabled[$k] == 1} checked="checked"{/if}>
							<label for="PRESTATILL_DRIVE_ENABLE_STORE_{$k|escape:'htmlall':'UTF-8'}_on" class="radioCheck">
								{l s='Enabled' mod='prestatilldrive'}
							</label>
						<input class="drive_enabled" type="radio" name="PRESTATILL_DRIVE_ENABLE_STORE_{$k|escape:'htmlall':'UTF-8'}" id="PRESTATILL_DRIVE_ENABLE_STORE_{$k|escape:'htmlall':'UTF-8'}_off" value="0" {if $drive_enabled[$k] == 0} checked="checked"{/if}>
							<label for="PRESTATILL_DRIVE_ENABLE_STORE_{$k|escape:'htmlall':'UTF-8'}_off" class="radioCheck">
								{l s='Disabled' mod='prestatilldrive'}
							</label>
						<a class="slide-button btn"></a>
					</span>
				</div>
			</div>
			<div class="clearfix"></div>
		</div>
		<br />
        <hr>
        <h3>{l s='Create a unique PIN CODE for automatic pick-up or pick-up verification.' mod='prestatilldrive'}</h3>
		<div class="form-group">
		    <div class="alert-info alert">
                {l s='You can choose to generate and send a unique PIN CODE in your customer\'s email to validate or pick-up their order in an automatic locker for example' mod='prestatilldrive'}
            </div>
		    <div class="new"><span>{l s='NEW FEATURE' mod='prestatilldrive'}</span></div>
			<label class="control-label col-lg-4" for="PRESTATILL_DRIVE_PIN_CODE_ACTIVE_{$k|escape:'htmlall':'UTF-8'}">
                <span>
                    {l s='Generate a unique verification PIN CODE to add on customer\'s E-mail' mod='prestatilldrive'}
                </span>
            </label>
            <div class="col-lg-8">
                <div class="col-lg-4">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input class="drive_enabled" type="radio" name="PRESTATILL_DRIVE_PIN_CODE_ACTIVE_{$k|escape:'htmlall':'UTF-8'}" id="PRESTATILL_DRIVE_PIN_CODE_ACTIVE_{$k|escape:'htmlall':'UTF-8'}_on" value="1" {if $pin_code_active[$k] == 1} checked="checked"{/if}>
                            <label for="PRESTATILL_DRIVE_PIN_CODE_ACTIVE_{$k|escape:'htmlall':'UTF-8'}_on" class="radioCheck">
                                {l s='Enabled' mod='prestatilldrive'}
                            </label>
                        <input class="drive_enabled" type="radio" name="PRESTATILL_DRIVE_PIN_CODE_ACTIVE_{$k|escape:'htmlall':'UTF-8'}" id="PRESTATILL_DRIVE_PIN_CODE_ACTIVE_{$k|escape:'htmlall':'UTF-8'}_off" value="0" {if $pin_code_active[$k] == 0} checked="checked"{/if}>
                            <label for="PRESTATILL_DRIVE_PIN_CODE_ACTIVE_{$k|escape:'htmlall':'UTF-8'}_off" class="radioCheck">
                                {l s='Disabled' mod='prestatilldrive'}
                            </label>
                        <a class="slide-button btn"></a>
                    </span>
                </div>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="form-group">
            <div class="new"><span>{l s='NEW FEATURE' mod='prestatilldrive'}</span></div>
            <label class="control-label col-lg-4" for="PRESTATILL_DRIVE_PIN_CODE_PREFIX_{$k|escape:'htmlall':'UTF-8'}">
                <span>
                    {l s='Add a prefix of one or multiple numbers before PIN CODE ?' mod='prestatilldrive'}
                </span>
            </label>
            <div class="col-lg-8">
                <div class="col-lg-3">
                    <div class="form-group">
                        <input type="number" name="PRESTATILL_DRIVE_PIN_CODE_PREFIX_{$k}" style="text-align: center" id="PRESTATILL_DRIVE_PIN_CODE_PREFIX_{$k}" value="{$pin_code_prefix[$k]|escape:'htmlall':'UTF-8'}" />  
                    </div>
                </div>
            </div>
            <div class="clearfix"></div>
        </div>
		<br />
		<hr>
		<h3>{l s='Choose the carrier associated to this store' mod='prestatilldrive'}</h3>
		<div class="table-responsive">		
			<div class="alert-info alert">
				{l s='If you havn\'t done yet, you can create a carrier with your own rules for using it as default carrier for Drive orders.' mod='prestatilldrive'}
				<a href="{$carriers_link|escape:'htmlall':'UTF-8'}" target="_blank" class="bt_drive_link">
					<i class="icon-external-link-sign"></i>
					{l s='Go to carriers page' mod='prestatilldrive'}
				</a>
			</div>
			<div class="new"><span>{l s='NEW FEATURE' mod='prestatilldrive'}</span></div>
			<div class="input-group col-xs-8">
				<select name="PRESTATILL_DRIVE_CARRIER_{$k|escape:'htmlall':'UTF-8'}" style="text-align: center" id="PRESTATILL_DRIVE_CARRIER_{$k|escape:'htmlall':'UTF-8'}">
					<option value="0" {if $store_carrier[$k|escape:'htmlall':'UTF-8'] == 0} selected {/if}>{l s='No carrier selected' mod='prestatilldrive'}</option>
					{foreach from=$carriers item=carrier}
						<option value="{$carrier.id_carrier|escape:'htmlall':'UTF-8'}" {if $store_carrier[$k|escape:'htmlall':'UTF-8'] == $carrier.id_carrier} selected {/if}>{$carrier.name|escape:'htmlall':'UTF-8'}</option>
					{/foreach}	
				</select>
			</div>
		</div>
		<hr>	
		<div class="table-responsive {if $drive_enabled[$k] == 0}drive_disabled{/if}">	
			<table class="table data-table hour_open">
				<thead>
					<th></th>
					<th class="text-left">{l s='Days' mod='prestatilldrive'}</th>
					<th class="text-left">{l s='Morning openning time' mod='prestatilldrive'}</th>
					<th class="text-left">{l s='Morning closing time' mod='prestatilldrive'}</th>
					<th class="text-left">{l s='Afternoon openning time' mod='prestatilldrive'}</th>
					<th class="text-left">{l s='Afternoon closing time' mod='prestatilldrive'}</th>
					<th class="text-left">{l s='Open non-stop' mod='prestatilldrive'}</th>					
				</thead>
				<tbody>
					{foreach from=$day item=d }
					<tr>
						<td class="text-center">
							<input type="checkbox" data-id="{$d.id_prestatill_drive|escape:'htmlall':'UTF-8'}" id="PRESTATILL_DRIVE_{$d.id_prestatill_drive|escape:'htmlall':'UTF-8'}" name="PRESTATILL_DRIVE_{$d.id_prestatill_drive|escape:'htmlall':'UTF-8'}" value="{$d.openning|escape:'htmlall':'UTF-8'}" {if $d.openning|escape:'htmlall':'UTF-8' == 1}checked{/if}/>
						</td>
						<td class="text-left">
							{$formatted_days[$d.id_day]|escape:'htmlall':'UTF-8'}
						</td>
						<td class="text-left no">
							<input type="time" step="2" data-id="{$d.id_prestatill_drive|escape:'htmlall':'UTF-8'}" id="{$d.id_prestatill_drive|escape:'htmlall':'UTF-8'}_opening_am" name="PRESTATILL_DRIVE_OPENING_AM_{$d.id_prestatill_drive|escape:'htmlall':'UTF-8'}" value="{$d.hour_open_am|escape:'htmlall':'UTF-8'}"/>
						</td>
						<td class="text-left no">
							<input type="time" step="2" data-id="{$d.id_prestatill_drive|escape:'htmlall':'UTF-8'}" id="{$d.id_prestatill_drive|escape:'htmlall':'UTF-8'}_closing_am" name="PRESTATILL_DRIVE_CLOSING_AM_{$d.id_prestatill_drive|escape:'htmlall':'UTF-8'}" value="{$d.hour_close_am|escape:'htmlall':'UTF-8'}"/>
						</td>
						<td class="text-left no">
							<input type="time" step="2" data-id="{$d.id_prestatill_drive|escape:'htmlall':'UTF-8'}" id="{$d.id_prestatill_drive|escape:'htmlall':'UTF-8'}_opening_pm" name="PRESTATILL_DRIVE_OPENING_PM_{$d.id_prestatill_drive|escape:'htmlall':'UTF-8'}" value="{$d.hour_open_pm|escape:'htmlall':'UTF-8'}"/>
						</td>
						<td class="text-left no">
							<input type="time" step="1" data-id="{$d.id_prestatill_drive|escape:'htmlall':'UTF-8'}" id="{$d.id_prestatill_drive|escape:'htmlall':'UTF-8'}_closing_pm" name="PRESTATILL_DRIVE_CLOSING_PM_{$d.id_prestatill_drive|escape:'htmlall':'UTF-8'}" value="{$d.hour_close_pm|escape:'htmlall':'UTF-8'}"/>
						</td>
						<td class="text-center no">
							<input type="checkbox" data-id="{$d.id_prestatill_drive|escape:'htmlall':'UTF-8'}" id="{$d.id_prestatill_drive|escape:'htmlall':'UTF-8'}_nonstop" {if value==1} checked{/if} name="PRESTATILL_DRIVE_OPENING_NONSTOP_{$d.id_prestatill_drive|escape:'htmlall':'UTF-8'}" value="{$d.nonstop|escape:'htmlall':'UTF-8'}" {if $d.nonstop == 1}checked{/if}/>
						</td>
					</tr>
					{/foreach}				
				</tbody>
			</table>
			<br />
			<br />
			<hr>
			<h3><i class="fa fa-clock"></i> {l s='Additional waiting time depending on the current day and time' mod='prestatilldrive'} <span class="store_name"></span></h3>	
			<div id="info_locales" class="col-xs-12 alert alert-info">{l s='You can here define an additional waiting time depending on the current day and time your customers are placing an order.' mod='prestatilldrive'}<br />
				{l s='For example: were are now ' mod='prestatilldrive'} <b>{$smarty.now|escape:'htmlall':'UTF-8'|date_format:"%A"}</b> {l s='and your waiting time is currently about ' mod='prestatilldrive'} <b>{$carence|escape:'htmlall':'UTF-8'} {l s='minutes' mod='prestatilldrive'} ({$carence/60|escape:'htmlall':'UTF-8'} {if $carence/60 == 1}}{l s='hour' mod='prestatilldrive'}{else}{l s='hours' mod='prestatilldrive'}{/if})</b>. {l s='If you add an additional 24-hour (1440 minutes) waiting time for orders placed today after 20:00, your waiting time will be:' mod='prestatilldrive'} <b>{$carence + 1440|escape:'htmlall':'UTF-8'} {l s='minutes' mod='prestatilldrive'} ({($carence + 1440)/60|escape:'htmlall':'UTF-8'} {l s='hours' mod='prestatilldrive'}).</b>
			</div>
			<div class="clearfix"></div>	
			<div id="carence_supp_{$k|escape:'htmlall':'UTF-8'}" class="carence_supp">
				<input type="hidden" id="id_shop_group" name="id_shop_group" value="{$id_shop_group|escape:'htmlall':'UTF-8'}" />
				<input type="hidden" id="id_shop" name="id_shop" value="{$id_shop|escape:'htmlall':'UTF-8'}" />
				<table class="table data-table hour_open">
					<thead>
						<th style="text-align: center;" colspan="2">{l s='For orders placed beetween' mod='prestatilldrive'}</th>
						<th style="text-align: center;" colspan="2">{l s='and' mod='prestatilldrive'}</th>
						<th style="text-align: center;">{l s='Additional waiting time:' mod='prestatilldrive'}</th>
						<th></th>
					</thead>
					<tbody>
						{include file="../tabs/carence_supp.tpl"}
					</tbody>
					<tfoot>
						<tr>
							<td style="text-align: center;width: 20%;">
								<select id="PRESTATILL_CARENCE_SUPP_DAY_{$k|escape:'htmlall':'UTF-8'}" name="PRESTATILL_CARENCE_SUPP_DAY_{$k|escape:'htmlall':'UTF-8'}" class="select_date_1">
									<option value="0">{l s='All days' mod='prestatilldrive'}</option>
									{foreach from=$day item=d }
										<option value="{$d.id_day|escape:'htmlall':'UTF-8'}">{$formatted_days[$d.id_day]|escape:'htmlall':'UTF-8'}</option>
									{/foreach}
								</select>
							</td>
							<td style="text-align: center;width: 20%;">
								<input type="time" step="1" id="PRESTATILL_CARENCE_SUPP_HOUR_LIMIT_{$k|escape:'htmlall':'UTF-8'}" name="PRESTATILL_CARENCE_SUPP_HOUR_LIMIT_{$k|escape:'htmlall':'UTF-8'}" value="23:00" />
							</td>
							<td style="text-align: center;width: 20%;">
								<select id="PRESTATILL_CARENCE_SUPP_DAY_END_{$k|escape:'htmlall':'UTF-8'}" name="PRESTATILL_CARENCE_SUPP_DAY_END_{$k|escape:'htmlall':'UTF-8'}" class="select_date_2">
									<option value="0">{l s='All days' mod='prestatilldrive'}</option>
									{foreach from=$day item=d }
										<option value="{$d.id_day|escape:'htmlall':'UTF-8'}">{$formatted_days[$d.id_day]|escape:'htmlall':'UTF-8'}</option>
									{/foreach}
								</select>
							</td>
							<td style="text-align: center;width: 20%;">
								<input type="time" step="1" id="PRESTATILL_CARENCE_SUPP_HOUR_LIMIT_END_{$k|escape:'htmlall':'UTF-8'}" name="PRESTATILL_CARENCE_SUPP_HOUR_LIMIT_END_{$k|escape:'htmlall':'UTF-8'}" value="06:00" />
							</td>
							<td style="text-align: center;width: 30%;">
								<div class="input-group">
									<input type="number" name="PRESTATILL_DRIVE_CARENCE_SUPP_{$k|escape:'htmlall':'UTF-8'}" style="text-align: center" id="PRESTATILL_DRIVE_CARENCE_SUPP_{$k|escape:'htmlall':'UTF-8'}" value="1440">	
									<span class="input-group-addon">min</span>				
								</div>
							</td>
							<td style="text-align: center;width: 10%;">
								<button name="carrence_supp_validate_{$k|escape:'htmlall':'UTF-8'}" id="carrence_supp_validate_{$k|escape:'htmlall':'UTF-8'}" type="submit" class="btn btn-default" data-id_store="{$k|escape:'htmlall':'UTF-8'}" data-id_lang={$id_lang|escape:'htmlall':'UTF-8'} data-url="{Context::getContext()->link->getModuleLink('prestatilldrive', 'validateordercarrier')|escape:'htmlall':'UTF-8'}">{l s='Add' mod='prestatilldrive'}</button>
							</td>
						</tr>
					</tfoot>
				</table>
				<div class="clearfix"></div>
			</div>	
			<div id="info_locales" class="col-xs-12 alert alert-info">{l s='If you want to display the dates in a specific languages in front-office, you must install the locales dates packages on your webserver.' mod='prestatilldrive'}<br />
				{l s='For example on Ubuntu server : ' mod='prestatilldrive'} <b>apt-get install language-pack-fr</b>. {l s='A server reboot could be necessary.' mod='prestatilldrive'}
			</div>
			<div class="clearfix"></div>	
		</div>
		<div class="panel-footer">
			 <div class="btn-group pull-right">
	            <button name="submitConfigDrive" id="submitConfigDrive" type="submit" class="btn btn-default"><i class="process-icon-save"></i> {l s='Save' mod='prestatilldrive'}</button>
	        </div>
	   </div>
	</section>
	{/foreach}
	</form>
{/if}

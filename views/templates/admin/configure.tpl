{*
* Prestatill Drive - Click & Collect
*
* Drive Module & Click & Collect for Prestashop
*
*  @author    Laurent Baumgartner laurent@prestatill.com
*  @copyright 2017-2020 Prestatill SAS
*  @license   Prestatill SAS
*}

<div class="clearfix"></div>

<div class="bootstrap">
	<!-- Module content -->
	<div id="modulecontent" class="clearfix">	
		{if isset($confirmation)}		
			{if $confirmation == true}
			<div class="alert alert-success">{l s='Settings updated' mod='prestatilldrive'}</div>
			{else}
			<div class="alert alert-warning">
				{l s='Error on settings updated' mod='prestatilldrive'}		
			</div>
			{/if}
		{/if}
		<!-- Nav tabs -->
		<div class="col-lg-2">
			<div class="list-group">
                <a href="#clickandcollect" class="menu_tab list-group-item {if $tab == 1}active{/if}" data-toggle="tab"><i class="icon-car"></i> {l s='Pick-up and slot selector' mod='prestatilldrive'}</a>
                <a href="#parameter" class="menu_tab list-group-item {if $tab == 2}active{/if}" data-toggle="tab"><i class="icon-cogs"></i> {l s='Parameters' mod='prestatilldrive'}</a>
				<a href="#openingdays" class="menu_tab list-group-item {if $tab == 3}active{/if}" data-toggle="tab"><i class="icon-calendar"></i> {l s='Opening Days' mod='prestatilldrive'}</a>
				<a href="#vacation" class="menu_tab list-group-item {if $tab == 4}active{/if}" data-toggle="tab"><i class="icon-sun-o"></i> {l s='Vacation' mod='prestatilldrive'}</a>
			</div>
			<div class="list-group">
				<a class="list-group-item"><i class="icon-info"></i> {l s='Version' mod='prestatilldrive'} {$module_version|escape:'htmlall':'UTF-8'}</a>
			</div>
		</div>
		
		<!-- Tab panes -->
		<div class="tab-content col-lg-10">
            <div class="tab-pane panel {if $tab == 1}active{/if}" id="clickandcollect">
                {include file="./tabs/clickandcollect.tpl"}
            </div>
        </div>
        
        <div class="tab-content col-lg-10">
			<div class="tab-pane panel {if $tab == 2}active{/if}" id="parameter">
				{include file="./tabs/parameter.tpl"}
			</div>
		</div>
		<div class="tab-content col-lg-10">
			
			<div class="tab-pane panel {if $tab == 3}active{/if} col-md-12 tab_padd_12" id="openingdays">
				{if $module_version|escape:'htmlall':'UTF-8' <= '1.2.0'}
					<div class="alert alert-danger">
						{l s='Be careful : if you see this message, you just updated de Drive & Click & collect module, but you should re-init it to fully use the new features (you\'ll just have to setup the opening days and hours again.' mod='prestatilldrive'}
					</div>
					<div>&nbsp;</div>
				{/if}
				<div class="{if !empty($stores)}col-md-2{else}col-xs-12{/if} store_list" id="storelisting">
					<h3><i class="material-icons mi-store"></i> {l s='Stores' mod='prestatilldrive'}</h3>
					{if !empty($stores)}
					<ul>
						{foreach from=$stores item=store name=foo}
							<li data-id_store="{$store.id_store|escape:'htmlall':'UTF-8'}" {if $smarty.foreach.foo.iteration == 1}class="active"{/if}>{$store.name|escape:'htmlall':'UTF-8'}</li>
						{/foreach}
					</ul>
					{else}
						<div class="alert-info alert">
							<div>
							{l s='No store created yet. Create at leat one store to configure opening days.' mod='prestatilldrive'}
							</div>
							<a href="{$stores_link|escape:'htmlall':'UTF-8'}" target="_blank" class="bt_drive_link">
								<i class="icon-external-link-sign"></i>
								{l s='Go to stores creation page' mod='prestatilldrive'}
							</a>
						</div>
					{/if}
				</div>
				<div class="col-md-10">
					{include file="./tabs/openingdays.tpl"}
				</div>
			</div>
		</div>
		
		<div class="tab-content col-lg-10">
			<div class="tab-pane panel {if $tab == 4}active{/if}" id="vacation">
				{include file="./tabs/vacation.tpl"}
			</div>
		</div>
	</div>
</div>
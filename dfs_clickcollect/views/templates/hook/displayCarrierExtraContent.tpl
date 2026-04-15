<style>
/* 
  Correction ciblée : 
  Empêche le thème Hummingbird de refermer ou couper le bloc Click & Collect.
*/
.delivery-options .carrier-extra-content,
.delivery-options__item .js-carrier-extra-content,
.delivery-option .carrier__extra-content-wrapper {
    max-height: none !important;
    height: auto !important;
    overflow: visible !important;
}
</style>

<div class="dfs-clickcollect-container col-sm-12 col-xs-12 w-100 clearfix" data-ajax-url="{$dfs_ajax_link}" style="margin-top: 15px; margin-bottom: 25px; clear: both; float: left; width: 100%;">
    <!-- Hidden inputs for state -->
    <input type="hidden" class="dfs_store" value="{if isset($dfs_stores[0])}{$dfs_stores[0].id_store}{/if}">
    <input type="hidden" class="dfs_date" value="">
    <input type="hidden" class="dfs_time" value="">

    <div style="background-color: #2cb1c1; color: white; padding: 15px; font-weight: bold; font-size: 16px;">
        Sélectionnez votre point de retrait
    </div>

    <!-- Store Selection -->
    <div style="background-color: #2cb1c1; padding: 0 15px 15px 15px; color:white;">
        <select class="form-control dfs_store_selector" style="width: 100%; border-radius: 4px; padding: 10px; font-weight:bold;">
            <option value="">-- Veuillez choisir un magasin --</option>
            {foreach from=$dfs_stores item=store}
                <option value="{$store.id_store}">{$store.name} - {$store.city}</option>
            {/foreach}
        </select>
        
        {foreach from=$dfs_stores item=store}
            <div class="dfs_store_details dfs_store_details_{$store.id_store}" style="display:none; padding-top:10px; font-size: 13px;">
                {$store.address1}<br />
                {if isset($store.address2) && $store.address2}{$store.address2}<br />{/if}
                {$store.postcode} {$store.city}
            </div>
        {/foreach}
    </div>

    <!-- Date Selection -->
    <div class="dfs_date_container" style="display:none; margin-top:20px;">
        <div style="overflow-x: auto; white-space: nowrap; padding-bottom: 15px; border-bottom: 1px solid #eee;" class="dfs_date_scroll">
            <div style="padding:15px; color:#999;">Chargement des dates...</div>
        </div>
    </div>

    <!-- Time Selection -->
    <div class="dfs_time_container" style="display:none; margin-top:15px;">
        <div style="overflow-x: auto; white-space: nowrap; padding-bottom: 15px;" class="dfs_time_scroll">
            <div style="padding:15px; color:#999;">Chargement des créneaux...</div>
        </div>
    </div>

    <!-- Validation Confirmation -->
    <div class="dfs_validation_message" style="display:none; margin-top: 15px; padding: 15px; background-color: #dff0d8; border: 1px solid #d6e9c6; color: #3c763d; border-radius: 4px; font-weight:bold; text-align:center;">
        <i class="material-icons" style="vertical-align: middle;">check_circle</i> Créneau validé pour cette commande
    </div>
</div>

{*
 * DFS Click & Collect
 *
 * @author    Cyrille Mohr - Digital Food System
 * @copyright Digital Food System
 * @license   Commercial
 *}

<script>
    document.addEventListener("DOMContentLoaded", function() {
        setTimeout(function() {
            var titleEl = document.querySelector('.page-title, .page-head h1.title');
            if (titleEl) {
                titleEl.innerText = 'Click & Collect Configuration';
            }
        }, 50);
    });
</script>

<div class="row">
    <!-- Colonne Gauche : Liste des magasins & Formulaire de paramètres -->
    <div class="col-lg-12 col-xl-4">
        <div class="panel">
            <div class="panel-heading">
                <i class="icon-cogs"></i> {l s='Paramètres Généraux' d='Modules.Dfsclickcollect.Admin'}
            </div>
            <form method="post" action="{$dfs_post_link|escape:'html':'UTF-8'}" class="form-horizontal">
                <div class="form-group mb-3">
                    <label class="control-label col-lg-6">{l s='Durée d\'un créneau (minutes)' d='Modules.Dfsclickcollect.Admin'}</label>
                    <div class="col-lg-6">
                        <input type="number" name="DFS_DRIVE_DUREE" class="form-control" value="{$dfs_duration|intval}">
                    </div>
                <div class="form-group mb-3">
                    <label class="control-label col-lg-6" style="padding-top: 10px;">{l s='Transporteur Click & Collect' d='Modules.Dfsclickcollect.Admin'}</label>
                    <div class="col-lg-6" style="padding-top: 10px;">
                        <select name="DFS_DRIVE_CARRIER_ID" class="form-control">
                            <option value="0">-- {l s='Sélectionnez un transporteur' d='Modules.Dfsclickcollect.Admin'} --</option>
                            {foreach from=$dfs_carriers item=carrier}
                                <option value="{$carrier.id_reference|intval}" {if $dfs_carrier_id == $carrier.id_reference}selected{/if}>{$carrier.name|escape:'html':'UTF-8'}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                <div class="panel-footer">
                    <button type="submit" name="submitDfsSettings" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Enregistrer' d='Modules.Dfsclickcollect.Admin'}</button>
                </div>
            </form>
        </div>

        <div class="panel mt-3" style="margin-top:20px;">
            <div class="panel-heading">
                <i class="icon-map-marker"></i> {l s='Magasins Physiques' d='Modules.Dfsclickcollect.Admin'}
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{l s='ID' d='Modules.Dfsclickcollect.Admin'}</th>
                            <th>{l s='Nom' d='Modules.Dfsclickcollect.Admin'}</th>
                            <th>{l s='Ville' d='Modules.Dfsclickcollect.Admin'}</th>
                            <th class="text-right">{l s='Action' d='Modules.Dfsclickcollect.Admin'}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach from=$dfs_stores item=store}
                            <tr {if $dfs_selected_store == $store.id_store}class="info"{/if}>
                                <td>{$store.id_store|intval}</td>
                                <td>{$store.name|escape:'html':'UTF-8'}</td>
                                <td>{$store.city|escape:'html':'UTF-8'}</td>
                                <td class="text-right">
                                    <a href="{$dfs_post_link|escape:'html':'UTF-8'}&configure_store={$store.id_store|intval}" class="btn btn-primary btn-sm">
                                        <i class="icon-calendar"></i> {l s='Horaires' d='Modules.Dfsclickcollect.Admin'}
                                    </a>
                                </td>
                            </tr>
                        {foreachelse}
                            <tr>
                                <td colspan="4" class="text-center">{l s='Aucun magasin physique n\'est créé dans Paramètres de la boutique > Contact > Magasins' d='Modules.Dfsclickcollect.Admin'}</td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <a href="{$link->getAdminLink('AdminStores')|escape:'html':'UTF-8'}" target="_blank" class="btn btn-default">
                    <i class="process-icon-plus"></i> {l s='Créer un nouveau magasin (Natif)' d='Modules.Dfsclickcollect.Admin'}
                </a>
            </div>
        </div>
    </div>

    <!-- Colonne Centrale : Formulaire d'horaires (affiché uniquement si selectionné) -->
    <div class="col-lg-12 col-xl-8">
        {if $dfs_selected_store}
            <div class="panel">
                <div class="panel-heading">
                    <i class="icon-calendar"></i> {l s='Planning du Magasin' d='Modules.Dfsclickcollect.Admin'} #{$dfs_selected_store|intval}
                </div>
                <form method="post" action="{$dfs_post_link|escape:'html':'UTF-8'}&configure_store={$dfs_selected_store|intval}">
                    <input type="hidden" name="id_store_planning" value="{$dfs_selected_store|intval}">
                    
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{l s='Jour' d='Modules.Dfsclickcollect.Admin'}</th>
                                    <th class="text-center">{l s='Ouverture' d='Modules.Dfsclickcollect.Admin'}</th>
                                    <th>{l s='Matin' d='Modules.Dfsclickcollect.Admin'}</th>
                                    <th>{l s='Après-midi' d='Modules.Dfsclickcollect.Admin'}</th>
                                    <th class="text-center">{l s='Délai min. (min)' d='Modules.Dfsclickcollect.Admin'}</th>
                                    <th class="text-center">{l s='Non-Stop' d='Modules.Dfsclickcollect.Admin'}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {foreach from=$dfs_store_planning key=i item=day}
                                <tr>
                                    <td style="font-weight: bold;">{$day.name|escape:'html':'UTF-8'}</td>
                                    
                                    <td class="text-center" style="vertical-align: middle;">
                                        <input type="checkbox" name="days[{$i|intval}][openning]" value="1" {if $day.openning}checked{/if}>
                                    </td>
                                    
                                    <td>
                                        <div class="input-group">
                                            <input type="time" class="form-control" name="days[{$i|intval}][am_open]" value="{$day.hour_open_am|escape:'html':'UTF-8'}">
                                            <span class="input-group-addon">{l s='à' d='Modules.Dfsclickcollect.Admin'}</span>
                                            <input type="time" class="form-control" name="days[{$i|intval}][am_close]" value="{$day.hour_close_am|escape:'html':'UTF-8'}">
                                        </div>
                                    </td>
                                    
                                    <td>
                                        <div class="input-group">
                                            <input type="time" class="form-control" name="days[{$i|intval}][pm_open]" value="{$day.hour_open_pm|escape:'html':'UTF-8'}">
                                            <span class="input-group-addon">{l s='à' d='Modules.Dfsclickcollect.Admin'}</span>
                                            <input type="time" class="form-control" name="days[{$i|intval}][pm_close]" value="{$day.hour_close_pm|escape:'html':'UTF-8'}">
                                        </div>
                                    </td>
                                    
                                    <td class="text-center" style="vertical-align: middle;">
                                        <input type="number" min="0" class="form-control" name="days[{$i|intval}][delay]" value="{$day.delay|intval}" style="width: 80px; margin: 0 auto;">
                                    </td>
                                    
                                    <td class="text-center" style="vertical-align: middle;">
                                        <input type="checkbox" name="days[{$i|intval}][nonstop]" value="1" {if $day.nonstop}checked{/if}>
                                    </td>
                                </tr>
                                {/foreach}
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="panel-footer">
                        <button type="submit" name="submitStorePlanning" class="btn btn-default pull-right">
                            <i class="process-icon-save"></i> {l s='Enregistrer le planning' d='Modules.Dfsclickcollect.Admin'}
                        </button>
                    </div>
                </form>
            </div>
        {else}
            <div class="alert alert-info">
                {l s='👈 Sélectionnez un magasin physique sur la gauche pour configurer son planning de retrait.' d='Modules.Dfsclickcollect.Admin'}
            </div>
        {/if}

        <!-- Fermetures Exceptionnelles -->
        <div class="panel mt-3" style="margin-top:20px;">
            <div class="panel-heading">
                <i class="icon-warning"></i> {l s='Fermetures Exceptionnelles (Vacances / Absences)' d='Modules.Dfsclickcollect.Admin'}
            </div>
            
            <form method="post" action="{$dfs_post_link|escape:'html':'UTF-8'}" class="form-inline mb-3 well" style="margin-bottom:20px;">
                <div class="row w-100">
                    <div class="form-group col-md-3">
                        <label>{l s='Magasin' d='Modules.Dfsclickcollect.Admin'}</label>
                        <select name="id_store_vacation" class="form-control" required>
                            <option value="0">-- {l s='Tous les magasins' d='Modules.Dfsclickcollect.Admin'} --</option>
                            {foreach from=$dfs_stores item=s}
                                <option value="{$s.id_store|intval}">{$s.name|escape:'html':'UTF-8'}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label>{l s='Date de début' d='Modules.Dfsclickcollect.Admin'}</label>
                        <input type="date" name="vacation_start" class="form-control" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label>{l s='Date de fin' d='Modules.Dfsclickcollect.Admin'}</label>
                        <input type="date" name="vacation_end" class="form-control" required>
                    </div>
                    <div class="form-group col-md-3" style="padding-top: 25px;">
                        <button type="submit" name="submitAddVacation" class="btn btn-primary d-block w-100">
                            <i class="icon-plus"></i> {l s='Ajouter la fermeture' d='Modules.Dfsclickcollect.Admin'}
                        </button>
                    </div>
                </div>
            </form>

            <table class="table">
                <thead>
                    <tr>
                        <th>{l s='Magasin concerné' d='Modules.Dfsclickcollect.Admin'}</th>
                        <th>{l s='Début de fermeture' d='Modules.Dfsclickcollect.Admin'}</th>
                        <th>{l s='Fin de fermeture' d='Modules.Dfsclickcollect.Admin'}</th>
                        <th class="text-right">{l s='Action' d='Modules.Dfsclickcollect.Admin'}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$dfs_vacations item=vac}
                        <tr>
                            <td>
                                {if $vac.id_store == 0}
                                    <span class="label label-warning">{l s='Tous' d='Modules.Dfsclickcollect.Admin'}</span>
                                {else}
                                    <span class="label label-info">{$vac.store_name|escape:'html':'UTF-8'}</span>
                                {/if}
                            </td>
                            <td>{$vac.vacation_start|escape:'html':'UTF-8'}</td>
                            <td>{$vac.vacation_end|escape:'html':'UTF-8'}</td>
                            <td class="text-right">
                                <form method="post" action="{$dfs_post_link|escape:'html':'UTF-8'}" style="display:inline;">
                                    <input type="hidden" name="id_vacation" value="{$vac.id_vacation|intval}">
                                    {if $dfs_selected_store}
                                        <input type="hidden" name="configure_store" value="{$dfs_selected_store|intval}">
                                    {/if}
                                    <button type="submit" name="deletevacation" class="btn btn-danger btn-sm" onclick="return confirm('{l s='Supprimer cette fermeture ?' d='Modules.Dfsclickcollect.Admin'}');">
                                        <i class="icon-trash"></i> {l s='Supprimer' d='Modules.Dfsclickcollect.Admin'}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    {foreachelse}
                        <tr>
                            <td colspan="4" class="text-center text-muted">{l s='Aucune fermeture exceptionnelle programmée.' d='Modules.Dfsclickcollect.Admin'}</td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    </div>
</div>

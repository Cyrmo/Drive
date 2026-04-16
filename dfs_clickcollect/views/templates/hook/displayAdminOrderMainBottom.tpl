{*
 * DFS Click & Collect
 *
 * @author    Cyrille Mohr - Digital Food System
 * @copyright Digital Food System
 * @license   Commercial
 *}

<div class="card mt-2">
    <div class="card-header">
        <h3 class="card-header-title">
            <i class="material-icons">store</i> {l s='DFS Click & Collect' d='Modules.Dfsclickcollect.Admin'}
        </h3>
    </div>
    <div class="card-body">
        {if isset($dfs_slot) && $dfs_slot}
            <div class="mb-3">
                <strong>{l s='Point de retrait :' d='Modules.Dfsclickcollect.Admin'}</strong> {$dfs_slot.store_name|escape:'html':'UTF-8'}<br>
                <strong>{l s='Date de retrait :' d='Modules.Dfsclickcollect.Admin'}</strong> {$dfs_slot.day|escape:'html':'UTF-8'}<br>
                <strong>{l s='Créneau :' d='Modules.Dfsclickcollect.Admin'}</strong> {$dfs_slot.hour|escape:'html':'UTF-8'}<br>
                {if $dfs_slot.pin_code}
                    <strong>{l s='Code PIN :' d='Modules.Dfsclickcollect.Admin'}</strong> <span class="badge badge-success">{$dfs_slot.pin_code|escape:'html':'UTF-8'}</span><br>
                {/if}
            </div>
            
            <hr>
            
            <p>DEBUG URL: <strong>{$dfs_ajax_url|escape:'html':'UTF-8'}</strong></p>
            
            <form action="{$dfs_ajax_url|escape:'html':'UTF-8'}" method="post" id="dfs_clickcollect_edit_slot_form">
                <input type="hidden" name="ajax" value="1">
                <input type="hidden" name="action" value="UpdateSlot">
                <input type="hidden" name="id_order" value="{$id_order|intval}">
                
                <p><strong>{l s='Modifier manuellement le créneau :' d='Modules.Dfsclickcollect.Admin'}</strong></p>
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>{l s='Point de retrait' d='Modules.Dfsclickcollect.Admin'}</label>
                        <select name="dfs_new_store" class="form-control" required>
                            {foreach from=$dfs_all_stores item=store}
                                <option value="{$store.id_store|intval}" {if $dfs_slot.id_store == $store.id_store}selected{/if}>{$store.name|escape:'html':'UTF-8'}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>{l s='Date' d='Modules.Dfsclickcollect.Admin'}</label>
                        <input type="date" name="dfs_new_date" class="form-control" value="{$dfs_slot.day|escape:'html':'UTF-8'}" required>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>{l s='Heure' d='Modules.Dfsclickcollect.Admin'}</label>
                        <input type="text" name="dfs_new_time" class="form-control" value="{$dfs_slot.hour|escape:'html':'UTF-8'}" required placeholder="ex: 10:00 - 11:00">
                    </div>
                    <div class="col-md-2 form-group d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">{l s='Valider' d='Modules.Dfsclickcollect.Admin'}</button>
                    </div>
                </div>
            </form>
        {else}
            <p class="text-muted">{l s='Cette commande n\'est pas associée à un retrait Click & Collect.' d='Modules.Dfsclickcollect.Admin'}</p>
        {/if}
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var form = document.getElementById("dfs_clickcollect_edit_slot_form");
        if (form) {
            form.addEventListener("submit", function(e) {
                e.preventDefault();
                var formData = new FormData(form);
                $.ajax({
                    url: form.getAttribute('action'),
                    type: 'POST',
                    processData: false,
                    contentType: false,
                    data: formData,
                    success: function(response) {
                        try {
                            var data = typeof response === 'string' ? JSON.parse(response) : response;
                            if (data.success) {
                                window.location.reload();
                            } else {
                                alert("Erreur: " + data.message);
                            }
                        } catch (e) {
                            alert("Détail de l'erreur brute : " + response.substring(0, 50));
                        }
                    },
                    error: function(xhr) {
                        alert("Une erreur inattendue est survenue: " + xhr.status + " " + xhr.statusText);
                    }
                });
            });
        }
    });
</script>

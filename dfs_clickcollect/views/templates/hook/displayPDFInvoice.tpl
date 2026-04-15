{*
 * DFS Click & Collect
 *
 * @author    Cyrille Mohr - Digital Food System
 * @copyright Digital Food System
 * @license   Commercial
 *}

<table style="width: 100%; border: 1px solid #333; padding: 5px; margin-top: 10px; background-color: #f9f9f9;">
    <tr>
        <td style="text-align: center; font-weight: bold; background-color: #2cb1c1; color: white; padding: 4px; font-size: 11pt;">
            {l s='DÉTAILS RETRAIT EN MAGASIN (CLICK & COLLECT)' d='Modules.Dfsclickcollect.Shop'}
        </td>
    </tr>
    <tr>
        <td style="text-align: center; padding: 8px; font-size: 10pt;">
            <br>
            <span style="font-size: 11pt;">{l s='BOUTIQUE :' d='Modules.Dfsclickcollect.Shop'} <strong>{$dfs_slot.store_name|escape:'html':'UTF-8'}</strong></span><br><br>
            <span style="font-size: 11pt;">{l s='DATE :' d='Modules.Dfsclickcollect.Shop'} <strong>{$dfs_slot.day|escape:'html':'UTF-8'}</strong></span><br><br>
            <span style="font-size: 11pt;">{l s='CRÉNEAU :' d='Modules.Dfsclickcollect.Shop'} <strong>{$dfs_slot.hour|escape:'html':'UTF-8'}</strong></span>
            <br>
        </td>
    </tr>
</table>

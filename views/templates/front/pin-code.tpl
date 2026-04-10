{*
* Prestatill Drive - Click & Collect
*
* Drive Module & Click & Collect for Prestashop
*
*  @author    Laurent Baumgartner laurent@prestatill.com
*  @copyright 2017-2020 Prestatill SAS
*  @license   Prestatill SAS
*}

{if !empty($pin_code)}
    <table style="text-align: center;" cellpadding="5" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th style="border:0px dashed #333;width:10%;font-weight:bold;text-align:center;">{l s='YOUR PICK-UP PIN CODE' mod='prestatilldrive'}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="border:2px dashed #333;background:#FFF;font-size:30px;padding:10px;">{$pin_code|escape:'htmlall':'UTF-8'}</td>
            </tr>
        </tbody>
    </table>
{/if}

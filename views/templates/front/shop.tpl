{*
* Prestatill Drive - Click & Collect
*
* Drive Module & Click & Collect for Prestashop
*
*  @author    Laurent Baumgartner laurent@prestatill.com
*  @copyright 2017-2020 Prestatill SAS
*  @license   Prestatill SAS
*}

	<div id="shop_selected" class="alert alert-warning">
		<h5>{l s='Your shop' mod='prestatilldrive'}</h5>
		<div id="shop" class="">
			
		</div>
        <button type="button" class="btn btn-secondary changeStore" data-id_store="{$id_store|escape:'htmlall':'UTF-8'}" data-toggle="" data-target="">
            {l s='Change store' mod='prestatilldrive'}
        </button>
        <div class="clearfix"></div>
        {if $store_selector_active}<input id="store_selector_active" value="" type="hidden" />{/if}
	</div>
	{*
	<div class="modal fade" id="confirm_change_modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{l s='Confirmation' mod='prestatilldrive'}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    {l s='Be carefull, you are going to loose your booked slot if you confirm change ?' mod='prestatilldrive'}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary closing" data-dismiss="modal">
                        {l s='Close' mod='prestatilldrive'}
                    </button>
                </div>
            </div>
        </div>
    </div>
    *}

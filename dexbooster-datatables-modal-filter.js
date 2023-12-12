setTimeout(() => {
    dexbooster_datatables_modal_filter_build_button()
    dexbooster_datatables_modal_filter_build_modal()
    dexbooster_datatables_modal_filter_build_form()
}, 1000)

function dexbooster_datatables_modal_filter_build_button() {
    jQuery(`[type="search"]`).after(`&nbsp;<input onclick="dexbooster_datatables_modal_filter_button_click()" style="height:25px !important; vertical-align:unset; color: white;" class="my-button" id="detail_filter" type="submit" value="Detail Filter">`)
}

function dexbooster_datatables_modal_filter_build_modal() {
    jQuery(`.wpdt-c`).eq(0).parent().append(`
        <div class="wpdt-c">
            <div id="dexbooster-datatables-modal-filter" class="modal fade" style="display: none" data-backdrop="static"
                data-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <form></form>
                        </div>
                        <div class="modal-footer">
                            <input type="submit" onclick="dexbooster_datatables_modal_filter_submit_form()" class="my-button" style="color: white; padding: 20px" value="Apply">
                            <input type="submit" class="my-button" style="color: white; padding: 20px" value="Reset">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `)
}

function dexbooster_datatables_modal_filter_build_form() {
    const fields = ['APY_1h', 'APY_24h', 'APY_6h', 'APY_chart', 'APY_day_1h', 'APY_day_24h', 'APY_day_6h', 'Address', 'Blockchain', 'Creation_date', 'DEX', 'Dex_image', 'FDV', 'Fees_1h', 'Fees_24h', 'Fees_6h', 'HTML', 'H_Range_Long', 'H_Range_Medium', 'H_Range_Short', 'L_Range_Long', 'L_Range_Medium', 'L_Range_Short', 'Pair', 'Price_USD', 'Price_change_1h', 'Price_change_24h', 'Price_change_6h', 'Price_vs_Native', 'Quote_Token', 'TVL', 'TVL 2', 'TVL_native', 'TVL_token', 'Tier', 'Token', 'Token_Address', 'Trx_1h', 'Trx_24h', 'Trx_6h', 'Trx_Buys_1h', 'Trx_Buys_24h', 'Trx_Buys_6h', 'Trx_Sells_1h', 'Trx_Sells_24h', 'Trx_Sells_6h', 'Volume_1h', 'Volume_24h', 'Volume_6h', 'apr_mensual', 'correlacion', 'recom1', 'recom2', 'recom3', 'recom4', 'recom5', 'srri', 'tvl_mensual', 'buy_tax', 'is_honeypot', 'is_mintable', 'is_proxy', 'sell_tax', 'url_goplus', 'verified']

    for (var field of fields) jQuery(`[id="dexbooster-datatables-modal-filter"] .modal-body form`).append(`
        <label style="width: 30%;">${field}</label>
            &nbsp;<input style="height: 35px; width: 30%;" type="text" name="${field}_min" placeholder="MIN">
            &nbsp;<input style="height: 35px; width: 30%;" type="text" name="${field}_max" placeholder="MAX">
        <br><br>
    `)
}

function dexbooster_datatables_modal_filter_button_click() {
    jQuery(`[id="dexbooster-datatables-modal-filter"]`).modal(`show`)
}

function dexbooster_datatables_modal_filter_submit_form() {
    const serialized = jQuery(`[id="dexbooster-datatables-modal-filter"] .modal-body form`).serialize()
    jQuery(`[type="search"]`).val(serialized).trigger(`keyup`)
    jQuery(`[id="dexbooster-datatables-modal-filter"]`).modal(`hide`)
}
setTimeout(() => {
    dexbooster_datatables_modal_filter_build_button()
    dexbooster_datatables_modal_filter_build_modal()
    dexbooster_datatables_modal_filter_build_form()
    dexbooster_datatables_modal_filter_build_form_buttons()
    dexbooster_datatables_modal_filter_intercept_ajax()
}, 1000)

function dexbooster_datatables_modal_filter_build_button() {
    jQuery(`[type="search"]`).after(`&nbsp;<input id="dexbooster_datatables_modal_filter_button" type="submit">`)
    const filter_button = jQuery(`#dexbooster_datatables_modal_filter_button`)
    filter_button.addClass(`my-button`)
    filter_button.prop(`style`, `height:25px !important; vertical-align:unset; color: white;`)

    filter_button.val(`Open Filter`)
    filter_button.click(() => {
        jQuery(`[id="dexbooster-datatables-modal-filter"]`).modal(`show`)
    })
}

function dexbooster_datatables_modal_filter_build_modal() {
    jQuery(`.wpdt-c`).eq(0).parent().append(`
        <div class="wpdt-c" style="font-family: 'IBM Plex Mono", Sans-serif';">
            <div id="dexbooster-datatables-modal-filter" class="modal fade" style="display: none" data-backdrop="static"
                data-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog" style="width: auto; font-size: 12px">
                    <div class="modal-content" style="color: white;">
                        <div class="modal-header" style="background-color: #28282d">
                            <b>Customize Filters</b>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true" style="color: white; font-size: 25px">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body" style="background-color: #1d1d22">
                            <br>
                            <form>
                                <table style="border:0"></table>
                            </form>
                        </div>
                        <div class="modal-footer" style="text-align: center; background-color: #28282d">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `)
    const modal = jQuery(`[id="dexbooster-datatables-modal-filter"]`)
    modal.on(`hidden.bs.modal`, () => {
        wpDataTables.table_1.fnDraw()
        var is_empty = true
        modal.find(`[type="text"]`).each(function () {
            if (`` !== jQuery(this).val()) is_empty = false
        })
        jQuery(`#dexbooster_datatables_modal_filter_button`).val(is_empty ? `Open Filter` : `Edit Filter`)
    })
}

function dexbooster_datatables_modal_filter_build_form() {
    const fields = [{ name: "APY_1h", label: "APY 1h", type: "min & max" },
    { name: "APY_24h", label: "APY 24h", type: "min & max" },
    { name: "APY_6h", label: "APY 6h", type: "min & max" },
    { name: "DEX", label: "DEX", type: "Name" },
    { name: "FDV", label: "FDV", type: "min & max" },
    { name: "Fees_1h", label: "Fees 1h", type: "min & max" },
    { name: "Fees_24h", label: "Fees 24h", type: "min & max" },
    { name: "Fees_6h", label: "Fees 6h", type: "min & max" },
    { name: "TVL", label: "TVL", type: "min & max" },
    { name: "Trx_1h", label: "Trx 1h", type: "min & max" },
    { name: "Trx_24h", label: "Trx 24h", type: "min & max" },
    { name: "Trx_6h", label: "Trx 6h", type: "min & max" },
    { name: "Volume_1h", label: "Volume 1h", type: "min & max" },
    { name: "Volume_24h", label: "Volume 24h", type: "min & max" },
    { name: "Volume_6h", label: "Volume 6h", type: "min & max" }]

    for (var field of fields) {
        const input = `min & max` === field.type ?
            `
            <td style="border: 0;">
            <div style="display: flex; align-content: stretch;">
                <span style="background-color: #333333; padding: 0.5em 1em">$</span>
                <input style="flex: 1 0 auto;background-color: #1d1d22; border: 1px solid #333333;" type="text" name="${field.name}_min" placeholder="Min">
            </div>
            </td><td style="border: 0;">
            <div style="display: flex; align-content: stretch;">
                <span style="background-color: #333333; padding: 0.5em 1em">$</span>
                <input style="flex: 1 0 auto;background-color: #1d1d22; border: 1px solid #333333;" type="text" name="${field.name}_max" placeholder="Max">
            </div>
            </td>
        `:
        `<td style="border: 0;" colspan="2">
            <input style="width:-webkit-fill-available; background-color: #1d1d22; border: 1px solid #333333;" type="text" name="${field.name}_name" placeholder="Name">
        </td>`
        jQuery(`[id="dexbooster-datatables-modal-filter"] .modal-body form table`).append(`
            <tr>
                <td style="border: 0;"><label>${field.label}</label></td>
                ${input}
            </tr>
        `)
    }
}

function dexbooster_datatables_modal_filter_build_form_buttons() {
    const modal = jQuery(`[id="dexbooster-datatables-modal-filter"]`)
    modal.find(`.modal-footer`).append(`<input type="submit" class="my-button" style="color: white; padding: 15px !important; line-height: 2px !important" value="&#10004; Apply">`)
    // modal.find(`.modal-footer`).append(`&nbsp;<input type="reset" class="my-button" style="color: white; padding: 20px" value="Reset">`)

    // modal.find(`[type="reset"]`).click(() => {
    //     modal.find(`input[type="text"]`).val(``)
    //     modal.find(`[type="reset"]`).blur()
    // })

    modal.find(`[type="submit"]`).click(() => {
        modal.modal(`hide`)
    })
}

function dexbooster_datatables_modal_filter_intercept_ajax() {
    wpDataTables.table_1.on('preXhr.dt', function (e, settings, data) {
        data.dexbooster_datatables_modal_filter = jQuery(`[id="dexbooster-datatables-modal-filter"] .modal-body form`).serialize()
    })
}
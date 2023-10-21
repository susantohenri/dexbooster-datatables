jQuery(`.dexbooster-datatables`).not(`.dataTable`).each(function () {
    jQuery(this).dataTable({
        serverSide: true,
        processing: true,
        lengthChange: false,
        info: false,
        bSort: false,
        ajax: {
            url: dexbooster_datatables.json_parser_url,
            data: { source: jQuery(this).attr(`json-url`) },
            type: `POST`
        }
    })
})
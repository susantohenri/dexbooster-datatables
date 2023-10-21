jQuery(`.dexbooster-datatables`).not(`.dataTable`).each(function () {
    new DataTable(jQuery(this), {
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

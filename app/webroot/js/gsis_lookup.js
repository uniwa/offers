function gsis_lookup(req_url) {
    $.ajax(
        {
            type: 'GET',
            url: req_url,
            beforeSend: function() {
                $("#ajax-status").html('<img src="../img/ajax-loader.gif" width="18" height="18" alt="searching" />');
            },
            success: function(data, textStatus, jqXHR) {
                    data = JSON.parse(data);
                    $('#CompanyName').val(data.onomasia);
                    $('#CompanyPhone').val(data.firmPhone);
                    $('#CompanyFax').attr('value', data.firmFax);
                    $('#CompanyPostalcode').attr('value', data.postalZipCode);
                    $('#CompanyServiceType').attr('value', data.actLongDescr);
                    $('#CompanyAddress').attr('value',
                        data.postalAddress + ' ' + data.postalAddressNo);
                    $("#ajax-status").html('');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $("#ajax-status").html('<i class="icon-warning-sign"></i>');
            }
        }
    );
}
